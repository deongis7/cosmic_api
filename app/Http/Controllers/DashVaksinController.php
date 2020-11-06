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
	        $dashvaksin = DB::select("SELECT * FROM vaksin_dashboard()");

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
	    $dashvaksin = DB::select("SELECT * FROM vaksin_summary_bymcid('$id')");
	    
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
	    $dashkasus_perusahaan = DB::select("SELECT * FROM vaksin_dashboard_perusahaan()");
	    
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
	    $dashkasus_provinsi = DB::select("SELECT * FROM vaksin_dashboard_provinsi()");
	    
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
	    $dashkasus_kabupaten = DB::select("SELECT * FROM vaksin_dashboard_kabupaten()");
	    
	    foreach($dashkasus_kabupaten as $dvk){
	        $data[] = array(
	            "v_mkab" => $dvk->v_mkab,
	            "v_jml" => $dvk->v_jml
	        );
	    }
	    //});
	    return response()->json(['status' => 200,'data' => $data]);
	}
}
