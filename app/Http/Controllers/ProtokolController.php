<?php

namespace App\Http\Controllers;

use App\Protokol;
use App\TblProtokol;
use App\Region;
use App\Perimeter;
use App\PerimeterLevel;
use App\PerimeterDetail;
use App\PerimeterKategori;
use App\User;
use App\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use DB;


class ProtokolController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

	 
    public function __construct()
    {
        //
    }

	public function index(){

	}
	public function show($id){

	}
	
	public function store (Request $request){

	}

	//Get Protokol
	public function protokol($id){
		$data = array();
		$protokol = Protokol::select('master_protokol.mpt_id','master_protokol.mpt_name','table_protokol.tbpt_filename','table_protokol.tbpt_date_insert' )
					->leftJoin('table_protokol', function($q) use ($id)
						{
							$q->on('table_protokol.tbpt_mpt_id', '=', 'master_protokol.mpt_id')
								->where('table_protokol.tbpt_mc_id', '=', $id);
						})
					->where('master_protokol.mpt_type','=','1')	
					->orderBy('master_protokol.mpt_id', 'asc')
					->get();
		
		foreach ($protokol as $itemprotokol){
				
				$data[] = array(
					"protokol" => $itemprotokol->mpt_name,
					"filename" => $itemprotokol->tbpt_filename,
					"tgl_upload" => ($itemprotokol->tbpt_date_insert != null ? Carbon::parse($itemprotokol->tbpt_date_insert)->format('d-m-Y H:i')
					:null)
					);
				} 
		
		if($protokol->count()<>0) {
			return response()->json(['status' => 200,'data' => $data]);
		} else {
			return response()->json(['status' => 404,'message' => 'Tidak ada data'])->setStatusCode(404);	
		}	
	}
	
	//Upload Protokol
	public function uploadProtokol(Request $request)
    {
		$this->validate($request, [
            'file_protokol' => 'required|mimes:pdf',
			'kd_perusahaan' => 'required',
			'protokol' => 'required',
			'user_id' => 'required',
        ]);
		
		$file = $request->file('file_protokol');
		$kd_perusahaan = $request->kd_perusahaan;
		$protokol = $request->protokol;
		$user_id = $request->user_id;
	
		if ($request->file_protokol != null || $request->file_protokol != '') {
						$timestamp = str_replace([' ', ':'], '', Carbon::now()->toDateTimeString());
						//$name = $timestamp . '-'.  $request->nama_file_jaminan->getClientOriginalName();
						$name = round(microtime(true) * 1000).'.pdf';
						
						$request->file_protokol->move(storage_path() . '/app/public/protokol/'.$kd_perusahaan.'/', $name);

						$filename_protokol = $name;
					}
		$dataProtokol= TblProtokol::updateOrCreate(['tbpt_mpt_id' => $protokol, 'tbpt_mc_id' => $kd_perusahaan],['tbpt_filename' => $filename_protokol, 'tbpt_user_insert' => $user_id]);	

		if($dataProtokol) {
			return response()->json(['status' => 200,'message' => 'Data Berhasil di Import']);
		} else {
			return response()->json(['status' => 500,'message' => 'Data Gagal di Import'])->setStatusCode(500);	
		}			
	}

	//Upload Protokol JSON
	public function uploadProtokolJSON(Request $request)
    {

		$this->validate($request, [
            'file_protokol' => 'required',
			'kd_perusahaan' => 'required',
			'protokol' => 'required',
			'user_id' => 'required',
        ]);
		
		$file = $request->file_protokol;
		$kd_perusahaan = $request->kd_perusahaan;
		$protokol = $request->protokol;
		$user_id = $request->user_id;
	
		if ($request->file_protokol != null || $request->file_protokol != '') {
						
						$image = str_replace('data:application/pdf;base64,', '', $file); 	 
						$filedecode = base64_decode($image);
						//dd($filedecode);
						
						$name = round(microtime(true) * 1000).'.pdf';;
						//\File::put(storage_path(). '/public/protokol/' . $name, base64_decode($image));
						Storage::disk('public')->put('protokol/'.$kd_perusahaan.'/'.$name, base64_decode($image));
						$filename_protokol = $name;
					}
		$dataProtokol= TblProtokol::updateOrCreate(['tbpt_mpt_id' => $protokol, 'tbpt_mc_id' => $kd_perusahaan],['tbpt_filename' => $filename_protokol, 'tbpt_user_insert' => $user_id]);	
		
		

		if($dataProtokol) {
			return response()->json(['status' => 200,'message' => 'Data Berhasil di Import']);
		} else {
			return response()->json(['status' => 500,'message' => 'Data Gagal di Import'])->setStatusCode(500);	
		}			
	}
	
	//Download File Protokol by binary
	public function getDownloadFileProtokol($kd_perusahaan,$id_protokol)
	{
    //PDF file is stored under project/public/download/info.pdf
	$protokol = TblProtokol::where('tbpt_mpt_id',$id_protokol)->where('tbpt_mc_id',$kd_perusahaan)->first();
    $file= storage_path() . "/app/public/protokol/".$kd_perusahaan."/". $protokol->tbpt_filename;

	$headers = [
				  'Content-Type' => 'application/pdf',
				 ];
	
	if (!is_file($file)) {  
	   return response()->json(['status' => 404,'message' => 'Data Tidak Ada'])->setStatusCode(404);	
		}
	$response = new BinaryFileResponse($file, 200 , $headers);

	return $response;	
	//return response()->file($file);

	}
	

	


    //
}
