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
	    $datacache =  Cache::remember("get_cosmicindex_all", 30 * 60, function() {
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
	    $datacache =  Cache::remember("get_perimeter_bykategori_all", 10 * 60, function(){
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
	    $datacache =  Cache::remember("get_perimeter_byprovinsi_all", 10 * 60, function() {
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
	    $datacache =  Cache::remember("get_dashboard_head", 10 * 60, function() {
	        $data = array();
	        $dashboard_head = DB::select("SELECT * FROM dashboard_head()");
	        
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
	
	public function getWeekList(){
	    $datacache =  Cache::remember("get_week", 60 * 60, function() {
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
	    $datacache =  Cache::remember("get_week", 10 * 60, function() {
	        $data = array();
	        $dashboard_head = DB::select("SELECT * pemenuhan_monitoring_bymcidweek($id,$tgl)");
	        
	        foreach($dashboard_head as $dh){
	            $data[] = array(
	                "v_monitoring" => $dh->v_monitoring,
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}
	
}
