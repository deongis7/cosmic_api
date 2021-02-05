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
    //$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_terpapar_bymcid_".$id, 5 * 60, function()use($id){
	    $terpapar = DB::connection('pgsql3')->select("SELECT msk_id, msk_name2,
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
	    //});
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}

	public function getDataHomeAll() {
// 	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_terpapar_all", 5 * 60, function(){
	    $terpapar = DB::connection('pgsql3')->select("SELECT * FROM dashboard_kasus()");
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "id_kasus" => $tpp->v_msk_id,
    	            "jenis_kasus" => $tpp->v_msk_name,
    	            "jumlah" => $tpp->v_cnt
    	        );
    	    }
// 	    });
        return response()->json(['status' => 200,
            'data' => $data]);
	}
	
	public function getClusterDataHomeAll($id) {
// 	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_terpapar_bymcid_".$id, 5 * 60, function()use($id){
	    $terpapar = DB::connection('pgsql3')->select("SELECT * FROM cluster_dashboard_kasus('$id')");
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "id_kasus" => $tpp->v_msk_id,
    	            "jenis_kasus" => $tpp->v_msk_name,
    	            "jumlah" => $tpp->v_cnt
    	        );
    	    }
// 	    });
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}

	public function getDatadetail($id, $page, $search, Request $request) {
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
                    msp_name, mpro_name, mkab_name, tk_tempat_perawatan, tk_tindakan,
                    tk_nik, tk_mjk_id, tk_mpm_id, tk_direksi,
                    mpm_name, mpm_alamat, mpmk_name
                    FROM transaksi_kasus tk
                    INNER JOIN master_company mc ON mc.mc_id=tk.tk_mc_id
                    INNER JOIN master_status_kasus msk ON msk.msk_id=tk.tk_msk_id
                    LEFT JOIN master_status_pegawai msp ON msp.msp_id=tk.tk_msp_id
                    LEFT JOIN master_provinsi mpro ON mpro.mpro_id=tk.tk_mpro_id
                    LEFT JOIN master_kabupaten mkab ON mkab.mkab_id=tk.tk_mkab_id AND mkab.mkab_mpro_id=mpro.mpro_id
                    LEFT JOIN master_perimeter AS mpm ON mpm.mpm_id=tk_mpm_id
                    LEFT JOIN master_perimeter_kategori AS mpmk ON mpmk.mpmk_id=mpm.mpm_mpmk_id
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
	    $terpaparall = DB::connection('pgsql3')->select($query);

	    $terpapar = DB::connection('pgsql3')->select($query . " OFFSET $pageq LIMIT $row");

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
	                "perimeter_id" => $tpp->tk_mpm_id,
	                "perimeter_name" => $tpp->mpm_name,
	                "perimeter_kategori" => $tpp->mpmk_name,
	                "perimeter_alamat" => $tpp->mpm_alamat,
	                "nik" => $tpp->tk_nik,
	                "jns_kelamin" => $tpp->tk_mjk_id,
	                "direksi" => $tpp->tk_direksi
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
	    
// 	    if($request->jenis_kasus > 2 && $request->jenis_kasus < 6){
// 	        $datareq['tanggal'] = 'required';
// 	    }
	    
	    if($request->jenis_kasus > 2 && $request->jenis_kasus < 6){
	        if($request->jenis_kasus==3){
	            $datareq['tanggal_positif'] = 'required';
	        }else{
	            $datareq['tanggal'] = 'required';
	            $datareq['tanggal_positif'] = 'required';
	        }
	    }

        $this->validate($request, $datareq);

        $tgl = strtotime($request->tanggal);
        $tanggal = date('Y-m-d',$tgl);
       
        if(isset($request->tanggal_positif)){
            $tgl_positif = strtotime($request->tanggal_positif);
            $tanggal_positif = date('Y-m-d',$tgl_positif);
            $data->tk_date_positif = $tanggal_positif;
        }
        
        if($request->jenis_kasus==5){
            $data->tk_date_positif = $tanggal_positif;
            $data->tk_date_meninggal = $tanggal;
            $data->tk_date = $tanggal;
        }else if($request->jenis_kasus==4){
            $data->tk_date_positif = $tanggal_positif;
            $data->tk_date_sembuh = $tanggal;
            $data->tk_date = $tanggal;
        }else if($request->jenis_kasus==3){
            $data->tk_date_positif = $tanggal_positif;
            $data->tk_date_positif = $tanggal;
            $data->tk_date = $tanggal;
        }
        
        if(isset($request->tanggal_positif)){
           $data->tk_date_positif = $tanggal_positif;
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
	    
	    $data->tk_nik = $request->nik;
	    $data->tk_mjk_id = $request->jns_kelamin;
	    $data->tk_mpm_id = $request->perimeter;
	    $data->tk_direksi = $request->direksi;
	    
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
	    
// 	    if($request->jenis_kasus > 2 && $request->jenis_kasus < 6){
// 	        $datareq['tanggal'] = 'required';
// 	    }
	    
	    if($request->jenis_kasus > 2 && $request->jenis_kasus < 6){
	        if($request->jenis_kasus==3){
	            $datareq['tanggal_positif'] = 'required';
	        }else{
	            $datareq['tanggal'] = 'required';
	            $datareq['tanggal_positif'] = 'required';
	        }
	    }
	    
	    $this->validate($request, $datareq);
	    
	    $tgl = strtotime($request->tanggal);
	    $tanggal = date('Y-m-d',$tgl);
	    
	    if(isset($request->tanggal_positif)){
	        $tgl_positif = strtotime($request->tanggal_positif);
	        $tanggal_positif = date('Y-m-d',$tgl_positif);
	        $data->tk_date_positif = $tanggal_positif;
	    }
	    
	    if($request->jenis_kasus==5){
	        $data->tk_date_positif = $tanggal_positif;
	        $data->tk_date_meninggal = $tanggal;
	        $data->tk_date = $tanggal;
	    }else if($request->jenis_kasus==4){
	        $data->tk_date_positif = $tanggal_positif;
	        $data->tk_date_sembuh = $tanggal;
	        $data->tk_date = $tanggal;
	    }else if($request->jenis_kasus==3){
	        $data->tk_date_positif = $tanggal_positif;
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
	    
	    $data->tk_nik = $request->nik;
	    $data->tk_mjk_id = $request->jns_kelamin;
	    $data->tk_mpm_id = $request->perimeter;
	    $data->tk_direksi = $request->direksi;
	    
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
	    $terpapar = DB::connection('pgsql3')->select("SELECT tk_id, tk_mc_id, tk_nama, mc_name, msk_name, msk_name2,
            msp_name, mpro_id, mpro_name, mkab_id, mkab_name, tk_tempat_perawatan, tk_tindakan,
            tk_date_meninggal, tk_date_sembuh, tk_date_positif,
            tk_nik, tk_mjk_id, tk_mpm_id, tk_direksi,
            mpm_name, mpm_alamat, mpmk_name
            FROM transaksi_kasus tk
            INNER JOIN master_company mc ON mc.mc_id=tk.tk_mc_id
            INNER JOIN master_status_kasus msk ON msk.msk_id=tk.tk_msk_id
            LEFT JOIN master_status_pegawai msp ON msp.msp_id=tk.tk_msp_id
            LEFT JOIN master_provinsi mpro ON mpro.mpro_id=tk.tk_mpro_id
            LEFT JOIN master_kabupaten mkab ON mkab.mkab_id=tk.tk_mkab_id AND mkab.mkab_mpro_id=mpro.mpro_id
            LEFT JOIN master_perimeter AS mpm ON mpm.mpm_id=tk_mpm_id
            LEFT JOIN master_perimeter_kategori AS mpmk ON mpmk.mpmk_id=mpm.mpm_mpmk_id
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
	                "date_positif" => $tpp->tk_date_positif,
	                "perimeter_id" => $tpp->tk_mpm_id,
	                "perimeter_name" => $tpp->mpm_name,
	                "perimeter_kategori" => $tpp->mpmk_name,
	                "perimeter_alamat" => $tpp->mpm_alamat,
	                "nik" => $tpp->tk_nik,
	                "jns_kelamin" => $tpp->tk_mjk_id,
	                "direksi" => $tpp->tk_direksi
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
// 	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_terpaparcompany_bymskid_".$id, 5 * 60, function()use($id){
	    $terpapar = DB::connection('pgsql3')->select("SELECT * FROM allkasus_company_bymskid($id)");
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "mc_id" => $tpp->x_mc_id,
    	            "mc_name" => $tpp->x_mc_name,
    	            "jumlah" => $tpp->x_jml
    	        );
    	    }
// 	    });
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}

	public function getDashboardProvinsibyMskid($id) {
// 	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_terpaparprovinsi_bymskid_".$id, 5 * 60, function()use($id){
	    $terpapar = DB::connection('pgsql3')->select("SELECT * FROM allkasus_provinsi_bymskid($id)");
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "mpro_id" => $tpp->x_mpro_id,
    	            "mpro_name" => $tpp->x_mpro_name,
    	            "jumlah" => $tpp->x_jml
    	        );
    	    }
