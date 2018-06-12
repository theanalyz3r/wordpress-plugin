<?php
/**
 *
 * PHP version 5 and 7
 *
 * @author Qordoba Team <support@qordoba.com>
 * @copyright 2018 Qordoba Team
 *
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class Qordoba_Custom_Fields_Table
 */
class Qordoba_Custom_Fields_Table extends WP_List_Table {

	/**
	 * @const int
	 */
	const ITEMS_PER_PAGE = 50;
	/**
	 * @const string
	 */
	const SEARCH_IDENTIFIER = 's';

	/**
	 * @var array
	 */
	protected static $post_custom_fields = array();

	/**
	 * Qordoba_Custom_Fields_Table constructor.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Customer', 'qordoba' ),
			'plural'   => __( 'Customers', 'qordoba' ),
			'ajax'     => false,
		) );
	}

	/**
	 * @param string $glue
	 *
	 * @return string
	 */
	protected static function include_custom_fields_list( $glue = ',' ) {
		$list = [
			'_hero_title',
			'_hero_type',
			'_product',
			'_product_0_benefit_call_to_actions',
			'_product_0_benefit_call_to_actions_0_cta_description',
			'_product_0_benefit_call_to_actions_0_cta_icon',
			'_product_0_benefit_call_to_actions_0_cta_title',
			'_product_0_benefit_call_to_actions_1_cta_description',
			'_product_0_benefit_call_to_actions_1_cta_icon',
			'_product_0_benefit_call_to_actions_1_cta_title',
			'_product_0_benefit_call_to_actions_2_cta_description',
			'_product_0_benefit_call_to_actions_2_cta_icon',
			'_product_0_benefit_call_to_actions_2_cta_title',
			'_product_0_benefit_call_to_actions_3_cta_description',
			'_product_0_benefit_call_to_actions_3_cta_icon',
			'_product_0_benefit_call_to_actions_3_cta_title',
			'_product_0_benefit_call_to_actions_4_cta_description',
			'_product_0_benefit_call_to_actions_4_cta_icon',
			'_product_0_benefit_call_to_actions_4_cta_title',
			'_product_0_benefit_call_to_actions_5_cta_description',
			'_product_0_benefit_call_to_actions_5_cta_icon',
			'_product_0_benefit_call_to_actions_5_cta_title',
			'_product_0_benefits_background_color',
			'_product_0_benefits_button_label',
			'_product_0_benefits_heading',
			'_product_0_benefits_introduction',
			'_product_0_benefits_link',
			'_product_0_button_label',
			'_product_0_column_1',
			'_product_0_column_1_1',
			'_product_0_column_2',
			'_product_0_description',
			'_product_0_featured_resources',
			'_product_0_heading',
			'_product_0_link',
			'_product_0_resources',
			'_product_0_sub_heading',
			'_product_0_title',
			'_product_1_benefit_call_to_actions',
			'_product_1_benefit_call_to_actions_0_cta_description',
			'_product_1_benefit_call_to_actions_0_cta_icon',
			'_product_1_benefit_call_to_actions_0_cta_title',
			'_product_1_benefit_call_to_actions_1_cta_description',
			'_product_1_benefit_call_to_actions_1_cta_icon',
			'_product_1_benefit_call_to_actions_1_cta_title',
			'_product_1_benefit_call_to_actions_2_cta_description',
			'_product_1_benefit_call_to_actions_2_cta_icon',
			'_product_1_benefit_call_to_actions_2_cta_title',
			'_product_1_benefit_call_to_actions_3_cta_description',
			'_product_1_benefit_call_to_actions_3_cta_icon',
			'_product_1_benefit_call_to_actions_3_cta_title',
			'_product_1_benefits_background_color',
			'_product_1_benefits_button_label',
			'_product_1_benefits_heading',
			'_product_1_benefits_introduction',
			'_product_1_benefits_link',
			'_product_1_button_label',
			'_product_1_column_1_1',
			'_product_1_description',
			'_product_1_featured_resources',
			'_product_1_heading',
			'_product_1_link',
			'_product_1_product_feature_alignment',
			'_product_1_product_feature_background_color',
			'_product_1_product_feature_button_label',
			'_product_1_product_feature_description',
			'_product_1_product_feature_heading',
			'_product_1_product_feature_height',
			'_product_1_product_feature_image',
			'_product_1_product_feature_image_position',
			'_product_1_product_feature_link',
			'_product_1_product_feature_sub_heading',
			'_product_1_sub_heading',
			'_product_1_title',
			'_product_2_benefit_call_to_actions',
			'_product_2_benefit_call_to_actions_0_cta_description',
			'_product_2_benefit_call_to_actions_0_cta_icon',
			'_product_2_benefit_call_to_actions_0_cta_title',
			'_product_2_benefit_call_to_actions_1_cta_description',
			'_product_2_benefit_call_to_actions_1_cta_icon',
			'_product_2_benefit_call_to_actions_1_cta_title',
			'_product_2_benefit_call_to_actions_2_cta_description',
			'_product_2_benefit_call_to_actions_2_cta_icon',
			'_product_2_benefit_call_to_actions_2_cta_title',
			'_product_2_benefit_call_to_actions_3_cta_description',
			'_product_2_benefit_call_to_actions_3_cta_icon',
			'_product_2_benefit_call_to_actions_3_cta_title',
			'_product_2_benefits_background_color',
			'_product_2_benefits_button_label',
			'_product_2_benefits_heading',
			'_product_2_benefits_introduction',
			'_product_2_benefits_link',
			'_product_2_button_label',
			'_product_2_column_1_1',
			'_product_2_description',
			'_product_2_featured_resources',
			'_product_2_heading',
			'_product_2_link',
			'_product_2_product_feature_alignment',
			'_product_2_product_feature_background_color',
			'_product_2_product_feature_button_label',
			'_product_2_product_feature_description',
			'_product_2_product_feature_heading',
			'_product_2_product_feature_height',
			'_product_2_product_feature_image',
			'_product_2_product_feature_image_position',
			'_product_2_product_feature_link',
			'_product_2_product_feature_sub_heading',
			'_product_2_sub_heading',
			'_product_2_title',
			'_product_3_benefit_call_to_actions',
			'_product_3_benefit_call_to_actions_0_cta_description',
			'_product_3_benefit_call_to_actions_0_cta_icon',
			'_product_3_benefit_call_to_actions_0_cta_title',
			'_product_3_benefit_call_to_actions_1_cta_description',
			'_product_3_benefit_call_to_actions_1_cta_icon',
			'_product_3_benefit_call_to_actions_1_cta_title',
			'_product_3_benefit_call_to_actions_2_cta_description',
			'_product_3_benefit_call_to_actions_2_cta_icon',
			'_product_3_benefit_call_to_actions_2_cta_title',
			'_product_3_benefits_background_color',
			'_product_3_benefits_button_label',
			'_product_3_benefits_heading',
			'_product_3_benefits_introduction',
			'_product_3_benefits_link',
			'_product_3_button_label',
			'_product_3_column_1_1',
			'_product_3_description',
			'_product_3_featured_resources',
			'_product_3_gallery',
			'_product_3_heading',
			'_product_3_job_board_description',
			'_product_3_job_board_title',
			'_product_3_link',
			'_product_3_product_feature_alignment',
			'_product_3_product_feature_background_color',
			'_product_3_product_feature_button_label',
			'_product_3_product_feature_description',
			'_product_3_product_feature_heading',
			'_product_3_product_feature_height',
			'_product_3_product_feature_image',
			'_product_3_product_feature_image_position',
			'_product_3_product_feature_link',
			'_product_3_product_feature_sub_heading',
			'_product_3_sub_heading',
			'_product_3_title',
			'_product_4_benefit_call_to_actions',
			'_product_4_benefit_call_to_actions_0_cta_description',
			'_product_4_benefit_call_to_actions_0_cta_icon',
			'_product_4_benefit_call_to_actions_0_cta_title',
			'_product_4_benefit_call_to_actions_1_cta_description',
			'_product_4_benefit_call_to_actions_1_cta_icon',
			'_product_4_benefit_call_to_actions_1_cta_title',
			'_product_4_benefit_call_to_actions_2_cta_description',
			'_product_4_benefit_call_to_actions_2_cta_icon',
			'_product_4_benefit_call_to_actions_2_cta_title',
			'_product_4_benefits_background_color',
			'_product_4_benefits_button_label',
			'_product_4_benefits_heading',
			'_product_4_benefits_introduction',
			'_product_4_benefits_link',
			'_product_4_button_label',
			'_product_4_column_1_1',
			'_product_4_description',
			'_product_4_featured_resources',
			'_product_4_heading',
			'_product_4_link',
			'_product_4_product_feature_alignment',
			'_product_4_product_feature_background_color',
			'_product_4_product_feature_button_label',
			'_product_4_product_feature_description',
			'_product_4_product_feature_heading',
			'_product_4_product_feature_height',
			'_product_4_product_feature_image',
			'_product_4_product_feature_image_position',
			'_product_4_product_feature_link',
			'_product_4_product_feature_sub_heading',
			'_product_4_sub_heading',
			'_product_4_title',
			'_product_5_button_label',
			'_product_5_column_1_1',
			'_product_5_description',
			'_product_5_featured_resources',
			'_product_5_gallery',
			'_product_5_heading',
			'_product_5_job_board_description',
			'_product_5_job_board_title',
			'_product_5_link',
			'_product_5_sub_heading',
			'_product_5_title',
			'_product_6_column_1_1',
			'_product_6_description',
			'_product_6_featured_resources',
			'_product_6_title',
			'_product_7_description',
			'_product_7_featured_resources',
			'_product_7_title',
			'_product_8_description',
			'_product_8_featured_resources',
			'_product_8_title',
			'_product_group',
			'_product_group_0_group_headline',
			'_product_group_0_group_title',
			'_product_group_0_products',
			'_product_group_0_products_0_product_description',
			'_product_group_0_products_0_product_image',
			'_product_group_0_products_0_product_link',
			'_product_group_0_products_0_product_title',
			'_product_group_0_products_1_product_description',
			'_product_group_0_products_1_product_image',
			'_product_group_0_products_1_product_link',
			'_product_group_0_products_1_product_title',
			'_product_group_0_products_2_product_description',
			'_product_group_0_products_2_product_image',
			'_product_group_0_products_2_product_link',
			'_product_group_0_products_2_product_title',
			'_product_group_0_products_3_product_description',
			'_product_group_0_products_3_product_image',
			'_product_group_0_products_3_product_link',
			'_product_group_0_products_3_product_title',
			'_product_group_1_group_headline',
			'_product_group_1_group_title',
			'_product_group_1_products',
			'_product_group_1_products_0_product_description',
			'_product_group_1_products_0_product_image',
			'_product_group_1_products_0_product_link',
			'_product_group_1_products_0_product_title',
			'_product_group_1_products_1_product_description',
			'_product_group_1_products_1_product_image',
			'_product_group_1_products_1_product_link',
			'_product_group_1_products_1_product_title',
			'_product_group_1_products_2_product_description',
			'_product_group_1_products_2_product_image',
			'_product_group_1_products_2_product_link',
			'_product_group_1_products_2_product_title',
			'_product_group_1_products_3_product_description',
			'_product_group_1_products_3_product_image',
			'_product_group_1_products_3_product_link',
			'_product_group_1_products_3_product_title',
			'_product_group_2_group_headline',
			'_product_group_2_group_title',
			'_product_group_2_products',
			'_product_group_2_products_0_product_description',
			'_product_group_2_products_0_product_image',
			'_product_group_2_products_0_product_link',
			'_product_group_2_products_0_product_title',
			'_relation_threshold',
			'_resource_alt_url',
			'_resource_author',
			'_resource_cat',
			'_resource_date',
			'_resource_type',
			'_show_call_to_action',
			'_type',
			'_alt_url',
			'_alt_url_target',
		];
		foreach ( $list as $key => &$item ) {
			$list[ $key ] = "'{$item}'";
		}

		return implode( $glue, $list );
	}

