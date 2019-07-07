<?php


namespace MikroTikApi;

use PEAR2\Net\RouterOS as RouterOS;
use PEAR2\Net\RouterOS\Exception;
use Psr\Log\LoggerInterface;

class Connection {
	/**
	 * @var string
	 */
	private $host;
	/**
	 * @var string
	 */
	private $user;
	/**
	 * @var string
	 */
	private $password;
	/**
	 * @var int
	 */
	private $port;
	/**
	 * @var RouterOS\Client
	 */
	private $client = null;
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * Connection constructor.
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param int $port
	 */
	public function __construct(string $host, string $user, string $password, $port = 8728) {
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->port = $port;
	}

	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private static function convertDashToCamelCaseProperty(string $key) {
		if (substr($key, 0, 1) === '.')
			return self::convertDashToCamelCaseProperty(substr($key, 1));
		else
			return self::convertDashToCamelCase($key);
	}

	/**
	 * @param string $string
	 * @return string
	 */
	private static function convertDashToCamelCase(string $string) {
		return str_replace(" ", "", lcfirst(ucwords(str_replace("-", " ", $string))));
	}

	/**
	 * @param $prop string|string[]
	 * @return string|string[]
	 * @throws MikroTikConnectionException
	 */
	public static function convertCamelCaseToDash($prop) {
		$r = preg_replace('/[A-Z]{1}/', '-$0', $prop);
		if (is_string($r))
			return mb_strtolower($r, 'utf-8');
		else if (is_array($r)) {
			$n = [];
			foreach ($r as $p)
				$n[] = mb_strtolower($p, 'utf-8');
			return $n;
		} else
			throw new MikroTikConnectionException("preg replace failed");
	}

	/**
	 * @param string $path
	 * @return bool
	 */
	public static function verifyRequestPath(string $path): bool {
		$regex = '/^\/(([a-z0-9]+-)*([a-z0-9]+) )*(([a-z0-9]+-)*([a-z0-9]+))$/';

		$return = preg_match($regex, $path, $output);
		return !(!$return || count($output) < 1 || $output[0] !== $path);
	}

	/**
	 * @param string $camelCasedProperty
	 * @return string
	 * @throws MikroTikConnectionException
	 */
	public static function convertToRouterOSProperty(string $camelCasedProperty): string {
		$converted = self::convertCamelCaseToDash($camelCasedProperty);
		return self::propertyIsReadOnly($camelCasedProperty) ? ".$converted" : "$converted";
	}

	public static function propertyIsReadOnly(string $property): bool {
		return in_array($property, ["id", "dead", "nextid"]);
	}

	private static function needAllValuesAsArgumentsForAction($actionDashed): bool {
		switch ($actionDashed) {
			case 'add':
				return true;
			default:
				return false;
		}
	}

