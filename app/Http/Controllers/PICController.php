<?php

namespace App\Http\Controllers;


use App\ClusterRuangan;
use App\Perimeter;
use App\PerimeterLevel;
use App\PerimeterDetail;
use App\PerimeterKategori;
use App\TrnAktifitas;

use App\User;
use App\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Intervention\Image\ImageManagerStatic as Image;

use Storage;
use DB;


class PICController extends Controller
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

	//Jumlah Perimeter
	public function updateDailyMonitoring(Request $request){
		$this->validate($request, [
            'id_perimeter_cluster' => 'required',
			'id_konfig_cluster_aktifitas' => 'required',
			'nik' => 'required',
			'file_foto' => 'required',
        ]);
		
		$user = User::where(DB::raw("TRIM(username)"),'=',trim($request->nik))->first();
		if($user==null){
			 return response()->json(['status' => 404,'message' => 'User Tidak Ditemukan'])->setStatusCode(404);
		}	
		$kd_perusahaan = $user->mc_id;
		$file = $request->file_foto;
		$id_perimeter_cluster = $request->id_perimeter_cluster;
		$id_konfig_cluster_aktifitas = $request->id_konfig_cluster_aktifitas;
		$nik = trim($request->nik);
		
		$user_id = $request->user_id;
		$tanggal= Carbon::now()->format('Y-m-d');
		
		
        if(!Storage::exists('/public/aktifitas/'.$kd_perusahaan.'/'.$tanggal)) {
            Storage::disk('public')->makeDirectory('/aktifitas/'.$kd_perusahaan.'/'.$tanggal);
        }
  
        $destinationPath = base_path("storage\app\public\aktifitas/").$kd_perusahaan.'/'.$tanggal;
		$name1 = round(microtime(true) * 1000).'.jpg';
        $name2 = round(microtime(true) * 1000).'_tumb.jpg';
		
        if ($file != null || $file != '') {
            $img1 = explode(',', $file);
            $image1 = $img1[1];
            $filedecode1 = base64_decode($image1);
            

            Image::make($filedecode1)->resize(700, NULL, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$name1);
			Image::make($filedecode1)->resize(50, NULL, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$name2);
        }
		
		$trn_aktifitas= TrnAktifitas::create(
            ['ta_tpmd_id' => $id_perimeter_cluster, 'ta_nik' => $nik, 'ta_kcar_id' => $id_konfig_cluster_aktifitas,'ta_date' => $tanggal, 'ta_file' => $name1, 'ta_filetumb' => $name2]);
		


        if($trn_aktifitas) {
            return response()->json(['status' => 200,'message' => 'Data Berhasil Disimpan']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
        }
		

	}



    //
}
