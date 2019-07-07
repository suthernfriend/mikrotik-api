<?php


namespace MikroTikApi;

use Psr\Log\LoggerInterface;

class ConnectionManager {
	/**
	 * @var array
	 */
	private $configuration;

	/**
	 * @var Connection[]
	 */

	private $connections = [];
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * ConnectionManager constructor.
	 * @param LoggerInterface $logger
	 * @param array $config
	 */
	public function __construct(LoggerInterface $logger, array $config) {
		$this->logger = $logger;
		$this->configuration = $config;
	}

	/**
	 * @param string $connectionIdentifier
	 * @return Connection
	 * @throws MikroTikConnectionException
	 */
	public function getConnection($connectionIdentifier = "") {

		// default connection is the first
		if ($connectionIdentifier === "") {
			$connectionKeys = array_keys($this->configuration);
			if (count($connectionKeys) == 0)
				throw new MikroTikConnectionException("No connection specified. Please add credentials to services.yaml");
			return $this->getConnection($connectionKeys[0]);
		} else {
			if (isset($this->configuration[$connectionIdentifier])) {
				$config = $this->configuration[$connectionIdentifier];

				if (!isset($this->connections[$connectionIdentifier])) {
					$newConnection = new Connection($config["host"], $config["user"], $config["password"]);
					$newConnection->setLogger($this->logger);
					$this->connections[$connectionIdentifier] = $newConnection;
				}

				return $this->connections[$connectionIdentifier];
			} else {
				throw new MikroTikConnectionException("Invalid connection '$connectionIdentifier'");
			}
		}
	}

	/**
	 * @param $name
	 * @return Connection
	 * @throws MikroTikConnectionException
	 */
	public function __get($name) {
		return $this->getConnection($name);
	}

	/**
	 * @param string $connectionIdentifier
	 * @param mixed ...$params
	 * @throws MikroTikConnectionException
	 */
	public function execute(string $connectionIdentifier = "", ...$params) {
		$this->getConnection($connectionIdentifier)->execute(...$params);
	}

	/**
	 * @param string $connectionIdentifier
	 * @return ConnectionAwareRequestBuilderObject
	 * @throws MikroTikConnectionException
	 */
	public function createRequest(string $connectionIdentifier = "") {
		return $this->getConnection($connectionIdentifier)->createRequest();
	}
}