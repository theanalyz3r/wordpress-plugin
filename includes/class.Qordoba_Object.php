<?php

/**
 * Class Qordoba_Object
 */
class Qordoba_Object
{
    /**
     * @var null
     */
    protected $document = null;
    /**
     * @var bool
     */
    protected $loaded = false;
    /**
     * @var null|WP_Post|WP_Term
     */
    protected $object = null;
    /**
     * @var int|null
     */
    protected $object_id = null;
    /**
     * @var null|string
     */
    protected $object_type = null;
    /**
     * @var null|string
     */
    protected $object_name = null;
    /**
     * @var array
     */
    protected $sent_documents = array();
    /**
     * @var int
     */
    protected $version = 0;
    /**
     * @var array
     */
    protected $meta = array();

    // TODO: get values from configuration
    /**
     * @var array
     */
    protected $translated_meta = array();

    /**
     * Qordoba_Object constructor.
     * @param $object
     * @param array $additionalMeta
     * @throws Exception
     */
    public function __construct($object, $additionalMeta = array())
    {

        if ($object instanceof WP_Post) {
            $this->translated_meta = apply_filters("qor_translated_{$object->post_type}_meta", qor()->options()->get('post_fields'));
            $this->object_type = 'post';
            $this->object_name = $object->post_type;
            $this->object_id = $object->ID;
            $this->meta = $additionalMeta;
        } elseif ($object instanceof WP_Term) {
            $this->translated_meta = apply_filters("qor_translated_{$object->taxonomy}_meta", qor()->options()->get('term_fields'));
            $this->object_type = 'term';
            $this->object_name = $object->taxonomy;
            $this->object_id = $object->term_id;
            $this->meta = $additionalMeta;
        } else {
            throw new Exception('Invalid $object, should be an instance of either WP_Post or WP_Term');
        }

        if (!$this->translated_meta || !is_array($this->translated_meta))
            $this->translated_meta = array();

        $this->object = $object;

        $this->version = (int)$this->get_meta('_qor_version', true);
    }

