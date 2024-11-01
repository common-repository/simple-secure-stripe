<?php

namespace SimpleSecureWP\SimpleSecureStripe\Traits;

use Exception;

/**
 *
 * @since 1.0.0
 *
 * @author Simple & Secure WP
 */
trait Settings {

	protected $tab_title;

	private $admin_output = false;

	public function admin_nav_tab( $tabs ) {
		$tabs[ $this->id ] = $this->tab_title;

		return $tabs;
	}

	public function is_active( $key ) {
		return wc_string_to_bool( $this->get_option( $key ) );
	}

	public function get_prefix() {
		return $this->plugin_id . $this->id . '_';
	}

	public function generate_multiselect_html( $key, $data ) {
		$value           = (array) $this->get_option( $key, [] );
		$data['options'] = array_merge( array_flip( $value ), $data['options'] );

		return parent::generate_multiselect_html( $key, $data );
	}

	public function get_custom_attribute_html( $attribs ) {
		if ( ! empty( $attribs['custom_attributes'] ) && is_array( $attribs['custom_attributes'] ) ) {
			foreach ( $attribs['custom_attributes'] as $k => $v ) {
				if ( is_array( $v ) ) {
					$attribs['custom_attributes'][ $k ] = htmlspecialchars( wp_json_encode( $v ) );
				}
			}
		}

		return parent::get_custom_attribute_html( $attribs );
	}

	public function generate_description_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$data      = wp_parse_args(
			$data,
			array(
				'class'       => '',
				'style'       => '',
				'description' => '',
			)
		);
		if ( is_callable( $data['description'] ) ) {
			$data['description'] = call_user_func( $data['description'] );
		}
		ob_start();
		include SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/description.php';

		return ob_get_clean();
	}

	public function generate_paragraph_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'label'             => '',
			'class'             => '',
			'css'               => '',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => [],
		);
		$data      = wp_parse_args( $data, $defaults );
		if ( ! $data['label'] ) {
			$data['label'] = $data['title'];
		}
		ob_start();
		include SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/paragraph.php';

		return ob_get_clean();
	}

	public function generate_stripe_button_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$data      = wp_parse_args(
			$data,
			array(
				'title'       => '',
				'class'       => '',
				'style'       => '',
				'description' => '',
				'desc_tip'    => false,
				'id'          => 'sswps-button_' . $key,
				'disabled'    => false,
				'css'         => '',
			)
		);
		ob_start();
		include SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/button.php';

		return ob_get_clean();
	}

	public function generate_button_demo_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$data      = wp_parse_args(
			$data,
			array(
				'title'       => '',
				'class'       => '',
				'style'       => '',
				'description' => '',
				'desc_tip'    => false,
				'id'          => 'sswps-button-demo',
			)
		);
		ob_start();
		include SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/button-demo.php';

		return ob_get_clean();
	}

	public function generate_multi_select_countries_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$value     = (array) $this->get_option( $key );
		$data      = wp_parse_args(
			$data,
			array(
				'title'       => '',
				'class'       => '',
				'style'       => '',
				'description' => '',
				'desc_tip'    => false,
				'id'          => $field_key,
				'options'     => ! empty( $this->limited_countries ) ? $this->limited_countries : []
			)
		);
		ob_start();
		include SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/multi-select-countries.php';

		return ob_get_clean();
	}

	/**
	 * Added override to provide more control on which fields are saved and which are skipped.
	 * This plugin
	 * has custom setting fields like "paragraph" that are for info display only and not for saving.
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Settings_API::process_admin_options()
	 */
	public function process_admin_options() {
		$this->init_settings();

		$post_data = $this->get_post_data();

		$skip_types = array( 'title', 'paragraph', 'button', 'description', 'button_demo', 'stripe_button' );

		foreach ( $this->get_form_fields() as $key => $field ) {
			$skip = isset( $field['skip'] ) && $field['skip'] == true;
			if ( ! in_array( $this->get_field_type( $field ), $skip_types ) && ! $skip ) {
				try {
					$this->settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
				}
				catch ( Exception $e ) {
					$this->add_error( $e->getMessage() );
				}
			}
		}

		return update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings ), 'yes' );
	}

	public function get_stripe_documentation_url() {
		return sprintf( 'https://docs.paymentplugins.com/sswps/config/#/%s', $this->id );
	}

	public function validate_multi_select_countries_field( $key, $value ) {
		return is_array( $value ) ? array_map( 'wc_clean', array_map( 'stripslashes', $value ) ) : '';
	}

}
