<?php
namespace App\Http\Controllers;
use App\Report;
use App\TrnSurveiKepuasan;
use App\TrnDataWFHWFO;
use App\Perimeter;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Validator;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
class ReportController extends Controller {

    public function __construct() {
    }

    public function getDashReportCardByJns($id){
        //$datacache =  Cache::remember(env('APP_ENV', 'prod')."_get_report_dashboardall_card_byjns_".$id, 15 * 60, function()use($id) {
        $data = array();
        $dashreportcard_head = DB::connection('pgsql3')->select("select * from report_dashboardall_card_byjns('$id')");

        foreach($dashreportcard_head as $dh){
            $data[] = array(
                "v_id" => $dh->v_id,
                "v_judul" => $dh->v_judul,
                "v_jml" => $dh->v_jml
            );
        }
        //});
        return response()->json(['status' => 200,'data' => $data]);
    }

    public function getDashReportByJns($id){
         //$datacache =  Cache::remember(env('APP_ENV', 'prod')."_report_dashboardall_byjns_".$id, 15 * 60, function()use($id) {
        $data = array();
        $dashreportcard_head = DB::connection('pgsql3')->select("select * from report_dashboardall_byjns('$id')");

        foreach($dashreportcard_head as $dh){
            $data[] = array(
                "v_mc_id" => $dh->v_mc_id,
                "v_mc_name" => $dh->v_mc_name,
                "v_jml_1" => $dh->v_jml_1,
                "v_jml_2" => $dh->v_jml_2,
                "v_jml_3" => $dh->v_jml_3
            );
        }
        //});
        return response()->json(['status' => 200,'data' => $data]);
    }

