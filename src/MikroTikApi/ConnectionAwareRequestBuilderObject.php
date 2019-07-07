<?php


namespace MikroTikApi;


class ConnectionAwareRequestBuilderObject {

	/**
	 * @var RequestBuilderObject
	 */
	private $requestBuilderObject;
	/**
	 * @var Connection
	 */
	private $connection;

	/**
	 * ConnectionAwareRequestBuilderObject constructor.
	 * @param Connection $connection
	 */
	public function __construct(Connection $connection) {
		$this->requestBuilderObject = new RequestBuilderObject();
		$this->connection = $connection;
	}

	/**
	 * @param $name
	 * @return $this
	 */
	public function __get($name) {
		$this->requestBuilderObject = $this->requestBuilderObject->__get($name);
		return $this;
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return DataObjectArray
	 * @throws MikroTikConnectionException
	 */
	public function __call($name, $arguments) {
		$request = $this->requestBuilderObject->__call($name, []);
		return $this->connection->execute($request, ...$arguments);
	}

}