<?php
namespace App\Http\Controllers;
use App\Terpapar;
use App\User;
use App\UserGroup;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\TrnKasus;

class TerpaparController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

	public function index() {

	}
	
	public function show($id) {

	}
	
	public function store (Request $request) {

	}

	public function getCountData() {

	}	
	
	public function getDataHome($id) {
	    $terpapar = DB::select("SELECT msk_id, msk_name2,
                    CASE WHEN jml IS NULL THEN 0 ELSE jml END AS jml
                    FROM master_status_kasus msk
                    LEFT JOIN (
                        SELECT tk_msk_id, COUNT(tk_msk_id) jml 
                        FROM transaksi_kasus
                        WHERE tk_mc_id='$id' AND tk_msk_id!=3
                        GROUP BY tk_msk_id
                        UNION ALL
                        SELECT 3, COUNT(tk_msk_id) jml 
                        FROM transaksi_kasus
                        WHERE tk_mc_id='$id' AND tk_msk_id IN (3,4,5)
                    ) tk on tk.tk_msk_id=msk.msk_id
                    ORDER BY msk_id");

	    foreach($terpapar as $tpp){
	        $data[] = array(
	            "id_kasus" => $tpp->msk_id,
	            "jenis_kasus" => $tpp->msk_name2,
	            "jumlah" => $tpp->jml
	        );
	    }
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}
	
	public function getDataHomeAll() {
	   $terpapar = DB::select("SELECT msk_id, msk_name2,
                    CASE WHEN jml IS NULL THEN 0 ELSE jml END AS jml
                    FROM master_status_kasus msk
                    LEFT JOIN (
                        SELECT tk_msk_id, COUNT(tk_msk_id) jml
                        FROM transaksi_kasus
                        WHERE tk_msk_id!=3
                        GROUP BY tk_msk_id
                        UNION ALL
                        SELECT 3, COUNT(tk_msk_id) jml
                        FROM transaksi_kasus
                        WHERE tk_msk_id IN (3,4,5)
                    ) tk on tk.tk_msk_id=msk.msk_id
                    ORDER BY msk_id");
	    
	    foreach($terpapar as $tpp){
	        $data[] = array(
	            "id_kasus" => $tpp->msk_id,
	            "jenis_kasus" => $tpp->msk_name2,
	            "jumlah" => $tpp->jml
	        );
	    }
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}
	
// 	public function getDatadetail($id, $page) {
// 	    if($page > 0){
// 	        $page=$page-1;
// 	    }else{
// 	        $page=0;
// 	    }
	    
// 	    $row = 10;
// 	    $pageq = $page*$row;
	    
// 	    $terpaparall = DB::select("SELECT tk_id, tk_mc_id, tk_nama, mc_name, msk_name, msk_name2, 
//                     msp_name, mpro_name, mkab_name, tk_tempat_perawatan
//                     FROM transaksi_kasus tk
//                     INNER JOIN master_company mc ON mc.mc_id=tk.tk_mc_id
//                     INNER JOIN master_status_kasus msk ON msk.msk_id=tk.tk_msk_id
//                     LEFT JOIN master_status_pegawai msp ON msp.msp_id=tk.tk_msp_id
//                     LEFT JOIN master_provinsi mpro ON mpro.mpro_id=tk.tk_mpro_id
//                     LEFT JOIN master_kabupaten mkab ON mkab.mkab_id=tk.tk_mkab_id AND mkab.mkab_mpro_id=mpro.mpro_id
//                     WHERE tk_mc_id='$id' ORDER BY tk_id");
	    
// 	    $terpapar = DB::select("SELECT tk_id, tk_mc_id, tk_nama, mc_name, msk_name, msk_name2, 
//                     msp_name, mpro_name, mkab_name, tk_tempat_perawatan
//                     FROM transaksi_kasus tk
//                     INNER JOIN master_company mc ON mc.mc_id=tk.tk_mc_id
//                     INNER JOIN master_status_kasus msk ON msk.msk_id=tk.tk_msk_id
//                     LEFT JOIN master_status_pegawai msp ON msp.msp_id=tk.tk_msp_id
//                     LEFT JOIN master_provinsi mpro ON mpro.mpro_id=tk.tk_mpro_id
//                     LEFT JOIN master_kabupaten mkab ON mkab.mkab_id=tk.tk_mkab_id AND mkab.mkab_mpro_id=mpro.mpro_id
//                     WHERE tk_mc_id='$id' ORDER BY tk_id
// 					OFFSET $pageq LIMIT $row");
	    
// 	    $cntterpaparall = count($terpaparall);
//         $pageend = ceil($cntterpaparall/$row);
	
// 	    if (count($terpapar) > 0){
//     	    foreach($terpapar as $tpp){
//     	        $data[] = array(
//     	            "id" => $tpp->tk_id,
//     	            "kd_perusahaan" => $tpp->tk_mc_id,
//     	            "perusahaan" => $tpp->mc_name,
//     	            "nama_pasien" => $tpp->tk_nama,
//     	            "jenis_kasus" => $tpp->msk_name2,
//     	            "jenis_kasus2" => $tpp->msk_name,
//     	            "status_pegawai" => $tpp->msp_name,
//     	            "provinsi" => $tpp->mpro_name,
//     	            "kabupaten" => $tpp->mkab_name,
//     	            "tempat_perawatan" => $tpp->tk_tempat_perawatan,
//     	        );
//     	    }
// 	    }else{
// 	        $data = array();
// 	    }
// 	    return response()->json(['status' => 200, 'page_end'=>$pageend, 'data' => $data]);
// 	}
	
	public function getDatadetail($id, $page, $search) {
	    if($page > 0){
	        $page=$page-1;
	    }else{
	        $page=0;
	    }
	    
	    $row = 10;
	    $pageq = $page*$row;
	    
	    $terpaparall = DB::select("SELECT tk_id, tk_mc_id, tk_nama, mc_name, msk_name, msk_name2,
                    msp_name, mpro_name, mkab_name, tk_tempat_perawatan
                    FROM transaksi_kasus tk
                    INNER JOIN master_company mc ON mc.mc_id=tk.tk_mc_id
                    INNER JOIN master_status_kasus msk ON msk.msk_id=tk.tk_msk_id
                    LEFT JOIN master_status_pegawai msp ON msp.msp_id=tk.tk_msp_id
                    LEFT JOIN master_provinsi mpro ON mpro.mpro_id=tk.tk_mpro_id
                    LEFT JOIN master_kabupaten mkab ON mkab.mkab_id=tk.tk_mkab_id AND mkab.mkab_mpro_id=mpro.mpro_id
                    WHERE tk_mc_id='$id' AND LOWER(tk_nama) LIKE LOWER('%$search%')
                    ORDER BY tk_id");
	    
	    $terpapar = DB::select("SELECT tk_id, tk_mc_id, tk_nama, mc_name, msk_name, msk_name2,
                    msp_name, mpro_name, mkab_name, tk_tempat_perawatan
                    FROM transaksi_kasus tk
                    INNER JOIN master_company mc ON mc.mc_id=tk.tk_mc_id
                    INNER JOIN master_status_kasus msk ON msk.msk_id=tk.tk_msk_id
                    LEFT JOIN master_status_pegawai msp ON msp.msp_id=tk.tk_msp_id
                    LEFT JOIN master_provinsi mpro ON mpro.mpro_id=tk.tk_mpro_id
                    LEFT JOIN master_kabupaten mkab ON mkab.mkab_id=tk.tk_mkab_id AND mkab.mkab_mpro_id=mpro.mpro_id
                    WHERE tk_mc_id='$id' AND LOWER(tk_nama) LIKE LOWER('%$search%')
                    ORDER BY tk_id
					OFFSET $pageq LIMIT $row");
	    
	    $cntterpaparall = count($terpaparall);
	    $pageend = ceil($cntterpaparall/$row);
	    
	    if (count($terpapar) > 0){
	        foreach($terpapar as $tpp){
	            $data[] = array(
	                "id" => $tpp->tk_id,
	                "kd_perusahaan" => $tpp->tk_mc_id,
	                "perusahaan" => $tpp->mc_name,
	                "nama_pasien" => $tpp->tk_nama,
	                "jenis_kasus" => $tpp->msk_name2,
	                "jenis_kasus2" => $tpp->msk_name,
	                "status_pegawai" => $tpp->msp_name,
	                "provinsi" => $tpp->mpro_name,
	                "kabupaten" => $tpp->mkab_name,
	                "tempat_perawatan" => $tpp->tk_tempat_perawatan,
	            );
	        }
	    }else{
	        $data = array();
	    }
	    return response()->json(['status' => 200, 'page_end'=>$pageend, 'data' => $data]);
	}
	
	public function InsertKasus(Request $request) {
	    $data = new TrnKasus();

	    if($request->jenis_kasus > 2){
	        $this->validate($request, [
	            'kd_perusahaan' => 'required',
	            'nama_pasien' => 'required',
	            'jenis_kasus' => 'required',
	            'status_pegawai' => 'required',
	            'provinsi' => 'required',
	            'kabupaten' => 'required',
	            'tanggal' => 'required'
	        ]);
	        
	        $tgl = strtotime($request->tanggal);
	        $tanggal = date('Y-m-d',$tgl);
	       
	        if($request->jenis_kasus==5){
                $data->tk_date_meninggal = $tanggal;
	        }else if($request->jenis_kasus==4){
	            $data->tk_date_sembuh = $tanggal;
	        }else if($request->jenis_kasus==3){
	            $data->tk_date_positif = $tanggal;
	        }
	    }else{
	        $this->validate($request, [
	            'kd_perusahaan' => 'required',
	            'nama_pasien' => 'required',
	            'jenis_kasus' => 'required',
	            'status_pegawai' => 'required',
	            'provinsi' => 'required',
	            'kabupaten' => 'required',
	        ]);
	    }
	    date_default_timezone_set('Asia/Jakarta');
	    $data->tk_mc_id = $request->kd_perusahaan;
	    $data->tk_nama = $request->nama_pasien;
	    $data->tk_msk_id = $request->jenis_kasus;
	    $data->tk_msp_id = $request->status_pegawai;
	    $data->tk_mpro_id = $request->provinsi;
	    $data->tk_mkab_id = $request->kabupaten;
	    $data->tk_tempat_perawatan = $request->tempat_perawatan;
	    $data->tk_user_insert = Auth::guard('api')->user()->id;
	    $data->tk_date_insert = date('d-m-Y H:i:s');
	    $data->save();

	    if($data->save()){
	        return response()->json(['status' => 200,'message' => 'Data Kasus Pegawai Berhasil diSimpan']);
	    } else {
	        return response()->json(['status' => 500,'message' => 'Data Kasus Pegawai diSimpan'])->setStatusCode(500);
	    }
	}
	
	public function UpdateKasus(Request $request, $id){
	    $data = TrnKasus::where('tk_id',$id)->first();
	    if($request->jenis_kasus > 2){
	        $this->validate($request, [
	            'kd_perusahaan' => 'required',
	            'nama_pasien' => 'required',
	            'jenis_kasus' => 'required',
	            'status_pegawai' => 'required',
	            'provinsi' => 'required',
	            'kabupaten' => 'required',
	            'tanggal' => 'required'
	        ]);
	        
	        $tgl = strtotime($request->tanggal);
	        $tanggal = date('Y-m-d',$tgl);
	        
	        if($request->jenis_kasus==5){
	            $data->tk_date_meninggal = $tanggal;
	        }else if($request->jenis_kasus==4){
	            $data->tk_date_sembuh = $tanggal;
	        }else if($request->jenis_kasus==3){
	            $data->tk_date_positif = $tanggal;
	        }
	    }else{
	        $this->validate($request, [
	            'kd_perusahaan' => 'required',
	            'nama_pasien' => 'required',
	            'jenis_kasus' => 'required',
	            'status_pegawai' => 'required',
	            'provinsi' => 'required',
	            'kabupaten' => 'required',
	        ]);
	    }
	    
	    $data->tk_mc_id = $request->kd_perusahaan;
	    $data->tk_nama = $request->nama_pasien;
	    $data->tk_msk_id = $request->jenis_kasus;
	    $data->tk_msp_id = $request->status_pegawai;
	    $data->tk_mpro_id = $request->provinsi;
	    $data->tk_mkab_id = $request->kabupaten;
	    $data->tk_tempat_perawatan = $request->tempat_perawatan;
	    $data->tk_user_update = Auth::guard('api')->user()->id;
	    $data->tk_date_update = date('d-m-Y H:i:s');
	    $data->save();
	    
	    if($data->save()){
	        return response()->json(['status' => 200,'message' => 'Data Kasus Pegawai Berhasil diUpdate']);
	    } else {
	        return response()->json(['status' => 500,'message' => 'Data Kasus Pegawai diUpdate'])->setStatusCode(500);
	    }
	}
	
	public function getDataByid($id) {
	    $terpapar = DB::select("SELECT tk_id, tk_mc_id, tk_nama, mc_name, msk_name, msk_name2,
                    msp_name, mpro_name, mkab_name, tk_tempat_perawatan,
                    tk_date_meninggal, tk_date_sembuh, tk_date_positif
                    FROM transaksi_kasus tk
                    INNER JOIN master_company mc ON mc.mc_id=tk.tk_mc_id
                    INNER JOIN master_status_kasus msk ON msk.msk_id=tk.tk_msk_id
                    LEFT JOIN master_status_pegawai msp ON msp.msp_id=tk.tk_msp_id
                    LEFT JOIN master_provinsi mpro ON mpro.mpro_id=tk.tk_mpro_id
                    LEFT JOIN master_kabupaten mkab ON mkab.mkab_id=tk.tk_mkab_id AND mkab.mkab_mpro_id=mpro.mpro_id
                    WHERE tk_id=$id");
	    
	    if (count($terpapar) > 0){
	        foreach($terpapar as $tpp){
	            $data[] = array(
	                "id" => $tpp->tk_id,
	                "kd_perusahaan" => $tpp->tk_mc_id,
	                "perusahaan" => $tpp->mc_name,
	                "nama_pasien" => $tpp->tk_nama,
	                "jenis_kasus" => $tpp->msk_name2,
	                "jenis_kasus2" => $tpp->msk_name,
	                "status_pegawai" => $tpp->msp_name,
	                "provinsi" => $tpp->mpro_name,
	                "kabupaten" => $tpp->mkab_name,
	                "tempat_perawatan" => $tpp->tk_tempat_perawatan,
	                "date_meninggal" => $tpp->tk_date_meninggal,
	                "date_sembuh" => $tpp->tk_date_sembuh,
	                "date_positif" => $tpp->tk_date_positif
	            );
	        }
	        return response()->json(['status' => 200,'data' => $data]);
	    }else{
	        return response()->json(['status' => 404,'message' => 'Tidak ada data'])->setStatusCode(404);
	    }	
	}
}
