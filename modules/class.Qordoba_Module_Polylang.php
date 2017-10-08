<?php

class Qordoba_Module_Polylang extends Qordoba_Module {

  public function __construct() {
    // get list of posts/taxonomies, which translations are managed by Polylang
    $this->translated_post_types = PLL()->model->get_translated_post_types(true);
    $this->translated_taxonomies = PLL()->model->get_translated_taxonomies(true);

    add_action('pll_save_strings_translations', array($this, 'send_strings'));
    add_action('wp_ajax_qordoba_pll_download_strings', array($this, 'qordoba_pll_download_strings'));
    add_action('qordoba_after_options_extra', array($this, 'options_extra_content'));

    parent::__construct();
  }

  public function save_post_translation($post_id, $lang, $translation) {
    $post_id = (int) $post_id;

    // never override post in project language (source). should never happen though
    if ( $lang == $this->project_lang )
      return false;

    // get the main post in project language
    $source_id = $this->get_source_post_id($post_id);
    $source = get_post($source_id);

    // can't translate post if there is no source
    // this could happen if site's default language is not set
    if ( !$source_id || !$source || is_wp_error($source) ) {
      return false;
    }

    $defaults = array(
      'post_title' => $source->post_title,
      'post_content' => '',
      'post_excerpt' => '',
      'post_type' => 'post',
      'post_status' => 'pending',
    );

    $translation = wp_parse_args($translation, $defaults);

    $tr_post = array(
      'ID'                      => null,
      'post_title'              => $translation['post_title'],
      'post_content'            => $translation['post_content'],
      'post_excerpt'            => $translation['post_excerpt'],
      'post_type'               => $source->post_type,
    );

    // translated post already exists, update it
    if ( $tr_post_id = PLL()->model->post->get($source_id, $lang) ) {
      $tr_post['ID'] = $tr_post_id;

      // save translated meta data
      if ( isset($translation['custom_fields']) )
        $this->save_translated_meta($tr_post_id, $translation['custom_fields']);

      wp_update_post($tr_post);

      PLL()->sync->copy_taxonomies($source_id, $tr_post_id, $lang);

    // insert a new post
    } else {
      // TODO: translate parent
      if ( $source->post_parent && $tr_parent = PLL()->model->post->get_translation($source->post_parent, $lang) ) {
        $tr_post['post_parent'] = $tr_parent;
      }

      if ('attachment' == $source->post_type) {
        $tr_post['post_mime_type'] = $source->post_mime_type;
        $tr_post['post_status'] = 'inherit';
        $tr_post_id = wp_insert_attachment($tr_post);
        add_post_meta($tr_post_id, '_wp_attached_file', get_post_meta($source_id, '_wp_attached_file', true));
        add_post_meta($tr_post_id, '_wp_attachment_metadata', get_post_meta($source_id, '_wp_attachment_metadata', true));
      } else {
        $tr_post['post_status'] = 'pending';
        $tr_post_id = wp_insert_post($tr_post);
      }

      if ($tr_post_id) {
        // set language of the post
        PLL()->model->post->set_language($tr_post_id, $lang);

        // get existing translations of source post
        $source_translations = PLL()->model->post->get_translations($source_id);

        // add new translation
        $source_translations[$lang] = $tr_post_id;

        // save updated translations reference
        PLL()->model->post->save_translations($source_id, $source_translations);

        // copy taxonomies from source language
        PLL()->sync->copy_taxonomies($source_id, $tr_post_id, $lang);

        // copy meta fields
        PLL()->sync->copy_post_metas($source_id, $tr_post_id, $lang);

        // save translated meta data
        if ( isset($translation['custom_fields']) )
          $this->save_translated_meta($tr_post_id, $translation['custom_fields']);
      }
    }

    if ( $tr_post_id && class_exists('PLL_Share_Post_Slug') ) {
      wp_update_post(array(
        'ID' => $tr_post_id,
        'post_name' => $source->post_name,
      ));
    }

    return $tr_post_id;
  }

  public function get_source_post_id($translation_id) {
    return PLL()->model->post->get($translation_id, $this->project_lang);
  }

  public function get_source_term_id($translation_id) {
    return PLL()->model->term->get($translation_id, $this->project_lang);
  }

