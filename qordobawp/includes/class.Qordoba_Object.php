<?php

class Qordoba_Object {
  protected $document = null;
  protected $loaded = false;
  protected $object = null;
  protected $object_id = null;
  protected $object_type = null;
  protected $version = 0;

  // TODO: get values from configuration
  protected $translated_meta = array();

  public function __construct($object) {
    $this->document = qor()->new_qordoba_document();

    if ($object instanceof WP_Post) {
      $this->translated_meta = apply_filters("qor_translated_{$object->post_type}_meta", qor()->options()->get('post_fields'));
      $this->object_type = 'post';
      $this->object_id = $object->ID;
      $this->document->setName(sprintf('%s-%d', $object->post_type, $this->object_id));

    } elseif ($object instanceof WP_Term) {
      $this->translated_meta = apply_filters("qor_translated_{$object->taxonomy}_meta", qor()->options()->get('term_fields'));
      $this->object_type = 'term';
      $this->object_id = $object->term_id;
      $this->document->setName(sprintf('%s-%d', $object->taxonomy, $this->object_id));

    } else {
      throw new Exception('Invalid $object, should be an instance of either WP_Post or WP_Term');
    }

    if ( !$this->translated_meta || !is_array($this->translated_meta) )
      $this->translated_meta = array();

    $this->object = $object;

    $this->version = (int) $this->get_meta('_qor_version', true);
  }

  public function download($language = null) {
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
    // increment version
    $this->version++;
    $this->document->setTag( (string) $this->version );

    if ('term' == $this->object_type)
      $this->build_term_translation();
    elseif ('post' == $this->object_type)
      $this->build_post_translation();

    $this->add_meta_section();
    $this->document->createTranslation();

    $this->update_meta('_qor_version', $this->version);
  }

  public function build_post_translation() {
    $post_fields = apply_filters("qor_translated_{$this->object->post_type}_fields", array('post_title', 'post_content', 'post_excerpt'));
    $post_fields = array_unique($post_fields);

    foreach ($post_fields as $post_field) {
      $this->document->addTranslationString($post_field, $this->object->{$post_field});
    }
  }

  public function build_term_translation() {
    $term_fields = apply_filters("qor_translated_{$this->object->taxonomy}_fields", array('name', 'description'));
    $term_fields = array_unique($term_fields);

    foreach ($term_fields as $term_field) {
      $this->document->addTranslationString($term_field, $this->object->{$term_field});
    }

  }

  public function add_meta_section() {
    $section_meta = $this->document->addSection('custom_fields');
    // discard meta fields which keys are not present in translated_meta
    $meta = $this->get_meta();

    if (!$meta)
      return;

    $meta = array_intersect_key($meta, array_flip($this->translated_meta) );

    foreach ($meta as $key => $values) {
      foreach ($values as $i => $value) {
        $string_name = sprintf('%s_%d', $key, $i);
        $section_meta->addTranslationString($string_name, $value);
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




