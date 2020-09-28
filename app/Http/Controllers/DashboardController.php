<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;

use DB;


class DashboardController extends Controller
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

	public function getCosmicIndexAll(){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_cosmicindex_all", 360 * 60, function() {
	        $data = array();
	        $cosmicindex_all = DB::select("SELECT * FROM dashboard_perimeter_bycosmicindex()");

	        foreach($cosmicindex_all as $cia){
	            $data[] = array(
	                "v_judul" => $cia->z_judul,
	                "v_jml" => $cia->z_jml
	            );
	        }
	        return $data;
	    });
        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getPerimeterbyKategoriAll(){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_perimeter_bykategori_all", 360 * 60, function(){
	        $data = array();
	        $perimeter_bykategori_all = DB::select("SELECT * FROM dashboard_perimeter_bykategori()");

	        foreach($perimeter_bykategori_all as $pka){
	            $data[] = array(
	                "v_judul" => $pka->v_judul,
	                "v_jml" => $pka->v_jml
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getPerimeterbyProvinsiAll(){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_perimeter_byprovinsi_all", 360 * 60, function() {
	        $data = array();
	        $perimeter_byprovinsi_all = DB::select("SELECT * FROM dashboard_perimeter_byprovinsi()");

	        foreach($perimeter_byprovinsi_all as $ppa){
	            $data[] = array(
	                "v_judul" => $ppa->v_judul,
	                "v_jml" => $ppa->v_jml
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getDashboardHead(){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashmin_head", 360 * 60, function() {
	        $data = array();
	        $dashboard_head = DB::select("SELECT * FROM dashboard_head()");

	        foreach($dashboard_head as $dh){
	            $data[] = array(
	                "v_id" => $dh->x_id,
	                "v_judul" => $dh->x_judul,
	                "v_jml" => $dh->x_jml,
	                "v_flag_link" => $dh->x_flag_link,
	                "v_link" => $dh->x_link
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getWeekList(){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_week", 360 * 60, function() {
	        $data = array();
	        $dashboard_head = DB::select("SELECT * FROM list_aktivitas_week()");

	        foreach($dashboard_head as $dh){
	            $data[] = array(
	                "v_no" => $dh->v_rownum,
	                "v_week" => $dh->v_week,
	                "v_awal" => $dh->v_awal,
	                "v_akhir" => $dh->v_akhir,
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getMonitoring_ByMcidWeek($id, $tgl){
	    $datacache = Cache::remember(env('APP_ENV', 'dev')."_getmonitoring_bymcidweek_".$id."_".$tgl, 360 * 60, function()use($id, $tgl) {
	        $data = array();
	        $dashboard_head = DB::select("SELECT * FROM pemenuhan_monitoring_bymcidweek('$id','$tgl')");

	        foreach($dashboard_head as $dh){
	            $data[] = array(
	                "v_monitoring" => $dh->v_monitoring,
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getListMonitoring_ByMcidWeek($id, $tgl){
	    $datacache = Cache::remember(env('APP_ENV', 'dev')."_getlistmonitoring_bymcidweek_".$id."_".$tgl, 360 * 60, function()use($id, $tgl) {
	        $data = array();
	        $dashboard_head = DB::select("SELECT a.v_mpm_name, a.v_mpml_name, a.v_mpmk_name,
                    a.v_pic, a.v_fo, a.v_cek, b.persen_det
                    FROM week_historymonitoring_level('$id','$tgl') a
                    INNER JOIN week_aktivitas_cnt_bymcid_weekdet_pic('$id','$tgl') b
                    ON a.v_mpm_id=b.v_mpm_id;");

	        foreach($dashboard_head as $dh){
	            $data[] = array(
	                "v_mpm_name" => $dh->v_mpm_name,
	                "v_mpml_name" => $dh->v_mpml_name,
	                "v_mpmk_name" => $dh->v_mpmk_name,
	                "v_pic" => $dh->v_pic,
	                "v_fo" => $dh->v_fo,
	                "v_cek" => $dh->v_cek,
	                "persen_det" => $dh->persen_det,
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}
	
	public function getDashboardHeadBUMN($id){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashbumn_head_".$id, 360 * 60, function()use($id) {
	        $data = array();
	        $dashboard_head = DB::select("SELECT * FROM dashboardbumn_head('$id')");
	        
	        foreach($dashboard_head as $dh){
	            $data[] = array(
	                "v_id" => $dh->x_id,
	                "v_judul" => $dh->x_judul,
	                "v_jml" => $dh->x_jml
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}
	
	public function getDashboardProtokolBUMN($id){
       // $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashprotokolbumn_".$id, 15 * 60, function()use($id) {
	        $data = array();
	        $dashboard_head = DB::select("SELECT v_mpt_id, v_mpt_name,
                        CASE WHEN v_tbpt_id > 0 THEN 'Terupload' ELSE 'Belum Terupload' END AS v_upload
                        FROM protokol_bymc('$id')");
	        
	        foreach($dashboard_head as $dh){
	            $data[] = array(
	                "v_id" => $dh->v_mpt_id,
	                "v_name" => $dh->v_mpt_name,
	                "v_status" => $dh->v_upload
	            );
	        }
	        return $data;
	   // });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}
	
	public function getDashboardMrMpmBUMN($id){
       $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashmrmpmbumn_".$id, 15 * 60, function()use($id) {
    	    $data = array();
    	    $dashboard_head = DB::select("SELECT mr_name, COUNT(mpm_id) cnt
                        FROM master_region mr
                        INNER JOIN master_perimeter mpm ON mpm_mr_id=mr.mr_id
                        WHERE mpm_id in (SELECT mpml_mpm_id FROM master_perimeter_level mpml)
                        AND mr.mr_mc_id='$id'
                        GROUP BY mr_name
                        ORDER BY mr_name");
    	    
    	    foreach($dashboard_head as $dh){
    	        $data[] = array(
    	            "v_region_name" => $dh->mr_name,
    	            "v_cnt" => $dh->cnt
    	        );
    	    }
    	    return $data;
	    });
	    return response()->json(['status' => 200,'data' => $datacache]);
	}
}