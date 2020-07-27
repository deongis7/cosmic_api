<?php
namespace App\Http\Controllers;
use App\Terpapar;
use App\User;
use App\UserGroup;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
                    SELECT tk_msk_id, count(tk_msk_id) jml 
                    from transaksi_kasus
                    where tk_mc_id=?
                    group by tk_msk_id) tk on tk.tk_msk_id=msk.msk_id",[$id]);

	    foreach($terpapar as $tpp){
	        $data[] = array(
	            "jenis_kasus" => $tpp->msk_name2,
	            "jumlah" => $tpp->jml
	        );
	    }
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}
	
	public function getDatadetail($id) {
	    $terpapar = DB::select("SELECT tk_id, tk_mc_id, tk_nama, mc_name, msk_name2
                    FROM transaksi_kasus tk 
                    INNER JOIN master_company mc ON mc.mc_id=tk.tk_mc_id
                    INNER JOIN master_status_kasus msk ON msk.msk_id=tk.tk_msk_id
                    WHERE tk_mc_id='$id'");
	    if (count($terpapar) > 0){
    	    foreach($terpapar as $tpp){
    	        $data[] = array(
    	            "id" => $tpp->tk_id,
    	            "kd_perusahaan" => $tpp->tk_mc_id,
    	            "perusahaan" => $tpp->mc_name,
    	            "nama_pasien" => $tpp->tk_nama,
    	            "jenis_kasus" => $tpp->msk_name2
    	        );
    	    }
	    }else{
	        $data = array();
	    }
	    return response()->json(['status' => 200,'data' => $data]);
	}
}
