<?php

namespace App\Http\Controllers;
use App\Vaksin;
use App\Company;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;

use DB;

class DashVaksinController extends Controller
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


	public function getDashVaksin(Request $request){
	    $query_level = ' AND mav.v_mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	    }else{
	        $level = 0;
	    }

	    $query_mc_id = ' ';
	    if(isset($request->kd_perusahaan)) {
            $mc_id = $request->kd_perusahaan;
	    }else{
            $mc_id ='ALL';
	    }

	    $query_lansia_id = ' ';
        if(isset($request->lansia) && $request->lansia!='ALL'){
            $lansia = $request->lansia;
        }else{
            $lansia ='ALL';
        }

        $query_msp = ' ';
        if(isset($request->sts_pegawai) && $request->sts_pegawai!='ALL'){
            $sts_pegawai = $request->sts_pegawai;
        }else{
            $sts_pegawai ='ALL';
        }

        $query_msv = ' ';
        if(isset($request->sts_vaksin) && $request->sts_vaksin!='ALL'){
            $sts_vaksin = $request->sts_vaksin;
        }else{
            $sts_vaksin ='ALL';
        }

        $query_mkab = ' ';
        if(isset($request->kabupaten) && $request->kabupaten!='0'){
            $kabupaten = $request->kabupaten;
        }else{
            $kabupaten ='0';
        }

        $string = "_get_dashvaksinhead_".$level.'_'.$mc_id.'_'.$lansia.'_'.$sts_pegawai.'_'.$sts_vaksin.'_'.$kabupaten;
        $datacache = Cache::tags(['users'])->remember(env('APP_ENV', 'dev').$string, 60, function () use($level,
            $mc_id, $lansia, $sts_pegawai, $sts_vaksin, $kabupaten) {

	        if($level > 0){
	            $query_level = ' AND mav.v_mc_level='.$level;
	        }else{
	            $query_level = ' AND mav.v_mc_level IN (1,2,3) ';
	        }

	        if($mc_id!='ALL'){
	            if(isset($request->level) && $request->level>1){
	                $query_mc_id = " AND mc.mc_id = '$mc_id' ";
	            }else{
	                $query_mc_id = " AND mc.mc_id_induk = '$mc_id' ";
	            }
	        }else{
	            $query_mc_id = " ";
	        }

	        if($lansia!='ALL'){
	            if(isset($request->lansia) && $request->lansia!='ALL'){
	                $query_lansia = " AND mav.v_is_lansia = $lansia ";
	            }else{
	                $query_lansia = " ";
	            }
	        }else{
	            $query_lansia = " ";
	        }

	        if($sts_pegawai!='ALL'){
	            if(isset($request->sts_pegawai) && $request->sts_pegawai!='ALL'){
	                $query_stspegawai = " AND mav.v_msp_id = $sts_pegawai ";
	            }else{
	                $query_stspegawai = " ";
	            }
	        }else{
	            $query_stspegawai = " ";
	        }

	        if($sts_vaksin!='ALL'){
	            if(isset($request->sts_vaksin) && $request->sts_vaksin!='ALL'){
	                $query_stsvaksin = " AND mav.v_status_vaksin_pcare = $sts_vaksin ";
	            }else{
	                $query_stsvaksin = " ";
	            }
	        }else{
	            $query_stsvaksin = " ";
	        }

	        if($kabupaten!='0'){
	            if(isset($request->kabupaten) && $request->kabupaten!='0'){
	                $query_kabupaten = " AND mav.v_mkab_id = $kabupaten ";
	            }else{
	                $query_kabupaten = " ";
	            }
	        }else{
	            $query_kabupaten = " ";
	        }

    	    $data = array();
            $query = "
                SELECT 0::int2, 'Total Pegawai' judul,
                    COALESCE(SUM(v_jml_pegawai),0) AS jml
                FROM mvt_admin_vaksin mav
                INNER JOIN master_company mc ON mc.mc_id=mav.v_mc_id
                WHERE 1=1
                $query_level
                $query_lansia
                $query_mc_id
                $query_stspegawai
                $query_stsvaksin
                $query_kabupaten
                UNION ALL
                SELECT 1::int2, 'Total Siap Vaksin' judul,
                    COALESCE(SUM(v_jml_siap_vaksin),0) AS jml
                FROM mvt_admin_vaksin mav
                INNER JOIN master_company mc ON mc.mc_id=mav.v_mc_id
                WHERE 1=1
                $query_level
                $query_lansia
                $query_mc_id
                $query_stspegawai
                $query_stsvaksin
                $query_kabupaten
                UNION ALL
                SELECT 2::int2, 'Total Sudah Vaksin 1' judul,
                    COALESCE(SUM(v_jml_sudah_vaksin1),0) AS jml
                FROM mvt_admin_vaksin mav
                INNER JOIN master_company mc ON mc.mc_id=mav.v_mc_id
                WHERE 1=1
                $query_level
                $query_lansia
                $query_mc_id
                $query_stspegawai
                $query_stsvaksin
                $query_kabupaten
                UNION ALL
                SELECT 3::int2, 'Total Sudah Vaksin 2' judul,
                    COALESCE(SUM(v_jml_sudah_vaksin1),0) AS jml
                FROM mvt_admin_vaksin mav
                INNER JOIN master_company mc ON mc.mc_id=mav.v_mc_id
                WHERE 1=1
                $query_level
                $query_lansia
                $query_mc_id
                $query_stspegawai
                $query_stsvaksin
                $query_kabupaten
                UNION ALL
                SELECT 4::int2, 'Jumlah Keluarga Inti Pegawai' judul,
                    COALESCE(SUM(v_jml_keluargainti),0) AS jml
                FROM mvt_admin_vaksin mav
                INNER JOIN master_company mc ON mc.mc_id=mav.v_mc_id
                WHERE 1=1
                $query_level
                $query_lansia
                $query_mc_id
                $query_stspegawai
                $query_stsvaksin
                $query_kabupaten
                ";

                //var_dump($query);die;
                $dashvaksin = DB::connection('pgsql_vaksin')->select($query);
                foreach($dashvaksin as $dv){
                    $data[] = array(
                        "v_judul" => $dv->judul,
                        "v_jml" => number_format($dv->jml,0,".",",")
                    );
                }
           return $data;
	    });
        Cache::tags(['users'])->flush();
        return response()->json(['status' => 200,'data' => $datacache]);
        //return response()->json(['status' => 200,'data' => $data]);
	}

	public function getDashVaksin_bymcid($id){
	    $data = array();
	    $dashvaksin = DB::connection('pgsql_vaksin')->select("SELECT * FROM vaksin_summary_bymcid('$id')");

	    foreach($dashvaksin as $dv){
    	        $data[] = array(
    	            "v_judul" => $dv->v_judul,
    	            "v_jml" => number_format($dv->v_jml,0,".",",")
    	        );
    	    }
	    return response()->json(['status' => 200,'data' => $data]);
	}

	public function getDashVaksinPerusahaan(Request $request){
	    $query_level = ' AND mav.v_mc_level IN (1,2,3) ';
      $page=null;
      $limit=null;
      $search=null;
      $sort=null;
      $col=null;
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;

	    }else{
	        $level = 0;
	    }

	    $query_mc_id = ' ';
	    if(isset($request->kd_perusahaan)) {
	        if(isset($request->level) && $request->level>1){
	            $mc_id = $request->kd_perusahaan;
	        }else{
	            $mc_id = $request->kd_perusahaan;
	        }
	    }else{
	        $mc_id ='ALL';
	    }

	    $query_lansia_id = ' ';
	    if(isset($request->lansia)) {
	        if(isset($request->lansia) && $request->lansia!='ALL'){
	            $lansia = $request->lansia;
	        }else{
	            $lansia = $request->lansia;
	        }
	    }else{
	        $lansia ='ALL';
	    }

      $query_msp = ' ';
      if(isset($request->sts_pegawai) && $request->sts_pegawai!='ALL'){
          $sts_pegawai = $request->sts_pegawai;
      }else{
          $sts_pegawai ='ALL';
      }

      $query_msv = ' ';
      if(isset($request->sts_vaksin) && $request->sts_vaksin!='ALL'){
          $sts_vaksin = $request->sts_vaksin;
      }else{
          $sts_vaksin ='ALL';
      }

      $str='';
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

      if(isset($request->p_sort)){
          $str = $str.'_sort_'. str_replace(' ','_',$request->p_sort);
          $sort=$request->p_sort;
      }

      if(isset($request->column_sort)){
          $str = $str.'_col_'. str_replace(' ','_',$request->column_sort);
          $col=$request->column_sort;
      }




	    $string = "_get_dashvaksin_byperusahaan_".$level.'_'.$mc_id.'_'.$lansia.'_'.$mc_id.'_'.$lansia.'_'.$sts_pegawai.'_'.$sts_vaksin.$str;
	    $datacache = Cache::tags(['users'])->remember(env('APP_ENV', 'dev').$string, 60, function () use($level, $mc_id, $lansia,$sts_pegawai,$sts_vaksin,$limit,$page,$search,$sort,$col) {
        $query_search='';
	        if($level > 0){
	            $query_level = ' AND mav.v_mc_level='.$level;
	            $query_level1 = ' AND mc1.mc_level='.$level;
	        }else{
	            $query_level = ' AND mav.v_mc_level IN (1,2,3) ';
	            $query_level1 = ' AND mc1.mc_level IN (1,2,3) ';
	        }

	        if($mc_id!='ALL'){
	            if(isset($request->level) && $request->level>1){
	                $query_mc_id = " AND mc.mc_id = '$mc_id' ";
	            }else{
	                $query_mc_id = " AND mc.mc_id_induk = '$mc_id' ";
	            }
	        }else{
	            $query_mc_id = " ";
	        }

	        if($lansia!='ALL'){
	            if(isset($request->lansia) && $request->lansia!='ALL'){
	                $query_lansia = " AND mav.v_is_lansia = $lansia ";
	            }else{
	                $query_lansia = " ";
	            }
	        }else{
	            $query_lansia = " ";
	        }

          if($sts_pegawai!='ALL'){
              if(isset($request->sts_pegawai) && $request->sts_pegawai!='ALL'){
                  $query_stspegawai = " AND mav.v_msp_id = $sts_pegawai ";
              }else{
                  $query_stspegawai = " ";
              }
          }else{
              $query_stspegawai = " ";
          }

          if($sts_vaksin!='ALL'){
              if(isset($request->sts_vaksin) && $request->sts_vaksin!='ALL'){
                  $query_stsvaksin = " AND mav.v_status_vaksin_pcare = $sts_vaksin ";
              }else{
                  $query_stsvaksin = " ";
              }
          }else{
              $query_stsvaksin = " ";
          }
          if(isset($search)) {
              $query_search = " AND (lower(TRIM(mc1.mc_name)) like '%".strtolower(trim($search))."%' or lower(TRIM(mc1.mc_id)) like '%".strtolower(trim($search))."%')";
          }

          //order by / sort
            if(isset($col)) {
                  if(isset($sort)) {
                      $query_sort = ' ORDER BY '.$col.' '.$sort;
                  }else{
                      $query_sort = ' ORDER BY '.$col.' DESC';
                  }
              }else{
                  $query_sort = ' ORDER BY mc_name';
              }


    	    $data = array();
    	    $query = "SELECT mc1.mc_id, mc1.mc_name,
    					(SELECT COALESCE(SUM(v_jml_pegawai),0)
                        FROM mvt_admin_vaksin mav
                        INNER JOIN master_company mc ON mc.mc_id=mav.v_mc_id
        				WHERE mc.mc_id=mc1.mc_id
                        $query_level
                        $query_lansia
                        $query_mc_id
                        $query_stspegawai
                        $query_stsvaksin) AS jml
    				FROM master_company mc1
    				WHERE mc1.mc_flag=1
                    $query_level1
                    $query_search
    				        $query_sort ";
    	    $querycount = "SELECT count(*)
    				FROM master_company mc1
    				WHERE mc1.mc_flag=1
                    $query_level1
                    $query_search ";
            $cnt = DB::connection('pgsql_vaksin')->select($querycount);
            $jmltotal=$cnt[0]->count;
            $endpage=0;
            if(isset($limit)) {
                $query = $query." limit ".$limit;
                $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

                if (isset($page)) {
                    $offset = ((int)$page -1) * (int)$limit;
                    $query = $query." offset ".$offset;
                }
            }
                    //var_dump($query);die;
    		$dashvaksin_perusahaan = DB::connection('pgsql_vaksin')->select($query);
    	    foreach($dashvaksin_perusahaan as $dvp){
    	        $data[] = array(
    	            "v_mc_id" => $dvp->mc_id,
    	            "v_mc_name" => $dvp->mc_name,
    	            "v_jml" => number_format($dvp->jml,0,".",",")
    	        );
    	    }
            return array('data'=>$data,'page_end'=>$endpage);
	    });
        Cache::tags(['users'])->flush();
        return response()->json(['status' => 200,'page_end'=> $datacache['page_end'],'data' => $datacache['data']]);
	}

	public function getDashVaksinPerusahaanFilter(Request $request){
		$limit = null;
        $page = null;
        $endpage = 1;

        if(isset($request->limit)){
            // $str = $str.'_limit_'. $request->limit;
            $limit=$request->limit;
            if(isset($request->page)){
                // $str = $str.'_page_'. $request->page;
                $page=$request->page;
            }
        }

		 $filter_perusahaan = $request->status_perusahaan;
		 $filter_pegawai = $request->status_pegawai;
		 $filter_name = $request->nama_perusahaan;
		/*$filter_perusahaan = $sp1;
		$filter_pegawai = $sp2;*/
		$w1 = " AND mc_level IN (1,2,3)";
		if(!empty($filter_perusahaan)){
			$w1 = " AND mc_level = '$filter_perusahaan'";
		}

		$w2 = " ";
		if(!empty($filter_pegawai)){
			$w2 = " AND tv.tv_msp_id = '$filter_pegawai'";
		}

		$w3 = " ";
		if(!empty($filter_name)){
			$w3 = " AND mc1.mc_name LIKE '%$filter_name%'";
		}

		$string = "SELECT mc_id, mc_name,
				(SELECT COUNT(*)
				FROM transaksi_vaksin tv
				INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
				--INNER JOIN master_sektor ms ON ms.ms_id=mc.mc_msc_id
				WHERE
				--WHERE mc.mc_level IN (0,1,2,3,9)
				--AND ms.ms_type = 'CCOVID'
				tv.tv_mc_id=mc.mc_id
				$w2
				AND is_lansia=0
				AND mc.mc_id=mc1.mc_id

				) jml
				FROM master_company mc1
				where 1=1
				$w1
				$w3
				";

		$string_count = "SELECT count(*) count
				FROM master_company mc1
				where 1=1
				$w1
				$w3";

	    //$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashvaksin_perusahaan", 15 * 60, function() {
	    $data = array();
	    $count = DB::connection('pgsql_vaksin')->select($string_count);

	    $jmltotal=$count[0]->count;
            // dd($jmltotal);

	    //order by / sort
	      if(isset($request->column_sort)) {
              if(isset($request->p_sort)) {
                  $sql_sort = ' ORDER BY '.$request->column_sort.' '.$request->p_sort;
              }else{
                  $sql_sort = ' ORDER BY '.$request->column_sort.' DESC';
              }
          }else{
              $sql_sort = ' ORDER BY mc_name';
          }
          	$string .= $sql_sort;

            if(isset($request->limit)) {
                $limit = $request->limit;
                $sql_limit = ' LIMIT '.$request->limit;
                $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

                $string .= $sql_limit;

                if (isset($request->page)) {
                    $page = $request->page;
                    $offset = ((int)$page-1) * (int)$limit;
                    $sql_offset= ' OFFSET '.$offset;

                    $string .= $sql_offset;
                }
            }
	    $get_data = DB::connection('pgsql_vaksin')->select($string);
	    foreach($get_data as $dvp){
    	        $data[] = array(
    	            "v_mc_id" => $dvp->mc_id,
    	            "v_mc_name" => $dvp->mc_name,
    	            "v_jml" => number_format($dvp->jml,0,".",",")
    	        );
    	    }
	    //});
	    return response()->json(['status' => 200,'page_end' =>$endpage,'data' => $data]);
	}


	public function getDashVaksinPegawaiFilter(Request $request){
		$limit = null;
        $page = null;
        $endpage = 10;
        $filter_nama = strtoupper($request->name);
		$filter_mc_id = $request->mc_id;
		$filter_status = $request->status;
		$filter_status_vaksin = $request->status_vaksin;

        $str = "_get_dashvaksin_pegawai".$filter_mc_id.$filter_nama.$filter_status_vaksin;
        if(isset($request->limit)){
            $str = $str.'_limit_'. $request->limit;
            $limit=$request->limit;
            if(isset($request->page)){
                $str = $str.'_page_'. $request->page;
                $page=$request->page;
            }
        }

      	if(isset($request->name)){
            $str = $str.'_search_'. str_replace(' ','_',$request->name);
            $filter_nama = strtoupper($request->name);
        }
        if(isset($request->status)){
            $str = $str.'_status_'. str_replace(' ','_',$request->status);
            $filter_status=$request->status;
        }
        if(isset($request->status_vaksin)){
            $str = $str.'_status_'. str_replace(' ','_',$request->status_vaksin);
            $filter_status_vaksin=$request->status_vaksin;
        }


      	//dd($string);
        $datacache = Cache::tags([$str])->remember(env('APP_ENV', 'dev').$str, 5 * 10, function () use($filter_nama, $filter_mc_id, $filter_status, $filter_status_vaksin, $limit, $page, $endpage) {
		/*$datacache = Cache::remember(env('APP_ENV', 'dev').$str, 5 * 10, function()use($filter_nama, $filter_mc_id, $filter_status, $limit, $page, $endpage) {*/
	        $data = array();

		    $vaksin = new Vaksin();
		    $vaksin->setConnection('pgsql_vaksin');
		    $vaksin = $vaksin->select('tv_nik', 'tv_nama', 'msp_name2', 'tv_file1','tv_mc_id')
	        ->leftjoin('master_status_pegawai AS msp','msp.msp_id','tv_msp_id')
	        ->leftjoin('master_status_vaksin AS msv','msv.msv_id','tv_status_vaksin_pcare')
	        ->where('tv_mc_id', $filter_mc_id);
	        //->where('is_lansia', 0);

	        if(!empty($filter_nama)) {
	            $search = $filter_nama;
	            $vaksin = $vaksin->where(DB::raw("lower(TRIM(tv_nama))"),'like','%'.strtolower(trim($search)).'%');
	        }

	        if(!empty($filter_status)) {
	            $vaksin = $vaksin->where(DB::raw("tv_msp_id"),'=',trim($filter_status));
	        }

	        if(!empty($filter_status_vaksin)) {
	            $vaksin = $vaksin->where(DB::raw("tv_status_vaksin_pcare"),'=',trim($filter_status_vaksin));
	        }

	        $jmltotal=($vaksin->count());
	        if(isset($limit)) {
	            $vaksin = $vaksin->limit($limit);
	            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));
	        	//dd($endpage);

	            if (!empty($page)) {
	                $offset = ((int)$page -1) * (int)$limit;
	                $vaksin = $vaksin->offset($offset);
	            }
	        }


		    $vaksin = $vaksin->get();
	        foreach($vaksin as $dvp){
	        	if($dvp->tv_file1 !=NULL || $dvp->tv_file1 !=''){
                    if (!file_exists(base_path("storage/app/public/vaksin_eviden/".$dvp->tv_mc_id.'/'.$dvp->tv_file1))) {
                        $path_file404 = '/404/img404.jpg';
                        $filevksn1 = $path_file404;
                    }else{
                        $path_file1 = '/vaksin_eviden/'.$dvp->tv_file1;
                        $filevksn1 = $path_file1;
                    }
                }else{
                    $filevksn1 = '/404/img404.jpg';
                }

    	        $data[] = array(
    	            "nik" => $dvp->tv_nik,
    	            "nama" => $dvp->tv_nama,
    	            "status" => $dvp->msp_name2,
    	            "photo" => $filevksn1
    	        );
    	    }
	    	//return $data;
		    return array('status' => 200,'page_end' =>$endpage,'data' => $data);
		});
		    Cache::tags([$str])->flush();
	    return response()->json($datacache);
	}

	public function getDashVaksinProvinsi(Request $request){
	    $query_level = ' AND mav.v_mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	    }else{
	        $level = 0;
	    }

	    $query_mc_id = ' ';
	    if(isset($request->kd_perusahaan)) {
	        $mc_id = $request->kd_perusahaan;
	    }else{
	        $mc_id ='ALL';
	    }

	    $query_lansia_id = ' ';
	    if(isset($request->lansia) && $request->lansia!='ALL'){
	        $lansia = $request->lansia;
	    }else{
	        $lansia ='ALL';
	    }

	    $string = "_get_dashvaksin_byprovinsi_".$level.'_'.$mc_id.'_'.$lansia;
	    $datacache = Cache::tags(['users'])->remember(env('APP_ENV', 'dev').$string, 60, function () use($level, $mc_id, $lansia) {
	        if($level > 0){
	            $query_level = ' AND mav.v_mc_level='.$level;
	        }else{
	            $query_level = ' AND mav.v_mc_level IN (1,2,3) ';
	        }

	        if($mc_id!='ALL'){
	            if(isset($request->kd_perusahaan) && $request->kd_perusahaan!='ALL'){
	                $query_mc_id = " AND mc.mc_id = '$mc_id' ";
	            }else{
	                $query_mc_id = "  ";
	            }
	        }else{
	            $query_mc_id = " ";
	        }

	        if($lansia!='ALL'){
	            if(isset($request->lansia) && $request->lansia!='ALL'){
	                $query_lansia = " AND mav.v_is_lansia = $lansia ";
	            }else{
	                $query_lansia = "  ";
	            }
	        }else{
	            $query_lansia = " ";
	        }


    	    $data = array();
    	    $query = "SELECT mpro.mpro_id, mpro.mpro_name::TEXT,
        		(SELECT COALESCE(SUM(v_jml_pegawai),0)
                    FROM mvt_admin_vaksin mav
                    INNER JOIN master_company mc ON mc.mc_id=mav.v_mc_id
    				WHERE mav.v_mpro_id=mpro.mpro_id
                    $query_level
                    $query_lansia
                    $query_mc_id)::int8 AS jml
        		FROM master_provinsi mpro
        		ORDER BY mpro.mpro_id";
                //var_dump($query);die;
    		$dashkasus_provinsi = DB::connection('pgsql_vaksin')->select($query);
    	    foreach($dashkasus_provinsi as $dvp){
    	        $data[] = array(
    	            "v_mpro_id" => $dvp->mpro_id,
    	            "v_mpro" => $dvp->mpro_name,
    	            "v_jml" => number_format($dvp->jml,0,".",",")
    	        );
    	    }
    	    return $data;
	    });
        Cache::tags(['users'])->flush();
        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getDashVaksinKabupaten(Request $request){
	    $query_level = ' AND mav.v_mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	    }else{
	        $level = 0;
	    }

	    $query_mc_id = ' ';
	    if(isset($request->kd_perusahaan)) {
	        $mc_id = $request->kd_perusahaan;
	    }else{
	        $mc_id ='ALL';
	    }

	    $query_lansia_id = ' ';
	    if(isset($request->lansia) && $request->lansia!='ALL'){
	        $lansia = $request->lansia;
	    }else{
	        $lansia ='ALL';
	    }

	    $string = "_get_dashvaksin_bykabupaten_".$level.'_'.$mc_id.'_'.$lansia;
	    $datacache = Cache::tags(['users'])->remember(env('APP_ENV', 'dev').$string, 60, function () use($level, $mc_id, $lansia) {
	        if($level > 0){
	            $query_level = ' AND mav.v_mc_level='.$level;
	        }else{
	            $query_level = ' AND mav.v_mc_level IN (1,2,3) ';
	        }

	        if($mc_id!='ALL'){
	            if(isset($request->level) && $request->level>1){
	                $query_mc_id = " AND mc.mc_id = '$mc_id' ";
	            }else{
	                $query_mc_id = " AND mc.mc_id_induk = '$mc_id' ";
	            }
	        }else{
	            $query_mc_id = " ";
	        }

	        if($lansia!='ALL'){
	            if(isset($request->lansia) && $request->lansia!='ALL'){
	                $query_lansia = " AND mav.v_is_lansia = $lansia ";
	            }else{
	                $query_lansia = "  ";
	            }
	        }else{
	            $query_lansia = " ";
	        }

    	    $data = array();
    	    $query = "
                SELECT mkab_id, mkab.mkab_name::TEXT,
    				(SELECT COALESCE(SUM(v_jml_pegawai),0)
                    FROM mvt_admin_vaksin mav
                    INNER JOIN master_company mc ON mc.mc_id=mav.v_mc_id
    				WHERE mav.v_mkab_id=mkab.mkab_id
                    $query_level
                    $query_lansia
                    $query_mc_id)::int8 AS jml
				FROM master_kabupaten mkab
				ORDER BY mkab.mkab_name ";
                    //var_dump($query);die;
    		$dashkasus_kabupaten = DB::connection('pgsql_vaksin')->select($query);
    	    foreach($dashkasus_kabupaten as $dvk){
    	        $data[] = array(
    	            "v_mkab_id" => $dvk->mkab_id,
    	            "v_mkab" => $dvk->mkab_name,
    	            "v_jml" => number_format($dvk->jml,0,".",",")
    	        );
    	    }
    	    return $data;
	    });
        Cache::tags(['users'])->flush();
        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getDashVaksinLokasi1(Request $request){
	    $query_level = ' AND mav.v_mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	    }else{
	        $level = 0;
	    }

	    $query_mc_id = ' ';
	    if(isset($request->kd_perusahaan)) {
	        $mc_id = $request->kd_perusahaan;
	    }else{
	        $mc_id ='ALL';
	    }

	    $query_lansia_id = ' ';
	    if(isset($request->lansia) && $request->lansia!='ALL'){
	        $lansia = $request->lansia;
	    }else{
	        $lansia ='ALL';
	    }

	    $string = "_get_dashvaksin_bylokasi1_".$level.'_'.$mc_id.'_'.$lansia;
	    $datacache = Cache::tags(['users'])->remember(env('APP_ENV', 'dev').$string, 60, function () use($level, $mc_id, $lansia) {
	        if($level > 0){
	            $query_level = ' AND mc.mc_level='.$level;
	        }else{
	            $query_level = ' AND mc.mc_level IN (1,2,3) ';
	        }

	        if($mc_id!='ALL'){
	            if(isset($request->level) && $request->level>1){
	                $query_mc_id = " AND mc.mc_id = '$mc_id' ";
	            }else{
	                $query_mc_id = " AND mc.mc_id_induk = '$mc_id' ";
	            }
	        }else{
	            $query_mc_id = " ";
	        }

	        if($lansia!='ALL'){
	            if(isset($request->lansia) && $request->lansia!='ALL'){
	                $query_lansia = " AND tv.is_lansia = $lansia ";
	            }else{
	                $query_lansia = " ";
	            }
	        }else{
	            $query_lansia = " ";
	        }

    	    $data = array();
    	    $query = "SELECT tv.tv_lokasi_vaksin_pcare1::TEXT, COALESCE(COUNT(*))::int8 AS jml
				FROM transaksi_vaksin tv
				INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
				WHERE mc.mc_flag=1
				$query_level
				$query_lansia
				$query_mc_id
				AND (tv_lokasi_vaksin_pcare1 !=NULL or tv_lokasi_vaksin_pcare1 !='')
				GROUP BY tv.tv_lokasi_vaksin_pcare1
				ORDER BY tv.tv_lokasi_vaksin_pcare1";

    		$dashvaksin_lokasi1 = DB::connection('pgsql_vaksin')->select($query);
    	    foreach($dashvaksin_lokasi1 as $dl1){
    	        $data[] = array(
    	            "v_lokasi" => $dl1->tv_lokasi_vaksin_pcare1,
    	            "v_jml" => number_format($dl1->jml,0,".",",")
    	        );
    	    }
    	    return $data;
	    });
        Cache::tags(['users'])->flush();
        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getDashVaksinLokasi2(Request $request){
	    $query_level = ' AND mav.v_mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	    }else{
	        $level = 0;
	    }

	    $query_mc_id = ' ';
	    if(isset($request->kd_perusahaan)) {
	        $mc_id = $request->kd_perusahaan;
	    }else{
	        $mc_id ='ALL';
	    }

	    $query_lansia_id = ' ';
	    if(isset($request->lansia) && $request->lansia!='ALL'){
	        $lansia = $request->lansia;
	    }else{
	        $lansia ='ALL';
	    }

	    $string = "_get_dashvaksin_bylokasi2_".$level.'_'.$mc_id.'_'.$lansia;
	    $datacache = Cache::tags(['users'])->remember(env('APP_ENV', 'dev').$string, 60, function () use($level, $mc_id, $lansia) {
	        if($level > 0){
	            $query_level = ' AND mc.mc_level='.$level;
	        }else{
	            $query_level = ' AND mc.mc_level IN (1,2,3) ';
	        }

	        if($mc_id!='ALL'){
	            if(isset($request->level) && $request->level>1){
	                $query_mc_id = " AND mc.mc_id = '$mc_id' ";
	            }else{
	                $query_mc_id = " AND mc.mc_id_induk = '$mc_id' ";
	            }
	        }else{
	            $query_mc_id = " ";
	        }

	        if($lansia!='ALL'){
	            if(isset($request->lansia) && $request->lansia!='ALL'){
	                $query_lansia = " AND tv.is_lansia = $lansia ";
	            }else{
	                $query_lansia = "  ";
	            }
	        }else{
	            $query_lansia = " ";
	        }

	        $data = array();
	        $query = "SELECT tv.tv_lokasi_vaksin_pcare2::TEXT, COALESCE(COUNT(*))::int8 AS jml
				FROM transaksi_vaksin tv
				INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
				WHERE tv.is_lansia=0
				AND mc.mc_flag=1
				$query_level
				$query_lansia
				$query_mc_id
				AND (tv_lokasi_vaksin_pcare2 !=NULL or tv_lokasi_vaksin_pcare2 !='')
				GROUP BY tv.tv_lokasi_vaksin_pcare2
				ORDER BY tv.tv_lokasi_vaksin_pcare2";

			$dashvaksin_lokasi2 = DB::connection('pgsql_vaksin')->select($query);
			foreach($dashvaksin_lokasi2 as $dl2){
			    $data[] = array(
			        "v_lokasi" => $dl2->tv_lokasi_vaksin_pcare2,
			        "v_jml" => number_format($dl2->jml,0,".",",")
			    );
			}
            return $data;
	    });
        Cache::tags(['users'])->flush();
        return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function qrcode_mpm($id) {
	    set_time_limit(0);
	    ini_set('max_execution_time', 0);
	    ini_set('memory_limit', '-1');
	    ini_set('post_max_size', '409600M');
	    ini_set('max_input_time', 360000);

	    $client    = new Client();
	    $url = 'http://103.146.244.78/cosmic_api/public/api/v1/dashboard/perimeter_bykategori_all';
	    $request  = $client->request('GET', $url);

	    $response = $request->getBody()->getContents();
	    $result   = json_decode($response, true);

	    return response()->json(['status' => 200,'data' => $result]);
	}

	public function getVaksinRaw(Request $request) {
	    $limit = null;
	    $page = null;
	    $search = null;
	    $endpage = 1;

	    $vaksin = new Vaksin();
	    $vaksin->setConnection('pgsql_vaksin');
	    $vaksin = $vaksin->select('mc_id','mc_name','msp_name','mkab_name',
	        'mpro_id', 'mpro_name',
	        'tv_id','tv_mc_id','tv_nama','tv_msp_id','tv_nip','tv_unit','tv_mjk_id',
	        'tv_mkab_id','tv_nik','tv_ttl_date','tv_no_hp','tv_jml_keluarga','tv_nik_pasangan','tv_nama_pasangan',
	        'tv_nik_anak1','tv_nama_anak1','tv_nik_anak2','tv_nama_anak2','tv_nik_anak3','tv_nama_anak3',
	        'tv_nik_anak4','tv_nama_anak4','tv_nik_anak5','tv_nama_anak5',
	        'tv_date1','tv_lokasi1','tv_date2','tv_lokasi2','tv_date3','tv_lokasi3',
	        'tv_file1','tv_file1_tumb','tv_file2','tv_file2_tumb',
	        'tv_user_insert','tv_date_insert','tv_user_update','tv_date_update')
        ->join('master_company AS mc','mc.mc_id','tv_mc_id')
        ->leftjoin('master_status_pegawai AS msp','msp.msp_id','tv_msp_id')
        ->leftjoin('master_kabupaten AS mkab','mkab.mkab_id','tv_mkab_id')
        ->leftjoin('master_provinsi AS mpro','mpro.mpro_id','mkab.mkab_mpro_id');
        //->where('mc.mc_level', 1);
        if(isset($request->search)) {
            $search = $request->search;
            $vaksin = $vaksin->where(DB::raw("lower(TRIM(tv_nama))"),'like','%'.strtolower(trim($search)).'%');
        }

        $jmltotal=($vaksin->count());
        if(isset($request->limit)) {
            $limit = $request->limit;
            $vaksin = $vaksin->limit($limit);
            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

            if (isset($request->page)) {
                $page = $request->page;
                $offset = ((int)$page -1) * (int)$limit;
                $vaksin = $vaksin->offset($offset);
            }
        }
        $vaksin = $vaksin->get();
        $totalvaksin = $vaksin->count();

        if (count($vaksin) > 0){
            foreach($vaksin as $vksn){
                if($vksn->tv_file1 !=NULL || $vksn->tv_file1 !=''){
                    if (!file_exists(base_path("storage/app/public/vaksin_eviden/".$vksn->tv_mc_id.'/'.$vksn->tv_file1))) {
                        $path_file404 = '/404/img404.jpg';
                        $filevksn1 = $path_file404;
                    }else{
                        $path_file1 = '/vaksin_eviden/'.$vksn->tv_file1;
                        $filevksn1 = $path_file1;
                    }
                }else{
                    $filevksn1 = '/404/img404.jpg';
                }

                if($vksn->tv_file2 !=NULL || $vksn->tv_file2 !=''){
                    if (!file_exists(base_path("storage/app/public/vaksin_eviden/".$vksn->tv_mc_id.'/'.$vksn->tv_file2))) {
                        $path_file404 = '/404/img404.jpg';
                        $filevksn2 = $path_file404;
                    }else{
                        $path_file2 = '/vaksin_eviden/'.$vksn->tv_file2;
                        $filevksn2 = $path_file2;
                    }
                }else{
                    $filevksn2 = '/404/img404.jpg';
                }

                if($vksn->tv_file3 !=NULL || $vksn->tv_file3 !=''){
                    if (!file_exists(base_path("storage/app/public/vaksin_eviden/".$vksn->tv_mc_id.'/'.$vksn->tv_file3))) {
                        $path_file404 = '/404/img404.jpg';
                        $filevksn3 = $path_file404;
                    }else{
                        $path_file3 = '/vaksin_eviden/'.$vksn->tv_file3;
                        $filevksn3 = $path_file3;
                    }
                }else{
                    $filevksn3 = '/404/img404.jpg';
                }

                if($vksn->tv_mjk_id==1){
                    $jns_kelamin='Laki-laki';
                }else{
                    $jns_kelamin='Perempuan';
                }

                $data[] = array(
                    "kode_perusahaan" => $vksn->mc_id,
                    "nama_perusahaan" => $vksn->mc_name,
                    "id" => $vksn->tv_id,
                    "nama" => $vksn->tv_nama,
                    "sts_pegawai_id" => $vksn->tv_msp_id,
                    "sts_pegawai" => $vksn->msp_name,
                    "nip" => $vksn->tv_nip,
                    "unit" => $vksn->tv_unit,
                    "jns_kelamin_id" => $vksn->tv_mjk_id,
                    "jns_kelamin" => $jns_kelamin,
                    "kabupaten_id" => $vksn->tv_mkab_id,
                    "kabupaten" => $vksn->mkab_name,
                    "provinsi_id" => $vksn->mpro_id,
                    "provinsi" => $vksn->mpro_name,
                    "nik" => $vksn->tv_nik,
                    "tanggal_lahir" => $vksn->tv_ttl_date,
                    "no_hp" => $vksn->tv_no_hp,
                    "jml_keluarga" => $vksn->tv_jml_keluarga,
                    "nik_pasangan" => $vksn->tv_nik_pasangan,
                    "nama_pasangan" => $vksn->tv_nama_pasangan,
                    "nik_anak_1" => $vksn->tv_nik_anak1,
                    "nama_anak_1" => $vksn->tv_nama_anak1,
                    "nik_anak_2" => $vksn->tv_nik_anak2,
                    "nama_anak_2" => $vksn->tv_nama_ana2,
                    "nik_anak_3" => $vksn->tv_nik_anak3,
                    "nama_anak_3" => $vksn->tv_nama_anak3,
                    "nik_anak_4" => $vksn->tv_nik_anak4,
                    "nama_anak_4" => $vksn->tv_nama_anak4,
                    "nik_anak_5" => $vksn->tv_nik_anak5,
                    "nama_anak_5" => $vksn->tv_nama_anak5,
                    "date_1" => $vksn->tv_date1,
                    "lokasi_1" => $vksn->tv_lokasi1,
                    "date_2" => $vksn->tv_date2,
                    "lokasi_2" => $vksn->tv_lokasi2,
                    "date_3" => $vksn->tv_date3,
                    "lokasi_3" => $vksn->tv_lokasi3,
                    "file_1" => $filevksn1,
                    "file_2" => $filevksn2,
                    "file_3" => $filevksn3,
                    "date_insert" =>$vksn->tv_date_insert,
                    "date_update" =>$vksn->tv_date_update,
                );
            }
        }else{
            $data = array();
        }
        return response()->json(['status' => 200, 'page_end'=> $endpage,
            'data' => $data]);
	}

	public function getDataJmlPegawai(Request $request) {
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level= '.$level;
	    }

	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id='.$msp;
	    }

	    $data = array();
	    $query = "SELECT COALESCE(COUNT(*)) jml
			FROM transaksi_vaksin tv
			INNER JOIN master_kabupaten mkab ON mkab.mkab_id=tv.tv_mkab_id
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			WHERE tv.is_lansia=0
			AND mc.mc_flag=1
            $query_level
            $query_msp ";

        $retdb = DB::connection('pgsql_vaksin')->select($query);
        foreach($retdb as $dvp){
            $data[] = array(
                "jml" => number_format($dvp->jml,0,".",",")
            );
        }
        return response()->json(['status' => 200,'data' => $data]);
	}

	public function getDashVaksinMobileByJnsKelamin(Request $request) {
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level= '.$level;
	    }

	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id='.$msp;
	    }

	    $query_lansia = ' ';
	    if(isset($request->lansia) && $request->lansia!='ALL') {
	        $lansia = $request->lansia;
	        $query_lansia = ' AND tv.is_lansia='.$lansia;
	    }

	    $data = array();
	    $query = "SELECT mjk.mjk_id, mjk.mjk_name,
			(SELECT COALESCE(COUNT(*))
			FROM transaksi_vaksin tv
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			WHERE mc.mc_flag=1
            $query_level
            $query_msp
            $query_lansia
			AND tv.tv_mjk_id = mjk.mjk_id
			AND tv.tv_mjk_id IS NOT NULL) AS jml
			FROM master_jenis_kelamin mjk
			ORDER BY mjk.mjk_name;";

	    $retdb = DB::connection('pgsql_vaksin')->select($query);
	    foreach($retdb as $dvp){
	        $data[] = array(
	            "id" => $dvp->mjk_id,
	            "judul" => $dvp->mjk_name,
	            "jml" => $dvp->jml
	        );
	    }
	    return response()->json(['status' => 200,'data' => $data]);
	}

	public function getDashVaksinMobileByStsPegawai(Request $request) {
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level= '.$level;
	    }

	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id='.$msp;
	    }

	    $data = array();
	    $query = "SELECT msp.msp_id, msp.msp_name2,
			(SELECT COALESCE(COUNT(*))
			FROM transaksi_vaksin tv
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			WHERE tv.is_lansia=0
			AND mc.mc_flag=1
            $query_level
            $query_msp
			AND msp.msp_id=tv.tv_msp_id
			AND tv.tv_msp_id IS NOT NULL) AS jml
			FROM master_status_pegawai msp
            WHERE msp.msp_id NOT IN (4,5,6)
			ORDER BY msp.msp_name2;";

        $retdb = DB::connection('pgsql_vaksin')->select($query);
        foreach($retdb as $dvp){
            $data[] = array(
                "id" => $dvp->msp_id,
                "judul" => $dvp->msp_name2,
                "jml" => $dvp->jml
            );
        }
        return response()->json(['status' => 200,'data' => $data]);
	}

	public function getDashVaksinMobileByProvinsi(Request $request) {
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level= '.$level;
	    }

	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id='.$msp;
	    }

	    $data = array();
	    $query = "SELECT mpro.mpro_id, mpro.mpro_name,
			(SELECT COALESCE(COUNT(*))
			FROM transaksi_vaksin tv
			INNER JOIN master_kabupaten mkab ON mkab.mkab_id=tv.tv_mkab_id
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			WHERE tv.is_lansia=0
			AND mc.mc_flag=1
            $query_level
            $query_msp
			AND mkab.mkab_id=tv.tv_mkab_id
			AND tv.tv_mkab_id IS NOT NULL
			AND mkab.mkab_mpro_id=mpro.mpro_id) AS jml
			FROM master_provinsi mpro
			ORDER BY mpro.mpro_id;";

        $retdb = DB::connection('pgsql_vaksin')->select($query);
        foreach($retdb as $dvp){
            $data[] = array(
                "id" => $dvp->mpro_id,
                "judul" => $dvp->mpro_name,
                "jml" => $dvp->jml
            );
        }
        return response()->json(['status' => 200,'data' => $data]);
	}

	public function getDashVaksinMobileByUsia(Request $request) {
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level= '.$level;
	    }

	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id='.$msp;
	    }

	    $data = array();
	    $query = " SELECT mu.mu_id, mu.mu_nama,
			(SELECT COALESCE(COUNT(*))
			FROM transaksi_vaksin tv
			INNER JOIN master_kabupaten mkab ON mkab.mkab_id=tv.tv_mkab_id
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			WHERE tv.is_lansia=0
			AND mc.mc_flag=1
            $query_level
            $query_msp
			AND mkab.mkab_id=tv.tv_mkab_id
			AND tv.tv_mkab_id IS NOT NULL
			AND mu.mu_awal <= tv.tv_usia
			AND mu.mu_akhir >= tv.tv_usia) AS jml
			FROM master_usia mu
			ORDER BY mu.mu_id;";

        $retdb = DB::connection('pgsql_vaksin')->select($query);
        foreach($retdb as $dvp){
            $data[] = array(
                "id" => $dvp->mu_id,
                "judul" => $dvp->mu_nama,
                "jml" => $dvp->jml
            );
        }
        return response()->json(['status' => 200,'data' => $data]);
	}

	public function getDashVaksinMobileCompanyByKabupaten($id, Request $request) {
	    $limit = '';
	    $page = '';
	    $endpage = 1;

	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    $query_level1 = ' AND mc1.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level= '.$level;
	        $query_level1 = ' AND mc1.mc_level= '.$level;
	    }

	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id= '.$msp;
	    }

	    $query_lansia = ' ';
	    if(isset($request->lansia) && $request->lansia!='ALL'){
	        $query_lansia = " AND tv.is_lansia=0 ";
	    }else{
	        $query_lansia = " ";
	    }

	    $query_search = ' ';
	    if(isset($request->search)) {
	        $query_search = " AND LOWER(TRIM(mc1.mc_name)) LIKE LOWER(TRIM('%$request->search%')) ";
	    }

	    $data = array();
	    $query = "SELECT mc1.mc_id, mc1.mc_name,
			(SELECT COALESCE(COUNT(*))
			FROM transaksi_vaksin tv
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			INNER JOIN master_kabupaten mkab ON mkab.mkab_id=tv.tv_mkab_id
			WHERE 1=1
            $query_lansia
            $query_msp
            $query_level
			AND tv.tv_mkab_id=$id
			AND tv.tv_mkab_id IS NOT NULL
			AND mc.mc_id=mc1.mc_id) AS jml
			FROM master_company mc1
	        WHERE 1=1
			AND mc1.mc_flag=1
            $query_level1
            $query_search ";

        if(isset($request->column_sort)) {
            if(isset($request->p_sort)) {
                $sql_sort = ' ORDER BY '.$request->column_sort.' '.$request->p_sort;
            }else{
                $sql_sort = ' ORDER BY '.$request->column_sort.' ASC';
            }
        }else{
            $sql_sort = ' ORDER BY mc1.mc_name ASC ';
        }
        $query .= $sql_sort;

        $retdbtotal = DB::connection('pgsql_vaksin')->select($query);
        $jmltotal=(count($retdbtotal));

        if(isset($request->limit)) {
            $limit = $request->limit;
            $sql_limit = ' LIMIT '.$request->limit;
            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

            $query .= $sql_limit;

            if (isset($request->page)) {
                $page = $request->page;
                $offset = ((int)$page-1) * (int)$limit;
                $sql_offset= ' OFFSET '.$offset;

                $query .= $sql_offset;
            }
        }

        $retdb = DB::connection('pgsql_vaksin')->select($query);
        foreach($retdb as $dvp){
            $data[] = array(
                "id" => $dvp->mc_id,
                "judul" => $dvp->mc_name,
                "jml" => $dvp->jml
            );
        }
        return response()->json(['status' => 200, 'page_end'=>$endpage, 'data' => $data]);
	}

	public function getDashVaksinMobileKabPro(Request $request) {
	    $endpage = 1;
	    $query_level = ' AND mc.mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;
	        $query_level = ' AND mc.mc_level='.$level;
	    }

	    $query_msp = ' ';
	    if(isset($request->stspegawai) && $request->stspegawai>0) {
	        $msp = $request->stspegawai;
	        $query_msp = ' AND tv.tv_msp_id='.$msp;
	    }

	    $query_search_mpro = ' ';
	    $query_search_mkab = ' ';
	    if(isset($request->search)) {
	        $query_search_mpro = " AND LOWER(TRIM(mpro.mpro_name)) LIKE LOWER(TRIM('%$request->search%')) ";
	        $query_search_mkab = " AND LOWER(TRIM(mkab.mkab_name)) LIKE LOWER(TRIM('%$request->search%')) ";
	    }

	    $data = array();
	    $query = "SELECT mpro.mpro_id, mpro.mpro_name,
			(SELECT COALESCE(COUNT(*))
			FROM transaksi_vaksin tv
			INNER JOIN master_kabupaten mkab ON mkab.mkab_id=tv.tv_mkab_id
			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
			WHERE tv.is_lansia=0
			AND mc.mc_flag=1
            $query_level
            $query_msp
			AND mkab.mkab_id=tv.tv_mkab_id
			AND tv.tv_mkab_id IS NOT NULL
			AND mkab.mkab_mpro_id=mpro.mpro_id) AS jml
			FROM master_provinsi mpro
            WHERE 1=1
            $query_search_mpro
			ORDER BY mpro.mpro_id ";

        $retdbtotal = DB::connection('pgsql_vaksin')->select($query);
        $jmltotal=(count($retdbtotal));

        if(isset($request->limit)) {
            $limit = $request->limit;
            $sql_limit = ' LIMIT '.$request->limit;
            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

            $query .= $sql_limit;

            if (isset($request->page)) {
                $page = $request->page;
                $offset = ((int)$page-1) * (int)$limit;
                $sql_offset= ' OFFSET '.$offset;

                $query .= $sql_offset;
            }
        }

        $retdb = DB::connection('pgsql_vaksin')->select($query);
        foreach($retdb as $dvp){
    	    $query_kab = "SELECT mkab.mkab_id, mkab.mkab_name,
    			(SELECT COALESCE(COUNT(*))
    			FROM transaksi_vaksin tv
    			INNER JOIN master_company mc ON mc.mc_id=tv.tv_mc_id
    			WHERE tv.is_lansia=0
    			AND mc.mc_flag=1
    		    $query_level
                $query_msp
    			AND tv.tv_mkab_id IS NOT NULL
    			AND mkab.mkab_id=tv.tv_mkab_id) AS jml
    			FROM master_kabupaten mkab
    			WHERE 1=1
                $query_search_mkab
                AND mkab_mpro_id=$dvp->mpro_id
    			ORDER BY mkab.mkab_name ";

            $retdb_kab = DB::connection('pgsql_vaksin')->select($query_kab);
            $data_kab = array();
            foreach($retdb_kab as $dvp_kab){
                $data_kab[] = array(
                    "id" => $dvp_kab->mkab_id,
                    "judul" => $dvp_kab->mkab_name,
                    "jml" => $dvp_kab->jml
                );
            }

            $data[] = array(
                "id" => $dvp->mpro_id,
                "judul" => $dvp->mpro_name,
                "jml" => $dvp->jml,
                "kab" => $data_kab
            );
        }
        return response()->json(['status' => 200, 'page_end'=>$endpage, 'data' => $data]);
	}

	public function getDetailProfile(Request $request){
		$tv_nik = $request->tv_nik;

        $str = "_get_dashvaksin_profile".$tv_nik;

      	//dd($string);
        $datacache = Cache::tags([$str])->remember(env('APP_ENV', 'dev').$str, 5 * 10, function () use($tv_nik) {
		/*$datacache = Cache::remember(env('APP_ENV', 'dev').$str, 5 * 10, function()use($filter_nama, $filter_mc_id, $filter_status, $limit, $page, $endpage) {*/
	        $data = array();

		    $vaksin = new Vaksin();
		    $vaksin->setConnection('pgsql_vaksin');
		    $vaksin = $vaksin->select('*')
	        ->leftjoin('master_status_pegawai AS msp','msp.msp_id','tv_msp_id')
	        ->leftjoin('master_status_vaksin AS msv','msv.msv_id','tv_status_vaksin_pcare')
	        ->leftjoin('master_company AS mc','mc.mc_id' ,'tv_mc_id')
	        ->leftjoin('master_kabupaten AS mk','mk.mkab_id','tv_mkab_id')
	        ->where('tv_nik', $tv_nik);

		    $vaksin = $vaksin->get();
	        foreach($vaksin as $dvp){
	        	if($dvp->tv_file1 !=NULL || $dvp->tv_file1 !=''){
                    if (!file_exists(base_path("storage/app/public/vaksin_eviden/".$dvp->tv_mc_id.'/'.$dvp->tv_file1))) {
                        $path_file404 = '/404/img404.jpg';
                        $filevksn1 = $path_file404;
                    }else{
                        $path_file1 = '/vaksin_eviden/'.$dvp->tv_file1;
                        $filevksn1 = $path_file1;
                    }
                }else{
                    $filevksn1 = '/404/img404.jpg';
                }

    	        $data[] = array(
    	            "nik" => $dvp->tv_nik,
    	            "nama" => $dvp->tv_nama,
    	            "tgl_lahir" => $dvp->tv_ttl_date,
    	            "nama_perusahaan" => $dvp->mc_name,
    	            "no_hp" => $dvp->tv_no_hp,
    	            "tgl_lahir" => $dvp->tv_ttl_date,
    	            "kota" => $dvp->mkab_name,
    	            "alamat" => $dvp->tv_alamat,
    	            "keluarga_inti" => $dvp->tv_jml_keluarga,
    	            "status_vaksin" => is_null($dvp->msv_name)?"Siap Vaksin":$dvp->msv_name,
    	            "tanggal_vaksin1" => $dvp->tv_date1,
    	            "jam_vaksin1" => $dvp->tv_jam_vaksin1,
    	            "lokasi_vaksin1" => $dvp->tv_lokasi1,
    	            "tanggal_vaksin2" => $dvp->tv_date2,
    	            "jam_vaksin2" => $dvp->tv_jam_vaksin2,
    	            "lokasi_vaksin2" => $dvp->tv_lokasi2,
    	            "unit" => $dvp->tv_unit,
    	            "id_pegawai" => $dvp->tv_nomor_pegawai,
    	            "photo" => $filevksn1
    	        );
    	    }
	    	//return $data;
		    return array('status' => 200,'data' => $data);
		});
		    Cache::tags([$str])->flush();
	    return response()->json($datacache);
	}


	public function getDashVaksinKabPerusahaanWeb(Request $request){
	    $query_level = ' AND mav.v_mc_level IN (1,2,3) ';
	    if(isset($request->level) && $request->level>0) {
	        $level = $request->level;

	    }else{
	        $level = 0;
	    }

	    $query_lansia_id = ' ';
	    if(isset($request->lansia)) {
	        if(isset($request->lansia) && $request->lansia!='ALL'){
	            $lansia = $request->lansia;
	        }else{
	            $lansia = $request->lansia;
	        }
	    }else{
	        $lansia ='ALL';
	    }

	    $query_kabupaten = ' ';
	    if(isset($request->kabupaten) && $request->kabupaten>0) {
	        $kabupaten = $request->kabupaten;
	    }else{
	        $kabupaten = 0;
	    }

	    $query_msv = ' ';
	    if(isset($request->sts_vaksin) && $request->sts_vaksin!='ALL'){
	        $sts_vaksin = $request->sts_vaksin;
	    }else{
	        $sts_vaksin ='ALL';
	    }

	    $string = "_get_dashvaksin_bykabperusahaanweb_".$level.'_'.$lansia.'_'.$kabupaten.'_'.$sts_vaksin;
	    $datacache = Cache::tags(['users'])->remember(env('APP_ENV', 'dev').$string, 60, function () use($level, $lansia, $kabupaten, $sts_vaksin) {

	        if($level > 0){
	            $query_level   = ' AND mav.v_mc_level='.$level;
	            $query_level1  = ' AND mc1.mc_level='.$level;
	        }else{
	            $query_level   = ' AND mav.v_mc_level IN (1,2,3) ';
	            $query_level1  = ' AND mc1.mc_level IN (1,2,3) ';
	        }

	        if($lansia!='ALL'){
	            if(isset($request->lansia) && $request->lansia!='ALL'){
	                $query_lansia = " AND mav.v_is_lansia = $lansia ";
	            }else{
	                $query_lansia = ' ';
	            }
	        }else{
	            $query_lansia = ' ';
	        }

	        if($kabupaten > 0){
	            $query_kabupaten = ' AND mav.v_mkab_id='.$kabupaten;
	        }else{
	            $query_kabupaten = ' ';
	        }

	        if($sts_vaksin!='ALL'){
	            $query_sts_vaksin = ' AND mav.v_status_vaksin_pcare='.$sts_vaksin;
	        }else{
	            $query_sts_vaksin = ' ';
	        }

	        $data = array();
	        $query = "
                    SELECT mc1.mc_id, mc1.mc_name,
                    (SELECT COALESCE(SUM(v_jml_siap_vaksin),0)
                    FROM mvt_admin_vaksin mav
                    INNER JOIN master_company mc ON mc.mc_id=mav.v_mc_id
                    WHERE mc.mc_id=mc1.mc_id
                    $query_level
                    $query_lansia
                    $query_kabupaten
                    $query_sts_vaksin) AS jml_siap_vaksin,
                    (SELECT COALESCE(SUM(v_jml_sudah_vaksin1),0)
                    FROM mvt_admin_vaksin mav
                    INNER JOIN master_company mc ON mc.mc_id=mav.v_mc_id
                    WHERE mc.mc_id=mc1.mc_id
                    $query_level
                    $query_lansia
                    $query_kabupaten
                    $query_sts_vaksin) AS jml_sudah_vaksin1,
                    (SELECT COALESCE(SUM(v_jml_sudah_vaksin2),0)
                    FROM mvt_admin_vaksin mav
                    INNER JOIN master_company mc ON mc.mc_id=mav.v_mc_id
                    WHERE mc.mc_id=mc1.mc_id
                    $query_level
                    $query_lansia
                    $query_kabupaten
                    $query_sts_vaksin) AS jml_sudah_vaksin2
                    FROM master_company mc1
                    WHERE mc1.mc_flag=1
                    $query_level1
                    ORDER BY mc1.mc_name ";

            $dashvaksin_perusahaan = DB::connection('pgsql_vaksin')->select($query);
            foreach($dashvaksin_perusahaan as $dvp){
                $data[] = array(
                    "v_mc_id" => $dvp->mc_id,
                    "v_mc_name" => $dvp->mc_name,
                    "v_jml_siap_vaksin" => number_format($dvp->jml_siap_vaksin,0,".",","),
                    "v_jml_sudah_vaksin1" => number_format($dvp->jml_sudah_vaksin1,0,".",","),
                    "v_jml_sudah_vaksin2" => number_format($dvp->jml_sudah_vaksin2,0,".",",")
                );
            }
            return $data;
	    });
        Cache::tags(['users'])->flush();
        return response()->json(['status' => 200,'data' => $datacache]);
	}
}
