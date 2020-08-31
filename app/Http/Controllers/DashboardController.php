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
	    $datacache =  Cache::remember("get_cosmicindex_all", 20 * 60, function()use($id) {
	        $data = array();
	        $cosmicindex_all = DB::select("SELECT * FROM dashboard_perimeter_bycosmicindex()");
	        
	        foreach($cosmicindex_all as $cia){
	            $data[] = array(
	                "v_judul" => $cia->v_judul,
	                "v_jml" => $cia->v_jml
	            );
	        }
	        return $data;
	    });
	    return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getCosmicIndexAll(){
	    $datacache =  Cache::remember("get_cosmicindex_all", 20 * 60, function()use($id) {
	        $data = array();
	        $cosmicindex_all = DB::select("SELECT * FROM dashboard_perimeter_bycosmicindex()");
	        
	        foreach($cosmicindex_all as $cia){
	            $data[] = array(
	                "v_judul" => $cia->v_judul,
	                "v_jml" => $cia->v_jml
	            );
	        }
	        return $data;
	    });
        return response()->json(['status' => 200,'data' => $datacache]);
	}
	
	public function getPerimeterbyKategoriAll(){
	    $datacache =  Cache::remember("get_perimeter_bykategori_all", 20 * 60, function()use($id) {
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
	    $datacache =  Cache::remember("get_perimeter_byprovinsi_all", 20 * 60, function()use($id) {
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
}