	/**
	 *
	 */
	public function no_items() {
		_e( 'There are no fields found for translating.', 'qordoba' );
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		if ( $this->is_acf_field( $item ) ) {
			switch ( $column_name ) {
				case 'meta_key':
					return strip_tags( $this->extract_acf_value( $item, 'label' ) );
				case 'meta_value':
					return strip_tags( $this->extract_acf_value( $item, 'name' ) );
				case 'meta_type':
					return strip_tags( $this->extract_acf_value( $item, 'type' ) );
				case 'meta_translation':
					return $this->is_checked( $item ) ? 'Yes' : '';
				default:
					return '';
			}
		} else {
			switch ( $column_name ) {
				case 'meta_key':
					return strip_tags( $item[ $column_name ] );
				case 'meta_value':
					return strip_tags( $item[ $column_name ] );
				case 'meta_type':
					return 'Text';
				case 'meta_translation':
					return $this->is_checked( $item ) ? 'Yes' : '';
				default:
					return '';
			}
		}
	}

	/**
	 * @param $data
	 *
	 * @return bool
	 */
	private function is_acf_field( $data ) {
		return ( 0 === strpos( $data['meta_key'], 'field_' ) );
	}

	/**
	 * @param $data
	 * @param $key
	 *
	 * @return string
	 */
	private function extract_acf_value( $data, $key ) {
		static $valueData = array();
		$value = '';
		if ( 0 === count( $valueData ) ) {
			$data = unserialize( $data['meta_value'] );
		}
		if ( isset( $data[ $key ] ) ) {
			$value = ucfirst( $data[ $key ] );
		}

		return $value;
	}

