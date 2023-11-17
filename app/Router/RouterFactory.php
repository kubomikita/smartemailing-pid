<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\CliRouter;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;

		if (PHP_SAPI === "cli") {
			$router->add( new CliRouter(['presenter' => 'Cron']) );
		}

		$router->addRoute('<presenter>/<action>[/<id>]', 'Home:api');
		return $router;
	}
}
