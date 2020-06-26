<?php

namespace App\Http\Controllers;

use App\TmpPerimeter;
use App\Region;
use App\Perimeter;
use App\PerimeterLevel;
use App\PerimeterDetail;
use App\PerimeterKategori;
use App\User;
use App\UserGroup;
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
	
    
		
		$tmp_perimeter = TmpPerimeter::where('status','=',0)->limit(20)
					->orderBy('id', 'asc')
					//->where(function($query){
					//		 $query->orWhere('nama_file_validity', '=', null);
					//		 $query->orWhere('nama_file_validity', '=', '');
					//	 })

					->get();
					
		foreach($tmp_perimeter as $item_tmp_perimeter){
			try {
			
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
			$cekdata_pic=(User::where('username','=',$item_tmp_perimeter->nik_pic)->count());
			
			//cek  user sudah terdaftar di master atau belum
			if ($cekdata_pic == 0) {
				$data_pic = new User();
				
				$data_pic->username= $item_tmp_perimeter->nik_pic;
				$data_pic->password= Hash::make('P@ssw0rd');
				$data_pic->first_name = $item_tmp_perimeter->pic;
				$data_pic->mc_id= $item_tmp_perimeter->kd_perusahaan;
												
				$data_pic->save();
				
				$data_pic_group = new UserGroup();				
				$data_pic_group->user_id= User::where('username','=',$item_tmp_perimeter->nik_pic)->first()->id;
				$data_pic_group->group_id= 3;
				$data_pic_group->save();
				//dd('berhasil');
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
				$data_perimeter->mpm_me_nik = $pic_nik;
				$data_perimeter->mpm_longitude = $item_tmp_perimeter->longitude;
				$data_perimeter->mpm_latitude = $item_tmp_perimeter->latitude;
				
				
				$data_perimeter->save();
			}	else {
				$data_perimeter=(Perimeter::where('mpm_mr_id',$region_id)
							->where('mpm_name','like','%'.$item_tmp_perimeter->perimeter.'%')->first());
				$data_perimeter->mpm_mpmk_id = $kat_perimeter_id;
				$data_perimeter->mpm_me_nik = $pic_nik;		
				$data_perimeter->mpm_longitude = $item_tmp_perimeter->longitude;
				$data_perimeter->mpm_latitude = $item_tmp_perimeter->latitude;				
				$data_perimeter->save();			
			}				
			
			$perimeter_id =(Perimeter::where('mpm_mr_id',$region_id)
							->where('mpm_name','like','%'.$item_tmp_perimeter->perimeter.'%')->first()->mpm_id);
			
			
			//cek fo user
			$cekdata_fo=(User::where('username',$item_tmp_perimeter->nik_fo)->count());
			
			//cek  fo user sudah terdaftar di master atau belum
			if ($cekdata_fo == 0) {
				$data_fo = new User();
				
				$data_fo->username= $item_tmp_perimeter->nik_fo;
				$data_fo->password= Hash::make('P@ssw0rd');
				$data_fo->first_name = $item_tmp_perimeter->fo;
				$data_fo->mc_id = $item_tmp_perimeter->kd_perusahaan;
								
				$data_fo->save();
				
				$data_fo_group = new UserGroup();				
				$data_fo_group->user_id= User::where('username','=',$item_tmp_perimeter->nik_fo)->first()->id;
				$data_fo_group->group_id= 4;
				$data_fo_group->save();
				
				
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
				$data_perimeter_level->mpml_ket = $item_tmp_perimeter->keterangan;
				$data_perimeter_level->mpml_me_nik = $fo_nik;
				
				$data_perimeter_level->save();
			}	else {
				$data_perimeter_level=(PerimeterLevel::where('mpml_mpm_id',$perimeter_id)
							->where('mpml_name','=',$item_tmp_perimeter->level)->first());
				$data_perimeter_level->mpml_ket =$item_tmp_perimeter->keterangan;
				$data_perimeter_level->mpml_me_nik = $fo_nik;		
				$data_perimeter_level->save();			
			}				
			
			$perimeter_level_id =(PerimeterLevel::where('mpml_mpm_id',$perimeter_id)
							->where('mpml_name','=',$item_tmp_perimeter->level)->first()->mpml_id);	
			//dd('berhasil');
			//lobby
			$c1= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '1'],['tpmd_cek' => (($item_tmp_perimeter->c1== 'v' || $item_tmp_perimeter->c1== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n1== '' || $item_tmp_perimeter->n1== null) ? '0':$item_tmp_perimeter->n1)]);
			
			//r kerja
			$c2= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '2'],['tpmd_cek' => (($item_tmp_perimeter->c2== 'v' || $item_tmp_perimeter->c2== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n2== '' || $item_tmp_perimeter->n2== null) ? '0':$item_tmp_perimeter->n2)]);
			
			//r meeting
			$c3= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '3'],['tpmd_cek' => (($item_tmp_perimeter->c3== 'v' || $item_tmp_perimeter->c3== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n3== '' || $item_tmp_perimeter->n3== null) ? '0':$item_tmp_perimeter->n3)]);
			
			//toilet
			$c4= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '4'],['tpmd_cek' => (($item_tmp_perimeter->c4== 'v' || $item_tmp_perimeter->c4== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n4== '' || $item_tmp_perimeter->n4== null) ? '0':$item_tmp_perimeter->n4)]);
			
			//a_tangga
			$c5= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '5'],['tpmd_cek' => (($item_tmp_perimeter->c5== 'v' || $item_tmp_perimeter->c5== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n5== '' || $item_tmp_perimeter->n5== null) ? '0':$item_tmp_perimeter->n5)]);
			
			//a_lift
			$c6= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '6'],['tpmd_cek' => (($item_tmp_perimeter->c6== 'v' || $item_tmp_perimeter->c6== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n6== '' || $item_tmp_perimeter->n6== null) ? '0':$item_tmp_perimeter->n6)]);
			
			//r_tunggu
			$c7= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '7'],['tpmd_cek' => (($item_tmp_perimeter->c7== 'v' || $item_tmp_perimeter->c7== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n7== '' || $item_tmp_perimeter->n7== null) ? '0':$item_tmp_perimeter->n7)]);
			
			//r_penyimpanan
			$c8= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '8'],['tpmd_cek' => (($item_tmp_perimeter->c8== 'v' || $item_tmp_perimeter->c8== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n8== '' || $item_tmp_perimeter->n8== null) ? '0':$item_tmp_perimeter->n8)]);
			
			//r_server
			$c9= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '9'],['tpmd_cek' => (($item_tmp_perimeter->c9== 'v' || $item_tmp_perimeter->c9== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n9== '' || $item_tmp_perimeter->n9== null) ? '0':$item_tmp_perimeter->n9)]);
			
			//a_pemeriksaan
			$c10= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '10'],['tpmd_cek' => (($item_tmp_perimeter->c10== 'v' || $item_tmp_perimeter->c10== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n10== '' || $item_tmp_perimeter->n10== null) ? '0':$item_tmp_perimeter->n10)]);
			
			//a_parkir
			$c11= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '11'],['tpmd_cek' => (($item_tmp_perimeter->c11== 'v' || $item_tmp_perimeter->c11== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n11== '' || $item_tmp_perimeter->n11== null) ? '0':$item_tmp_perimeter->n11)]);
			
			//a_perdagangan
			$c12= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '12'],['tpmd_cek' => (($item_tmp_perimeter->c12== 'v' || $item_tmp_perimeter->c12== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n12== '' || $item_tmp_perimeter->n12== null) ? '0':$item_tmp_perimeter->n12)]);
			
			//a_produksi
			$c13= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '13'],['tpmd_cek' => (($item_tmp_perimeter->c13== 'v' || $item_tmp_perimeter->c13== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n13== '' || $item_tmp_perimeter->n13== null) ? '0':$item_tmp_perimeter->n13)]);
			
			//r_laboratorium
			$c14= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '14'],['tpmd_cek' => (($item_tmp_perimeter->c14== 'v' || $item_tmp_perimeter->c14== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n14== '' || $item_tmp_perimeter->n14== null) ? '0':$item_tmp_perimeter->n14)]);
			
			//r_ibadah
			$c15= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '15'],['tpmd_cek' => (($item_tmp_perimeter->c15== 'v' || $item_tmp_perimeter->c15== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n15== '' || $item_tmp_perimeter->n15== null) ? '0':$item_tmp_perimeter->n15)]);
			
			//a_konstruksi
			$c16= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '16'],['tpmd_cek' => (($item_tmp_perimeter->c16== 'v' || $item_tmp_perimeter->c16== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n16== '' || $item_tmp_perimeter->n16== null) ? '0':$item_tmp_perimeter->n16)]);
			
			//r_pengemudi
			$c17= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '17'],['tpmd_cek' => (($item_tmp_perimeter->c17== 'v' || $item_tmp_perimeter->c17== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n17== '' || $item_tmp_perimeter->n17== null) ? '0':$item_tmp_perimeter->n17)]);
			
			//r_klinik
			$c18= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '18'],['tpmd_cek' => (($item_tmp_perimeter->c18== 'v' || $item_tmp_perimeter->c18== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n18== '' || $item_tmp_perimeter->n18== null) ? '0':$item_tmp_perimeter->n18)]);
			
			//banking
			$c19= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '19'],['tpmd_cek' => (($item_tmp_perimeter->c19== 'v' || $item_tmp_perimeter->c19== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n19== '' || $item_tmp_perimeter->n19== null) ? '0':$item_tmp_perimeter->n19)]);
			
			//cafetaria
			$c20= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '20'],['tpmd_cek' => (($item_tmp_perimeter->c20== 'v' || $item_tmp_perimeter->c20== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n20== '' || $item_tmp_perimeter->n20== null) ? '0':$item_tmp_perimeter->n20)]);
			
			//hospitality
			$c21= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '21'],['tpmd_cek' => (($item_tmp_perimeter->c21== 'v' || $item_tmp_perimeter->c21== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n21== '' || $item_tmp_perimeter->n21== null) ? '0':$item_tmp_perimeter->n21)]);
			
			//aula
			$c22= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '22'],['tpmd_cek' => (($item_tmp_perimeter->c22== 'v' || $item_tmp_perimeter->c22== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n22== '' || $item_tmp_perimeter->n22== null) ? '0':$item_tmp_perimeter->n22)]);
			
			//dapur
			$c23= PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $perimeter_level_id, 'tpmd_mcr_id' => '23'],['tpmd_cek' => (($item_tmp_perimeter->c23== 'v' || $item_tmp_perimeter->c23== '1') ? true:false), 'tpmd_jml' => (($item_tmp_perimeter->n23== '' || $item_tmp_perimeter->n23== null) ? '0':$item_tmp_perimeter->n23)]);
			
			
			//ubah status tmp perimeter
			$item_tmp_perimeter->status = 1;
			$item_tmp_perimeter->save();
			
					
			} catch (Throwable $e) {
				$item_tmp_perimeter->status = 2;
				$item_tmp_perimeter->save();
			}	
			//dd('berhasil');
			
		}

	}
	
	


    //
}
