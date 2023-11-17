<?php

declare(strict_types=1);

namespace App\Router;

use Nette;

final class CliRouter implements Nette\Routing\Router
{
	use Nette\SmartObject;

	private const PresenterKey = 'action';

	public function match(Nette\Http\IRequest $httpRequest): ?array
	{
		if (empty($_SERVER['argv']) || !is_array($_SERVER['argv'])) {
			return null;
		}

		$names = [self::PresenterKey];
		$args = $_SERVER['argv'];
		array_shift($args);
		$args[] = '--';

		foreach ($args as $arg) {
			$opt = preg_replace('#/|-+#A', '', $arg);
			if ($opt === $arg) {
				if (isset($flag) || $flag = array_shift($names)) {
					$params[$flag] = $arg;
				} else {
					$params[] = $arg;
				}

				$flag = null;
				continue;
			}

			if (isset($flag)) {
				$params[$flag] = true;
				$flag = null;
			}

			if ($opt === '') {
				continue;
			}

			$pair = explode('=', $opt, 2);
			if (isset($pair[1])) {
				$params[$pair[0]] = $pair[1];
			} else {
				$flag = $pair[0];
			}
		}

		if (!isset($params[self::PresenterKey])) {
			throw new Nette\InvalidStateException('Missing presenter & action in route definition.');
		}

		[$module, $presenter] = Nette\Application\Helpers::splitName($params[self::PresenterKey]);
		if ($module !== '') {
			$params[self::PresenterKey] = $presenter;
			$presenter = $module;
		}

		$params['presenter'] = $presenter;

		return $params;
	}

	public function constructUrl(array $params, Nette\Http\UrlScript $refUrl): ?string
	{
		return null;
	}
}
