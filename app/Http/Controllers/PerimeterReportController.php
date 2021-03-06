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
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;
use App\TrnAktifitasFile;

use DB;
use function Complex\negative;


class PerimeterReportController extends Controller
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
        $column = null;
        $sort = null;
        $endpage = 1;
        $monitoring = $request->monitoring;
        $week=$request->week;

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
            $str = $str.'_week_'. $request->week;

        }

        if(isset($request->column_sort)) {
          $str = $str.'_sort_'. $request->column_sort;
          $column=$request->column_sort;
            if(isset($request->p_sort)) {
              $str = $str.'_'. $request->p_sort;
              $sort=$request->p_sort;
            }
        }
        //dd($str);
        $datacache = Cache::remember(env('APP_ENV', 'dev').$str, 60 * 60, function()use($kd_perusahaan,$nik,$user,$role_id,$limit,$page,$monitoring,$endpage,$search,$week,$column,$sort) {
            $data = array();
            $dashboard = array("total_perimeter" => 0, "sudah_dimonitor" => 0, "belum_dimonitor" => 0,);
            //current week
            $crweeks = AppHelper::Weeks();
            $currentweek =$crweeks['startweek'].'-'.$crweeks['endweek'];
            $param =  [$kd_perusahaan, $week];

            if (isset($week) && ($week != $currentweek)){
              $sql = "select rhw.rhw_mr_id, rhw_mr_name, rhw.rhw_mpm_id, rhw_mpm_name,'-'::varchar as alamat,'-'::varchar as kategori,
                    '-'::varchar  as provinsi, '-'::varchar  as kabupaten , (case when avg(rhw_mpml_cek)=1 then true else false end) as status_monitoring,
                    round(avg(rhw_mpml_cek),2) as percentage,status_monitoring_perimeter_last_update(rhw.rhw_mpm_id) as last_update from report_history_week rhw";

             if(isset($monitoring)) {
                if ($monitoring == 'true') {
                $sql =  $sql. " join (select rhw_week,rhw_mr_id,rhw_mpm_id ,round(avg(rhw_mpml_cek),2) as avgs from report_history_week group by rhw_week,rhw_mr_id,rhw_mpm_id )a on a.rhw_mpm_id = rhw.rhw_mpm_id
                       and round(a.avgs,0)=1 and a.rhw_week = rhw.rhw_week";
                } else if ($monitoring == 'false'){
                $sql =  $sql. " join (select rhw_week,rhw_mr_id,rhw_mpm_id ,round(avg(rhw_mpml_cek),2) as avgs from report_history_week group by rhw_week,rhw_mr_id,rhw_mpm_id )a on a.rhw_mpm_id = rhw.rhw_mpm_id
                         and round(a.avgs,0)<1 and a.rhw_week = rhw.rhw_week";
                }
              }
              $sql =  $sql. " where rhw.rhw_mc_id = ? and rhw.rhw_week = ?";

              if(isset($nik) && ($user != null)) {
                        $role_id = $user->roles()->first()->id;
                        if ($role_id == 3) {
                          $sql = $sql. " and rhw_pic_nik = ? ";
                          $param[] = $nik;
                        } else if ($role_id == 4) {
                          $sql = $sql. " and rhw_pic_fo = ? ";
                          $param[] = $nik;
                        }
              }
              if(isset($search)) {
                  $sql = $sql." and lower(TRIM(rhw_mpm_name)) like ? ";
                  $searchparam = '%'.strtolower(trim($search)).'%';
                  $param[] = $searchparam;
              }

              $sql = $sql." GROUP BY rhw.rhw_mr_id, rhw_mr_name, rhw.rhw_mpm_id, rhw_mpm_name,'-'::varchar ,'-'::varchar , '-'::varchar , '-'::varchar";

              if(isset($column)) {
                  if(isset($sort)) {
                      $sql = $sql. " order by ".$column." ".$sort ;
                  }else{
                      $sql = $sql . " order by ".$column." asc ";
                  }
              }else{
                  $sql = $sql . " order by rhw_mpm_name asc ";
              }

              $jmltotal=(count(DB::connection('pgsql3')->select($sql, $param)));

              if(isset($limit)) {
                        $sql = $sql . ' limit ?';
                        $param[] = $limit;
                        $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

                        if (isset($page)) {
                            $offset = ((int)$page -1) * (int)$limit;
                            $sql = $sql . ' offset ?';
                            $param[] = $offset;
                        }
              }

              $perimeter = DB::connection('pgsql3')->select($sql, $param);
              foreach($perimeter as $itemperimeter){
                $data[] = array(
                    "id_region" => $itemperimeter->rhw_mr_id,
                    "region" => $itemperimeter->rhw_mr_name,
                    "id_perimeter" => $itemperimeter->rhw_mpm_id,
                    "nama_perimeter" => $itemperimeter->rhw_mpm_name,
                    "alamat" => $itemperimeter->alamat,
                    "kategori" => $itemperimeter->kategori,
                    "status_monitoring" => $itemperimeter->status_monitoring,
                    "last_update" => $itemperimeter->last_update,
                    "percentage" =>  $itemperimeter->percentage,
                    "provinsi" => $itemperimeter->provinsi,
                    "kabupaten" => $itemperimeter->kabupaten,
                );
              }
            return array('page_end' => $endpage, 'status_current_week' => false,'data' => $data);
          } else {
            $perimeter = new Perimeter;
            $perimeter->setConnection('pgsql3');
            $perimeter = $perimeter->select('master_region.mr_id','master_region.mr_name','master_perimeter.mpm_id',
                'master_perimeter.mpm_name','master_perimeter.mpm_alamat',
                'master_perimeter_kategori.mpmk_name',
                'master_provinsi.mpro_name', 'master_kabupaten.mkab_name',DB::raw("status_monitoring_perimeter_last_update(master_perimeter.mpm_id) as last_update")
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

            $perimeter = $perimeter->where('master_perimeter.mpm_mc_id', $kd_perusahaan);

            if(isset($search)) {
                $perimeter = $perimeter->where(DB::raw("lower(TRIM(master_perimeter.mpm_name))"),'like','%'.strtolower(trim($search)).'%');
            }

            $perimeter = $perimeter->groupBy('master_region.mr_id','master_region.mr_name','master_perimeter.mpm_id','master_perimeter.mpm_name','master_perimeter.mpm_alamat',
                    'master_perimeter_kategori.mpmk_name','master_provinsi.mpro_name', 'master_kabupaten.mkab_name');

            if(isset($column)) {
                if(isset($sort)) {
                    $perimeter = $perimeter->orderBy($column,$sort);
                }else{
                    $perimeter = $perimeter->orderBy($column,"asc");
                }
            }else{
                  $perimeter = $perimeter->orderBy('master_perimeter.mpm_name', 'asc');
            }


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
              $cluster = new PerimeterLevel;
              $cluster->setConnection('pgsql3');
                $cluster = $cluster->join('table_perimeter_detail','table_perimeter_detail.tpmd_mpml_id', 'master_perimeter_level.mpml_id')
                    ->where('table_perimeter_detail.tpmd_cek', true)
                    ->where('master_perimeter_level.mpml_mpm_id',$itemperimeter->mpm_id)->count();
                $status = $this->getStatusMonitoringPerimeter($itemperimeter->mpm_id, $role_id, $cluster);

                $status_monitoring = ($status['status']);

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
                                "last_update" => $itemperimeter->last_update,
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
                                "last_update" => $itemperimeter->last_update,
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
                        "last_update" => $itemperimeter->last_update,
                        "percentage" => ($status['percentage']),
                        "provinsi" => $itemperimeter->mpro_name,
                        "kabupaten" => $itemperimeter->mkab_name,

                    );
                }

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
            return array('page_end' => $endpage, 'status_current_week' => true,'data' => $data);
          }

        });
            if(isset($nik) && ($user != null)) {
              $status_dashboard = $this->getJumlahPerimeterLevel($kd_perusahaan,$nik,$week);
            } else {
                $status_dashboard = array("total_perimeter" => 0, "sudah_dimonitor" => 0, "belum_dimonitor" => 0,);
            }
        //$status_dashboard = $this->getJumlahPerimeterLevel($kd_perusahaan,$nik);
        //$status_dashboard = array("total_perimeter" => 0, "sudah_dimonitor" => 0, "belum_dimonitor" => 0,);
        return response()->json(['status' => 200,'page_end' =>$datacache['page_end'], 'status_current_week' =>$datacache['status_current_week'],'data_dashboard' => $status_dashboard, 'data' => $datacache['data']]);

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
        $week=$request->week;
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
        if(isset($request->week)){
            $str = $str.'_week_'. $request->week;
        }
        //dd($str);
        //dd($str_fnc);
        $datacache = Cache::remember(env('APP_ENV', 'dev').$str, 1 * 5, function()use($id_perimeter,$nik,$user,$role_id,$limit,$page,$endpage,$search,$week) {

            $data = array();
            $dashboard = array("total_perimeter" => 0, "sudah_dimonitor" => 0, "belum_dimonitor" => 0,);
            //current week
            $crweeks = AppHelper::Weeks();
            $currentweek =$crweeks['startweek'].'-'.$crweeks['endweek'];
            $start = substr($week, 0, 10);
            $end = substr($week, 11, 20);
            //dd($start.$end);

            $param =  [$start,$end,$id_perimeter, $week];


              if (isset($week) && ($week != $currentweek)){
                $sql = "select rhw.rhw_mpm_id, rhw.rhw_mpml_id, rhw_mpm_name, rhw_mpml_name, rhw_pic_nik, rhw_pic_name,
                      rhw_fo_nik, rhw_fo_name, rhw_mpml_cek,(case when rhw_mpml_cek = 1 then true else false end) as status_monitoring ,
                      '0'::int4 as percentage, (case when tbpc.tbpc_status = 2 then 2 else 0 end) status_perimeter, tbpc.tbpc_alasan
                       from report_history_week rhw
                       left join table_perimeter_closed tbpc on tbpc.tbpc_mpml_id = rhw.rhw_mpml_id and tbpc.tbpc_startdate = ? and tbpc.tbpc_enddate =? ";

                $sql =  $sql. " where rhw.rhw_mpm_id = ? and rhw.rhw_week = ?";

                if(isset($nik) && ($user != null)) {
                          $role_id = $user->roles()->first()->id;
                          if ($role_id == 3) {
                            $sql = $sql. " and rhw_pic_nik = ? ";
                            $param[] = $nik;
                          } else if ($role_id == 4) {
                            $sql = $sql. " and rhw_pic_fo = ? ";
                            $param[] = $nik;
                          }
                }
                if(isset($search)) {
                    $sql = $sql." and lower(TRIM(rhw.rhw_mpml_name)) like ? ";
                    $searchparam = '%'.strtolower(trim($search)).'%';
                    $param[] = $searchparam;
                }

                $sql = $sql." order by rhw_mpml_name asc";

                $jmltotal=(count(DB::connection('pgsql3')->select($sql, $param)));

                if(isset($limit)) {
                          $sql = $sql . ' limit ?';
                          $param[] = $limit;
                          $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

                          if (isset($page)) {
                              $offset = ((int)$page -1) * (int)$limit;
                              $sql = $sql . ' offset ?';
                              $param[] = $offset;
                          }
                }

                $perimeter = DB::connection('pgsql3')->select($sql, $param);
                $totalperimeter = count($perimeter);
                $totalpmmonitoring = 0;
                foreach($perimeter as $itemperimeter){
                  $data[] = array(
                      "id_perimeter" => $itemperimeter->rhw_mpm_id,
                      "id_perimeter_level" => $itemperimeter->rhw_mpml_id,
                      "nama_perimeter" => $itemperimeter->rhw_mpm_name,
                      "level" => $itemperimeter->rhw_mpml_name,
                      "nik_pic" => $itemperimeter->rhw_pic_nik,
                      "pic" => $itemperimeter->rhw_pic_name,
                      "nik_fo" => $itemperimeter->rhw_fo_nik,
                      "fo" => $itemperimeter->rhw_fo_name,
                      "status_monitoring" =>  $itemperimeter->status_monitoring,
                      "status_perimeter" => $itemperimeter->status_perimeter,
                      "alasan" => $itemperimeter->tbpc_alasan,
                      "percentage" => $itemperimeter->percentage,
                  );

                  if ($itemperimeter->status_monitoring == true) {
                              $totalpmmonitoring++;
                          }
                }
                $dashboard = array(
                    "total_perimeter" => $totalperimeter,
                    "sudah_dimonitor" => $totalpmmonitoring,
                    "belum_dimonitor" => $totalperimeter - $totalpmmonitoring
                );

                return array('status' => 200, 'page_end' => $endpage,'data_dashboard' => $dashboard, 'data' => $data);

            } else {
              $perimeter = new Perimeter;
              $perimeter->setConnection('pgsql3');
              $perimeter = $perimeter->select( "master_perimeter.mpm_id", "master_perimeter_level.mpml_id", "master_perimeter_level.mpml_name","master_perimeter.mpm_name",
                          "master_perimeter_level.mpml_ket", "userpic.username as nik_pic", "userpic.first_name as pic", "userfo.username as nik_fo",
                          "userfo.first_name as fo",DB::raw("(CASE WHEN tpc.tbpc_status is null THEN 0 ELSE tpc.tbpc_status END) AS status_perimeter"),"tpc.tbpc_alasan")
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
                  ->orderBy('master_perimeter_level.mpml_name', 'asc')->where('master_perimeter.mpm_lockdown',0);
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
                  $cluster = new TblPerimeterDetail;
                  $cluster->setConnection('pgsql3');
                  $cluster = $cluster->where('tpmd_mpml_id', $itemperimeter->mpml_id)->where('tpmd_cek', true)->count();
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
                              "status_perimeter" => $itemperimeter->status_perimeter,
                              "alasan" => $itemperimeter->tbpc_alasan,
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

              return array('status' => 200, 'page_end' => $endpage,'data_dashboard' => $dashboard, 'data' => $data);

            }


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
    private function getJumlahPerimeterLevel($kd_perusahaan,$nik,$week){
        ini_set('max_execution_time', 180);
        $user = null;
        $role_id = null;
        $nik = $nik;
        $week=$week;
        $str = "_get_jumlah_perimeterlevellist_by_perimeter_". $kd_perusahaan;

        if(isset($nik)){
            $str = $str.'_nik_'. $nik;
            $user = new User;
            $user->setConnection('pgsql2');
            $user = $user->where('username', $nik)->first();
            $str_fnc[]=$nik;
        }

        if(isset($week)){
            $str = $str.'_week_'. $week;
        }
        //dd($str_fnc);
        // $datacache = Cache::remember(env('APP_ENV', 'dev').$str, 40 * 60, function()use($kd_perusahaan,$nik,$user,$role_id,$week) {
        $datacache = Cache::tags([$str])->remember(env('APP_ENV', 'dev').$str, 10, function () use($kd_perusahaan,$nik,$user,$role_id,$week) {

          //current week
          $crweeks = AppHelper::Weeks();
          $currentweek =$crweeks['startweek'].'-'.$crweeks['endweek'];
          $start = substr($week, 0, 10);
          $end = substr($week, 11, 20);
          $param =  [$kd_perusahaan, $week];
          $totalperimeter =0;
          $totalpmmonitoring=0;


            $data = array("total_perimeter" => 0, "sudah_dimonitor" => 0, "belum_dimonitor" => 0,);

            if (isset($week) && ($week != $currentweek)){
              $sql = "select rhw.rhw_mc_id , sum(case when rhw.rhw_mpml_cek = 1 then 1 else 0 end) as jml_monitoring,
                      sum(case when rhw.rhw_mpml_cek = 0  or rhw.rhw_mpml_cek is null then 1 else 0 end) as jml_belum_monitoring, count(rhw.rhw_mpml_cek) as total
                     from report_history_week rhw";

              $sql =  $sql. " where rhw.rhw_mc_id = ? and rhw.rhw_week = ?";

              if(isset($nik) && ($user != null)) {
                        $role_id = $user->roles()->first()->id;
                        if ($role_id == 3) {
                          $sql = $sql. " and rhw_pic_nik = ? ";
                          $param[] = $nik;
                        } else if ($role_id == 4) {
                          $sql = $sql. " and rhw_pic_fo = ? ";
                          $param[] = $nik;
                        }
              }
              $sql =  $sql. " group by rhw.rhw_mc_id";
              //dd($sql);
              $perimeter = DB::connection('pgsql2')->select($sql, $param);
              //dd($perimeter);
              foreach ($perimeter as $itemperimeter) {
                $totalperimeter = $itemperimeter->total;
                $totalpmmonitoring = $itemperimeter->jml_monitoring;
              }
            } else {
              $perimeter = new Perimeter;
              $perimeter->setConnection('pgsql2');
              $perimeter = $perimeter->select( 'master_perimeter.mpm_id', 'master_perimeter_level.mpml_id')
                  ->join('master_perimeter_level', 'master_perimeter_level.mpml_mpm_id', 'master_perimeter.mpm_id')
                  ->leftjoin('app_users as userpic', 'userpic.username', 'master_perimeter_level.mpml_pic_nik')
                  ->leftjoin('app_users as userfo', 'userfo.username', 'master_perimeter_level.mpml_me_nik')
                  ->where('master_perimeter.mpm_lockdown',0);

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
                $cluster = new TblPerimeterDetail;
                $cluster->setConnection('pgsql3');
                  $cluster = $cluster->where('tpmd_mpml_id', $itemperimeter->mpml_id)->where('tpmd_cek', true)->count();
                  $status = $this->getStatusMonitoring($itemperimeter->mpml_id, $role_id, $cluster);

                  if ($status['status'] == true) {
                      $totalpmmonitoring++;
                  }
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
        Cache::tags([$str])->flush();
        return ($datacache);

    }

    //Get Jumlah Perimeter List
    public function getStatusPerimeterLevel($kd_perusahaan, Request $request){

        $nik = $request->nik;
        $week = $request->week;
       $data = $this->getJumlahPerimeterLevel($kd_perusahaan,$nik,$week);
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
            $clustertrans = DB::connection('pgsql3')->select( "select tpd.tpmd_id, mpl.mpml_mpm_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join master_perimeter mp on mpl.mpml_mpm_id = mp.mpm_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where  mpl.mpml_mpm_id= ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4 and ta.ta_status <> 2
		group by tpd.tpmd_id,  mpl.mpml_mpm_id,tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter, $startdate, $enddate]);
        } else {
            $clustertrans = DB::connection('pgsql3')->select( "select tpd.tpmd_id,  mpl.mpml_mpm_id,tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
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
            $perimeter->setConnection('pgsql3');
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



}
