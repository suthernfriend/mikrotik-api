<?php


namespace MikroTikApi\Types;


class MacAddress {

	/**
	 * @var string
	 */
	private $mac;

	/**
	 * MacAddress constructor.
	 * @param string $mac
	 * @throws MikroTikTypeException
	 */
	public function __construct(string $mac) {
		$this->mac = strtolower($mac);
		if (!self::isMacAddress($this->mac))
			throw new MikroTikTypeException("$mac is not a valid mac address");
	}

	/**
	 * @param $addr
	 * @return bool
	 */
	public static function isMacAddress($addr): bool {
		return \BlakeGardner\MacAddress::validateMacAddress($addr);
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		return $this->mac;
	}
}