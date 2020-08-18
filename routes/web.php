<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
use Intervention\Image\ImageManagerStatic as Image;

use Illuminate\Support\Facades\Cache;

$router->get('/', function () use ($router) {
    return $router->app->version();
});



$router->get('/redis', function () {
	Cache::flush();
});
$router->get('/todo', 'todoController@index');
$router->get('/key', 'todoController@apikey');
$router->get('/todo/{id}', 'todoController@show');
$router->post('/todo', 'todoController@store');
$router->get('/storage/{jenis}/{kd_perusahaan}/{tgl}/{filename}', function ($jenis,$kd_perusahaan,$tgl,$filename)
	{
		return Image::make(storage_path('app/public/'.$jenis.'/'.$kd_perusahaan.'/' .$tgl.'/'. $filename))->response();
	});

$router->group(['prefix' => 'api/v1'], function () use ($router) {
	
	//Perimeter
	$router->get('/perimeter/count/{id}', 'PerimeterController@getCountPerimeter');
	$router->get('/perimeter/map/{id}', 'PerimeterController@getPerimeterMap');
	$router->get('/perimeter/{id}', 'PerimeterController@getPerimeter');
	$router->get('/perimeter/region/{id}', 'PerimeterController@getPerimeterbyRegion');
	$router->get('/perimeter/user/{nik}', 'PICController@getPerimeterbyUser');
	
	//TaskForce
	$router->get('/taskforce/count/{id}', 'PerimeterController@getCountTaskForce');
	$router->get('/taskforce/{id}', 'PerimeterController@getTaskForce');
	$router->get('/taskforce/region/{id}', 'PerimeterController@getTaskForcebyRegion');
	$router->get('/taskforce/detail/{nik}', 'PerimeterController@getTaskForceDetail');
	
	
	
	//Cluster Ruangan
	$router->get('/cluster/perimeter/{id}', 'PerimeterController@getClusterbyPerimeter');
	$router->get('/cluster/perimeter/{id}/{nik}', 'PICController@getClusterbyPerimeter');

	
	//Protokol
	$router->get('/protokol/{id}', 'ProtokolController@protokol');
	$router->post('/protokol/upload', 'ProtokolController@uploadProtokol');
	$router->post('/protokol/upload_json', 'ProtokolController@uploadProtokolJSON');
	$router->get('/protokol/download/{kd_perusahaan}/{id_protokol}', 'ProtokolController@getDownloadFileProtokol');
	
	//Temporary Perimeter
    $router->get('/tmp_perimeter', 'TmpPerimeterController@index');
    $router->get('/parsingperimeter', 'TmpPerimeterController@parsingPerimeter');
	$router->post('/import', 'ImportController@import');
	
	//Data_detail
	$router->get('/terpapar/laporan_home/{id}', 'TerpaparController@getDataHome');
	$router->get('/terpapar/laporan_detail/{id}/{page}', 'TerpaparController@getDatadetail');
	
	
	
	//Cluster Aktifitas Ruangan
	$router->get('/cluster_aktfiktas_ruangan/getall/', 'CARuanganController@getAll');
	$router->get('/cluster_aktfiktas_ruangan/getbyid/{id}', 'CARuanganController@getById');
	$router->get('/cluster_aktfiktas_ruangan/create/', 'CARuanganController@CreateCARuangan');
	$router->get('/cluster_aktfiktas_ruangan/update/{id}', 'CARuanganController@UpdateCARuangan');
	$router->get('/cluster_aktfiktas_ruangan/delete/{id}', 'CARuanganController@DeleteCARuangan');
	
	//Sosialisasi
	$router->get('/sosialisasi/get_bymcid/{id}', 'SosialisasiController@getDataByMcid');
	$router->get('/sosialisasi/get_byid/{id}', 'SosialisasiController@getDataById');
	$router->post('/sosialisasi/upload_json', 'SosialisasiController@uploadSosialisasiJSON');
	$router->get('/sosialisasi/delete/{id}', 'SosialisasiController@deleteSosialisasi');
	$router->post('/sosialisasi/update_json/{id}', 'SosialisasiController@updateSosialisasiJSON');
	
	//PIC
	$router->post('/monitoring', 'PICController@updateDailyMonitoring');
	$router->post('/monitoring/file','PICController@updateMonitoringFile');
	$router->post('/validasi_monitoring', 'PICController@validasiMonitoring');
	$router->get('/monitoring/{nik}/{id_perimeter_cluster}', 'PICController@getAktifitasbyCluster');
	$router->get('/monitoring/perimeter/{nik}/{id_perimeter_level}', 'PICController@getAktifitasbyPerimeter');
	$router->get('/monitoring_detail/{id_aktifitas}', 'PICController@getMonitoringDetail');
	$router->get('/monitoring_detail/file/{id_file}', 'PICController@getFileByID');
	$router->get('/notif/{nik}', 'PICController@getNotifFO');
	
	
	//Execution
	$router->get('/report/execution/{id}', 'PerimeterController@getExecutionReport');
	Route::group(['middleware' => 'auth:api'], function () {
		//Data_User
		Route::get('/user/detail', 'UserController@getDetailUser');
		Route::post('/user/detail/{id}', 'UserController@updateDetailUser');
		Route::post('/user/change_password', 'UserController@change_password');
		Route::post('/user/logout', 'UserController@logout');
		Route::post('/user/detail_first/{id}', 'UserController@updateFirstDetailUser');
	});
	

	   
});