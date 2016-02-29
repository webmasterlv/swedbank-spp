<?php

namespace Swedbank\SPP\Response;

use Swedbank\SPP\Gateway;

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
class QueryResponsePP extends QueryResponse
{

	/**
	 * @inheritDoc
	 */
	protected function getTransactionStatus()
	{
		if (!isset($this -> data['PayPalTxn'])) {
			return false;
		}

		$status		 = false;
		$data		 = $this -> data['PayPalTxn'];
		$statusCode	 = $data['checkoutstatus'];

		switch ($statusCode) {
			case 'PaymentActionNotInitiated': {
					$status = [
						'code' => Gateway::STATUS_PENDING,
						'reason' => $data['pendingreason'],
						'gw_code' => $statusCode
					];
					break;
				}
			case 'PaymentActionFailed': {
					$status = [
						'code' => Gateway::STATUS_ERROR,
						'reason' => '',
						'gw_code' => $statusCode
					];
					break;
				}
			case 'PaymentActionCompleted': {
					$status = [
						'code' => Gateway::STATUS_SUCCESS,
						'reason' => '',
						'gw_code' => $statusCode
					];
					break;
				}
			case 'PaymentActionInProgress': {
					$status = [
						'code' => Gateway::STATUS_PENDING,
						'reason' => '',
						'gw_code' => $statusCode
					];
					break;
				}
		}

		return $status;
	}

	/**
	 * @inheritDoc
	 */
	public function getExtendedReference()
	{
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function getExtendedData()
	{
		return isset($this -> data['PayPalTxn']) ? $this -> data['PayPalTxn'] : [];
	}

	/**
	 * Is PayPal transaction awaiting submission
	 * 
	 * @return boolean
	 */
	public function isWaitingSubmission()
	{
		if (!isset($this -> data['PayPalTxn'])) {
			return false;
		}

		$r = $this -> data['PayPalTxn'];

		$checkout = isset($r['checkoutstatus']) ? $r['checkoutstatus'] : '';
		return $checkout == 'PaymentActionNotInitiated';
	}

	/**
	 * @inheritDoc
	 */
	public function getRemoteStatus()
	{
		if (!isset($this -> data['PayPalTxn'])) {
			return '';
		}

		return $this -> data['PayPalTxn']['ack'];
	}
}