	/**
	 * @param $item
	 *
	 * @return bool
	 */
	public function is_checked( $item ) {
		if ( 0 === count( self::$post_custom_fields ) ) {
			$options = get_option( 'qordoba_options', array() );
			if ( isset( $options['post_fields'] ) ) {
				self::$post_custom_fields = $options['post_fields'];
			}
		}

		return in_array( $item['meta_key'], self::$post_custom_fields, true );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="bulk[]" value="%s"/>', $item['meta_key'] );
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {
		return isset( $item['meta_key'] ) ? trim( $item['meta_key'] ) : '';
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		return array(
			'cb'               => '<input type="checkbox" />',
			'meta_key'         => __( 'Fields Label', 'qordoba' ),
			'meta_value'       => __( 'Fields Name', 'qordoba' ),
			'meta_type'        => __( 'Fields Type', 'qordoba' ),
			'meta_translation' => __( 'Fields Translation', 'qordoba' ),
		);
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'bulk-save'   => __( 'Save', 'qordoba' ),
			'bulk-remove' => __( 'Remove', 'qordoba' ),
		);
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->process_bulk_action();
		$this->_column_headers = $this->get_column_info();


		$per_page     = $this->get_items_per_page( 'fields_per_page', self::ITEMS_PER_PAGE );
		$current_page = $this->get_pagenum();

		$this->set_pagination_args( array(
			'total_items' => self::record_count(),
			'per_page'    => $per_page,
		) );

		$this->items = self::get_custom_fields( $per_page, $current_page );
	}

