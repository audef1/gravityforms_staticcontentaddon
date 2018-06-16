<?php

GFForms::include_addon_framework();

class GFStaticContentAddOn extends GFAddOn {

	protected $_version = GF_STATIC_CONTENT_ADDON_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'gfstaticcontentaddon';
	protected $_path = 'gfstaticcontentaddon/staticcontentaddon.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms Static Content Add-On';
	protected $_short_title = 'Static Content Add-On';

	/**
	 * @var object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return object $_instance An instance of this class.
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Include the field early so it is available when entry exports are being performed.
	 */
	public function pre_init() {
		parent::pre_init();

		if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
			require_once( 'includes/class-static-content-field.php' );
		}
	}

	public function init_admin() {
		parent::init_admin();

        add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
        add_action( 'gform_field_standard_settings', array( $this, 'static_content_settings' ), 10, 2 );
	}


	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Include static_content_script.js when the form contains a 'staticcontent' type field.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'static_content_script_js',
				'src'     => $this->get_base_url() . '/js/static_content_script.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array( 'field_types' => array( 'staticcontent' ) ),
				),
			),

		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Include static_content_styles.css when the form contains a 'staticcontent' type field.
	 *
	 * @return array
	 */
	public function styles() {
		$styles = array(
			array(
				'handle'  => 'static_content_styles_css',
				'src'     => $this->get_base_url() . '/css/static_content_styles.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( 'staticcontent' ) )
				)
			)
		);

		return array_merge( parent::styles(), $styles );
	}


	// # FIELD SETTINGS -------------------------------------------------------------------------------------------------

    /**
     * Add the tooltips for the field.
     *
     * @param array $tooltips An associative array of tooltips where the key is the tooltip name and the value is the tooltip.
     *
     * @return array
     */
    public function tooltips( $tooltips ) {
        $static_content_tooltips = array(
            'post_type_setting' => sprintf( '<h6>%s</h6>%s', esc_html__( 'Post Type', 'gfstaticcontentaddon' ), esc_html__( 'Select what post type should be displayed.', 'gfstaticcontentaddon' ) ),
            'post_id_setting' => sprintf( '<h6>%s</h6>%s', esc_html__( 'Post ID', 'gfstaticcontentaddon' ), esc_html__( 'Select the post, which should be displayed.', 'gfstaticcontentaddon' ) ),
        );

        return array_merge( $tooltips, $static_content_tooltips );
    }

    /**
     * Add the static content settings to the general tab.
     *
     * @param int $position The position the settings should be located at.
     * @param int $form_id The ID of the form currently being edited.
     */
    public function static_content_settings( $position, $form_id ) {
        if ( $position == 250 ) {
            ?>
            <li class="post_type_setting field_setting">
                <label for="post_type_setting" class="section_label">
                    <?php esc_html_e( 'Post Type', 'gfstaticcontentaddon' ); ?>
                    <?php gform_tooltip( 'post_type_setting' ) ?>
                </label>
                <select id="post_type_setting" class="fieldwidth-3" onchange="SetPostTypeSetting(jQuery(this).val());">
                    <option value=""></option>
                    <option value="page">Page</option>
                    <?php
/*                    $args = [
                        'public' => true,
                    ];

                    $post_types = get_post_types( $args, 'objects' );
                    foreach ( $post_types as $post_type_obj ):
                        $labels = get_post_type_labels( $post_type_obj );
                   */?><!--
                        <option value="<?php /*echo esc_attr( $post_type_obj->name ); */?>"><?php /*echo esc_html( $labels->name ); */?></option>
                   --><?php /*endforeach; */?>
                </select>
            </li>
            <li class="post_id_setting field_setting">
                <label for="post_id_setting" class="section_label">
                    <?php esc_html_e( 'Post ID', 'gfstaticcontentaddon' ); ?>
                    <?php gform_tooltip( 'post_id_setting' ) ?>
                </label>
                <select id="post_id_setting" class="fieldwidth-3" onchange="SetPostIdSetting(jQuery(this).val());">
                    <option value=""></option>
                    <?php
                    $pages = get_pages();
                    foreach ( $pages as $page ) {
                        $option = '<option value="' . $page->ID . '">';
                        $option .= $page->post_title;
                        $option .= '</option>';
                        echo $option;
                    }
                    ?>
                </select>
            </li>
            <?php
        }
    }
}
