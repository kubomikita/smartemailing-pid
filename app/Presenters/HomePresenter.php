<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\PidPointModel;
use App\Presenters\Traits\JsonResponseTrait;
use Nette;


final class HomePresenter extends Nette\Application\UI\Presenter
{

	use JsonResponseTrait;

	public function __construct(private PidPointModel $pidPointModel)
	{
	}

	public function actionApi(bool $isOpen = false, ?string $dateTime = null){
		try {
			$dateTime = $dateTime !== null ? Nette\Utils\DateTime::from($dateTime) : new Nette\Utils\DateTime();
		} catch (\Throwable $e){
			$dateTime = new Nette\Utils\DateTime();
		}

		try {
			$this->jsonData["response"]           = "OK";
			$this->jsonData["statusCode"]         = 200;
			$this->jsonData["filter"]["isOpen"]   = $isOpen;
			$this->jsonData["filter"]["dateTime"] = $isOpen ? $dateTime : null;


			$selection = $this->pidPointModel->findAll();
			if($isOpen){
				$selection->where(
					"(SELECT COUNT(id) FROM pid_point_opening_hours WHERE weekday=? AND (TIME(start_hour) <= TIME(?) AND TIME(?) <= TIME(end_hour)) AND pid_point_id=pid_point.id) > 0",
					$dateTime->format("w"),
					$dateTime->format("H:i"),
					$dateTime->format("H:i")
				);
			}

			$this->jsonData["dataCount"]              = $selection->count("id");
			$this->jsonData["data"]               = [];

			foreach ($selection as $point){
				$this->jsonData["data"][] = $this->pidPointModel->toArray($point, $dateTime);
			}

		} catch (\Throwable $e){
			$this->jsonData = ["response" => 'An error occured during processing your request.', "statusCode" => $e->getCode() > 0 ? $e->getCode() : 500];
		}

	}

}