	/**
	 * Usage:
	 *
	 * execute($request) : usually print without conditions
	 * execute($request, DataObject::create(["id" => "*1234"])) : query: id, attributes: none
	 * execute($request, DataObject::create(["name" => "test-list"]) // query: name, attributes: none
	 *
	 * $x = execute($request, DataObject::create(["name" => "test-list"])
	 * $x->ip = '10.1.1.1'
	 * execute($request, $x); // query: id, attributes everything besides id
	 *
	 *
	 * @param Request $request
	 * @param DataObject|DataObjectArray|null $oneOrMoreObjects
	 * @return DataObjectArray
	 * @throws MikroTikConnectionException
	 */
	public function execute(Request $request, $oneOrMoreObjects = null): DataObjectArray {

		/** @var DataObject $object */
		$object = $oneOrMoreObjects;

		if ($object instanceof DataObjectArray)
			$object = $oneOrMoreObjects->getOne();

		// Prepare request
		$dashedPath = implode("/", self::convertCamelCaseToDash($request->getRequestPath()));
		$dashedAction = self::convertCamelCaseToDash($request->getAction());

		$command = "/$dashedPath/$dashedAction";
		$routerOsRequest = new RouterOS\Request($command);

		$debugArguments = [];
		$debugQuery = [];

		if ($object !== null && count($object) > 0) {

			$propertiesToBeUsed = $object->getChangedProperties();

			if ($object->hasProperty("id") && !isset($propertiesToBeUsed["id"])) {
				// existing object with id not changed but others changed
				foreach ($propertiesToBeUsed as $key => $value) {

					$routerOsKey = self::convertToRouterOSProperty($key);
					$routerOsValue = Types\TypeUtils::convertDataObjectToRouterOSValue($value);
					$this->logger->debug("Setting argument '$routerOsKey' for request '$command' to $routerOsValue");
					$routerOsRequest->setArgument($routerOsKey, $routerOsValue);
					$debugArguments[] = "$routerOsKey = $routerOsValue";
				}

				$id = $object->getProperty("id");
				$this->logger->debug("Setting argument 'numbers' for request '$command' to $id");
				$routerOsRequest->setArgument("numbers", Types\TypeUtils::convertDataObjectToRouterOSValue($id));
				$debugArguments[] = "numbers = $id";

			} else {
				// this is a create or filtered read

				/** @var RouterOS\Query $query */
				$query = null;

				if (self::needAllValuesAsArgumentsForAction($dashedAction)) {

					foreach ($object as $key => $value) {

						$routerOsKey = self::convertToRouterOSProperty($key);
						$routerOsValue = Types\TypeUtils::convertDataObjectToRouterOSValue($value);

						$this->logger->debug("Setting argument '$routerOsKey' for request '$command' to '$routerOsValue'");
						$debugArguments[] = "$routerOsKey=$routerOsValue";

						$routerOsRequest->setArgument($routerOsKey, $routerOsValue);
					}
				} else {

					foreach ($object as $key => $value) {

						$routerOsKey = self::convertToRouterOSProperty($key);
						$routerOsValue = Types\TypeUtils::convertDataObjectToRouterOSValue($value);

						$this->logger->debug("Setting query clause '$routerOsKey' for request '$command' to '$routerOsValue'");
						$debugQuery[] = "$routerOsKey=$routerOsValue";

						if ($query === null) {
							$query = RouterOS\Query::where($routerOsKey, $routerOsValue, RouterOS\Query::OP_EQ);
						} else {
							$query->andWhere($routerOsKey, $routerOsValue, RouterOS\Query::OP_EQ);
						}
					}

					$routerOsRequest->setQuery($query);
				}

			}
		}

		$this->ensureOpen();

		$this->logger->info("Sending Router OS request: " . $command . " with arguments: {" . implode(" ",
				$debugArguments) . "} and query: {" . implode(", ", $debugQuery) . "}");

		$response = $this->client->sendSync($routerOsRequest);

		// Parse Result into DataObjectArray

		/** @var array $propertyMap */
		$propertyMap = $response->getPropertyMap();

		$result = new DataObjectArray();

		foreach ($response as $row) {

			if ($row->getType() == RouterOS\Response::TYPE_FATAL) {
				throw new MikroTikConnectionException("Request '$request' failed with unknown error");
			} else
				if ($row->getType() == RouterOS\Response::TYPE_ERROR) {

					$errorKeys = array_keys($propertyMap);
					$errorValues = [];

					if (count($errorKeys) == 1)
						$errorString = $row->getProperty($errorKeys[0]);
					else {
						foreach ($errorKeys as $key)
							$errorValues[] = "$key = " . $row->getProperty($key);
						$errorString = implode(",", $errorValues);
					}

					throw new MikroTikConnectionException("Request '$request' failed with error: $errorString");
				} else if ($response->getType() == RouterOS\Response::TYPE_DATA) {

					$dataObject = new DataObject();

					foreach ($propertyMap as $key => $value) {


						$dataObjectKey = self::convertDashToCamelCaseProperty($key);
						$dataObjectValue = Types\TypeUtils::convertRouterOSToDataObjectValue($row->getProperty($key),
							$dataObjectKey);

						$dataObject->setProperty($dataObjectKey, $dataObjectValue, false);
					}

					$result->append($dataObject);
				}
		}

		if (count($result) > 1)
			$this->logger->info("Received response with " . count($result) . " items");
		else
			$this->logger->info("Received response single item: $result");

		if ($object !== null)
			$object->clearChangedProperties();

		return $result;
	}

	/**
	 * @return ConnectionAwareRequestBuilderObject
	 */
	public function createRequest() {
		return new ConnectionAwareRequestBuilderObject($this);
	}

	/**
	 * @throws MikroTikConnectionException
	 */
	private function ensureOpen() {
		if ($this->client === null) {
			try {
				$this->client = new RouterOS\Client($this->host, $this->user, $this->password, $this->port);
				$this->logger->info("Connected to " . $this->host . " with user " . $this->user);
			} catch (Exception $e) {
				throw new MikroTikConnectionException("Cannot open RouterOS connection: " . $e->getMessage(), 0, $e);
			}
		}
	}

}