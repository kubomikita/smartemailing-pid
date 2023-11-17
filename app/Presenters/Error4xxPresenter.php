<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


/**
 * Handles 4xx HTTP error responses.
 */
final class Error4xxPresenter extends Nette\Application\UI\Presenter
{
	protected function checkHttpMethod(): void
	{
		// allow access via all HTTP methods and ensure the request is a forward (internal redirect)
		if (!$this->getRequest()->isMethod(Nette\Application\Request::FORWARD)) {
			$this->error();
		}
	}


	public function renderDefault(Nette\Application\BadRequestException $exception): void
	{
		$reflection = new \ReflectionClass($exception);
		$this->sendJson(["response" => $reflection->getShortName(), "statusCode" => $exception->getCode()]);
	}
}
