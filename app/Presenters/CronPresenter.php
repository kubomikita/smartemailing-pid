<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\PidPayMethodModel;
use App\Models\PidPointModel;
use App\Models\PidPointOpeningHoursModel;
use App\Models\PidPointPayMethodModel;
use App\Models\PidPointServiceModel;
use App\Models\PidPointTypeModel;
use App\Models\PidServiceModel;
use App\Utils\Bits;
use GuzzleHttp\Client;
use Nette;


final class CronPresenter extends Nette\Application\UI\Presenter
{

	public function __construct(
		private readonly PidPointTypeModel $pidPointTypeModel,
		private readonly PidPayMethodModel $pidPayMethodModel,
		private readonly PidServiceModel $pidServiceModel,
		private readonly PidPointModel $pidPointModel,
		private readonly PidPointServiceModel $pidPointServiceModel,
		private readonly PidPointPayMethodModel $pidPointPayMethodModel,
		private readonly PidPointOpeningHoursModel $pidPointOpeningHoursModel,
		private readonly Nette\DI\Container $container
	)
	{
	}

	protected function startup()
	{
		if (PHP_SAPI !== 'cli') {
			$this->terminate();
		}
		parent::startup();
	}
	protected function beforeRender():void	{
		$this->terminate();
	}

	protected function log($message) : void
	{
		echo date("d.m.Y H:i:s") . "\t" . $message . "\n";
	}

	public function actionSyncPid(){
		$this->log("Starting syncing lists");
		try {
			$client = new Client();
			$result = $client->get($this->container->getParameter("pidListEndpoint"));

			$response = Nette\Utils\Json::decode($result->getBody()->getContents(), forceArrays: true);

			$pointTypes = $response["pointsOfSaleConsts"][0]["pointTypes"];
			if(!empty($pointTypes)) {
				$this->pidPointTypeModel->insertUpdate($pointTypes);
			}
			$payMethods = $response["pointsOfSaleConsts"][2]["payMethods"];
			if(!empty($pointTypes)) {
				$this->pidPayMethodModel->insertUpdate($payMethods);
			}

			$services = [];
			foreach ($response["pointsOfSaleConsts"][1]["serviceGroups"] as $serviceGroup){
				foreach ($serviceGroup["services"] as $service) {
					$services[] = $service + ["group" => $serviceGroup["desc"]];
				}

			}
			if(!empty($services)){
				$this->pidServiceModel->insertUpdate($services);
			}

		} catch (\Throwable $e){
			$this->log("An error occurred while syncing list. Error: ".$e->getMessage());
		}
		$this->log("Ended syncing lists");
		$this->log("-------------------");
		$this->log("Starting syncing points");

		try {
			$client = new Client();
			$result = $client->get($this->container->getParameter("pidPointEndpoint"));

			$response = Nette\Utils\Json::decode($result->getBody()->getContents(), forceArrays: true);
		} catch (\Throwable $e){
			$this->log("An error occurred while syncing points. Error: ".$e->getMessage());
		}

		if(isset($response)) {
			$pointTypes     = $this->pidPointTypeModel->findAll()->fetchPairs("name", "id");
			$payMethods     = $this->pidPayMethodModel->findAll()->fetchPairs("val", "id");
			$services       = $this->pidServiceModel->findAll()->fetchPairs("val", "id");
			$existingPoints = $this->pidPointModel->findAll()->fetchPairs("pid_id");

			$db = $this->pidPointModel->getDatabaseConnection();

			$pointIds = [];

			foreach ($response as $point) {
				$pointIds[] = $point["id"];
				try {
					$db->beginTransaction();

					$data = [
						"pid_id"            => $point["id"],
						"pid_point_type_id" => $pointTypes[$point["type"]],
						"name"              => $point["name"],
						"address"           => $point["address"],
						"lat"               => $point["lat"],
						"lon"               => $point["lon"],
						"link"              => $point["link"] ?? null,
						"remarks"           => $point["remarks"] ?? null,
					];

					if (isset($existingPoints[$point["id"]])) {
						/** @var Nette\Database\Table\ActiveRow $existRow */
						$existRow           = $existingPoints[$point["id"]];
						$exist              = $existRow->toArray();
						unset($exist["id"]);

						$diff = array_diff($data, $exist);
						if ( ! empty($diff)) {
							$existRow->update($diff);
						}
						$pointId   = $existRow->id;
					} else {
						$pointRow  = $this->pidPointModel->insert($data);
						$pointId   = $pointRow->id;
					}

					$pointPayMethods = [];
					foreach (Bits::getSetBits($point["payMethods"]) as $bit) {
						$pointPayMethods[] = ["pid_point_id" => $pointId, "pid_pay_method_id" => $payMethods[$bit]];
					}

					$this->pidPointPayMethodModel->findBy(["pid_point_id" => $pointId])->delete();
					if ( ! empty($pointPayMethods)) {
						$this->pidPointPayMethodModel->insert($pointPayMethods);
					}

					$pointServices = [];
					foreach (Bits::getSetBits($point["services"]) as $bit) {
						$pointServices[] = ["pid_point_id" => $pointId, "pid_service_id" => $services[$bit]];
					}

					$this->pidPointServiceModel->findBy(["pid_point_id" => $pointId])->delete();
					if ( ! empty($pointServices)) {
						$this->pidPointServiceModel->insert($pointServices);
					}


					$pointOpenningHours = [];
					if(!empty($point["openingHours"])) {
						foreach ($point["openingHours"] as $openingHour) {
							for ($i = $openingHour["from"]; $i <= $openingHour["to"]; $i++) {
								$hoursString = explode(",", $openingHour["hours"]);
								foreach ($hoursString as $hours) {
									$hours = str_replace("–", "-", $hours);
									list($startHour, $endHour) = explode("-", $hours);
									$pointOpenningHours[] = [
										"pid_point_id" => $pointId,
										"weekday"      => $i,
										"start_hour"   => $startHour,
										"end_hour"     => $endHour
									];
								}

							}
						}
					}

					$this->pidPointOpeningHoursModel->findBy(["pid_point_id" => $pointId])->delete();
					if ( ! empty($pointOpenningHours)) {
						$this->pidPointOpeningHoursModel->insert($pointOpenningHours);
					}

					$db->commit();
				} catch (\Throwable $e){
					$db->rollBack();
					$this->log("An error occurred while syncing point with id ". $point["id"].". Error: ".$e->getMessage());
				}
			}

			$this->pidPointModel->findBy(["pid_id NOT IN ?" => $pointIds])->delete();

		}

		$this->log("Ended syncing points");
		$this->terminate();
	}
}