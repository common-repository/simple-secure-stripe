<?php
/**
 * @license GPL-2.0
 *
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns;

use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\DB;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Clauses\OrderBy;

/**
 * @since 1.0.0
 */
trait OrderByStatement {
	/**
	 * @var OrderBy[]
	 */
	protected $orderBys = [];

	/**
	 * @param  string  $column
	 * @param  string  $direction  ASC|DESC
	 *
	 * @return $this
	 */
	public function orderBy( $column, $direction = 'ASC' ) {
		$this->orderBys[] = new OrderBy( $column, $direction );

		return $this;
	}

	/**
	 * @return array|string[]
	 */
	protected function getOrderBySQL() {
		if ( empty( $this->orderBys ) ) {
			return [];
		}

		$orderBys = implode(
			', ',
			array_map( function ( OrderBy $order ) {
				return DB::prepare( '%1s %2s', $order->column, $order->direction );
			}, $this->orderBys )
		);


		return [ 'ORDER BY ' . $orderBys ];
	}
}