  public function get_post_translation($post_id, $lang) {
  }

  public function save_term_translation($term_id, $lang, $translation) {
    $term_id = (int) $term_id;

    // never override term in project language (source). should never happen though
    if ( $lang == $this->project_lang )
      return false;

    // get the main post in project language
    $source_id = $this->get_source_term_id($term_id);
    $source = get_term($source_id);

    // can't translate term if there is no source
    // this could happen if site's default language is not set
    if ( !$source_id || !$source || is_wp_error($source) ) {
      return false;
    }

    $tr_term = array(
      'name' => !empty($translation['name']) ? $translation['name'] : $source->name,
      'description' => !empty($translation['description'])? $translation['description'] : $source->description,
    );

    // translation exists
    if ( $tr_term_id = PLL()->model->term->get($source_id, $lang) ) {
      // save translated meta data
      if ( isset($translation['custom_fields']) )
        $this->save_translated_meta($tr_term_id, $translation['custom_fields'], 'term');

      // update term
      wp_update_term($tr_term_id, $source->taxonomy, $tr_term);
    } else {
      if ( $source->parent && $tr_parent = PLL()->model->term->get_translation($source->parent, $lang) ) {
        $tr_term['parent'] = $tr_parent;
      }

      $tr_term['slug'] = sanitize_title($tr_term['name']);

      // if the term with given slug exists, append language to make it unique
      if ( term_exists($tr_term['slug'], $source->taxonomy) ) {
        $tr_term['slug'] .= "-$lang";
      }

      $i = 0;
      $slug = $tr_term['slug'];

      // try to avoid further possible slug collisions, give up at some point which shouldn't ever happen
      while ( term_exists($tr_term['slug'], $source->taxonomy) && $i < 100) {
        $i++;
        $tr_term['slug'] = $slug . "-$i";
      }

      $tr_term_object = wp_insert_term($tr_term['name'], $source->taxonomy, $tr_term);

      if (!is_wp_error($tr_term_object)) {
        $tr_term_id = $tr_term_object['term_id'];

        // set language of the term
        PLL()->model->term->set_language($tr_term_object['term_id'], $lang);

        // get existing translations of source post
        $source_translations = PLL()->model->term->get_translations($source_id);

        // add new translation
        $source_translations[$lang] = $tr_term_id;

        // save updated translations reference
        PLL()->model->term->save_translations($source_id, $source_translations);
      } else {
        throw new Exception($tr_term_object->get_error_message());
      }
    }

    return $tr_term_id;
  }

  public function get_post_languages($post_id) {

  }

  // e.g. slug "en" or locale "en_US"
  public function get_default_language($field = 'slug') {
    $language = PLL()->model->get_language($this->project_lang);

    if ($language && property_exists($language, $field)) {
      return $language->{$field};
    } else {
      return false;
    }
    //return pll_default_language($field);
  }

  public function get_site_languages($include_project_lang = true) {
    if (null === $this->site_languages) {
      $slugs =  pll_languages_list(array('hide_empty' => 0, 'fields' => 'slug'));
      $names = pll_languages_list(array('hide_empty' => 0, 'fields' => 'name'));
      $locales =  pll_languages_list(array('hide_empty' => 0, 'fields' => 'locale'));

      $this->get_site_languages = array();

      foreach ($slugs as $i => $slug) {
        if (!$include_project_lang && $slug == $this->project_lang) {
          continue;
        }

        $this->site_languages[$slug] = array(
          'id'            => $slug,
          'name'          => isset($names[$i])? $names[$i] : $slug,
          'locale'        => isset($locales[$i])? $locales[$i] : $slug,
        );
      }
    }

    return $this->site_languages;
  }

  # bool
  public static function plugin_enabled() {
    return function_exists('pll_current_language');
  }

  public function get_menu_parent_slug() {
    return 'mlang';
  }

