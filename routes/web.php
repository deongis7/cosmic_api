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
use Illuminate\Http\Request;

$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->get('/version', function () use ($router) {
    return  response()->json(['app' => env('APP_NAME', '1.0'),'version' => env('APP_VERSION', '1.0')]);;
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
$router->get('/storage/{jenis}/{filename}', function ($jenis,$filename)
{
    return Image::make(storage_path('app/public/'.$jenis.'/'. $filename))->response();
});

$router->group(['prefix' => 'api/v1'], function () use ($router) {
	//Perimeter
	$router->get('/perimeter/count/{id}', 'PerimeterController@getCountPerimeter');
	$router->get('/perimeter/map/{id}', 'PerimeterController@getPerimeterMap');
	$router->get('/perimeter/{id}', 'PerimeterController@getPerimeter');
	$router->get('/perimeter/region/{id}', 'PerimeterController@getPerimeterbyRegion');
	$router->get('/perimeter/user/{nik}', 'PICController@getPerimeterbyUser');
	$router->get('/perimeter/detail/{id_perimeter_level}', 'PerimeterController@getDetailPerimeter');
	$router->get('/perimeter/{kd_perusahaan}/kota/{id_kota}', 'PerimeterController@getPerimeterbyKota');
	$router->get('/perimeter_level/perimeter/{id_perimeter}', 'PerimeterController@getLevelbyPerimeter');
	$router->post('/perimeter_level/update', 'PerimeterController@updateDetailPerimeterLevel');
	$router->post('/perimeter/update', 'PerimeterListController@updateDetailPerimeter');
	$router->post('/perimeter_closed/add', 'PerimeterListController@addClosedPerimeter');
	$router->post('/perimeter_closed/validasi', 'PerimeterListController@validasiClosedPerimeter');



	//TaskForce
	$router->get('/taskforce/count/{id}', 'PerimeterController@getCountTaskForce');
	$router->get('/taskforce/{id}', 'PerimeterController@getTaskForce');
	$router->get('/taskforce/region/{id}', 'PerimeterController@getTaskForcebyRegion');
	$router->get('/taskforce/detail/{nik}', 'PerimeterController@getTaskForceDetail');
	$router->get('/taskforce/detail_user/{nik}', 'PerimeterController@getTaskForceDetailUser');
	$router->post('/taskforce/add', 'PerimeterController@addTaskForce');
	$router->post('/taskforce/change_password/{nik}', 'PerimeterController@changePasswordTaskForce');
	$router->get('/taskforce/reset_password/{nik}', 'PerimeterController@resetPasswordTaskForce');

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
	$router->get('/terpapar/laporan_detail/{id}/{page}/{search}', 'TerpaparController@getDatadetail');
	$router->get('/terpapar/byid/{id}', 'TerpaparController@getDataByid');

	$router->get('/terpapar/laporan_home_all', 'TerpaparController@getDataHomeAll');
	$router->get('/terpapar/dashkasus_company_bymskid/{id}', 'TerpaparController@getDashboardCompanybyMskid');
	$router->get('/terpapar/dashkasus_provinsi_bymskid/{id}', 'TerpaparController@getDashboardProvinsibyMskid');
	$router->get('/terpapar/dashkasus_kabupaten_bymskid/{id}', 'TerpaparController@getDashboardKabupatenbyMskid');



	//Cluster Aktifitas Ruangan
	$router->get('/cluster_aktfiktas_ruangan/getall/', 'CARuanganController@getAll');
	$router->get('/cluster_aktfiktas_ruangan/getbyid/{id}', 'CARuanganController@getById');
	$router->get('/cluster_aktfiktas_ruangan/create/', 'CARuanganController@CreateCARuangan');
	$router->get('/cluster_aktfiktas_ruangan/update/{id}', 'CARuanganController@UpdateCARuangan');
	$router->get('/cluster_aktfiktas_ruangan/delete/{id}', 'CARuanganController@DeleteCARuangan');

	//Sosialisasi
	$router->get('/sosialisasi/get_bymcid/{id}/{page}', 'SosialisasiController@getDataByMcid');
	$router->get('/sosialisasi/get_byid/{id}', 'SosialisasiController@getDataById');

	$router->post('/sosialisasi/upload_json', 'SosialisasiController@uploadSosialisasiJSON');
	$router->get('/sosialisasi/delete/{id}', 'SosialisasiController@deleteSosialisasi');
	$router->post('/sosialisasi/update_json/{id}', 'SosialisasiController@updateSosialisasiJSON');
	$router->get('/sosialisasi/get_last2/{id}', 'SosialisasiController@getDataLast2ByMcid');


	//PIC
	$router->post('/monitoring', 'PICController@updateDailyMonitoring');
	$router->post('/monitoring/file','PICController@updateMonitoringFile');
	$router->post('/validasi_monitoring', 'PICController@validasiMonitoring');
	$router->get('/monitoring/{nik}/{id_perimeter_cluster}', 'PICController@getAktifitasbyCluster');
	$router->get('/monitoring/perimeter/{nik}/{id_perimeter_level}', 'PICController@getAktifitasbyPerimeter');
	$router->get('/monitoring_detail/{id_aktifitas}', 'PICController@getMonitoringDetail');
	$router->get('/monitoring_detail/file/{id_file}', 'PICController@getFileByID');
	$router->get('/notif/{nik}', 'PICController@getNotifFO');

	//PerimeterList
    $router->get('/list_perimeter_level/perimeter/{id_perimeter}', 'PerimeterListController@getPerimeterLevelListbyPerimeter');
    $router->get('/list_perimeter/{kd_perusahaan}', 'PerimeterListController@getPerimeterList');
    $router->get('/list_perimeter_level/count/{kd_perusahaan}', 'PerimeterListController@getStatusPerimeterLevel');
    $router->get('/list_perimeter/detail/{id_perimeter}', 'PerimeterListController@getPerimeterDetail');
    $router->get('/list_perimeter/region/{id}', 'PerimeterListController@getPerimeterListbyRegion');

    //Region
    $router->get('/region/{kd_perusahaan}', 'PerimeterListController@getRegionList');

	//Kota
	$router->get('/kota', 'MasterController@getAllKota');
	$router->get('/kota/{id_provinsi}', 'MasterController@getKotaByProvinsi');
	$router->get('/provinsi', 'MasterController@getAllProvinsi');

    //Master
	$router->get('/stskasus', 'MasterController@getAllStsKasus');
	$router->get('/stspegawai', 'MasterController@getAllStsPegawai');
	$router->get('/sosialisasikategori', 'MasterController@getAllSosialisasiKategori');
	$router->get('/perimeterkategori', 'MasterController@getKategoriPerimeter');
	$router->get('/clusterruangan', 'MasterController@getClusterRuangan');

	//Company
	$router->get('/company', 'MasterController@getAllCompany');
	$router->get('/company/detail/{id}', 'MasterController@getDetailCompany');
	$router->post('/company/upload_foto', 'MasterController@uploadFotoBUMN');


	//Dashboard
	$router->get('/dashboard/cosmicindex', 'DashboardController@getCosmicIndexAll');
	$router->get('/dashboard/perimeter_bykategori_all', 'DashboardController@getPerimeterbyKategoriAll');
	$router->get('/dashboard/perimeter_byprovinsi_all', 'DashboardController@getPerimeterbyProvinsiAll');
	$router->get('/dashboard/dashboardhead', 'DashboardController@getDashboardHead');
	$router->get('/dashboard/list_week', 'DashboardController@getWeekList');
	$router->get('/dashboard/monitoring_bymciddate/{id}/{tgl}', 'DashboardController@getMonitoring_ByMcidWeek');
	$router->get('/dashboard/listmonitoring_bymciddate/{id}/{tgl}', 'DashboardController@getListMonitoring_ByMcidWeek');
	$router->get('/dashboard/cosmic_index_report', 'DashboardController@getCosmicIndexReport');
	$router->get('/dashboard/cosmic_index_detail/{kd_perusahaan}', 'DashboardController@getCosmicIndexbyCompanyAndDate');

	//Materialized View
	$router->get('/dashboard/refresh_mv_rangkumanall/', 'DashboardController@RefreshMvRangkumanAll');

	//Execution
	$router->get('/report/execution/{id}', 'PerimeterController@getExecutionReport');
	$router->get('/dashboard/dashboardhead_bumn/{id}', 'DashboardController@getDashboardHeadBUMN');
	$router->get('/dashboard/dashboardprotokol_bumn/{id}', 'DashboardController@getDashboardProtokolBUMN');
	$router->get('/dashboard/dashboardmrmpm_bumn/{id}', 'DashboardController@getDashboardMrMpmBUMN');
    //Log
    $router->get('/log_activity', 'UserController@setActivityLog');

    //Sosialisasi Web
    Route::post('/sosialisasi/webupload_json/{user_id}', 'SosialisasiController@WebuploadSosialisasiJSON');
    Route::post('/sosialisasi/webupdate_json/{user_id}/{id}', 'SosialisasiController@WebupdateSosialisasiJSON');

	Route::group(['middleware' => 'auth:api'], function () {
		//Data_User
		Route::get('/user/detail', 'UserController@getDetailUser');
		Route::post('/user/detail/{id}', 'UserController@updateDetailUser');
		Route::post('/user/change_password', 'UserController@change_password');
		Route::post('/user/logout', 'UserController@logout');
		Route::post('/user/detail_first/{id}', 'UserController@updateFirstDetailUser');

		Route::post('/terpapar/add', 'TerpaparController@InsertKasus');
		Route::post('/terpapar/update/{id}', 'TerpaparController@UpdateKasus');
        Route::delete('/terpapar/delete/{id_kasus}', 'TerpaparController@deleteKasus');


		Route::post('/sosialisasi/upload_json', 'SosialisasiController@uploadSosialisasiJSON');
		Route::post('/sosialisasi/update_json/{id}', 'SosialisasiController@updateSosialisasiJSON');
	});



});
