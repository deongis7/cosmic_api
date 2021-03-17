<?php

namespace App\Http\Controllers;
use App\Vaksin;
use App\Company;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;

use DB;

class DashVaksinController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function __construct()
    {
        //
    }

	public function index(){

	}
	public function show($id){

	}

	public function store (Request $request){

	}

	public function getDashVaksin(){
	    //$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashvaksin", 15 * 60, function() {
	        $data = array();
	        $dashvaksin = DB::connection('pgsql_vaksin')->select("SELECT * FROM vaksin_dashboard()");
	        //$dashvaksin = DB::select("SELECT * FROM vaksin_dashboard()");

	        foreach($dashvaksin as $dv){
	            $data[] = array(
	                "v_judul" => $dv->v_judul,
	                "v_jml" => $dv->v_jml
	            );
	        }
	    //});
        return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksin_bymcid($id){
	    //$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashvaksin_".$id, 15 * 60, function()use($id){
	    $data = array();
	    $dashvaksin = DB::connection('pgsql_vaksin')->select("SELECT * FROM vaksin_summary_bymcid('$id')");
	    //$dashvaksin = DB::select("SELECT * FROM vaksin_summary_bymcid('$id')");
	    
	    foreach($dashvaksin as $dv){
    	        $data[] = array(
    	            "v_judul" => $dv->v_judul,
    	            "v_jml" => $dv->v_jml
    	        );
    	    }
	    //});
	    return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksinPerusahaan(){
	    //$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashvaksin_perusahaan", 15 * 60, function() {
	    $data = array();
	    $dashkasus_perusahaan = DB::connection('pgsql_vaksin')->select("SELECT * FROM vaksin_dashboard_perusahaan()");
	    //$dashkasus_perusahaan = DB::select("SELECT * FROM vaksin_dashboard_perusahaan()");
	    
	    foreach($dashkasus_perusahaan as $dvp){
    	        $data[] = array(
    	            "v_mc_id" => $dvp->v_mc_id,
    	            "v_mc_name" => $dvp->v_mc_name,
    	            "v_jml" => $dvp->v_jml
    	        );
    	    }
	    //});
	    return response()->json(['status' => 200,'data' => $data]);
	}

	public function getDashVaksinPerusahaanFilter(Request $request){
		 $filter_perusahaan = $request->status_perusahaan;
		 $filter_pegawai = $request->status_pegawai;
		/*$filter_perusahaan = $sp1;
		$filter_pegawai = $sp2;*/
		$w1 = " WHERE mc_level IN (1,2,3)";
		if(!empty($filter_perusahaan)){
			$w1 = "WHERE mc_level = '$filter_perusahaan'";
		}

		$w2 = " ";
		if(!empty($filter_pegawai)){
			$w2 = " AND tv.tv_msp_id = '$filter_pegawai'";
		}

	    //$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashvaksin_perusahaan", 15 * 60, function() {
	    $data = array();
	    $dashkasus_perusahaan = DB::connection('pgsql_vaksin')->select("SELECT mc_id, mc_name, 
				
				(SELECT COUNT(*) 
				FROM transaksi_vaksin tv
				INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
				--INNER JOIN master_sektor ms ON ms.ms_id=mc.mc_msc_id
				WHERE 
				--WHERE mc.mc_level IN (0,1,2,3,9) 
				--AND ms.ms_type = 'CCOVID'
				tv.tv_mc_id=mc.mc_id
				$w2
				AND is_lansia=0
				AND mc.mc_id=mc1.mc_id) jml
				FROM master_company mc1
				$w1
				ORDER BY mc_name ");
	    //$dashkasus_perusahaan = DB::select("SELECT * FROM vaksin_dashboard_perusahaan()");
	    
	    foreach($dashkasus_perusahaan as $dvp){
    	        $data[] = array(
    	            "v_mc_id" => $dvp->mc_id,
    	            "v_mc_name" => $dvp->mc_name,
    	            "v_jml" => $dvp->jml
    	        );
    	    }
	    //});
	    return response()->json(['status' => 200,'data' => $data]);
	}

	
	public function getDashVaksinPegawaiFilter(Request $request){
		$filter_nama = $request->nama;
		$filter_mc_id = $request->mc_id;
        $string = "_get_dashvaksin_pegawai".$filter_mc_id;
        $datacache = Cache::tags(['users'])->remember(env('APP_ENV', 'dev').$string, 0*60, function () use($filter_nama, $filter_mc_id) {
		

		$where_name = "";
		if(!empty($filter_nama)){
			$where_name = " AND tv.tv_nama LIKE '%$filter_nama%'";
		}

	    // $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashvaksin_pegawai", 15 * 60, function() {
		
	    
	    $data = array();
	    $dashpegawai = DB::connection('pgsql_vaksin')->select("select tv_nik, tv_nama, msp_name2 from transaksi_vaksin tv 
			join master_status_pegawai msp on msp.msp_id = tv.tv_msp_id 
			where tv.tv_mc_id = '".$filter_mc_id."' AND is_lansia=0 $where_name");
	    //$dashkasus_perusahaan = DB::select("SELECT * FROM vaksin_dashboard_perusahaan()");
	    
	    foreach($dashpegawai as $dvp){
    	        $data[] = array(
    	            "nik" => $dvp->tv_nik,
    	            "nama" => $dvp->tv_nama,
    	            "status" => $dvp->msp_name2
    	        );
    	    }
    	    return $data;
	    });
	    Cache::tags(['users'])->flush();
	    return response()->json(['status' => 200,'data' => $datacache]);
	}
	
	public function getDashVaksinProvinsi(){
	    //$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashvaksin_provinsi", 15 * 60, function() {
	    $data = array();
	    $dashkasus_provinsi = DB::connection('pgsql_vaksin')->select("SELECT * FROM vaksin_dashboard_provinsi()");
	    //$dashkasus_provinsi = DB::select("SELECT * FROM vaksin_dashboard_provinsi()");
	    
	    foreach($dashkasus_provinsi as $dvp){
	        $data[] = array(
	            "v_mpro" => $dvp->v_mpro,
	            "v_jml" => $dvp->v_jml
	        );
	    }
	    //});
	    return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksinKabupaten(){
	    //$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashvaksin_kabupaten", 15 * 60, function() {
	    $data = array();
	    $dashkasus_kabupaten = DB::connection('pgsql_vaksin')->select("SELECT * FROM vaksin_dashboard_kabupaten()");
	    //$dashkasus_kabupaten = DB::select("SELECT * FROM vaksin_dashboard_kabupaten()");
	    
	    foreach($dashkasus_kabupaten as $dvk){
	        $data[] = array(
	            "v_mkab" => $dvk->v_mkab,
	            "v_jml" => $dvk->v_jml
	        );
	    }
	    //});
	    return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksinLokasi1(){
	    //$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashvaksin_lokasi1", 15 * 60, function() {
	    $data = array();
	    $dashvaksin_lokasi1 = DB::connection('pgsql_vaksin')->select("SELECT * FROM vaksin_dashboard_lokasi1()");
	    //$dashkasus_kabupaten = DB::select("SELECT * FROM vaksin_dashboard_lokasi1()");
	    
	    foreach($dashvaksin_lokasi1 as $dl1){
	        $data[] = array(
	            "v_lokasi" => $dl1->v_lokasi,
	            "v_jml" => $dl1->v_jml
	        );
	    }
	    //});
	    return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksinLokasi2(){
	    //$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashvaksin_lokasi2", 15 * 60, function() {
	    $data = array();
	    $dashvaksin_lokasi2 = DB::connection('pgsql_vaksin')->select("SELECT * FROM vaksin_dashboard_lokasi2()");
	    //$dashkasus_kabupaten = DB::select("SELECT * FROM vaksin_dashboard_lokasi2()");
	    
	    foreach($dashvaksin_lokasi2 as $dl2){
	        $data[] = array(
	            "v_lokasi" => $dl2->v_lokasi,
	            "v_jml" => $dl2->v_jml
	        );
	    }
	    //});
	    return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksinLokasi3(){
	    //$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashvaksin_lokasi3", 15 * 60, function() {
	    $data = array();
	    $dashvaksin_lokasi3 = DB::connection('pgsql_vaksin')->select("SELECT * FROM vaksin_dashboard_lokasi3()");
	    //$dashkasus_kabupaten = DB::select("SELECT * FROM vaksin_dashboard_lokasi3()");
	    
	    foreach($dashvaksin_lokasi3 as $dl3){
	        $data[] = array(
	            "v_lokasi" => $dl3->v_lokasi,
	            "v_jml" => $dl3->v_jml
	        );
	    }
	    //});
	    return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function qrcode_mpm($id) {
	    set_time_limit(0);
	    ini_set('max_execution_time', 0);
	    ini_set('memory_limit', '-1');
	    ini_set('post_max_size', '409600M');
	    ini_set('max_input_time', 360000);
	    
	    $client    = new Client();
	    $url = 'http://103.146.244.78/cosmic_api/public/api/v1/dashboard/perimeter_bykategori_all';
	    $request  = $client->request('GET', $url);
	    
	    $response = $request->getBody()->getContents();
	    $result   = json_decode($response, true);
	    
	    return response()->json(['status' => 200,'data' => $result]);
	}
	
	public function getVaksinRaw(Request $request) {
	    $limit = null;
	    $page = null;
	    $search = null;
	    $endpage = 1;
	    
	    $vaksin = new Vaksin();
	    $vaksin->setConnection('pgsql_vaksin');
	    $vaksin = $vaksin->select('mc_id','mc_name','msp_name','mkab_name',
	        'mpro_id', 'mpro_name', 
	        'tv_id','tv_mc_id','tv_nama','tv_msp_id','tv_nip','tv_unit','tv_mjk_id',
	        'tv_mkab_id','tv_nik','tv_ttl_date','tv_no_hp','tv_jml_keluarga','tv_nik_pasangan','tv_nama_pasangan',
	        'tv_nik_anak1','tv_nama_anak1','tv_nik_anak2','tv_nama_anak2','tv_nik_anak3','tv_nama_anak3',
	        'tv_nik_anak4','tv_nama_anak4','tv_nik_anak5','tv_nama_anak5',
	        'tv_date1','tv_lokasi1','tv_date2','tv_lokasi2','tv_date3','tv_lokasi3',
	        'tv_file1','tv_file1_tumb','tv_file2','tv_file2_tumb',
	        'tv_user_insert','tv_date_insert','tv_user_update','tv_date_update')
        ->join('master_company AS mc','mc.mc_id','tv_mc_id')
        ->leftjoin('master_status_pegawai AS msp','msp.msp_id','tv_msp_id')
        ->leftjoin('master_kabupaten AS mkab','mkab.mkab_id','tv_mkab_id')
        ->leftjoin('master_provinsi AS mpro','mpro.mpro_id','mkab.mkab_mpro_id');
        //->where('mc.mc_level', 1);
        if(isset($request->search)) {
            $search = $request->search;
            $vaksin = $vaksin->where(DB::raw("lower(TRIM(tv_nama))"),'like','%'.strtolower(trim($search)).'%');
        }
        
        $jmltotal=($vaksin->count());
        if(isset($request->limit)) {
            $limit = $request->limit;
            $vaksin = $vaksin->limit($limit);
            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));
            
            if (isset($request->page)) {
                $page = $request->page;
                $offset = ((int)$page -1) * (int)$limit;
                $vaksin = $vaksin->offset($offset);
            }
        }
        $vaksin = $vaksin->get();
        $totalvaksin = $vaksin->count();
        
        if (count($vaksin) > 0){
            foreach($vaksin as $vksn){
                if($vksn->tv_file1 !=NULL || $vksn->tv_file1 !=''){
                    if (!file_exists(base_path("storage/app/public/vaksin_eviden/".$vksn->tv_mc_id.'/'.$vksn->tv_file1))) {
                        $path_file404 = '/404/img404.jpg';
                        $filevksn1 = $path_file404;
                    }else{
                        $path_file1 = '/vaksin_eviden/'.$vksn->tv_file1;
                        $filevksn1 = $path_file1;
                    }
                }else{
                    $filevksn1 = '/404/img404.jpg';
                }
                
                if($vksn->tv_file2 !=NULL || $vksn->tv_file2 !=''){
                    if (!file_exists(base_path("storage/app/public/vaksin_eviden/".$vksn->tv_mc_id.'/'.$vksn->tv_file2))) {
                        $path_file404 = '/404/img404.jpg';
                        $filevksn2 = $path_file404;
                    }else{
                        $path_file2 = '/vaksin_eviden/'.$vksn->tv_file2;
                        $filevksn2 = $path_file2;
                    }
                }else{
                    $filevksn2 = '/404/img404.jpg';
                }
                
                if($vksn->tv_file3 !=NULL || $vksn->tv_file3 !=''){
                    if (!file_exists(base_path("storage/app/public/vaksin_eviden/".$vksn->tv_mc_id.'/'.$vksn->tv_file3))) {
                        $path_file404 = '/404/img404.jpg';
                        $filevksn3 = $path_file404;
                    }else{
                        $path_file3 = '/vaksin_eviden/'.$vksn->tv_file3;
                        $filevksn3 = $path_file3;
                    }
                }else{
                    $filevksn3 = '/404/img404.jpg';
                }
                
                if($vksn->tv_mjk_id==1){
                    $jns_kelamin='Laki-laki';
                }else{
                    $jns_kelamin='Perempuan';
                }
                
                $data[] = array(
                    "kode_perusahaan" => $vksn->mc_id,
                    "nama_perusahaan" => $vksn->mc_name,
                    "id" => $vksn->tv_id,
                    "nama" => $vksn->tv_nama,
                    "sts_pegawai_id" => $vksn->tv_msp_id,
                    "sts_pegawai" => $vksn->msp_name,
                    "nip" => $vksn->tv_nip,
                    "unit" => $vksn->tv_unit,
                    "jns_kelamin_id" => $vksn->tv_mjk_id,
                    "jns_kelamin" => $jns_kelamin,
                    "kabupaten_id" => $vksn->tv_mkab_id,
                    "kabupaten" => $vksn->mkab_name,
                    "provinsi_id" => $vksn->mpro_id,
                    "provinsi" => $vksn->mpro_name,
                    "nik" => $vksn->tv_nik,
                    "tanggal_lahir" => $vksn->tv_ttl_date,
                    "no_hp" => $vksn->tv_no_hp,
                    "jml_keluarga" => $vksn->tv_jml_keluarga,
                    "nik_pasangan" => $vksn->tv_nik_pasangan,
                    "nama_pasangan" => $vksn->tv_nama_pasangan,
                    "nik_anak_1" => $vksn->tv_nik_anak1,
                    "nama_anak_1" => $vksn->tv_nama_anak1,
                    "nik_anak_2" => $vksn->tv_nik_anak2,
                    "nama_anak_2" => $vksn->tv_nama_ana2,
                    "nik_anak_3" => $vksn->tv_nik_anak3,
                    "nama_anak_3" => $vksn->tv_nama_anak3,
                    "nik_anak_4" => $vksn->tv_nik_anak4,
                    "nama_anak_4" => $vksn->tv_nama_anak4,
                    "nik_anak_5" => $vksn->tv_nik_anak5,
                    "nama_anak_5" => $vksn->tv_nama_anak5,
                    "date_1" => $vksn->tv_date1,
                    "lokasi_1" => $vksn->tv_lokasi1,
                    "date_2" => $vksn->tv_date2,
                    "lokasi_2" => $vksn->tv_lokasi2,
                    "date_3" => $vksn->tv_date3,
                    "lokasi_3" => $vksn->tv_lokasi3,
                    "file_1" => $filevksn1,
                    "file_2" => $filevksn2,
                    "file_3" => $filevksn3,
                    "date_insert" =>$vksn->tv_date_insert,
                    "date_update" =>$vksn->tv_date_update,
                );
            }
        }else{
            $data = array();
        }
        return response()->json(['status' => 200, 'page_end'=> $endpage,
            'data' => $data]);
	}
	
	public function getDataJmlPegawai(Request $request) {
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level= '.$level;
	    }
	    
	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id='.$msp;
	    }
	    
	    $data = array();
	    $query = "SELECT COALESCE(COUNT(*)) jml
			FROM transaksi_vaksin tv
			INNER JOIN master_kabupaten mkab ON mkab.mkab_id=tv.tv_mkab_id
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			WHERE tv.is_lansia=0
			AND mc.mc_flag=1
            $query_level
            $query_msp ";
            
        $retdb = DB::connection('pgsql_vaksin')->select($query);
        foreach($retdb as $dvp){
            $data[] = array(
                "jml" => $dvp->jml
            );
        }
        return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksinMobileByJnsKelamin(Request $request) {
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level= '.$level;
	    }
	    
	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id='.$msp;
	    }

	    $data = array();
	    $query = "SELECT mjk.mjk_id, mjk.mjk_name,
			(SELECT COALESCE(COUNT(*)) 
			FROM transaksi_vaksin tv 
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			WHERE tv.is_lansia=0
			AND mc.mc_flag=1
            $query_level
            $query_msp
			AND tv.tv_mjk_id = mjk.mjk_id
			AND tv.tv_mjk_id IS NOT NULL) AS jml
			FROM master_jenis_kelamin mjk
			ORDER BY mjk.mjk_name;";
	    
	    $retdb = DB::connection('pgsql_vaksin')->select($query);
	    foreach($retdb as $dvp){
	        $data[] = array(
	            "id" => $dvp->mjk_id,
	            "judul" => $dvp->mjk_name,
	            "jml" => $dvp->jml
	        );
	    }
	    return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksinMobileByStsPegawai(Request $request) {
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level= '.$level;
	    }
	    
	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id='.$msp;
	    }
	    
	    $data = array();
	    $query = "SELECT msp.msp_id, msp.msp_name2,
			(SELECT COALESCE(COUNT(*)) 
			FROM transaksi_vaksin tv 
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			WHERE tv.is_lansia=0
			AND mc.mc_flag=1
            $query_level
            $query_msp
			AND msp.msp_id=tv.tv_msp_id
			AND tv.tv_msp_id IS NOT NULL) AS jml
			FROM master_status_pegawai msp
			ORDER BY msp.msp_name2;";
        
        $retdb = DB::connection('pgsql_vaksin')->select($query);
        foreach($retdb as $dvp){
            $data[] = array(
                "id" => $dvp->msp_id,
                "judul" => $dvp->msp_name2,
                "jml" => $dvp->jml
            );
        }
        return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksinMobileByProvinsi(Request $request) {
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level= '.$level;
	    }
	    
	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id='.$msp;
	    }
	    
	    $data = array();
	    $query = "SELECT mpro.mpro_id, mpro.mpro_name,
			(SELECT COALESCE(COUNT(*)) 
			FROM transaksi_vaksin tv 
			INNER JOIN master_kabupaten mkab ON mkab.mkab_id=tv.tv_mkab_id
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			WHERE tv.is_lansia=0
			AND mc.mc_flag=1
            $query_level
            $query_msp
			AND mkab.mkab_id=tv.tv_mkab_id
			AND tv.tv_mkab_id IS NOT NULL
			AND mkab.mkab_mpro_id=mpro.mpro_id) AS jml
			FROM master_provinsi mpro
			ORDER BY mpro.mpro_id;";
        
        $retdb = DB::connection('pgsql_vaksin')->select($query);
        foreach($retdb as $dvp){
            $data[] = array(
                "id" => $dvp->mpro_id,
                "judul" => $dvp->mpro_name,
                "jml" => $dvp->jml
            );
        }
        return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksinMobileByUsia(Request $request) {
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level= '.$level;
	    }
	    
	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id='.$msp;
	    }
	    
	    $data = array();
	    $query = " SELECT mu.mu_id, mu.mu_nama,
			(SELECT COALESCE(COUNT(*)) 
			FROM transaksi_vaksin tv 
			INNER JOIN master_kabupaten mkab ON mkab.mkab_id=tv.tv_mkab_id
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			WHERE tv.is_lansia=0
			AND mc.mc_flag=1
            $query_level
            $query_msp
			AND mkab.mkab_id=tv.tv_mkab_id
			AND tv.tv_mkab_id IS NOT NULL
			AND mu.mu_awal <= tv.tv_usia
			AND mu.mu_akhir >= tv.tv_usia) AS jml
			FROM master_usia mu
			ORDER BY mu.mu_id;";
            
        $retdb = DB::connection('pgsql_vaksin')->select($query);
        foreach($retdb as $dvp){
            $data[] = array(
                "id" => $dvp->mu_id,
                "judul" => $dvp->mu_nama,
                "jml" => $dvp->jml
            );
        }
        return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksinMobileCompanyByKabupaten($id, Request $request) {
	    $limit = '';
	    $page = '';
	    $endpage = 1;

	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    $query_level1 = ' AND mc1.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level= '.$level;
	        $query_level1 = ' AND mc1.mc_level= '.$level;
	    }
	    
	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id= '.$msp;
	    }
	    
	    $query_search = ' ';
	    if(isset($request->search)) {
	        $query_search = " AND LOWER(TRIM(mc1.mc_name)) LIKE LOWER(TRIM('%$request->search%')) ";
	    }
	    
	    $data = array();
	    $query = "SELECT mc1.mc_id, mc1.mc_name,
			(SELECT COALESCE(COUNT(*)) 
			FROM transaksi_vaksin tv 
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			INNER JOIN master_kabupaten mkab ON mkab.mkab_id=tv.tv_mkab_id
			WHERE 1=1 
            AND tv.is_lansia=0
            $query_msp
            $query_level
			AND tv.tv_mkab_id=$id
			AND tv.tv_mkab_id IS NOT NULL
			AND mc.mc_id=mc1.mc_id) AS jml
			FROM master_company mc1
	        WHERE 1=1 
			AND mc1.mc_flag=1
            $query_level1
            $query_search ";
	    
        if(isset($request->column_sort)) {
            if(isset($request->p_sort)) {
                $sql_sort = ' ORDER BY '.$request->column_sort.' '.$request->p_sort;
            }else{
                $sql_sort = ' ORDER BY '.$request->column_sort.' ASC';
            }
        }else{
            $sql_sort = ' ORDER BY mc1.mc_name ASC ';
        }
        $query .= $sql_sort;
        
        $retdbtotal = DB::connection('pgsql_vaksin')->select($query);
        $jmltotal=(count($retdbtotal));
            
        if(isset($request->limit)) {
            $limit = $request->limit;
            $sql_limit = ' LIMIT '.$request->limit;
            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));
            
            $query .= $sql_limit;
            
            if (isset($request->page)) {
                $page = $request->page;
                $offset = ((int)$page-1) * (int)$limit;
                $sql_offset= ' OFFSET '.$offset;
                
                $query .= $sql_offset;
            }
        }
            
        $retdb = DB::connection('pgsql_vaksin')->select($query);
        foreach($retdb as $dvp){
            $data[] = array(
                "id" => $dvp->mc_id,
                "judul" => $dvp->mc_name,
                "jml" => $dvp->jml
            );
        }
        return response()->json(['status' => 200, 'page_end'=>$endpage, 'data' => $data]);
	}
	
	public function getDashVaksinMobileKabPro(Request $request) {
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level='.$level;
	    }
	    
	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id='.$msp;
	    }
	    
	    $query_search_mpro = ' ';
	    $query_search_mkab = ' ';
	    if(isset($request->search)) {
	        $query_search_mpro = " AND LOWER(TRIM(mpro.mpro_name)) LIKE LOWER(TRIM('%$request->search%')) ";
	        $query_search_mkab = " AND LOWER(TRIM(mkab.mkab_name)) LIKE LOWER(TRIM('%$request->search%')) ";
	    }
	    
	    $data = array();
	    $query = "SELECT mpro.mpro_id, mpro.mpro_name,
			(SELECT COALESCE(COUNT(*))
			FROM transaksi_vaksin tv
			INNER JOIN master_kabupaten mkab ON mkab.mkab_id=tv.tv_mkab_id
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			WHERE tv.is_lansia=0
			AND mc.mc_flag=1
            $query_level
            $query_msp
			AND mkab.mkab_id=tv.tv_mkab_id
			AND tv.tv_mkab_id IS NOT NULL
			AND mkab.mkab_mpro_id=mpro.mpro_id) AS jml
			FROM master_provinsi mpro
            WHERE 1=1
            $query_search_mpro
			ORDER BY mpro.mpro_id ";

        $retdbtotal = DB::connection('pgsql_vaksin')->select($query);
        $jmltotal=(count($retdbtotal));
        
        if(isset($request->limit)) {
            $limit = $request->limit;
            $sql_limit = ' LIMIT '.$request->limit;
            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));
            
            $query .= $sql_limit;
            
            if (isset($request->page)) {
                $page = $request->page;
                $offset = ((int)$page-1) * (int)$limit;
                $sql_offset= ' OFFSET '.$offset;
                
                $query .= $sql_offset;
            }
        }
        
        $retdb = DB::connection('pgsql_vaksin')->select($query);
        foreach($retdb as $dvp){
    	    $query_kab = "SELECT mkab.mkab_id, mkab.mkab_name,
    			(SELECT COALESCE(COUNT(*))
    			FROM transaksi_vaksin tv
    			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
    			WHERE tv.is_lansia=0
    			AND mc.mc_flag=1
    		    $query_level
                $query_msp
    			AND tv.tv_mkab_id IS NOT NULL
    			AND mkab.mkab_id=tv.tv_mkab_id) AS jml
    			FROM master_kabupaten mkab
    			WHERE 1=1
                $query_search_mkab
                AND mkab_mpro_id=$dvp->mpro_id
    			ORDER BY mkab.mkab_name ";
    	    
            $retdb_kab = DB::connection('pgsql_vaksin')->select($query_kab);
            $data_kab = array();
            foreach($retdb_kab as $dvp_kab){
                $data_kab[] = array(
                    "id" => $dvp_kab->mkab_id,
                    "judul" => $dvp_kab->mkab_name,
                    "jml" => $dvp_kab->jml
                );
            }
            
            $data[] = array(
                "id" => $dvp->mpro_id,
                "judul" => $dvp->mpro_name,
                "jml" => $dvp->jml,
                "kab" => $data_kab
            );
        }
        return response()->json(['status' => 200, 'page_end'=>$endpage, 'data' => $data]);
	}
}
