<?php
/**
 * @license GPL-2.0
 *
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Clauses;

use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\DB;

/**
 * @since 1.0.0
 */
class RawSQL {
	/**
	 * @var string
	 */
	public $sql;

	/**
	 * @param  string  $sql
	 * @param  array<int,mixed>|string|null  $args
	 */
	public function __construct( $sql, $args = null ) {
		$this->sql = $args ? DB::prepare( $sql, $args ) : $sql;
	}
}
