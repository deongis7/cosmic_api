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

$router->get('/storage/{jenis}/{kd_perusahaan}/{filename}', function ($jenis,$kd_perusahaan,$filename)
{
    return Image::make(storage_path('app/public/'.$jenis.'/'.$kd_perusahaan.'/'. $filename))->response();
});

$router->get('/storage/{jenis}/{kd_perusahaan}/id-{id}/{filename}', function ($jenis,$kd_perusahaan,$id,$filename)
{
    return Image::make(storage_path('app/public/'.$jenis.'/'.$kd_perusahaan.'/'.$id.'/'. $filename))->response();
});

$router->get('/download/template/{filename}', function ($filename)
{
    $path = storage_path('app/public/protokol/example/' . $filename);

    // Download file with custom headers
    return response()->download($path, $filename, [
        'Content-Type' => 'application/vnd.ms-excel',
        'Content-Disposition' => 'inline; filename="' . $filename . '"'
    ]);
});

$router->group(['prefix' => 'api/v1'], function () use ($router) {
    $router->get('/product/card_perimeter_pl/{id}', 'ProductController@getCardPerimeterQR');
    $router->post('/perimeter_pedulilindungi/update/{id}', 'ProductController@updatePerimeterPL');
    $router->post('/perimeter_pedulilindungi/insert', 'ProductController@insertPerimeterPL');
    $router->get('/perimeterpl_bymcid/{id}', 'ProductController@PerimeterPLByMcid');
    $router->get('/perimeterpl_byid/{id}', 'ProductController@PerimeterPLByid');
	//Perimeter
    $router->get('/report/readiness/{id}', 'PerimeterController@getReadinessIndex');
    $router->get('/dashboard/readiness/{id}', 'DashboardController@getReadinessIndexbyCompany');

    $router->get('/perimeter/count/{id}', 'PerimeterController@getCountPerimeter');
	$router->get('/perimeter/map/{id}', 'PerimeterController@getPerimeterMap');
	$router->get('/perimeter/{id}', 'PerimeterController@getPerimeter');
	$router->get('/perimeter/region/{id}', 'PerimeterController@getPerimeterbyRegion');
	$router->get('/perimeter/user/{nik}', 'PICController@getPerimeterbyUser');
	$router->get('/perimeter/detail/{id_perimeter_level}', 'PerimeterController@getDetailPerimeter');
	$router->get('/perimeter/{kd_perusahaan}/kota/{id_kota}', 'PerimeterController@getPerimeterbyKota');
	$router->get('/perimeter_level/perimeter/{id_perimeter}', 'PerimeterController@getLevelbyPerimeter');
	$router->post('/perimeter_level/update', 'PerimeterController@updateDetailPerimeterLevel');
	$router->post('/perimeter_level/add', 'PerimeterListController@addDetailPerimeter');
	$router->post('/perimeter_level/add_file', 'PICController@addFilePerimeterLevel');
    $router->get('/perimeter_level/get_file_by_id/{id_file}', 'PICController@getFilePerimeterLevelByID');
    $router->get('/perimeter_level/get_file/{id_perimeter_level}', 'PICController@getFilePerimeterLevelByPerimeterLevel');
	$router->post('/perimeter/update', 'PerimeterListController@updateDetailPerimeter');
//	$router->post('/perimeter_closed/add', 'PerimeterListController@addClosedPerimeter');
    $router->post('/perimeter_closed/add', function () use ($router) {
      return response()->json(['status' => 500,'message' => 'Fitur saat ini ditutup sementara'])->setStatusCode(500);
    });
	$router->post('/perimeter_closed/validasi', 'PerimeterListController@validasiClosedPerimeter');
	$router->post('/perimeter_closed/addActivity', 'PerimeterListController@updateAktifitasClosedPerimeter');  //force add for actifity closed perimeter
	$router->post('/perimeter_open/add', 'PerimeterListController@openPerimeter');

	//TaskForce
	$router->get('/taskforce/count/{id}', 'PerimeterController@getCountTaskForce');
	$router->get('/taskforce/{id}', 'PerimeterController@getTaskForce');
	$router->get('/taskforce2/{id}', 'PerimeterController@getTaskForce2');
	$router->get('/taskforce/region/{id}', 'PerimeterController@getTaskForcebyRegion');
	$router->get('/taskforce/detail/{nik}', 'PerimeterController@getTaskForceDetail');
	$router->get('/taskforce/detail_user/{nik}', 'PerimeterController@getTaskForceDetailUser');
	$router->post('/taskforce/add', 'PerimeterController@addTaskForce');
	$router->post('/taskforce/change_password/{nik}', 'PerimeterController@changePasswordTaskForce');
	$router->get('/taskforce/reset_password/{nik}', 'PerimeterController@resetPasswordTaskForce');
	$router->get('/taskforce/delete/{nik}', 'PerimeterController@deleteTaskForce');


	//Cluster Ruangan
	$router->get('/cluster/perimeter/{id}', 'PerimeterController@getClusterbyPerimeter');
	$router->get('/cluster/perimeter/{id}/{nik}', 'PICController@getClusterbyPerimeter');
	$router->get('/cluster/get_file/{id_perimeter_cluster}', 'PICController@getFileClusterRuanganByID');
	$router->post('/cluster/add_file', 'PICController@addFileClusterRuangan');

	//Protokol
	$router->get('/protokol/{id}', 'ProtokolController@protokol');
	$router->post('/protokol/upload', 'ProtokolController@uploadProtokol');
	$router->post('/protokol/upload_json', 'ProtokolController@uploadProtokolJSON');
	$router->get('/protokol/download/{kd_perusahaan}/{id_protokol}', 'ProtokolController@getDownloadFileProtokol');

	//Temporary Perimeter
    $router->get('/tmp_perimeter', 'TmpPerimeterController@index');
    $router->get('/parsingperimeter', 'TmpPerimeterController@parsingPerimeter');
	$router->post('/import', 'ImportController@import');

	//Data_detail /Terpapar /Kasus
	$router->get('/terpapar/laporan_home/{id}', 'TerpaparController@getDataHome');

	$router->get('/terpapar/laporan_detail_new/{id}', 'TerpaparController@getDatadetailNew');
	$router->get('/terpapar/byid/{id}', 'TerpaparController@getDataByid');

	$router->get('/terpapar/laporan_home_all', 'TerpaparController@getDataHomeAll');
	$router->get('/terpapar/dashkasus_company_bymskid/{id}', 'TerpaparController@getDashboardCompanybyMskid');
	$router->get('/terpapar/dashkasus_provinsi_bymskid/{id}', 'TerpaparController@getDashboardProvinsibyMskid');
	$router->get('/terpapar/dashkasus_kabupaten_bymskid/{id}', 'TerpaparController@getDashboardKabupatenbyMskid');
	$router->get('/terpapar/dashkasus_companymobile_bymskid/{id}', 'TerpaparController@getDashboardCompanyMobilebyMskid');


	$router->get('/terpapar/cluster_laporan_home_all/{id}', 'TerpaparController@getClusterDataHomeAll');
	$router->get('/terpapar/dashclusterkasus_company_bymskid/{id}/{msc_id}', 'TerpaparController@getClusterDashboardCompanybyMskid');
	$router->get('/terpapar/dashclusterkasus_provinsi_bymskid/{id}/{msc_id}', 'TerpaparController@getClusterDashboardProvinsibyMskid');
	$router->get('/terpapar/dashclusterkasus_kabupaten_bymskid/{id}/{msc_id}', 'TerpaparController@getClusterDashboardKabupatenbyMskid');

	//Cluster Aktifitas Ruangan
	$router->get('/cluster_aktfiktas_ruangan/getall/', 'CARuanganController@getAll');
	$router->get('/cluster_aktfiktas_ruangan/getbyid/{id}', 'CARuanganController@getById');
	$router->get('/cluster_aktfiktas_ruangan/create/', 'CARuanganController@CreateCARuangan');
	$router->get('/cluster_aktfiktas_ruangan/update/{id}', 'CARuanganController@UpdateCARuangan');
	$router->get('/cluster_aktfiktas_ruangan/delete/{id}', 'CARuanganController@DeleteCARuangan');

	//Sosialisasi
	$router->get('/sosialisasi/get_bymcid/{id}/{page}', 'SosialisasiController@getDataByMcid');
	$router->get('/sosialisasi/get_byid/{id}', 'SosialisasiController@getDataById');
	$router->get('/sosialisasi/download/{kd_perusahaan}/{filename}', 'SosialisasiController@getDownloadFileSosialisasi');

	$router->post('/sosialisasi/upload_json', 'SosialisasiController@uploadSosialisasiJSON');
	$router->get('/sosialisasi/delete/{id}', 'SosialisasiController@deleteSosialisasi');
	$router->post('/sosialisasi/update_json/{id}', 'SosialisasiController@updateSosialisasiJSON');
	$router->get('/sosialisasi/get_last2/{id}', 'SosialisasiController@getDataLast2ByMcid');
    $router->get('/sosialisasi/get_perusahaan_all', 'DashboardController@getEventbyPerusahaanAll');
    $router->get('/sosialisasi/total_perusahaan_all', 'DashboardController@countEventbyPerusahaanAll');

    //EID
    $router->get('/sosialisasi/sosialisasiraw', 'SosialisasiController@getSosialisasiRaw');
    //$router->get('/vaksin/vaksinraw', 'DashVaksinController@getVaksinRaw');
    $router->get('/vaksin/vaksinraw', function () use ($router) {
        return response()->json(['status' => 500,'message' => 'Data tidak dapat diakses'])->setStatusCode(500);
    });
    $router->get('/terpapar/terpaparraw', 'TerpaparController@getTerpaparRaw');

	//PIC
	$router->post('/monitoring', 'PICController@updateDailyMonitoring');
	$router->post('/monitoring/file','PICController@updateMonitoringFile');
	$router->post('/validasi_monitoring', 'UserController@validasiMonitoring');
	$router->get('/monitoring/{nik}/{id_perimeter_cluster}', 'PICController@getAktifitasbyCluster');
	$router->get('/monitoring/perimeter/{nik}/{id_perimeter_level}', 'PICController@getAktifitasbyPerimeter');
	$router->get('/monitoring_detail/{id_aktifitas}', 'PICController@getMonitoringDetail');
	$router->get('/monitoring_detail/file/{id_file}', 'PICController@getFileByID');
	$router->get('/notif/{nik}', 'PICController@getNotifFO');

	//PerimeterList
    $router->get('/list_perimeter_level/perimeter/{id_perimeter}', 'PerimeterListController@getPerimeterLevelListbyPerimeter');
    $router->get('/list_perimeter/{kd_perusahaan}', 'PerimeterListController@getPerimeterList');
    $router->get('/list_perimeter_all', 'PerimeterListController@getPerimeterListAll');
    $router->get('/list_perimeter_level/count/{kd_perusahaan}', 'PerimeterListController@getStatusPerimeterLevel');
    $router->get('/list_perimeter/detail/{id_perimeter}', 'PerimeterListController@getPerimeterDetail');
    $router->get('/list_perimeter/region/{id}', 'PerimeterListController@getPerimeterListbyRegion');
    $router->get('/list_perimeter_report/{kd_perusahaan}', 'PerimeterReportController@getPerimeterList');
    $router->get('/list_perimeter_level_report/perimeter/{id_perimeter}', 'PerimeterReportController@getPerimeterLevelListbyPerimeter');
    $router->get('/list_perimeter_level_report/count/{kd_perusahaan}', 'PerimeterReportController@getStatusPerimeterLevel');
    $router->post('/list_perimeter/add', 'PerimeterListController@addPerimeterList');
    $router->post('/list_perimeter/update_gmap/{id_perimeter}', 'PerimeterListController@updatePerimeterListGmap');
    $router->get('/list_perimeter/rate_week/{id_perimeter}', 'PerimeterListController@getWeekPerimeterRate');

    $router->get('/list_perimeter_new/{kd_perusahaan}', 'PerimeterListController@getPerimeterListNew');

    //pisah antara PIC & FO
    $router->get('/list_perimeter_fo/{kd_perusahaan}', 'PerimeterListController@getPerimeterListFo');
    $router->get('/list_perimeter_pic/{kd_perusahaan}', 'PerimeterListController@getPerimeterListPic');

    //report
    $router->get('/report/perimeter/{id_perimeter}', 'PerimeterListController@getReportByPerimeter');
    $router->get('/report/by_id/{id_report}', 'PerimeterListController@getReportPerimeterByID');
    $router->get('/review/perimeter/{id_perimeter}', 'PerimeterListController@getReviewByPerimeter');
    $router->get('/review/by_id/{id_review}', 'PerimeterListController@getReviewPerimeterByID');

    //Region
    $router->get('/region/{kd_perusahaan}', 'PerimeterListController@getRegionList');

	//Kota
	$router->get('/kota', 'MasterController@getAllKota');
	$router->get('/kota/{id_provinsi}', 'MasterController@getKotaByProvinsi');
	$router->get('/provinsi', 'MasterController@getAllProvinsi');

    //Master
	$router->get('/stskasus', 'MasterController@getAllStsKasus');
	$router->get('/stskasus2', 'MasterController@getAllStsKasus2');
	$router->get('/stspegawai', 'MasterController@getAllStsPegawai');
	$router->get('/sosialisasikategori', 'MasterController@getAllSosialisasiKategori');
	$router->get('/perimeterkategori', 'MasterController@getKategoriPerimeter');
	$router->get('/clusterruangan', 'MasterController@getClusterRuangan');
	$router->get('/weeklist', 'MasterController@getWeekList');
	$router->get('/monthlist', 'MasterController@getMonthList');
	$router->get('/fasilitas_rumah', 'MasterController@getFasilitasRumah');
	$router->get('/kriteria_orang', 'MasterController@getKriteriaOrang');
	$router->get('/jenis_industri', 'MasterController@getJenisIndustri');
	$router->get('/jns_industri', 'MasterController@getJnsIndustri');
	$router->get('/stsperimeterpl', 'ProductController@getStsPerimeterPL');
	$router->get('/picheaderperimeter/{id}', 'ProductController@getPICPerimeterPL');
	//Company
	$router->get('/company', 'MasterController@getAllCompany');
	$router->get('/company/detail/{id}', 'MasterController@getDetailCompany');
	$router->post('/company/upload_foto', 'MasterController@uploadFotoBUMN');

	//Alert
	$router->get('/dashboard/alert_week_bymcid/{id}', 'DashboardController@getAlertWeek_byMcid');

	//rangkuman_all
	$router->get('/dashboard/rangkuman_all', 'DashboardController@getRangkumanAll');

	//Dashboard
	$router->get('/dashboard/cosmicindex', 'DashboardController@getCosmicIndexAll');
	$router->get('/dashboard/perimeter_bykategori_all', 'DashboardController@getPerimeterbyKategoriAll');
	$router->get('/dashboard/perimeter_byprovinsi_all', 'DashboardController@getPerimeterbyProvinsiAll');
	$router->get('/dashboard/perimeter_byperusahaan_all', 'DashboardController@getPerimeterbyPerusahaanAll');
	$router->get('/dashboard/provinsi_bykategori/{id_kategori}', 'DashboardController@getProvinsibyKategoribyID');
	$router->get('/dashboard/region_byperusahaan/{kd_perusahaan}', 'DashboardController@getRegionbyPerusahaanbyID');
	$router->get('/dashboard/perimeter_bykategori_byprovinsi/{id_kategori}/{id_provinsi}', 'DashboardController@getListPerimeter_byKategoribyProvinsi');
	$router->get('/dashboard/perimeter_byperusahaan_byregion/{kd_perusahaan}/{id_region}', 'DashboardController@getListPerimeter_byPerusahaanbyRegion');

	$router->get('/dashboard/dashboardhead', 'DashboardController@getDashboardHead');
	$router->get('/dashboard/list_week', 'DashboardController@getWeekList');
	$router->get('/dashboard/monitoring_bymciddate/{id}/{tgl}', 'DashboardController@getMonitoring_ByMcidWeek');
	$router->get('/dashboard/listmonitoring_bymciddate/{id}/{tgl}', 'DashboardController@getListMonitoring_ByMcidWeek');
	$router->get('/dashboard/cosmic_index_report', 'DashboardController@getCosmicIndexReport');
	$router->get('/dashboard/cosmic_index_report_average', 'DashboardController@getCosmicIndexReportAverage');

	//sprint16
	$router->get('/dashboard/perimeter_bykategoriperusahaan/{name}', 'DashboardController@getPerimeter_bykategoriperusahaan');
	$router->get('/dashboard/perimeter_bykategoriperusahaanProv/{id}', 'DashboardController@getPerimeter_bykategoriperusahaanProv');

	$router->get('/dashboard/cosmic_index_detail/{kd_perusahaan}', 'DashboardController@getCosmicIndexbyCompanyAndDate');
	$router->get('/dashboard/cosmic_index_detaillist/{kd_perusahaan}', 'DashboardController@getCosmicIndexListbyCompany');
	$router->get('/dashboard/cosmic_index_detaillist/download/{kd_perusahaan}', 'DashboardController@getDownloadCosmicIndexListbyCompany');
    $router->get('/dashboard/cosmic_index_detail_average/{kd_perusahaan}', 'DashboardController@getAverageCosmicIndexDetailbyCompany');
    $router->get('/dashboard/cosmic_index_list_average', 'DashboardController@getAverageCosmicIndexList');
	//sprint18
	$router->get('/dashboard/perusahaan_byprovinsi_all', 'DashboardController@getPerusahaanbyProvinsiAll');
	$router->get('/dashboard/perusahaan_byindustri_all', 'DashboardController@getPerusahaanbyIndustriAll');
	//agregasi_data
	$router->get('/dashboard/agregasi_data_pegawai/{mc_id}', 'DashboardController@getAgregasiData');
	$router->post('/dashboard/agregasi_data_pegawai/add', 'DashboardController@addAgregasiData');

	//DashboardCluster
	$router->get('/dashcluster/cluster_dashboardhead/{id}', 'DashClusterController@getClusterDashboardHead');
	$router->get('/dashcluster/cluster_perimeter_bykategori_all/{id}', 'DashClusterController@getClusterPerimeterbyKategoriAll');
	$router->get('/dashcluster/cluster_perimeter_byprovinsi_all/{id}', 'DashClusterController@getClusterPerimeterbyProvinsiAll');
	$router->get('/dashcluster/cluster_perimeter_bycosmicindex/{id}', 'DashClusterController@getClusterCosmicIndexAll');

	//DashboardVaksin
	$router->get('/dashvaksin/dashvaksin', 'DashVaksinController@getDashVaksin');
	$router->get('/dashvaksin/dashvaksin_bymcid/{id}', 'DashVaksinController@getDashVaksin_bymcid');
	$router->get('/dashvaksin/dashvaksin_mc', 'DashVaksinController@getDashVaksinPerusahaan');
	$router->get('/dashvaksin/dashvaksin_mpro', 'DashVaksinController@getDashVaksinProvinsi');
	$router->get('/dashvaksin/dashvaksin_mkab', 'DashVaksinController@getDashVaksinKabupaten');
	$router->get('/dashvaksin/dashvaksin_lokasi1', 'DashVaksinController@getDashVaksinLokasi1');
	$router->get('/dashvaksin/dashvaksin_lokasi2', 'DashVaksinController@getDashVaksinLokasi2');
	$router->get('/dashvaksin/dashvaksin_lokasi3', 'DashVaksinController@getDashVaksinLokasi3');
	$router->get('/dashvaksin/dashvaksin_kabmc', 'DashVaksinController@getDashVaksinKabPerusahaanWeb');
	$router->get('/dashvaksin/dashvaksin_provmc', 'DashVaksinController@getDashVaksinProvPerusahaanWeb');


	$router->get('/dashvaksin/download/{kd_perusahaan}', 'DashboardController@getDownloadVaksinbyCompany');
	$router->get('/dashvaksin/downloadtmp/{kd_perusahaan}', 'DashboardController@getDownloadVaksinTmpbyCompany');
	$router->get('/dashvaksin/dashvaksin_mc_filter', 'DashVaksinController@getDashVaksinPerusahaanFilter');
	$router->get('/dashvaksin/dashvaksin_pegawai_filter', 'DashVaksinController@getDashVaksinPegawaiFilter');
	$router->get('/detail_profile', 'DashVaksinController@getDetailProfile');

	$router->get('/vaksin/vaksin_byid/{id}', 'VaksinController@getDataByid');
	$router->get('/vaksin/vaksin_bykdperusahaan/{id}', 'VaksinController@getDataByMcid');
	$router->get('/vaksin/vaksin_deletebyid/{id}', 'VaksinController@deleteVaksin');

	$router->get('/mobiledashvaksin/jmlpegawai', 'DashVaksinController@getDataJmlPegawai');
	$router->get('/mobiledashvaksin/groupbyjnskelamin', 'DashVaksinController@getDashVaksinMobileByJnsKelamin');
	$router->get('/mobiledashvaksin/groupbystspegawai', 'DashVaksinController@getDashVaksinMobileByStsPegawai');
	$router->get('/mobiledashvaksin/groupbyprovinsi', 'DashVaksinController@getDashVaksinMobileByProvinsi');
	$router->get('/mobiledashvaksin/groupbyusia', 'DashVaksinController@getDashVaksinMobileByUsia');
	$router->get('/mobiledashvaksin/groupbykabupaten/{id}', 'DashVaksinController@getDashVaksinMobileKabByProvinsi');
	$router->get('/mobiledashvaksin/groupbykabpro', 'DashVaksinController@getDashVaksinMobileKabPro');
	$router->get('/mobiledashvaksin/groupbycompany/{id}', 'DashVaksinController@getDashVaksinMobileCompanyByKabupaten');

	//Materialized View
	$router->get('/dashboard/refresh_mv_rangkumanall/', 'DashboardController@RefreshMvRangkumanAll');

	//Execution
	$router->get('/report/execution/{id}', 'PerimeterController@getExecutionReport');
	$router->get('/dashboard/dashboardhead_bumn/{id}', 'DashboardController@getDashboardHeadBUMN');
	$router->get('/dashboard/dashboardprotokol_bumn/{id}', 'DashboardController@getDashboardProtokolBUMN');
	$router->get('/dashboard/dashboardmrmpm_bumn/{id}', 'DashboardController@getDashboardMrMpmBUMN');
	$router->get('/dashboard/dashboardjml_pegawai/{id}', 'DashboardController@getDashboardJmlPegawai');
    //Log
    $router->get('/log_activity', 'UserController@setActivityLog');

    //Product
  	$router->get('/product/list_pengajuan_atestasi/{id_produk}', 'ProductController@getPengajuanAtestasi');
  	$router->get('/product/layanan_produk', 'ProductController@getLayananProduk');
  	$router->post('/product/add_pengajuan_atestasi/{id_produk}', 'ProductController@addPengajuanAtestasi');
  	$router->post('/product/add_pengajuan_layanan/{id_produk}', 'ProductController@addPengajuanLayanan');
  	$router->post('/product/add_pelaporan_mandiri/{id_produk}', 'ProductController@addPelaporanMandiri');

  	$router->get('/product/daftar_riwayat', 'ProductController@getListRiwayatProduk');
  	$router->get('/product/detail_produk', 'ProductController@getPengajuanById');

    //Sosialisasi Web
    Route::post('/sosialisasi/webupload_json/{user_id}', 'SosialisasiController@WebuploadSosialisasiJSON');
    Route::post('/sosialisasi/webupdate_json/{user_id}/{id}', 'SosialisasiController@WebupdateSosialisasiJSON');

    //atestasi&sertifikasi
    $router->get('/dashboard/card_produk', 'DashboardController@getCardProduk');
    $router->get('/dashboard/card_atestasi', 'DashboardController@getCardAtestasi');
    $router->get('/dashboard/card_sertifikasi', 'DashboardController@getCardSertifikasi');

    // Report Protokol
    $router->get('/dashreport/all_card_byjns/{id}', 'ReportController@getDashReportCardByJns');
    $router->get('/dashreport/all_byjns/{id}', 'ReportController@getDashReportByJns');
    $router->get('/dashreport/all_byjnsmcid/{id}/{mc_id}', 'ReportController@getDashReportByJnsMCid');

    $router->get('/dashreport/card_bymcid/{id}', 'ReportController@getDashReportCardByMcid');
    $router->get('/report/byid/{id}', 'ReportController@getDataByid');
    $router->get('/report/bymcid/{id}', 'ReportController@getDataByMcid');
    $router->get('/report/picfobymcid/{id}', 'ReportController@getMobilePICFObyMcid');

    $router->get('/dashreport/mobileall_byjns/{id}', 'ReportController@getDashReportMobileByJns');


    //User Reset Password
    $router->post('/user/reset_password', 'UserController@postResetPassword');
    $router->post('/user/cek_user', 'UserController@postCekUser');

    //Report Protokol  Web
    Route::post('/report/webupdate_json/{user_id}/{id}', 'ReportController@WebUpdateReportJSON');

    //Survei Kepuasan
    Route::post('/report/survei_kepuasan', 'ReportController@postSurveiKepuasan');
    Route::post('/report/data_wfh/add', 'ReportController@postDataWFHWFO');
    Route::get('/report/data_wfh/{mc_id}', 'ReportController@getDataWFHWFOByPerusahaan');
    Route::get('/download/data_wfh/{kd_perusahaan}/{filename}', 'ReportController@getDownloadFileProtokolWFH');
    Route::get('/report/dashboard_pelaporan/{mc_id}', 'ReportController@getDataPelaporanWFHWFOByPerusahaan');

    Route::post('/user/token_update/{id}', 'UserController@tokenUpdate');
    Route::post('/user/sendfirebase/{id}', 'UserController@sendFirebase');

    Route::get('/get_token', 'UserController@get_token');
    Route::get('/notif_pic/{nik}', 'UserController@getNotifpic');

	Route::group(['middleware' => 'auth:api'], function () {
		//Data_User
		Route::get('/user/detail', 'UserController@getDetailUser');
		Route::post('/user/detail/{id}', 'UserController@updateDetailUser');
		Route::post('/user/change_password', 'UserController@change_password');
		Route::post('/user/logout', 'UserController@logout');
		Route::post('/user/detail_first/{id}', 'UserController@updateFirstDetailUser');
		Route::post('/user/upload_foto_profile', 'UserController@uploadFotoProfile');

		//Route::post('/terpapar/add', 'TerpaparController@InsertKasus');
		Route::post('/terpapar/update/{id}', 'TerpaparController@UpdateKasus');
        Route::delete('/terpapar/delete/{id_kasus}', 'TerpaparController@deleteKasus');
        Route::post('/terpapar/add', 'TerpaparController@InsertKasus');

		Route::post('/sosialisasi/upload_json', 'SosialisasiController@uploadSosialisasiJSON');
		Route::post('/sosialisasi/update_json/{id}', 'SosialisasiController@updateSosialisasiJSON');
		Route::post('/report/update_json/{id}', 'ReportController@updateReportJSON');

		Route::get('/vaksinwlb/vaksin_byid/{id}', 'VaksinController@getDataByidWLB');
		Route::get('/vaksinwlb/vaksin_bykdperusahaan/{id}', 'VaksinController@getDataByMcidWLB');
		Route::get('/vaksinwlb/vaksin', 'VaksinController@getDataAllWLB');

		Route::get('/vaksinkemenkes/vaksin', 'VaksinController@getDataAllKEMENKES');
		Route::get('/vaksinkemenkes/vaksin_bykdperusahaan/{id}', 'VaksinController@getDataByMcidKEMENKES');
		Route::get('/vaksinkemenkes/vaksin_bynik/{id}', 'VaksinController@getDataByNIKKEMENKES');

        Route::get('/vaksinpl/vaksin', 'VaksinController@getDataAllPL');

		Route::get('/monitoringbumn/perimeter/{nik}/{id_perimeter_level}', 'PICController@getAktifitasbyPerimeterBUMN');
		Route::get('/list_perimeterbumn/{kd_perusahaan}', 'PerimeterListController@getPerimeterListBUMN');
		Route::get('/taskforcebumn/{id}', 'PerimeterController@getTaskForceBUMN');

        //Rumah Singgah
      	Route::get('/rumah_singgah', 'RumahSinggahController@getListRumahSinggah');
      	Route::get('/rumah_singgah/provinsi', 'RumahSinggahController@getGroupRumahSinggahByProv');
      	Route::get('/rumah_singgah/provinsi_kota/{id_provinsi}', 'RumahSinggahController@getGroupRumahSinggahByProvKota');
        Route::get('/rumah_singgah/{id}', 'RumahSinggahController@getRumahSinggahById');
        Route::delete('/rumah_singgah/{id}', 'RumahSinggahController@deleteRumahSinggah');
        Route::post('/rumah_singgah/add', 'RumahSinggahController@addRumahSinggah');
        Route::post('/rumah_singgah/update/{id}', 'RumahSinggahController@updateRumahSinggah');
        Route::get('/total_rumah_singgah', 'RumahSinggahController@getJumlahRumahSinggah');

        //lockdown
        Route::post('/update_lockdown', 'ReportController@UpdateLockdown');
        Route::get('/terpapar/laporan_detail/{id}/{page}/{search}', 'TerpaparController@getDatadetail');
	});
});

