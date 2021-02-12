<?php

namespace Swedbank\SPP\Payment;

/**
 * This class provides helper functions for payment methods.
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class Helper
{

	/**
	 * Returns payment method instance by its string code. Returns false when invalid code supplied.
	 *
	 * @param type $code Payment method code
	 * @param type $subcode Additional type, used for internet banks
	 * @return Method|boolean
	 */
	public static function getMethodByCode($code, $subcode=null)
	{
		switch ($code) {
			case 'CC': return new CreditCard();
			case 'PP': return new PayPal();
			case 'IB': return new InternetBank($subcode);
		}

		return false;
	}
}
