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
use Illuminate\Support\Facades\Cache;

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
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_terpapar_bymcid_".$id, 5 * 60, function()use($id){
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
    	    $data=array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "id_kasus" => $tpp->msk_id,
    	            "jenis_kasus" => $tpp->msk_name2,
    	            "jumlah" => $tpp->jml
    	        );
    	    }
	    });
	    return response()->json(['status' => 200,
	        'data' => $datacache]);
	}

	public function getDataHomeAll() {
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_terpapar_all", 5 * 60, function(){
    	    $terpapar = DB::select("SELECT * FROM dashboard_kasus()");
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "id_kasus" => $tpp->v_msk_id,
    	            "jenis_kasus" => $tpp->v_msk_name,
    	            "jumlah" => $tpp->v_cnt
    	        );
    	    }
	    });
        return response()->json(['status' => 200,
            'data' => $datacache]);
	}
	
	public function getClusterDataHomeAll($id) {
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_terpapar_bymcid_".$id, 5 * 60, function()use($id){
    	    $terpapar = DB::select("SELECT * FROM cluster_dashboard_kasus('$id')");
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "id_kasus" => $tpp->v_msk_id,
    	            "jenis_kasus" => $tpp->v_msk_name,
    	            "jumlah" => $tpp->v_cnt
    	        );
    	    }
	    });
	    return response()->json(['status' => 200,
	        'data' => $datacache]);
	}

	public function getDatadetail($id, $page, $search,Request $request) {
	    if($page > 0){
	        $page=$page-1;
	    }else{
	        $page=0;
	    }

	    $row = 10;
	    $pageq = $page*$row;
	    if($search=='all'){
	        $search='';
	    }
	    $query = "SELECT tk_id, tk_mc_id, tk_nama, mc_name, msk_name, msk_name2,
                    msp_name, mpro_name, mkab_name, tk_tempat_perawatan, tk_tindakan
                    FROM transaksi_kasus tk
                    INNER JOIN master_company mc ON mc.mc_id=tk.tk_mc_id
                    INNER JOIN master_status_kasus msk ON msk.msk_id=tk.tk_msk_id
                    LEFT JOIN master_status_pegawai msp ON msp.msp_id=tk.tk_msp_id
                    LEFT JOIN master_provinsi mpro ON mpro.mpro_id=tk.tk_mpro_id
                    LEFT JOIN master_kabupaten mkab ON mkab.mkab_id=tk.tk_mkab_id AND mkab.mkab_mpro_id=mpro.mpro_id
                    WHERE tk_mc_id='$id' AND LOWER(tk_nama) LIKE LOWER('%$search%') ";
        if(isset($request->status_kasus)){
            if(($request->status_kasus != 'null' && $request->status_kasus != 'undefined')){
                if ($request->status_kasus == '3'){
                    $query = $query . " and (tk_msk_id = '3' or tk_msk_id = '4' or tk_msk_id = '5') " ;
                } else {
                    $query = $query . " and tk_msk_id = " .$request->status_kasus;
                }

            }

        }
	    $query = $query . " ORDER BY tk_id ";
	    $terpaparall = DB::select($query);

	    $terpapar = DB::select($query . " OFFSET $pageq LIMIT $row");

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
	                "tindakan" => $tpp->tk_tindakan,
	            );
	        }
	    }else{
	        $data = array();
	    }
	    return response()->json(['status' => 200, 'page_end'=>$pageend, 'data' => $data]);
	}

	public function InsertKasus(Request $request) {
	    $data = new TrnKasus();
	    $datareq = [
	        'kd_perusahaan' => 'required',
	        'nama_pasien' => 'required',
	        'jenis_kasus' => 'required',
	        'status_pegawai' => 'required',
	        'provinsi' => 'required',
	        'kabupaten' => 'required',
	        'tindakan' => 'required',
	    ];
	    
	    if($request->jenis_kasus > 2 && $request->jenis_kasus < 6){
	        $datareq['tanggal'] = 'required';
	    }

        $this->validate($request, $datareq);

        $tgl = strtotime($request->tanggal);
        $tanggal = date('Y-m-d',$tgl);

        if($request->jenis_kasus==5){
            $data->tk_date_meninggal = $tanggal;
            $data->tk_date = $tanggal;
        }else if($request->jenis_kasus==4){
            $data->tk_date_sembuh = $tanggal;
            $data->tk_date = $tanggal;
        }else if($request->jenis_kasus==3){
            $data->tk_date_positif = $tanggal;
            $data->tk_date = $tanggal;
        }

	    date_default_timezone_set('Asia/Jakarta');
	    $data->tk_mc_id = $request->kd_perusahaan;
	    $data->tk_nama = $request->nama_pasien;
	    $data->tk_msk_id = $request->jenis_kasus;
	    $data->tk_msp_id = $request->status_pegawai;
	    $data->tk_mpro_id = $request->provinsi;
	    $data->tk_mkab_id = $request->kabupaten;
	    $data->tk_tempat_perawatan = $request->tempat_perawatan;
	    $data->tk_tindakan = $request->tindakan;
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
	    
	    $datareq = [
	        'kd_perusahaan' => 'required',
	        'nama_pasien' => 'required',
	        'jenis_kasus' => 'required',
	        'status_pegawai' => 'required',
	        'provinsi' => 'required',
	        'kabupaten' => 'required',
	        'tindakan' => 'required',
	    ];
	    
	    if($request->jenis_kasus > 2 && $request->jenis_kasus < 6){
	        $datareq['tanggal'] = 'required';
	    }
	    
	    $this->validate($request, $datareq);
        $tgl = strtotime($request->tanggal);
        
        $tanggal = date('Y-m-d',$tgl);

        if($request->jenis_kasus==5){
            $data->tk_date_meninggal = $tanggal;
            $data->tk_date = $tanggal;
        }else if($request->jenis_kasus==4){
            $data->tk_date_sembuh = $tanggal;
            $data->tk_date = $tanggal;
        }else if($request->jenis_kasus==3){
            $data->tk_date_positif = $tanggal;
            $data->tk_date = $tanggal;
        }

	    $data->tk_mc_id = $request->kd_perusahaan;
	    $data->tk_nama = $request->nama_pasien;
	    $data->tk_msk_id = $request->jenis_kasus;
	    $data->tk_msp_id = $request->status_pegawai;
	    $data->tk_mpro_id = $request->provinsi;
	    $data->tk_mkab_id = $request->kabupaten;
	    $data->tk_tempat_perawatan = $request->tempat_perawatan;
	    $data->tk_tindakan = $request->tindakan;
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
                    msp_name, mpro_id, mpro_name, mkab_id, mkab_name, tk_tempat_perawatan, tk_tindakan,
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
	                "provinsi_id" => $tpp->mpro_id,
	                "provinsi" => $tpp->mpro_name,
	                "kabupaten_id" => $tpp->mkab_id,
	                "kabupaten" => $tpp->mkab_name,
	                "tempat_perawatan" => $tpp->tk_tempat_perawatan,
	                "tindakan" => $tpp->tk_tindakan,
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

    public function deleteKasus($id_kasus) {
        $data = TrnKasus::where('tk_id',$id_kasus)->delete();
        if ($data){
            return response()->json(['status' => 200,'message' => 'Data Berhasil DiHapus']);
        }  else if($data==null){
            return response()->json(['status' => 404,'message' => 'Data Perusahaan Tidak Ditemukan'])->setStatusCode(404);
        }
        else{
            return response()->json(['status' => 500,'message' => 'Data Gagal Dihapus'])->setStatusCode(500);
        }
    }

	public function getDashboardCompanybyMskid($id) {
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_terpaparcompany_bymskid_".$id, 5 * 60, function()use($id){
    	    $terpapar = DB::select("SELECT * FROM allkasus_company_bymskid($id)");
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "mc_id" => $tpp->x_mc_id,
    	            "mc_name" => $tpp->x_mc_name,
    	            "jumlah" => $tpp->x_jml
    	        );
    	    }
	    });
	    return response()->json(['status' => 200,
	        'data' => $datacache]);
	}

	public function getDashboardProvinsibyMskid($id) {
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_terpaparprovinsi_bymskid_".$id, 5 * 60, function()use($id){
    	    $terpapar = DB::select("SELECT * FROM allkasus_provinsi_bymskid($id)");
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "mpro_id" => $tpp->x_mpro_id,
    	            "mpro_name" => $tpp->x_mpro_name,
    	            "jumlah" => $tpp->x_jml
    	        );
    	    }
	    });
	    return response()->json(['status' => 200,
	        'data' => $datacache]);
	}

	public function getDashboardKabupatenbyMskid($id) {
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_terpaparkabupaten_bymskid_".$id, 5 * 60, function()use($id){
    	    $terpapar = DB::select("SELECT * FROM allkasus_kabupaten_bymskid($id)");
    	   // var_dump($terpapar);die;
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "mkab_id" => $tpp->x_mkab_id,
    	            "mkab_name" => $tpp->x_mkab_name,
    	            "jumlah" => $tpp->x_jml
    	        );
    	    }
	    });
	    return response()->json(['status' => 200,
	        'data' => $datacache]);
	}
	
	public function getClusterDashboardCompanybyMskid($id, $msc_id) {
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_clusterterpaparcompany_".$id.'_'.$msc_id, 5 * 60, function()use($id, $msc_id){
    	    $terpapar = DB::select("SELECT * FROM cluster_allkasus_company_bymskid($id,'$msc_id')");
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "mc_id" => $tpp->x_mc_id,
    	            "mc_name" => $tpp->x_mc_name,
    	            "jumlah" => $tpp->x_jml
    	        );
    	    }
	    });
	    return response()->json(['status' => 200,
	        'data' => $datacache]);
	}
	
	public function getClusterDashboardProvinsibyMskid($id, $msc_id) {
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_clusterterpaparprovinsi_".$id.'_'.$msc_id, 5 * 60, function()use($id, $msc_id){
    	    $terpapar = DB::select("SELECT * FROM cluster_allkasus_provinsi_bymskid($id,'$msc_id')");
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "mpro_id" => $tpp->x_mpro_id,
    	            "mpro_name" => $tpp->x_mpro_name,
    	            "jumlah" => $tpp->x_jml
    	        );
    	    }
	    });
	    return response()->json(['status' => 200,
	        'data' => $datacache]);
	}
	
	public function getClusterDashboardKabupatenbyMskid($id, $msc_id) {
	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_clusterterpaparkabupaten_".$id.'_'.$msc_id, 5 * 60, function()use($id, $msc_id){
	
    	    $terpapar = DB::select("SELECT * FROM cluster_allkasus_kabupaten_bymskid($id,'$msc_id')");
    	    // var_dump($terpapar);die;
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "mkab_id" => $tpp->x_mkab_id,
    	            "mkab_name" => $tpp->x_mkab_name,
    	            "jumlah" => $tpp->x_jml
    	        );
    	    }
	    });
	    return response()->json(['status' => 200,
	        'data' => $datacache]);
	}
	
}
