<?php


namespace MikroTikApi\Types;

/**
 * Class MikroTikId
 * @package MikroTikApi\Types
 */
class MikroTikId {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @param $id
	 * @return bool
	 */
	public static function isMikroTikId($id): bool {
		return !!preg_match('/\*[0-9a-fA-F]+/', $id);
	}

	/**
	 * MikroTikId constructor.
	 * @param string $id
	 * @throws MikroTikTypeException
	 */
	public function __construct(string $id) {
		if (!self::isMikroTikId($id))
			throw new MikroTikTypeException("Not a MikroTik id");

		$this->id = hexdec(substr($id, 1));
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		return "*" . strtoupper(dechex($this->id));
	}
}