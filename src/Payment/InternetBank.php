<?php

namespace Swedbank\SPP\Payment;

/**
 * This class describes data to be used for internet banking payments
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class InternetBank implements Method
{
	/**
	 * Available internet banks
	 */
	const SWEDBANK			 = 'SW';
	const NORDEA			 = 'NL';
	const SEB_LATVIA		 = 'SV';
	const SEB_LITHUANIA		 = 'SL';
	const DNB				 = 'DN';
	const DANSKE			 = 'DL';
	const CITADELE			 = 'CA';

	/**
	 * Selected internet bank
	 * 
	 * @var string
	 */
	protected $bank;

	/**
	 * New internet bank payment
	 *
	 * @param string $bank Bank to use
	 */
	public function __construct($bank)
	{
		$this -> bank = $bank;
	}

	/**
	 * Get selected bank
	 *
	 * @return string
	 */
	public function getBank()
	{
		return $this -> bank;
	}

	/**
	 * @inheritDoc
	 */
	public function getCodeName()
	{
		return 'IB';
	}
}