	/**
	 *
	 */
	public function process_bulk_action() {
		if ( isset( $_POST['action'] ) || isset( $_POST['action2'] ) ) {
			if ( ( 'bulk-remove' === $_POST['action'] ) || ( 'bulk-remove' === $_POST['action2'] ) ) {
				if ( ( isset( $_POST['bulk'] ) && is_array( $_POST['bulk'] ) ) ) {
					$options = get_option( 'qordoba_options', array() );
					foreach ( $_POST['bulk'] as $key => $item ) {
						unset( $options['post_fields'][ array_search( $item, $options['post_fields'] ) ] );
					}
					$this->update_options( $options );
					$this->clear_wp_cache();
				}
			}
			if ( ( 'bulk-save' === $_POST['action'] ) || ( 'bulk-save' === $_POST['action2'] ) ) {
				if ( ( isset( $_POST['bulk'] ) && is_array( $_POST['bulk'] ) ) ) {
					$options = get_option( 'qordoba_options', array() );
					foreach ( $_POST['bulk'] as $key => $item ) {
						$options['post_fields'][] = $item;
					}
					$this->update_options( $options );
					$this->clear_wp_cache();
				}
			}
		}
	}

	/**
	 * @param $data
	 */
	public function update_options( $data ) {
		global $wpdb;
		$sql = sprintf( 'UPDATE %s SET option_value = \'%s\' WHERE option_name = \'qordoba_options\';', $wpdb->options,
			maybe_serialize( $data ) );
		$wpdb->query( $sql );
	}

