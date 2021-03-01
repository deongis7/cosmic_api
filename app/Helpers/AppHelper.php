<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\LogActivityCRUD;
use GuzzleHttp\Client;
class AppHelper {

  public static function Weeks() {
	$now = Carbon::now();
	$startdate = $now->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
	$enddate = $now->endOfWeek(Carbon::FRIDAY)->format('Y-m-d');
	$last_month = $now->subMonth(1)->format('Y-m-d');
	$three_month = $now->subMonth(3)->format('Y-m-d');
	$six_month = $now->subMonth(6)->format('Y-m-d');
	$last_year = $now->subMonth(12)->format('Y-m-d');

	$data = [
		"startweek"=> $startdate,
		"endweek"=> $enddate,
		"last_month"=> $last_month,
		"three_month"=> $three_month,
		"six_month"=> $six_month,
		"last_year"=> $last_year,
		"weeks"=> $startdate.'-'.$enddate
		];

    return $data;
  }

    public static   function setActivityLog($modul, $modul_id, $action, $description, $username)
    {
        $dataActivity = new LogActivityCRUD();
        // Creste Data Activity Log

        $dataActivity->lac_modul= strtolower($modul);
        $dataActivity->lac_modul_id = $modul_id;
        $dataActivity->lac_jns_aktivitas = strtolower($action);
        $dataActivity->lac_dtl_aktivitas = 'User '.strtolower($username) . ' ' . $description;
        $dataActivity->lac_username = $username;
//dd($dataActivity);
        // Update Data Activity Log
        if($dataActivity->save()){
            return true;
        } else {
            return false;
        }
    }

//Ubah format Kalimat-Judul
    function strtotitle($title) {
        $smallwordsarray = array( 'of','a','the','and','an','or','nor','but','is','if','then','else','when', 'at','from','by','on','off','for','in','to','into','with','it', 'as','dari','oleh','dengan','telah','data' );
        $words = $temp = explode(' ', strip_tags($title));

        foreach ($temp as $key => $word) {
            if ($key == 0 or !in_array($word, $smallwordsarray)) $temp[$key] = ucwords($word);
        }

        foreach($words as $index => $word) $title = str_replace($word, $temp[$index], $title);
        return $title;
    }

    public static function sendFirebase($token_device, $body, $title){
        
        $token = "AAAAIOJgA7s:APA91bGsiFlggeNexu_qv7QdxyEKeudNqJatbkZaMkMjI9dKJHjPDcQQdXOeCmlGiDsepZ2HkuLCFxzU6DiYMxn-2ZoueHFnGNTXlwY4krhF9HZ207WocMTamycUzk_vMQsz6wlLvasW";
        $headers = [
            'Authorization' => 'Key=' . $token,
            'Accept'        => 'application/json',
            'Content-Type' => 'application/json'
        ];

        // $token_device = "testtoken";
        $data_param = [
            "to" => $token_device,
            "notification" => [
                  "body" => $body,
                  "title" =>  $title
              ]
          ];

        $header_params = json_encode($data_param);
        // print_r($header_params);
        $client    = new Client();
        $request = $client->request('POST', 'https://fcm.googleapis.com/fcm/send', [
                  'headers' => $headers,
                  'body' => $header_params
            ]);

        $response = $request->getBody()->getContents();
        $result   = json_decode($response, true);
        return response()->json($result, 204);
        // dd($result);
        // return response()->json(['status' => 200,'data' => $result]);
        
    }
}
