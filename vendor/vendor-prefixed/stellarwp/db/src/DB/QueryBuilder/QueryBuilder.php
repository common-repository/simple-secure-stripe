<?php
/**
 * @license GPL-2.0
 *
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder;

use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\Aggregate;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\CRUD;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\FromClause;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\GroupByStatement;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\HavingClause;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\JoinClause;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\LimitStatement;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\MetaQuery;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\OffsetStatement;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\OrderByStatement;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\SelectStatement;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\TablePrefix;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\UnionOperator;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\QueryBuilder\Concerns\WhereClause;

/**
 * @since 1.0.0
 */
class QueryBuilder {
	use Aggregate;
	use CRUD;
	use FromClause;
	use GroupByStatement;
	use HavingClause;
	use JoinClause;
	use LimitStatement;
	use MetaQuery;
	use OffsetStatement;
	use OrderByStatement;
	use SelectStatement;
	use TablePrefix;
	use UnionOperator;
	use WhereClause;

	/**
	 * @return string
	 */
	public function getSQL() {
		$sql = array_merge(
			$this->getSelectSQL(),
			$this->getFromSQL(),
			$this->getJoinSQL(),
			$this->getWhereSQL(),
			$this->getGroupBySQL(),
			$this->getHavingSQL(),
			$this->getOrderBySQL(),
			$this->getLimitSQL(),
			$this->getOffsetSQL(),
			$this->getUnionSQL()
		);

		// Trim double spaces added by DB::prepare
		return str_replace(
			[ '   ', '  ' ],
			' ',
			implode( ' ', $sql )
		);
	}
}