	/**
	 * @return bool
	 */
	private function clear_wp_cache() {
		return wp_cache_delete( 'alloptions', 'options' );
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		if ( self::is_acf_pro_plugin_exist() ) {
			$sql = sprintf( 'SELECT COUNT(DISTINCT (post_excerpt)) FROM %s WHERE post_type = \'acf-field\'',
				$wpdb->posts );
		} elseif ( self::is_acf_plugin_exist() ) {
			$sql = sprintf( 'SELECT COUNT(DISTINCT (meta_key)) FROM %s WHERE', $wpdb->postmeta );
			$sql .= sprintf( ' meta_key NOT IN (%s)', self::excluded_custom_fields_list() );
		} else {
			$sql = sprintf( 'SELECT COUNT(DISTINCT (meta_key), meta_value) FROM %s WHERE meta_key NOT LIKE \'%s\'',
				$wpdb->postmeta, '_qor%' );
			$sql .= sprintf( ' meta_key NOT IN (%s)', self::excluded_custom_fields_list() );
		}
		if ( isset( $_POST[ self::SEARCH_IDENTIFIER ] ) && ( '' !== $_POST[ self::SEARCH_IDENTIFIER ] ) ) {
			$sql .= " AND (meta_key LIKE '%{$_POST[self::SEARCH_IDENTIFIER]}%' OR meta_value LIKE '%{$_POST[self::SEARCH_IDENTIFIER]}%')";
		}

		return $wpdb->get_var( $sql );
	}

	/**
	 * @return bool
	 */
	public static function is_acf_pro_plugin_exist() {
		return class_exists( 'acf_pro' );
	}

	/**
	 * @return bool
	 */
	public static function is_acf_plugin_exist() {
		return class_exists( 'acf' );
	}

