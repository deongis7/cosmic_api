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
use App\TblPerimeterClosed;
use App\TrnAktifitas;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;
use App\TrnAktifitasFile;

use DB;
use function Complex\negative;


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
        $search = null;
        $endpage = 1;
        $monitoring = $request->monitoring;

        $nik = $request->nik;
        $str = "_get_perimeterlist_by_perusahaan_". $kd_perusahaan;

        if(isset($nik)){
            $str = $str.'_nik_'. $nik;
            $user = User::where('username', $nik)->first();
            $str_fnc[]=$nik;
        }
        if(isset($monitoring)){
            $str = $str.'_monitoring_'. $monitoring;
            $str_fnc[]=$monitoring;
        }
        if(isset($request->limit)){
            $str = $str.'_limit_'. $request->limit;
            $limit=$request->limit;
            if(isset($request->page)){
                $str = $str.'_page_'. $request->page;
                $page=$request->page;
            }
        }
        if(isset($request->search)){
            $str = $str.'_searh_'. str_replace(' ','_',$request->search);
            $search=$request->search;
        }
        if(isset($request->week)){
            $str = $str.'_week_'. str_replace(' ','_',$request->search);
            $week=$request->week;
        }
        //dd($str);
        $datacache = Cache::remember(env('APP_ENV', 'dev').$str, 20 * 60, function()use($kd_perusahaan,$nik,$user,$role_id,$limit,$page,$monitoring,$endpage,$search) {
            $data = array();
            $dashboard = array("total_perimeter" => 0, "sudah_dimonitor" => 0, "belum_dimonitor" => 0,);
            //current week
            $crweeks = AppHelper::Weeks();
            $currentweek =$crweeks['startweek'].'-'.$crweeks['endweek'];

            $perimeter = new Perimeter;
            $perimeter->setConnection('pgsql2');
            $perimeter = $perimeter->select('master_region.mr_id','master_region.mr_name','master_perimeter.mpm_id',
                'master_perimeter.mpm_name','master_perimeter.mpm_alamat',
                'master_perimeter_kategori.mpmk_name',
                'master_provinsi.mpro_name', 'master_kabupaten.mkab_name',
                DB::raw("status_monitoring_perimeter_bumn(master_perimeter.mpm_id) as status_bumn"),
                DB::raw("status_monitoring_perimeter_pic(master_perimeter.mpm_id,max(userpic.username)) as status_pic"),
                DB::raw("status_monitoring_perimeter_fo(master_perimeter.mpm_id,max(userfo.username)) as status_fo")

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
              if(isset($monitoring)) {
                  if ($monitoring == 'true') {
                    if(isset($nik) && ($user != null)) {
                      if ($role_id == 3) {
                          $perimeter = $perimeter->where(DB::raw("status_monitoring_perimeter_pic(master_perimeter.mpm_id,userpic.username)"),true);
                      } else if ($role_id == 4) {
                          $perimeter = $perimeter->where(DB::raw("status_monitoring_perimeter_fo(master_perimeter.mpm_id,userfo.username)"),true);
                      }
                    } else {
                      $perimeter = $perimeter->where(DB::raw("status_monitoring_perimeter_bumn(master_perimeter.mpm_id)"),true);
                    }

                  } else{
                    if(isset($nik) && ($user != null)) {
                      if ($role_id == 3) {
                          $perimeter = $perimeter->where(DB::raw("status_monitoring_perimeter_pic(master_perimeter.mpm_id,userpic.username)"),false);
                      } else if ($role_id == 4) {
                          $perimeter = $perimeter->where(DB::raw("status_monitoring_perimeter_fo(master_perimeter.mpm_id,userfo.username)"),false);
                      }
                    } else {
                      $perimeter = $perimeter->where(DB::raw("status_monitoring_perimeter_bumn(master_perimeter.mpm_id)"),false);
                    }
                  }
              }

            $perimeter = $perimeter->where('master_perimeter.mpm_mc_id', $kd_perusahaan);

            if(isset($search)) {
                $perimeter = $perimeter->where(DB::raw("lower(TRIM(master_perimeter.mpm_name))"),'like','%'.strtolower(trim($search)).'%');
            }

            $perimeter = $perimeter->groupBy('master_region.mr_id','master_region.mr_name','master_perimeter.mpm_id','master_perimeter.mpm_name','master_perimeter.mpm_alamat',
                    'master_perimeter_kategori.mpmk_name','master_provinsi.mpro_name', 'master_kabupaten.mkab_name',
                    DB::raw("status_monitoring_perimeter_bumn(master_perimeter.mpm_id) "))
                ->orderBy('master_perimeter.mpm_name', 'asc');
            //dd(count($perimeter->get()) );
            $jmltotal=(count($perimeter->get()));
            if(isset($limit)) {
                $perimeter = $perimeter->limit($limit);
                $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

                if (isset($page)) {
                    $offset = ((int)$page -1) * (int)$limit;
                    $perimeter = $perimeter->offset($offset);
                }
            }
            $perimeter = $perimeter->get();
            //$totalperimeter = $perimeter->count();
            //$totalpmmonitoring = 0;

            foreach ($perimeter as $itemperimeter) {
              /** $cluster = new PerimeterLevel;
              //$cluster->setConnection('pgsql2');
              //$cluster = $cluster->join('table_perimeter_detail','table_perimeter_detail.tpmd_mpml_id', 'master_perimeter_level.mpml_id')
                    ->where('table_perimeter_detail.tpmd_cek', true)
                    ->where('master_perimeter_level.mpml_mpm_id',$itemperimeter->mpm_id)->count();
                $status = $this->getStatusMonitoringPerimeter($itemperimeter->mpm_id, $role_id, $cluster);
              */
              /**$status_monitoring = ($status['status']);

                if(isset($monitoring)) {
                    //dd('tes1');
                    if ($monitoring == 'true') {
                        if ($status['status'] == true) {
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
                        }

                    } else if ($monitoring == 'false') {

                        if ($status['status'] == false) {
                            //dd('tes');
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
                        }
                    }
                } else {
                    //dd('tesa');
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
                }
                */
                if(isset($nik) && ($user != null)) {
                  $status_monitoring = ($role_id==3?$itemperimeter->status_pic:$itemperimeter->status_fo);
                } else {
                  $status_monitoring = $itemperimeter->status_bumn;
                }

                $data[] = array(
                    "id_region" => $itemperimeter->mr_id,
                    "region" => $itemperimeter->mr_name,
                    "id_perimeter" => $itemperimeter->mpm_id,
                    "nama_perimeter" => $itemperimeter->mpm_name,
                    "alamat" => $itemperimeter->mpm_name,
                    "kategori" => $itemperimeter->mpmk_name,
                    "status_monitoring" => $status_monitoring,
                    //"status_monitoring" => ($status['status']),
                    //"percentage" => ($status['percentage']),
                    "percentage" => 0,
                    "provinsi" => $itemperimeter->mpro_name,
                    "kabupaten" => $itemperimeter->mkab_name,

                );
                //if ($status['status'] == true) {
                  //  $totalpmmonitoring++;
                //}
            }

            //dashboard
            //$dashboard = array(
            //   "total_perimeter" => $totalperimeter,
            //   "sudah_dimonitor" => $totalpmmonitoring,
             //  "belum_dimonitor" => $totalperimeter - $totalpmmonitoring
            //);

            //return  $data;
            return array('page_end' => $endpage, 'data' => $data);
        });
            if(isset($nik) && ($user != null)) {
              $status_dashboard = $this->getJumlahPerimeterLevel($kd_perusahaan,$nik);
            } else {
                $status_dashboard = array("total_perimeter" => 0, "sudah_dimonitor" => 0, "belum_dimonitor" => 0,);
            }
        //$status_dashboard = $this->getJumlahPerimeterLevel($kd_perusahaan,$nik);
        //$status_dashboard = array("total_perimeter" => 0, "sudah_dimonitor" => 0, "belum_dimonitor" => 0,);
        return response()->json(['status' => 200,'page_end' =>$datacache['page_end'], 'data_dashboard' => $status_dashboard, 'data' => $datacache['data']]);

    }


    //Get Perimeter Level by Perimeter
    public function getPerimeterLevelListbyPerimeter($id_perimeter,Request $request){

        $limit = null;
        $page = null;
        $search = null;
        $endpage = 1;
        $user = null;
        $role_id = null;
        $nik = $request->nik;
        $str = "_get_perimeterlevellist_by_perimeter_". $id_perimeter;


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
        if(isset($request->search)){
            $str = $str.'_searh_'. str_replace(' ','_',$request->search);
            $search=$request->search;
        }
        //dd($str);
        //dd($str_fnc);
        $datacache = Cache::remember(env('APP_ENV', 'dev').$str, 1 * 5, function()use($id_perimeter,$nik,$user,$role_id,$limit,$page,$endpage,$search) {

            $data = array();
            $dashboard = array("total_perimeter" => 0, "sudah_dimonitor" => 0, "belum_dimonitor" => 0,);
            $perimeter = new Perimeter;
            $perimeter->setConnection('pgsql2');
            $perimeter = $perimeter->select( "master_perimeter.mpm_id", "master_perimeter_level.mpml_id", "master_perimeter_level.mpml_name","master_perimeter.mpm_name",
                        "master_perimeter_level.mpml_ket", "userpic.username as nik_pic", "userpic.first_name as pic", "userfo.username as nik_fo",
                        "userfo.first_name as fo",DB::raw("(CASE WHEN tpc.tbpc_status is null THEN 0 ELSE tpc.tbpc_status END) AS status_perimeter"),"tpc.tbpc_alasan",
                        DB::raw("status_monitoring_perimeter_level_pic(master_perimeter_level.mpml_id,userpic.username) as status_pic"),
                        DB::raw("status_monitoring_perimeter_level_fo(master_perimeter_level.mpml_id,userfo.username) as status_fo")
                        )
                        ->join("master_perimeter_level", "master_perimeter_level.mpml_mpm_id", "master_perimeter.mpm_id")
                        ->leftjoin("app_users as userpic", "userpic.username", "master_perimeter_level.mpml_pic_nik")
                        ->leftjoin("app_users as userfo", "userfo.username", "master_perimeter_level.mpml_me_nik")
                        ->leftjoin("table_perimeter_closed as tpc", function($join)
                        {
                            $join->on("tpc.tbpc_mpml_id","=", "master_perimeter_level.mpml_id");
                            $join->on("tpc.tbpc_startdate","<=",DB::raw("'".Carbon::now()->format("Y-m-d")."'"));
                            $join->on("tpc.tbpc_enddate",">=",DB::raw("'".Carbon::now()->format("Y-m-d")."'"));

                        });
            if(isset($nik) && ($user != null)) {
                $role_id = $user->roles()->first()->id;
                if ($role_id == 3) {
                    $perimeter = $perimeter->where('userpic.username', $nik);
                } else if ($role_id == 4) {
                    $perimeter = $perimeter->where('userfo.username', $nik);
                }
            }

            if(isset($search)) {
                $perimeter = $perimeter->where(DB::raw("lower(TRIM(mpml_name))"),'like','%'.strtolower(trim($search)).'%');
            }

            $perimeter = $perimeter->where('master_perimeter.mpm_id', $id_perimeter)
                ->orderBy('master_perimeter.mpm_name', 'asc')
                ->orderBy('master_perimeter_level.mpml_name', 'asc');
            $jmltotal=($perimeter->count());
            if(isset($limit)) {
                $perimeter = $perimeter->limit($limit);
                $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

                if (isset($page)) {
                    $offset = ((int)$page -1) * (int)$limit;
                    $perimeter = $perimeter->offset($offset);
                }
            }
            $perimeter = $perimeter->get();
            $totalperimeter = $perimeter->count();
            $totalpmmonitoring = 0;

            foreach ($perimeter as $itemperimeter) {
                //$cluster = new TblPerimeterDetail;
                //$cluster->setConnection('pgsql2');
                //$cluster = $cluster->where('tpmd_mpml_id', $itemperimeter->mpml_id)->where('tpmd_cek', true)->count();
                //$status = $this->getStatusMonitoring($itemperimeter->mpml_id, $role_id, $cluster);


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
                            //"status_monitoring" => ($status['status']),
                            "status_monitoring" => ($role_id==3?$itemperimeter->status_pic:$itemperimeter->status_fo),
                            "status_perimeter" => $itemperimeter->status_perimeter,
                            "alasan" => $itemperimeter->tbpc_alasan,
                            //"percentage" => ($status['percentage']),
                            "percentage" => 0,

                    );
                if ($role_id==3?$itemperimeter->status_pic:$itemperimeter->status_fo == true) {
                            $totalpmmonitoring++;
                        }
            }

                    //dashboard
            $dashboard = array(
                "total_perimeter" => $totalperimeter,
                "sudah_dimonitor" => $totalpmmonitoring,
                "belum_dimonitor" => $totalperimeter - $totalpmmonitoring
            );

            return array('status' => 200, 'page_end' => $endpage,'data_dashboard' => $dashboard, 'data' => $data);

        });
        return response()->json($datacache);

    }

    //Get Region
    public function getRegionList($kd_perusahaan,Request $request){
//dd($kd_perusahaaan);
        $limit = null;
        $page = null;
        $search = null;
        $endpage = 1;
        $str = "_get_regionlist_". $kd_perusahaan;
        if(isset($request->limit)){
            $str = $str.'_limit_'. $request->limit;
            $limit=$request->limit;
            if(isset($request->page)){
                $str = $str.'_page_'. $request->page;
                $page=$request->page;
            }
        }
        if(isset($request->search)){
            $str = $str.'_searh_'. str_replace(' ','_',$request->search);
            $search=$request->search;
        }
        //dd($str);
        $datacache = Cache::remember(env('APP_ENV', 'dev').$str, 50 * 60, function()use($kd_perusahaan,$page,$limit,$endpage,$search) {

            $data = array();
            $region = new Region;
            $region->setConnection('pgsql2');
            $region = $region->where( 'mr_mc_id', '=', $kd_perusahaan);
            if(isset($search)) {
                $region = $region->where(DB::raw("lower(TRIM(mr_name))"),'like','%'.strtolower(trim($search)).'%');
            }
            $region = $region->orderBy('mr_name','asc');
            $jmltotal=($region->count());
            if(isset($limit)) {
                $region = $region->limit($limit);
                $endpage = (int)(ceil((int)$jmltotal/(int)$limit));
                     if (isset($page)) {
                         $offset = ((int)$page -1) * (int)$limit;
                         $region = $region->offset($offset);
                     }
            }
            $region = $region->get();

            foreach ($region as $itemregion) {

                //dd($status['status']);
                $data[] = array(
                    "id_region" => $itemregion->mr_id,
                    "nama_region" => $itemregion->mr_name,
                );
            }

            return array('status' => 200, 'page_end' => $endpage, 'data' => $data);

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
    private function getJumlahPerimeterLevel($kd_perusahaan,$nik){

        $user = null;
        $role_id = null;
        $nik = $nik;
        $str = "_get_jumlah_perimeterlevellist_by_perimeter_". $kd_perusahaan;

        if(isset($nik)){
            $str = $str.'_nik_'. $nik;
            $user = new User;
            $user->setConnection('pgsql2');
            $user = $user->where('username', $nik)->first();
            $str_fnc[]=$nik;
        }
        //dd($str_fnc);
        $datacache = Cache::remember(env('APP_ENV', 'dev').$str, 40 * 60, function()use($kd_perusahaan,$nik,$user,$role_id) {


            $data = array("total_perimeter" => 0, "sudah_dimonitor" => 0, "belum_dimonitor" => 0,);

            $perimeter = new Perimeter;
            $perimeter->setConnection('pgsql2');
            $perimeter = $perimeter->select( 'master_perimeter.mpm_id', 'master_perimeter_level.mpml_id',
                    DB::raw("status_monitoring_perimeter_level_pic(master_perimeter_level.mpml_id,userpic.username) as status_pic"),
                    DB::raw("status_monitoring_perimeter_level_fo(master_perimeter_level.mpml_id,userfo.username) as status_fo")
                  )
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
              //$cluster = new TblPerimeterDetail;
              //$cluster->setConnection('pgsql2');
              //$cluster = $cluster->where('tpmd_mpml_id', $itemperimeter->mpml_id)->where('tpmd_cek', true)->count();
              //$status = $this->getStatusMonitoring($itemperimeter->mpml_id, $role_id, $cluster);

                if (($role_id==3?$itemperimeter->status_pic:$itemperimeter->status_fo)== true) {
                    $totalpmmonitoring++;
                }
            }

            //dashboard
            $data= array(
                "total_perimeter" => $totalperimeter,
                "sudah_dimonitor" => $totalpmmonitoring,
                "belum_dimonitor" => $totalperimeter - $totalpmmonitoring
            );

            return $data;

        });
        return ($datacache);

    }

    //Get Jumlah Perimeter List
    public function getStatusPerimeterLevel($kd_perusahaan, Request $request){

        $nik = $request->nik;
       $data = $this->getJumlahPerimeterLevel($kd_perusahaan,$nik);
        return response()->json(['status' => 200 ,'data' => $data]);

    }

    //Get Status Monitoring Perimeter Level
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

    //Get Status Monitoring Perimeter
    private function getStatusMonitoringPerimeter($id_perimeter,$id_role, $cluster){

        $data = array();
        $weeks = AppHelper::Weeks();
        $startdate = $weeks['startweek'];
        $enddate = $weeks['endweek'];

        if($id_role == 4){
            $clustertrans = DB::connection('pgsql2')->select( "select tpd.tpmd_id, mpl.mpml_mpm_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join master_perimeter mp on mpl.mpml_mpm_id = mp.mpm_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where  mpl.mpml_mpm_id= ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4 and ta.ta_status <> 2
		group by tpd.tpmd_id,  mpl.mpml_mpm_id,tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter, $startdate, $enddate]);
        } else {
            $clustertrans = DB::connection('pgsql2')->select( "select tpd.tpmd_id,  mpl.mpml_mpm_id,tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
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
            $perimeter = new Perimeter;
            $perimeter->setConnection('pgsql2');
            //Perimeter::select('master_region.mr_id','master_region.mr_name','master_perimeter_level.mpml_id',
            $perimeter =   $perimeter->select('master_region.mr_id','master_region.mr_name',
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
                    "alamat" => $perimeter->mpm_alamat,
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

    //Get Perimeter Detail
    public function updateDetailPerimeter(Request $request){
        $this->validate($request, [
            'id_perimeter_level' => 'required',
            'nik_fo' => 'required',
            'nik_pic' => 'required',
            'id_kategori_perimeter' => 'required'
        ]);

        $data = array();
        $cluster=$request->cluster;


        //Perimeter::select('master_region.mr_id','master_region.mr_name','master_perimeter_level.mpml_id',
        $perimeterlevel = PerimeterLevel::where('mpml_id',$request->id_perimeter_level)->first();


        if ($perimeterlevel!= null){
            $perimeter = Perimeter::where('mpm_id',$perimeterlevel->mpml_mpm_id)->first();
            if ($perimeter!= null){
                $perimeterlevel->mpml_ket = $request->keterangan;
                $perimeterlevel->mpml_me_nik = $request->nik_fo;
                $perimeterlevel->mpml_pic_nik = $request->nik_pic;
                $perimeter->mpm_mpmk_id = $request->id_kategori_perimeter;
                if($perimeter->save()){
                    $perimeterlevel->save();

                    PerimeterDetail::where('tpmd_mpml_id' ,$request->id_perimeter_level)->update(['tpmd_cek' => false]);

                    //dd((strtolower($item_tmp_perimeter->c1)));
                    //lobby
                    foreach($cluster as $itemcluster){
                        $jml=$itemcluster['jumlah'];

                        for ($i = 1; $i <= $jml; $i++){
                            PerimeterDetail::updateOrCreate(['tpmd_mpml_id' => $request->id_perimeter_level, 'tpmd_mcr_id' => $itemcluster['id_cluster_ruangan'], 'tpmd_order' => $i],['tpmd_cek' => true]);
                        }
                    }


                    return response()->json(['status' => 200,'message' => 'Data Berhasil Disimpan']);

                } else {
                    return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
                }

            } else {
                return response()->json(['status' => 404,'message' => 'Data Tidak Ditemukan'])->setStatusCode(404);

            }
        } else {
            return response()->json(['status' => 404,'message' => 'Data Tidak Ditemukan'])->setStatusCode(404);
        }

    }

    //Get Perimeter per Region
    public function getPerimeterListbyRegion($id,Request $request){
        $limit = null;
        $page = null;
        $search = null;
        $endpage = 1;
        $str = "_get_perimeterlist_by_region_". $id;
        if(isset($request->limit)){
            $str = $str.'_limit_'. $request->limit;
            $limit=$request->limit;
            if(isset($request->page)){
                $str = $str.'_page_'. $request->page;
                $page=$request->page;
            }
        }
        //dd(str_replace(' ','_',$request->search));
        if(isset($request->search)){
            $str = $str.'_searh_'. str_replace(' ','_',$request->search);
            $search=$request->search;
        }

        $datacache = Cache::remember(env('APP_ENV', 'dev').$str, 10 * 60, function()use($id,$limit,$page, $endpage,$search) {
            $data = array();
            $perimeter = new Perimeter;
            $perimeter->setConnection('pgsql2');
            $perimeter = $perimeter->select('master_region.mr_id', 'master_region.mr_name',
                'master_perimeter.mpm_id', 'master_perimeter.mpm_name',
                'master_perimeter.mpm_alamat', 'master_perimeter_kategori.mpmk_name',
                'master_provinsi.mpro_name', 'master_kabupaten.mkab_name', 'master_perimeter.mpm_mc_id')
                ->join('master_region', 'master_region.mr_id', 'master_perimeter.mpm_mr_id')
                ->join('master_perimeter_kategori', 'master_perimeter_kategori.mpmk_id', 'master_perimeter.mpm_mpmk_id')
                ->leftjoin('master_provinsi', 'master_provinsi.mpro_id', 'master_perimeter.mpm_mpro_id')
                ->leftjoin('master_kabupaten', 'master_kabupaten.mkab_id', 'master_perimeter.mpm_mkab_id')
                ->where('master_region.mr_id', $id);
            if(isset($search)) {
                $perimeter = $perimeter->where(DB::raw("lower(TRIM(mpm_name))"),'like','%'.strtolower(trim($search)).'%');
            }
            $perimeter = $perimeter->orderBy('master_perimeter.mpm_name', 'asc');

            //total_jumlah
            $jmltotal=($perimeter->count());
            if(isset($limit)) {
                $perimeter = $perimeter->limit($limit);
                $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

                if (isset($page)) {
                    $offset = ((int)$page -1) * (int)$limit;
                    $perimeter = $perimeter->offset($offset);
                }
            }
            $perimeter =$perimeter->get();

            foreach ($perimeter as $itemperimeter) {
                $data[] = array(
                    "id_perimeter" => $itemperimeter->mpm_id,
                    "nama_perimeter" => $itemperimeter->mpm_name,

                    "alamat" => $itemperimeter->mpm_name,
                    "kategori" => $itemperimeter->mpmk_name,

                    "provinsi" => $itemperimeter->mpro_name,
                    "kabupaten" => $itemperimeter->mkab_name,
                    "aktifitas" => $this->getFotoByPerimeter($itemperimeter->mpm_id, $itemperimeter->mpm_mc_id)
                );
            }
            return array('status' => 200,'page_end' => $endpage,'data' => $data);
        });
        return response()->json($datacache);

    }

    //Post Perimeter Closed
    public function addClosedPerimeter(Request $request){
        $this->validate($request, [
            'id_perimeter_level' => 'required',
            'alasan' => 'required'
        ]);
        $weeks = AppHelper::Weeks();
        $startdate = $weeks['startweek'];
        $enddate = $weeks['endweek'];


        $closed = TblPerimeterClosed::where('tbpc_mpml_id', $request->id_perimeter_level)
            ->where('tbpc_startdate', $startdate)
            ->where('tbpc_enddate', $enddate)->first();

        if ($closed == null){
            $closed= New TblPerimeterClosed();
            $closed->tbpc_mpml_id = $request->id_perimeter_level;
            $closed->tbpc_alasan = $request->alasan;
            $closed->tbpc_requestor = $request->nik;
            $closed->tbpc_startdate = $startdate;
            $closed->tbpc_enddate = $enddate;
            $closed->tbpc_status = 1;
        } else {
            $closed->tbpc_alasan = $request->alasan;
            $closed->tbpc_requestor = $request->nik;
            $closed->tbpc_startdate = $startdate;
            $closed->tbpc_enddate = $enddate;
            $closed->tbpc_status = 1;
        }
        //ditutup sementara
        if($closed->save()) {
            return response()->json(['status' => 200, 'message' => 'Data Berhasil Disimpan']);
        }
        else {
         return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
        // return response()->json(['status' => 500,'message' => 'Untuk saat ini fitur dimatikan sementara'])->setStatusCode(500);
      }
         //return response()->json(['status' => 404,'message' => 'Untuk saat ini fitur dimatikan sementara'])->setStatusCode(404);

    }

    //Post Perimeter Closed
    public function validasiClosedPerimeter(Request $request){
        $this->validate($request, [
            'id_perimeter_level' => 'required',
            'status' => 'required'
        ]);
        $weeks = AppHelper::Weeks();
        $startdate = $weeks['startweek'];
        $enddate = $weeks['endweek'];

        $closed = TblPerimeterClosed::where('tbpc_mpml_id', $request->id_perimeter_level)
            ->where('tbpc_startdate', $startdate)
            ->where('tbpc_enddate', $enddate)
            ->where('tbpc_status', 1)->first();


        if ($closed != null){
            $fo_nik = $closed->tbpc_requestor;
            $closed->tbpc_approval= $request->nik;
            $closed->tbpc_status = $request->status;
            $closed->tbpc_alasan = $request->alasan;
        } else {
            return response()->json(['status' => 404,'message' => 'Data Tidak Ditemukan'])->setStatusCode(404);
        }
        if($closed->save()) {
          if($request->status == 2){
            $perimeterdet=TblPerimeterDetail::where('tpmd_mpml_id', $request->id_perimeter_level)->get();
            foreach($perimeterdet as $itemperimeterdet){
              $kcar=KonfigurasiCAR::JOIN('master_cluster_ruangan','master_cluster_ruangan.mcr_id','konfigurasi_car.kcar_mcr_id')
                      ->JOIN('table_perimeter_detail','table_perimeter_detail.tpmd_mcr_id','master_cluster_ruangan.mcr_id')
                      ->where('table_perimeter_detail.tpmd_id', $itemperimeterdet->tpmd_id)->get();
              foreach($kcar as $itemkcar){
                $trn_aktifitas= TrnAktifitas::updateOrCreate(
                        ['ta_tpmd_id' => $itemperimeterdet->tpmd_id, 'ta_nik' => $fo_nik, 'ta_kcar_id' => $itemkcar->kcar_id,'ta_week' =>$weeks['weeks']],['ta_date' => $enddate, 'ta_status' => 1, 'ta_keterangan' =>'Tutup Perimeter']);
                $trn_aktifitas->save();
              }

            }
          }

            return response()->json(['status' => 200, 'message' => 'Data Berhasil Disimpan']);
        }
        else {
            return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
        }

       //return response()->json(['status' => 404,'message' => 'Untuk saat ini fitur dimatikan sementara'])->setStatusCode(404);

    }

    //Post Perimeter Closed
    public function updateAktifitasClosedPerimeter(Request $request){
        $this->validate($request, [
            'id_perimeter_level' => 'required',
            'date' => 'required'
        ]);
        $strdate =  Carbon::parse($request->date);
        $startdate = $strdate->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $enddate = $strdate->endOfWeek(Carbon::FRIDAY)->format('Y-m-d');

        $closed = TblPerimeterClosed::where('tbpc_mpml_id', $request->id_perimeter_level)
            ->where('tbpc_startdate', $startdate)
            ->where('tbpc_enddate', $enddate)->first();


        if ($closed != null){
            $fo_nik = $closed->tbpc_requestor;

                $perimeterdet=TblPerimeterDetail::where('tpmd_mpml_id', $request->id_perimeter_level)->get();
                foreach($perimeterdet as $itemperimeterdet){
                  $kcar=KonfigurasiCAR::JOIN('master_cluster_ruangan','master_cluster_ruangan.mcr_id','konfigurasi_car.kcar_mcr_id')
                          ->JOIN('table_perimeter_detail','table_perimeter_detail.tpmd_mcr_id','master_cluster_ruangan.mcr_id')
                          ->where('table_perimeter_detail.tpmd_id', $itemperimeterdet->tpmd_id)->get();
                  foreach($kcar as $itemkcar){
                    $trn_aktifitas= TrnAktifitas::updateOrCreate(
                            ['ta_tpmd_id' => $itemperimeterdet->tpmd_id, 'ta_nik' => $fo_nik, 'ta_kcar_id' => $itemkcar->kcar_id,'ta_week' =>$startdate.'-'.$enddate],['ta_date' => $enddate, 'ta_status' => 1, 'ta_keterangan' =>'Tutup Perimeter']);
                    $trn_aktifitas->save();
                  }

                }


                return response()->json(['status' => 200, 'message' => 'Data Berhasil Disimpan']);
        } else {
            return response()->json(['status' => 404,'message' => 'Data Tidak Ditemukan'])->setStatusCode(404);
        }


    }

    //Get Status Monitoring Perimeter Level
    private function getFotoByPerimeter($id_perimeter,$mc_id){
$datacache = Cache::remember(env('APP_ENV', 'dev').'_get_foto_by_perimeter_'.$id_perimeter, 5 * 60, function()use($id_perimeter,$mc_id) {
        $data = array();
        $weeks = AppHelper::Weeks();
        $startdate = $weeks['startweek'];
        $enddate = $weeks['endweek'];


        $clustertrans = DB::connection('pgsql2')->select( "select ta.ta_id, ta.ta_date, mpl.mpml_id, mpl.mpml_name,mcr.mcr_name,mcar.mcar_name, us.username as nik_fo, us.first_name as fo from transaksi_aktifitas ta
    		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
    		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
    		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
    		join master_cluster_ruangan mcr on mcr.mcr_id = tpd.tpmd_mcr_id
    		join master_car mcar on mcar.mcar_id = kc.kcar_mcar_id
    		join app_users us on us.username = mpl.mpml_me_nik
    		where ta.ta_status = 1 and mpl.mpml_mpm_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
        order by ta.ta_date desc limit 7", [$id_perimeter, $startdate, $enddate]);

        foreach ($clustertrans as $itemclustertrans) {
            $data[] = array(
                "id_perimeter_level" => $itemclustertrans->mpml_id,
                "level" => 'Lantai '.$itemclustertrans->mpml_name,
                "cluster" => $itemclustertrans->mcr_name,
                "id_aktifitas" => $itemclustertrans->ta_id,
                "aktifitas" => $itemclustertrans->mcar_name,
                "nik_fo" => $itemclustertrans->nik_fo,
                "fo" => $itemclustertrans->fo,
                "tanggal" => $itemclustertrans->ta_date,
                "file" => $this->getFile($itemclustertrans->ta_id,$mc_id)
              );
        }
          return $data;
      });
      return $datacache;


    }

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

    //POST
    public function openPerimeter(Request $request){
        $this->validate($request, [
            'id_perimeter_level' => 'required'
        ]);
        $weeks = AppHelper::Weeks();
        $startdate = $weeks['startweek'];
        $enddate = $weeks['endweek'];


        $open = TblPerimeterClosed::where('tbpc_mpml_id', $request->id_perimeter_level)
            ->where('tbpc_startdate', $startdate)
            ->where('tbpc_enddate', $enddate)->first();

        if ($open == null){
            $open= New TblPerimeterClosed();
            $open->setConnection('pgsql');
            $open->tbpc_mpml_id = $request->id_perimeter_level;
            $open->tbpc_requestor = $request->nik;
            $open->tbpc_startdate = $startdate;
            $open->tbpc_enddate = $enddate;
            $open->tbpc_status = 0;
        } else {
            $open->tbpc_requestor = $request->nik;
            $open->tbpc_startdate = $startdate;
            $open->tbpc_enddate = $enddate;
            $open->tbpc_status = 0;
        }
        if($open->save()) {
            return response()->json(['status' => 200, 'message' => 'Data Berhasil Disimpan']);
        }
         else {
             return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
         }

    }
}
