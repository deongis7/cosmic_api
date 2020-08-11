<?php

namespace App\Http\Controllers;


use App\ClusterRuangan;
use App\Region;
use App\Perimeter;
use App\PerimeterLevel;
use App\PerimeterDetail;
use App\PerimeterKategori;
use App\User;
use App\UserGroup;
use App\Helpers\AppHelper;
use App\TblPerimeterDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;

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
		$region = Region::where('mr_mc_id',$id)->count();
		$user = User::join('app_users_groups','app_users_groups.user_id','app_users.id')
					->where('app_users.mc_id',$id)
					->where('app_users_groups.group_id','3')
					->count();
		$perimeter = Perimeter::join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
					->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
					->where('master_region.mr_mc_id',$id)	
					->count();
				
			$data[] = array(
					"jml_perimeter" => $perimeter,
					"jml_pic" => $user,
					"jml_region" => $region
					
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
		$perimeter =   Cache::remember("get_perimeter_by_company_id_". $id, 30 * 60, function()use($id) {
			return Perimeter::select('master_region.mr_id','master_region.mr_name','master_perimeter_level.mpml_id',
		    'master_perimeter.mpm_name','master_perimeter.mpm_alamat',
		    'master_perimeter_level.mpml_name','master_perimeter_level.mpml_ket',
		    'master_perimeter_kategori.mpmk_name','userpic.username as nik_pic',
		    'userpic.first_name as pic','userfo.username as nik_fo','userfo.first_name as fo',
		    'master_provinsi.mpro_name', 'master_kabupaten.mkab_name'
		    )
					->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
					->join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
					->join('master_perimeter_kategori','master_perimeter_kategori.mpmk_id','master_perimeter.mpm_mpmk_id')
					->leftjoin('app_users as userpic','userpic.username','master_perimeter_level.mpml_pic_nik')
					->leftjoin('app_users as userfo','userfo.username','master_perimeter_level.mpml_me_nik')
					->leftjoin('master_provinsi','master_provinsi.mpro_id','master_perimeter.mpm_mpro_id')
					->leftjoin('master_kabupaten','master_kabupaten.mkab_id','master_perimeter.mpm_mkab_id')
					->where('master_region.mr_mc_id',$id)	
					->orderBy('master_region.mr_name', 'asc')
					->orderBy('master_perimeter.mpm_name', 'asc')
					->orderBy('master_perimeter_level.mpml_name', 'asc')
					->get();
		
		});
			
		foreach($perimeter as $itemperimeter){		
			$cluster = TblPerimeterDetail::where('tpmd_mpml_id',$itemperimeter->mpml_id)->where('tpmd_cek',true)->count();
		
			$status = $this->getStatusMonitoring($itemperimeter->mpml_id,$cluster);
			$data[] = array(
					"id_region" => $itemperimeter->mr_id,
					"region" => $itemperimeter->mr_name,
					"id_perimeter_level" => $itemperimeter->mpml_id,
					"nama_perimeter" => $itemperimeter->mpm_name.' - '.$itemperimeter->mpml_name,
					"level" => $itemperimeter->mpml_name,
					"keterangan" => $itemperimeter->mpml_ket,
					"alamat" => $itemperimeter->mpm_name,
					"kategori" => $itemperimeter->mpmk_name,
					"nik_pic" => $itemperimeter->nik_pic,
					"pic" => $itemperimeter->pic,
					"nik_fo" => $itemperimeter->nik_fo,
					"fo" => $itemperimeter->fo,
					"status_monitoring" =>($status['status']),
					"percentage" =>($status['percentage']),
			        "provinsi" => $itemperimeter->mpro_name,
			        "kabupaten" => $itemperimeter->mkab_name,
				);
		}
		return response()->json(['status' => 200,'data' => $data]);

	}
	
	//Get Perimeter per Region
	public function getPerimeterbyRegion($id){
		$data = array();
		$perimeter = Perimeter::select('master_region.mr_id','master_region.mr_name',
		    'master_perimeter_level.mpml_id','master_perimeter.mpm_name',
		    'master_perimeter.mpm_alamat','master_perimeter_level.mpml_name',
		    'master_perimeter_level.mpml_ket','master_perimeter_kategori.mpmk_name',
		    'userpic.username as nik_pic','userpic.first_name as pic',
		    'userfo.username as nik_fo','userfo.first_name as fo',
		    'master_provinsi.mpro_name', 'master_kabupaten.mkab_name')
					->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
					->join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
					->join('master_perimeter_kategori','master_perimeter_kategori.mpmk_id','master_perimeter.mpm_mpmk_id')
					->leftjoin('app_users as userpic','userpic.username','master_perimeter_level.mpml_pic_nik')
					->leftjoin('app_users as userfo','userfo.username','master_perimeter_level.mpml_me_nik')
					->leftjoin('master_provinsi','master_provinsi.mpro_id','master_perimeter.mpm_mpro_id')
					->leftjoin('master_kabupaten','master_kabupaten.mkab_id','master_perimeter.mpm_mkab_id')
					->where('master_region.mr_id',$id)
					->orderBy('master_region.mr_name', 'asc')
					->orderBy('master_perimeter.mpm_name', 'asc')
					->orderBy('master_perimeter_level.mpml_name', 'asc')					
					->get();
		foreach($perimeter as $itemperimeter){		
			$data[] = array(
					"id_perimeter_level" => $itemperimeter->mpml_id,
					"nama_perimeter" => $itemperimeter->mpm_name.' - '.$itemperimeter->mpml_name,
					"level" => $itemperimeter->mpml_name,
					"keterangan" => $itemperimeter->mpml_ket,
					"alamat" => $itemperimeter->mpm_name,
					"kategori" => $itemperimeter->mpmk_name,
					"nik_pic" => $itemperimeter->nik_pic,
					"pic" => $itemperimeter->pic,
					"nik_fo" => $itemperimeter->nik_fo,
					"fo" => $itemperimeter->fo,
			        "provinsi" => $itemperimeter->mpro_name,
			        "kabupaten" => $itemperimeter->mkab_name,					
				);
		}
		return response()->json(['status' => 200,'data' => $data]);

	}

	//Get Cluster per Perimeter Level
	public function getClusterbyPerimeter($id){
		
		$data = array();

			$perimeter = DB::select( "select mpm.mpm_id,mpl.mpml_id,tpd.tpmd_id,mcr.mcr_id, mpm.mpm_name, mpk.mpmk_name, mpl.mpml_name,mcr.mcr_name,tpmd_order,mpl.mpml_pic_nik as nikpic,mpl.mpml_me_nik as nikfo from master_perimeter_level mpl
					join master_perimeter mpm on mpm.mpm_id = mpl.mpml_mpm_id
					join master_perimeter_kategori mpk on mpk.mpmk_id = mpm.mpm_mpmk_id
					join table_perimeter_detail tpd on tpd.tpmd_mpml_id = mpl.mpml_id and tpd.tpmd_cek=true
					join master_cluster_ruangan mcr on mcr.mcr_id = tpd.tpmd_mcr_id
					where mpl.mpml_id = ?
					order by mcr.mcr_name asc, tpmd_order asc", [$id]);				
			foreach($perimeter as $itemperimeter){

	
				$status = $this->getStatusMonitoringCluster($itemperimeter->tpmd_id);
				$data[] = array(
						"id_perimeter_level" => $itemperimeter->mpml_id,
						"level" => $itemperimeter->mpml_name,
						"id_perimeter_cluster" => $itemperimeter->tpmd_id,
						"id_cluster" => $itemperimeter->mcr_id,
						"cluster_ruangan" => (($itemperimeter->tpmd_order > 1)? ($itemperimeter->mcr_name.' - '.$itemperimeter->tpmd_order) :$itemperimeter->mcr_name),
						"order" => $itemperimeter->tpmd_order,
						"status" => $status,
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
	
	//Get Status Monitoring per Cluster
	private function getStatusMonitoringCluster($id_perimeter_cluster){
		
		$data = array();
        $weeks = AppHelper::Weeks();
		$startdate = $weeks['startweek'];
		$enddate = $weeks['endweek'];

		$clustertrans = DB::select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where tpd.tpmd_id = ? and ta.ta_status = 1 and (ta.ta_date >= ? and ta.ta_date <= ? ) 
		group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter_cluster, $startdate, $enddate]);			
	
		
		if ( count($clustertrans)>0) {
			return true;	
		} else {
			return false;
		}	
	}
	

	public function getExecutionReport($id){
	    $data = array();
	    $execution = DB::select("  
                    SELECT *, CASE 
                    WHEN v_persen>=100 THEN '#33cc33' 
                    WHEN v_persen<50 THEN '#cc2900' 
                    ELSE '#ff9933' END as v_color
                    FROM (
                    	SELECT v_id, v_judul, v_desc, CAST(v_jml as int) v_persen 
                    	FROM execution_report('$id')
                    	UNION ALL 
                    	SELECT 0, 'COSMIC INDEX', 'Impelemetasi Leading Indikator', 
                    	(SELECT SUM((CAST(v_jml as int))*(CAST(v_bobot as int))/100) 
                    	FROM execution_report('$id'))
                    ) z
                        ");
	 
	    foreach($execution as $exec){
	        $data[] = array(
	            "id" => $exec->v_id,
	            "judul" => $exec->v_judul,
	            "desc" => $exec->v_desc,
	            "color" => $exec->v_color,
	            "persen" => $exec->v_persen
	        );
	    }
	   
	    return response()->json(['status' => 200,'data' => $data]);
	}
	
	//Get Status Monitoring
	private function getStatusMonitoring($id_perimeter_level, $cluster){
		
		$data = array();
		$weeks = AppHelper::Weeks();
		$startdate = $weeks['startweek'];
		$enddate = $weeks['endweek'];
		
			
		$clustertrans[] =  Cache::remember("get_status_monitoring_by_perimeter_level_". $id_perimeter_level."_cluster_". $cluster, 30 * 60, function()use($id_perimeter_level, $startdate, $enddate) {
			return DB::select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
			join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
			join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
			join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
			where ta.ta_status = 1 and tpd.tpmd_mpml_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
			group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter_level, $startdate, $enddate]);
		});	
		//dd($clustertrans );
			if ($cluster <= count($clustertrans)) {
				//return true;
				return array(
								"status" => true,
								"percentage" => 1);
			} else {
				//return false;
				return array(
								"status" => false,
								"percentage" => round((count($clustertrans)/$cluster),2));
			}	
	

	}
}