    public function getDashReportByJnsMCid($id,$mc_id){
        //$datacache =  Cache::remember(env('APP_ENV', 'prod')."_report_dashboardall_byjnsmcid_".$id.'_'.$mc_id, 15 * 60, function()use($id) {
        $data = array();
        $dashreportcard_head = DB::connection('pgsql3')->select("SELECT * FROM report_dashboardall_byjns('$id')
        WHERE v_mc_id='$mc_id'");

        foreach($dashreportcard_head as $dh){
            $data[] = array(
                "v_mc_id" => $dh->v_mc_id,
                "v_mc_name" => $dh->v_mc_name,
                "v_jml_1" => $dh->v_jml_1,
                "v_jml_2" => $dh->v_jml_2,
                "v_jml_3" => $dh->v_jml_3
            );
        }
        //});
        return response()->json(['status' => 200,'data' => $data]);
    }

    public function getDashReportCardByMcid($id){
        //$datacache =  Cache::remember(env('APP_ENV', 'prod')."_get_dashreportbumn_head_".$id, 15 * 60, function()use($id) {
            $data = array();
            $dashreport_head = DB::connection('pgsql3')->select("select * from report_dashboardcard_bymcid('$id')");

            foreach($dashreport_head as $dh){
                $data[] = array(
                    "v_id" => $dh->x_id,
                    "v_judul" => $dh->x_judul,
                    "v_jml" => $dh->x_jml
                );
            }
        //});
            return response()->json(['status' => 200,'data' => $data]);
    }

    public function getDataByMcid($id, Request $request) {
        $limit = null;
        $page = null;
        $search = null;
        $endpage = 1;

        $report = new Report();
        $report->setConnection('pgsql3');
        $report = $report->select('tr_id', 'tr_mpml_id', 'tr_laporan',
            'tr_file1', 'tr_file2', 'tr_tl_file1',  'tr_tl_file2',
            'tr_no', 'tr_penanggungjawab', 'tr_close',  'tr_date_insert',
            'mc.mc_id', 'mc.mc_name',
            'mpm.mpm_id', 'mpm.mpm_name', 'mpml.mpml_id', 'mpml.mpml_name',
            DB::raw(" to_char((tr_date_insert)::timestamp with time zone, 'DD/MM/YYYY'::text) AS date_insert"),
            DB::raw("CASE WHEN (tr_close = 1) THEN 'Selesai diproses'::text ELSE 'Belum diproses'::text END AS status")
         )
         ->join('master_perimeter_level AS mpml','mpml.mpml_id','tr_mpml_id')
         ->join('master_perimeter AS mpm','mpm.mpm_id','mpml.mpml_mpm_id')
         ->join('master_company AS mc','mc.mc_id','mpm.mpm_mc_id')
         ->where('mc.mc_level', 1)
         ->where('mc.mc_id', $id);

         if(isset($request->close)) {
             if($request->close == 1){
                 $report = $report->where('tr_close', 1);
             }else if($request->close == 0){
                 $report = $report->where('tr_close', 0);
             }
         }

         if(isset($request->search)) {
             $search = $request->search;
             $report = $report->where(DB::raw("lower(TRIM(tr_laporan))"),'like','%'.strtolower(trim($search)).'%');
         }

         $jmltotal=($report->count());
         if(isset($request->limit)) {
             $limit = $request->limit;
             $report = $report->limit($limit);
             $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

             if (isset($request->page)) {
                 $page = $request->page;
                 $offset = ((int)$page -1) * (int)$limit;
                 $report = $report->offset($offset);
             }
         }
         $report = $report->get();
         $totalreport = $report->count();

        if (count($report) > 0){
            foreach($report as $rep){
                if($rep->tr_file1 !=NULL || $rep->tr_file1 !=''){
                    if (!file_exists(base_path("storage/app/public/report_protokol/".$rep->mc_id."/".$rep->mpml_id."/".$rep->tr_file1))) {
                        $path_file404 = '/404/img404.jpg';
                        $filerep1 = $path_file404;
                    }else{
                        $path_file1 = '/report_protokol/'.$rep->mc_id.'/'.$rep->mpml_id.'/'.$rep->tr_file1;
                        $filerep1 = $path_file1;
                    }
                }else{
                    $filerep1 = '/404/img404.jpg';
                }

                if($rep->tr_file2 !=NULL || $rep->tr_file2 !=''){
                    if (!file_exists(base_path("storage/app/public/report_protokol/".$rep->mc_id."/".$rep->mpml_id."/".$rep->tr_file2))) {
                        $path_file404 = '/404/img404.jpg';
                        $filerep2 = $path_file404;
                    }else{
                        $path_file2 = '/report_protokol/'.$rep->mc_id.'/'.$rep->mpml_id.'/'.$rep->tr_file2;
                        $filerep2 = $path_file2;
                    }
                }else{
                    $filerep2 = '/404/img404.jpg';
                }

                if($rep->tr_tl_file1 !=NULL || $rep->tr_tl_file1 !=''){
                    if (!file_exists(base_path("storage/app/public/report_protokol/".$rep->mc_id."/".$rep->mpml_id."/".$rep->tr_tl_file1))) {
                        $path_file404 = '/404/img404.jpg';
                        $filerep_tl1 = $path_file404;
                    }else{
                        $path_file1 = '/report_protokol/'.$rep->mc_id.'/'.$rep->mpml_id.'/'.$rep->tr_tl_file1;
                        $filerep_tl1 = $path_file1;
                    }
                }else{
                    $filerep_tl1 = '/404/img404.jpg';
                }

                if($rep->tr_tl_file2 !=NULL || $rep->tr_tl_file2 !=''){
                    if (!file_exists(base_path("storage/app/public/report_protokol/".$rep->mc_id."/".$rep->mpml_id."/".$rep->tr_tl_file2))) {
                        $path_file404 = '/404/img404.jpg';
                        $filerep_tl2 = $path_file404;
                    }else{
                        $path_file2 = '/report_protokol/'.$rep->mc_id.'/'.$rep->mpml_id.'/'.$rep->tr_tl_file2;
                        $filerep_tl2 = $path_file2;
                    }
                }else{
                    $filerep_tl2 = '/404/img404.jpg';
                }

                $data[] = array(
                    "id" => $rep->tr_id,
                    "mpml_id" => $rep->tr_mpml_id,
                    "mpml_name" => $rep->mpml_name,
                    "mpm_id" => $rep->mpm_id,
                    "mpm_name" => $rep->mpm_name,
                    "mc_id" => $rep->mc_id,
                    "file_1" => $filerep1,
                    "file_2" => $filerep2,
                    "file_tl_1" => $filerep_tl1,
                    "file_tl_2" => $filerep_tl2,
                    "laporan" => $rep->tr_laporan,
                    "close" => $rep->tr_close,
                    "status" => $rep->status,
                    "no_laporan" => $rep->tr_no,
                    "penanggungjawab" => $rep->tr_penanggungjawab,
                    "date_insert" => $rep->date_insert,
                );
            }
        }else{
            $data = array();
        }
        return response()->json(['status' => 200, 'page_end'=> $endpage,
             'data' => $data]);
    }

    public function WebUpdateReportJSON($user_id, $id, Request $request) {
        date_default_timezone_set('Asia/Jakarta');
        $penanggungjawab = $request->penanggungjawab;
        $ceklis = $request->ceklis;
        $r_file1 = $request->file_report1;
        $r_file2 = $request->file_report2;

        $dataReport = Report::find($id);
        $filex1 = $dataReport->tr_tl_file1;
        $filex2 = $dataReport->tr_tl_file2;

        $report_data =  DB::connection('pgsql3')->select("SELECT tr.*, mc.mc_id, mc.mc_name, mpm.mpm_id, mpm.mpm_name,
        mpml.mpml_id, mpml.mpml_name
				FROM transaksi_report tr
				INNER JOIN master_perimeter_level mpml ON mpml.mpml_id=tr.tr_mpml_id
				INNER JOIN master_perimeter mpm ON mpm.mpm_id=mpml.mpml_mpm_id
				INNER JOIN master_company mc ON mc.mc_id=mpm.mpm_mc_id
				AND tr.tr_id=$id");

        if(count($report_data) > 0){
            $mc_id = $report_data[0]->mc_id;
            $mpml_id = $report_data[0]->mpml_id;

            if(!Storage::exists('/app/public/report_protokol/')) {
                Storage::disk('public')->makeDirectory('/report_protokol/');
            }

            if(!Storage::exists('/app/public/report_protokol/'.$mc_id)) {
                Storage::disk('public')->makeDirectory('/report_protokol/'.$mc_id);
            }

            if(!Storage::exists('/app/public/report_protokol/'.$mc_id.'/'.$mpml_id)) {
                Storage::disk('public')->makeDirectory('/report_protokol/'.$mc_id.'/'.$mpml_id);
            }

            $destinationPath = storage_path().'/app/public/report_protokol/'.$mc_id.'/'.$mpml_id;

            $name1 = $filex1;
            if(isset($request->file_report1)){
                if ($request->file_report1 != null || $request->file_report1 != '') {
                    if($filex1!=NULL && file_exists(storage_path().'/app/public/report_protokol/'.$filex1)){
                        unlink(storage_path().'/app/public/report_protokol/'.$mc_id.'/'.$mpml_id.'/'.$filex1);
                    }

                    $img1 = explode(',', $r_file1);
                    $image1 = $img1[1];
                    $filedecode1 = base64_decode($image1);
                    $name1 = round(microtime(true) * 1000).'.jpg';

                    Image::make($filedecode1)->resize(700, NULL, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath.'/'.$name1);
                }
            }

            $name2 = $filex2;
            if(isset($request->file_report2)){
                if ($request->file_report2 != null || $request->file_report2 != '') {
                    if($filex2!=NULL && file_exists(storage_path().'/app/public/report_protokol/'.$filex2)){
                        unlink(storage_path().'/app/public/report_protokol/'.$mc_id.'/'.$mpml_id.'/'.$filex2);
                    }

                    $img2 = explode(',', $r_file2);
                    $image2 = $img2[1];
                    $filedecode2 = base64_decode($image2);
                    $name2 = round(microtime(true) * 1000).'.jpg';

                    Image::make($filedecode2)->resize(700, NULL, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath.'/'.$name2);
                }
            }

            $dataReport->tr_tl_file1 = $name1;
            $dataReport->tr_tl_file2 = $name2;
            $dataReport->tr_close = $ceklis;
            $dataReport->tr_penanggungjawab = $penanggungjawab;
            $dataReport->tr_date_update = date('Y-m-d H:i:s');
            $dataReport->tr_user_update = $user_id;
            $dataReport->save();

            if($dataReport->save()) {
                return response()->json(['status' => 200,'message' => 'Data Report Protokol Berhasil diUpdate']);
            } else {
                return response()->json(['status' => 500,'message' => 'Data Report Protokol Gagal diUpdate'])->setStatusCode(500);
            }
        }else{
            return response()->json(['status' => 404,'message' => 'Data Report Protokol Tidak Ditemukan '])->setStatusCode(404);
        }
    }

    public function updateReportJSON($id, Request $request) {
        date_default_timezone_set('Asia/Jakarta');
        $penanggungjawab = $request->penanggungjawab;
        $ceklis = $request->ceklis;
        $r_file1 = $request->file_report1;
        $r_file2 = $request->file_report2;

        $report_data =  DB::connection('pgsql3')->select("SELECT tr.*, mc.mc_id, mc.mc_name, mpm.mpm_id, mpm.mpm_name,
        mpml.mpml_id, mpml.mpml_name
				FROM transaksi_report tr
				INNER JOIN master_perimeter_level mpml ON mpml.mpml_id=tr.tr_mpml_id
				INNER JOIN master_perimeter mpm ON mpm.mpm_id=mpml.mpml_mpm_id
				INNER JOIN master_company mc ON mc.mc_id=mpm.mpm_mc_id
				AND tr.tr_id=$id");

        if(count($report_data) > 0){
            $mc_id = $report_data[0]->mc_id;
            $mpml_id = $report_data[0]->mpml_id;

            $dataReport = Report::find($id);
            $filex1 = $dataReport->tr_tl_file1;
            $filex2 = $dataReport->tr_tl_file2;

            if(!Storage::exists('/app/public/report_protokol/')) {
                Storage::disk('public')->makeDirectory('/report_protokol/');
            }

            if(!Storage::exists('/app/public/report_protokol/'.$mc_id)) {
                Storage::disk('public')->makeDirectory('/report_protokol/'.$mc_id);
            }

            if(!Storage::exists('/app/public/report_protokol/'.$mc_id.'/'.$mpml_id)) {
                Storage::disk('public')->makeDirectory('/report_protokol/'.$mc_id.'/'.$mpml_id);
            }

            $destinationPath = storage_path().'/app/public/report_protokol/'.$mc_id.'/'.$mpml_id;

            $name1 = $filex1;
            if(isset($request->file_report1)){
                if ($request->file_report1 != null || $request->file_report1 != '') {
                    if($filex1!=NULL && file_exists(storage_path().'/app/public/report_protokol/'.$filex1)){
                        unlink(storage_path().'/app/public/report_protokol/'.$mc_id.'/'.$mpml_id.'/'.$filex1);
                    }

                    $img1 = explode(',', $r_file1);
                    $image1 = $img1[1];
                    $filedecode1 = base64_decode($image1);
                    $name1 = round(microtime(true) * 1000).'.jpg';

                    Image::make($filedecode1)->resize(700, NULL, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath.'/'.$name1);
                }
            }

            $name2 = $filex2;
            if(isset($request->file_report2)){
                if ($request->file_report2 != null || $request->file_report2 != '') {
                    if($filex2!=NULL && file_exists(storage_path().'/app/public/report_protokol/'.$filex2)){
                        unlink(storage_path().'/app/public/report_protokol/'.$mc_id.'/'.$mpml_id.'/'.$filex2);
                    }

                    $img2 = explode(',', $r_file2);
                    $image2 = $img2[1];
                    $filedecode2 = base64_decode($image2);
                    $name2 = round(microtime(true) * 1000).'.jpg';

                    Image::make($filedecode2)->resize(700, NULL, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath.'/'.$name2);
                }
            }

            $dataReport->tr_tl_file1 = $name1;
            $dataReport->tr_tl_file2 = $name2;
            $dataReport->tr_close = $ceklis;
            $dataReport->tr_penanggungjawab = $penanggungjawab;
            $dataReport->tr_date_update = date('Y-m-d H:i:s');
            $dataReport->tr_user_update = Auth::guard('api')->user()->id;
            $dataReport->save();


            if($dataReport->save()) {
                return response()->json(['status' => 200,'message' => 'Data Report Protokol Berhasil diUpdate']);
            } else {
                return response()->json(['status' => 500,'message' => 'Data Report Protokol Gagal diUpdate'])->setStatusCode(500);
            }
        }else{
            return response()->json(['status' => 404,'message' => 'Data Report Protokol Tidak Ditemukan '])->setStatusCode(404);
        }

    }

    public function getDataById($id) {
        $report = DB::connection('pgsql3')->select("SELECT tr.*, mc.mc_id, mc.mc_name, mpm.mpm_id, mpm.mpm_name,
        mpml.mpml_id, mpml.mpml_name,
        CASE
            WHEN (tr.tr_close = 1) THEN 'Selesai diproses'::text
            ELSE 'Belum diproses'::text
        END AS status,
        to_char((tr.tr_date_insert)::timestamp with time zone, 'DD/MM/YYYY'::text) AS date_insert
				FROM transaksi_report tr
				INNER JOIN master_perimeter_level mpml ON mpml.mpml_id=tr.tr_mpml_id
				INNER JOIN master_perimeter mpm ON mpm.mpm_id=mpml.mpml_mpm_id
				INNER JOIN master_company mc ON mc.mc_id=mpm.mpm_mc_id
				AND tr.tr_id=$id
    	        ORDER BY tr_id DESC");

        if(count($report) > 0) {
            foreach($report as $rep){
                if($rep->tr_file1 !=NULL || $rep->tr_file1 !=''){
                    if (!file_exists(base_path("storage/app/public/report_protokol/".$rep->mc_id."/".$rep->mpml_id."/".$rep->tr_file1))) {
                        $path_file404 = '/404/img404.jpg';
                        $filerep1 = $path_file404;
                    }else{
                        $path_file1 = '/report_protokol/'.$rep->mc_id.'/'.$rep->mpml_id.'/'.$rep->tr_file1;
                        $filerep1 = $path_file1;
                    }
                }else{
                    $filerep1 = '/404/img404.jpg';
                }

                if($rep->tr_file2 !=NULL || $rep->tr_file2 !=''){
                    if (!file_exists(base_path("storage/app/public/report_protokol/".$rep->mc_id."/".$rep->mpml_id."/".$rep->tr_file2))) {
                        $path_file404 = '/404/img404.jpg';
                        $filerep2 = $path_file404;
                    }else{
                        $path_file2 = '/report_protokol/'.$rep->mc_id.'/'.$rep->mpml_id.'/'.$rep->tr_file2;
                        $filerep2 = $path_file2;
                    }
                }else{
                    $filerep2 = '/404/img404.jpg';
                }

                if($rep->tr_tl_file1 !=NULL || $rep->tr_tl_file1 !=''){
                    if (!file_exists(base_path("storage/app/public/report_protokol/".$rep->mc_id."/".$rep->mpml_id."/".$rep->tr_tl_file1))) {
                        $path_file404 = '/404/img404.jpg';
                        $filerep_tl1 = $path_file404;
                    }else{
                        $path_file1 = '/report_protokol/'.$rep->mc_id.'/'.$rep->mpml_id.'/'.$rep->tr_tl_file1;
                        $filerep_tl1 = $path_file1;
                    }
                }else{
                    $filerep_tl1 = '/404/img404.jpg';
                }

                if($rep->tr_tl_file2 !=NULL || $rep->tr_tl_file2 !=''){
                    if (!file_exists(base_path("storage/app/public/report_protokol/".$rep->mc_id."/".$rep->mpml_id."/".$rep->tr_tl_file2))) {
                        $path_file404 = '/404/img404.jpg';
                        $filerep_tl2 = $path_file404;
                    }else{
                        $path_file2 = '/report_protokol/'.$rep->mc_id.'/'.$rep->mpml_id.'/'.$rep->tr_tl_file2;
                        $filerep_tl2 = $path_file2;
                    }
                }else{
                    $filerep_tl2 = '/404/img404.jpg';
                }

                if(($filerep1==NULL && $filerep2==NULL) || ($filerep1=='' && $filerep2=='')){
                    $flag_foto = false;
                }else{
                    $flag_foto = true;
                }

                $data = array(
                    "id" => $rep->tr_id,
                    "mpml_id" => $rep->tr_mpml_id,
                    "mpml_name" => $rep->mpml_name,
                    "mpm_id" => $rep->mpm_id,
                    "mpm_name" => $rep->mpm_name,
                    "mc_id" => $rep->mc_id,
                    "file_1" => $filerep1,
                    "file_2" => $filerep2,
                    "file_tl_1" => $filerep_tl1,
                    "file_tl_2" => $filerep_tl2,
                    "laporan" => $rep->tr_laporan,
                    "close" => $rep->tr_close,
                    "status" => $rep->status,
                    "no_laporan" => $rep->tr_no,
                    "penanggungjawab" => $rep->tr_penanggungjawab,
                    "date_insert" => $rep->date_insert,
                );
            }
        }else{
            $data = array();
        }
        return response()->json(['status' => 200,'data' => $data]);
    }

    public function getDashReportMobileByJns($id, Request $request){
        $data = array();
        $limit = '';
        $page = 1;
        $endpage = 1;

        $query = " SELECT *  FROM report_dashboardall_byjns('$id')
                WHERE 1=1 ";

        if(isset($request->search)) {
            $search = $request->search;
            $query .= " AND LOWER(TRIM(v_mc_name)) LIKE LOWER(TRIM('%$request->search%')) ";
        }

        $reportall = DB::connection('pgsql3')->select($query);


        $jmltotal=(count($reportall));

        if(isset($request->column_sort)) {
            if(isset($request->p_sort)) {
                $sql_sort = ' ORDER BY '.$request->column_sort.' '.$request->p_sort;
            }else{
                $sql_sort = ' ORDER BY '.$request->column_sort.' DESC';
            }
        }else{
            $sql_sort = ' ORDER BY v_jml_1 DESC ';
        }
        $query .= $sql_sort;

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

        $report =  DB::connection('pgsql3')->select($query);
        $cntreportall = count($reportall);
        foreach($report as $dh){
            $data[] = array(
                "v_mc_id" => $dh->v_mc_id,
                "v_mc_name" => $dh->v_mc_name,
                "v_jml_1" => $dh->v_jml_1,
                "v_jml_2" => $dh->v_jml_2,
                "v_jml_3" => $dh->v_jml_3
            );
        }

        return response()->json(['status' => 200, 'page_end'=>$endpage, 'data' => $data]);
    }

    public function getMobilePICFObyMcid($id, Request $request){
        $data = array();
        $limit = '';
        $page = '';
        $endpage = 1;

        $query = " SELECT au.id, au.username, au.first_name
                FROM app_users au
                INNER JOIN app_users_groups aug ON aug.user_id=au.id
                WHERE aug.group_id IN (3,4)
                AND au.mc_id='$id' ";

        if(isset($request->search)) {
            $search = $request->search;
            $sqlsearch = " AND LOWER(TRIM(first_name)) LIKE LOWER(TRIM('%$request->search%')) ";
        }else{
            $search = '';
            $sqlsearch = "";
        }

        $picfoall =  DB::connection('pgsql3')->select($query . $sqlsearch);
        $picfo =  DB::connection('pgsql3')->select($query . $sqlsearch);

        $jmltotal=(count($picfoall));
        if(isset($request->limit)) {
            $limit = $request->limit;
            $report =  DB::connection('pgsql3')->select($query . $sqlsearch .  " ORDER BY first_name LIMIT $limit ");
            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

            if (isset($request->page)) {
                $page = $request->page;
                $offset = ((int)$page-1) * (int)$limit;
                $report =  DB::connection('pgsql3')->select($query . $sqlsearch .  " ORDER BY first_name OFFSET $offset LIMIT $limit ");
            }
        }

        $cntpicfoall = count($picfoall);
        foreach($picfo as $pf){
            $data[] = array(
                "id" => $pf->id,
                "username" => $pf->username,
                "firstname" => $pf->first_name
            );
        }

        return response()->json(['status' => 200, 'page_end'=>$endpage, 'data' => $data]);
    }

    public function postSurveiKepuasan(Request $request) {
        date_default_timezone_set('Asia/Jakarta');
        $this->validate($request, [
              'username' => 'required',
          ]);
        $username = $request->username;
        $rate_kepuasan = $request->rate_kepuasan;
        $ket_kepuasan = $request->ket_kepuasan;
        $rate_rekomendasi = $request->rate_rekomendasi;
        $ket_rekomendasi = $request->ket_rekomendasi;
        $kritik_saran = $request->kritik_saran;

        $surveikepuasan = New TrnSurveiKepuasan();
        $surveikepuasan = $surveikepuasan->setConnection('pgsql');
        $surveikepuasan = $surveikepuasan->whereRaw("LOWER(TRIM(tsk_username)) ='".strtolower(trim($username))."'" )->first();
        //dd($surveikepuasan);
        if($surveikepuasan == null){
          $surveikepuasan= New TrnSurveiKepuasan();
          $surveikepuasan->setConnection('pgsql');
          $surveikepuasan->tsk_username= $username;
          if(isset($rate_kepuasan)){
            $surveikepuasan->tsk_rate_kp= $rate_kepuasan;
          }
          if(isset($ket_kepuasan)){
            $surveikepuasan->tsk_ket_kp= $ket_kepuasan;
          }
          if(isset($rate_rekomendasi)){
            $surveikepuasan->tsk_rate_rek= $rate_rekomendasi;
          }
          if(isset($ket_rekomendasi)){
            $surveikepuasan->tsk_ket_rek= $ket_rekomendasi;
          }
          if(isset($kritik_saran)){
            $surveikepuasan->tsk_kritik_saran= $kritik_saran;
          }

        } else {
          $surveikepuasan->tsk_username= $username;
          if(isset($rate_kepuasan)){
            $surveikepuasan->tsk_rate_kp= $rate_kepuasan;
          }
          if(isset($ket_kepuasan)){
            $surveikepuasan->tsk_ket_kp= $ket_kepuasan;
          }
          if(isset($rate_rekomendasi)){
            $surveikepuasan->tsk_rate_rek= $rate_rekomendasi;
          }
          if(isset($ket_rekomendasi)){
            $surveikepuasan->tsk_ket_rek= $ket_rekomendasi;
          }
          if(isset($kritik_saran)){
            $surveikepuasan->tsk_kritik_saran= $kritik_saran;
          }

        }

            if($surveikepuasan->save()) {
                return response()->json(['status' => 200,'message' => 'Data Survei Kepuasan Berhasil diupdate']);
            } else {
                return response()->json(['status' => 500,'message' => 'Data Survei Kepuasan Gagal diupdate'])->setStatusCode(500);
            }


    }

    public function UpdateLockdown(Request $request) {
        date_default_timezone_set('Asia/Jakarta');
        $this->validate($request, [
              'mpm_id' => 'required',
        ]);
        $mpm_id = $request->mpm_id;
        $is_lockdown = $request->is_lockdown;
        $keterangan = $request->keterangan;

        $mst = New Perimeter();
        $mst = $mst->setConnection('pgsql');

        if($is_lockdown == 0){
            $mst = $mst->whereRaw("mpm_id ='".$mpm_id."'" )->first();
            if(isset($is_lockdown)){
                //$mst->mpm_lockdown= 1;
                $mst->mpm_lockdown= $is_lockdown;
            }

            if(isset($keterangan)){
                $mst->mpm_keterangan_lockdown= null ;
            }

            $mst->mpm_date_lockdown = date('Y-m-d');
            $mst->save();
            return response()->json(['status' => 200,'message' => 'Berhasil buka perimeter']);
        } else {
            $mst = $mst->whereRaw("mpm_id ='".$mpm_id."'" )->first();
            if(isset($is_lockdown)){
                //$mst->mpm_lockdown= 0;
                $mst->mpm_lockdown= $is_lockdown;
            }
            if(isset($keterangan)){
                $mst->mpm_keterangan_lockdown= $keterangan;
            }
            $mst->save();
            return response()->json(['status' => 200,'message' => 'Perimeter berhasil di lockdown']);
        }
    }

    public function postDataWFHWFO(Request $request) {
        date_default_timezone_set('Asia/Jakarta');
        $this->validate($request, [
              'bulan' => 'required',
              'tahun' => 'required',
              'kd_perusahaan' => 'required',
              'user_id' => 'required',
          ]);
        $user_id = $request->user_id;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $kd_perusahaan = $request->kd_perusahaan;
        $jml_peg_tetap = $request->jml_peg_tetap;
        $jml_peg_kontrak = $request->jml_peg_kontrak;
        $jml_peg_alihdaya = $request->jml_peg_alihdaya;
        $jml_rata_peg_masuk = $request->jml_rata_peg_masuk;
        $jns_industri = $request->jns_industri;
        $file_protokoll_wfh = NULL;
        $file_jadwal = NULL;
        $flag_dok_protokol = $request->flag_dok_protokol;

        if(!Storage::exists('/app/public/data_wfh_wfo/'.$kd_perusahaan)) {
            Storage::disk('public')->makeDirectory('/data_wfh_wfo/'.$kd_perusahaan, 0777, true);
        }

        //$destinationPath = base_path("storage\app\public\sosialisasi/").$kd_perusahaan.'/'.$tanggal;
        $destinationPath = storage_path().'/app/public/data_wfh_wfo/' .$kd_perusahaan;

        $nameid = round(microtime(true) * 1000);
        $name_pdf = NULL;
        if(isset($request->file_protokol_wfh)){
          if ($request->file_protokol_wfh != null || $request->file_protokol_wfh != '') {
              $image = str_replace('data:application/pdf;base64,', '',$request->file_protokol_wfh);
              $filedecode = base64_decode($image);
              $name_pdf = 'protokol_wfh_'.$nameid.'.pdf';
              Storage::disk('public')->put('data_wfh_wfo/'.$kd_perusahaan.'/'.$name_pdf, base64_decode($image));

          }
        }

        $name_pdf2 = NULL;
        if(isset($request->file_jadwal)){
          if ($request->file_jadwal != null || $request->file_jadwal != '') {
              $image = str_replace('data:application/pdf;base64,', '',$request->file_jadwal);
              $filedecode = base64_decode($image);
              $name_pdf2 = 'jadwal_'.$nameid.'.pdf';
              Storage::disk('public')->put('data_wfh_wfo/'.$kd_perusahaan.'/'.$name_pdf2, base64_decode($image));

          }
        }

        $datawfh = New TrnDataWFHWFO();
        $datawfh = $datawfh->setConnection('pgsql');
        $datawfh = $datawfh->where("tw_tahun",$tahun )->where("tw_bulan",$bulan)->where("tw_mc_id",$kd_perusahaan)->first();

        //dd($surveikepuasan);
        if($datawfh == null){
          $datawfh= New TrnDataWFHWFO();
          $datawfh->setConnection('pgsql');
          $datawfh->tw_mc_id = $kd_perusahaan;
          $datawfh->tw_bulan = $bulan;
          $datawfh->tw_tahun = $tahun;
          $datawfh->tw_jml_peg_tetap = $jml_peg_tetap;
          $datawfh->tw_jml_peg_kontrak = $jml_peg_kontrak;
          $datawfh->tw_jml_peg_alihdaya = $jml_peg_alihdaya;
          $datawfh->tw_jml_rata_peg_masuk = $jml_rata_peg_masuk;
          $datawfh->tw_jns_industri = $jns_industri;
          $datawfh->tw_file_protokol_wfh = $name_pdf;
          $datawfh->tw_file_jadwal = $name_pdf2;
          $datawfh->tw_flag_dok_protokol = $flag_dok_protokol;
          $datawfh->tw_user_insert = $user_id;

        } else {
          $datawfh->tw_jml_peg_tetap = $jml_peg_tetap;
          $datawfh->tw_jml_peg_kontrak = $jml_peg_kontrak;
          $datawfh->tw_jml_peg_alihdaya = $jml_peg_alihdaya;
          $datawfh->tw_jml_rata_peg_masuk = $jml_rata_peg_masuk;
          $datawfh->tw_jns_industri = $jns_industri;
          $datawfh->tw_file_protokol_wfh = $name_pdf;
          $datawfh->tw_file_jadwal = $name_pdf2;
          $datawfh->tw_flag_dok_protokol = $flag_dok_protokol;
          $datawfh->tw_user_update = $user_id;

        }

            if($datawfh->save()) {
                return response()->json(['status' => 200,'message' => 'Data WFH WFO Berhasil diupdate']);
            } else {
                return response()->json(['status' => 500,'message' => 'Data WFH WFO Gagal diupdate'])->setStatusCode(500);
            }


    }

    public function getDataWFHWFOByPerusahaan($mc_id) {
        $data_wfh_wfo = DB::connection('pgsql')->select("SELECT tw.*, mc.mc_name, mc.mc_id, mj.jenis
                FROM transaksi_wfh_wfo tw
                LEFT JOIN master_company mc ON mc.mc_id=tw.tw_mc_id
                LEFT JOIN master_jenis_industri mj ON mj.id=tw.tw_jns_industri
                WHERE tw_mc_id='$mc_id' and tw_bulan = date_part('month', now()) and tw_tahun = date_part('year', now()) order by tw_id desc limit 1");

        if(count($data_wfh_wfo) > 0) {
            foreach($data_wfh_wfo as $wfh){
                if($wfh->tw_file_protokol_wfh !=NULL || $wfh->tw_file_protokol_wfh !=''){
                    if (!file_exists(base_path("storage/app/public/data_wfh_wfo/".$mc_id.'/'.$wfh->tw_file_protokol_wfh))) {
                        $path_file404 = '/404/img404.jpg';
                        $filewfh1 = $path_file404;

                    }else{
                        $path_file1 = '/data_wfh_wfo/'.$mc_id.'/'.$wfh->tw_file_protokol_wfh;
                        $filewfh1 = $path_file1;

                    }
                }else{
                    $filewfh1 = '/404/img404.jpg';

                }

                if($wfh->tw_file_jadwal !=NULL || $wfh->tw_file_jadwal !=''){
                    if (!file_exists(base_path("storage/app/public/data_wfh_wfo/".$mc_id.'/'.$wfh->tw_file_jadwal))) {
                        $path_file404 = '/404/img404.jpg';
                        $filewfh2 = $path_file404;
                    }else{
                        $path_file2 = '/data_wfh_wfo/'.$mc_id.'/'.$wfh->tw_file_jadwal;
                        $filewfh2 = $path_file2;

                    }
                }else{
                    $filewfh2 = '/404/img404.jpg';

                }


                $data = array(
                    "kd_perusahaan" => $wfh->mc_id,
                    "tahun" => $wfh->tw_tahun,
                    "bulan" => $wfh->tw_bulan,
                    "jml_peg_tetap" => $wfh->tw_jml_peg_tetap,
                    "jml_peg_kontrak" => $wfh->tw_jml_peg_kontrak,
                    "jml_peg_alihdaya" => $wfh->tw_jml_peg_alihdaya,
                    "jml_rata_peg_masuk" => $wfh->tw_jml_rata_peg_masuk,
                    "jns_industri" =>$wfh->tw_jns_industri,
                    "nm_jns_industri" =>$wfh->jenis,
                    "file_protokol_wfh" =>$wfh->tw_file_protokol_wfh,
                    "file_jadwal" =>$wfh->tw_file_jadwal,
                    "flag_dok_protokol" =>$wfh->tw_flag_dok_protokol,

                );
            }
        }else{
          $data = array(
              "kd_perusahaan" => $mc_id,
              "tahun" => Carbon::now()->year,
              "bulan" => Carbon::now()->month,
              "jml_peg_tetap" => 0,
              "jml_peg_kontrak" => 0,
              "jml_peg_alihdaya" => 0,
              "jml_rata_peg_masuk" => 0,
              "jns_industri" =>0,
              "nm_jns_industri" =>'',
              "file_protokol_wfh" =>NULL,
              "file_jadwal" =>NULL,
              "flag_dok_protokol" =>false,

          );
        }
        return response()->json(['status' => 200,'data' => $data]);
    }

    public function getDataPelaporanWFHWFOByPerusahaan($mc_id, Request $request) {
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $data_wfh_wfo = DB::connection('pgsql3')->select("SELECT tw.*, mc.mc_name, mc.mc_id, mj.jenis
                FROM transaksi_wfh_wfo tw
                LEFT JOIN master_company mc ON mc.mc_id=tw.tw_mc_id
                LEFT JOIN master_jenis_industri mj ON mj.id=tw.tw_jns_industri
                WHERE tw_mc_id='$mc_id' and tw_bulan = '$bulan' and tw_tahun = '$tahun' order by tw_id desc limit 1");

        if(count($data_wfh_wfo) > 0) {
            foreach($data_wfh_wfo as $wfh){
                if($wfh->tw_file_protokol_wfh !=NULL || $wfh->tw_file_protokol_wfh !=''){
                    if (!file_exists(base_path("storage/app/public/data_wfh_wfo/".$mc_id.'/'.$wfh->tw_file_protokol_wfh))) {
                        $path_file404 = '/404/img404.jpg';
                        $filewfh1 = $path_file404;

                    }else{
                        $path_file1 = '/data_wfh_wfo/'.$mc_id.'/'.$wfh->tw_file_protokol_wfh;
                        $filewfh1 = $path_file1;

                    }
                }else{
                    $filewfh1 = '/404/img404.jpg';

                }

                if($wfh->tw_file_jadwal !=NULL || $wfh->tw_file_jadwal !=''){
                    if (!file_exists(base_path("storage/app/public/data_wfh_wfo/".$mc_id.'/'.$wfh->tw_file_jadwal))) {
                        $path_file404 = '/404/img404.jpg';
                        $filewfh2 = $path_file404;
                    }else{
                        $path_file2 = '/data_wfh_wfo/'.$mc_id.'/'.$wfh->tw_file_jadwal;
                        $filewfh2 = $path_file2;

                    }
                }else{
                    $filewfh2 = '/404/img404.jpg';

                }


                $data = array(
                    "kd_perusahaan" => $wfh->mc_id,
                    "tahun" => $wfh->tw_tahun,
                    "bulan" => $wfh->tw_bulan,
                    "jml_peg_tetap" => $wfh->tw_jml_peg_tetap,
                    "jml_peg_kontrak" => $wfh->tw_jml_peg_kontrak,
                    "jml_peg_alihdaya" => $wfh->tw_jml_peg_alihdaya,
                    "jml_rata_peg_masuk" => $wfh->tw_jml_rata_peg_masuk,
                    "jns_industri" =>$wfh->tw_jns_industri,
                    "nm_jns_industri" =>$wfh->jenis,
                    "file_protokol_wfh" =>$wfh->tw_file_protokol_wfh,
                    "file_jadwal" =>$wfh->tw_file_jadwal,
                    "flag_dok_protokol" =>$wfh->tw_flag_dok_protokol,

                );
            }
        }else{
          $data = array(
              "kd_perusahaan" => $mc_id,
              "tahun" => Carbon::now()->year,
              "bulan" => Carbon::now()->month,
              "jml_peg_tetap" => 0,
              "jml_peg_kontrak" => 0,
              "jml_peg_alihdaya" => 0,
              "jml_rata_peg_masuk" => 0,
              "jns_industri" =>0,
              "nm_jns_industri" =>'',
              "file_protokol_wfh" =>NULL,
              "file_jadwal" =>NULL,
              "flag_dok_protokol" =>false,

          );
        }
        return response()->json(['status' => 200,'data' => $data]);
    }

    public function getDownloadFileProtokolWFH($kd_perusahaan,$filename)
    {
      //PDF file is stored under project/public/download/info.pdf
    //$protokol = TblProtokol::where('tbpt_mpt_id',$id_protokol)->where('tbpt_mc_id',$kd_perusahaan)->first();
      $file= storage_path() . "/app/public/data_wfh_wfo/".$kd_perusahaan."/". $filename;

    $headers = [
            'Content-Type' => 'application/pdf',
           ];

    if (!is_file($file)) {
       return response()->json(['status' => 404,'message' => 'Data Tidak Ada'])->setStatusCode(404);
      }
    $response = new BinaryFileResponse($file, 200 , $headers);

    return $response;
    //return response()->file($file);

    }
}
