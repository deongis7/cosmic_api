<?php
namespace App\Http\Controllers;
use App\Sosialisasi;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
class SosialisasiController extends Controller {
    
    public function __construct() {
    }
    
    public function getDataAllByMcid($id) {
        $sosialisasi = Sosialisasi::where('ts_mc_id', $id)->get();

        if(count($sosialisasi) > 0) {
            foreach($sosialisasi as $sos){
                $data = array(
                    "nama_kegiatan" => $sos->ts_nama_kegiatan,
                    "tanggal" => $sos->ts_tanggal,
                );
            }
        }else{
            $data = array();
        }
        return response()->json(['status' => 200,'data' => $data]);
    }
    
    public function getDataById($tanggal) {
        $sosialisasi = Sosialisasi::where('ts_tanggal',$tanggal)->get();
       
        if(count($sosialisasi) > 0) {
            foreach($sosialisasi as $sos){
                $path_file1 =  base_path("storage\app\public\sosialisasi/").$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file1;
                $type1 = pathinfo($path_file1, PATHINFO_EXTENSION);
                $data_file1 = file_get_contents($path_file1);
                $filesos1 = base64_encode($data_file1);
    
                $path_file2 =  base_path("storage\app\public\sosialisasi/").$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file2;
                $type2 = pathinfo($path_file2, PATHINFO_EXTENSION);
                $data_file2 = file_get_contents($path_file2);
                $filesos2 = base64_encode($data_file2);
                
                $data = array(
                    "nama_kegiatan" => $sos->ts_nama_kegiatan,
                    "tanggal" => $sos->ts_tanggal,
                    "file_1" => $filesos1,
                    "file_2" => $filesos2,
                );
            }
        }else{
            $data = array();
        }
        return response()->json(['status' => 200,'data' => $data]);
    }
    
    public function uploadSosialisasiJSON(Request $request) {
        $this->validate($request, [
            'kd_perusahaan' => 'required',
            'nama_kegiatan' => 'required',
            'tanggal' => 'required',
            'user_id' => 'required',
            'file_sosialisasi1' => 'required',
        ]);
        
        $file1 = $request->file_sosialisasi1;
        $kd_perusahaan = $request->kd_perusahaan;
        $nama_kegiatan = $request->nama_kegiatan;
        $tanggal = $request->tanggal;
        $user_id = $request->user_id;

        if(!Storage::exists('/public/sosialisasi/'.$kd_perusahaan.'/'.$tanggal)) {
            Storage::disk('public')->makeDirectory('/sosialisasi/'.$kd_perusahaan.'/'.$tanggal);
        }
  
        $destinationPath = base_path("storage\app\public\sosialisasi/").$kd_perusahaan.'/'.$tanggal;

        if ($request->file_sosialisasi1 != null || $request->file_sosialisasi1 != '') {
            $img1 = explode(',', $file1);
            $image1 = $img1[1];
            $filedecode1 = base64_decode($image1);
            $name1 = round(microtime(true) * 1000).'.jpg';

            Image::make($filedecode1)->resize(50, NULL, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$name1);
        }
        
        $name2 = NULL;
        if(isset($request->file_sosialisasi2)){
            $file2 = $request->file_sosialisasi2;
            if ($request->file_sosialisasi2 != null || $request->file_sosialisasi2 != '') {
                $img2 = explode(',', $file2);
                $image2 = $img1[1];
                $filedecode2 = base64_decode($image2);
                $name2 = round(microtime(true)*1000).'.jpg';
                          
                Image::make($filedecode2)->resize(50, NULL, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath.'/'.$name2);
            }
        }
        
        $dataSosialisasi= Sosialisasi::updateOrCreate(
            ['ts_mc_id' => $kd_perusahaan, 'ts_nama_kegiatan' => $nama_kegiatan,          
             'ts_tanggal'=> $tanggal, 'ts_file1' => $name1],
            ['ts_file2' => $name2, 'ts_user_insert' => $user_id]);

        if($dataSosialisasi) {
            return response()->json(['status' => 200,'message' => 'Data Berhasil di Import']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Gagal di Import'])->setStatusCode(500);
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
            
            $file2 = storage_path('app/public/sosialisasi/'.$data->ts_mc_id.'/'.$data->ts_tanggal.'/'.$data->ts_file2);
            if(is_file($file2)){
                unlink(storage_path('app/public/sosialisasi/'.$data->ts_mc_id.'/'.$data->ts_tanggal.'/'.$data->ts_file2));
            }

            return response('Berhasil Menghapus Data');
        }else{
            return response('Gagal Menghapus Data');
        }
    }
    
    public function updateSosialisasiJSON($id, Request $request) {
        $this->validate($request, [
            'nama_kegiatan' => 'required',
            'user_id' => 'required',
            'file_sosialisasi1' => 'required',
        ]);
        $nama_kegiatan = $request->nama_kegiatan;
        $user_id = $request->user_id;
        $file1 = $request->file_sosialisasi1;
        $file2 = $request->file_sosialisasi2;
        
        $dataSosialisasi = Sosialisasi::find($id);
        $kd_perusahaan = $dataSosialisasi->ts_mc_id;
        $tanggal = $dataSosialisasi->ts_tanggal;
        $filex1 = $dataSosialisasi->ts_file1;
        $filex2 = $dataSosialisasi->ts_file2;
        
        $destinationPath = base_path("storage\app\public\sosialisasi/").$kd_perusahaan.'/'.$tanggal;
        
        $name1 = NULL;
        if ($request->file_sosialisasi1 != null || $request->file_sosialisasi1 != '') {
            if($filex1!=NULL){
                unlink(storage_path('app/public/sosialisasi/'.$kd_perusahaan.'/'.$tanggal.'/'.$filex1));
            }
            $img1 = explode(',', $file1);
            $image1 = $img1[1];
            $filedecode1 = base64_decode($image1);
            $name1 = round(microtime(true) * 1000).'.jpg';
            
            Image::make($filedecode1)->resize(50, NULL, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$name1);
        }
        
        $name2 = NULL;
        if(isset($request->file_sosialisasi2)){
            if($filex2!=NULL){
                unlink(storage_path('app/public/sosialisasi/'.$kd_perusahaan.'/'.$tanggal.'/'.$filex2));
            }
            if ($request->file_sosialisasi2 != null || $request->file_sosialisasi2 != '') {
                $img2 = explode(',', $file2);
                $image2 = $img1[1];
                $filedecode2 = base64_decode($image2);
                $name2 = round(microtime(true)*1000).'.jpg';
                
                Image::make($filedecode2)->resize(50, NULL, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath.'/'.$name2);
            }
        }

        $dataSosialisasi->ts_file1 = $name1;
        $dataSosialisasi->ts_file2 = $name2;
        $dataSosialisasi->save();
        if($dataSosialisasi) {
            return response()->json(['status' => 200,'message' => 'Data Berhasil di Update']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Gagal di Update'])->setStatusCode(500);
        }
    }
}
