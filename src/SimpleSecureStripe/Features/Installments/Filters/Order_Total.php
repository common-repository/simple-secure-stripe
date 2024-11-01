<?php

namespace SimpleSecureWP\SimpleSecureStripe\Features\Installments\Filters;

class Order_Total extends Abstract_Filter {

	private $total;

	private $currency;

	private $limits = [
		'MXN' => 300,
		'BRL' => 1,
	];

	public function __construct( $total, $currency ) {
		$this->total    = $total;
		$this->currency = $currency;
	}

	function is_available() {
		return $this->total >= $this->limits[ $this->currency ];
	}

}