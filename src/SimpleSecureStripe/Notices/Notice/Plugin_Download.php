<?php
namespace SimpleSecureWP\SimpleSecureStripe\Notices\Notice;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Notices\Notices;

/**
 * Shows an admin notice telling users which requisite plugins they need to download
 */
class Plugin_Download {

	private $plugin_path;

	private $plugins_required = [];

	/**
	 * @param string $plugin_path Path to the plugin file we're showing a notice for
	 */
	public function __construct( $plugin_path ) {
		$this->plugin_path = $plugin_path;

		App::get( Notices::class )->register(
			plugin_basename( $plugin_path ),
			[ $this, 'show_inactive_plugins_alert' ]
		);
	}

	/**
	 * Add a required plugin to the notice
	 *
	 * @since 1.0.0
	 *
	 * @param string $name           Name of the required plugin
	 * @param null   $thickbox_url   Download or purchase URL for plugin from within /wp-admin/ thickbox
	 * @param bool   $is_active      Indicates if the plugin is installed and active or not
	 * @param string $version        Optional version number of the required plugin
	 * @param bool   $addon          Indicates if the plugin is an add-on for The Events Calendar or Event Tickets
	 */
	public function add_required_plugin( $name, $thickbox_url = null, $is_active = null, $version = null, $addon = false ) {
		$this->plugins_required[ $name ] = [
			'name'           => $name,
			'thickbox_url'   => $thickbox_url,
			'is_active'      => $is_active,
			'version'        => $version ? $version . '+' : null,
			'addon'          => $addon,
		];
	}

	/**
	 * Echoes the admin notice, attach to admin_notices
	 *
	 * @see Plugin_Download::add_required_plugin()
	 *
	 * @since 1.0.0
	 */
	public function show_inactive_plugins_alert() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$plugin_data = get_plugin_data( $this->plugin_path );
		$req_plugins = [];

		if ( empty( $this->plugins_required ) ) {
			return;
		}

		// Make sure Thickbox is available and consistent appearance regardless of which admin page we're on
		wp_enqueue_style( 'plugin-install' );
		wp_enqueue_script( 'plugin-install' );
		add_thickbox();

		foreach ( $this->plugins_required as $req_plugin ) {
			$item    = $req_plugin['name'];
			$version = empty( $req_plugin['version'] ) ? '' : ' (' . str_replace( '-dev', '', $req_plugin['version'] ) . ')';

			if ( ! empty( $req_plugin['thickbox_url'] ) ) {
				$item = sprintf(
					'<a href="%1$s" class="thickbox" title="%2$s">%3$s%4$s</a>',
					esc_attr( $req_plugin['thickbox_url'] ),
					esc_attr( $req_plugin['name'] ),
					esc_html( $item ),
					esc_html( $version )
				);
			}

			if ( false === $req_plugin['is_active'] ) {
				$item = sprintf(
					'<strong class="sswps-inactive-plugin">%1$s</strong>',
					$item
				);
			}

			if ( ! empty( $req_plugin['addon'] ) ) {
				$plugin_name[] = $req_plugin['name'];
			}

			$req_plugins[] = $item;
		}

		// If empty then add in the default name.
		if ( empty( $plugin_name[0] ) ) {
			$plugin_name[] = $plugin_data['Name'];
		}

		$allowed_html = [
			'strong' => [],
			'a'      => [ 'href' => [] ],
		];

		$plugin_names_clean_text = wp_kses( $this->implode_with_grammar( $plugin_name ), $allowed_html );
		$req_plugin_names_clean_text = wp_kses( $this->implode_with_grammar( $req_plugins ), $allowed_html );

		/* translators: 2: plugin name(s), 3: required plugin name(s). */
		$notice_html_content = '<p>' . esc_html__( 'To begin using %2$s, please install (or upgrade) and activate %3$s.', 'simple-secure-stripe' ) . '</p>';

		printf(
			'<div class="error sswps-notice sswps-dependency-error" data-plugin="%1$s">'
			. wp_kses_post( $notice_html_content )
			. '</div>',
			esc_attr( sanitize_title( $plugin_data['Name'] ) ),
			wp_kses_post( $plugin_names_clean_text ),
			wp_kses_post( $req_plugin_names_clean_text )
		);
	}

	/**
	 * Implodes a list of items with proper grammar.
	 *
	 * If only 1 item, no grammar. If 2 items, just conjunction. If 3+ items, commas with conjunction.
	 *
	 * @param array $items List of items to implode
	 *
	 * @return string String of items
	 */
	public function implode_with_grammar( $items ) {
		$separator   = _x( ', ', 'separator used in a list of items', 'simple-secure-stripe' );
		$conjunction = _x( ' and ', 'the final separator in a list of two or more items', 'simple-secure-stripe' );
		$output      = $last_item = array_pop( $items );

		if ( $items ) {
			$output = implode( $separator, $items );

			if ( 1 < count( $items ) ) {
				$output .= $separator;
			}

			$output .= $conjunction . $last_item;
		}

		return $output;
	}

}