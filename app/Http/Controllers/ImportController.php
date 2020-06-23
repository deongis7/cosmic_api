<?php

namespace App\Http\Controllers;

use App\TmpPerimeterImport;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;


class ImportController extends Controller
{


    public function import(Request $request)
    {
		$this->validate($request, [
            'file_import' => 'required|mimes:xls,xlsx',
			'kd_perusahaan' => 'required',
        ]);
		$file = $request->file('file_import');
		$kd_perusahaan = $request->kd_perusahaan;
		
		// membuat nama file unik
        //$nama_file = $file->hashName();
        $nama_file = time().$file->getClientOriginalName();
		
        //temporary file
        $path = $file->storeAs('public/import/',$nama_file);

        // import data
        $import = Excel::import(new TmpPerimeterImport($nama_file,$kd_perusahaan), storage_path('app/public/import/'.$nama_file));
        //$dataimport = Excel::load(storage_path('app/public/import/tes.xlsx'))->get();
       
        //remove from server
        Storage::delete($path);

        if($import) {
            //redirect
            return response()->json([
				'name' => '200',
				'state' => 'Data Berhasil di Import'
			]);
        } else {
            //redirect
            return response()->json([
				'name' => '500',
				'state' => 'Data Gagal di Import'
			]);
        }
    }

}