$router->group(['prefix' => 'api/v2'], function () use ($router) {
    Route::get('/jns_industri', 'MasterController@getJnsIndustri');
    Route::get('/report/readiness/{id}', 'PerimeterController@getReadinessIndex');
    Route::get('/dashboard/readiness/{id}', 'DashboardController@getReadinessIndexbyCompany');

    Route::get('/product/list_pengajuan_atestasi/{id_produk}', 'ProductController@getPengajuanAtestasi');
    Route::get('/product/layanan_produk', 'ProductController@getLayananProduk');
    Route::post('/product/add_pengajuan_atestasi/{id_produk}', 'ProductController@addPengajuanAtestasi');
    Route::post('/product/add_pengajuan_layanan/{id_produk}', 'ProductController@addPengajuanLayanan');
    Route::post('/product/add_pelaporan_mandiri/{id_produk}', 'ProductController@addPelaporanMandiri');

    Route::post('/user/reset_password', 'UserController@postResetPassword');
    Route::get('/company', 'MasterController@getAllCompany');
    Route::post('/user/cek_user', 'UserController@postCekUser');

    Route::group(['middleware' => 'auth:api'], function () {
		//Data_User
    Route::get('/report/perimeter/{id_perimeter}', 'PerimeterListController@getReportByPerimeter');
    Route::get('/report/by_id/{id_report}', 'PerimeterListController@getReportPerimeterByID');
    Route::get('/review/perimeter/{id_perimeter}', 'PerimeterListController@getReviewByPerimeter');
    Route::get('/review/by_id/{id_review}', 'PerimeterListController@getReviewPerimeterByID');

    Route::post('/user/token_update/{id}', 'UserController@tokenUpdate');
    Route::post('/user/sendfirebase/{id}', 'UserController@sendFirebase');
    Route::get('/user/detail', 'UserController@getDetailUser');
    Route::post('/user/detail/{id}', 'UserController@updateDetailUser');
    Route::post('/user/change_password', 'UserController@change_password');
    Route::post('/user/logout', 'UserController@logout');
    Route::post('/user/detail_first/{id}', 'UserController@updateFirstDetailUser');
    Route::post('/user/upload_foto_profile', 'UserController@uploadFotoProfile');

    //PIC n FO
    Route::get('/protokol/{id}', 'ProtokolController@protokol');
    Route::post('/protokol/upload', 'ProtokolController@uploadProtokol');
    Route::post('/protokol/upload_json', 'ProtokolController@uploadProtokolJSON');
    Route::get('/list_perimeter/{kd_perusahaan}', 'PerimeterListController@getPerimeterList');
    Route::get('/list_perimeter/detail/{id_perimeter}', 'PerimeterListController@getPerimeterDetail');
    Route::post('/list_perimeter/update_gmap/{id_perimeter}', 'PerimeterListController@updatePerimeterListGmap');
    Route::get('/list_perimeter_level/count/{kd_perusahaan}', 'PerimeterListController@getStatusPerimeterLevel');
    Route::get('/list_perimeter_level/perimeter/{id_perimeter}', 'PerimeterListController@getPerimeterLevelListbyPerimeter');

    Route::get('/perimeter_level/get_file/{id_perimeter_level}', 'PICController@getFilePerimeterLevelByPerimeterLevel');

    Route::get('/cluster/perimeter/{id}', 'PerimeterController@getClusterbyPerimeter');
    Route::get('/cluster/perimeter/{id}/{nik}', 'PICController@getClusterbyPerimeter');
    Route::post('/cluster/add_file', 'PICController@addFileClusterRuangan');

    Route::get('/monitoring/{nik}/{id_perimeter_cluster}', 'PICController@getAktifitasbyCluster');
    Route::get('/monitoring/perimeter/{nik}/{id_perimeter_level}', 'PICController@getAktifitasbyPerimeter');
    Route::post('/monitoring', 'PICController@updateDailyMonitoring');
    Route::post('/monitoring/file','PICController@updateMonitoringFile');
    Route::post('/validasi_monitoring', 'UserController@validasiMonitoring');

    Route::post('/perimeter_closed/add', 'PerimeterListController@addClosedPerimeter');

    Route::post('/perimeter_closed/validasi', 'PerimeterListController@validasiClosedPerimeter');
    Route::post('/perimeter_closed/addActivity', 'PerimeterListController@updateAktifitasClosedPerimeter');  //force add for actifity closed perimeter
    Route::post('/perimeter_open/add', 'PerimeterListController@openPerimeter');

    Route::get('/notif/{nik}', 'PICController@getNotifFO');
    Route::get('/dashboard/cosmic_index_detail/{kd_perusahaan}', 'DashboardController@getCosmicIndexbyCompanyAndDate');

    //BUMN
    Route::get('/clusterruangan', 'MasterController@getClusterRuangan');
    Route::get('/perimeterkategori', 'MasterController@getKategoriPerimeter');
    Route::get('/weeklist', 'MasterController@getWeekList');
    Route::get('/monthlist', 'MasterController@getMonthList');
    Route::get('/region/{kd_perusahaan}', 'PerimeterListController@getRegionList');
    Route::get('/provinsi', 'MasterController@getAllProvinsi');
    Route::get('/kota', 'MasterController@getAllKota');
    Route::get('/kota/{id_provinsi}', 'MasterController@getKotaByProvinsi');
    Route::get('/taskforce/{id}', 'PerimeterController@getTaskForce');
    Route::get('/taskforce2/{id}', 'PerimeterController@getTaskForce2');
    Route::get('/taskforce/detail/{nik}', 'PerimeterController@getTaskForceDetail');
    Route::get('/taskforce/delete/{nik}', 'PerimeterController@deleteTaskForce');
    Route::get('/taskforce/reset_password/{nik}', 'PerimeterController@resetPasswordTaskForce');
    Route::post('/taskforce/add', 'PerimeterController@addTaskForce');
    Route::get('/detail_profile', 'DashVaksinController@getDetailProfile');
    Route::get('/dashboard/cosmic_index_detail_average/{kd_perusahaan}', 'DashboardController@getAverageCosmicIndexDetailbyCompany');
    Route::get('/dashboard/cosmic_index_detaillist/{kd_perusahaan}', 'DashboardController@getCosmicIndexListbyCompany');
    Route::get('/dashboard/cosmic_index_list_average', 'DashboardController@getAverageCosmicIndexList');
	Route::get('/dashboard/alert_week_bymcid/{id}', 'DashboardController@getAlertWeek_byMcid');
    Route::get('/dashboard/dashboardhead', 'DashboardController@getDashboardHead');
    Route::get('/dashboard/card_produk', 'DashboardController@getCardProduk');
    Route::get('/dashboard/card_atestasi', 'DashboardController@getCardAtestasi');
    Route::get('/dashboard/card_sertifikasi', 'DashboardController@getCardSertifikasi');
    Route::get('/dashvaksin/dashvaksin', 'DashVaksinController@getDashVaksin');
    Route::get('/dashvaksin/dashvaksin_pegawai_filter', 'DashVaksinController@getDashVaksinPegawaiFilter');

    Route::get('/perimeter/{id}', 'PerimeterController@getPerimeter');
    Route::get('/perimeter/detail/{id_perimeter_level}', 'PerimeterController@getDetailPerimeter');
    Route::get('/perimeter/count/{id}', 'PerimeterController@getCountPerimeter');
    Route::get('/perimeter/{kd_perusahaan}/kota/{id_kota}', 'PerimeterController@getPerimeterbyKota');
    Route::post('/perimeter/update', 'PerimeterListController@updateDetailPerimeter');
    Route::get('/perimeter_level/perimeter/{id_perimeter}', 'PerimeterController@getLevelbyPerimeter');
    Route::post('/perimeter_level/update', 'PerimeterController@updateDetailPerimeterLevel');
	Route::post('/import', 'ImportController@import');

    Route::get('/list_perimeter/region/{id}', 'PerimeterListController@getPerimeterListbyRegion');
    Route::get('/list_perimeter_report/{kd_perusahaan}', 'PerimeterReportController@getPerimeterList');
    Route::get('/list_perimeter_new/{kd_perusahaan}', 'PerimeterListController@getPerimeterListNew');
    Route::get('/report/execution/{id}', 'PerimeterController@getExecutionReport');

    Route::get('/sosialisasi/get_bymcid/{id}/{page}', 'SosialisasiController@getDataByMcid');
    Route::get('/sosialisasi/get_byid/{id}', 'SosialisasiController@getDataById');
    Route::post('/sosialisasi/upload_json', 'SosialisasiController@uploadSosialisasiJSON');
    Route::post('/sosialisasi/update_json/{id}', 'SosialisasiController@updateSosialisasiJSON');
    Route::get('/sosialisasi/delete/{id}', 'SosialisasiController@deleteSosialisasi');

    Route::get('/stspegawai', 'MasterController@getAllStsPegawai');
    Route::get('/stskasus', 'MasterController@getAllStsKasus');
    Route::get('/sosialisasikategori', 'MasterController@getAllSosialisasiKategori');

    Route::get('/terpapar/laporan_home/{id}', 'TerpaparController@getDataHome');
    Route::post('/terpapar/add', 'TerpaparController@InsertKasus');
    Route::delete('/terpapar/delete/{id_kasus}', 'TerpaparController@deleteKasus');

	Route::get('/product/layanan_produk', 'ProductController@getLayananProduk');
    Route::get('/product/daftar_riwayat', 'ProductController@getListRiwayatProduk');
    Route::get('/product/detail_produk', 'ProductController@getPengajuanById');
    Route::post('/product/add_pengajuan_atestasi/{id_produk}', 'ProductController@addPengajuanAtestasi');

    Route::post('/company/upload_foto', 'MasterController@uploadFotoBUMN');

    //Gugus Tugas
	Route::get('/dashboard/perimeter_byperusahaan_all', 'DashboardController@getPerimeterbyPerusahaanAll');
    Route::get('/dashboard/perimeter_bykategori_all', 'DashboardController@getPerimeterbyKategoriAll');
    Route::get('/dashboard/region_byperusahaan/{kd_perusahaan}', 'DashboardController@getRegionbyPerusahaanbyID');
    Route::get('/dashboard/perimeter_byperusahaan_byregion/{kd_perusahaan}/{id_region}', 'DashboardController@getListPerimeter_byPerusahaanbyRegion');
    Route::get('/dashboard/perimeter_bykategori_byprovinsi/{id_kategori}/{id_provinsi}', 'DashboardController@getListPerimeter_byKategoribyProvinsi');
    Route::get('/dashboard/provinsi_bykategori/{id_kategori}', 'DashboardController@getProvinsibyKategoribyID');
    Route::get('/dashboard/cosmic_index_report', 'DashboardController@getCosmicIndexReport');
    Route::get('/dashboard/cosmic_index_report_average', 'DashboardController@getCosmicIndexReportAverage');
    Route::post('/dashboard/agregasi_data_pegawai/add', 'DashboardController@addAgregasiData');
	Route::get('/dashboard/cosmic_index_detaillist/download/{kd_perusahaan}', 'DashboardController@getDownloadCosmicIndexListbyCompany');
    Route::get('/dashreport/mobileall_byjns/{id}', 'ReportController@getDashReportMobileByJns');
    Route::get('/dashreport/card_bymcid/{id}', 'ReportController@getDashReportCardByMcid');
    Route::get('/dashreport/all_card_byjns/{id}', 'ReportController@getDashReportCardByJns');
    Route::get('/dashvaksin/dashvaksin_mc', 'DashVaksinController@getDashVaksinPerusahaan');
    Route::get('/dashvaksin/dashvaksin_mc_filter', 'DashVaksinController@getDashVaksinPerusahaanFilter');
    Route::get('/dashvaksin/dashvaksin_lokasi1', 'DashVaksinController@getDashVaksinLokasi1');
    Route::get('/dashvaksin/dashvaksin_lokasi2', 'DashVaksinController@getDashVaksinLokasi2');

    Route::get('/list_perimeter/rate_week/{id_perimeter}', 'PerimeterListController@getWeekPerimeterRate');

    Route::get('/review/perimeter/{id_perimeter}', 'PerimeterListController@getReviewByPerimeter');
    Route::get('/report/perimeter/{id_perimeter}', 'PerimeterListController@getReportByPerimeter');
    Route::get('/report/bymcid/{id}', 'ReportController@getDataByMcid');
    Route::get('/stskasus2', 'MasterController@getAllStsKasus2');

    Route::get('/terpapar/laporan_home_all', 'TerpaparController@getDataHomeAll');
    Route::get('/terpapar/dashkasus_companymobile_bymskid/{id}', 'TerpaparController@getDashboardCompanyMobilebyMskid');
    Route::get('/terpapar/laporan_detail/{id}/{page}/{search}', 'TerpaparController@getDatadetail');
    Route::get('/terpapar/byid/{id}', 'TerpaparController@getDataByid');
    Route::post('/terpapar/update/{id}', 'TerpaparController@UpdateKasus');

    Route::get('/sosialisasi/get_perusahaan_all', 'DashboardController@getEventbyPerusahaanAll');
    Route::get('/sosialisasi/total_perusahaan_all', 'DashboardController@countEventbyPerusahaanAll');
    Route::get('/download/sosialisasi/{kd_perusahaan}/{filename}', 'SosialisasiController@getDownloadFileSosialisasi');

    Route::get('/mobiledashvaksin/jmlpegawai', 'DashVaksinController@getDataJmlPegawai');
    Route::get('/mobiledashvaksin/groupbyjnskelamin', 'DashVaksinController@getDashVaksinMobileByJnsKelamin');
    Route::get('/mobiledashvaksin/groupbystspegawai', 'DashVaksinController@getDashVaksinMobileByStsPegawai');
    Route::get('/mobiledashvaksin/groupbyprovinsi', 'DashVaksinController@getDashVaksinMobileByProvinsi');
    Route::get('/mobiledashvaksin/groupbyusia', 'DashVaksinController@getDashVaksinMobileByUsia');
    Route::get('/mobiledashvaksin/groupbykabupaten/{id}', 'DashVaksinController@getDashVaksinMobileKabByProvinsi');
    Route::get('/mobiledashvaksin/groupbykabpro', 'DashVaksinController@getDashVaksinMobileKabPro');
    Route::get('/mobiledashvaksin/groupbycompany/{id}', 'DashVaksinController@getDashVaksinMobileCompanyByKabupaten');

    Route::get('/notif_pic/{nik}', 'UserController@getNotifpic');
    Route::get('/protokol/download/{kd_perusahaan}/{id_protokol}', 'ProtokolController@getDownloadFileProtokol');
    Route::get('/list_perimeter_level_report/count/{kd_perusahaan}', 'PerimeterReportController@getStatusPerimeterLevel');
    Route::get('/kriteria_orang', 'MasterController@getKriteriaOrang');
    Route::get('/fasilitas_rumah', 'MasterController@getFasilitasRumah');
    Route::post('/perimeter_level/add_file', 'PICController@addFilePerimeterLevel');
    Route::post('/report/survei_kepuasan', 'ReportController@postSurveiKepuasan');
    Route::post('/report/data_wfh/add', 'ReportController@postDataWFHWFO');
    Route::get('/report/data_wfh/{mc_id}', 'ReportController@getDataWFHWFOByPerusahaan');
    Route::get('/download/data_wfh/{kd_perusahaan}/{filename}', 'ReportController@getDownloadFileProtokolWFH');

    Route::get('/rumah_singgah', 'RumahSinggahController@getListRumahSinggah');
    Route::get('/rumah_singgah/provinsi', 'RumahSinggahController@getGroupRumahSinggahByProv');
    Route::get('/total_rumah_singgah', 'RumahSinggahController@getJumlahRumahSinggah');
    Route::post('/update_lockdown', 'ReportController@UpdateLockdown');
    Route::post('/report/update_json/{id}', 'ReportController@updateReportJSON');

    Route::get('/report/dashboard_pelaporan/{mc_id}', 'ReportController@getDataPelaporanWFHWFOByPerusahaan');
    Route::post('/perimeter_pedulilindungi/update/{id}', 'ProductController@updatePerimeterPL');
    Route::post('/perimeter_pedulilindungi/insert', 'ProductController@insertPerimeterPL');
    Route::get('/product/card_perimeter_pl/{id}', 'ProductController@getCardPerimeterQR');
    
    Route::get('/stsperimeterpl', 'ProductController@getStsPerimeterPL');
    Route::get('/picheaderperimeter/{id}', 'ProductController@getPICPerimeterPL');
    Route::get('/perimeterpl_bymcid/{id}', 'ProductController@PerimeterPLByMcid');
    Route::get('/perimeterpl_byid/{id}', 'ProductController@PerimeterPLByid');
    Route::get('/kota', 'MasterController@getAllKota');
	});
});
