<?php

namespace Swedbank\SPP\Response;

use Swedbank\SPP\Gateway;
use Swedbank\SPP\Operation\Query;

/**
 * Response for 'Query' operation
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class QueryResponseCC extends QueryResponseIB
{

	/**
	 * @inheritDoc
	 */
	public function getExtendedData()
	{
		if ($this -> type == Query::TYPE_INFO) {
			return [];
		}

		$data = $this -> data['QueryTxnResult'];

		return isset($data['Card']) ? $data['Card'] : [];
	}

	/**
	 * @inheritDoc
	 */
	protected function getTransactionStatus()
	{
		$status = $this -> getHPSStatus();

		if ($status !== false) {
			switch ($status['code']) {
				case 1: $status['code']	 = Gateway::STATUS_SUCCESS;
					break;
				case 3: $status['code']	 = Gateway::STATUS_TIMEOUT;
					break;
				case 7: $status['code']	 = Gateway::STATUS_REFUSED;
					break;
				default: $status['code'] = Gateway::STATUS_ERROR;
					break;
			}

			return $status;
		}

		return false;
	}
}