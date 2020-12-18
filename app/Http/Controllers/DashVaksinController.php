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
	    $dashkasus_lokasi1 = DB::connection('pgsql_vaksin')->select("SELECT * FROM vaksin_dashboard_lokasi1()");
	    //$dashkasus_kabupaten = DB::select("SELECT * FROM vaksin_dashboard_lokasi1()");
	    
	    foreach($dashkasus_lokasi1 as $dl1){
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
	    $dashkasus_lokasi2 = DB::connection('pgsql_vaksin')->select("SELECT * FROM vaksin_dashboard_lokasi2()");
	    //$dashkasus_kabupaten = DB::select("SELECT * FROM vaksin_dashboard_lokasi2()");
	    
	    foreach($dashkasus_lokasi2 as $dl2){
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
	    $dashkasus_lokasi3 = DB::connection('pgsql_vaksin')->select("SELECT * FROM vaksin_dashboard_lokasi3()");
	    //$dashkasus_kabupaten = DB::select("SELECT * FROM vaksin_dashboard_lokasi3()");
	    
	    foreach($dashkasus_lokasi3 as $dl3){
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
        ->leftjoin('master_provinsi AS mpro','mpro.mpro_id','mkab.mkab_mpro_id')
        ->where('mc.mc_level', 1);
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
                    if (!file_exists(base_path("storage/app/public/vaksin_eviden/".$vksn->tv_file1))) {
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
                    if (!file_exists(base_path("storage/app/public/vaksin_eviden/".$vksn->tv_file2))) {
                        $path_file404 = '/404/img404.jpg';
                        $filevksn2 = $path_file404;
                    }else{
                        $path_file2 = '/vaksin_eviden/'.$vksn->tv_file2;
                        $filevksn2 = $path_file1;
                    }
                }else{
                    $filevksn2 = '/404/img404.jpg';
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
}
