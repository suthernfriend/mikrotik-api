<?php


namespace MikroTikApi;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use MikroTikApi\Types\TypeUtils;
use ArrayIterator;

class DataObject implements ArrayAccess, IteratorAggregate, Countable, JsonSerializable {

	public static function create(array $data) {
		$dataObject = new DataObject();
		foreach ($data as $key => $value)
			$dataObject->setProperty($key, $value);
		return $dataObject;
	}

	/**
	 * @var mixed[]
	 */
	private $properties = [];

	/**
	 * @var string[]
	 */
	private $changedProperties = [];

	/**
	 * @param $name
	 * @param $value
	 * @param bool $isAnActualChange
	 */
	public function setProperty($name, $value, $isAnActualChange = true) {
		$this->properties[$name] = $value;
		if ($isAnActualChange && !in_array($name, $this->changedProperties))
			$this->changedProperties[] = $name;
	}

	/**
	 * @return string[]
	 */
	public function getChangedProperties() {
		$r = [];
		foreach ($this->changedProperties as $key)
			$r[$key] = $this->properties[$key];
		return $r;
	}

	/**
	 *
	 */
	public function clearChangedProperties() {
		$properties = $this->properties;
		$this->properties = [];
		foreach ($properties as $k => $v)
			$this->setProperty($k, $v, false);
	}

	/**
	 * @param $name
	 * @return bool
	 */
	public function hasProperty($name): bool {
		return isset($this->properties[$name]);
	}

	/**
	 * @param $name
	 * @return mixed
	 * @throws MikroTikConnectionException
	 */
	public function getProperty($name) {
		if (!$this->hasProperty($name))
			throw new MikroTikConnectionException("Unknown property '$name'");
		return $this->properties[$name];
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value) {
		return $this->setProperty($name, $value);
	}

	/**
	 * @param $name
	 * @return mixed
	 * @throws MikroTikConnectionException
	 */
	public function __get($name) {
		return $this->getProperty($name);
	}

	/**
	 * Retrieve an external iterator
	 * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return ArrayIterator
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator() {
		return new ArrayIterator($this->properties);
	}

	/**
	 * Whether a offset exists
	 * @link https://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	public function offsetExists($offset) {
		return $this->hasProperty($offset);
	}

	/**
	 * Offset to retrieve
	 * @link https://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 * @return mixed Can return all value types.
	 * @since 5.0.0
	 * @throws MikroTikConnectionException
	 */
	public function offsetGet($offset) {
		return $this->getProperty($offset);
	}

	/**
	 * Offset to set
	 * @link https://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetSet($offset, $value) {
		$this->setProperty($offset, $value);
	}

	/**
	 * Offset to unset
	 * @link https://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset($offset) {
		unset($this->properties[$offset]);
	}

	/**
	 * Count elements of an object
	 * @link https://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * </p>
	 * <p>
	 * The return value is cast to an integer.
	 * @since 5.1.0
	 */
	public function count(): int {
		return count($this->properties);
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 * @throws Types\MikroTikTypeException
	 */
	public function jsonSerialize() {
		$r = [];
		foreach ($this->properties as $name => $property) {
			$r[$name] = TypeUtils::convertDataObjectToJsonValue($property);
		}
		return $r;
	}

	/**
	 * @return string
	 * @throws Types\MikroTikTypeException
	 */
	public function __toString(): string {
		return json_encode($this->jsonSerialize());
	}
}