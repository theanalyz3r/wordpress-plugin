<?php

/**
 * Class Qordoba_Module_Default
 */
class Qordoba_Module_Default extends Qordoba_Module
{

    /**
     * Qordoba_Module_Default constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->translated_post_types = array('post', 'page');
        $this->translated_taxonomies = array('category', 'post_tag');
    }

    /**
     * @param $post_id
     * @param $lang
     * @param $translation
     */
    public function save_post_translation($post_id, $lang, $translation)
    {
        return; // nothing to do here
    }

    /**
     * @param ID $translation_id
     * @return ID
     */
    public function get_source_post_id($translation_id)
    {
        return $translation_id;
    }

    /**
     * @param $post_id
     * @param $lang
     * @return array|null|WP_Post
     */
    public function get_post_translation($post_id, $lang)
    {
        return get_post($post_id);
    }

    /**
     * @param $translation_id
     * @return mixed
     */
    public function get_source_term_id($translation_id)
    {
        return $translation_id;
    }

    /**
     * @param $term_id
     * @param $lang
     * @param $translation
     */
    public function save_term_translation($term_id, $lang, $translation)
    {

    }

    /**
     * @param $post_id
     * @return array
     */
    public function get_post_languages($post_id)
    {
        return array($this->get_default_language());
    }

    /**
     * @return string
     */
    public function get_default_language()
    {
        return get_locale();
    }

    /**
     * @param bool $include_default
     * @return array|null
     */
    public function get_site_languages($include_default = true)
    {
        if (null === $this->site_languages) {
            $this->site_languages = array(
                'en' => array(
                    'id' => 'en',
                    'locale' => 'en_US',
                    'name' => 'English',
                ),
            );
        }

        return $this->site_languages;
    }

    /**
     * @return bool
     */
    public static function plugin_enabled()
    {
        return true;
    }

    /**
     * @param $post
     */
    public function qordoba_metabox_post($post)
    {
        printf('<p class="">%s</p>', __('Please install one of Wordpress Multilingual plugins to start translating content with Qordoba. Supported plugins are:', 'qordoba'));
        print '<ol><li>' . implode('</li><li>', array_keys(qor()->get_modules())) . '</li></ol>';
    }
}
