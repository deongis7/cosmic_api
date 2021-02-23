<?php

namespace App\Http\Controllers;


use App\ClusterRuangan;
use App\Perimeter;
use App\PerimeterLevel;
use App\PerimeterLevelFile;
use App\PerimeterDetail;
use App\PerimeterKategori;
use App\TblPerimeterDetail;
use App\TrnAktifitas;
use App\TrnAktifitasFile;
use App\KonfigurasiCAR;

use App\User;
use App\UserGroup;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Redis;

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

	 /**
	 * @OA\post(
	 *     path="/monitoring",
	 *     description="Monitoring",
	 *     @OA\Response(response="default", description="Daily Monitoring")
	 * )
	 */
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
            ['ta_tpmd_id' => $id_perimeter_cluster, 'ta_nik' => $nik, 'ta_kcar_id' => $id_konfig_cluster_aktifitas,'ta_week' =>$weeks['weeks']],['ta_date' => $tanggal, 'ta_keterangan' => $keterangan]);

		if ($trn_aktifitas->ta_status == '2'){
			TrnAktifitasFile::where('taf_ta_id',$trn_aktifitas->ta_id)->delete();
		}

		$trn_aktifitas_file= TrnAktifitasFile::create(
            ['taf_ta_id' => $trn_aktifitas->ta_id,'taf_date' => $tanggal, 'taf_file' => $name1, 'taf_file_tumb' => $name2]);

		$trn_aktifitas->ta_status =0;
		$trn_aktifitas->save();

        if($trn_aktifitas) {
            return response()->json(['status' => 200,'message' => 'Data Berhasil Disimpan']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
        }
	}

	//Update File Foto
	public function updateMonitoringFile(Request $request){
		$this->validate($request, [
            'id_file' => 'required',
			'nik' => 'required',
			'file_foto' => 'required',
        ]);


		$user = User::where(DB::raw("TRIM(username)"),'=',trim($request->nik))->first();

		if($user==null){
			 return response()->json(['status' => 404,'message' => 'User Tidak Ditemukan'])->setStatusCode(404);
		}
		$kd_perusahaan = $user->mc_id;
		$file = $request->file_foto;
		$id_file = $request->id_file;
		$nik = trim($request->nik);

		$user_id = $user->user_id;
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
		$trn_aktifitas_file= TrnAktifitasFile::where('taf_id' ,$id_file)
						->update(['taf_date' => $tanggal, 'taf_file' => $name1, 'taf_file_tumb' => $name2]);


        if($trn_aktifitas_file) {
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
    $user = new User;
    $user->setConnection('pgsql2');
		$user = $user->where('username',$nik)->first();


		$data = array();
		$dashboard = array("total_perimeter"=> 0,"sudah_dimonitor"=>0,"belum_dimonitor"=>0,);
		if ($user != null){
			$role_id = $user->roles()->first()->id;

			if ($role_id == 3 || $role_id == 4 ){
        $perimeter = new Perimeter;
        $perimeter->setConnection('pgsql2');
				$perimeter = $perimeter->select('master_region.mr_id','master_region.mr_name','master_perimeter_level.mpml_id','master_perimeter.mpm_name','master_perimeter.mpm_alamat','master_perimeter_level.mpml_name','master_perimeter_level.mpml_ket','master_perimeter_kategori.mpmk_name','userpic.username as nik_pic','userpic.first_name as pic','userfo.username as nik_fo','userfo.first_name as fo','master_provinsi.mpro_name', 'master_kabupaten.mkab_name')
							->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
							->join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
							->join('master_perimeter_kategori','master_perimeter_kategori.mpmk_id','master_perimeter.mpm_mpmk_id')
							->leftjoin('app_users as userpic','userpic.username','master_perimeter_level.mpml_pic_nik')
							->leftjoin('app_users as userfo','userfo.username','master_perimeter_level.mpml_me_nik')
							->leftjoin('master_provinsi','master_provinsi.mpro_id','master_perimeter.mpm_mpro_id')
							->leftjoin('master_kabupaten','master_kabupaten.mkab_id','master_perimeter.mpm_mkab_id');
				if ($role_id == 3 )	{
					$perimeter = $perimeter->where('userpic.username',$nik);
				} else {
					$perimeter = $perimeter->where('userfo.username',$nik);
				}

				$perimeter = $perimeter->where('master_perimeter.mpm_mc_id',$user->mc_id)->orderBy('master_region.mr_name', 'asc')
					->orderBy('master_perimeter.mpm_name', 'asc')
					->orderBy('master_perimeter_level.mpml_name', 'asc')->get();
				$totalperimeter = $perimeter->count();
				$totalpmmonitoring = 0;

				foreach($perimeter as $itemperimeter){
          $cluster = new TblPerimeterDetail;
          $cluster->setConnection('pgsql2');
					$cluster = $cluster->where('tpmd_mpml_id',$itemperimeter->mpml_id)->where('tpmd_cek',true)->count();

					$status = $this->getStatusMonitoring($itemperimeter->mpml_id,$role_id,$cluster);
					//dd($status['status']);
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
							"status_monitoring" =>($status['status']),
							"percentage" =>($status['percentage']),
							"provinsi" => $itemperimeter->mpro_name,
							"kabupaten" => $itemperimeter->mkab_name,

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
	private function getClusterAktifitas($id_perimeter_cluster,$id_cluster,$id_role){

		$data = array();

		$cluster = DB::connection('pgsql2')->select( "select  kc.kcar_id, kc.kcar_mcr_id, kc.kcar_ag_id, mcar.mcar_name from konfigurasi_car kc
		join  master_cluster_ruangan mcr on kc.kcar_mcr_id = mcr.mcr_id
		join master_car mcar on mcar.mcar_id =kc.kcar_mcar_id and mcar.mcar_active=true
		left join transaksi_aktifitas ta on  ta.ta_kcar_id = kc.kcar_id
		where kc.kcar_mcr_id = ? and kc.kcar_ag_id = 4
		order by mcar.mcar_name asc", [$id_cluster]);
		foreach($cluster as $itemcluster){
			$data[] = array(
					"id_konfig_cluster_aktifitas" => $itemcluster->kcar_id,
					"aktifitas" => $itemcluster->mcar_name,


				);
		}
		return $data;

	}

	//Get Cluster Aktifitas
	private function getClusterAktifitasMonitoring($id_perimeter_cluster,$id_cluster,$id_role,$id_perusahaan){

		$data = array();

		$weeks = AppHelper::Weeks();
		$startdate = $weeks['startweek'];
		$enddate = $weeks['endweek'];

		$cluster = DB::connection('pgsql3')->select( "select  kc.kcar_id, kc.kcar_mcr_id, kc.kcar_ag_id, mcar.mcar_name,ta.ta_id,ta.ta_status,ta.ta_ket_tolak from konfigurasi_car kc
		join  master_cluster_ruangan mcr on kc.kcar_mcr_id = mcr.mcr_id
		join master_car mcar on mcar.mcar_id =kc.kcar_mcar_id and mcar.mcar_active=true
		left join transaksi_aktifitas ta on  ta.ta_kcar_id = kc.kcar_id and (ta.ta_date >= ? and ta.ta_date <= ? ) and ta.ta_tpmd_id = ?
		where  kc.kcar_mcr_id = ? and kc.kcar_ag_id = 4
		order by mcar.mcar_name asc", [ $startdate, $enddate,$id_perimeter_cluster,$id_cluster]);


		foreach($cluster as $itemcluster){

			$data[] = array(
					"id_konfig_cluster_aktifitas" => $itemcluster->kcar_id,
					"aktifitas" => $itemcluster->mcar_name,
					"id_aktifitas" => $itemcluster->ta_id,
					"status" => $itemcluster->ta_status,
					"ket_tolak" => $itemcluster->ta_ket_tolak,
					"file" => $this->getFile($itemcluster->ta_id,$id_perusahaan),

				);
		}
		return $data;

	}

	//Get File
	private function getFile($id_aktifitas,$id_perusahaan){
		$data =[];

		if ($id_aktifitas != null){
		$transaksi_aktifitas_file = TrnAktifitasFile::join("transaksi_aktifitas","transaksi_aktifitas.ta_id","transaksi_aktifitas_file.taf_ta_id")
						->where("ta_status", "<>", "2")
						->where("taf_ta_id",$id_aktifitas)->orderBy("taf_id","desc")->limit("2")->get();

			foreach($transaksi_aktifitas_file as $itemtransaksi_aktifitas_file){

				$data[] = array(
						"id_file" => $itemtransaksi_aktifitas_file->taf_id,
						"file" => "/aktifitas/".$id_perusahaan."/".$itemtransaksi_aktifitas_file->taf_date."/".$itemtransaksi_aktifitas_file->taf_file,
						"file_tumb" => "/aktifitas/".$id_perusahaan."/".$itemtransaksi_aktifitas_file->taf_date."/".$itemtransaksi_aktifitas_file->taf_file_tumb,
					);
			}
		}


		return $data;
	}

	//Get File Tolak
	private function getFileTolak($id_aktifitas,$id_perusahaan){
		$data =[];
		if ($id_aktifitas != null){
		$transaksi_aktifitas_file = TrnAktifitasFile::join("transaksi_aktifitas","transaksi_aktifitas.ta_id","transaksi_aktifitas_file.taf_ta_id")
						->where("ta_status", "=", "2")
						->where("taf_ta_id",$id_aktifitas)->orderBy("taf_id","desc")->limit("2")->get();

			foreach($transaksi_aktifitas_file as $itemtransaksi_aktifitas_file){

				$data[] = array(
						"id_file" => $itemtransaksi_aktifitas_file->taf_id,
						"file" => "/aktifitas/".$id_perusahaan."/".$itemtransaksi_aktifitas_file->taf_date."/".$itemtransaksi_aktifitas_file->taf_file,
						"file_tumb" => "/aktifitas/".$id_perusahaan."/".$itemtransaksi_aktifitas_file->taf_date."/".$itemtransaksi_aktifitas_file->taf_file_tumb,
					);
			}
		}
		return $data;
	}

	//Get File ID
	public function getFileByID($id_file){
		$data =[];
		if ($id_file != null){

		$transaksi_aktifitas_file = TrnAktifitasFile::join('transaksi_aktifitas','transaksi_aktifitas_file.taf_ta_id','transaksi_aktifitas.ta_id','master_perimeter.mc_id')
					->join('konfigurasi_car','konfigurasi_car.kcar_id','transaksi_aktifitas.ta_kcar_id')
					->join('master_car','master_car.mcar_id','konfigurasi_car.kcar_mcar_id')
					->join('table_perimeter_detail','table_perimeter_detail.tpmd_id','transaksi_aktifitas.ta_tpmd_id')
					->join('master_perimeter_level','master_perimeter_level.mpml_id','table_perimeter_detail.tpmd_mpml_id')
					->join('master_perimeter','master_perimeter.mpm_id','master_perimeter_level.mpml_mpm_id')
					->where('transaksi_aktifitas_file.taf_id',$id_file)
					->first();

			if ($transaksi_aktifitas_file != null){

				$data = array(
						"id_file" => $transaksi_aktifitas_file->taf_id,
						"file" => "/aktifitas/".$transaksi_aktifitas_file->mc_id."/".$transaksi_aktifitas_file->taf_date."/".$transaksi_aktifitas_file->taf_file,
						"file_tumb" => "/aktifitas/".$transaksi_aktifitas_file->mc_id."/".$transaksi_aktifitas_file->taf_date."/".$transaksi_aktifitas_file->taf_file_tumb,
					);
			}
		}
		return response()->json(['status' => 200,'data' => $data]);
	}

	//Get Status Monitoring
	private function getStatusMonitoring($id_perimeter_level,$id_role, $cluster){

		$data = array();
		$weeks = AppHelper::Weeks();
		$startdate = $weeks['startweek'];
		$enddate = $weeks['endweek'];

		if($id_role == 4){
		$clustertrans = DB::connection('pgsql2')->select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where tpd.tpmd_mpml_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4 and ta.ta_status <> 2
		group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter_level, $startdate, $enddate]);
		} else {
		$clustertrans = DB::connection('pgsql2')->select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where ta.ta_status = 1 and tpd.tpmd_mpml_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
		group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter_level, $startdate, $enddate]);
		}
		//dd($cluster);
		if ($cluster <> 0){
			if (($cluster <= count($clustertrans))) {
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
		} else {
			//return false;
			return array(
							"status" => false,
							"percentage" => 0);
		}

	}

	//Get Status Monitoring per Cluster
	private function getStatusMonitoringCluster($id_perimeter_cluster,$id_role,$aktifitas){

		$data = array();
		$weeks = AppHelper::Weeks();
		$startdate = $weeks['startweek'];
		$enddate = $weeks['endweek'];

        $konfirmasi = DB::connection('pgsql3')->select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id, ta.ta_kcar_id,max(ta.ta_date_update) from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where  ta.ta_status = 1 and  tpd.tpmd_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
		group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id, ta.ta_kcar_id order by max(ta.ta_date_update) desc", [$id_perimeter_cluster, $startdate, $enddate]);

		if($id_role == 4){
		$clustertrans = DB::connection('pgsql3')->select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id, ta.ta_kcar_id,max(ta.ta_date_update) from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where  tpd.tpmd_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4 and ta.ta_status <> 2
		group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id, ta.ta_kcar_id order by max(ta.ta_date_update) desc", [$id_perimeter_cluster, $startdate, $enddate]);
		} else {
		$clustertrans = $konfirmasi;
		}
        if( $aktifitas <= count($konfirmasi)){
            $sts_mnt = 2;
        } else {
            if ( $aktifitas <= count($clustertrans)) {
                $sts_mnt = 1;
            } else {
                $sts_mnt = 0;
            }
        }

		if (count($clustertrans) > 0) {
			if ( $aktifitas <= count($clustertrans)) {
				return array(
								"status_konfirmasi" => $sts_mnt,
                                "status" => true,
								"last_date" =>$clustertrans[0]->max);
			} else {
				return array(
                                "status_konfirmasi" => $sts_mnt,
								"status" => false,
								"last_date" =>$clustertrans[0]->max);

			}
		} else {
			return array(       "status_konfirmasi" => $sts_mnt,
								"status" => false,
								"last_date" => null);
		}

	}

	//Get List Monitoring
	private function getDataMonitoring($id_aktifitas,$id_role,$nik,$mc_id){

		$data = array();

		$i=1;
		$weeks = AppHelper::Weeks();
		$startdate = $weeks['startweek'];
		$enddate = $weeks['endweek'];

		$clustertrans = DB::connection('pgsql2')->select( "select tpd.tpmd_id,kc.kcar_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id,ta.ta_id,taf.taf_id,taf.taf_file ,taf.taf_file_tumb , taf.taf_date from transaksi_aktifitas_file taf
		join transaksi_aktifitas ta on ta.ta_id = taf.taf_ta_id and ta.ta_status <> 2
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where ta.ta_id = ?
		order by  ta.ta_id desc limit 2", [$id_aktifitas]);


			foreach ($clustertrans as $itemclustertrans){


				$data[] = array(
							"nomor" => $i,
							"id_perimeter_cluster" => $itemclustertrans->tpmd_id,
							"id_konfig_cluster_aktifitas" => $itemclustertrans->kcar_id,
							"id_aktifitas" => $itemclustertrans->ta_id,
							"id_file" => $itemclustertrans->taf_id,
							"file" => "/aktifitas/".$mc_id."/".$itemclustertrans->taf_date."/".$itemclustertrans->taf_file,
							"file_tumb" => "/aktifitas/".$mc_id."/".$itemclustertrans->taf_date."/".$itemclustertrans->taf_file_tumb,


						);

				$i++;
			}


		return $data;


	}

	//Get Cluster per Perimeter Level
	public function getClusterbyPerimeter($id,$nik){
    $datacache =Cache::remember(env('APP_ENV', 'dev')."_get_cluster_perimeter_level_by_". $id."_".$nik, 3 * 60, function()use($id,$nik) {

  		$user = User::where('username',$nik)->first();
      $total_monitoring = 0;
  		$jml_monitoring = 0;
  		$dataprogress = array("total_monitor"=> 0,"sudah_dimonitor"=>0,"belum_dimonitor"=>0,);
  		$data = array();
  		if ($user != null){
  			$role_id = $user->roles()->first()->id;


  			$perimeter = DB::connection('pgsql2')->select( "select mpm.mpm_id,mpl.mpml_id,tpd.tpmd_id,mcr.mcr_id, mpm.mpm_name, mpk.mpmk_name, mpl.mpml_name,mcr.mcr_name,tpmd_order,mpl.mpml_pic_nik as nikpic,mpl.mpml_me_nik as nikfo ,case when tsp.tbsp_status is null then 0 else tsp.tbsp_status end as status_konfirmasi,
            case when tsp.tbsp_status = 2 then true else false end as status_pic,
            case when tsp.tbsp_status = 1 then true when tsp.tbsp_status = 2 then true else false end as status_fo,
            tsp.updated_at as last_update
            from master_perimeter_level mpl
  					join master_perimeter mpm on mpm.mpm_id = mpl.mpml_mpm_id
  					join master_perimeter_kategori mpk on mpk.mpmk_id = mpm.mpm_mpmk_id
  					join table_perimeter_detail tpd on tpd.tpmd_mpml_id = mpl.mpml_id and tpd.tpmd_cek=true
  					join master_cluster_ruangan mcr on mcr.mcr_id = tpd.tpmd_mcr_id
						left join table_status_perimeter tsp on tsp.tbsp_tpmd_id=tpd.tpmd_id
  					where mpl.mpml_id = ?
  					order by mcr.mcr_name asc, tpmd_order asc", [$id]);
  			foreach($perimeter as $itemperimeter){
  				$data_aktifitas_cluster = array();
          //$aktifitas = new KonfigurasiCAR;
          //$aktifitas->setConnection('pgsql2');
  				//$aktifitas = $aktifitas->join('master_car','master_car.mcar_id','konfigurasi_car.kcar_mcar_id')
  				//			->where('konfigurasi_car.kcar_ag_id',4)->where('konfigurasi_car.kcar_mcr_id',$itemperimeter->mcr_id)
  				//			->where('master_car.mcar_active',true)->count();

  				//$data_aktifitas_cluster = $this->getClusterAktifitas($itemperimeter->tpmd_id,$itemperimeter->mcr_id,$role_id);
  				//$status = $this->getStatusMonitoringCluster($itemperimeter->tpmd_id,$role_id,$aktifitas);
          $total_monitoring = $total_monitoring + 1;
  				$jml_monitoring = $jml_monitoring + (($role_id==3?$itemperimeter->status_pic:$itemperimeter->status_fo)?1:0);
  				$data[] = array(
  						"id_perimeter_level" => $itemperimeter->mpml_id,
  						"level" => $itemperimeter->mpml_name,
  						"id_perimeter_cluster" => $itemperimeter->tpmd_id,
  						"id_cluster" => $itemperimeter->mcr_id,
  						"cluster_ruangan" => (($itemperimeter->tpmd_order > 1)? ($itemperimeter->mcr_name.' - '.$itemperimeter->tpmd_order) :$itemperimeter->mcr_name),
  						"order" => $itemperimeter->tpmd_order,
              //"status_konfirmasi" => $status['status_konfirmasi'],
  						//"status" => $status['status'],
              "status_konfirmasi" => $itemperimeter->status_konfirmasi,
              "status" => ($role_id==3?$itemperimeter->status_pic:$itemperimeter->status_fo),
              "last_update" => $itemperimeter->last_update,
  	  				"aktifitas" => null,

  					);
            $dataprogress = array("total_monitor"=> $total_monitoring,
                    "sudah_dimonitor"=> $jml_monitoring,
                    "belum_dimonitor"=> $total_monitoring - $jml_monitoring );
  			}
  			return array('status_monitoring' => $dataprogress,'status' => 200,'data' => $data);
  		} else {
  			return  array('status_monitoring' => $dataprogress,'status' => 200,'data' => $data);
  		}
    });
    return response()->json($datacache);
	}

	//Get Cluster per Perimeter Level
	public function getAktifitasbyCluster($nik,$id_perimeter_cluster){
		$user = User::where('username',$nik)->first();
		$data = array();

		if ($user != null){
			$role_id = $user->roles()->first()->id;
			$weeks = AppHelper::Weeks();
			$startdate = $weeks['startweek'];
			$enddate = $weeks['endweek'];
			$aktifitas = DB::connection('pgsql2')->select( "select tpd.tpmd_id,kc.kcar_id,kc.kcar_mcar_id, mcr.mcr_name,tpd.tpmd_order, mcar.mcar_name,ta.ta_id,ta.ta_status,ta.ta_ket_tolak from  table_perimeter_detail tpd
			join master_cluster_ruangan mcr on mcr.mcr_id = tpd.tpmd_mcr_id
			join konfigurasi_car kc on kc.kcar_mcr_id = mcr.mcr_id
			join master_car mcar on mcar.mcar_id =kc.kcar_mcar_id and mcar.mcar_active=true
			left join transaksi_aktifitas ta on tpd.tpmd_id = ta.ta_tpmd_id and ta.ta_kcar_id = kc.kcar_id and (ta.ta_date >= ? and ta.ta_date <= ? )
			where tpd.tpmd_cek=true and tpd.tpmd_id = ? and kc.kcar_ag_id = 4
			order by mcr.mcr_name asc,tpd.tpmd_order asc, mcar.mcar_name asc", [$startdate,$enddate,$id_perimeter_cluster]);
			foreach($aktifitas as $itemaktifitas){
				$data_monitoring = array();
				$data_monitoring = $this->getDataMonitoring($itemaktifitas->ta_id,$role_id,$nik,$user->mc_id);

				$data[] = array(
						"id_perimeter_cluster" => $itemaktifitas->tpmd_id,
						"cluster" => $itemaktifitas->mcr_name,
						"order" => $itemaktifitas->tpmd_order,
						"id_konfig_cluster_aktifitas" => $itemaktifitas->kcar_id,
						"aktifitas" => $itemaktifitas->mcar_name,
						"id_aktifitas" => $itemaktifitas->ta_id,
						"status" => $itemaktifitas->ta_status,
						"ket_tolak" => $itemaktifitas->ta_ket_tolak,
						"monitoring" => $data_monitoring,
					);
			}
			return response()->json(['status' => 200,'data' => $data]);
		} else {
			return response()->json(['status' => 200,'data' => $data]);
		}

	}

	//Get Cluster per Perimeter Level
	public function getAktifitasbyPerimeter($nik,$id_perimeter_level){
		$user = User::where('username',$nik)->first();
		$total_monitoring = 0;
		$jml_monitoring = 0;
		$dataprogress = array("total_monitor"=> 0,"sudah_dimonitor"=>0,"belum_dimonitor"=>0,);
		$data = array();
		if ($user != null){
			$role_id = $user->roles()->first()->id;

            $perimeter =Cache::remember(env('APP_ENV', 'dev')."_perimeter_in_aktifitas_by_". $id_perimeter_level, 7 * 60, function()use($id_perimeter_level) {
                return $cacheperimeter = DB::connection('pgsql2')->select("select mpm.mpm_id,mpl.mpml_id,tpd.tpmd_id,mcr.mcr_id, mpm.mpm_name, mpk.mpmk_name, mpl.mpml_name,mcr.mcr_name,tpmd_order,mpl.mpml_pic_nik as nikpic,mpl.mpml_me_nik as nikfo,case when tsp.tbsp_status is null then 0 else tsp.tbsp_status end as status_konfirmasi,
          case when tsp.tbsp_status = 2 then true else false end as status_pic,
          case when tsp.tbsp_status = 1 then true when tsp.tbsp_status = 2 then true else false end as status_fo,
          tpd.tpmd_file_foto,tpd.tpmd_file_tumb, mpm.mpm_mc_id,
          tsp.updated_at as last_update
          from master_perimeter_level mpl
					join master_perimeter mpm on mpm.mpm_id = mpl.mpml_mpm_id
					join master_perimeter_kategori mpk on mpk.mpmk_id = mpm.mpm_mpmk_id
					join table_perimeter_detail tpd on tpd.tpmd_mpml_id = mpl.mpml_id and tpd.tpmd_cek=true
					join master_cluster_ruangan mcr on mcr.mcr_id = tpd.tpmd_mcr_id
					left join table_status_perimeter tsp on tsp.tbsp_tpmd_id=tpd.tpmd_id
					where mpl.mpml_id = ?
					order by mpm.mpm_name asc,mpl.mpml_name asc, mcr.mcr_name asc, tpmd_order asc", [$id_perimeter_level]);
            });
			foreach($perimeter as $itemperimeter){
				$data_aktifitas_cluster = array();
        //$aktifitas = new KonfigurasiCAR;
        //$aktifitas->setConnection('pgsql2');
				//$aktifitas = $aktifitas->join('master_car','master_car.mcar_id','konfigurasi_car.kcar_mcar_id')
				//			->where('konfigurasi_car.kcar_ag_id',4)->where('konfigurasi_car.kcar_mcr_id',$itemperimeter->mcr_id)
				//			->where('master_car.mcar_active',true)->count();

				$data_aktifitas_cluster = $this->getClusterAktifitasMonitoring($itemperimeter->tpmd_id,$itemperimeter->mcr_id,$role_id,  $user->mc_id);
				//$status = $this->getStatusMonitoringCluster($itemperimeter->tpmd_id,$role_id,$aktifitas);
				$total_monitoring = $total_monitoring + 1;
				$jml_monitoring = $jml_monitoring + (($role_id==3?$itemperimeter->status_pic:$itemperimeter->status_fo)==true?1:0);
				$data[] = array(
						"id_perimeter_level" => $itemperimeter->mpml_id,
						"level" => $itemperimeter->mpml_name,
						"id_perimeter_cluster" => $itemperimeter->tpmd_id,
						"id_cluster" => $itemperimeter->mcr_id,
						"cluster_ruangan" => (($itemperimeter->tpmd_order > 1)? ($itemperimeter->mcr_name.' - '.$itemperimeter->tpmd_order) :$itemperimeter->mcr_name),
						"order" => $itemperimeter->tpmd_order,
						//"status_konfirmasi" => $status['status_konfirmasi'],
						//"status" => $status['status'],
						"status_konfirmasi" => $itemperimeter->status_konfirmasi,
						"status" => ($role_id==3?$itemperimeter->status_pic:$itemperimeter->status_fo),

            "file_cluster" => $itemperimeter->tpmd_file_foto != null ? "/cluster_ruangan/".$itemperimeter->mpm_mc_id."/".$itemperimeter->tpmd_file_foto:null,
            "file_cluster_tumb" => $itemperimeter->tpmd_file_tumb != null ? "/cluster_ruangan/".$itemperimeter->mpm_mc_id."/".$itemperimeter->tpmd_file_tumb:null,
						"last_update" => $itemperimeter->last_update,
						"aktifitas" => $data_aktifitas_cluster,

					);

			}
			$dataprogress = array("total_monitor"=> $total_monitoring,
							"sudah_dimonitor"=> $jml_monitoring,
							"belum_dimonitor"=> $total_monitoring - $jml_monitoring );

			return response()->json(['status_monitoring' => $dataprogress,'status' => 200,'data' => $data]);
		} else {
			return response()->json(['status_monitoring' => $dataprogress,'status' => 200,'data' => $data]);
		}

	}

	//Get ID
	public function getMonitoringDetail($id_aktifitas){
		$data = array();


		$monitor = TrnAktifitas::join('konfigurasi_car','konfigurasi_car.kcar_id','transaksi_aktifitas.ta_kcar_id')
					->join('master_car','master_car.mcar_id','konfigurasi_car.kcar_mcar_id')
					->join('table_perimeter_detail','table_perimeter_detail.tpmd_id','transaksi_aktifitas.ta_tpmd_id')
					->join('master_perimeter_level','master_perimeter_level.mpml_id','table_perimeter_detail.tpmd_mpml_id')
					->join('master_perimeter','master_perimeter.mpm_id','master_perimeter_level.mpml_mpm_id')
					->where('transaksi_aktifitas.ta_id',$id_aktifitas)
					->first();

		if ($monitor != null) {

				$data = array(
						"id_perimeter_cluster" => $monitor->ta_tpmd_id,
						"id_konfig_cluster_aktifitas" => $monitor->ta_kcar_id,
						"aktifitas" => $monitor->mcar_name,
						"id_aktifitas" => $monitor->ta_id,
						"status" => $monitor->ta_status,
						"ket_tolak" => $monitor->ta_ket_tolak,
						"file" => $this->getFile($id_aktifitas,$monitor->mpm_mc_id),

					);

			return response()->json(['status' => 200,'data' => $data]);
		}  else {
			return response()->json(['status' => 200,'data' => $data]);
		}

	}

	//Get Notif
	public function getNotifFO($nik){
		$data = array();

		$weeks = AppHelper::Weeks();
		$startdate = $weeks['startweek'];
		$enddate = $weeks['endweek'];

		$notif = DB::connection('pgsql2')->select( "select mp.mpm_name,mp.mpm_mc_id,mpl.mpml_id, mpl.mpml_name, mcr.mcr_name,tpd.tpmd_order,mcar.mcar_name, ta.ta_tpmd_id,ta.ta_kcar_id,ta.ta_id, ta.ta_status, ta.ta_ket_tolak from transaksi_aktifitas ta
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		join master_cluster_ruangan mcr on mcr.mcr_id = kc.kcar_mcr_id
		join master_car mcar on mcar.mcar_id = kcar_mcar_id
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join master_perimeter mp on mp.mpm_id = mpl.mpml_mpm_id
		where ta.ta_status = 2 and ta.ta_nik = ?  and (ta.ta_date >= ? and ta.ta_date <= ? )
		order by ta_date_update asc", [$nik,$startdate,$enddate]);


		foreach($notif as $itemnotif){
		//dd($this->getOneFile($itemnotif->ta_id,$itemnotif->mpm_mc_id)['file_tumb']);
			$data[] = array(
					"id_perimeter_level" => $itemnotif->mpml_id,
					"id_perimeter_cluster" => $itemnotif->ta_tpmd_id,
					"id_konfig_cluster_aktifitas" => $itemnotif->ta_kcar_id,
					"perimeter" => $itemnotif->mpm_name,
			        "level" => $itemnotif->mpml_name,
					"cluster" => $itemnotif->mcr_name. " ". $itemnotif->tpmd_order,
					"aktifitas" => $itemnotif->mcar_name,
					"id_aktifitas" => $itemnotif->ta_id,
					"status" => $itemnotif->ta_status,
					"ket_tolak" => $itemnotif->ta_ket_tolak,
					"file" => $this->getFileTolak($itemnotif->ta_id,$itemnotif->mpm_mc_id)
				);
		}
		return response()->json(['status' => 200,'data' => $data]);
	}

public function addFilePerimeterLevel(Request $request){
    $this->validate($request, [
      'id_perimeter_level' => 'required',
      'file_foto' => 'required',
      'nik' => 'required'
        ]);


    $user = User::where(DB::raw("TRIM(username)"),'=',trim($request->nik))->first();
    if($user==null){
       return response()->json(['status' => 404,'message' => 'User Tidak Ditemukan'])->setStatusCode(404);
    }
    $kd_perusahaan = $user->mc_id;
    $file = $request->file_foto;
    $id_perimeter_level = $request->id_perimeter_level;
    $nik = trim($request->nik);

    $user_id = $user->id;

        if(!Storage::exists('/public/perimeter_level/'.$kd_perusahaan)) {
            Storage::disk('public')->makeDirectory('/perimeter_level/'.$kd_perusahaan);
        }

        //$destinationPath = base_path("storage\app\public\aktifitas/").$kd_perusahaan.'/'.$tanggal;
    $destinationPath = storage_path().'/app/public/perimeter_level/' .$kd_perusahaan;
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

    $perimeter_level_file= PerimeterLevelFile::create(
            ['mpmlf_mpml_id' => $id_perimeter_level, 'mpmlf_file' => $name1, 'mpmlf_file_tumb' => $name2,'mpmlf_user_insert' => $user_id,'mpmlf_user_update' => $user_id,]);

    $perimeter_level_file->save();

        if($perimeter_level_file) {
            return response()->json(['status' => 200,'message' => 'Data Berhasil Disimpan']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
        }
  }

  //Get File ID
  public function getFilePerimeterLevelByID($id_file){
    $data =[];
    if ($id_file != null){

    $perimeter_level_file = PerimeterLevelFile::select('master_perimeter.mpm_mc_id','master_perimeter_level_file.mpmlf_file','master_perimeter_level_file.mpmlf_file_tumb')
          ->join('master_perimeter_level','master_perimeter_level.mpml_id','master_perimeter_level_file.mpmlf_mpml_id')
          ->join('master_perimeter','master_perimeter.mpm_id','master_perimeter_level.mpml_mpm_id')
          ->where('master_perimeter_level_file.mpmlf_id',$id_file)
          ->first();

      if ($perimeter_level_file != null){

        $data = array(
            "id_file" => $id_file,
            "file" => "/perimeter_level/".$perimeter_level_file->mpm_mc_id."/".$perimeter_level_file->mpmlf_file,
            "file_tumb" => "/perimeter_level/".$perimeter_level_file->mpm_mc_id."/".$perimeter_level_file->mpmlf_file_tumb,
          );
      }
    }
    return response()->json(['status' => 200,'data' => $data]);
  }

  //Get File ID
  public function getFilePerimeterLevelByPerimeterLevel($id_perimeter_level){
    $data =[];
    if ($id_perimeter_level != null){

    $perimeter_level_file = PerimeterLevelFile::select('master_perimeter_level_file.mpmlf_id','master_perimeter.mpm_mc_id','master_perimeter_level_file.mpmlf_file','master_perimeter_level_file.mpmlf_file_tumb')
          ->join('master_perimeter_level','master_perimeter_level.mpml_id','master_perimeter_level_file.mpmlf_mpml_id')
          ->join('master_perimeter','master_perimeter.mpm_id','master_perimeter_level.mpml_mpm_id')
          ->where('master_perimeter_level.mpml_id',$id_perimeter_level)
          ->orderBy('mpmlf_id','desc')
          ->limit(3)
          ->get();

      if ($perimeter_level_file->count() >0){
        foreach($perimeter_level_file as $plf){
          $data[] = array(
            "id_file" => $plf->mpmlf_id,
            "file" => "/perimeter_level/".$plf->mpm_mc_id."/".$plf->mpmlf_file,
            "file_tumb" => "/perimeter_level/".$plf->mpm_mc_id."/".$plf->mpmlf_file_tumb,
            );
        }

      }
    }
    return response()->json(['status' => 200,'data' => $data]);
  }

  public function addFileClusterRuangan(Request $request){
      $this->validate($request, [
        'id_perimeter_cluster' => 'required',
        'file_foto' => 'required',
        'nik' => 'required'
          ]);


      $user = User::where(DB::raw("TRIM(username)"),'=',trim($request->nik))->first();
      if($user==null){
         return response()->json(['status' => 404,'message' => 'User Tidak Ditemukan'])->setStatusCode(404);
      }
      $kd_perusahaan = $user->mc_id;
      $file = $request->file_foto;
      $id_perimeter_cluster = $request->id_perimeter_cluster;
      $nik = trim($request->nik);

      $user_id = $user->id;

          if(!Storage::exists('/public/cluster_ruangan/'.$kd_perusahaan)) {
              Storage::disk('public')->makeDirectory('/cluster_ruangan/'.$kd_perusahaan);
          }


      $destinationPath = storage_path().'/app/public/cluster_ruangan/' .$kd_perusahaan;
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

      $cluster_ruangan= PerimeterDetail::where('tpmd_id',$id_perimeter_cluster)->first();
      $cluster_ruangan->tpmd_file_foto= $name1;
      $cluster_ruangan->tpmd_file_tumb= $name2;
      $cluster_ruangan->save();
      //dd('*'.env('APP_ENV', 'dev')."_perimeter_in_aktifitas_by_". $cluster_ruangan->tpmd_mpml_id);
      Redis::del(Redis::keys('*'.env('APP_ENV', 'dev')."_perimeter_in_aktifitas_by_". $cluster_ruangan->tpmd_mpml_id));
          if($cluster_ruangan) {
              return response()->json(['status' => 200,'message' => 'Data Berhasil Disimpan']);
          } else {
              return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
          }
    }

    public function getFileClusterRuanganByID($id_perimeter_cluster){
      $data =[];
      if ($id_perimeter_cluster != null){

      $cluster_ruangan_file = PerimeterDetail::select('table_perimeter_detail.tpmd_id','master_perimeter.mpm_mc_id','table_perimeter_detail.tpmd_file_foto','table_perimeter_detail.tpmd_file_tumb')
            ->join('master_perimeter_level','master_perimeter_level.mpml_id','table_perimeter_detail.tpmd_mpml_id')
            ->join('master_perimeter','master_perimeter.mpm_id','master_perimeter_level.mpml_mpm_id')
            ->where('table_perimeter_detail.tpmd_id',$id_perimeter_cluster)
            ->first();

        if ($cluster_ruangan_file != null){

            $data = array(
              "id_file" => $cluster_ruangan_file->tpmd_id,
              "file" =>  $cluster_ruangan_file->tpmd_file_foto != null ?"/perimeter_level/".$cluster_ruangan_file->mpm_mc_id."/".$cluster_ruangan_file->tpmd_file_foto:null,
              "file_tumb" =>  $cluster_ruangan_file->tpmd_file_tumb != null ?"/perimeter_level/".$cluster_ruangan_file->mpm_mc_id."/".$cluster_ruangan_file->tpmd_file_tumb:null,
              );


        }
      }
      return response()->json(['status' => 200,'data' => $data]);
    }
    
    public function getAktifitasbyPerimeterBUMN($nik,$id_perimeter_level){
        $user = User::where('username',$nik)->first();
        $auth_mc_id =Auth::guard('api')->user()->mc_id;
        //var_dump($auth_mc_id);die;
        //$mc_id = $user->mc_id;
        $total_monitoring = 0;
        $jml_monitoring = 0;
        $dataprogress = array("total_monitor"=> 0,"sudah_dimonitor"=>0,"belum_dimonitor"=>0,);
        $data = array();
        if ($user != null){
            $role_id = $user->roles()->first()->id;
            
                $perimeter=Cache::remember(env('APP_ENV', 'dev')."_perimeter_in_aktifitasbumn_by_". $id_perimeter_level, 7 * 60, function()use($id_perimeter_level) {
                return $cacheperimeter 
                    = DB::connection('pgsql2')->select("select mpm.mpm_id,mpl.mpml_id,tpd.tpmd_id,mcr.mcr_id, mpm.mpm_name, mpk.mpmk_name, mpl.mpml_name,mcr.mcr_name,tpmd_order,mpl.mpml_pic_nik as nikpic,mpl.mpml_me_nik as nikfo,case when tsp.tbsp_status is null then 0 else tsp.tbsp_status end as status_konfirmasi,
                    case when tsp.tbsp_status = 2 then true else false end as status_pic,
                    case when tsp.tbsp_status = 1 then true when tsp.tbsp_status = 2 then true else false end as status_fo,
                    tpd.tpmd_file_foto,tpd.tpmd_file_tumb, mpm.mpm_mc_id,
                    tsp.updated_at as last_update
                    from master_perimeter_level mpl
					join master_perimeter mpm on mpm.mpm_id = mpl.mpml_mpm_id
					join master_perimeter_kategori mpk on mpk.mpmk_id = mpm.mpm_mpmk_id
					join table_perimeter_detail tpd on tpd.tpmd_mpml_id = mpl.mpml_id and tpd.tpmd_cek=true
					join master_cluster_ruangan mcr on mcr.mcr_id = tpd.tpmd_mcr_id
					left join table_status_perimeter tsp on tsp.tbsp_tpmd_id=tpd.tpmd_id
					where mpl.mpml_id = ?
                    and mpm.mpm_mc_id= ?
					order by mpm.mpm_name asc,mpl.mpml_name asc, mcr.mcr_name asc, tpmd_order asc", [$id_perimeter_level, $auth_mc_id]);
           }); 
                foreach($perimeter as $itemperimeter){
                    $data_aktifitas_cluster = array();
                    $data_aktifitas_cluster = $this->getClusterAktifitasMonitoring($itemperimeter->tpmd_id,$itemperimeter->mcr_id,$role_id,  $user->mc_id);
                   
                    $total_monitoring = $total_monitoring + 1;
                    $jml_monitoring = $jml_monitoring + (($role_id==3?$itemperimeter->status_pic:$itemperimeter->status_fo)==true?1:0);
                    $data[] = array(
                        "id_perimeter_level" => $itemperimeter->mpml_id,
                        "level" => $itemperimeter->mpml_name,
                        "id_perimeter_cluster" => $itemperimeter->tpmd_id,
                        "id_cluster" => $itemperimeter->mcr_id,
                        "cluster_ruangan" => (($itemperimeter->tpmd_order > 1)? ($itemperimeter->mcr_name.' - '.$itemperimeter->tpmd_order) :$itemperimeter->mcr_name),
                        "order" => $itemperimeter->tpmd_order,
                        "status_konfirmasi" => $itemperimeter->status_konfirmasi,
                        "status" => ($role_id==3?$itemperimeter->status_pic:$itemperimeter->status_fo),
                        "file_cluster" => $itemperimeter->tpmd_file_foto != null ? "/cluster_ruangan/".$itemperimeter->mpm_mc_id."/".$itemperimeter->tpmd_file_foto:null,
                        "file_cluster_tumb" => $itemperimeter->tpmd_file_tumb != null ? "/cluster_ruangan/".$itemperimeter->mpm_mc_id."/".$itemperimeter->tpmd_file_tumb:null,
                        "last_update" => $itemperimeter->last_update,
                        "aktifitas" => $data_aktifitas_cluster,
                    );
                }
                $dataprogress = array("total_monitor"=> $total_monitoring,
                    "sudah_dimonitor"=> $jml_monitoring,
                    "belum_dimonitor"=> $total_monitoring - $jml_monitoring );
                
                return response()->json(['status_monitoring' => $dataprogress,'status' => 200,'data' => $data]);
        } else {
            return response()->json(['status_monitoring' => $dataprogress,'status' => 200,'data' => $data]);
        }
    }
}
