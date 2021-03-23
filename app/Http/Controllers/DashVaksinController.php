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

	public function getDashVaksin(Request $request){
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level='.$level;
	    }else{
	        $level = 0;
	    }
	    
	    $query_mc_id = ' ';
	    if(isset($request->kd_perusahaan)) {
	        $mc_id = $request->kd_perusahaan;
	        $query_mc_id = " AND mc.mc_id_induk= '$mc_id'";
	    }else{
	        $mc_id = 'ALL';
	    }
	    
	    $string = "_get_dashvaksinhead_".$level.'_'.$mc_id;
	    //$datacache = Cache::tags(['users'])->remember(env('APP_ENV', 'dev').$string, 10, function () use($level, $mc_id) {

    	    $data = array();
            $query = "
                SELECT 0::int2, 'Total Pegawai BUMN' judul, 
                    COALESCE(COUNT(*))  AS jml
                FROM transaksi_vaksin tv 
                INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
                WHERE tv.is_lansia=0
                AND mc.mc_flag=1
                $query_level
                $query_mc_id
                UNION ALL 
                SELECT 1::int2, 'SIAP VAKSIN' judul, 
                    COALESCE(COUNT(*))  AS jml
                FROM transaksi_vaksin tv 
                INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
                WHERE tv.is_lansia=0
                AND mc.mc_flag=1
                $query_level
                $query_mc_id
                AND tv.tv_status_vaksin_pcare=0
                UNION ALL 
                SELECT 2::int2, 'SUDAH VAKSIN 1' judul, 
                    COALESCE(COUNT(*))  AS jml
                FROM transaksi_vaksin tv 
                INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
                WHERE tv.is_lansia=0
                AND mc.mc_flag=1
                $query_level
                $query_mc_id
                AND tv.tv_status_vaksin_pcare=1
                UNION ALL 
                SELECT 3::int2, 'SUDAH VAKSIN 2' judul, 
                    COALESCE(COUNT(*))  AS jml
                FROM transaksi_vaksin tv 
                INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
                WHERE tv.is_lansia=0
                AND mc.mc_flag=1
                $query_level
                $query_mc_id
                AND tv.tv_status_vaksin_pcare=2
                UNION ALL 
                SELECT 4::int2, 'Total Keluarga inti Pegawai' judul, 
                    SUM(COALESCE(tv_jml_keluarga,0)) AS jml
                FROM transaksi_vaksin tv 
                INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
                WHERE tv.is_lansia=0
                AND mc.mc_flag=1
                $query_level 
                $query_mc_id ";
        
                $dashvaksin = DB::connection('pgsql_vaksin')->select($query);
                foreach($dashvaksin as $dv){
                    $data[] = array(
                        "v_judul" => $dv->judul,
                        "v_jml" => $dv->jml
                    );
                }
            return $data;
	    //});
        //Cache::tags(['users'])->flush();
        //return response()->json(['status' => 200,'data' => $datacache]);
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
	
	public function getDashVaksinPerusahaan(Request $request){
	    $level = 0;
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level='.$level;
	    }
	    
	    $mc_id = 'ALL';
	    $query_mc_id = ' ';
	    if(isset($request->kd_perusahaan)) {
	        $mc_id = $request->kd_perusahaan;
	        $query_mc_id = " AND mc.mc_id_induk= '$mc_id'";
	    }
	    
	    $data = array();
	    $query = "SELECT mc.mc_id, mc.mc_name,
					(SELECT COALESCE(COUNT(*)) 
					FROM transaksi_vaksin tv 
                    INNER JOIN master_kabupaten mkab ON mkab.mkab_id=tv.tv_mkab_id
					WHERE tv.is_lansia=0
					AND tv.tv_mc_id=mc.mc_id) AS jml
				FROM master_company mc
				WHERE mc.mc_flag=1
				$query_level
				$query_mc_id
				ORDER BY mc.mc_name ";
            
		$dashvaksin_perusahaan = DB::connection('pgsql_vaksin')->select($query);
	    foreach($dashvaksin_perusahaan as $dvp){
    	        $data[] = array(
    	            "v_mc_id" => $dvp->mc_id,
    	            "v_mc_name" => $dvp->mc_name,
    	            "v_jml" => $dvp->jml
    	        );
    	    }
	    //});
	    return response()->json(['status' => 200,'data' => $data]);
	}

	public function getDashVaksinPerusahaanFilter(Request $request){
		$limit = null;
        $page = null;
        $endpage = 1;

        if(isset($request->limit)){
            // $str = $str.'_limit_'. $request->limit;
            $limit=$request->limit;
            if(isset($request->page)){
                // $str = $str.'_page_'. $request->page;
                $page=$request->page;
            }
        }

		 $filter_perusahaan = $request->status_perusahaan;
		 $filter_pegawai = $request->status_pegawai;
		 $filter_name = $request->nama_perusahaan;
		/*$filter_perusahaan = $sp1;
		$filter_pegawai = $sp2;*/
		$w1 = " AND mc_level IN (1,2,3)";
		if(!empty($filter_perusahaan)){
			$w1 = " AND mc_level = '$filter_perusahaan'";
		}

		$w2 = " ";
		if(!empty($filter_pegawai)){
			$w2 = " AND tv.tv_msp_id = '$filter_pegawai'";
		}

		$w3 = " ";
		if(!empty($filter_name)){
			$w3 = " AND mc1.mc_name LIKE '%$filter_name%'";
		}

		$string = "SELECT mc_id, mc_name, 
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
				AND mc.mc_id=mc1.mc_id
				
				) jml
				FROM master_company mc1
				where 1=1
				$w1
				$w3
				ORDER BY mc_name ";

		$string_count = "SELECT count(*) count
				FROM master_company mc1
				where 1=1
				$w1
				$w3";		

	    //$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashvaksin_perusahaan", 15 * 60, function() {
	    $data = array();
	    $count = DB::connection('pgsql_vaksin')->select($string_count);

	    $jmltotal=$count[0]->count;
            // dd($jmltotal);
            if(isset($request->limit)) {
                $limit = $request->limit;
                $sql_limit = ' LIMIT '.$request->limit;
                $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

                $string .= $sql_limit;

                if (isset($request->page)) {
                    $page = $request->page;
                    $offset = ((int)$page-1) * (int)$limit;
                    $sql_offset= ' OFFSET '.$offset;

                    $string .= $sql_offset;
                }
            }
	    $get_data = DB::connection('pgsql_vaksin')->select($string);
	    foreach($get_data as $dvp){
    	        $data[] = array(
    	            "v_mc_id" => $dvp->mc_id,
    	            "v_mc_name" => $dvp->mc_name,
    	            "v_jml" => $dvp->jml
    	        );
    	    }
	    //});
	    return response()->json(['status' => 200,'page_end' =>$endpage,'data' => $data]);
	}

	
	public function getDashVaksinPegawaiFilter(Request $request){
		$limit = null;
        $page = null;
        $endpage = 10;
        $filter_nama = strtoupper($request->name);
		$filter_mc_id = $request->mc_id;
		$filter_status = $request->status;
        
        $str = "_get_dashvaksin_pegawai".$filter_mc_id.$filter_nama;
        if(isset($request->limit)){
            $str = $str.'_limit_'. $request->limit;
            $limit=$request->limit;
            if(isset($request->page)){
                $str = $str.'_page_'. $request->page;
                $page=$request->page;
            }
        }

      	if(isset($request->name)){
            $str = $str.'_search_'. str_replace(' ','_',$request->name);
            $filter_nama = strtoupper($request->name);
        }
        if(isset($request->status)){
            $str = $str.'_status_'. str_replace(' ','_',$request->status);
            $filter_status=$request->status;
        }

      	//dd($string);
        $datacache = Cache::tags([$str])->remember(env('APP_ENV', 'dev').$str, 5 * 10, function () use($filter_nama, $filter_mc_id, $filter_status, $limit, $page, $endpage) {
		/*$datacache = Cache::remember(env('APP_ENV', 'dev').$str, 5 * 10, function()use($filter_nama, $filter_mc_id, $filter_status, $limit, $page, $endpage) {*/
	        $data = array();
			
		    $vaksin = new Vaksin();
		    $vaksin->setConnection('pgsql_vaksin');
		    $vaksin = $vaksin->select('tv_nik', 'tv_nama', 'msp_name2')
	        ->leftjoin('master_status_pegawai AS msp','msp.msp_id','tv_msp_id')
	        ->where('tv_mc_id', $filter_mc_id)
	        ->where('is_lansia', 0);

	        if(!empty($filter_nama)) {
	            $search = $filter_nama;
	            $vaksin = $vaksin->where(DB::raw("lower(TRIM(tv_nama))"),'like','%'.strtolower(trim($search)).'%');
	        }

	        if(!empty($filter_status)) {
	            $vaksin = $vaksin->where(DB::raw("tv_msp_id"),'=',trim($filter_status));
	        }
	        $jmltotal=($vaksin->count());
	        if(isset($limit)) {
	            $vaksin = $vaksin->limit($limit);
	            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));
	        	//dd($endpage);
	            
	            if (!empty($page)) {
	                $offset = ((int)$page -1) * (int)$limit;
	                $vaksin = $vaksin->offset($offset);
	            }
	        }
	        
		    
		    $vaksin = $vaksin->get();
	        foreach($vaksin as $dvp){
    	        $data[] = array(
    	            "nik" => $dvp->tv_nik,
    	            "nama" => $dvp->tv_nama,
    	            "status" => $dvp->msp_name2,
    	            "photo" => "https://png.pngtree.com/png-clipart/20190924/original/pngtree-user-vector-avatar-png-image_4830521.jpg"
    	        );
    	    }
	    	//return $data;
		    return array('status' => 200,'page_end' =>$endpage,'data' => $data);
		});
		    Cache::tags([$str])->flush();
	    return response()->json($datacache);
	}
	
	public function getDashVaksinProvinsi(Request $request){
	    $level = 0;
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level='.$level;
	    }
	    
	    $mc_id = 'ALL';
	    $query_mc_id = ' ';
	    if(isset($request->kd_perusahaan)) {
	        $mc_id = $request->kd_perusahaan;
	        $query_mc_id = " AND mc.mc_id_induk= '$mc_id'";
	    }
	    
	    $data = array();
	    $query = "SELECT mpro.mpro_name::TEXT,
    		(SELECT COALESCE(COUNT(*)) 
    		FROM transaksi_vaksin tv 
    		INNER JOIN master_kabupaten mkab ON mkab.mkab_id=tv.tv_mkab_id
    		INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
    		WHERE tv.is_lansia=0
    		AND mc.mc_flag=1
    		$query_level
    		$query_mc_id
    		AND mkab.mkab_id=tv.tv_mkab_id
    		AND tv.tv_mkab_id IS NOT NULL
    		AND mkab.mkab_mpro_id=mpro.mpro_id)::int8 AS jml
    		FROM master_provinsi mpro
    		ORDER BY mpro.mpro_id";
				
		$dashkasus_provinsi = DB::connection('pgsql_vaksin')->select($query);
	    foreach($dashkasus_provinsi as $dvp){
	        $data[] = array(
	            "v_mpro" => $dvp->mpro_name,
	            "v_jml" => $dvp->jml
	        );
	    }
	    //});
	    return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksinKabupaten(Request $request){
	    $level = 0;
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level='.$level;
	    }
	    
	    $mc_id = 'ALL';
	    $query_mc_id = ' ';
	    if(isset($request->kd_perusahaan)) {
	        $mc_id = $request->kd_perusahaan;
	        $query_mc_id = " AND mc.mc_id_induk= '$mc_id'";
	    }
	    
	    $data = array();
	    $query = "SELECT mkab.mkab_name::TEXT,
				(SELECT COALESCE(COUNT(*)) 
				FROM transaksi_vaksin tv 
				INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
				WHERE tv.is_lansia=0
				AND mc.mc_flag=1
				$query_level
				$query_mc_id
				AND tv.tv_mkab_id=mkab.mkab_id)::int8 AS jml
				FROM master_kabupaten mkab
				ORDER BY mkab.mkab_name;";
	    
		$dashkasus_kabupaten = DB::connection('pgsql_vaksin')->select($query);
	    foreach($dashkasus_kabupaten as $dvk){
	        $data[] = array(
	            "v_mkab" => $dvk->mkab_name,
	            "v_jml" => $dvk->jml
	        );
	    }
	    //});
	    return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksinLokasi1(Request $request){
	    $level = 0;
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level='.$level;
	    }
	    
	    $mc_id = 'ALL';
	    $query_mc_id = ' ';
	    if(isset($request->kd_perusahaan)) {
	        $mc_id = $request->kd_perusahaan;
	        $query_mc_id = " AND mc.mc_id_induk= '$mc_id'";
	    }
	    
	    $data = array();
	    $query = "SELECT tv.tv_lokasi1::TEXT, COALESCE(COUNT(*))::int8 AS jml
				FROM transaksi_vaksin tv 
				INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
				WHERE tv.is_lansia=0
				AND mc.mc_flag=1
				$query_level
				$query_mc_id
				AND (tv_lokasi1 !=NULL or tv_lokasi1 !='')
				GROUP BY tv.tv_lokasi1
				ORDER BY tv.tv_lokasi1";
				
		$dashvaksin_lokasi1 = DB::connection('pgsql_vaksin')->select($query);
	    foreach($dashvaksin_lokasi1 as $dl1){
	        $data[] = array(
	            "v_lokasi" => $dl1->tv_lokasi1,
	            "v_jml" => $dl1->jml
	        );
	    }
	    //});
	    return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDashVaksinLokasi2(Request $request){
	    $level = 0;
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level='.$level;
	    }
	    
	    $mc_id = 'ALL';
	    $query_mc_id = ' ';
	    if(isset($request->kd_perusahaan)) {
	        $mc_id = $request->kd_perusahaan;
	        $query_mc_id = " AND mc.mc_id_induk= '$mc_id'";
	    }
	    
	    $data = array();
	    $query = "SELECT tv.tv_lokasi2::TEXT, COALESCE(COUNT(*))::int8 AS jml
				FROM transaksi_vaksin tv 
				INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
				WHERE tv.is_lansia=0
				AND mc.mc_flag=1
				$query_level
				$query_mc_id
				AND (tv_lokasi2 !=NULL or tv_lokasi2 !='')
				GROUP BY tv.tv_lokasi2
				ORDER BY tv.tv_lokasi2";
				
		$dashvaksin_lokasi2 = DB::connection('pgsql_vaksin')->select($query);
	    foreach($dashvaksin_lokasi2 as $dl2){
	        $data[] = array(
	            "v_lokasi" => $dl2->tv_lokasi2,
	            "v_jml" => $dl2->jml
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
	    $endpage = 1;
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
