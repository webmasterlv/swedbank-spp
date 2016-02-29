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
class QueryResponseIB extends QueryResponse
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

		return isset($data['APMTxn']) ? $data['APMTxn'] : [];
	}

	/**
	 * Returns payment status code
	 * 
	 * @return array|false
	 */
	protected function getHPSStatus()
	{
		$status = false;

		if ($this -> type == Query::TYPE_INFO && isset($this -> data['HpsTxn'])) {
			$data = $this -> data['HpsTxn']['AuthAttempts']['Attempt'];

			$status = [
				'code' => $data['dc_response'],
				'reason' => $data['reason'],
				'gw_code' => $data['dc_response']
			];
		}

		if ($this -> type == Query::TYPE_EXTENDED && isset($this -> data['QueryTxnResult'])) {
			$data	 = $this -> data['QueryTxnResult'];
			$status	 = [
				'code' => $data['status'],
				'reason' => $data['reason'],
				'gw_code' => $data['status']
			];
		}

		return $status;
	}

	/**
	 * @inheritDoc
	 */
	protected function getTransactionStatus()
	{
		$status = $this -> getHPSStatus();

		if ($status !== false) {
			switch ($status['code']) {
				case 1: $status['code']		 = Gateway::STATUS_SUCCESS;
					break;
				case 2051: $status['code']	 = Gateway::STATUS_PENDING;
					break;
				case 2052: $status['code']	 = Gateway::STATUS_ERROR;
					break;
				case 2053: $status['code']	 = Gateway::STATUS_PENDING;
					break;
				case 2054: $status['code']	 = Gateway::STATUS_CANCELED;
					break;
				case 2066: $status['code']	 = Gateway::STATUS_INVESTIGATE;
					break;
				case 7: $status['code']		 = Gateway::STATUS_REFUSED;
					break;
			}

			return $status;
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getExtendedReference()
	{
		if ($this -> type == Query::TYPE_INFO) {
			if (!isset($this -> data['HpsTxn'])) {
				return '';
			}

			$data = $this -> data['HpsTxn']['AuthAttempts']['Attempt'];

			return $data['datacash_reference'];
		}

		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getRemoteStatus()
	{
		$status = $this -> getHPSStatus();

		if ($status !== false) {
			return $status['code'];
		}

		return $this -> data['status'];
	}
}