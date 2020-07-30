<?php

namespace App\Http\Controllers;


use App\ClusterRuangan;
use App\Perimeter;
use App\PerimeterLevel;
use App\PerimeterDetail;
use App\PerimeterKategori;
use App\TblPerimeterDetail;
use App\TrnAktifitas;
use App\TrnAktifitasFile;

use App\User;
use App\UserGroup;
use App\Helpers\AppHelper;
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

    }

	public function index(){

	}
	public function show($id){

	}
	
	public function store (Request $request){

	}

	//Daily Monitoring
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
		$keterangan = $request->keterangan;
		$nik = trim($request->nik);
		
		$user_id = $request->user_id;
		$tanggal= Carbon::now()->format('Y-m-d');

		$weeks = AppHelper::Weeks();
		//dd($weeks['weeks']);
	
        if(!Storage::exists('/public/aktifitas/'.$kd_perusahaan.'/'.$tanggal)) {
            Storage::disk('public')->makeDirectory('/aktifitas/'.$kd_perusahaan.'/'.$tanggal);
        }
  
        //$destinationPath = base_path("storage\app\public\aktifitas/").$kd_perusahaan.'/'.$tanggal;
		$destinationPath = storage_path().'/app/public/aktifitas/' .$kd_perusahaan.'/'.$tanggal;
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
		
		$trn_aktifitas= TrnAktifitas::updateOrCreate(
            ['ta_tpmd_id' => $id_perimeter_cluster, 'ta_nik' => $nik, 'ta_kcar_id' => $id_konfig_cluster_aktifitas,'ta_week' =>$weeks['weeks']],['ta_date' => $tanggal, 'ta_keterangan' => $keterangan , 'ta_status' => 0]);	
		
		
		$trn_aktifitas_file= TrnAktifitasFile::create(
            ['taf_ta_id' => $trn_aktifitas->ta_id,'taf_date' => $tanggal, 'taf_file' => $name1, 'taf_file_tumb' => $name2]);

        if($trn_aktifitas) {
            return response()->json(['status' => 200,'message' => 'Data Berhasil Disimpan']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
        }		
	}

	//Validasi
	public function validasiMonitoring(Request $request){
		$this->validate($request, [
            'id_perimeter_cluster' => 'required',
			'id_konfig_cluster_aktifitas' => 'required',
			'status' => 'required',
        ]);
			
		$id_perimeter_cluster = $request->id_perimeter_cluster;
		$id_konfig_cluster_aktifitas = $request->id_konfig_cluster_aktifitas;	
		$weeks = AppHelper::Weeks();
		//dd($weeks['weeks']);
		
		$trn_aktifitas= TrnAktifitas::where('ta_tpmd_id',$id_perimeter_cluster)
									->where('ta_kcar_id',$id_konfig_cluster_aktifitas)
									->where('ta_week',$weeks['weeks'])->first();
		if($trn_aktifitas != null){
			$trn_aktifitas->ta_status = $request->status;
			if($request->status==2){
				$trn_aktifitas->ta_ket_tolak = $request->keterangan;
			}
			
			if($trn_aktifitas->save()) {
				return response()->json(['status' => 200,'message' => 'Data Berhasil Disimpan']);
			} else {
				return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
			}
		} else {
			return response()->json(['status' => 404,'message' => 'Data Tidak Ditemukan'])->setStatusCode(404);
		}	
		   		
	}
	
	//Get Perimeter per NIK
	public function getPerimeterbyUser($nik){
		$user = User::where('username',$nik)->first();
		
		
		$data = array();
		$dashboard = array("total_perimeter"=> 0,"sudah_dimonitor"=>0,"belum_dimonitor"=>0,);
		if ($user != null){
			$role_id = $user->roles()->first()->id;
			
			if ($role_id == 3 || $role_id == 4 ){
				$perimeter = Perimeter::select('master_region.mr_id','master_region.mr_name','master_perimeter_level.mpml_id','master_perimeter.mpm_name','master_perimeter.mpm_alamat','master_perimeter_level.mpml_name','master_perimeter_level.mpml_ket','master_perimeter_kategori.mpmk_name','userpic.username as nik_pic','userpic.first_name as pic','userfo.username as nik_fo','userfo.first_name as fo')
							->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
							->join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
							->join('master_perimeter_kategori','master_perimeter_kategori.mpmk_id','master_perimeter.mpm_mpmk_id')
							->leftjoin('app_users as userpic','userpic.username','master_perimeter_level.mpml_pic_nik')			
							->leftjoin('app_users as userfo','userfo.username','master_perimeter_level.mpml_me_nik');							
				if ($role_id == 3 )	{
					$perimeter = $perimeter->where('userpic.username',$nik);	
				} else {
					$perimeter = $perimeter->where('userfo.username',$nik);
				}	

				$perimeter = $perimeter->get();
				$totalperimeter = $perimeter->count();
				$totalpmmonitoring = 0;
				
				foreach($perimeter as $itemperimeter){
					$cluster = TblPerimeterDetail::where('tpmd_mpml_id',$itemperimeter->mpml_id)->where('tpmd_cek',true)->count();
		
					$status = $this->getStatusMonitoring($itemperimeter->mpml_id,$role_id,$cluster);	
					//dd($status['status']);
					$data[] = array(
							"id_perimeter_level" => $itemperimeter->mpml_id,
							"nama_perimeter" => $itemperimeter->mpm_name.' - '.$itemperimeter->mpml_name,
							"level" => $itemperimeter->mpml_name,
							"keterangan" => $itemperimeter->mpml_ket,
							"alamat" => $itemperimeter->mpm_name,
							"kategori" => $itemperimeter->mpmk_name,
							"nik_pic" => $itemperimeter->username,
							"pic" => $itemperimeter->first_name,
							"nik_fo" => $itemperimeter->nik_fo,
							"fo" => $itemperimeter->fo,
							"status_monitoring" =>($status['status']),
							"percentage" =>($status['percentage']),
							
						);
					if ($status['status'] == true ){ $totalpmmonitoring++; }
				}
				
				//dashboard
				$dashboard = array (
							"total_perimeter"=> $totalperimeter,
							"sudah_dimonitor"=> $totalpmmonitoring,
							"belum_dimonitor"=> $totalperimeter - $totalpmmonitoring
							);
				
				return response()->json(['status' => 200,'data_dashboard' => $dashboard ,'data' => $data]);
			} else {
				return response()->json(['status' => 200,'data_dashboard' => $dashboard, 'data' => $data]);
			}	
			
		} else {
			return response()->json(['status' => 200,'data_dashboard' => $dashboard,'data' => $data]);
		}	
		

	}
	
	//Get Cluster Aktifitas
	private function getClusterAktifitas($id_cluster,$id_role){
		
		$data = array();

		$cluster = DB::select( "select  kc.kcar_id, kc.kcar_mcr_id, kc.kcar_ag_id, mcar.mcar_name from konfigurasi_car kc
		join  master_cluster_ruangan mcr on kc.kcar_mcr_id = mcr.mcr_id
		join master_car mcar on mcar.mcar_id =kc.kcar_mcar_id and mcar.mcar_active=true
		where kc.kcar_mcr_id = ? and kc.kcar_ag_id = ?
		order by kc.kcar_mcar_id asc, mcar.mcar_name asc", [$id_cluster, $id_role]);				
		foreach($cluster as $itemcluster){		
			$data[] = array(
					"id_konfig_cluster_aktifitas" => $itemcluster->kcar_id,
					"aktifitas" => $itemcluster->mcar_name,
					
				);
		}
		return $data;

	}	
	
	//Get Status Monitoring
	private function getStatusMonitoring($id_perimeter_level,$id_role, $cluster){
		
		$data = array();
        $now = Carbon::now();

		$startdate = $now->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
		$enddate = $now->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');	
		
		if($id_role == 4){
		$clustertrans = DB::select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where ta.ta_aktif = true and tpd.tpmd_mpml_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
		group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter_level, $startdate, $enddate]);				
		} else {	
		$clustertrans = DB::select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where ta.ta_aktif = true and ta.ta_status = 1 and tpd.tpmd_mpml_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
		group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter_level, $startdate, $enddate]);
		}
		
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
	
	//Get Status Monitoring per Cluster
	private function getStatusMonitoringCluster($id_perimeter_cluster,$id_role){
		
		$data = array();
        $now = Carbon::now();

		$startdate = $now->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
		$enddate = $now->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');	
		
		if($id_role == 4){
		$clustertrans = DB::select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where ta.ta_aktif = true and tpd.tpmd_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
		group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter_cluster, $startdate, $enddate]);				
		} else {
		$clustertrans = DB::select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where ta.ta_aktif = true and ta.ta_status = 1 and  tpd.tpmd_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
		group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter_cluster, $startdate, $enddate]);		
		}	
		
		if ( count($clustertrans)>0) {
			return true;
			
		} else {
			return false;
			
		}	

	}

	//Get List Monitoring
	private function getDataMonitoring($id_perimeter_cluster,$id_konfig_cluster_aktifitas,$id_role,$nik,$mc_id){
		
		$data = array();
        $now = Carbon::now();
		$i=1;

		$startdate = $now->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
		$enddate = $now->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');	
		
		$clustertrans = DB::select( "select tpd.tpmd_id,kc.kcar_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id,ta.ta_id,ta.ta_filetumb,ta.ta_nik,ta.ta_keterangan,ta.ta_date from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where ta.ta_aktif = true and tpd.tpmd_id = ? and ta.ta_kcar_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = ? and ta.ta_nik = ?
		order by  ta.ta_id desc limit 2", [$id_perimeter_cluster, $id_konfig_cluster_aktifitas,$startdate, $enddate, $id_role,$nik]);				
			
	    
			foreach ($clustertrans as $itemclustertrans){
				
				
				$data[] = array(
							"nomor" => $i,
							"id_perimeter_cluster" => $id_perimeter_cluster,
							"id_konfig_cluster_aktifitas" => $id_konfig_cluster_aktifitas,
							"id_transaksi" => $itemclustertrans->ta_id,
							"nik" => $itemclustertrans->ta_nik,
							"file" => "/aktifitas/".$mc_id."/".$itemclustertrans->ta_date."/".$itemclustertrans->ta_filetumb,
							"keterangan" => $itemclustertrans->ta_keterangan,

						);
							
				$i++;	
			}
			

		return $data;


	}
	
	//Get Cluster per Perimeter Level
	public function getClusterbyPerimeter($id,$nik){
		$user = User::where('username',$nik)->first();
		$data = array();
		if ($user != null){
			$role_id = $user->roles()->first()->id;
			

			$perimeter = DB::select( "select mpm.mpm_id,mpl.mpml_id,tpd.tpmd_id,mcr.mcr_id, mpm.mpm_name, mpk.mpmk_name, mpl.mpml_name,mcr.mcr_name,tpmd_order,mpl.mpml_pic_nik as nikpic,mpl.mpml_me_nik as nikfo from master_perimeter_level mpl
					join master_perimeter mpm on mpm.mpm_id = mpl.mpml_mpm_id
					join master_perimeter_kategori mpk on mpk.mpmk_id = mpm.mpm_mpmk_id
					join table_perimeter_detail tpd on tpd.tpmd_mpml_id = mpl.mpml_id and tpd.tpmd_cek=true
					join master_cluster_ruangan mcr on mcr.mcr_id = tpd.tpmd_mcr_id
					where mpl.mpml_id = ?
					order by mpm.mpm_name asc, mpk.mpmk_name asc, mpl.mpml_name asc", [$id]);				
			foreach($perimeter as $itemperimeter){
				$data_aktifitas_cluster = array();
				$data_aktifitas_cluster = $this->getClusterAktifitas($itemperimeter->mcr_id,$role_id );
				$status = $this->getStatusMonitoringCluster($itemperimeter->tpmd_id,$role_id);
				$data[] = array(
						"id_perimeter_level" => $itemperimeter->mpml_id,
						"level" => $itemperimeter->mpml_name,
						"id_perimeter_cluster" => $itemperimeter->tpmd_id,
						"id_cluster" => $itemperimeter->mcr_id,
						"cluster_ruangan" => (($itemperimeter->tpmd_order > 1)? ($itemperimeter->mcr_name.' - '.$itemperimeter->tpmd_order) :$itemperimeter->mcr_name),
						"order" => $itemperimeter->tpmd_order,
						"status" => $status,
						"aktifitas" => $data_aktifitas_cluster,

						
						
					);
			}
			return response()->json(['status' => 200,'data' => $data]);
		} else {
			return response()->json(['status' => 200,'data' => $data]);
		}			

	}
	
	//Get Cluster per Perimeter Level
	public function getAktifitasbyCluster($nik,$id_perimeter_cluster){
		$user = User::where('username',$nik)->first();
		$data = array();
		if ($user != null){
			$role_id = $user->roles()->first()->id;
			

			$aktifitas = DB::select( "select tpd.tpmd_id,kc.kcar_id,kc.kcar_mcar_id, mcr.mcr_name,tpd.tpmd_order, mcar.mcar_name from  table_perimeter_detail tpd  
			join master_cluster_ruangan mcr on mcr.mcr_id = tpd.tpmd_mcr_id
			join konfigurasi_car kc on kc.kcar_mcr_id = mcr.mcr_id
			join master_car mcar on mcar.mcar_id =kc.kcar_mcar_id and mcar.mcar_active=true
			where tpd.tpmd_cek=true and tpd.tpmd_id = ? and kc.kcar_ag_id = ?
			order by mcr.mcr_name asc,tpd.tpmd_order asc, mcar.mcar_name asc", [$id_perimeter_cluster,$role_id]);				
			foreach($aktifitas as $itemaktifitas){
				$data_monitoring = array();
				$data_monitoring = $this->getDataMonitoring($itemaktifitas->tpmd_id,$itemaktifitas->kcar_id,$role_id,$nik,$user->mc_id);
				
				$data[] = array(
						"id_perimeter_cluster" => $itemaktifitas->tpmd_id,
						"cluster" => $itemaktifitas->mcr_name,
						"order" => $itemaktifitas->tpmd_order,
						"id_konfig_cluster_aktifitas" => $itemaktifitas->kcar_id,
						"aktifitas" => $itemaktifitas->mcar_name,
						"monitoring" => $data_monitoring,

						
					);
			}
			return response()->json(['status' => 200,'data' => $data]);
		} else {
			return response()->json(['status' => 200,'data' => $data]);
		}			

	}
	

    //
}
