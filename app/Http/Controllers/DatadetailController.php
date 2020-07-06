<?php
namespace App\Http\Controllers;
use App\Datadetail;
use App\User;
use App\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use DB;
class DatadetailController extends Controller {
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
	    $datadetail = DB::select("SELECT mjk_name,
    		CASE WHEN jml IS NULL THEN 0 ELSE jml END AS jml
    		FROM master_jenis_kasus mjk
    		LEFT JOIN (SELECT dd1.jenis_kasus, 
    		COUNT(dd1.jenis_kasus) jml 
    		FROM data_detail1 dd1
    		WHERE dd1.kd_perusahaan=?
    		GROUP BY dd1.jenis_kasus) b on b.jenis_kasus=mjk.mjk_name",[$id]);

	    foreach($datadetail as $dd){
	        $data[] = array(
	            "jenis_kasus" => $dd->mjk_name,
	            "jumlah" => $dd->jml
	        );
	    }
	    return response()->json(['status' => 200,'data' => $data]);
	}
	
	public function getDatadetail($id) {
	    $datadetail = Datadetail::where('kd_perusahaan', $id)->get();
	    
	    foreach($datadetail as $dd){
	        $data[] = array(
	            "id" => $dd->id,
	            "kd_perusahaan" => $dd->kd_perusahaan,
	            "perusahaan" => $dd->perusahaan,
	            "nama_pasien" => $dd->nama_pasien,
	            "jenis_kasus" => $dd->jenis_kasus,
	        );
	    }
	    return response()->json(['status' => 200,'data' => $data]);
	}
}
