<?php
declare(strict_types=1);
namespace App\Models;


use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

final class PidPointModel extends BaseModel {
	protected static ?string $tableName = "pid_point";


	public function toArray(ActiveRow $point, DateTime $dateTime) : array
	{
		$data = $point->toArray();
		unset($data["pid_point_type_id"]);
		$data["point_type"] = $point->pid_point_type->desc;

		foreach ($point->related("pid_point_pay_method") as $item){
			$data["pay_methods"][] = $item->pid_pay_method->desc;
		}

		foreach ($point->related("pid_point_service") as $item){
			$data["services"][] = $item->pid_service->group ." - ". $item->pid_service->desc;
		}

		$data["isOpen"] = false;

		foreach ($point->related("pid_point_opening_hours")->where(["weekday" => $dateTime->format("w")]) as $openningHour){
			$startDate = $dateTime->modifyClone($openningHour["start_hour"]->format("%H:%I"));
			$endDate = $dateTime->modifyClone($openningHour["end_hour"]->format("%H:%I"));
			if($startDate <= $dateTime && $dateTime <= $endDate){
				$data["isOpen"] = true;
				break;
			}
		}

		return $data;
	}

}