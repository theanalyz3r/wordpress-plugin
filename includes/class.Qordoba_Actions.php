<?php

/**
 * Class Qordoba_Actions
 */
class Qordoba_Actions
{
    /**
     * Qordoba_Actions constructor.
     */
    public function __construct()
    {
        //admin-only actions
        if (is_admin()) {
            add_action('admin_enqueue_scripts', array($this, 'register_admin_scripts_styles'));
            add_action('save_post', array($this, 'maybe_send_post'), 50, 2);
            add_action('edit_attachment', array($this, 'maybe_send_post'), 50);
            add_action('edited_term', array($this, 'maybe_send_term'), 50, 3);
        }

        add_action('wp_enqueue_scripts', array($this, 'register_scripts'));

        add_action('wp_ajax_qordoba_ajax_send', array($this, 'qordoba_ajax_send'));
        add_action('wp_ajax_qordoba_ajax_download', array($this, 'qordoba_ajax_download'));

        add_action('wp_ajax_qordoba_cron', array($this, 'ajax_qordoba_cron'));
        add_action('wp_ajax_nopriv_qordoba_cron', array($this, 'ajax_qordoba_cron'));

        add_action('wp_ajax_qordoba_send_bulk', array($this, 'ajax_qordoba_send_bulk'));
        add_action('wp_ajax_qordoba_download_bulk', array($this, 'ajax_qordoba_download_bulk'));
    }

    /**
     *
     */
    public function register_admin_scripts_styles()
    {
        wp_register_script(
            'qordoba-widget',
            QORDOBA_PLUGIN_URL . 'assets/js/qordoba.widget.js',
            array('jquery'),
            QORDOBA_VERSION,
            true
        );

        wp_register_script(
            'qordoba-options',
            QORDOBA_PLUGIN_URL . 'assets/js/qordoba.options.js',
            array('jquery'),
            QORDOBA_VERSION,
            true
        );

    }

    /**
     *
     */
    public function register_scripts()
    {
        wp_register_script(
            'qordoba-cron',
            QORDOBA_PLUGIN_URL . 'assets/js/qordoba.cron.js',
            array('jquery'),
            QORDOBA_VERSION,
            true
        );

        if (qor()->cron_job_ready() && qor()->get_project_language()) {
            $data = array(
                'ajaxurl' => admin_url('admin-ajax.php'),
            );

            wp_enqueue_script('qordoba-cron');
            wp_localize_script('qordoba-cron', 'qordoba', $data);
        }
    }

    /**
     * @param $post_id
     * @param null $post
     */
    public function maybe_send_post($post_id, $post = null)
    {
        // skip on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        // compatibility with edit_attachment hook, which does not pass $post object
        if (null === $post)
            $post = get_post($post_id);

        // check if post type supports translation
        if (!qor()->module()->is_translated_post_type($post->post_type))
            return;

        // mark post as candidate for automatic translation
        update_post_meta($post_id, '_qor_updated', current_time('timestamp'));

        // check if user has permissions to translate post
        if (!qor()->current_user_can_translate($post_id, 'post'))
            return;

        // check if translation was requested
        if (!isset($_REQUEST['save']) || $_REQUEST['save'] !== 'qordoba_send')
            return;

        // avoid infinite loop
        remove_action('save_post', array($this, 'maybe_send_post'), 50, 2);
        remove_action('edit_attachment', array($this, 'maybe_send_post'), 50);
        qor()->send_post($post_id);
    }

    /**
     * @param $term_id
     * @param $tt_id
     * @param $taxonomy
     */
    public function maybe_send_term($term_id, $tt_id, $taxonomy)
    {
        // check if taxonomy supports translation
        if (!qor()->module()->is_translated_taxonomy($taxonomy))
            return;

        // mark post as candidate for automatic translation
        update_term_meta($term_id, '_qor_updated', current_time('timestamp'));

        // check if translation was requested
        if (!isset($_REQUEST['submit']) || $_REQUEST['submit'] !== 'qordoba_send')
            return;

        // check if user has permissions to translate post
        if (!qor()->current_user_can_translate($term_id, 'term'))
            return;

        // avoid infinite loop
        remove_action('edited_term', array($this, 'maybe_send_term'), 50, 3);
        qor()->send_term($term_id);
    }

    /**
     *
     */
    public function ajax_qordoba_send_bulk()
    {
        if (!check_ajax_referer('qordoba_send_bulk', 'qor_nonce', false) || !current_user_can('manage_options')) {
            wp_send_json(array(
                'error' => true,
                'errorMessage' => __('Access denied or your session has expired.')
            ));

            wp_die(); //just in case
        }

        if (!qor()->get_project_language()) {
            wp_send_json(array(
                'error' => true,
                'errorMessage' => 'Main language is not configured!',
            ));
        }

        $max_time = isset($_REQUEST['max_time']) ? (int)$_REQUEST['max_time'] : 0;
        $max_time = max($max_time, 20);

        $result = qor()->send_updated_translations(10, true);

        wp_send_json($result);
        wp_die(); // again, just in case
    }

