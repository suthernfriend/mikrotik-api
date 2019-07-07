<?php


namespace MikroTikApi;

class RequestBuilderObject {

	/**
	 * @var string[]
	 */
	private $requestPath;

	/**
	 * Request constructor.
	 * @param string[] $requestPath
	 */
	public function __construct(array $requestPath = []) {
		$this->requestPath = $requestPath;
	}

	/**
	 * Generate the actual Request object using $name as the action to be executed
	 * Several actions are predefined
	 *
	 * @param string $action
	 * @param $arguments array ignored
	 * @return Request
	 */
	public function __call(string $action, array $arguments): Request {
		return new Request($this->requestPath, $action);
	}

	/**
	 * A clone is returned so RequestBuilderObjects can be temporary stored:
	 * p.e:
	 *
	 * ```
	 * $x = new RequestBuilderObject();
	 * $firewall = $x->ip->firewall;
	 *
	 * $connectionManager->execute($firewall->nat->add(), $natDataObject);
	 * $connectionManager->execute($firewall->filter->add(), $filterDataObject);
	 * ```
	 *
	 * @param string $name the name of the next deeper level of the MikroTik menu
	 * @return RequestBuilderObject
	 */
	public function __get(string $name): RequestBuilderObject {
		$newRequestPath = $this->requestPath;
		$newRequestPath[] = $name;
		return new RequestBuilderObject($newRequestPath);
	}

}