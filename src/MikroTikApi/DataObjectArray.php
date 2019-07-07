<?php


namespace MikroTikApi;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;

class DataObjectArray implements ArrayAccess, IteratorAggregate, JsonSerializable, Countable {

	/**
	 * @var DataObject[]
	 */
	private $dataArray = [];

	/**
	 * DataObjectArray constructor.
	 */
	public function __construct() {
	}

	/**
	 * @param $name
	 * @return mixed
	 * @throws MikroTikConnectionException
	 */
	public function __get($name) {
		if (count($this->dataArray) !== 1)
			throw new MikroTikConnectionException("DataObjectArray size is not 1. Direct access not allowed");

		return $this->dataArray[0]->__get($name);
	}

	/**
	 * @param $name
	 * @param $value
	 * @throws MikroTikConnectionException
	 */
	public function __set($name, $value) {
		if (count($this->dataArray) !== 1)
			throw new MikroTikConnectionException("DataObjectArray size is not 1. Direct access not allowed");

		$this->dataArray[0]->__set($name, $value);
	}

	/**
	 * @return DataObject
	 * @throws MikroTikConnectionException
	 */
	public function getOne() {
		if (count($this->dataArray) !== 1)
			throw new MikroTikConnectionException("DataObjectArray->getOne() failed: size is not 1 but " . count($this->dataArray) . ".");

		return $this->dataArray[0];
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
	public function offsetExists($offset): bool {
		return isset($this->dataArray[$offset]);
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
	public function offsetGet($offset): DataObject {
		if (!$this->offsetExists($offset))
			throw new MikroTikConnectionException("Offset does not exist");

		return $this->dataArray[$offset];
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
	 * @throws MikroTikConnectionException
	 */
	public function offsetSet($offset, $value) {
		if (!($value instanceof DataObject))
			throw new MikroTikConnectionException("DataObjectArray can only contain DataObjects");

		$this->dataArray[$offset] = $value;
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
		unset($this->dataArray[$offset]);
	}

	/**
	 * Retrieve an external iterator
	 * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return ArrayIterator
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator() {
		return new ArrayIterator($this->dataArray);
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		$r = [];
		foreach ($this as $k => $v)
			$r[] = "$k = $v";
		return "{" . implode(", ", $r) . "}";
	}

	/**
	 * @param DataObject $dataObject
	 */
	public function append(DataObject $dataObject) {
		$this->dataArray[] = $dataObject;
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
		return $this->dataArray;
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
	public function count() {
		return count($this->dataArray);
	}
}