	/**
	 * @param string $glue
	 *
	 * @return string
	 */
	protected static function excluded_custom_fields_list( $glue = ',' ) {
		$list = array(
			'_edit_last',
			'_edit_lock',
//			'_pll_strings_translations',
			'_qor_version',
			'_thumbnail_id',
//			'_wp_attached_file',
//			'_wp_attachment_metadata',
//			'_wp_desired_post_slug',
//			'_wp_page_template',
//			'_wp_trash_meta_status',
//			'_wp_trash_meta_time',
//			'_pingme',
//			'_encloseme',
//			'_elementor_data',
//			'_elementor_source_image_hash',
//			'_elementor_edit_mode',
//			'_elementor_version',
//			'_elementor_css',
//			'_elementor_template_type',
//			'rule',
//			'position',
//			'hide_on_screen',
		);
		foreach ( $list as $key => &$item ) {
			$list[ $key ] = "'{$item}'";
		}

		return implode( $glue, $list );
	}

	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_custom_fields( $per_page = 200, $page_number = 1 ) {
		global $wpdb;
		$offset = ( $page_number - 1 ) * $per_page;
		if ( self::is_acf_pro_plugin_exist() ) {
			$sql = sprintf( 'SELECT DISTINCT (post_excerpt) AS meta_key, post_title AS meta_value FROM %s WHERE post_type = \'acf-field\'', $wpdb->posts );
		} elseif ( self::is_acf_plugin_exist() ) {
			$sql = sprintf( 'SELECT DISTINCT (meta_key), meta_value FROM %s', $wpdb->postmeta );
			$sql .= sprintf( ' WHERE meta_key NOT IN (%s)', self::excluded_custom_fields_list() );
		} else {
			$sql = sprintf( 'SELECT DISTINCT (meta_key), meta_value FROM %s WHERE meta_key NOT LIKE \'%s\'', $wpdb->postmeta, '_qor%' );
			$sql .= sprintf( ' WHERE meta_key NOT IN (%s)', self::excluded_custom_fields_list() );
		}
		if ( isset( $_POST[ self::SEARCH_IDENTIFIER ] ) && ( '' !== $_POST[ self::SEARCH_IDENTIFIER ] ) ) {
			if ( self::is_acf_pro_plugin_exist() ) {
				$sql .= " AND (post_excerpt LIKE '%{$_POST[self::SEARCH_IDENTIFIER]}%' OR post_title LIKE '%{$_POST[self::SEARCH_IDENTIFIER]}%')";
			} else {
				$sql .= " AND (meta_key LIKE '%{$_POST[self::SEARCH_IDENTIFIER]}%' OR meta_value LIKE '%{$_POST[self::SEARCH_IDENTIFIER]}%')";
			}
		}
		$sql .= sprintf( ' LIMIT %d OFFSET %d', $per_page, $offset );

		return $wpdb->get_results( $sql, ARRAY_A );
	}
}


/**
 * Class SP_Plugin
 */
class SP_Plugin {

	/**
	 * @var SP_Plugin
	 */
	static private $instance;

	/**
	 * @var
	 */
	public $customers_obj;

	/**
	 * SP_Plugin constructor.
	 */
	public function __construct() {
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
	}

	/**
	 * @param $status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 */
	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * @return SP_Plugin
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 *
	 */
	public function plugin_menu() {
		$hook = add_submenu_page(
			qor()->module()->get_menu_parent_slug(),
			__( 'Custom Fields', 'qordoba' ),
			__( 'Custom Fields', 'qordoba' ),
			'manage_options',
			'qordoba_custom_fields',
			array( $this, 'plugin_settings_page' )
		);
		add_action( "load-$hook", array( $this, 'screen_option' ) );
	}

	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
		?>
        <div class="wrap">
            <h2>Custom Fields</h2>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
								<?php
								$this->customers_obj->prepare_items();
								?>
                                <div>
									<?= $this->customers_obj->search_box( __( 'Search Metas', 'qordoba' ),
										Qordoba_Custom_Fields_Table::SEARCH_IDENTIFIER ); ?>
                                </div>
								<?php
								$this->customers_obj->display(); ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
		<?php
	}

	/**
	 *
	 */
	public function screen_option() {
		$option = 'per_page';
		$args   = array(
			'label'   => 'Custom Fields',
			'default' => Qordoba_Custom_Fields_Table::ITEMS_PER_PAGE,
			'option'  => 'fields_per_page',
		);
		add_screen_option( $option, $args );
		$this->customers_obj = new Qordoba_Custom_Fields_Table();
	}
}


add_action(
	'plugins_loaded', function () {
	SP_Plugin::get_instance();
} );