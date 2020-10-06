<?php

namespace App\Http\Controllers;

use App\Company;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;

use DB;


class DashClusterController extends Controller
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

	public function getClusterDashboardHead($id){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_clusterdashmin_head".$id, 360 * 60, function()use($id){
	        $data = array();
	        $dashboard_head = DB::select("SELECT * FROM cluster_dashboard_head('$id')");

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

	public function getClusterPerimeterbyKategoriAll($id){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_getcluster_perimeter_bykategori_all".$id, 360 * 60, function()use($id){
	        $data = array();
	        $perimeter_bykategori_all = DB::select("SELECT * FROM cluster_dashboard_perimeter_bykategori('$id')");
	        
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
	
	public function getClusterPerimeterbyProvinsiAll($id){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_getcluster_perimeter_byprovinsi_all".$id, 360 * 60, function()use($id){
	        $data = array();
	        $perimeter_byprovinsi_all = DB::select("SELECT * FROM cluster_dashboard_perimeter_byprovinsi('$id')");
	        
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
	
	public function getClusterCosmicIndexAll($id){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_getcluster_cosmicindex_all".$id, 360 * 60, function()use($id){
	        $data = array();
	        $cosmicindex_all = DB::select("SELECT * FROM cluster_dashboard_perimeter_bycosmicindex('$id')");
	        
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
}
