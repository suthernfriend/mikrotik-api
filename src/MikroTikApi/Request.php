<?php


namespace MikroTikApi;


class Request {
	/**
	 * @var string[]
	 */
	private $requestPath;

	/**
	 * @var string
	 */
	private $action;

	/**
	 * Request constructor.
	 * @param string[] $requestPath
	 * @param string $action
	 */
	public function __construct(array $requestPath, string $action) {
		$this->requestPath = $requestPath;
		$this->action = $action;
	}

	/**
	 * @return string
	 */
	public function getAction(): string {
		return $this->action;
	}


	/**
	 * @return string[]
	 */
	public function getRequestPath() {
		return $this->requestPath;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return "path: " . implode("/", $this->requestPath) . ", action: " . $this->action;
	}
}