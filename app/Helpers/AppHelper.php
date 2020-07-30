<?php

namespace App\Helpers;

use Carbon\Carbon;

class AppHelper {
	
  public static function Weeks() {
	$now = Carbon::now();
	$startdate = $now->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
	$enddate = $now->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
	
	$data = [
		"startweek"=> $startdate,
		"endweek"=> $enddate,
		"weeks"=> $startdate.'-'.$enddate
		];  
		
    return $data;
  }
  

}