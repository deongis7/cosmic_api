<?php
namespace App\Http\Controllers;
use App\Sosialisasi;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
class SosialisasiController extends Controller {
    
    public function __construct() {
    }
    
    public function getDataByMcid($id, $page) {
        $sosialisasiweek = DB::select("SELECT ts.ts_id, ts.ts_mc_id, ts.ts_nama_kegiatan, ts.ts_tanggal,
                ts.ts_mslk_id,  mslk.mslk_name, ts.ts_deskripsi, ts.ts_file1, ts.ts_file1_tumb, ts.ts_file2, ts.ts_file2_tumb
                FROM transaksi_sosialisasi ts
                LEFT JOIN master_sosialisasi_kategori mslk ON mslk.mslk_id=ts.ts_mslk_id
                WHERE ts_mc_id='$id' 
                AND ts_tanggal IN (
                	SELECT 
                	CAST(date_trunc('week', CURRENT_DATE) AS DATE) + i
                	FROM generate_series(0,4) i
                )");
        
        if(count($sosialisasiweek) > 0){  $week = true; }else{ $week = false; }
        if($page > 0){   $page=$page-1; }else{ $page=0; }
        
        $row = 10;
        $pageq = $page*$row;
        
        $sosialisasiall = DB::select("SELECT ts_id, ts_mc_id, ts_nama_kegiatan, ts_tanggal,
                ts.ts_mslk_id,  mslk.mslk_name, ts_deskripsi, ts_file1, ts_file1_tumb, ts_file2, ts_file2_tumb
                FROM transaksi_sosialisasi ts
                LEFT JOIN master_sosialisasi_kategori mslk ON mslk.mslk_id=ts.ts_mslk_id
                WHERE ts_mc_id='$id'
    	        ORDER BY ts_tanggal DESC");
        
        $sosialisasi = DB::select("SELECT ts_id, ts_mc_id, ts_nama_kegiatan, ts_tanggal,
               ts.ts_mslk_id,  mslk.mslk_name, ts_deskripsi, ts_file1, ts_file1_tumb, ts_file2, ts_file2_tumb
                FROM transaksi_sosialisasi ts
                LEFT JOIN master_sosialisasi_kategori mslk ON mslk.mslk_id=ts.ts_mslk_id
                WHERE ts_mc_id='$id'
    	        ORDER BY ts_tanggal DESC
                OFFSET $pageq LIMIT $row");
        
        $cntsosialisasiall = count($sosialisasiall);
        $pageend = ceil($cntsosialisasiall/$row);
        
        if (count($sosialisasi) > 0){
            foreach($sosialisasi as $sos){
                if($sos->ts_file1 !=NULL || $sos->ts_file1 !=''){
                    if (!file_exists(base_path("storage/app/public/sosialisasi/".$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file1))) {
                        $path_file404 = '/404/img404.jpg';
                        $filesos1 = $path_file404;
                        $filesos1_tumb = $path_file404;
                    }else{
                        $path_file1 = '/sosialisasi/'.$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file1;
                        $path_file1_tumb =  '/sosialisasi/'.$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file1_tumb;
                        $filesos1 = $path_file1;
                        $filesos1_tumb = $path_file1_tumb;
                    }
                }else{
                    $filesos1 = '/404/img404.jpg';
                    $filesos1_tumb = '/404/img404.jpg';
                }
                
                if($sos->ts_file2 !=NULL || $sos->ts_file2 !=''){
                    if (!file_exists(base_path("storage/app/public/sosialisasi/".$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file2))) {
                        $path_file404 = '/404/img404.jpg';
                        $filesos2 = $path_file404;
                        $filesos2_tumb = $path_file404;
                    }else{
                        $path_file2 = '/sosialisasi/'.$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file2;
                        $path_file2_tumb = '/sosialisasi/'.$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file2_tumb;
                        $filesos2 = $path_file2;
                        $filesos2_tumb = $path_file2_tumb;
                    }
                }else{
                    $filesos2 = '/404/img404.jpg';
                    $filesos2_tumb ='/404/img404.jpg';
                }
                
                $data[] = array(
                    "id" => $sos->ts_id,
                    "nama_kegiatan" => $sos->ts_nama_kegiatan,
                    "jenis_kegiatan" => $sos->ts_jenis_kegiatan,
                    //"jenis_kegiatan" => $sos->mslk_name,
                    //"jenis_kegiatan_id" => $sos->ts_mslk_id,
                    "deskripsi" => $sos->ts_deskripsi,
                    "tanggal" => $sos->ts_tanggal,
                    "file_1" => $filesos1,
                    "file_1_tumb" => $filesos1_tumb,
                    "file_2" => $filesos2,
                    "file_2_tumb" => $filesos2_tumb
                );
            }
        }else{
            $data = array();
        }
        return response()->json(['status' => 200, 'page_end'=> $pageend,
            'week' => $week, 'data' => $data]);
    }
    
    public function getDataById($id) {
        $sosialisasi = DB::select("SELECT ts_id, ts_mc_id, ts_nama_kegiatan, ts_tanggal,
                ts.ts_mslk_id,  mslk.mslk_name, ts_deskripsi, ts_file1, ts_file1_tumb, ts_file2, ts_file2_tumb
                FROM transaksi_sosialisasi ts
                LEFT JOIN master_sosialisasi_kategori mslk ON mslk.mslk_id=ts.ts_mslk_id
                WHERE ts_id='$id'");
        
        if(count($sosialisasi) > 0) {
            foreach($sosialisasi as $sos){
                if($sos->ts_file1 !=NULL || $sos->ts_file1 !=''){
                    if (!file_exists(base_path("storage/app/public/sosialisasi/".$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file1))) {
                        $path_file404 = '/404/img404.jpg';
                        $filesos1 = $path_file404;
                        $filesos1_tumb = $path_file404;
                    }else{
                        $path_file1 = '/sosialisasi/'.$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file1;
                        $path_file1_tumb =  '/sosialisasi/'.$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file1_tumb;
                        $filesos1 = $path_file1;
                        $filesos1_tumb = $path_file1_tumb;
                    }
                }else{
                    $filesos1 = '/404/img404.jpg';
                    $filesos1_tumb = '/404/img404.jpg';
                }
                
                if($sos->ts_file2 !=NULL || $sos->ts_file2 !=''){
                    if (!file_exists(base_path("storage/app/public/sosialisasi/".$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file2))) {
                        $path_file404 = '/404/img404.jpg';
                        $filesos2 = $path_file404;
                        $filesos2_tumb = $path_file404;
                    }else{
                        $path_file2 = '/sosialisasi/'.$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file2;
                        $path_file2_tumb = '/sosialisasi/'.$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file2_tumb;
                        $filesos2 = $path_file2;
                        $filesos2_tumb = $path_file2_tumb;
                    }
                }else{
                    $filesos2 = '/404/img404.jpg';
                    $filesos2_tumb ='/404/img404.jpg';
                }
                
                if(($filesos1==NULL && $filesos2==NULL) || ($filesos1=='' && $filesos2=='')){
                    $flag_foto = false;
                }else{
                    $flag_foto = true;
                }
                
                $data = array(
                    "nama_kegiatan" => $sos->ts_nama_kegiatan,
                    "jenis_kegiatan" => $sos->ts_jenis_kegiatan,
                    //"jenis_kegiatan" => $sos->mslk_name,
                    //"jenis_kegiatan_id" => $sos->ts_mslk_id,
                    "deskripsi" => $sos->ts_deskripsi,
                    "tanggal" => $sos->ts_tanggal,
                    "flag_foto" => $sos->ts_tanggal,
                    "file_1" => $filesos1,
                    "file_1_tumb" => $filesos1_tumb,
                    "file_2" => $filesos2,
                    "file_2_tumb" => $filesos2_tumb
                );
            }
        }else{
            $data = array();
        }
        return response()->json(['status' => 200,'data' => $data]);
    }
    
    public function uploadSosialisasiJSON(Request $request) {
        date_default_timezone_set('Asia/Jakarta');
        $this->validate($request, [
            'kd_perusahaan' => 'required',
            'nama_kegiatan' => 'required',
            'jenis_kegiatan' => 'required',
            'deskripsi' => 'required',
            'tanggal' => 'required',
            'file_sosialisasi1' =>'required',
        ]);
        
        $file1 = $request->file_sosialisasi1;
        $kd_perusahaan = $request->kd_perusahaan;
        $nama_kegiatan = $request->nama_kegiatan;
        //$dataSosialisasi->ts_mslk_id = $jenis_kegiatan;
        $dataSosialisasi->ts_jenis_kegiatan = $jenis_kegiatan;
        $deskripsi = $request->deskripsi;
        $tgl = strtotime($request->tanggal);
        $tanggal = date('Y-m-d',$tgl);
        $user_id = $request->user_id;    
        //var_dump($tanggal);die;
        
        if(!Storage::exists('/app/public/sosialisasi/'.$kd_perusahaan.'/'.$tanggal)) {
            Storage::disk('public')->makeDirectory('/sosialisasi/'.$kd_perusahaan.'/'.$tanggal);
        }
      
        //$destinationPath = base_path("storage\app\public\sosialisasi/").$kd_perusahaan.'/'.$tanggal;
        $destinationPath = storage_path().'/app/public/sosialisasi/' .$kd_perusahaan.'/'.$tanggal;
      
        $name1 = NULL;
        $name1_tumb = NULL;
        if ($request->file_sosialisasi1 != null || $request->file_sosialisasi1 != '') {
            $img1 = explode(',', $file1);
            $image1 = $img1[1];
            $filedecode1 = base64_decode($image1);
            $name1 = round(microtime(true) * 1000).'.jpg';
            $name1_tumb = round(microtime(true) * 1000).'_tumb.jpg';
            
            Image::make($filedecode1)->resize(700, NULL, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$name1);
            
            Image::make($filedecode1)->resize(50, NULL, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$name1_tumb);
        }
        
        $name2 = NULL;
        $name2_tumb = NULL;
        if(isset($request->file_sosialisasi2)){
            $file2 = $request->file_sosialisasi2;
            if ($request->file_sosialisasi2 != null || $request->file_sosialisasi2 != '') {
                $img2 = explode(',', $file2);
                $image2 = $img1[1];
                $filedecode2 = base64_decode($image2);
                $name2 = round(microtime(true)*1000).'.jpg';
                $name2_tumb = round(microtime(true) * 1000).'_tumb.jpg';
                
                Image::make($filedecode2)->resize(700, NULL, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath.'/'.$name2);
                
                Image::make($filedecode2)->resize(50, NULL, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath.'/'.$name2_tumb);
            }
        }
        
        $dataSosialisasi = new Sosialisasi();
        $dataSosialisasi->ts_mc_id = $kd_perusahaan;
        $dataSosialisasi->ts_nama_kegiatan = $nama_kegiatan;
        //$dataSosialisasi->ts_mslk_id = $jenis_kegiatan;
        $dataSosialisasi->ts_jenis_kegiatan = $jenis_kegiatan;
        $dataSosialisasi->ts_deskripsi = $deskripsi;
        $dataSosialisasi->ts_tanggal = $tanggal;
        $dataSosialisasi->ts_file1 = $name1;
        $dataSosialisasi->ts_file1_tumb = $name1_tumb;
        $dataSosialisasi->ts_date_insert = date('Y-m-d H:i:s');
        $dataSosialisasi->ts_user_insert = Auth::guard('api')->user()->id;
        $dataSosialisasi->save();
        
        if($dataSosialisasi->save()) {
            return response()->json(['status' => 200,'message' => 'Data Sosialisasi Berhasil diImport']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Sosialisasi Gagal diImport'])->setStatusCode(500);
        }
    }
    
    public function deleteSosialisasi($id){
        $data = Sosialisasi::where('ts_id',$id)->first();
        $data->delete();
        
        if($data->delete()===NULL){
            $file1 = storage_path('app/public/sosialisasi/'.$data->ts_mc_id.'/'.$data->ts_tanggal.'/'.$data->ts_file1);
            if(is_file($file1)){
                unlink(storage_path('app/public/sosialisasi/'.$data->ts_mc_id.'/'.$data->ts_tanggal.'/'.$data->ts_file1));
            }
            $file1_tumb = storage_path('app/public/sosialisasi/'.$data->ts_mc_id.'/'.$data->ts_tanggal.'/'.$data->ts_file1_tumb);
            if(is_file($file1_tumb)){
                unlink(storage_path('app/public/sosialisasi/'.$data->ts_mc_id.'/'.$data->ts_tanggal.'/'.$data->ts_file1_tumb));
            }
            
            $file2 = storage_path('app/public/sosialisasi/'.$data->ts_mc_id.'/'.$data->ts_tanggal.'/'.$data->ts_file2);
            if(is_file($file2)){
                unlink(storage_path('app/public/sosialisasi/'.$data->ts_mc_id.'/'.$data->ts_tanggal.'/'.$data->ts_file2));
            }
            $file2_tumb = storage_path('app/public/sosialisasi/'.$data->ts_mc_id.'/'.$data->ts_tanggal.'/'.$data->ts_file2_tumb);
            if(is_file($file2_tumb)){
                unlink(storage_path('app/public/sosialisasi/'.$data->ts_mc_id.'/'.$data->ts_tanggal.'/'.$data->ts_file2_tumb));
            }
            
            return response()->json(['status' => 200,'message' => 'Data Sosialisasi Berhasil diDelete']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Sosialisasi Gagal diDelete'])->setStatusCode(500);
        }
    }
    
    public function updateSosialisasiJSON($id, Request $request) {
        date_default_timezone_set('Asia/Jakarta');
        $this->validate($request, [
            'kd_perusahaan' => 'required',
            'nama_kegiatan' => 'required',
            'jenis_kegiatan' => 'required',
            'deskripsi' => 'required',
            'tanggal' => 'required',
        ]);
        $r_nama_kegiatan = $request->nama_kegiatan;
        $r_jenis_kegiatan = $request->jenis_kegiatan;
        $r_deskripsi = $request->deskripsi;
        $r_file1 = $request->file_sosialisasi1;
        $r_file2 = $request->file_sosialisasi2;
        $r_tgl = strtotime($request->tanggal);
        $r_tanggal = date('Y-m-d',$r_tgl);
        
        $dataSosialisasi = Sosialisasi::find($id);
        //var_dump($dataSosialisasi);die;
        $kd_perusahaan = $dataSosialisasi->ts_mc_id;
        $tanggal = $dataSosialisasi->ts_tanggal;
        $filex1 = $dataSosialisasi->ts_file1;
        $filex1_tumb = $dataSosialisasi->ts_file1_tumb;
        $filex2 = $dataSosialisasi->ts_file2;
        $filex2_tumb = $dataSosialisasi->ts_file2_tumb;
        
        //$destinationPath = base_path("storage\app\public\sosialisasi/").$kd_perusahaan.'/'.$tanggal;
        $destinationPath = storage_path().'/app/public/sosialisasi/' .$kd_perusahaan.'/'.$tanggal;
        
        $name1 = NULL;
        $name1_tumb = NULL;
        if ($request->file_sosialisasi1 != null || $request->file_sosialisasi1 != '') {
            if($filex1!=NULL && file_exists(storage_path().'/app/public/sosialisasi/' .$kd_perusahaan.'/'.$tanggal.'/'.$filex1)){
                unlink(storage_path().'/app/public/sosialisasi/' .$kd_perusahaan.'/'.$tanggal.'/'.$filex1);
            }
            
            if($filex1_tumb!=NULL && file_exists(storage_path().'/app/public/sosialisasi/' .$kd_perusahaan.'/'.$tanggal.'/'.$filex1_tumb)){
                unlink(storage_path().'/app/public/sosialisasi/' .$kd_perusahaan.'/'.$tanggal.'/'.$filex1_tumb);
            }
            
            $img1 = explode(',', $r_file1);
            $image1 = $img1[1];
            $filedecode1 = base64_decode($image1);
            $name1 = round(microtime(true) * 1000).'.jpg';
            $name1_tumb = round(microtime(true) * 1000).'_tumb.jpg';
            
            Image::make($filedecode1)->resize(700, NULL, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$name1);
            
            Image::make($filedecode1)->resize(50, NULL, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$name1_tumb);
        }
        
        $name2 = NULL;
        $name2_tumb = NULL;
        if(isset($request->file_sosialisasi2)){
            if($filex2!=NULL && file_exists(storage_path().'/app/public/sosialisasi/' .$kd_perusahaan.'/'.$tanggal.'/'.$filex2)){
                unlink(storage_path().'/app/public/sosialisasi/' .$kd_perusahaan.'/'.$tanggal.'/'.$filex2);
            }
            if($filex2_tumb!=NULL && file_exists(storage_path().'/app/public/sosialisasi/' .$kd_perusahaan.'/'.$tanggal.'/'.$filex2_tumb)){
                unlink(storage_path().'/app/public/sosialisasi/' .$kd_perusahaan.'/'.$tanggal.'/'.$filex2_tumb);
            }
            if ($request->file_sosialisasi2 != null || $request->file_sosialisasi2 != '') {
                $img2 = explode(',', $r_file2);
                $image2 = $img1[1];
                $filedecode2 = base64_decode($image2);
                $name2 = round(microtime(true)*1000).'.jpg';
                $name2_tumb = round(microtime(true) * 1000).'_tumb.jpg';
                
                Image::make($filedecode2)->resize(700, NULL, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath.'/'.$name2);
                
                Image::make($filedecode2)->resize(50, NULL, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath.'/'.$name2_tumb);
            }
        }
        
	    $dataSosialisasi->ts_nama_kegiatan = $r_nama_kegiatan;
	    //$dataSosialisasi->ts_mslk_id = $jenis_kegiatan;
	    $dataSosialisasi->ts_jenis_kegiatan = $jenis_kegiatan;
	    $dataSosialisasi->ts_deskripsi = $r_deskripsi;
	    $dataSosialisasi->ts_tanggal = $r_tanggal;
        $dataSosialisasi->ts_file1 = $name1;
        $dataSosialisasi->ts_file2 = $name2;
        $dataSosialisasi->ts_file1_tumb = $name1_tumb;
        $dataSosialisasi->ts_file2_tumb = $name2_tumb;
        $dataSosialisasi->ts_date_update = date('Y-m-d H:i:s');
        $dataSosialisasi->ts_user_update = Auth::guard('api')->user()->id;
        $dataSosialisasi->save();
        
        if($dataSosialisasi->save()) {
            return response()->json(['status' => 200,'message' => 'Data Sosialisasi Berhasil diUpdate']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Sosialisasi Gagal diUpdate'])->setStatusCode(500);
        }
    }
}
