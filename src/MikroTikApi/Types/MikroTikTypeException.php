<?php

namespace MikroTikApi\Types;

use MikroTikApi\MikroTikConnectionException;
use Throwable;

/**
 * Class MikroTikTypeException
 * @package MikroTikApi\Types
 */
class MikroTikTypeException extends MikroTikConnectionException {

	/**
	 * MikroTikTypeException constructor.
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		return parent::__toString();
	}

}