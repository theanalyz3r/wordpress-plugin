<?php

use Qordoba\Document;

class Qordoba {
  private static $instance = null;

  protected $_module = null;

  protected $options = null;

  protected $modules = array(
    'Polylang'  => 'Qordoba_Module_Polylang',
    //'WPML'      => 'Qordoba_Module_WPML',
  );

  protected $_api = null;

  public $actions = null;

  private function __construct() {
    // init module
    if ( is_admin() )
      add_action('wp_loaded', array($this, 'init'));

    $this->options = new Qordoba_Options();
    $this->actions = new Qordoba_Actions();
  }

  private function __clone() {}

  public static function getInstance() {
    if (null === self::$instance) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function init() {
    $this->modules = apply_filters('qordoba_available_modules', $this->modules);
    $this->load_module();
  }

  protected function load_module() {
    if ( null !== $this->_module ) {
      return $this->_module;
    }

    foreach ($this->modules as $name => $module) {
      if ( !class_exists($module) )
        continue;

      if ( true === call_user_func(array($module, 'plugin_enabled')) ) {
        return $this->_module = new $module();
      }
    }

    require_once QORDOBA_PLUGIN_DIR . '/modules/class.Qordoba_Module_Default.php';
    return $this->_module = new Qordoba_Module_Default();
  }

  public function options() {
    return $this->options;
  }

  public function get_project_language() {
    $project = $this->get_project_meta_data();

    if ($project) {
      return $this->map_qordoba_lang($project->source_language->code);
    } else {
      return false;
    }
  }

  public function module() {
    return $this->load_module();
  }

  public function get_modules() {
    return $this->modules;
  }

  public function api() {
    if ( null !== $this->_api )
      return $this->_api;

    return $this->_api = $this->new_qordoba_document();
  }

  public function get_project_languages() {
    $project_meta = $this->get_project_meta_data();

    if ( is_object($project_meta) && property_exists($project_meta, 'target_languages') )
      return $this->get_project_meta_data()->target_languages;

    return array();
  }

  public function get_project_meta_data() {
    if ( !$this->qordoba_api_configured() ) {
      return false;
    }

    $project = get_transient('qor_project_metadata');

    // request project data from Qordoba if there is nothing cached or if cached project doesn't match the one in options
    if (false === $project || $project->id != $this->options->get('project_id') || $project->organization_id != $this->options->get('organization_id')) {

      try {
        $project = $this->api()->getProject()->getMetadata()->project;
        set_transient('qor_project_metadata', $project, HOUR_IN_SECONDS);
      } catch (Exception $e) {
        return false;
      }

    }

    return $project;
  }

  public function current_user_can_translate($object_id = null, $object_type = 'post') {
    return current_user_can('manage_options');
  }

  public function qordoba_api_configured() {
    return $this->options->get('login') && $this->options->get('password') && $this->options->get('project_id') && $this->options->get('organization_id');
  }

  public function new_qordoba_document($type = 'html') {
    if (!$this->qordoba_api_configured())
      throw new Exception('Missing Qordoba API configuration (login, password, organization id or project id)');

    $document = new Qordoba\Document(
      QORDOBA_API_URL,
      $this->options->get('login'),
      $this->options->get('password'),
      $this->options->get('project_id'),
      $this->options->get('organization_id')
    );

    $document->setType($type);

    return $document;
  }

  public function map_qordoba_lang($qordoba_lang) {
    $languages = $this->options->get('languages');

    if ( !array($languages) || empty($languages) )
      return false;

    $languages = array_flip($languages);

    // return language that module will understand
    return isset($languages[$qordoba_lang]) ? $languages[$qordoba_lang] : false;
  }
  public function map_module_lang($module_lang) {
    $languages = $this->options->get('languages');

    if ( !array($languages) || empty($languages) )
      return false;

    // return language that Qordoba will understand
    return isset($languages[$module_lang]) ? $languages[$module_lang] : false;
  }

  public function send_post($post_id, $languages = false) {
    $source_id = $this->module()->get_source_post_id($post_id);
    $source_post = get_post($source_id);

    if ( !$source_id || !$source_post || is_wp_error($source_post) )
      return false; //TODO: error handling

    $object = new Qordoba_Object($source_post);
    $object->upload();

    // mark post as queued by adding timestamp
    update_post_meta($source_id, '_qor_queued', current_time('timestamp'));
    delete_post_meta($source_id, '_qor_updated');
  }

  public function download_post($post_id, $languages = false, $override = false) {
    $source_id = $this->module()->get_source_post_id($post_id);
    $source_post = get_post($source_id);

    if ( !$source_id || !$source_post || is_wp_error($source_post) )
      return false; //TODO: error handling

    $object = new Qordoba_Object($source_post);
    $translations =  $object->download();
    $new_version = (int) $object->get_version();

    foreach ($translations as $lang => $translation) {
      $lang = $this->map_qordoba_lang($lang);
      $saved_version = (int) get_post_meta($source_id, "_qor_version_$lang", true);

      // check if the language is mapped and translation version is outdated (to prevent overriding actual translation versions
      // unless $override is set to TRUE)
      if ( $lang && ($override || $saved_version < $new_version) ) {
        $translation_id = $this->module()->save_post_translation($source_id, $lang, $translation);

        if ( $translation_id && $new_version ) {
          update_post_meta($source_id, "_qor_version_$lang", $new_version);
        }
      }
    }

    if ($this->translated_versions_match($source_id, 'post')) {
      // if all translations are completed, remove from queue
      delete_post_meta($source_id, '_qor_queued');
      delete_post_meta($source_id, '_qor_updated');
    } else {
      // otherwise move the object to the bottom of queue
      update_post_meta($source_id, '_qor_queued', current_time('timestamp'));
    }

    return $source_id;
  }

  public function send_term($term_id) {
    $source_id = $this->module()->get_source_term_id($term_id);
    $source_term = get_term($source_id);

    if ( !$source_id || !$source_term || is_wp_error($source_term) )
      return false; //TODO: error handling

    $object = new Qordoba_Object($source_term);
    $translations =  $object->upload();

    // mark term as queued by adding timestamp
    update_term_meta($source_id, '_qor_queued', current_time('timestamp'));
    delete_term_meta($source_id, '_qor_updated');
  }

  public function download_term($term_id, $languages = false, $override = false) {
    $source_id = $this->module()->get_source_term_id($term_id);
    $source_term = get_term($source_id);

    if ( !$source_id || !$source_term || is_wp_error($source_term) )
      return false; //TODO: error handling

    $object = new Qordoba_Object($source_term);
    $translations =  $object->download();
    $new_version = (int) $object->get_version();

    foreach ($translations as $lang => $translation) {
      $lang = $this->map_qordoba_lang($lang);
      $saved_version = (int) get_term_meta($source_id, "_qor_version_$lang", true);

      // check if the language is mapped and translation version is outdated (to prevent overriding actual translation versions
      // unless $override is set to TRUE)
      if ( $lang && ($override || $saved_version < $new_version) ) {
        $translation_id = $this->module()->save_term_translation($source_id, $lang, $translation);

        if ( $translation_id && $object->get_version() ) {
          update_term_meta($source_id, "_qor_version_$lang", $object->get_version());
        }
      }
    }

    if ($this->translated_versions_match($source_id, 'term')) {
      // if all translations are completed, remove from queue
      delete_term_meta($source_id, '_qor_queued');
      delete_term_meta($source_id, '_qor_updated');
    } else {
      // otherwise move the object to the bottom of queue
      update_term_meta($source_id, '_qor_queued', current_time('timestamp'));
    }

    return $source_id;
  }

/**
 * @brief Check if versions of all saved translations of post or term match qordoba file version
 *
 * @param int $object_id Post ID or term_id
 * @param string $object_type either post or term
 *
 * @return bool
 */
  public function translated_versions_match($object_id, $object_type = 'post') {
    if ($object_type == 'post')
      $meta = get_post_meta($object_id);
    elseif ('term' == $object_type)
      $meta = get_term_meta($object_id);
    else
      throw new Exception("Wrong object type $object_type, excpected either post or term");

    $versions = array();
    $version = isset($meta['_qor_version']) ? (int) reset($meta['_qor_version']): 0;
    $languages = $this->module()->get_site_languages(false);

    foreach ($languages as $l) {
      $key = sprintf('_qor_version_%s', $l['id']);
      $versions[] = isset($meta[$key])? (int) reset($meta[$key]) : 0;
    }

    return empty($versions) ? false : $version <= array_sum($versions)/count($versions);
  }

  public function doing_automatic_translation() {
    return $this->get_lock() > current_time('timestamp');
  }

  public function get_lock() {
    return (int) get_option('_qor_lock', 0);
  }

  public function cron_job_ready() {
    $cron_period = max(HOUR_IN_SECONDS, qor()->options()->get('cron_schedule'));
    return !$this->doing_automatic_translation() && current_time('timestamp') > $this->get_lock() + $cron_period;
  }

  protected function set_lock($expires) {
    $expires = (int) $expires;

    return update_option('_qor_lock', $expires);
  }

  public function download_pending_translations($max_time = MINUTE_IN_SECONDS, $max_items = 50, $timestamp = false) {
    $safe_extra_time = 5*MINUTE_IN_SECONDS;

    // increase execution time, has no effect if PHP is running in safe mode!
    set_time_limit( $max_time + $safe_extra_time );

    $start = current_time('timestamp');
    $lock_expires = $this->get_lock();

    if ( $start < $lock_expires) {
      throw new Exception( sprintf('Another process has started downloading translations (please wait %d seconds)', $lock_expires - $start) );
    }

    $new_lock_expires = $start + $max_time + $safe_extra_time;
    $this->set_lock($new_lock_expires);

    $post_ids = $this->get_queued_posts(-1, $timestamp);
    $term_ids = $this->get_queued_terms(0, $timestamp);

    $result = array(
      'updated' => 0,
      'total'   => count($post_ids) + count($term_ids),
    );

    foreach ($post_ids as $post_id) {
      if ( current_time('timestamp') > $start + $max_time || $result['updated'] >= $max_items) {
        // time ran off or max items limit exceeded - skip to return
        break;
      } elseif ( $new_lock_expires !== (int) get_option('_qor_lock') ) {
        //something happened and lock was updated outside this function!
        throw new Exception('Lock overridden by another process!');
      }

      try {
        $this->download_post($post_id);
      } catch (Exception $e) {
        $this->set_lock(current_time('timestamp'));
        throw $e;
      }

      $result['updated']++;
    }

    // if there is time, process remaining taxonomies (some of them may be already translated by download_post();
    foreach ($term_ids as $term_id) {
      if ( current_time('timestamp') > $start + $max_time || $result['updated'] >= $max_items) {
        // time ran off or max items limit exceeded - skip to return
        break;
      } elseif ( $new_lock_expires !== (int) get_option('_qor_lock') ) {
        //something happened and lock was updated outside this function!
        throw new Exception('Lock overridden by another process!');
      }

      try {
        $this->download_term($term_id);
      } catch (Exception $e) {
        $this->set_lock(current_time('timestamp'));
        throw $e;
      }

      $result['updated']++;
    }

    // time when current batch was finished
    $this->set_lock(current_time('timestamp'));

    return $result;
  }

  public function send_updated_translations($max_time = MINUTE_IN_SECONDS) {
    $posts = $this->get_updated_posts();
    $terms = $this->get_updated_terms();
    $start = current_time('timestamp');
    $result = array(
      'updated' => 0,
      'total'   => count($posts) + count($terms),
    );

    try {
      foreach($posts as $post_id) {
        if ( current_time('timestamp') > $start + $max_time )
          return $result;

        $this->send_post($post_id);
        $result['updated']++;

      }

      foreach ($terms as $term_id) {
        if ( current_time('timestamp') > $start + $max_time )
          return $result;

        $this->send_term($term_id);
        $result['updated']++;
      }
    } catch (Exception $e) {
      $result['error'] = $e->getMessage();
      return $result;
    }

    return $result;

  }

  public function get_updated_posts($count = -1) {
    $args = array(
      'fields'          => 'ids',
      'post_type'       => $this->module()->translated_post_types,
      'posts_per_page'  => -1,
      'meta_query'      => array(
        'relation'      => 'OR',
        array(
          'key'         => '_qor_version',
          'compare'     => 'NOT EXISTS'
        ),
        array(
          'key'         => '_qor_updated',
          'compare'     => 'EXISTS',
        ),
      ),
    );

    $lang = $this->module()->get_default_language('slug');

    return $this->module()->get_posts_by_lang($lang, $args);
  }

  public function get_updated_terms($number = 0) {
    $args = array(
      'fields'          => 'ids',
      'taxonomy'        => $this->module()->translated_taxonomies,
      'hide_empty'      => false,
      'number'          => $number,
      'meta_query'      => array(
        'relation'      => 'OR',
        array(
          'key'         => '_qor_version',
          'compare'     => 'NOT EXISTS'
        ),
        array(
          'key'         => '_qor_updated',
          'compare'     => 'EXISTS',
        ),
      ),
    );

    $lang = $this->module()->get_default_language('slug');

    return $this->module()->get_terms_by_lang($lang, $args);
  }

  public function get_queued_posts($count = -1, $timestamp = false) {
    $args = array(
      'fields'          => 'ids',
      'post_type'       => $this->module()->translated_post_types,
      'posts_per_page'  => $count,
      'meta_key'        => '_qor_queued',
      'orderby'         => 'meta_value_num',
      'order'           => 'ASC',
    );

    if ($timestamp) {
      $args['meta_value'] = (int) $timestamp;
      $args['meta_compare'] = '<';
    }

    $lang = $this->module()->get_default_language('slug');

    return $this->module()->get_posts_by_lang($lang, $args);
  }

  public function get_queued_terms($number = 0, $timestamp = false) {
    $args = array(
      'taxonomy'        => $this->module()->translated_taxonomies,
      'fields'          => 'ids',
      'number'          => $number,
      'meta_key'        => '_qor_queued',
      'orderby'         => 'meta_value_num',
      'order'           => 'ASC',
    );

    if ($timestamp) {
      $args['meta_value'] = (int) $timestamp;
      $args['meta_compare'] = '<';
    }

    $lang = $this->module()->get_default_language('slug');

    return $this->module()->get_terms_by_lang($lang, $args);
  }

  public function view($template, $variables = array()) {
    if (!$variables) {
      $variables = array();
    }

    extract($variables);

    if ($filename = locate_template( sprintf('qordoba/%s.php', $template), false )) {
      return include($filename);
    }

    return include sprintf('%s/%s.php', QORDOBA_PLUGIN_DIR, $template);
  }

}
