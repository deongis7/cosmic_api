<?php

namespace App\Http\Controllers;


use App\ClusterRuangan;
use App\Perimeter;
use App\PerimeterLevel;
use App\PerimeterDetail;
use App\PerimeterKategori;
use App\User;
use App\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;

use DB;


class PerimeterController extends Controller
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
	public function getCountPerimeter($id){
		$data = array();
		$perimeter = Perimeter::join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
					->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
					->where('master_region.mr_mc_id',$id)	
					->count();
				
			$data[] = array(
					"jml_perimeter" => $perimeter,
					
					);

		return response()->json(['status' => 200,'data' => $data]);

	}

	
	//Peta Sebaran Perimeter
	public function getPerimeterMap($id){
		$data = array();
		$perimeter = Perimeter::select('master_perimeter.mpm_id','master_perimeter.mpm_name','master_perimeter.mpm_alamat','master_perimeter.mpm_longitude','master_perimeter.mpm_latitude')
					->join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
					->where('master_region.mr_mc_id',$id)	
					->get();
		foreach($perimeter as $itemperimeter){		
			$data[] = array(
					"id_perimeter" => $itemperimeter->mpm_id,
					"nama_perimeter" => $itemperimeter->mpm_name,
					"alamat" => $itemperimeter->mpm_alamat,	
					"longitude" => str_replace("'","",$itemperimeter->mpm_longitude),
					"latitude" => str_replace("'","",$itemperimeter->mpm_latitude),
				);
		}
		return response()->json(['status' => 200,'data' => $data]);

	}

	//Get Perimeter by Kode Perusahaan
	public function getPerimeter($id){
		$data = array();
		$perimeter = Perimeter::select('master_region.mr_id','master_region.mr_name','master_perimeter_level.mpml_id','master_perimeter.mpm_name','master_perimeter.mpm_alamat','master_perimeter_level.mpml_name','master_perimeter_level.mpml_ket','master_perimeter_kategori.mpmk_name','userpic.username as nik_pic','userpic.first_name as pic','userfo.username as nik_fo','userfo.first_name as fo')
					->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
					->join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
					->join('master_perimeter_kategori','master_perimeter_kategori.mpmk_id','master_perimeter.mpm_mpmk_id')
					->leftjoin('app_users as userpic','userpic.username','master_perimeter_level.mpml_pic_nik')
					->leftjoin('app_users as userfo','userfo.username','master_perimeter_level.mpml_me_nik')
					->where('master_region.mr_mc_id',$id)	
					->get();
		foreach($perimeter as $itemperimeter){		
			$data[] = array(
					"id_region" => $itemperimeter->mr_id,
					"region" => $itemperimeter->mr_name,
					"id_perimeter_level" => $itemperimeter->mpml_id,
					"nama_perimeter" => $itemperimeter->mpm_name,
					"level" => $itemperimeter->mpml_name,
					"keterangan" => $itemperimeter->mpml_ket,
					"alamat" => $itemperimeter->mpm_name,
					"kategori" => $itemperimeter->mpmk_name,
					"nik_pic" => $itemperimeter->nik_pic,
					"pic" => $itemperimeter->pic,
					"nik_fo" => $itemperimeter->nik_fo,
					"fo" => $itemperimeter->fo,
					
				);
		}
		return response()->json(['status' => 200,'data' => $data]);

	}
	
	//Get Perimeter per Region
	public function getPerimeterbyRegion($id){
		$data = array();
		$perimeter = Perimeter::select('master_region.mr_id','master_region.mr_name','master_perimeter_level.mpml_id','master_perimeter.mpm_name','master_perimeter.mpm_alamat','master_perimeter_level.mpml_name','master_perimeter_level.mpml_ket','master_perimeter_kategori.mpmk_name','userpic.username as nik_pic','userpic.first_name as pic','userfo.username as nik_fo','userfo.first_name as fo')
					->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
					->join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
					->join('master_perimeter_kategori','master_perimeter_kategori.mpmk_id','master_perimeter.mpm_mpmk_id')
					->leftjoin('app_users as userpic','userpic.username','master_perimeter_level.mpml_pic_nik')
					->leftjoin('app_users as userfo','userfo.username','master_perimeter_level.mpml_me_nik')
					->where('master_region.mr_id',$id)	
					->get();
		foreach($perimeter as $itemperimeter){		
			$data[] = array(
					"id_perimeter_level" => $itemperimeter->mpml_id,
					"nama_perimeter" => $itemperimeter->mpm_name,
					"level" => $itemperimeter->mpml_name,
					"keterangan" => $itemperimeter->mpml_ket,
					"alamat" => $itemperimeter->mpm_name,
					"kategori" => $itemperimeter->mpmk_name,
					"nik_pic" => $itemperimeter->username,
					"pic" => $itemperimeter->first_name,
					"nik_fo" => $itemperimeter->nik_fo,
					"fo" => $itemperimeter->fo,
					
				);
		}
		return response()->json(['status' => 200,'data' => $data]);

	}

	
	//Jumlah Task Force
	public function getCountTaskForce($id){
		$data = array();
		$taskforce = User::join('master_company','master_company.mc_id','app_users.mc_id')
					->join('app_users_groups','app_users_groups.user_id','app_users.id')
					->where('master_company.mc_id', '=', $id)
					->where(function($query){
							 $query->where('app_users_groups.group_id', '=', 3);
							 $query->orWhere('app_users_groups.group_id', '=', 4);
						 })
					->count();
				
			$data[] = array(
					"jml_taskforce" => $taskforce,
					
					);

		return response()->json(['status' => 200,'data' => $data]);

	}
	
	//Get Task Force per Region
	public function getTaskForce($id){
		$data = array();
		$taskforce = DB::select( "select app.username,app.first_name, (case when (a1.mpm_mr_id is null) then a2.mpm_mr_id else a1.mpm_mr_id end) as mpm_mr_id,
			(case when (a1.mpm_mr_id is null) then a2.mr_name else a1.mr_name end) as mr_name,app.mc_id
		from app_users app
		left JOIN (select mp1.mpm_mr_id , mr1.mr_name, mpl1.mpml_pic_nik from master_perimeter_level mpl1 
				join master_perimeter mp1  on mpl1.mpml_mpm_id = mp1.mpm_id
				join master_region mr1 on mr1.mr_id = mp1.mpm_mr_id) a1 on a1.mpml_pic_nik = app.username
		left JOIN (select mp2.mpm_mr_id, mr2.mr_name, mpl2.mpml_me_nik from master_perimeter_level mpl2 
				join master_perimeter mp2  on mpl2.mpml_mpm_id = mp2.mpm_id
				join master_region mr2 on mr2.mr_id = mp2.mpm_mr_id) a2 on a2.mpml_me_nik = app.username
		join app_users_groups aup on aup.user_id = app.id and (aup.group_id=3 or aup.group_id=4)
		where app.mc_id = ?
		GROUP BY app.username,app.first_name, (case when (a1.mpm_mr_id is null) then a2.mpm_mr_id else a1.mpm_mr_id end) ,
			(case when (a1.mpm_mr_id is null) then a2.mr_name else a1.mr_name end),app.mc_id
		order by mpm_mr_id asc,app.first_name asc", [$id]);
		
		foreach($taskforce as $itemtaskforce){			
			$data[] = array(
					"kd_perusahaan" => $itemtaskforce->mc_id,
					"kd_region" => $itemtaskforce->mpm_mr_id,
					"region" => $itemtaskforce->mr_name,
					"nik" => $itemtaskforce->username,
					"nama" => $itemtaskforce->first_name,			
					);
		}
		
		return response()->json(['status' => 200,'data' => $data]);

	}
	
		//Get Task Force per Region
	public function getTaskForcebyRegion($id){
		$data = array();
		$taskforce = DB::select( "select app.username,app.first_name, (case when (a1.mpm_mr_id is null) then a2.mpm_mr_id else a1.mpm_mr_id end) as mpm_mr_id,
			(case when (a1.mpm_mr_id is null) then a2.mr_name else a1.mr_name end) as mr_name,app.mc_id
		from app_users app
		left JOIN (select mp1.mpm_mr_id , mr1.mr_name, mpl1.mpml_pic_nik from master_perimeter_level mpl1 
				join master_perimeter mp1  on mpl1.mpml_mpm_id = mp1.mpm_id
				join master_region mr1 on mr1.mr_id = mp1.mpm_mr_id) a1 on a1.mpml_pic_nik = app.username
		left JOIN (select mp2.mpm_mr_id, mr2.mr_name, mpl2.mpml_me_nik from master_perimeter_level mpl2 
				join master_perimeter mp2  on mpl2.mpml_mpm_id = mp2.mpm_id
				join master_region mr2 on mr2.mr_id = mp2.mpm_mr_id) a2 on a2.mpml_me_nik = app.username
		join app_users_groups aup on aup.user_id = app.id and (aup.group_id=3 or aup.group_id=4)
		where a1.mpm_mr_id = ? or a2.mpm_mr_id = ?
		GROUP BY app.username,app.first_name, (case when (a1.mpm_mr_id is null) then a2.mpm_mr_id else a1.mpm_mr_id end) ,
			(case when (a1.mpm_mr_id is null) then a2.mr_name else a1.mr_name end),app.mc_id
		order by mpm_mr_id asc,app.first_name asc", [$id,$id]);
		
		foreach($taskforce as $itemtaskforce){			
			$data[] = array(
					"kd_perusahaan" => $itemtaskforce->mc_id,
					"kd_region" => $itemtaskforce->mpm_mr_id,
					"region" => $itemtaskforce->mr_name,
					"nik" => $itemtaskforce->username,
					"nama" => $itemtaskforce->first_name,			
					);
		}
		
		return response()->json(['status' => 200,'data' => $data]);

	}
	
	
	

    //
}