    /**
     *
     */
    public function ajax_qordoba_download_bulk()
    {
        if (!check_ajax_referer('qordoba_send_bulk', 'qor_nonce', false) || !current_user_can('manage_options')) {
            wp_send_json(array(
                'error' => true,
                'errorMessage' => __('Access denied or your session has expired.')
            ));

            wp_die(); //just in case
        }

        if (!qor()->get_project_language()) {
            wp_send_json(array(
                'error' => true,
                'errorMessage' => 'Main language is not configured!',
            ));
        }

        $max_time = isset($_REQUEST['max_time']) ? (int)$_REQUEST['max_time'] : 0;
        $max_time = max($max_time, 15);
        $max_items = isset($_REQUEST['max_items']) ? (int)$_REQUEST['max_items'] : 10;
        $timestamp = isset($_REQUEST['timestamp']) ? (int)$_REQUEST['timestamp'] : false;

        try {
            $result = qor()->download_pending_translations($max_time, $max_items, $timestamp);
        } catch (Exception $e) {
            wp_send_json(array(
                'error' => true,
                'errorMessage' => $e->getMessage(),
            ));

            wp_die();
        }

        wp_send_json($result);
    }

    /**
     *
     */
    public function ajax_qordoba_cron()
    {
        //pll_ajax_backend query variable SHOULD BE PASSED
        //otherwise Polylang won't load essential admin features

        //global $polylang;
        //$polylang = new PLL_Admin();

        if (!qor()->cron_job_ready()) {
            wp_die();
        }

        try {
            // spend about 5 minutes for requesting translations
            qor()->download_pending_translations(5 * MINUTE_IN_SECONDS);
        } catch (Exception $e) {
            printf('<p>Error: %s</p>', $e->getMessage());
        }
    }

    /**
     *
     */
    public function qordoba_ajax_send()
    {
        check_ajax_referer('qordoba_widget_action', 'qor_nonce', true);

        $object_id = $object_id = isset($_REQUEST['object_id']) ? (int)$_REQUEST['object_id'] : 0;
        $object_type = isset($_REQUEST['object_type']) ? $_REQUEST['object_type'] : 'post';
        $languages = isset($_REQUEST['languages']) ? $_REQUEST['languages'] : false;

        if (!$object_id) {
            wp_send_json(array('success' => false, 'message' => 'object_id not set'));
        }

        if (!qor()->current_user_can_translate($object_id, $object_type)) {
            wp_send_json(array('success' => false, 'message' => 'insufficient permissions'));
        }

        if ('post' == $object_type) {
            $resut = qor()->send_post($object_id);
        } elseif ('term' == $object_type) {
            $resut = qor()->send_term($object_id);
        } else {
            wp_send_json(array('success' => false, 'message' => 'unknown object type'));
        }

    }

    /**
     * @throws Exception
     */
    public function qordoba_ajax_download()
    {
        check_ajax_referer('qordoba_widget_action', 'qor_nonce', true);

        $object_id = $object_id = isset($_REQUEST['object_id']) ? (int)$_REQUEST['object_id'] : 0;
        $object_type = isset($_REQUEST['object_type']) ? $_REQUEST['object_type'] : 'post';
        $languages = isset($_REQUEST['languages']) ? $_REQUEST['languages'] : false;

        if (!$object_id) {
            wp_send_json(array('success' => false, 'message' => 'object_id not set'));
        }

        if (!qor()->current_user_can_translate($object_id, $object_type)) {
            wp_send_json(array('success' => false, 'message' => 'insufficient permissions'));
        }

        if ('post' == $object_type) {
            qor()->download_post($object_id, $languages, true);
        } elseif ('term' == $object_type) {
            qor()->download_term($object_id, $languages, true);
        } else {
            wp_send_json(array('success' => false, 'message' => 'unknown object type'));
        }

    }

    /*

    public function ajax_action_send() {
      // check ajax referer or die()
      wp_check_ajax_referer('ajax_widget_action', 'qor_nonce', true);

      // make sure the user has proper permissions
      if ( !qor()->current_user_can_translate($post_id) )
        wp_die();

      $post_id = isset($_REQUEST['post_id']) ? (int) $_REQUEST['post_id'] : 0;
      $object_type = isset($_REQUEST['object_type'])? $_REQUEST['object_type'] : 'post';
      $languages = isset($_REQUEST['lang'])? $_REQUEST['lang'] : array();

      switch ($object_type) {
        case 'post':
          qor()->queue_post_translation($post_id, $languages);
          break;
        case 'term':
          qor()->queue_term_translation($post_id, $languages);
          break;
        default:
          break;
      }


      wp_die();
    }
    */
}
