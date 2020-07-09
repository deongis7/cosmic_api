<?php
namespace App\Http\Controllers;
use App\Sosialisasi;
use App\User;
use App\UserGroup;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SosialisasiController extends Controller {
    
    public function __construct() {

    }
    
    public function getDataAllByMcid($id) {
        $sosialisasi = Sosialisasi::where('ts_mc_id', $id)->get();
       
        $data[] = array();
        if(count($sosialisasi) > 0) {
            foreach($sosialisasi as $sos){
                $data[] = array(
                    "nama_kegiatan" => $sos->ts_nama_kegiatan,
                    "tanggal" => $sos->ts_tanggal,
                );
            }
        }else{
            
        }
        return response()->json(['status' => 200,'data' => $data]);
    }
    
    public function getDataById($id) {
        $sosialisasi = Sosialisasi::where('ts_id',$id)->get();
        
        foreach($sosialisasi as $sos){
            $path_file1 = $_SERVER['DOCUMENT_ROOT'].'/cosmic_dev/uploads/sosialisasi/'.$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file1;
            $type1 = pathinfo($path_file1, PATHINFO_EXTENSION);
            $data_file1 = file_get_contents($path_file1);
            $filesos1 = base64_encode($data_file1);

            $path_file2 = $_SERVER['DOCUMENT_ROOT'].'/cosmic_dev/uploads/sosialisasi/'.$sos->ts_mc_id.'/'.$sos->ts_tanggal.'/'.$sos->ts_file2;
            $type2 = pathinfo($path_file2, PATHINFO_EXTENSION);
            $data_file2 = file_get_contents($path_file2);
            $filesos2 = base64_encode($data_file2);
            
            $data[] = array(
                "nama_kegiatan" => $sos->ts_nama_kegiatan,
                "tanggal" => $sos->ts_tanggal,
                "file_1" => $filesos1,
                "file_2" => $filesos2,
            );
        }
        return response()->json(['status' => 200,'data' => $data]);
    }
    
    public function uploadSosialisasiJSON(Request $request) {
        $this->validate($request, [
            'file_sosialisasi1' => 'required',
            'kd_perusahaan' => 'required',
            'nama_kegiatan' => 'required',
            'tanggal' => 'required',
            'user_id' => 'required',
        ]);
        
        $file1 = $request->file_sosialisasi1;
        $file2 = $request->file_sosialisasi2;
        $kd_perusahaan = $request->kd_perusahaan;
        $nama_kegiatan = $request->nama_kegiatan;
        $tanggal = $request->tanggal;
        $user_id = $request->user_id;
        
        //dd($request);
        if ($request->file_sosialisasi1 != null || $request->file_sosialisasi1 != '') {
            $img1 = explode(',', $file1);
            $image1 = $img1[1];
//             $type = explode('/', $img[0]);
//             $extention = explode(';', $type[1])[0];
            $filedecode1 = base64_decode($image1);
            $name1 = round(microtime(true) * 1000).'.jpg';
            Storage::disk('public')->put('sosialisasi/'.$name1, base64_decode($image1));
        }
        
        $name2 = NULL;
        if(isset($request->file_sosialisasi2)){
            if ($request->file_sosialisasi2 != null || $request->file_sosialisasi2 != '') {
                $img2 = explode(',', $file2);
                $image2 = $img2[1];
                //             $type = explode('/', $img[0]);
                //             $extention = explode(';', $type[1])[0];
                $filedecode2 = base64_decode($image2);
                $name2 = round(microtime(true) * 1000).'.jpg';
                Storage::disk('public')->put('sosialisasi/'.$name2, base64_decode($image2));
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
}
