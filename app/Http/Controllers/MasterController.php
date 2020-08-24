<?php

namespace App\Http\Controllers;


use App\Kota;
use App\Provinsi;

use App\Helpers\AppHelper;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;

use DB;
use App\Company;
use App\MstStsKasus;
use App\MstStsPegawai;


class MasterController extends Controller
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

	public function getAllKota(){
		$datacache = Cache::remember("get_all_kota", 360 * 60, function() {
			$kota = Kota::join('master_provinsi','mpro_id','mkab_mpro_id')->orderBy('mkab_name','asc')->get();
			
			foreach($kota as $itemkota){
				$data[] = array(
					"id_kota" => $itemkota->mkab_id,
					"kota" => $itemkota->mkab_name,
					"id_provinsi" => $itemkota->mkab_mpro_id,
					"provinsi" => $itemkota->mpro_name,

				);
			}
			return $data;
		});
		return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getKotaByProvinsi($id_provinsi){
		$datacache = Cache::remember("get_kota_by_prov_".$id_provinsi, 360 * 60, function() use ($id_provinsi) {
			$kota = Kota::join('master_provinsi','mpro_id','mkab_mpro_id')
							->where('mkab_mpro_id',$id_provinsi)
							->orderBy('mkab_name','asc')->get();
			
			foreach($kota as $itemkota){
				$data[] = array(
					"id_kota" => $itemkota->mkab_id,
					"kota" => $itemkota->mkab_name,
					"id_provinsi" => $itemkota->mkab_mpro_id,
					"provinsi" => $itemkota->mpro_name,

				);
			}
			return $data;
		});
		return response()->json(['status' => 200,'data' => $datacache]);
	}
	
	public function getAllProvinsi(){
		$datacache = Cache::remember("get_all_prov", 360 * 60, function() {
			$prov = Provinsi::all();
			
			foreach($prov as $itemprov){
				$data[] = array(
				   
					"id_provinsi" => $itemprov->mpro_id,
					"provinsi" => $itemprov->mpro_name,

				);
			}
			return $data;
		});
		return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getAllCompany(){
	    $Path = '/foto_bumn/';
	    
	    $datacache = Cache::remember("get_all_company", 360 * 60, function() {
	        $company = Company::all();
	        
	        foreach($company as $com){
	            $data[] = array(
	                "id_perusahaan" => $com->mc_id,
	                "kd_perusahaan" => $com->mc_code,
	                "nm_perusahaan" => $com->mc_name,
	                "foto" => $Path.$com->mc_foto
	            );
	        }
	        return $data;
	    });
	    
	    return response()->json(['status' => 200,'data' => $datacache]);
	}
	
	public function getDetailCompany($id) {
	    $Path = '/foto_bumn/';
	    
	    $datacache = Cache::remember("get_company_by_mcid_".$id, 360 * 60, function() use ($id) {
	        $company = Company::join('master_sektor','ms_id','mc_msc_id')
	        ->where('mc_id',$id)->where('ms_type','CCOVID')->get();
	        
	        foreach($company as $com){
	            $data[] = array(
	                "id_perusahaan" => $com->mc_id,
	                "kd_perusahaan" => $com->mc_code,
	                "nm_perusahaan" => $com->mc_name,
	                "foto" => $Path.$com->mc_foto,
	                "sektor" => $com->ms_name
	            );
	        }
	        return $data;
	    });
        return response()->json(['status' => 200,'data' => $datacache]);
	}
	
	public function getAllStsKasus(){
        $datacache = Cache::remember("get_all_mskasus", 360 * 60, function() {
            $mststskasus = MstStsKasus::all();
            
            foreach($mststskasus as $msk){
                $data[] = array(
                    "id" => $msk->msk_id,
                    "name" => $msk->msk_name,
                    "name2" => $msk->msk_name2,
                );
            }
            return $data;
        });
        return response()->json(['status' => 200,'data' => $datacache]);
    }
    
    public function getAllStsPegawai(){
        //var_dump();die;
        $datacache = Cache::remember("get_all_mspegawai", 360 * 60, function() {
            $mststspegawai = MstStsPegawai::all();
            
            foreach($mststspegawai as $msp){
                $data[] = array(
                    "id" => $msp->msp_id,
                    "name" => $msp->msp_name,
                );
            }
            return $data;
        });
        return response()->json(['status' => 200,'data' => $datacache]);
    }
}
