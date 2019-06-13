<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class Static_Content_GF_Field extends GF_Field {

	/**
	 * @var string $type The field type.
	 */
	public $type = 'staticcontent';

	/**
	 * Return the field title, for use in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'Static Content', 'gfstaticcontentaddon' );
	}

	/**
	 * Assign the field button to the Advanced Fields group.
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
		);
	}

	/**
	 * The settings which should be available on the field in the form editor.
	 *
	 * @return array
	 */
	function get_form_editor_field_settings() {
		return array(
			//'label_setting',
			//'description_setting',
			//'post_type_setting',
			'post_id_setting',
			'placeholder_setting',
			'css_class_setting',
			'admin_label_setting',
			'visibility_setting',
			'label_placement_setting',
			'conditional_logic_field_setting',
		);
	}

	/**
	 * Enable this field for use with conditional logic.
	 *
	 * @return bool
	 */
	public function is_conditional_logic_supported() {
		return true;
	}

	/**
	 * The scripts to be included in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_inline_script_on_page_render() {

		// set the default field label for the staticcontent type field
		$script = sprintf( "function SetDefaultValues_staticcontent(field) {field.label = '%s';}", $this->get_form_editor_field_title() ) . PHP_EOL;

		// initialize the fields custom settings
		$script .= "jQuery(document).bind('gform_load_field_settings', function (event, field, form) {" .
		           "var postType = field.postType == undefined ? '' : field.postType;" .
		           "var postId = field.postId == undefined ? '' : field.postId;" .
		           "var labelPlacement = field.labelPlacement == undefined ? 'hidden_label' : field.labelPlacement;" .
		           "jQuery('#post_type_setting').val(postType);" .
		           "jQuery('#post_id_setting').val(postId);" .
		           "jQuery('#field_label_placement').val(labelPlacement);" .
		           "});" . PHP_EOL;

		// saving the staticcontent setting
		$script .= "function SetPostTypeSetting(value) {SetFieldProperty('postType', value);}" . PHP_EOL;
		$script .= "function SetPostIdSetting(value) {SetFieldProperty('postId', value);}" . PHP_EOL;

		return $script;
	}

	/**
	 * Define the fields inner markup.
	 *
	 * @param array $form The Form Object currently being processed.
	 * @param string|array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array $entry Null or the Entry Object currently being edited.
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		$id              = absint( $this->id );
		$form_id         = absint( $form['id'] );
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		// Prepare the value of the input ID attribute.
		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "static_content_$id" : 'static_content_' . $form_id . "_$id";

		// Get the value of the staticContent property for the current field.
        $postType = $this->postType; // not yet implemented correctly

        // Get translated Post ID if WPML is installed
        if (class_exists('SitePress')) {
            $postId = $this->translate_object_id($this->postId, 'page');
        } else {
            $postId = $this->postId;
        }

        // Get the post content.
        $post_content = do_shortcode(get_post_field('post_content', $postId));

		// Prepare the input classes.
		$class_suffix = $is_entry_detail ? '_admin' : '';
		$class        = $class_suffix . ' ' . $postType;

		// Prepare the other input attributes.
		$tabindex              = $this->get_tabindex();
		$logic_event = '';
		if (! $is_form_editor && ! $is_entry_detail){
			if(version_compare( GFForms::$version, '2.4-beta-1', '<' )){
		        	$logic_event = $this->get_conditional_logic_event( 'keyup' );
            		}
        	}
		$placeholder_attribute = $this->get_field_placeholder_attribute();
		$invalid_attribute     = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
		$disabled_text         = $is_form_editor ? 'disabled="disabled"' : '';

		// Prepare the output.
		$output = "<div name='static_content_{$id}' id='{$field_id}' class='{$class}' {$tabindex} {$logic_event} {$placeholder_attribute} {$invalid_attribute} {$disabled_text}>" . $post_content . "</div>";

		return sprintf( "<div class='ginput_container ginput_container_%s'>%s</div>", $this->type, $output );
	}


    /**
     * Returns the translated object ID(post_type or term) or original if missing
     *
     * @param $object_id integer|string|array The ID/s of the objects to check and return
     * @param $type the object type: post, page, {custom post type name}, nav_menu, nav_menu_item, category, tag etc.
     * @return string or array of object ids
     */
    function translate_object_id( $object_id, $type ) {
        $current_language= apply_filters( 'wpml_current_language', NULL );
        // if array
        if( is_array( $object_id ) ){
            $translated_object_ids = array();
            foreach ( $object_id as $id ) {
                $translated_object_ids[] = apply_filters( 'wpml_object_id', $id, $type, true, $current_language );
            }
            return $translated_object_ids;
        }
        // if string
        elseif( is_string( $object_id ) ) {
            // check if we have a comma separated ID string
            $is_comma_separated = strpos( $object_id,"," );

            if( $is_comma_separated !== FALSE ) {
                // explode the comma to create an array of IDs
                $object_id     = explode( ',', $object_id );

                $translated_object_ids = array();
                foreach ( $object_id as $id ) {
                    $translated_object_ids[] = apply_filters ( 'wpml_object_id', $id, $type, true, $current_language );
                }

                // make sure the output is a comma separated string (the same way it came in!)
                return implode ( ',', $translated_object_ids );
            }
            // if we don't find a comma in the string then this is a single ID
            else {
                return apply_filters( 'wpml_object_id', intval( $object_id ), $type, true, $current_language );
            }
        }
        // if int
        else {
            return apply_filters( 'wpml_object_id', $object_id, $type, true, $current_language );
        }
    }

}

GF_Fields::register( new Static_Content_GF_Field() );
