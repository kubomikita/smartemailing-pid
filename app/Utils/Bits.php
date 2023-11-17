<?php

declare(strict_types=1);

namespace App\Utils;
final class Bits
{
	public static function getSetBits(int $mask): array
	{
		$bits = [];
		$scan = 1;
		while ($mask >= $scan) {
			if ($mask & $scan) {
				$bits[] = $scan;
			}
			$scan <<= 1;
		}
		return $bits;
	}
}