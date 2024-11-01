<?php
/**
 * @license GPL-2.0
 *
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns;

use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Clauses\RawSQL;

/**
 * @since 1.0.0
 */
trait TablePrefix {
	/**
	 * @param  string|RawSQL  $table
	 *
	 * @return string
	 */
	public static function prefixTable( $table ) {
		global $wpdb;

		//  Shared tables in  multisite environment
		$sharedTables = [
			'users'	=> $wpdb->users,
			'usermeta' => $wpdb->usermeta,
		];

		if ( $table instanceof RawSQL ) {
			return $table->sql;
		}

		if ( array_key_exists( $table, $sharedTables ) ) {
			return $sharedTables[ $table ];
		}

		return $wpdb->prefix . $table;
	}
}
