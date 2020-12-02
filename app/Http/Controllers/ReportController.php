<?php
namespace App\Http\Controllers;
use App\Report;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
class ReportController extends Controller {
    
    public function __construct() {
    }
    
    public function getDashboardReportByMcid($id){
        //$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashreportbumn_head_".$id, 15 * 60, function()use($id) {
            $data = array();
            $dashreport_head = DB::select("select * from dashboard_reportcard_bymcid('$id')");
                
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
    
    public function getDataByMcid($id, $page) {
        if($page > 0){   $page=$page-1; }else{ $page=0; }
        
        $row = 10;
        $pageq = $page*$row;
        
        $reportall = DB::select("SELECT tr.*, mc.mc_id, mc.mc_name, mpm.mpm_id, mpm.mpm_name,
        mpml.mpml_id, mpml.mpml_name
				FROM transaksi_report tr
				INNER JOIN master_perimeter_level mpml ON mpml.mpml_id=tr.tr_mpml_id
				INNER JOIN master_perimeter mpm ON mpm.mpm_id=mpml.mpml_mpm_id
				INNER JOIN master_company mc ON mc.mc_id=mpm.mpm_mc_id
				INNER JOIN master_sektor ms ON ms.ms_id=mc.mc_msc_id
				WHERE mc.mc_level = 1 
				AND ms.ms_type = 'CCOVID' 
				AND mc.mc_id='$id'
    	        ORDER BY tr_id DESC");
        
        $report = DB::select("SELECT tr.*, mc.mc_id, mc.mc_name, mpm.mpm_id, mpm.mpm_name,
        mpml.mpml_id, mpml.mpml_name
				FROM transaksi_report tr
				INNER JOIN master_perimeter_level mpml ON mpml.mpml_id=tr.tr_mpml_id
				INNER JOIN master_perimeter mpm ON mpm.mpm_id=mpml.mpml_mpm_id
				INNER JOIN master_company mc ON mc.mc_id=mpm.mpm_mc_id
				INNER JOIN master_sektor ms ON ms.ms_id=mc.mc_msc_id
				WHERE mc.mc_level = 1 
				AND ms.ms_type = 'CCOVID' 
				AND mc.mc_id='$id'
    	        ORDER BY tr_id DESC
                OFFSET $pageq LIMIT $row");
        
        $cntreportall = count($reportall);
        $pageend = ceil($reportall/$row);
        
        if (count($report) > 0){
            foreach($report as $rep){
                if($rep->tr_file1 !=NULL || $rep->tr_file1 !=''){
                    if (!file_exists(base_path("storage/app/public/report_protokol/".$rep->tr_file1))) {
                        $path_file404 = '/404/img404.jpg';
                        $filerep1 = $path_file404;
                    }else{
                        $path_file1 = '/report_protokol/'.$rep->tr_file1;
                        $filerep1 = $path_file1;
                    }
                }else{
                    $filerep1 = '/404/img404.jpg';
                }
                
                if($rep->tr_file2 !=NULL || $rep->tr_file2 !=''){
                    if (!file_exists(base_path("storage/app/public/report_protokol/".$rep->tr_file2))) {
                        $path_file404 = '/404/img404.jpg';
                        $filesos2 = $path_file404;
                        $filesos2_tumb = $path_file404;
                    }else{
                        $path_file2 = '/report_protokol/'.$rep->tr_file2;
                        $filerep2 = $path_file2;
                    }
                }else{
                    $filerep2 = '/404/img404.jpg';
                }
                
                $data[] = array(
                    "id" => $sos->ts_id,
                    "nama_kegiatan" => $sos->ts_nama_kegiatan,
                    "file_1" => $filesos1,
                    "file_2" => $filesos2,
                );
            }
        }else{
            $data = array();
        }
        return response()->json(['status' => 200, 'page_end'=> $pageend,
            'week' => $week, 'data' => $data]);
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
        
        if(!Storage::exists('/app/public/report_protokol/')) {
            Storage::disk('public')->makeDirectory('/report_protokol/');
        }
        
        $destinationPath = storage_path().'/app/public/report_protokol/';
        
        $name1 = $filex1;
        if(isset($request->file_report1)){
            if ($request->file_report1 != null || $request->file_report1 != '') {
                if($filex1!=NULL && file_exists(storage_path().'/app/public/report_protokol/' .$filex1)){
                    unlink(storage_path().'/app/public/report_protokol/'.$filex1);
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
                if($filex2!=NULL && file_exists(storage_path().'/app/public/report_protokol/' .$filex2)){
                    unlink(storage_path().'/app/public/report_protokol/'.$filex2);
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
    }
    
    public function updateReportJSON($id, Request $request) {
        date_default_timezone_set('Asia/Jakarta');
        $penanggungjawab = $request->penanggungjawab;
        $ceklis = $request->ceklis;
        $r_file1 = $request->file_report1;
        $r_file2 = $request->file_report2;
        
        $dataReport = Report::find($id);
        $filex1 = $dataReport->tr_tl_file1;
        $filex2 = $dataReport->tr_tl_file2;
        
        if(!Storage::exists('/app/public/report_protokol/')) {
            Storage::disk('public')->makeDirectory('/report_protokol/');
        }
        
        $destinationPath = storage_path().'/app/public/report_protokol/';
        
        $name1 = $filex1;
        if(isset($request->file_report1)){
            if ($request->file_report1 != null || $request->file_report1 != '') {
                if($filex1!=NULL && file_exists(storage_path().'/app/public/report_protokol/' .$filex1)){
                    unlink(storage_path().'/app/public/report_protokol/'.$filex1);
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
                if($filex2!=NULL && file_exists(storage_path().'/app/public/report_protokol/' .$filex2)){
                    unlink(storage_path().'/app/public/report_protokol/'.$filex1);
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
    }
    
    public function getDataById($id) {
        $report = DB::select("SELECT tr.*, mc.mc_id, mc.mc_name, mpm.mpm_id, mpm.mpm_name,
        mpml.mpml_id, mpml.mpml_name,
        CASE
            WHEN (tr.tr_close = 1) THEN 'Selesai diproses'::text
            ELSE 'Belum diproses'::text
        END AS status,
        to_char((tr.tr_date_insert)::timestamp with time zone, 'DD-MM-YYYY hh:mm:ss'::text) AS date_insert
				FROM transaksi_report tr
				INNER JOIN master_perimeter_level mpml ON mpml.mpml_id=tr.tr_mpml_id
				INNER JOIN master_perimeter mpm ON mpm.mpm_id=mpml.mpml_mpm_id
				INNER JOIN master_company mc ON mc.mc_id=mpm.mpm_mc_id
				INNER JOIN master_sektor ms ON ms.ms_id=mc.mc_msc_id
				WHERE mc.mc_level = 1
				AND ms.ms_type = 'CCOVID'
				AND tr.tr_id=$id
    	        ORDER BY tr_id DESC");
        
        if(count($report) > 0) {
            foreach($report as $rep){
                if($rep->tr_tl_file1 !=NULL || $rep->tr_tl_file1 !=''){
                    if (!file_exists(base_path("storage/app/public/report_protokol/".$rep->tr_tl_file1))) {
                        $path_file404 = '/404/img404.jpg';
                        $filerep1 = $path_file404;
                    }else{
                        $path_file1 = '/report_protokol/'.$rep->tr_tl_file1;
                        $filerep1 = $path_file1;
                    }
                }else{
                    $filerep1 = '/404/img404.jpg';
                }
                
                if($rep->tr_tl_file2 !=NULL || $rep->tr_tl_file2 !=''){
                    if (!file_exists(base_path("storage/app/public/report_protokol/".$rep->tr_tl_file2))) {
                        $path_file404 = '/404/img404.jpg';
                        $filerep2 = $path_file404;
                    }else{
                        $path_file2 = '/report_protokol/'.$rep->tr_tl_file2;
                        $filerep2 = $path_file2;
                    }
                }else{
                    $filerep2 = '/404/img404.jpg';
                }
                
                if(($filerep1==NULL && $filerep2==NULL) || ($filerep1=='' && $filerep2=='')){
                    $flag_foto = false;
                }else{
                    $flag_foto = true;
                }
                
                $data = array(
                    "id" => $rep->tr_id,
                    "laporan" => $rep->tr_laporan,
                    "no_laporan" => $rep->tr_no,
                    "perimeter" => $rep->mpm_name,
                    "perimeter_level" => $rep->mpml_name,
                    "tgl_lapor" => $rep->date_insert,
                    "status" => $rep->status,
                    "penanggungjawab" => $rep->tr_penanggungjawab,
                    "close" => $rep->tr_close,
                    "img_tl_1" => $filerep1,
                    "img_tl_2" => $filerep2
                );
            }
        }else{
            $data = array();
        }
        return response()->json(['status' => 200,'data' => $data]);
    }
}