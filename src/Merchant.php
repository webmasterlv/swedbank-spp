<?php

namespace Swedbank\SPP;

use Swedbank\SPP\Gateway;

/**
 * Describes a merchant.
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class Merchant implements DataSource
{
	/**
	 * Payment region
	 *
	 * @var string
	 */
	protected $region;

	/**
	 * Transaction currency
	 * 
	 * @var string
	 */
	protected $currency;

	/**
	 * Interface language
	 *
	 * @var string
	 */
	protected $language;

	/**
	 * Initializes payment gateway
	 *
	 */
	public function __construct()
	{
		$this -> currency	 = Gateway::CURRENCY_EUR;
		$this -> region		 = Gateway::REGION_LAT;
		$this -> language	 = Gateway::LANG_LAT;
	}

	/**
	 * Set operation region
	 *
	 * @param string $region
	 * @return boolean
	 */
	public function setRegion($region)
	{

		$regions = [Gateway::REGION_EST, Gateway::REGION_LAT, Gateway::REGION_LIT];
		if (!in_array($region, $regions)) {
			return false;
		}

		$this -> region = $region;
		return true;
	}

	/**
	 * Set interface language
	 *
	 * @param string $lang
	 */
	public function setLanguage($lang)
	{
		$this -> language = $lang;
	}

	/**
	 * Get current region
	 *
	 * @return string
	 */
	public function getRegion()
	{
		return $this -> region;
	}

	/**
	 * Get current currency
	 * 
	 * @return string
	 */
	public function getCurrency()
	{
		return $this -> currency;
	}

	/**
	 * Get interface language
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return $this -> language;
	}

	/**
	 * @inheritDoc
	 */
	public function getFields()
	{
		return [
			'currency' => $this -> getCurrency(),
			'region' => $this -> getRegion(),
			'lang' => $this -> getLanguage(),
		];
	}
}