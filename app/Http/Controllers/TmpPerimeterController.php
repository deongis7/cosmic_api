<?php

namespace App\Http\Controllers;

use App\TmpPerimeter;
use App\Region;
use App\Perimeter;
use App\PerimeterLevel;
use App\PerimeterKategori;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


class TmpPerimeterController extends Controller
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
		$data = TmpPerimeter::all();
		return response($data);
	}
	public function show($id){
		$data = TmpPerimeter::where('id',$id)->get();
		return response ($data);
	}
	
	public function store (Request $request){

	}

	public function parsingPerimeter(){
		
		$tmp_perimeter = TmpPerimeter::where('status','=',0)
					//->where(function($query){
					//		 $query->orWhere('nama_file_validity', '=', null);
					//		 $query->orWhere('nama_file_validity', '=', '');
					//	 })

					->get();
					//dd($tmp_perimeter);
		foreach($tmp_perimeter as $item_tmp_perimeter){
			
			//cek region
			$cekdata_region=(Region::where('mr_name','like','%'.$item_tmp_perimeter->region.'%')
							->where('mr_mc_id',$item_tmp_perimeter->kd_perusahaan)->count());
			//cek region sudah terdaftar di master atau belum
			if ($cekdata_region == 0) {
				$data_region = new Region();
				$data_region->mr_name = $item_tmp_perimeter->region;
				$data_region->mr_mc_id = $item_tmp_perimeter->kd_perusahaan;
				
				$data_region->save();
			}							
			$region_id = Region::where('mr_name','like','%'.$item_tmp_perimeter->region.'%')
							->where('mr_mc_id',$item_tmp_perimeter->kd_perusahaan)->first()->mr_id;
			
			
			//cek kategori perimeter
			$cekdata_kat_perimeter=(PerimeterKategori::where('mpmk_name','like','%'.$item_tmp_perimeter->k_perimeter.'%')->count());
			
			//cek kategori perimeter sudah terdaftar di master atau belum
			if ($cekdata_kat_perimeter == 0) {
				$data_kat_perimeter = new PerimeterKategori();
				$data_kat_perimeter->mpmk_name = $item_tmp_perimeter->k_perimeter;
				
				$data_kat_perimeter->save();
			}							
			$kat_perimeter_id = PerimeterKategori::where('mpmk_name','like','%'.$item_tmp_perimeter->k_perimeter.'%')->first()->mpmk_id;
			
			
			//cek pic
			$cekdata_pic=(User::where('mu_nik','like','%'.$item_tmp_perimeter->nik_pic.'%')->count());
			
			//cek  user sudah terdaftar di master atau belum
			if ($cekdata_pic == 0) {
				$data_pic = new User();
				$data_pic->mu_nik= $item_tmp_perimeter->nik_pic;
				$data_pic->mu_username= $item_tmp_perimeter->nik_pic;
				$data_pic->mu_password= Hash::make('P@ssw0rd');
				$data_pic->mu_name = $item_tmp_perimeter->pic;
				$data_pic->mu_mc_id= $item_tmp_perimeter->kd_perusahaan;
				
								
				$data_pic->save();
			}				
			
			$pic_nik = $item_tmp_perimeter->nik_pic;
			
			
			//cek perimeter
			$cekdata_perimeter=(Perimeter::where('mpm_mr_id',$region_id)
							->where('mpm_name','like','%'.$item_tmp_perimeter->perimeter.'%')->count());
			
			//cek perimeter sudah terdaftar di master atau belum
			if ($cekdata_perimeter == 0) {
				$data_perimeter = new Perimeter();
				$data_perimeter->mpm_mr_id= $region_id;
				$data_perimeter->mpm_name = $item_tmp_perimeter->perimeter;
				$data_perimeter->mpm_mpmk_id = $kat_perimeter_id;
				$data_perimeter->mpm_mu_nik = $pic_nik;
				$data_perimeter->mpm_longitude = $item_tmp_perimeter->longitude;
				$data_perimeter->mpm_latitude = $item_tmp_perimeter->latitude;
				
				
				$data_perimeter->save();
			}	else {
				$data_perimeter=(Perimeter::where('mpm_mr_id',$region_id)
							->where('mpm_name','like','%'.$item_tmp_perimeter->perimeter.'%')->first());
				$data_perimeter->mpm_mpmk_id = $kat_perimeter_id;
				$data_perimeter->mpm_mu_nik = $pic_nik;		
				$data_perimeter->mpm_longitude = $item_tmp_perimeter->longitude;
				$data_perimeter->mpm_latitude = $item_tmp_perimeter->latitude;				
				$data_perimeter->save();			
			}				
			
			$perimeter_id =(Perimeter::where('mpm_mr_id',$region_id)
							->where('mpm_name','like','%'.$item_tmp_perimeter->perimeter.'%')->first()->mpm_id);
			
			
			//cek fo user
			$cekdata_fo=(User::where('mu_nik','like','%'.$item_tmp_perimeter->nik_fo.'%')->count());
			
			//cek  fo user sudah terdaftar di master atau belum
			if ($cekdata_fo == 0) {
				$data_fo = new User();
				$data_fo->mu_nik= $item_tmp_perimeter->nik_fo;
				$data_fo->mu_username= $item_tmp_perimeter->nik_fo;
				$data_fo->mu_password= Hash::make('P@ssw0rd');
				$data_fo->mu_name = $item_tmp_perimeter->fo;
				$data_fo->mu_mc_id = $item_tmp_perimeter->kd_perusahaan;
				
				
				$data_fo->save();
			}				
			
			$fo_nik = $item_tmp_perimeter->nik_fo;
			
			
			//cek perimeter_level
			$cekdata_perimeter_level=(PerimeterLevel::where('mpml_mpm_id',$perimeter_id)
							->where('mpml_name','=',$item_tmp_perimeter->level)->count());
			
			//cek perimeter_level sudah terdaftar di master atau belum
			if ($cekdata_perimeter_level == 0) {
				$data_perimeter_level = new PerimeterLevel();
				$data_perimeter_level->mpml_mpm_id= $perimeter_id;
				$data_perimeter_level->mpml_name = $item_tmp_perimeter->level;
				$data_perimeter_level->mpml_ket = (isset($item_tmp_perimeter->keterangan)? '':$item_tmp_perimeter->keterangan);
				$data_perimeter_level->mpml_mu_nik = $fo_nik;
				
				$data_perimeter_level->save();
			}	else {
				$data_perimeter_level=(PerimeterLevel::where('mpml_mpm_id',$perimeter_id)
							->where('mpml_name','=',$item_tmp_perimeter->level)->first());
				$data_perimeter_level->mpml_ket =(isset($item_tmp_perimeter->keterangan)? '':$item_tmp_perimeter->keterangan);
				$data_perimeter_level->mpml_mu_nik = $fo_nik;		
				$data_perimeter_level->save();			
			}				
			
			$perimeter_level_id =(PerimeterLevel::where('mpml_mpm_id',$perimeter_id)
							->where('mpml_name','=',$item_tmp_perimeter->level)->first()->mpml_id);
			
			
			
		}

	}
	
	


    //
}