    /**
     * @param null $language
     * @return array
     */
    public function download_bak($language = null)
    {
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

    /**
     * @param null $language
     * @return array
     * @throws Exception
     */
    public function download($language = null)
    {
        $result = array();

        $sent_documents = $this->get_meta('_qor_sent_docs', true);
        $fields = $this->get_fields();
        $elementorData = $this->download_document($this->format_custom_field('elementor', $this->version), null, 'json');
        foreach ($fields as $field) {
            $document_name = $this->format_object_field($field);
            if (in_array($document_name, $sent_documents)) {
                $translations = $this->download_document($document_name);
                foreach ($translations as $lang => $translation) {
                    if (!isset($result[$lang])) {
                        $result[$lang] = array();
                    }
                    $result[$lang][$field] = trim(wp_kses($translation, wp_kses_allowed_html('post')));
                }

            }
        }
        if ($elementorData && 0 < count($elementorData)) {
            foreach ($elementorData as $lang => $translation) {
                if (!isset($result[$lang])) {
                    $result[$lang] = array();
                }
                $result[$lang]['elementor'] = $translation;
            }
        }
        $this->download_custom_fields($result);
        return $result;
    }

    /**
     * @return int
     */
    public function get_version()
    {
        return $this->version;
    }

    /**
     * @param $custom_fields
     * @return array
     */
    protected function parse_custom_fields($custom_fields)
    {
        $result = array();

        foreach ($custom_fields as $name => $value) {
            if (1 === preg_match('/(.*)_(\d+)$/', $name, $matches)) {
                $meta_key = $matches[1];
                $i = $matches[2];

                if (!isset($result[$meta_key]))
                    $result[$meta_key] = array();

                $result[$meta_key][$i] = $value;
            }

        }

        return $result;
    }

    /**
     *
     */
    public function upload()
    {
        $this->version++;
        $this->update_meta('_qor_version', $this->version);
        $format = (0 < count($this->meta)) ? 'json' : 'html';

        $fields = $this->get_fields();
        $this->sent_documents = array();
        foreach ($fields as $field) {
            $name = $this->format_object_field($field);
            if (isset($this->object->{$field}) && !empty($this->object->{$field})) {
                $this->send_document($name, $this->object->{$field});
            }
        }
        $this->send_custom_fields($format);
        $this->update_meta('_qor_sent_docs', $this->sent_documents);
    }

    /**
     * @param $field
     * @return mixed
     */
    protected function format_object_field($field)
    {
        $field = sprintf('%s-%d-%s',
            $this->object_name,
            $this->object_id,
            $field
        );

        return $this->sanitize_name($field);
    }

    /**
     * @param $field
     * @param $index
     * @return mixed
     */
    protected function format_custom_field($field, $index)
    {
        $field = sprintf('%s-%d_custom-field-%s-%d',
            $this->object_name,
            $this->object_id,
            $field,
            $index
        );

        return $this->sanitize_name($field);
    }

    /**
     * @return array|mixed|void
     */
    protected function get_fields()
    {
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

    /**
     * @param $name
     * @return mixed
     */
    protected function sanitize_name($name)
    {
        return preg_replace('/[^a-zA-Z0-9]/', '-', $name);
    }

    /**
     * @param $name
     * @param $content
     * @param string $type
     * @throws Exception
     * @throws \Qordoba\Exception\DocumentException
     */
    protected function send_document($name, $content, $type = 'html')
    {
        $name = $this->sanitize_name($name);

        if (empty($content)) {
            return;
        }

        $document = qor()->new_qordoba_document($type);
        $document->setName($name);
        $document->setTag((string)$this->version);

        if ('json' === $type) {
            foreach ($this->meta as $key => $item) {
                $document->addTranslationString($key, $item);
            }
        } else {
            $document->addTranslationContent($content);
        }
        // send
        $document->createTranslation();

        $this->sent_documents[] = $name;
    }

    /**
     * @param $name
     * @param null $language
     * @param string $type
     * @return array
     * @throws Exception
     */
    protected function download_document($name, $language = null, $type = 'html')
    {
        $name = $this->sanitize_name($name);
        $document = qor()->new_qordoba_document($type);
        $document->setName($name);
        $document->setTag((string)$this->version);

        return $document->fetchTranslation();
    }

    /**
     * @param string $type
     * @throws Exception
     * @throws \Qordoba\Exception\DocumentException
     */
    protected function send_custom_fields($type = 'html')
    {
        if (0 < count($this->meta)) {
            $this->send_document($this->format_custom_field('elementor', $this->version), $this->meta, 'json');
        } else {
            $meta = $this->get_meta();

            if (!$meta)
                return;

            // discard meta fields which keys are not present in translated_meta
            $meta = array_intersect_key($meta, array_flip($this->translated_meta));

            foreach ($meta as $key => $values) {
                foreach ($values as $i => $value) {
                    $name = $this->format_custom_field($key, $i);
                    $this->send_document($name, $value, $type);
                }
            }
        }

    }

    /**
     * @param $result
     * @return array
     * @throws Exception
     */
    protected function download_custom_fields(&$result)
    {
        $meta = $this->get_meta();

        if (!$meta && empty($result)) {
            return array();
        }

        $meta = array_intersect_key($meta, array_flip($this->translated_meta));
        $sent_documents = $this->get_meta('_qor_sent_docs', true);

        foreach ($meta as $key => $values) {

            // generate array of empty values of this field in all languages (to preserve field keys)
            foreach ($result as $lang => $translated_object) {
                $result[$lang]['custom_fields'][$key] = array_fill(0, count($values), null);
            }

            foreach ($values as $i => $value) {
                $name = $this->format_custom_field($key, $i);

                if (in_array($name, $sent_documents)) {
                    $field_translations = $this->download_document($name);

                    foreach ($field_translations as $lang => $field_translation) {
                        if (isset($result[$lang])) {
                            $result[$lang]['custom_fields'][$key][$i] = trim(wp_kses($field_translation, wp_kses_allowed_html('post')));
                        }
                    }
                }
            }

        }
    }

    /**
     * @param bool $key
     * @param bool $single
     * @return mixed
     */
    public function get_meta($key = false, $single = false)
    {
        return call_user_func("get_{$this->object_type}_meta", $this->object_id, $key, $single);
    }

    /**
     * @param $key
     * @param $value
     * @param string $prev_value
     * @return mixed
     */
    public function update_meta($key, $value, $prev_value = '')
    {
        return call_user_func("update_{$this->object_type}_meta", $this->object_id, $key, $value, $prev_value);
    }

    /**
     * @param $key
     * @param string $value
     * @return mixed
     */
    public function delete_meta($key, $value = '')
    {
        return call_user_func("delete_{$this->object_type}_meta", $this->object_id, $key, $value);
    }
}




