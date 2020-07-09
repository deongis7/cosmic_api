<?php
namespace App\Http\Controllers;
use App\CARuangan;
use App\User;
use App\UserGroup;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CARuanganController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }
	
	public function getAll() {
	    $car = CARuangan::all();

	    foreach($car as $c){
	        $data[] = array(
	            "cluster_aktifitas_ruangan" => $c->mcar_name,
	            "aktif" => $c->mcar_active
	        );
	    }
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}

	public function getById($id){
	    $data = CARuangan::where('mcar_id',$id)->get();
	    return response()->json(['status' => 200,
	        'data' => $data]);
	}
	
	public function CreateCARuangan (Request $request){
	    $data = new CARuangan();
	    $data->mcar_name = $request->input('name');
	    $data->mcar_active = $request->input('active');
	    $data->save();
	    
	    if($data->save()){
	        return response('Berhasil Tambah Data');
	    }else{
	        return response('Gagal Tambah Data');
	    }
	}
	
	public function UpdateCARuangan (Request $request, $id){
	    $data = CARuangan::where('mcar_id',$id)->first();
	    $data->mcar_name = $request->input('name');
	    $data->mcar_active = $request->input('active');
	    $data->save();

	    if($data->save()){
	        return response('Berhasil Merubah Data');
	    }else{
	        return response('Gagal Merubah Data');
	    }
	}
	
	public function DeleteCARuangan($id){
	    $data = CARuangan::where('mcar_id',$id)->first();
	    $data->delete();

	    if($data->delete()===NULL){
	        return response('Berhasil Menghapus Data');
	    }else{
	        return response('Gagal Menghapus Data');
	    }
	}
}
