<?php

namespace App\Http\Controllers;


use App\ClusterRuangan;
use App\KonfigurasiCAR;
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


class PerimeterListController extends Controller
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


    //Get Perimeter List
    public function getPerimeterList($kd_perusahaan,Request $request){

        $user = null;
        $role_id = null;
        $limit = null;
        $page = null;
        $nik = $request->nik;
        $str = "get_perimeterlist_by_perusahaan_". $kd_perusahaan;

        if(isset($nik)){
            $str = $str.'_nik_'. $nik;
            $user = User::where('username', $nik)->first();
            $str_fnc[]=$nik;
        }
        if(isset($request->limit)){
            $str = $str.'_limit_'. $request->limit;
            $limit=$request->limit;
            if(isset($request->page)){
                $str = $str.'_page_'. $request->page;
                $page=$request->page;
            }
        }
        //dd($str_fnc);
        $datacache = Cache::remember($str, 2 * 60, function()use($kd_perusahaan,$nik,$user,$role_id,$limit,$page) {

            $data = array();
            $dashboard = array("total_perimeter" => 0, "sudah_dimonitor" => 0, "belum_dimonitor" => 0,);

            $perimeter = Perimeter::select('master_region.mr_id','master_region.mr_name','master_perimeter.mpm_id',
                'master_perimeter.mpm_name','master_perimeter.mpm_alamat',
                'master_perimeter_kategori.mpmk_name',
                'master_provinsi.mpro_name', 'master_kabupaten.mkab_name'
            )
                ->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
                ->join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
                ->join('master_perimeter_kategori','master_perimeter_kategori.mpmk_id','master_perimeter.mpm_mpmk_id')
                ->leftjoin('app_users as userpic','userpic.username','master_perimeter_level.mpml_pic_nik')
                ->leftjoin('app_users as userfo','userfo.username','master_perimeter_level.mpml_me_nik')
                ->leftjoin('master_provinsi','master_provinsi.mpro_id','master_perimeter.mpm_mpro_id')
                ->leftjoin('master_kabupaten','master_kabupaten.mkab_id','master_perimeter.mpm_mkab_id');

            if(isset($nik) && ($user != null)) {
                $role_id = $user->roles()->first()->id;
                if ($role_id == 3) {
                    $perimeter = $perimeter->where('userpic.username', $nik);
                } else if ($role_id == 4) {
                    $perimeter = $perimeter->where('userfo.username', $nik);
                }
            }

            $perimeter = $perimeter->where('master_perimeter.mpm_mc_id', $kd_perusahaan)
                ->groupBy('master_region.mr_id','master_region.mr_name','master_perimeter.mpm_name','master_perimeter.mpm_id','master_perimeter.mpm_alamat',
                    'master_perimeter_kategori.mpmk_name','master_provinsi.mpro_name', 'master_kabupaten.mkab_name')
                ->orderBy('master_perimeter.mpm_name', 'asc');
            //dd($limit);
            if(isset($limit)) {
                $perimeter = $perimeter->limit($limit);

                if (isset($page)) {
                    $offset = ((int)$page -1) * (int)$limit;
                    $perimeter = $perimeter->offset($offset);
                }
            }
            $perimeter = $perimeter->get();
            $totalperimeter = $perimeter->count();
            $totalpmmonitoring = 0;

            foreach ($perimeter as $itemperimeter) {
                $cluster = PerimeterLevel::join('table_perimeter_detail','table_perimeter_detail.tpmd_mpml_id', 'master_perimeter_level.mpml_id')
                    ->where('table_perimeter_detail.tpmd_cek', true)
                    ->where('master_perimeter_level.mpml_mpm_id',$itemperimeter->mpm_id)->count();
                $status = $this->getStatusMonitoringPerimeter($itemperimeter->mpm_id, $role_id, $cluster);


                //dd($status['status']);
                $data[] = array(
                    "id_region" => $itemperimeter->mr_id,
                    "region" => $itemperimeter->mr_name,
                    "id_perimeter" => $itemperimeter->mpm_id,
                    "nama_perimeter" => $itemperimeter->mpm_name,
                    "alamat" => $itemperimeter->mpm_name,
                    "kategori" => $itemperimeter->mpmk_name,
                    "status_monitoring" => ($status['status']),
                    "percentage" => ($status['percentage']),
                    "provinsi" => $itemperimeter->mpro_name,
                    "kabupaten" => $itemperimeter->mkab_name,

                );
                //if ($status['status'] == true) {
                  //  $totalpmmonitoring++;
                //}
            }

            //dashboard
            //$dashboard = array(
            //    "total_perimeter" => $totalperimeter,
            //    "sudah_dimonitor" => $totalpmmonitoring,
            //    "belum_dimonitor" => $totalperimeter - $totalpmmonitoring
            //);

            return array('status' => 200, 'data' => $data);

        });
        return response()->json($datacache);

    }

    //Get Perimeter Level by Perimeter
    public function getPerimeterLevelListbyPerimeter($id_perimeter,Request $request){

        $user = null;
        $role_id = null;
        $nik = $request->nik;
        $str = "get_perimeterlevellist_by_perimeter_". $id_perimeter;

        if(isset($nik)){
            $str = $str.'_nik_'. $nik;
            $user = User::where('username', $nik)->first();
            $str_fnc[]=$nik;
        }
        //dd($str_fnc);
        $datacache = Cache::remember($str, 2 * 60, function()use($id_perimeter,$nik,$user,$role_id) {

            $data = array();
            $dashboard = array("total_perimeter" => 0, "sudah_dimonitor" => 0, "belum_dimonitor" => 0,);

            $perimeter = Perimeter::select( 'master_perimeter.mpm_id', 'master_perimeter_level.mpml_id', 'master_perimeter.mpm_name',
                        'master_perimeter_level.mpml_ket', 'userpic.username as nik_pic', 'userpic.first_name as pic', 'userfo.username as nik_fo',
                        'userfo.first_name as fo')
                        ->join('master_perimeter_level', 'master_perimeter_level.mpml_mpm_id', 'master_perimeter.mpm_id')
                        ->leftjoin('app_users as userpic', 'userpic.username', 'master_perimeter_level.mpml_pic_nik')
                        ->leftjoin('app_users as userfo', 'userfo.username', 'master_perimeter_level.mpml_me_nik');

            if(isset($nik) && ($user != null)) {
                $role_id = $user->roles()->first()->id;
                if ($role_id == 3) {
                    $perimeter = $perimeter->where('userpic.username', $nik);
                } else if ($role_id == 4) {
                    $perimeter = $perimeter->where('userfo.username', $nik);
                }
            }

            $perimeter = $perimeter->where('master_perimeter.mpm_id', $id_perimeter)
                ->orderBy('master_perimeter.mpm_name', 'asc')
                ->orderBy('master_perimeter_level.mpml_name', 'asc')->get();
            $totalperimeter = $perimeter->count();
            $totalpmmonitoring = 0;

            foreach ($perimeter as $itemperimeter) {
                $cluster = TblPerimeterDetail::where('tpmd_mpml_id', $itemperimeter->mpml_id)->where('tpmd_cek', true)->count();
                $status = $this->getStatusMonitoring($itemperimeter->mpml_id, $role_id, $cluster);


                //dd($status['status']);
                $data[] = array(
                            "id_perimeter" => $itemperimeter->mpm_id,
                            "id_perimeter_level" => $itemperimeter->mpml_id,
                            "nama_perimeter" => $itemperimeter->mpm_name,
                            "level" => $itemperimeter->mpml_name,
                            "nik_pic" => $itemperimeter->nik_pic,
                            "pic" => $itemperimeter->pic,
                            "nik_fo" => $itemperimeter->nik_fo,
                            "fo" => $itemperimeter->fo,
                            "status_monitoring" => ($status['status']),
                            "percentage" => ($status['percentage']),

                    );
                if ($status['status'] == true) {
                            $totalpmmonitoring++;
                        }
            }

                    //dashboard
            $dashboard = array(
                "total_perimeter" => $totalperimeter,
                "sudah_dimonitor" => $totalpmmonitoring,
                "belum_dimonitor" => $totalperimeter - $totalpmmonitoring
            );

            return array('status' => 200, 'data_dashboard' => $dashboard, 'data' => $data);

        });
        return response()->json($datacache);

    }
    /**
    //Get Cluster per Perimeter Level
    public function getAktifitasListbyPerimeterLevel($id_perimeter_level,Request $request){
        $user = null;
        $role_id = null;
        $nik = $request->nik;
        if(isset($nik)){
            $user = User::where('username', $nik)->first();
        }

        $total_monitoring = 0;
        $jml_monitoring = 0;
        $dataprogress = array("total_monitor"=> 0,"sudah_dimonitor"=>0,"belum_dimonitor"=>0,);
        $data = array();
        if ($user != null){
            $role_id = $user->roles()->first()->id;


            $perimeter = DB::select( "select mpm.mpm_id,mpl.mpml_id,tpd.tpmd_id,mcr.mcr_id, mpm.mpm_name, mpk.mpmk_name, mpl.mpml_name,mcr.mcr_name,tpmd_order,mpl.mpml_pic_nik as nikpic,mpl.mpml_me_nik as nikfo from master_perimeter_level mpl
					join master_perimeter mpm on mpm.mpm_id = mpl.mpml_mpm_id
					join master_perimeter_kategori mpk on mpk.mpmk_id = mpm.mpm_mpmk_id
					join table_perimeter_detail tpd on tpd.tpmd_mpml_id = mpl.mpml_id and tpd.tpmd_cek=true
					join master_cluster_ruangan mcr on mcr.mcr_id = tpd.tpmd_mcr_id
					where mpl.mpml_id = ?
					order by mpm.mpm_name asc,mpl.mpml_name asc, mcr.mcr_name asc, tpmd_order asc", [$id_perimeter_level]);
            foreach($perimeter as $itemperimeter){
                $data_aktifitas_cluster = array();
                $aktifitas = KonfigurasiCAR::join('master_car','master_car.mcar_id','konfigurasi_car.kcar_mcar_id')
                    ->where('konfigurasi_car.kcar_ag_id',4)->where('konfigurasi_car.kcar_mcr_id',$itemperimeter->mcr_id)
                    ->where('master_car.mcar_active',true)->count();

                $data_aktifitas_cluster = $this->getClusterAktifitasMonitoring($itemperimeter->tpmd_id,$itemperimeter->mcr_id,$role_id,  $user->mc_id);
                $status = $this->getStatusMonitoringCluster($itemperimeter->tpmd_id,$role_id,$aktifitas);
                $total_monitoring = $total_monitoring + 1;
                $jml_monitoring = $jml_monitoring + ($status['status']==true?1:0);
                $data[] = array(
                    "id_perimeter_level" => $itemperimeter->mpml_id,
                    "level" => $itemperimeter->mpml_name,
                    "id_perimeter_cluster" => $itemperimeter->tpmd_id,
                    "id_cluster" => $itemperimeter->mcr_id,
                    "cluster_ruangan" => (($itemperimeter->tpmd_order > 1)? ($itemperimeter->mcr_name.' - '.$itemperimeter->tpmd_order) :$itemperimeter->mcr_name),
                    "order" => $itemperimeter->tpmd_order,
                    "status" => $status['status'],
                    "last_update" => $status['last_date'],
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
    */

    //Get Jumlah Perimeter List
    public function getJumlahPerimeterLevel($kd_perusahaan,Request $request){

        $user = null;
        $role_id = null;
        $nik = $request->nik;
        $str = "get_jumlah_perimeterlevellist_by_perimeter_". $kd_perusahaan;

        if(isset($nik)){
            $str = $str.'_nik_'. $nik;
            $user = User::where('username', $nik)->first();
            $str_fnc[]=$nik;
        }
        //dd($str_fnc);
        $datacache = Cache::remember($str, 10 * 60, function()use($kd_perusahaan,$nik,$user,$role_id) {


            $data = array("total_perimeter" => 0, "sudah_dimonitor" => 0, "belum_dimonitor" => 0,);

            $perimeter = Perimeter::select( 'master_perimeter.mpm_id', 'master_perimeter_level.mpml_id')
                ->join('master_perimeter_level', 'master_perimeter_level.mpml_mpm_id', 'master_perimeter.mpm_id')
                ->leftjoin('app_users as userpic', 'userpic.username', 'master_perimeter_level.mpml_pic_nik')
                ->leftjoin('app_users as userfo', 'userfo.username', 'master_perimeter_level.mpml_me_nik');

            if(isset($nik) && ($user != null)) {
                $role_id = $user->roles()->first()->id;
                if ($role_id == 3) {
                    $perimeter = $perimeter->where('userpic.username', $nik);
                } else if ($role_id == 4) {
                    $perimeter = $perimeter->where('userfo.username', $nik);
                }
            }

            $perimeter = $perimeter->where('master_perimeter.mpm_mc_id', $kd_perusahaan)->get();
            $totalperimeter = $perimeter->count();
            $totalpmmonitoring = 0;

            foreach ($perimeter as $itemperimeter) {
                $cluster = TblPerimeterDetail::where('tpmd_mpml_id', $itemperimeter->mpml_id)->where('tpmd_cek', true)->count();
                $status = $this->getStatusMonitoring($itemperimeter->mpml_id, $role_id, $cluster);

                if ($status['status'] == true) {
                    $totalpmmonitoring++;
                }
            }

            //dashboard
            $data= array(
                "total_perimeter" => $totalperimeter,
                "sudah_dimonitor" => $totalpmmonitoring,
                "belum_dimonitor" => $totalperimeter - $totalpmmonitoring
            );

            return array('status' => 200, 'data' => $data);

        });
        return response()->json($datacache);

    }

    //Get Status Monitoring Perimeter Level
    private function getStatusMonitoring($id_perimeter_level,$id_role, $cluster){

        $data = array();
        $weeks = AppHelper::Weeks();
        $startdate = $weeks['startweek'];
        $enddate = $weeks['endweek'];

        if($id_role == 4){
            $clustertrans = DB::select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where tpd.tpmd_mpml_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
		group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter_level, $startdate, $enddate]);
        } else {
            $clustertrans = DB::select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
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

    //Get Status Monitoring Perimeter
    private function getStatusMonitoringPerimeter($id_perimeter,$id_role, $cluster){

        $data = array();
        $weeks = AppHelper::Weeks();
        $startdate = $weeks['startweek'];
        $enddate = $weeks['endweek'];

        if($id_role == 4){
            $clustertrans = DB::select( "select tpd.tpmd_id, mpl.mpml_mpm_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join master_perimeter mp on mpl.mpml_mpm_id = mp.mpm_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where  mpl.mpml_mpm_id= ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
		group by tpd.tpmd_id,  mpl.mpml_mpm_id,tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter, $startdate, $enddate]);
        } else {
            $clustertrans = DB::select( "select tpd.tpmd_id,  mpl.mpml_mpm_id,tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join master_perimeter mp on mpl.mpml_mpm_id = mp.mpm_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where ta.ta_status = 1 and  mpl.mpml_mpm_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
		group by tpd.tpmd_id, mpl.mpml_mpm_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter, $startdate, $enddate]);
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
/**
    //Get Status Monitoring per Cluster
    private function getStatusMonitoringCluster($id_perimeter_cluster,$id_role,$aktifitas){

        $weeks = AppHelper::Weeks();
        $startdate = $weeks['startweek'];
        $enddate = $weeks['endweek'];

        if($id_role == 4){
            $clustertrans = DB::select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id, ta.ta_kcar_id,max(ta.ta_date_update) from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where  tpd.tpmd_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
		group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id, ta.ta_kcar_id order by max(ta.ta_date_update) desc", [$id_perimeter_cluster, $startdate, $enddate]);
        } else {
            $clustertrans = DB::select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id, ta.ta_kcar_id,max(ta.ta_date_update) from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where  ta.ta_status = 1 and  tpd.tpmd_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
		group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id, ta.ta_kcar_id order by max(ta.ta_date_update) desc", [$id_perimeter_cluster, $startdate, $enddate]);
        }

        if (count($clustertrans) > 0) {
            if ( $aktifitas <= count($clustertrans)) {
                return array(
                    "status" => true,
                    "last_date" =>$clustertrans[0]->max);

            } else {
                return array(
                    "status" => false,
                    "last_date" =>$clustertrans[0]->max);

            }
        } else {
            return array(
                "status" => false,
                "last_date" => null);
        }

    }

    //Get Cluster Aktifitas
    private function getClusterAktifitasMonitoring($id_perimeter_cluster,$id_cluster,$id_role,$id_perusahaan){

        $data = array();

        $weeks = AppHelper::Weeks();
        $startdate = $weeks['startweek'];
        $enddate = $weeks['endweek'];

        $cluster = DB::select( "select  kc.kcar_id, kc.kcar_mcr_id, kc.kcar_ag_id, mcar.mcar_name,ta.ta_id,ta.ta_status,ta.ta_ket_tolak from konfigurasi_car kc
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
 *
 */

    //Get Perimeter Detail
    public function getPerimeterDetail($id_perimeter){

            $data = array();

            //Perimeter::select('master_region.mr_id','master_region.mr_name','master_perimeter_level.mpml_id',
            $perimeter = Perimeter::select('master_region.mr_id','master_region.mr_name',
                'master_perimeter.mpm_id','master_perimeter.mpm_name','master_perimeter.mpm_alamat',
                'master_perimeter_kategori.mpmk_name','master_perimeter.mpm_longitude','master_perimeter.mpm_latitude',
                'master_provinsi.mpro_name', 'master_kabupaten.mkab_name'
            )

                ->join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
                ->join('master_perimeter_kategori','master_perimeter_kategori.mpmk_id','master_perimeter.mpm_mpmk_id')

                ->leftjoin('master_provinsi','master_provinsi.mpro_id','master_perimeter.mpm_mpro_id')
                ->leftjoin('master_kabupaten','master_kabupaten.mkab_id','master_perimeter.mpm_mkab_id')
                ->where('master_perimeter.mpm_id',$id_perimeter)
                ->first();

            //});

            if ($perimeter!= null){

                $data[] = array(
                    "id_region" => $perimeter->mr_id,
                    "region" => $perimeter->mr_name,
                    "id_perimeter" => $perimeter->mpm_id,
                    "nama_perimeter" => $perimeter->mpm_name,
                    "file" => null,
                    "file_tumb" => null,
                    "alamat" => $perimeter->mpm_name,
                    "kategori" => $perimeter->mpmk_name,
                    "longitude" => $perimeter->mpm_longitude,
                    "latitude" => $perimeter->mpm_latitude,
                    "provinsi" => $perimeter->mpro_name,
                    "kabupaten" => $perimeter->mkab_name,
                );
                return response()->json(['status' => 200 ,'data' => $data]);
            } else {
                return response()->json(['status' => 404,'message' => 'Data Tidak Ditemukan'])->setStatusCode(404);

            }




    }
}
