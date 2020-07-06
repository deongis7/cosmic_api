<?php
namespace App\Http\Controllers;
use App\Terpapar;
use App\User;
use App\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use DB;
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
	    $terpapar = DB::select("SELECT mjk_name,
    		CASE WHEN jml IS NULL THEN 0 ELSE jml END AS jml
    		FROM master_jenis_kasus mjk
    		LEFT JOIN (SELECT tpp.jenis_kasus, 
    		COUNT(tpp.jenis_kasus) jml 
    		FROM table_terpapar tpp
    		WHERE tpp.kd_perusahaan=?
    		GROUP BY tpp.jenis_kasus) b on b.jenis_kasus=mjk.mjk_name",[$id]);

	    foreach($terpapar as $tpp){
	        $data[] = array(
	            "jenis_kasus" => $tpp->mjk_name,
	            "jumlah" => $tpp->jml
	        );
	    }
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}
	
	public function getDatadetail($id) {
	    $terpapar = Terpapar::where('kd_perusahaan', $id)->get();
	    
	    foreach($terpapar as $tpp){
	        $data[] = array(
	            "id" => $tpp->id,
	            "kd_perusahaan" => $tpp->kd_perusahaan,
	            "perusahaan" => $tpp->perusahaan,
	            "nama_pasien" => $tpp->nama_pasien,
	            "jenis_kasus" => $tpp->jenis_kasus,
	        );
	    }
	    return response()->json(['status' => 200,'data' => $data]);
	}
}
