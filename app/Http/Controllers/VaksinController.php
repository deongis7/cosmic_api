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

class VaksinController extends Controller
{
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

	public function getDataByid($id) {
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
	        ->where('tv_id', $id);
    
        $vaksin = $vaksin->get();
        
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
            return response()->json(['status' => 200, 'data' => $data]);
        }else{
            return response()->json(['status' => 404, 'message' => 'Tidak ada data'])->setStatusCode(404);
        }
	}
	
	public function getDataByMcid($id,Request $request) {
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
	        ->where('tv_mc_id', $id);
 
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
            return response()->json(['status' => 200, 'page_end'=> $endpage, 'data' => $data]);
        }else{
            return response()->json(['status' => 404, 'message' => 'Tidak ada data'])->setStatusCode(404);
        }
	}
	
	public function deleteVaksin($id){
	    $vaksin = new Vaksin();
	    $vaksin->setConnection('pgsql_vaksin');
	    $data = $vaksin->where('tv_id',$id)->first();
	
	    if($data!=NULL){
    	    $data->delete();
    	    
    	    if($data->delete()===NULL){
    	        $file1 = storage_path('app/public/vaksin_eviden/'.$data->tv_mc_id.'/'.$data->tv_file1);
    	        if(is_file($file1)){
    	            unlink(storage_path('app/public/vaksin_eviden/'.$data->tv_mc_id.'/'.$data->tv_file1));
    	        }
    	        $file1_tumb = storage_path('app/public/vaksin_eviden/'.$data->tv_mc_id.'/'.$data->tv_file1_tumb);
    	        if(is_file($file1_tumb)){
    	            unlink(storage_path('app/public/vaksin_eviden/'.$data->tv_mc_id.'/'.$data->tv_file1_tumb));
    	        }
    	        
    	        $file2 = storage_path('app/public/vaksin_eviden/'.$data->tv_mc_id.'/'.$data->tv_file2);
    	        if(is_file($file2)){
    	            unlink(storage_path('app/public/vaksin_eviden/'.$data->tv_mc_id.'/'.$data->tv_file2));
    	        }
    	        $file2_tumb = storage_path('app/public/vaksin_eviden/'.$data->tv_mc_id.'/'.$data->tv_file2_tumb);
    	        if(is_file($file2_tumb)){
    	            unlink(storage_path('app/public/vaksin_eviden/'.$data->tv_mc_id.'/'.$data->tv_file2_tumb));
    	        }
    	        
    	        return response()->json(['status' => 200,'message' => 'Data Vaksin Berhasil diDelete']);
    	    } else {
    	        return response()->json(['status' => 500,'message' => 'Data Vaksin Gagal diDelete'])->setStatusCode(500);
    	    }
	    }else{
	        return response()->json(['status' => 404,'message' => 'Data Vaksin Tidak Ada'])->setStatusCode(404);
	    }
	}
}
