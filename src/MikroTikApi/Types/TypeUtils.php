<?php


namespace MikroTikApi\Types;


use MikroTikApi\MikroTikConnectionException;
use Darsyn\IP\Exception\InvalidIpAddressException;
use Darsyn\IP\Exception\WrongVersionException;
use Darsyn\IP\Version\Multi;

class TypeUtils {
	/**
	 * @param $value
	 * @return string
	 * @throws MikroTikTypeException
	 */
	public static function convertDataObjectToJsonValue($value) {

		if (is_integer($value) || is_string($value) || is_null($value) || is_float($value) || is_bool($value))
			return $value;
		else if ($value instanceof Multi)
			return $value->getProtocolAppropriateAddress();
		else if ($value instanceof MacAddress)
			return "$value";
		else if ($value instanceof Duration)
			return $value->getSeconds();
		else if ($value instanceof MikroTikId)
			return "$value";
		else
			throw new MikroTikTypeException("Unknown type cant be converted to json value");
	}

	/**
	 * @param $value
	 * @param string|null $key
	 * @return string
	 * @throws MikroTikConnectionException
	 */
	public static function convertDataObjectToRouterOSValue($value, $key = null): string {

		if ($value instanceof MikroTikId)
			return "$value";
		if ($value instanceof Multi)
			return $value->getProtocolAppropriateAddress();
		if ($value instanceof MacAddress)
			return "$value";
		if ($value instanceof Duration)
			return $value->toRouterOsInterval();

		if (is_numeric($value))
			return "$value";

		if (is_string($value)) {
			return "$value";
		}

		if (is_bool($value))
			return $value ? "yes" : "no";

		if (is_array($value)) {
			$result = [];
			foreach ($value as $item)
				$result[] = self::convertDataObjectToRouterOSValue($key, $item);
			return implode(",", $result);
		}

		if (is_null($value))
			return "";

		throw new MikroTikConnectionException("Cannot convert value '$value' of type " . gettype($value) . " to a valid RouterOS value");
	}

	/**
	 * @param $value
	 * @param string|null $key
	 * @return bool|int|string
	 * @throws MikroTikTypeException
	 */
	public static function convertRouterOSToDataObjectValue($value, $key = null) {

		if ($key !== null) {
			if ($key === "id")
				if (MikroTikId::isMikroTikId($value))
					return new MikroTikId($value);
		}

		if (in_array($key, ["comment", "name", "authenticationTypes", "security.authenticationTypes"]))
			return "$value";
		if (in_array($value, ["true", "yes"]))
			return true;
		if (in_array($value, ["false", "no"]))
			return false;
		if (is_numeric($value))
			return intval($value);
		if (is_null($value))
			return null;
		if (MacAddress::isMacAddress($value))
			return new MacAddress($value);

		try {
			// always returns Multi (even tho factory returns IpInterface)
			if (strlen($value) > strlen("0.0.0.0"))
				return Multi::factory($value);
		} catch (WrongVersionException $e) {
		} catch (InvalidIpAddressException $e) {
		}

		if (Duration::isDuration($value))
			return new Duration($value);

		return "$value";

		// TODO: do ip address / network / mask / etc validation here (arrays?)
	}


}