<?php

class Qordoba_Object {
  protected $document = null;
  protected $loaded = false;
  protected $object = null;
  protected $object_id = null;
  protected $object_type = null;
  protected $object_name = null;
  protected $sent_documents = array();
  protected $version = 0;

  // TODO: get values from configuration
  protected $translated_meta = array();

  public function __construct($object) {

    if ($object instanceof WP_Post) {
      $this->translated_meta = apply_filters("qor_translated_{$object->post_type}_meta", qor()->options()->get('post_fields'));
      $this->object_type = 'post';
      $this->object_name = $object->post_type;
      $this->object_id = $object->ID;

    } elseif ($object instanceof WP_Term) {
      $this->translated_meta = apply_filters("qor_translated_{$object->taxonomy}_meta", qor()->options()->get('term_fields'));
      $this->object_type = 'term';
      $this->object_name = $object->taxonomy;
      $this->object_id = $object->term_id;

    } else {
      throw new Exception('Invalid $object, should be an instance of either WP_Post or WP_Term');
    }

    if ( !$this->translated_meta || !is_array($this->translated_meta) )
      $this->translated_meta = array();

    $this->object = $object;

    $this->version = (int) $this->get_meta('_qor_version', true);
  }

  public function download_bak($language = null) {
    $this->document->setTag($this->version);
    $translations = $this->document->fetchTranslation($language);

    $result = array();


    foreach ($translations as $lang => $translation) {
      $result[$lang] = array();

      foreach ($translation as $section => $content) {
        if ('custom_fields' == $section)
          $content = $this->parse_custom_fields($content);

        $result[$lang][$section] = is_object($content) ? (array)$content : $content;
      }
    }

    return $result;
  }

  public function download($language = null) {
    $result = array();

    $sent_documents = $this->get_meta('_qor_sent_docs', true);
    $fields = $this->get_fields();

    foreach ($fields as $field) {
      $document_name = $this->format_object_field($field);

      if ( in_array($document_name, $sent_documents) ) {
        $translations = $this->download_document($document_name);

        foreach ($translations as $lang => $translation) {
          if ( !isset($result[$lang]) ) {
            $result[$lang] = array();
          }

          $result[$lang][$field] = trim(wp_kses($translation, wp_kses_allowed_html('post')));
        }

      }
    }

    $this->download_custom_fields($result);

    return $result;
  }

  public function get_version() {
    return $this->version;
  }

  protected function parse_custom_fields($custom_fields) {
    $result = array();

    foreach ($custom_fields as $name => $value) {
      if ( 1 === preg_match('/(.*)_(\d+)$/', $name, $matches) ) {
        $meta_key = $matches[1];
        $i = $matches[2];

        if (!isset($result[$meta_key]))
          $result[$meta_key] = array();

        $result[$meta_key][$i] = $value;
      }

    }

    return $result;
  }

  public function upload() {
    $this->version++;
    $this->update_meta('_qor_version', $this->version);

    $fields = $this->get_fields();
    $this->sent_documents = array();

    foreach ($fields as $field) {
      $name = $this->format_object_field($field);

      if ( isset($this->object->{$field}) && !empty($this->object->{$field}) ) {
        $this->send_document($name, $this->object->{$field});
      }
    }

    $this->send_custom_fields();

    // remember which files were sent
    $this->update_meta('_qor_sent_docs', $this->sent_documents);
  }

  protected function format_object_field($field) {
    $field = sprintf('%s-%d-%s',
          $this->object_name,
          $this->object_id,
          $field
      );

    return $this->sanitize_name($field);
  }

  protected function format_custom_field($field, $index) {
    $field = sprintf('%s-%d_custom-field-%s-%d',
          $this->object_name,
          $this->object_id,
          $field,
          $index
      );

    return $this->sanitize_name($field);
  }

  protected function get_fields() {
    $fields = array();

    if ('post' === $this->object_type) {
      $fields = apply_filters("qor_translated_{$this->object->post_type}_fields", array('post_title', 'post_content', 'post_excerpt'));
      $fields = array_unique($fields);
    } elseif ('term' == $this->object_type) {
      $fields = apply_filters("qor_translated_{$this->object->taxonomy}_fields", array('name', 'description'));
      $fields = array_unique($fields);
    }

    return $fields;
  }

  protected function sanitize_name($name) {
    return preg_replace('/[^a-zA-Z0-9]/', '-', $name);
  }

  protected function send_document($name, $content) {
    $name = $this->sanitize_name($name);

    if ( empty($content) ){
      return;
    }

    $document = qor()->new_qordoba_document('html');
    $document->setName($name);
    $document->setTag( (string) $this->version );

    $document->addTranslationContent($content);

    // send
    $document->createTranslation();

    $this->sent_documents[] = $name;
  }

  protected function download_document($name, $language = null) {
    $name = $this->sanitize_name($name);

    $document = qor()->new_qordoba_document('html');
    $document->setName($name);
    $document->setTag( (string) $this->version );

    return $document->fetchTranslation();
  }

  protected function send_custom_fields() {
    $meta = $this->get_meta();

    if (!$meta)
      return;

    // discard meta fields which keys are not present in translated_meta
    $meta = array_intersect_key($meta, array_flip($this->translated_meta) );

    foreach ($meta as $key => $values) {
      foreach ($values as $i => $value) {
        $name = $this->format_custom_field($key, $i);
        $this->send_document($name, $value);
      }
    }
  }

  protected function download_custom_fields(&$result) {
    $meta = $this->get_meta();

    if (!$meta && empty($result))
      return array();

    // discard meta fields which keys are not present in translated_meta
    $meta = array_intersect_key($meta, array_flip($this->translated_meta) );
    $sent_documents = $this->get_meta('_qor_sent_docs', true);

    foreach ($meta as $key => $values) {

      // generate array of empty values of this field in all languages (to preserve field keys)
      foreach ($result as $lang => $translated_object) {
        $result[$lang]['custom_fields'][$key] = array_fill(0, count($values), null);
      }

      foreach ($values as $i => $value) {
        $name = $this->format_custom_field($key, $i);

        if ( in_array($name, $sent_documents) ) {
          $field_translations = $this->download_document($name);

          foreach ($field_translations as $lang => $field_translation) {
            if ( isset($result[$lang]) ) {
              $result[$lang]['custom_fields'][$key][$i] = trim(wp_kses($field_translation, wp_kses_allowed_html('post')));
            }
          }
        }
      }

    }
  }

  public function get_meta($key = false, $single = false) {
    // get_post_meta or get_term_meta
    return call_user_func("get_{$this->object_type}_meta", $this->object_id, $key, $single);
  }

  public function update_meta($key, $value, $prev_value = '') {
    return call_user_func("update_{$this->object_type}_meta", $this->object_id, $key, $value, $prev_value);
  }

  public function delete_meta($key, $value = '') {
    return call_user_func("delete_{$this->object_type}_meta", $this->object_id, $key, $value);
  }
}




