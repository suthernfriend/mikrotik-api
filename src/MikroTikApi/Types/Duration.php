<?php


namespace MikroTikApi\Types;


use DateInterval;
use Exception;

class Duration {

	private static $regex = '/^(\d{1,2}w)?(\dd)?(\d{1,2}h)?(\d{1,2}m)?(\d{1,2}s)?$/m';

	/**
	 * @var int
	 */
	private $totalSeconds;

	/**
	 * @var DateInterval
	 */
	private $interval;

	/**
	 * @param string $value
	 * @return bool
	 */
	public static function isDuration(string $value): bool {
		if ($value === "")
			return false;

		$matches = self::match($value);

		return count($matches) == 1 && count($matches[0]) == 6;
	}

	/**
	 * @param string $value
	 * @return array
	 */
	private static function match(string $value): array {
		preg_match_all(self::$regex, $value, $matches, PREG_SET_ORDER, 0);
		return $matches;
	}

	/**
	 * Duration constructor.
	 * @param string $value
	 * @throws MikroTikTypeException
	 */
	public function __construct(string $value) {
		if (!self::isDuration($value))
			throw new MikroTikTypeException("Type is not a duration");

		$matches = self::match($value);
		$nums = [];
		for ($i = 1; $i < 6; $i++) {
			if (strlen($matches[0][$i]) > 0)
				$nums[$i - 1] = intval(substr($matches[0][$i], 0, -1));
			else
				$nums[] = 0;
		}

		list($weeks, $days, $hours, $minutes, $secs) = $nums;

		$this->totalSeconds = $weeks * 604800 + $days * 86400 + $hours * 3600 + $minutes * 60 + $secs;
		try {
			$this->interval = new DateInterval("PT" . $this->totalSeconds . "S");
		} catch (Exception $e) {
			// should never happen
			throw new MikroTikTypeException("???");
		}
	}

	/**
	 * @return int
	 */
	public function getSeconds() {
		return $this->totalSeconds;
	}

	/**
	 * @return DateInterval
	 */
	public function getInterval() {
		return $this->interval;
	}

	/**
	 * @return string
	 */
	public function toRouterOsInterval() {

		$weeks = intval($this->totalSeconds / 604800);
		$days = intval(($this->totalSeconds / 86400) % 7);
		$hours = intval(($this->totalSeconds / 3600) % 24);
		$minutes = intval(($this->totalSeconds / 60) % 60);
		$secs = intval(($this->totalSeconds % 60));

		$r = "";
		if ($weeks > 0)
			$r .= $weeks . "w";
		if ($days > 0)
			$r .= $days . "d";
		if ($hours > 0)
			$r .= $hours . "h";
		if ($minutes > 0)
			$r .= $minutes . "m";
		if ($r == "" || $secs > 0)
			$r .= $secs . "s";

		return $r;

	}
}