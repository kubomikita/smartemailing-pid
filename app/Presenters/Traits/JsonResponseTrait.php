<?php

declare(strict_types=1);

namespace App\Presenters\Traits;

trait JsonResponseTrait
{
	protected array $jsonData = [];
	protected function beforeRender()
	{
		parent::beforeRender();

		$this->getHttpResponse()->setCode($this->jsonData["statusCode"] ?? 200);
		$this->sendJson($this->jsonData);
	}
}