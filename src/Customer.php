<?php

namespace Swedbank\SPP;

/**
 * Describes a customer.
 *
 * This file is part of the Swedbank/SPP package.
 *
 * (c) Deniss Kozlovs <deniss@codeart.lv>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
class Customer implements DataSource
{
	/**
	 * Internal customer data
	 *
	 * @var array
	 */
	protected $data = [
		'email' => '',
		'first_name' => '',
		'last_name' => '',
		'province' => '',
		'city' => '',
		'address' => '',
		'phone' => '',
		'zip_code' => '',
		'country' => '',
	];

	/**
	 * Mandatory fields
	 *
	 * @var array
	 */
	protected $mandatory = [
		'email'
	];

	/**
	 * New customer
	 *
	 * @param string $email Customer email
	 * @param string $first_name Customer first name
	 * @param string $last_name Customer last name
	 * @throws \InvalidArgumentException When email is not present
	 */
	public function __construct($email, $first_name, $last_name)
	{

		if (!$email) {
			throw new \InvalidArgumentException('Customer email ir required. Please pass \'email\' field');
		}

		$this -> set([
			'email' => $email,
			'first_name' => $first_name,
			'last_name' => $last_name,
		]);
	}

	/**
	 * Customer data mass-assignment
	 *
	 * @param array $fields Customer fields
	 * @return int How much fields set successfully
	 */
	public function set(array $fields)
	{
		$set = 0;
		foreach ($fields as $k => $v) {
			$r = $this -> __set($k, $v);
			if ($r) {
				$set++;
			}
		}

		return $set;
	}

	/**
	 * Assign customer data field
	 *
	 * @param string $key
	 * @param string $value
	 * @return boolean
	 */
	public function __set($key, $value)
	{
		if (in_array($key, $this -> mandatory) && !$value) {
			return false;
		}

		if (array_key_exists($key, $this -> data) && is_scalar($value)) {
			$this -> data[$key] = $value;
			return true;
		}

		return false;
	}

	/**
	 * Get field from customer data
	 *
	 * @param string $key
	 * @return string
	 */
	public function __get($key)
	{
		if (array_key_exists($key, $this -> data)) {
			return $this -> data[$key];
		}

		return null;
	}

	/**
	 * Create customer from array data
	 *
	 * @param array $data Custoemr data
	 * @return Customer
	 */
	public static function fromArray(array $data)
	{
		$data += [
			'email' => '',
			'first_name' => '',
			'last_name' => '',
		];

		$customer	 = static::class;
		$instance	 = new $customer($data['email'], $data['first_name'], $data['last_name']);
		$instance -> set($data);

		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function getFields()
	{
		return $this -> data;
	}
}