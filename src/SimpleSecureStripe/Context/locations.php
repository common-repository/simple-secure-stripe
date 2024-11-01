<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SimpleSecureWP\SimpleSecureStripe\Context\Context;

/**
 * Defines the locations the `Context` class should look up.
 *
 * The location definitions are moved here to avoid burdening the `Context` class with a long array definition
 * that would be loaded upfront every time the `Context` class file is loaded. Since locations will be required
 * only when the Context is built moving them here is a small optimization.
 * This file is meant to be included by the `Context::populate_locations` method.
 *
 * @since 1.0.0
 */
return [
	'post_id' => [
		'read' => [
			Context::FUNC => static function () {
				return get_the_ID();
			}
		],
	],
	'permalink_structure' => [
		'read' => [
			Context::OPTION => [ 'permalink_structure' ],
		],
	],
	'plain_permalink' => [
		'read' => [
			Context::LOCATION_FUNC => [
				'permalink_structure',
				static function( $struct ){
					return empty( $struct );
				},
			],
		],
	],
	'posts_per_page' => [
		'read'  => [
			Context::REQUEST_VAR  => 'posts_per_page',
			Context::OPTION       => 'posts_per_page',
		],
		'write' => [
			Context::REQUEST_VAR => 'posts_per_page',
		],
	],
	'is_main_query'  => [
		'read'  => [
			Context::FUNC => static function () {
				global $wp_query;

				if ( empty( $wp_query ) ) {
					return false;
				}

				if ( ! $wp_query instanceof WP_Query ) {
					return false;
				}

				return $wp_query->is_main_query();
			},
		],
		'write' => [
			Context::FUNC => static function () {
				global $wp_query, $wp_the_query;
				$wp_the_query = $wp_query;
			},
		],
	],
	'paged'          => [
		'read'  => [
			Context::REQUEST_VAR => [ 'paged', 'page' ],
			Context::QUERY_VAR   => [ 'paged', 'page' ],
		],
		'write' => [
			Context::REQUEST_VAR => 'paged',
			Context::QUERY_VAR   => 'paged',
		],
	],
	'page'           => [
		'read'  => [
			Context::REQUEST_VAR => [ 'page', 'paged' ],
			Context::QUERY_VAR   => [ 'page', 'paged' ],
		],
		'write' => [
			Context::REQUEST_VAR => 'page',
			Context::QUERY_VAR   => 'page',
		],
	],
	'name'           => [
		'read'  => [
			Context::REQUEST_VAR => [ 'name', 'post_name' ],
			Context::WP_PARSED   => [ 'name', 'post_name' ],
			Context::QUERY_VAR   => [ 'name', 'post_name' ],
		],
		'write' => [
			Context::REQUEST_VAR => [ 'name', 'post_name' ],
			Context::QUERY_VAR   => [ 'name', 'post_name' ],
		],
	],
	'post_type' => [
		'read' => [
			Context::FUNC        => static function() {
				$post_type_objs = get_post_types(
					[
						'public' => true,
						'_builtin' => false,
					],
					'objects'
				);

				foreach ( $post_type_objs as $post_type ) {
					if ( empty( $post_type->query_var ) ) {
						continue;
					}

					$url_value = sswps_get_request_var( $post_type->query_var, false );
					if ( empty( $url_value ) ) {
						continue;
					}

					return $post_type->name;
				}

				return Context::NOT_FOUND;
			},
			Context::QUERY_PROP  => 'post_type',
			Context::QUERY_VAR   => 'post_type',
			Context::REQUEST_VAR => 'post_type',
		],
	],
	'single' => [
		'read' => [ Context::QUERY_METHOD => 'is_single' ]
	],
	'taxonomy' => [
		'read' => [
			Context::QUERY_PROP  => [ 'taxonomy' ],
			Context::QUERY_VAR   => [ 'taxonomy' ],
			Context::REQUEST_VAR => [ 'taxonomy' ],
		],
	],
	'post_tag' => [
		'read' => [
			Context::QUERY_PROP  => [ 'post_tag', 'tag' ],
			Context::QUERY_VAR   => [ 'post_tag', 'tag' ],
			Context::REQUEST_VAR => [ 'post_tag', 'tag' ],
		],
	],
	'bulk_edit' => [
		'read' => [
			Context::REQUEST_VAR => [ 'bulk_edit' ],
		],
	],
	'inline_save' => [
		'read' => [
			Context::FUNC => [
				static function () {
					return sswps_get_request_var( 'action', false ) === 'inline-save'
						? true
						: Context::NOT_FOUND;
				}
			],
		],
	],
	'wc_settings_section' => [
		'read' => [
			Context::FUNC => static function() {
				$page = sswps_get_request_var( 'page', false );
				if ( empty( $page ) || $page !== 'wc-settings' ) {
					return Context::NOT_FOUND;
				}

				$section = sswps_get_request_var( 'section', false );
				if ( empty( $section ) ) {
					return Context::NOT_FOUND;
				}

				return $section;
			},
		],
	],
	'wc_settings_tab' => [
		'read' => [
			Context::FUNC => static function() {
				$page = sswps_get_request_var( 'page', false );
				if ( empty( $page ) || $page !== 'wc-settings' ) {
					return Context::NOT_FOUND;
				}

				$tab = sswps_get_request_var( 'tab', false );
				if ( empty( $tab ) ) {
					return Context::NOT_FOUND;
				}

				return $tab;
			},
		],
	],
];