// 	    });
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}

	public function getDashboardKabupatenbyMskid($id) {
// 	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_terpaparkabupaten_bymskid_".$id, 5 * 60, function()use($id){
	    $terpapar = DB::connection('pgsql3')->select("SELECT * FROM allkasus_kabupaten_bymskid($id)");
    	   // var_dump($terpapar);die;
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "mkab_id" => $tpp->x_mkab_id,
    	            "mkab_name" => $tpp->x_mkab_name,
    	            "jumlah" => $tpp->x_jml
    	        );
    	    }
// 	    });
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}
	
	public function getClusterDashboardCompanybyMskid($id, $msc_id) {
// 	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_clusterterpaparcompany_".$id.'_'.$msc_id, 5 * 60, function()use($id, $msc_id){
	    $terpapar = DB::connection('pgsql3')->select("SELECT * FROM cluster_allkasus_company_bymskid($id,'$msc_id')");
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "mc_id" => $tpp->x_mc_id,
    	            "mc_name" => $tpp->x_mc_name,
    	            "jumlah" => $tpp->x_jml
    	        );
    	    }
// 	    });
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}
	
	public function getClusterDashboardProvinsibyMskid($id, $msc_id) {
// 	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_clusterterpaparprovinsi_".$id.'_'.$msc_id, 5 * 60, function()use($id, $msc_id){
	    $terpapar = DB::connection('pgsql3')->select("SELECT * FROM cluster_allkasus_provinsi_bymskid($id,'$msc_id')");
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "mpro_id" => $tpp->x_mpro_id,
    	            "mpro_name" => $tpp->x_mpro_name,
    	            "jumlah" => $tpp->x_jml
    	        );
    	    }
