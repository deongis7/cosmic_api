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

class SosialisasiController extends Controller {
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
    
    public function getDataDetailByMcid($id) {
        $sosialisasi = Sosialisasi::where('mc_id', $id)->get();
        
        foreach($sosialisasi as $sos){
            $data[] = array(
                "id" => $sos->id,
                "kd_perusahaan" => $sos->ts_mc_id,
            );
        }
        return response()->json(['status' => 200,'data' => $data]);
    }
}