  /**
   * Saves translated meta data
   *
   * @param int $tr_id ID of translation in the appropriate language
   * @param array $meta array of meta data where meta keys are indexes:
   *   'meta_key' => array('value 1', 'value2')
   */
  public function save_translated_meta($tr_id, $meta, $object_type = 'post') {
    if ( empty($meta) || !is_array($meta))
      return; // nothing to do :(

    foreach ($meta as $key => $values) {
      if ( empty($key) )
        continue;

      if ( !is_array($values) )
        $values = array($values);

      // it would be easier to delete key first if we need to handle multiple values of a field
      if ('post' == $object_type) {
        $old_fields = get_post_meta($tr_id, $key);
        delete_post_meta($tr_id, $key);
      } elseif ('term' == $object_type) {
        $old_fields = get_term_meta($tr_id, $key);
        delete_term_meta($tr_id, $key);
      }

      foreach ($values as $index => $value) {
        if ($value === NULL) {
          // if value was not translated, try using existing value
          $value = isset($old_fields[$key]) && isset($old_fields[$key][$i]) ? $old_fields[$key][$i] : '';
        }

        if ('post' == $object_type) {
          add_post_meta($tr_id, $key, $value);
        } elseif ('term' == $object_type) {
          add_term_meta($tr_id, $key, $value);
        }
      }
    }
  }

  public function get_strings_version() {
    return (int) get_option('qor_strings_version');
  }

  public function send_strings($force = true) {
    $strings = PLL_Admin_Strings::get_strings();
    //$hash = hash('sha1', serialize($strings));

    $version = $this->get_strings_version();
    $version++;

    $document = qor()->new_qordoba_document('json');
    $document->setName('site-strings');
    $document->setTag((string) $version);
    $sections = array_fill_keys( wp_list_pluck($strings, 'context'), null );

    foreach ($sections as $context => $section)
      $sections[$context] = $document->addSection($context);

    foreach ($strings as $id => $string) {
      $sections[$string['context']]->addTranslationString($id, $string['string']);
    }

    $document->createTranslation();

    update_option('qor_strings_version', $version);

  }

  public function qordoba_pll_download_strings() {
    if ( !check_ajax_referer('qordoba_send_bulk', 'qor_nonce', false) || !current_user_can('manage_options') ) {
      wp_send_json(array(
        'error' => true,
        'errorMessage' => __('Access denied or your session has expired.')
      ));

      wp_die(); //just in case
    }

    try {
      $updated_languages = $this->download_string_translations();
    } catch (Exception $e) {
      wp_send_json(array(
        'error' => true,
        'errorMessage' => $e->getMessage(),
      ));
    }

    wp_send_json(
      array(
        'error' => false,
        'languages' => $updated_languages,
        'updated' => count($updated_languages),
        'total' => 0,
      )
    );
  }

  public function download_string_translations() {
    $updated_languages = array();

    // Polylang registered strings
    $strings = PLL_Admin_Strings::get_strings();

    // latest version sent to Qordoba for translation
    $version = $this->get_strings_version();

    // create new document
    $document = qor()->new_qordoba_document('json');
    $document->setName('site-strings');
    $document->setTag((string) $version);

    // fetch translations
    $languages = $document->fetchTranslation();

    foreach ($languages as $language => $context) {
      // skip if language is not mapped
      if (!$lang = qor()->map_qordoba_lang($language)) {
        continue;
      }

      // get PLL language object
      $lang = PLL()->model->get_language($lang);

      $saved_translations = array();

      // Import PLL strings from DB for in a specific language
      $mo = new PLL_MO();
      $mo->import_from_db( $lang );

      foreach($context as $translations) {
        foreach($translations as $key => $translation) {

          // if the string is still registered, update it's translation
          if ( isset($strings[$key]) ) {
            $mo->add_entry( $mo->make_entry( $strings[$key]['string'], $translation ) );
            $updated_languages[$lang->slug] = $lang->name;
          }

        }
      }

      // save translated string per a language
      $mo->export_to_db($lang);
    }

    return $updated_languages;
  }

  public function get_posts_by_lang($lang, $extra_args = array()) {
    $extra_args['lang'] = $lang;
    return parent::get_posts_by_lang($lang, $extra_args);
  }

  public function get_terms_by_lang($lang, $extra_args = array()) {
    $extra_args['lang'] = $lang;
    return parent::get_terms_by_lang($lang, $extra_args);
  }

  public function options_extra_content() {
    $vars = array(
      'version' => $this->get_strings_version(),
    );

    qor()->view('views/modules/polylang/options-extra', $vars);
  }
}