// 	    });
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}
	
	public function getClusterDashboardKabupatenbyMskid($id, $msc_id) {
// 	    $datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_clusterterpaparkabupaten_".$id.'_'.$msc_id, 5 * 60, function()use($id, $msc_id){
	
	    $terpapar = DB::connection('pgsql3')->select("SELECT * FROM cluster_allkasus_kabupaten_bymskid($id,'$msc_id')");
    	    // var_dump($terpapar);die;
    	    $data = array();
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "mkab_id" => $tpp->x_mkab_id,
    	            "mkab_name" => $tpp->x_mkab_name,
    	            "jumlah" => $tpp->x_jml
    	        );
    	    }
// 	    });
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}
	
	public function getDashboardCompanyMobilebyMskid($id, Request $request){
	    $data = array();
	    $limit = '';
	    $page = '';
	    $endpage = 1;
	    
	    $query = "SELECT * FROM allkasus_companymobile_bymskid($id)
                WHERE 1=1 ";

	    if(isset($request->search)) {
	        $query .= " AND LOWER(TRIM(x_mc_name)) LIKE LOWER(TRIM('%$request->search%')) ";
        }
	    
	    $terpaparall = DB::connection('pgsql3')->select($query);
	    $jmltotal=(count($terpaparall));

	    if(isset($request->column_sort)) {
	        if(isset($request->p_sort)) {
	            $sql_sort = ' ORDER BY '.$request->column_sort.' '.$request->p_sort;
	        }else{
	            $sql_sort = ' ORDER BY '.$request->column_sort.' DESC';
	        }
	    }else{
	        $sql_sort = ' ORDER BY x_jml DESC ';
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

	    $terpapar = DB::select($query);
	    $cntterpaparall = count($terpaparall);
	    foreach($terpapar as $tpp){
	        if($tpp->x_mc_foto !=NULL || $tpp->x_mc_foto !=''){
	            if (!file_exists(base_path("storage/app/public/foto_bumn/".$tpp->x_mc_foto))) {
	                $path_file404 = '/404/img404.jpg';
	                $file = $path_file404;
	            }else{
	                $path_file = '/foto_bumn/'.$tpp->x_mc_foto;
	                $file = $path_file;
	            }
	        }else{
	            $file = '/404/img404.jpg';
	        }
	        
	        $data[] = array(
	            "v_mc_id" => $tpp->x_mc_id,
	            "v_mc_name" => $tpp->x_mc_name,
	            "v_mc_foto" => $file,
	            "v_jml" => $tpp->x_jml,
	            "v_last_update" => $tpp->x_date
	        );
	    }
	    
	    return response()->json(['status' => 200, 'page_end'=>$endpage, 'data' => $data]);
	}
	
	
	public function getTerpaparRaw(Request $request) {
	    $limit = null;
	    $page = null;
	    $search = null;
	    $endpage = 1;
	    
	    $terpapar = new TrnKasus();
	    $terpapar->setConnection('pgsql3');
	    $terpapar = $terpapar->select('mc_id', 'mc_name', 'tk_id',
        'tk_nama', 'tk_msk_id', 'tk_msp_id',  'tk_mpro_id',  'tk_mkab_id',
        'tk_date_positif',  'tk_date_meninggal',  'tk_date_sembuh',
        'tk_date_insert', 'tk_date_update',  'tk_tempat_perawatan', 'tk_tindakan',
	    'msk_name','msk_name2','msp_name', 'mpro_name', 'mkab_name',
	    'tk_nik', 'tk_mjk_id', 'tk_mpm_id', 'tk_direksi',
	    'mpm_name','mpm_alamat', 'mpmk_id','mpmk_name',
	    'tk_date_insert', 'tk_date_update')
        ->join('master_company AS mc','mc.mc_id','tk_mc_id')
        ->join('master_status_kasus AS msk','msk.msk_id','tk_msk_id')
        ->leftjoin('master_status_pegawai AS msp','msp.msp_id','tk_msp_id')
        ->leftjoin('master_provinsi AS mpro','mpro.mpro_id','tk_mpro_id')
        ->leftjoin('master_kabupaten AS mkab','mkab.mkab_id','tk_mkab_id')
        ->leftjoin('master_perimeter AS mpm','mpm.mpm_id','tk_mpm_id')
        ->leftjoin('master_perimeter_kategori AS mpmk','mpmk.mpmk_id','mpm.mpm_mpmk_id')
        ;
        
        if(isset($request->search)) {
            $search = $request->search;
            $terpapar = $report->where(DB::raw("lower(TRIM(tk_nama))"),'like','%'.strtolower(trim($search)).'%');
        }
        
        $jmltotal=($terpapar->count());
        if(isset($request->limit)) {
            $limit = $request->limit;
            $terpapar = $terpapar->limit($limit);
            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));
            
            if (isset($request->page)) {
                $page = $request->page;
                $offset = ((int)$page -1) * (int)$limit;
                $terpapar = $terpapar->offset($offset);
            }
        }
        $terpapar = $terpapar->get();
        $totalterpapar = $terpapar->count();
        
        if (count($terpapar) > 0){
            foreach($terpapar as $tpp){
                $data[] = array(
                    "kode_perusahaan" => $tpp->mc_id,
                    "nama_perusahaan" => $tpp->mc_name,
                    "id" => $tpp->tk_id,
                    "nama" => $tpp->tk_nama,
                    "status_kasus_id" => $tpp->tk_msk_id,
                    "status_kasus" => $tpp->msk_name,
                    "status_kasus2" => $tpp->msk_name2,
                    "status_pegawai_id" => $tpp->tk_msp_id,
                    "status_pegawai" => $tpp->msp_name,
                    "provinsi_id" => $tpp->tk_mpro_id,
                    "provinsi" => $tpp->mpro_name,
                    "kabupaten_id" => $tpp->tk_mkab_id,
                    "kabupaten" => $tpp->mkab_name,
                    "date_positif" => $tpp->tk_date_positif,
                    "date_meninggal" => $tpp->tk_date_meninggal,
                    "date_sembuh" => $tpp->tk_date_sembuh,
                    "tempat_perawatan" => $tpp->tk_tempat_perawatan,
                    "tindakan" => $tpp->tk_tindakan,
                    "nik" => $tpp->tk_nik,
                    "jns_kelamin" => $tpp->tk_mjk_id,
                    "perimeter_id" => $tpp->tk_mpm_id,
                    "perimeter_name" => $tpp->mpm_name,
                    "perimeter_kategori" => $tpp->mpmk_name,
                    "perimeter_alamat" => $tpp->mpm_alamat,
                    "direksi" => $tpp->tk_direksi,
                    "date_insert" =>$tpp->tk_date_insert,
                    "date_update" =>$tpp->tk_date_update,
                );
            }
        }else{
            $data = array();
        }
        return response()->json(['status' => 200, 'page_end'=> $endpage,
            'data' => $data]);
	}
	
	public function getDatadetailNew($id, Request $request) {
	    $limit = null;
	    $page = null;
	    $search = null;
	    $endpage = 1;
	    
	    $terpapar = new TrnKasus();
	    $terpapar->setConnection('pgsql3');
	    $terpapar = $terpapar->select(
	        'tk_id', 'tk_mc_id', 'tk_nama', 'mc_name', 'msk_name', 'msk_name2',
	        'msp_name', 'mpro_name', 'mkab_name', 'tk_tempat_perawatan', 'tk_tindakan',
	        'tk_nik', 'tk_mjk_id', 'tk_mpm_id', 'tk_direksi', 'mpm_name','mpm_alamat',
	        'mpmk_id','mpmk_name'
	        )
        ->join('master_company AS mc','mc.mc_id','tk_mc_id')
        ->leftjoin('master_status_kasus AS msk', 'msk.msk_id','tk_msk_id')
        ->leftjoin('master_status_pegawai AS msp','msp.msp_id','tk_msp_id')
        ->leftjoin('master_provinsi AS mpro','mpro.mpro_id','tk_mpro_id')
        ->leftjoin('master_kabupaten AS mkab','mkab.mkab_id','tk_mkab_id')
        ->leftjoin('master_perimeter AS mpm','mpm.mpm_id','tk_mpm_id')
        ->leftjoin('master_perimeter_kategori AS mpmk','mpmk.mpmk_id','mpm.mpm_mpmk_id')
        ->where('mc.mc_level', 1)
        ->where('mc.mc_id', $id);

        if(isset($request->search)) {
            $search = $request->search;
            $terpapar = $terpapar->where(DB::raw("lower(TRIM(tk_nama))"),'like','%'.strtolower(trim($search)).'%');
        }
        
        if(isset($request->column_sort)) {
            if(isset($request->p_sort)) {
                $terpapar = $terpapar->orderBy($request->column_sort, $request->p_sort);
            }else{
                $terpapar = $terpapar->orderBy($request->column_sort, 'ASC');
            }
        }else{
            $terpapar = $terpapar->orderBy('tk_id', 'DESC');
        }
     
        $jmltotal=($terpapar->count());
        if(isset($request->limit)) {
            $limit = $request->limit;
            $terpapar = $terpapar->limit($limit);
            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));
            
            if (isset($request->page)) {
                $page = $request->page;
                $offset = ((int)$page -1) * (int)$limit;
                $report = $terpapar->offset($offset);
            }
        }
        $terpapar = $terpapar->get();
        $totalterpapar = $terpapar->count();
	    
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
	                "nik" => $tpp->tk_nik,
	                "jns_kelamin" => $tpp->tk_mjk_id,
	                "perimeter_id" => $tpp->tk_mpm_id,
	                "perimeter_name" => $tpp->mpm_name,
	                "perimeter_kategori" => $tpp->mpmk_name,
	                "perimeter_alamat" => $tpp->mpm_alamat,
	                "direksi" => $tpp->tk_direksi
	            );
	        }
	    }else{
	        $data = array();
	    }
	    return response()->json(['status' => 200, 'page_end'=>$endpage, 'data' => $data]);
	}
	
}
