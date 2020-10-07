<?php

namespace App\Http\Controllers;

use App\Company;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;

use DB;


class DashboardController extends Controller
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

	public function getCosmicIndexAll(){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_cosmicindex_all", 360 * 60, function() {
	        $data = array();
	        $cosmicindex_all = DB::select("SELECT * FROM dashboard_perimeter_bycosmicindex()");

	        foreach($cosmicindex_all as $cia){
	            $data[] = array(
	                "v_judul" => $cia->z_judul,
	                "v_jml" => $cia->z_jml
	            );
	        }
	        return $data;
	    });
        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getPerimeterbyKategoriAll(){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_perimeter_bykategori_all", 360 * 60, function(){
	        $data = array();
	        $perimeter_bykategori_all = DB::select("SELECT * FROM dashboard_perimeter_bykategori()");

	        foreach($perimeter_bykategori_all as $pka){
	            $data[] = array(
	                "v_judul" => $pka->v_judul,
	                "v_jml" => $pka->v_jml
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getPerimeter_bykategoriperusahaan($name){
		$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_perimeter_bykategoriperusahaan2_".$name, 360 * 60, function()use($name){
	        $data = array();
	        $perimeter_bykategori_all = DB::select("SELECT * FROM dashboard_perimeterbyperusahaan('$name')");

	        foreach($perimeter_bykategori_all as $pka){
	            $data[] = array(
	                "v_mpm_id" => $pka->v_mpm_id,
	                "v_name_kategori" => $pka->v_name_kategori,
	                "v_jml" => $pka->v_jml,
	                "v_name_perusahaan" => $pka->v_name_perusahaan,
	                "v_name_provinsi" => $pka->v_name_provinsi
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getPerimeterbyProvinsiAll(){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_perimeter_byprovinsi_all", 360 * 60, function() {
	        $data = array();
	        $perimeter_byprovinsi_all = DB::select("SELECT * FROM dashboard_perimeter_byprovinsi()");

	        foreach($perimeter_byprovinsi_all as $ppa){
	            $data[] = array(
	                "v_judul" => $ppa->v_judul,
	                "v_jml" => $ppa->v_jml
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getDashboardHead(){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashmin_head", 360 * 60, function() {
	        $data = array();
	        $dashboard_head = DB::select("SELECT * FROM dashboard_head()");

	        foreach($dashboard_head as $dh){
	            $data[] = array(
	                "v_id" => $dh->x_id,
	                "v_judul" => $dh->x_judul,
	                "v_jml" => $dh->x_jml,
	                "v_flag_link" => $dh->x_flag_link,
	                "v_link" => $dh->x_link
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getWeekList(){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_week", 360 * 60, function() {
	        $data = array();
	        $dashboard_head = DB::select("SELECT * FROM list_aktivitas_week()");

	        foreach($dashboard_head as $dh){
	            $data[] = array(
	                "v_no" => $dh->v_rownum,
	                "v_week" => $dh->v_week,
	                "v_awal" => $dh->v_awal,
	                "v_akhir" => $dh->v_akhir,
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getMonitoring_ByMcidWeek($id, $tgl){
	    $datacache = Cache::remember(env('APP_ENV', 'dev')."_getmonitoring_bymcidweek_".$id."_".$tgl, 360 * 60, function()use($id, $tgl) {
	        $data = array();
	        $dashboard_head = DB::select("SELECT * FROM pemenuhan_monitoring_bymcidweek('$id','$tgl')");

	        foreach($dashboard_head as $dh){
	            $data[] = array(
	                "v_monitoring" => $dh->v_monitoring,
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getListMonitoring_ByMcidWeek($id, $tgl){
	    $datacache = Cache::remember(env('APP_ENV', 'dev')."_getlistmonitoring_bymcidweek_".$id."_".$tgl, 360 * 60, function()use($id, $tgl) {
	        $data = array();
	        $dashboard_head = DB::select("SELECT a.v_mpm_name, a.v_mpml_name, a.v_mpmk_name,
                    a.v_pic, a.v_fo, a.v_cek, b.persen_det
                    FROM week_historymonitoring_level('$id','$tgl') a
                    INNER JOIN week_aktivitas_cnt_bymcid_weekdet_pic('$id','$tgl') b
                    ON a.v_mpm_id=b.v_mpm_id;");

	        foreach($dashboard_head as $dh){
	            $data[] = array(
	                "v_mpm_name" => $dh->v_mpm_name,
	                "v_mpml_name" => $dh->v_mpml_name,
	                "v_mpmk_name" => $dh->v_mpmk_name,
	                "v_pic" => $dh->v_pic,
	                "v_fo" => $dh->v_fo,
	                "v_cek" => $dh->v_cek,
	                "persen_det" => $dh->persen_det,
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getDashboardHeadBUMN($id){
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashbumn_head_".$id, 360 * 60, function()use($id) {
	        $data = array();
	        $dashboard_head = DB::select("SELECT * FROM dashboardbumn_head('$id')");

	        foreach($dashboard_head as $dh){
	            $data[] = array(
	                "v_id" => $dh->x_id,
	                "v_judul" => $dh->x_judul,
	                "v_jml" => $dh->x_jml
	            );
	        }
	        return $data;
	    });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getDashboardProtokolBUMN($id){
       $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashprotokolbumn_".$id, 15 * 60, function()use($id) {
	        $data = array();
	        $dashboard_head = DB::select("SELECT v_mpt_id, v_mpt_name,
                        CASE WHEN v_tbpt_id > 0 THEN 'Terupload' ELSE 'Belum Terupload' END AS v_upload
                        FROM protokol_bymc('$id')");

	        foreach($dashboard_head as $dh){
	            $data[] = array(
	                "v_id" => $dh->v_mpt_id,
	                "v_name" => $dh->v_mpt_name,
	                "v_status" => $dh->v_upload
	            );
	        }
	        return $data;
	   });
	        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getDashboardMrMpmBUMN($id){
       $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashmrmpmbumn_".$id, 15 * 60, function()use($id) {
    	    $data = array();
    	    $dashboard_head = DB::select("SELECT mr_name, COUNT(mpm_id) cnt
                        FROM master_region mr
                        INNER JOIN master_perimeter mpm ON mpm_mr_id=mr.mr_id
                        WHERE mpm_id in (SELECT mpml_mpm_id FROM master_perimeter_level mpml)
                        AND mr.mr_mc_id='$id'
                        GROUP BY mr_name
                        ORDER BY mr_name");

    	    foreach($dashboard_head as $dh){
    	        $data[] = array(
    	            "v_region_name" => $dh->mr_name,
    	            "v_cnt" => $dh->cnt
    	        );
    	    }
    	    return $data;
	    });
	    return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function RefreshMvRangkumanAll(){
        $dashboard_head = DB::select("REFRESH MATERIALIZED VIEW mv_rangkuman_all");
        return $dashboard_head;
	}

    public function getCosmicIndexReport(Request $request){
        $str = 'get_cosmic_index_report';
        if(isset($request->date)){
            $strdate =  Carbon::parse($request->date);
            //  dd($date);
            $startdate = $strdate->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            $enddate = $strdate->endOfWeek(Carbon::FRIDAY)->format('Y-m-d');
            $str = $str."_".$startdate."_".$enddate;
        } else {
            $crweeks = AppHelper::Weeks();
            $startdate = $crweeks['startweek'];
            $enddate = $crweeks['endweek'];
            $str = $str."_".$startdate."_".$enddate;
        }

        $datacache =  Cache::remember(env('APP_ENV', 'dev').$str, 60 * 60, function()use($startdate,$enddate) {
            $data = array();
            $weeks = AppHelper::Weeks();
            $startdatenow = $weeks['startweek'];
            $enddatenow = $weeks['endweek'];

            $week = $startdate ."-".$enddate;
            $weeknow = $startdatenow ."-".$enddatenow;
            $data=[];
            if ($week==$weeknow){
                $company = Company::select(DB::raw("cast(mc_id as varchar(5))"))->where('mc_level',1)->get();

                foreach($company as $itemcompany) {

                    $company_id = (string)$itemcompany->mc_id;
                    //dd($itemcompany->mc_id);
                    $sql = "SELECT
                        a.v_mc_id,
                        a.v_mc_name,
                        a.v_ms_id,
                        a.v_ms_name,
                        a.v_cosmic_index,
                        a.v_pemenuhan_protokol,
                        a.v_pemenuhan_ceklist_monitoring,
                        a.v_pemenuhan_eviden
                        FROM week_cosmic_index(?, ?) a
                        GROUP BY
                        a.v_mc_id,
                        a.v_mc_name,
                        a.v_ms_id,
                        a.v_ms_name,
                        a.v_cosmic_index,
                        a.v_pemenuhan_protokol,
                        a.v_pemenuhan_ceklist_monitoring,
                        a.v_pemenuhan_eviden
                        ";
                    //echo $sql;die;
                    $result = DB::select($sql, [(string)$company_id, (string)$enddate]);
                    //dd($result);
                    foreach ($result as $value) {
                        $data[] = array(
                            "week" =>  $week,
                            "mc_id" => $value->v_mc_id,
                            "mc_name" => $value->v_mc_name,
                            "ms_id" => $value->v_ms_id,
                            "ms_name" => $value->v_ms_name,
                            "cosmic_index" => $value->v_cosmic_index,
                            "pemenuhan_protokol" => $value->v_pemenuhan_protokol,
                            "pemenuhan_ceklist_monitoring" => $value->v_pemenuhan_ceklist_monitoring,
                            "pemenuhan_eviden" => $value->v_pemenuhan_eviden

                        );
                    }
                }
            } else {
                $rpi = DB::select("SELECT *
                        FROM report_cosmic_index rpi
                        WHERE rci_week = ?
                        ORDER BY rci_mc_name",[(string)$week]);

                foreach($rpi as $itemrpi){
                    $data[] = array(
                        "week" =>  $week,
                        "mc_id" => $itemrpi->rci_mc_id,
                        "mc_name" => $itemrpi->rci_mc_name,
                        "ms_id" => $itemrpi->rci_ms_id,
                        "ms_name" => $itemrpi->rci_ms_name,
                        "cosmic_index" => $itemrpi->rci_cosmic_index,
                        "pemenuhan_protokol" => $itemrpi->rci_pemenuhan_protokol,
                        "pemenuhan_ceklist_monitoring" => $itemrpi->rci_pemenuhan_ceklist_monitoring,
                        "pemenuhan_eviden" => $itemrpi->rci_pemenuhan_eviden,

                    );
                }
            }

            return $data;
        });
        return response()->json(['status' => 200,'data' => $datacache]);
    }

    public function getCosmicIndexbyCompanyAndDate($kd_perusahaan,Request $request){
        $str = '_get_cosmic_index_'.$kd_perusahaan;
        $mc_id = $kd_perusahaan;
        if(isset($request->date)){
            $strdate =  Carbon::parse($request->date);
            //  dd($date);
            $startdate = $strdate->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            $enddate = $strdate->endOfWeek(Carbon::FRIDAY)->format('Y-m-d');
            $str = $str."_".$startdate."_".$enddate;
        } else {
            $crweeks = AppHelper::Weeks();
            $startdate = $crweeks['startweek'];
            $enddate = $crweeks['endweek'];
            $str = $str."_".$startdate."_".$enddate;
        }

        $datacache =  Cache::remember(env('APP_ENV', 'dev').$str, 360 * 60, function()use($startdate,$enddate,$mc_id) {
            $data = array();
            $weeks = AppHelper::Weeks();
            $startdatenow = $weeks['startweek'];
            $enddatenow = $weeks['endweek'];

            $week = $startdate ."-".$enddate;
            $weeknow = $startdatenow ."-".$enddatenow;
            $data=[];
            $company_id = $mc_id;
            if ($week==$weeknow){


                    //dd($itemcompany->mc_id);
                    $sql = "SELECT
                        a.v_mc_id,
                        a.v_mc_name,
                        a.v_ms_id,
                        a.v_ms_name,
                        a.v_cosmic_index,
                        a.v_pemenuhan_protokol,
                        a.v_pemenuhan_ceklist_monitoring,
                        a.v_pemenuhan_eviden
                        FROM week_cosmic_index(?, ?) a
                        GROUP BY
                        a.v_mc_id,
                        a.v_mc_name,
                        a.v_ms_id,
                        a.v_ms_name,
                        a.v_cosmic_index,
                        a.v_pemenuhan_protokol,
                        a.v_pemenuhan_ceklist_monitoring,
                        a.v_pemenuhan_eviden
                        ";
                    //echo $sql;die;
                    $result = DB::select($sql, [(string)$company_id, (string)$enddate]);
                    //dd($result);
                    foreach ($result as $value) {
                        $data = array(
                            "week" =>  $week,
                            "mc_id" => $value->v_mc_id,
                            "mc_name" => $value->v_mc_name,
                            "ms_id" => $value->v_ms_id,
                            "ms_name" => $value->v_ms_name,
                            "cosmic_index" => $value->v_cosmic_index,
                            "pemenuhan_protokol" => $value->v_pemenuhan_protokol,
                            "pemenuhan_ceklist_monitoring" => $value->v_pemenuhan_ceklist_monitoring,
                            "pemenuhan_eviden" => $value->v_pemenuhan_eviden

                        );
                    }

            } else {
                $rpi = DB::select("SELECT *
                        FROM report_cosmic_index rpi
                        WHERE rci_week = ? and rci_mc_id = ?
                        ORDER BY rci_id limit 1",[(string)$week,(string)$company_id]);

                foreach($rpi as $itemrpi){
                    $data = array(
                        "week" =>  $week,
                        "mc_id" => $itemrpi->rci_mc_id,
                        "mc_name" => $itemrpi->rci_mc_name,
                        "ms_id" => $itemrpi->rci_ms_id,
                        "ms_name" => $itemrpi->rci_ms_name,
                        "cosmic_index" => $itemrpi->rci_cosmic_index,
                        "pemenuhan_protokol" => $itemrpi->rci_pemenuhan_protokol,
                        "pemenuhan_ceklist_monitoring" => $itemrpi->rci_pemenuhan_ceklist_monitoring,
                        "pemenuhan_eviden" => $itemrpi->rci_pemenuhan_eviden,

                    );
                }
            }

            return $data;
        });
        return response()->json(['status' => 200,'data' => $datacache]);
    }

    public function getCosmicIndexListbyCompany($kd_perusahaan){
        $str = '_get_cosmic_index_detail_list_'.$kd_perusahaan;
        $mc_id = $kd_perusahaan;


        $datacache =  Cache::remember(env('APP_ENV', 'dev').$str, 360 * 60, function()use($mc_id) {
            $data = array();
            $weeks = AppHelper::Weeks();
            $startdatenow = $weeks['startweek'];
            $enddatenow = $weeks['endweek'];

            $weeknow = $startdatenow ."-".$enddatenow;
            $data=[];
            $weeksday =  DB::select("SELECT * , CONCAT(v_awal,' s/d ', v_akhir) tgl
                  FROM list_aktivitas_week()
                  ORDER BY v_rownum DESC");

              $company_id = $mc_id;
              $perimeter = DB::select('select * from master_perimeter_level mpml
              join master_perimeter mpm on mpm.mpm_id = mpml.mpml_mpm_id
              where mpm.mpm_mc_id = ? and mpml.mpml_id in (select tpmd_mpml_id from table_perimeter_detail)',[$company_id]);
              $jml = count($perimeter);

              $ceknow = DB::select("SELECT *
                      FROM report_cosmic_index rpi
                      WHERE rci_week = ? and rci_mc_id = ?
                      ORDER BY rci_week asc limit 1 ",[(string)$weeknow,(string)$company_id]);


              $rpi = DB::select("SELECT *
                      FROM report_cosmic_index rpi
                      WHERE rci_mc_id = ?
                      ORDER BY rci_week asc ",[(string)$company_id]);

              foreach($rpi as $itemrpi){
                foreach ($weeksday as $itemweeksday){
                  if($itemweeksday->v_week==$itemrpi->rci_week){
                    $data[] = array(
                        "week" =>  $itemrpi->rci_week,
                          "weekname" =>  "Week ".$itemweeksday->v_rownum." ( ".$itemweeksday->tgl." )",
                        "mc_id" => $itemrpi->rci_mc_id,
                        "mc_name" => $itemrpi->rci_mc_name,
                        "ms_id" => $itemrpi->rci_ms_id,
                        "ms_name" => $itemrpi->rci_ms_name,
                        "cosmic_index" => $itemrpi->rci_cosmic_index,
                        "pemenuhan_protokol" => $itemrpi->rci_pemenuhan_protokol,
                        "pemenuhan_ceklist_monitoring" => $itemrpi->rci_pemenuhan_ceklist_monitoring,
                        "pemenuhan_eviden" => $itemrpi->rci_pemenuhan_eviden,
                        "jumlah_perimeter" => $itemrpi->rci_jml_perimeter,

                    );
                  }
                }

              }
              if ($ceknow==null){

                      $sql = "SELECT
                          a.v_mc_id,
                          a.v_mc_name,
                          a.v_ms_id,
                          a.v_ms_name,
                          a.v_cosmic_index,
                          a.v_pemenuhan_protokol,
                          a.v_pemenuhan_ceklist_monitoring,
                          a.v_pemenuhan_eviden
                          FROM week_cosmic_index(?, ?) a
                          GROUP BY
                          a.v_mc_id,
                          a.v_mc_name,
                          a.v_ms_id,
                          a.v_ms_name,
                          a.v_cosmic_index,
                          a.v_pemenuhan_protokol,
                          a.v_pemenuhan_ceklist_monitoring,
                          a.v_pemenuhan_eviden
                          ";
                      //echo $sql;die;
                      $result = DB::select($sql, [(string)$company_id, (string)$enddatenow]);
                      //dd($result);
                      foreach ($result as $value) {
                        foreach ($weeksday as $itemweeksday){
                          if($itemweeksday->v_week==$weeknow){
                            $data[] = array(
                                "week" =>  $weeknow,
                                "weekname" =>  "Week ".$itemweeksday->v_rownum." ( ".$itemweeksday->tgl." )",
                                "mc_id" => $value->v_mc_id,
                                "mc_name" => $value->v_mc_name,
                                "ms_id" => $value->v_ms_id,
                                "ms_name" => $value->v_ms_name,
                                "cosmic_index" => $value->v_cosmic_index,
                                "pemenuhan_protokol" => $value->v_pemenuhan_protokol,
                                "pemenuhan_ceklist_monitoring" => $value->v_pemenuhan_ceklist_monitoring,
                                "pemenuhan_eviden" => $value->v_pemenuhan_eviden,
                                "jumlah_perimeter" => $jml

                            );
                          }
                        }

                      }

              }



            return $data;
        });
        return response()->json(['status' => 200,'data' => $datacache]);
    }
}
