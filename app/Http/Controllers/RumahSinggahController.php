<?php

namespace App\Http\Controllers;

use App\Perimeter;
use App\TblPengajuanAtestasi;
use App\TblStatusPengajuanAtestasi;
use App\TblPengajuanSertifikasi;
use App\TblRumahSinggah;
use App\MstStsKasus;
use App\MstKriteriaOrang;
use App\MstFasilitasRumah;
use App\Helpers\AppHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;


use DB;
use function Complex\negative;


class RumahSinggahController extends Controller
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

  //Get Status Monitoring Perimeter Level
    public function getListRumahSinggah(Request $request){
      $str = "_daftar_rumah_singgah_";
      $search = null;
      $mc_id = null;
      $id_provinsi = null;
      $limit = null;
      $page = null;
      $endpage = 1;
      $column = null;
      $sort = null;

       if(isset($request->search)){
            $str = $str.'_searh_'. str_replace(' ','_',$request->search);
            $search=$request->search;
       }

       if(isset($request->mc_id)){
            $str = $str.'_mc_id_'. str_replace(' ','_',$request->mc_id);
            $mc_id=$request->mc_id;
       }

       if(isset($request->id_provinsi)){
            $str = $str.'_prov_'. str_replace(' ','_',$request->id_provinsi);
          $id_provinsi=$request->id_provinsi;
       }

       if(isset($request->limit)){
           $str = $str.'_limit_'. $request->limit;
           $limit=$request->limit;
           if(isset($request->page)){
               $str = $str.'_page_'. $request->page;
               $page=$request->page;
           }
       }

       if(isset($request->column_sort)) {
         $str = $str.'_sort_'. $request->column_sort;
         $column=$request->column_sort;
           if(isset($request->p_sort)) {
             $str = $str.'_'. $request->p_sort;
             $sort=$request->p_sort;
           }
       }

       //$datacache =Cache::remember(env('APP_ENV', 'dev').$str, 5 * 60, function()use($search, $mc_id) {

        $data = array();

        $rumahsinggah = new TblRumahSinggah;

        $rumahsinggah->setConnection('pgsql3');
        $rumahsinggah = $rumahsinggah->select('table_rumahsinggah.id','table_rumahsinggah.nama_rumahsinggah','mc.mc_name','mc.mc_id',
                    'mpro.mpro_name','mkab.mkab_name','mpro.mpro_id','mkab.mkab_id',
                    'table_rumahsinggah.membayar','table_rumahsinggah.kapasitas')
                    ->join('master_company as mc', 'mc.mc_id','table_rumahsinggah.mc_id')
                    ->leftjoin('master_provinsi as mpro', 'mpro.mpro_id','table_rumahsinggah.prov_id')
                    //  ->leftjoin('master_kabupaten as mkab', 'mkab.mkab_id','table_rumahsinggah.kota_id');
                    ->leftJoin('master_kabupaten as mkab', function($q)
                        {
                            $q->on('mkab.mkab_id', '=', 'table_rumahsinggah.kota_id')
                                ->on('mkab.mkab_mpro_id', '=', 'mpro.mpro_id');
                        });



        if(isset($mc_id)) {
                $rumahsinggah =$rumahsinggah->where('mc.mc_id', $mc_id);
        }
        if(isset($id_provinsi)) {
                $rumahsinggah =$rumahsinggah->where('mpro.mpro_id', $id_provinsi);
        }

        if(isset($search)) {
            $rumahsinggah = $rumahsinggah->where(DB::raw("lower(TRIM(table_rumahsinggah.nama_rumahsinggah))"),'like','%'.strtolower(trim($search)).'%');
        }

        if(isset($column)) {
            if(isset($sort)) {
                $rumahsinggah = $rumahsinggah->orderBy($column,$sort);
            }else{
                $rumahsinggah = $rumahsinggah->orderBy($column,"asc");
            }
        }else{
            $rumahsinggah = $rumahsinggah->orderBy('table_rumahsinggah.id', 'asc');
        }


        $jmltotal=(count($rumahsinggah->get()));
        if(isset($limit)) {
            $rumahsinggah = $rumahsinggah->limit($limit);
            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

            if (isset($page)) {
                $offset = ((int)$page -1) * (int)$limit;
                $rumahsinggah = $perimeter->offset($offset);
            }
        }
        $rumahsinggah= $rumahsinggah->get();
        foreach($rumahsinggah as $itemrs){
          $data[] = array(
              "id_rumah_singgah"=>$itemrs->id,
              "nama_rumah_singgah"=>$itemrs->nama_rumahsinggah,
              "kd_perusahaan" => $itemrs->mc_id,
              "nama_perusahaan" => $itemrs->mc_name,
              "id_provinsi" => $itemrs->mpro_id,
              "provinsi" => $itemrs->mpro_name,
              "id_kota" => $itemrs->mkab_id,
              "kota" => $itemrs->mkab_name,
              "bayar" => $itemrs->membayar,
              "kapasitas" =>  $itemrs->kapasitas,
            );
        }

      //        return $data;

       //});
       return response()->json(['status' => 200, 'page_end' =>$endpage,'data' => $data]);
    }

    public function getGroupRumahSinggahByProv(Request $request){
      $str = "_daftar_rumah_singgah_by_prov_";
      $search = null;
      $mc_id = null;
      $limit = null;
      $page = null;
      $endpage = 1;
      $column = null;
      $sort = null;

       if(isset($request->search)){
            $str = $str.'_searh_'. str_replace(' ','_',$request->search);
            $search=$request->search;
       }

       if(isset($request->mc_id)){
            $str = $str.'_mc_id_'. str_replace(' ','_',$request->mc_id);
            $mc_id=$request->mc_id;
       }

       if(isset($request->limit)){
           $str = $str.'_limit_'. $request->limit;
           $limit=$request->limit;
           if(isset($request->page)){
               $str = $str.'_page_'. $request->page;
               $page=$request->page;
           }
       }

       if(isset($request->column_sort)) {
         $str = $str.'_sort_'. $request->column_sort;
         $column=$request->column_sort;
           if(isset($request->p_sort)) {
             $str = $str.'_'. $request->p_sort;
             $sort=$request->p_sort;
           }
       }

       //$datacache =Cache::remember(env('APP_ENV', 'dev').$str, 5 * 60, function()use($search, $mc_id) {

        $data = array();

        $rumahsinggah = new TblRumahSinggah;

        $rumahsinggah->setConnection('pgsql3');
        $rumahsinggah = $rumahsinggah->select('mpro.mpro_id','mpro.mpro_name',DB::raw("count(table_rumahsinggah.id) as jumlah"))
                    ->join('master_company as mc', 'mc.mc_id','table_rumahsinggah.mc_id')
                    ->leftjoin('master_provinsi as mpro', 'mpro.mpro_id','table_rumahsinggah.prov_id')
                    //  ->leftjoin('master_kabupaten as mkab', 'mkab.mkab_id','table_rumahsinggah.kota_id');
                    ->leftJoin('master_kabupaten as mkab', function($q)
                        {
                            $q->on('mkab.mkab_id', '=', 'table_rumahsinggah.kota_id')
                                ->on('mkab.mkab_mpro_id', '=', 'mpro.mpro_id');
                        });

        if(isset($mc_id)) {
                $rumahsinggah =$rumahsinggah->where('mc.mc_id', $mc_id);
        }

        if(isset($search)) {
            $rumahsinggah = $rumahsinggah->where(DB::raw("lower(TRIM(mpro.mpro_name))"),'like','%'.strtolower(trim($search)).'%');
        }

        $rumahsinggah = $rumahsinggah->groupBy('mpro.mpro_id','mpro.mpro_name');

        if(isset($column)) {
            if(isset($sort)) {
                $rumahsinggah = $rumahsinggah->orderBy($column,$sort);
            }else{
                $rumahsinggah = $rumahsinggah->orderBy($column,"asc");
            }
        }else{
            $rumahsinggah = $rumahsinggah->orderBy('mpro.mpro_name', 'asc');
        }


        $jmltotal=(count($rumahsinggah->get()));
        if(isset($limit)) {
            $rumahsinggah = $rumahsinggah->limit($limit);
            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

            if (isset($page)) {
                $offset = ((int)$page -1) * (int)$limit;
                $rumahsinggah = $perimeter->offset($offset);
            }
        }
        $rumahsinggah= $rumahsinggah->get();
        foreach($rumahsinggah as $itemrs){
          $data[] = array(
              "id_provinsi" => $itemrs->mpro_id,
              "provinsi" => $itemrs->mpro_name,
              "jumlah" => $itemrs->jumlah,
            );
        }


       return response()->json(['status' => 200, 'page_end' =>$endpage,'data' => $data]);
    }

    public function getJumlahRumahSinggah(Request $request){
      $str = "_jumlah_rumah_singgah_";

      $mc_id = null;


       if(isset($request->mc_id)){
            $str = $str.'_mc_id_'. str_replace(' ','_',$request->mc_id);
            $mc_id=$request->mc_id;
       }


       //$datacache =Cache::remember(env('APP_ENV', 'dev').$str, 5 * 60, function()use($search, $mc_id) {

        $data = array();

        $rumahsinggah = new TblRumahSinggah;

        $rumahsinggah->setConnection('pgsql3');
        $rumahsinggah = $rumahsinggah->select(DB::raw("count(table_rumahsinggah.id) as jumlah"))
                    ->join('master_company as mc', 'mc.mc_id','table_rumahsinggah.mc_id')
                    ->leftjoin('master_provinsi as mpro', 'mpro.mpro_id','table_rumahsinggah.prov_id')
                    //  ->leftjoin('master_kabupaten as mkab', 'mkab.mkab_id','table_rumahsinggah.kota_id');
                    ->leftJoin('master_kabupaten as mkab', function($q)
                        {
                            $q->on('mkab.mkab_id', '=', 'table_rumahsinggah.kota_id')
                                ->on('mkab.mkab_mpro_id', '=', 'mpro.mpro_id');
                        });

        if(isset($mc_id)) {
                $rumahsinggah =$rumahsinggah->where('mc.mc_id', $mc_id);
        }

        $rumahsinggah= $rumahsinggah->get();

          $data[] = array(
              "jumlah_data" =>$rumahsinggah[0]->jumlah
            );


       return response()->json(['status' => 200,'data' => $data]);
    }

    public function getGroupRumahSinggahByProvKota($id_provinsi,Request $request){
      $str = "_daftar_rumah_singgah_by_prov_kota";
      $search = null;
      $mc_id = null;
      $limit = null;
      $page = null;
      $endpage = 1;
      $column = null;
      $sort = null;

       if(isset($request->search)){
            $str = $str.'_searh_'. str_replace(' ','_',$request->search);
            $search=$request->search;
       }

       if(isset($request->mc_id)){
            $str = $str.'_mc_id_'. str_replace(' ','_',$request->mc_id);
            $mc_id=$request->mc_id;
       }

       if(isset($request->limit)){
           $str = $str.'_limit_'. $request->limit;
           $limit=$request->limit;
           if(isset($request->page)){
               $str = $str.'_page_'. $request->page;
               $page=$request->page;
           }
       }

       if(isset($request->column_sort)) {
         $str = $str.'_sort_'. $request->column_sort;
         $column=$request->column_sort;
           if(isset($request->p_sort)) {
             $str = $str.'_'. $request->p_sort;
             $sort=$request->p_sort;
           }
       }

       //$datacache =Cache::remember(env('APP_ENV', 'dev').$str, 5 * 60, function()use($search, $mc_id) {

        $data = array();

        $rumahsinggah = new TblRumahSinggah;

        $rumahsinggah->setConnection('pgsql3');
        $rumahsinggah = $rumahsinggah->select('mpro.mpro_id','mpro.mpro_name','mkab.mkab_id','mkab.mkab_name',DB::raw("count(table_rumahsinggah.id) as jumlah"))
                    ->join('master_company as mc', 'mc.mc_id','table_rumahsinggah.mc_id')
                    ->leftjoin('master_provinsi as mpro', 'mpro.mpro_id','table_rumahsinggah.prov_id')
                    //  ->leftjoin('master_kabupaten as mkab', 'mkab.mkab_id','table_rumahsinggah.kota_id');
                    ->leftJoin('master_kabupaten as mkab', function($q)
                        {
                            $q->on('mkab.mkab_id', '=', 'table_rumahsinggah.kota_id')
                                ->on('mkab.mkab_mpro_id', '=', 'mpro.mpro_id');
                        });
        $rumahsinggah =$rumahsinggah->where('mpro.mpro_id', $id_provinsi);

        if(isset($mc_id)) {
                $rumahsinggah =$rumahsinggah->where('mc.mc_id', $mc_id);
        }

        if(isset($search)) {
            $rumahsinggah = $rumahsinggah->where(DB::raw("lower(TRIM(mpro.mpro_name))"),'like','%'.strtolower(trim($search)).'%');
        }

        $rumahsinggah = $rumahsinggah->groupBy('mpro.mpro_id','mpro.mpro_name','mkab.mkab_id','mkab.mkab_name');

        if(isset($column)) {
            if(isset($sort)) {
                $rumahsinggah = $rumahsinggah->orderBy($column,$sort);
            }else{
                $rumahsinggah = $rumahsinggah->orderBy($column,"asc");
            }
        }else{
            $rumahsinggah = $rumahsinggah->orderBy('mkab.mkab_name', 'asc');
        }


        $jmltotal=(count($rumahsinggah->get()));
        if(isset($limit)) {
            $rumahsinggah = $rumahsinggah->limit($limit);
            $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

            if (isset($page)) {
                $offset = ((int)$page -1) * (int)$limit;
                $rumahsinggah = $perimeter->offset($offset);
            }
        }
        $rumahsinggah= $rumahsinggah->get();
        foreach($rumahsinggah as $itemrs){
          $data[] = array(
              "id_provinsi" => $itemrs->mpro_id,
              "provinsi" => $itemrs->mpro_name,
              "id_kota" => $itemrs->mkab_id,
              "kota" => $itemrs->mkab_name,
              "jumlah" => $itemrs->jumlah,
            );
        }

      //        return $data;

       //});
       return response()->json(['status' => 200, 'page_end' =>$endpage,'data' => $data]);
    }



    //Get Status Monitoring Perimeter Level
    public function getRumahSinggahById($id){
       //$datacache =Cache::remember(env('APP_ENV', 'dev')."_layanan_produk_by_". $id_produk, 5 * 60, function()use($id_produk) {

        $data = array();

        $rumahsinggah = new TblRumahSinggah;
        $rumahsinggah->setConnection('pgsql3');
        $rumahsinggah = $rumahsinggah->select(DB::raw("table_rumahsinggah.*"),'mc.mc_name','mc.mc_id',
                            'mpro.mpro_name','mkab.mkab_name','mpro.mpro_id','mkab.mkab_id')
                            ->join('master_company as mc', 'mc.mc_id','table_rumahsinggah.mc_id')
                            ->leftjoin('master_provinsi as mpro', 'mpro.mpro_id','table_rumahsinggah.prov_id')
                            ->leftjoin('master_kabupaten as mkab', 'mkab.mkab_id','table_rumahsinggah.kota_id')
                            ->where('table_rumahsinggah.id',$id)->first();
        if ($rumahsinggah != null){
            $datastatuskasus=[];
            $datafasilitas_rumah=[];
            $datakriteria_orang=[];
          if (isset($rumahsinggah->jenis_kasus)){
            $statuskasus = new MstStsKasus;
            $statuskasus->setConnection('pgsql3');
            $statuskasus = $statuskasus->whereIn('msk_id',explode(',',str_replace("'","",$rumahsinggah->jenis_kasus)))->get();

            //dd($statuskasus);
            $datastatuskasus=[];
              foreach ($statuskasus as $sk) {
                //  dd($sk);
                $datastatuskasus[] = array(
                  "id_kasus" => $sk['msk_id'],
                  "kasus" => $sk['msk_name'],
                  "kasus2" => $sk['msk_name2'],

                );
              }
          }
          if (isset($rumahsinggah->fas_rumah_id)){
            $fasilitas_rumah = new MstFasilitasRumah;
            $fasilitas_rumah->setConnection('pgsql3');
            $fasilitas_rumah = $fasilitas_rumah->whereIn('id',explode(',',str_replace("'","",$rumahsinggah->fas_rumah_id)))->get();

            $datafasilitas_rumah=[];
              foreach ($fasilitas_rumah as $fr) {
                $datafasilitas_rumah[] = array(
                  "id_fasilitas" => $fr->id,
                  "fasilitas" => $fr->jenis
                );
              }
          }
          if (isset($rumahsinggah->fas_rumah_id)){
            $kriteria_orang = new MstKriteriaOrang;
            $kriteria_orang->setConnection('pgsql3');
            $kriteria_orang = $kriteria_orang->whereIn('id',explode(',',str_replace("'","",$rumahsinggah->kriteria_id)))->get();
            $datakriteria_orang=[];
              foreach ($kriteria_orang as $ko) {
                $datakriteria_orang[] = array(
                  "id_kriteria" => $ko->id,
                  "kriteria" => $ko->jenis
                );
              }

          }


              $data[] = array(
                "id_rumah_singgah"=>$rumahsinggah->id,
                "nama_rumah_singgah"=>$rumahsinggah->nama_rumahsinggah,
                "alamat"=>$rumahsinggah->alamat,
                "kd_perusahaan" => $rumahsinggah->mc_id,
                "nama_perusahaan" => $rumahsinggah->mc_name,
                "id_provinsi" => $rumahsinggah->mpro_id,
                "provinsi" => $rumahsinggah->mpro_name,
                "id_kota" => $rumahsinggah->mkab_id,
                "kota" => $rumahsinggah->mkab_name,
                "pic" => $rumahsinggah->pic_nik,
                "pic_kontak" => $rumahsinggah->pic_kontak,
                "available" => $rumahsinggah->ruangan_available,
                "biaya" => $rumahsinggah->biaya_ygdiperlukan,
                "bayar" => $rumahsinggah->membayar,
                "kapasitas" =>  $rumahsinggah->kapasitas,
                "keterangan" =>  $rumahsinggah->keterangan,
                "file" =>  (($rumahsinggah->file <> '') ? '/rumahsinggah/'. $rumahsinggah->file:''),
                "file2" =>  (($rumahsinggah->file2 <> '') ? '/rumahsinggah/'. $rumahsinggah->file2:''),
                "jenis_kasus" =>  $datastatuskasus,
                "fasilitas_rumah" =>  $datafasilitas_rumah,
                "kriteria_orang" =>  $datakriteria_orang
              );

        return array('status' => 200,'data' => $data);
        } else {
          return response()->json(['status' => 404,'message' => 'Data Tidak Ditemukan'])->setStatusCode(404);
        }

    }




    //POST
    public function addRumahSinggah(Request $request){
        $this->validate($request, [
          'nama_rumah_singgah' => 'required',
          'alamat' => 'required',
          'id_provinsi' => 'required',
          'id_kota' => 'required',
          'kapasitas' => 'required',
          'kd_perusahaan'=>'required',
            'available'=>'required',
            'keterangan'=>'required',
            'pic'=>'required',
            'pic_kontak'=>'required'
        ]);


            $rumahsinggah= New TblRumahSinggah();
            $rumahsinggah->setConnection('pgsql');

            $rumahsinggah->nama_rumahsinggah = $request->nama_rumah_singgah;
            $rumahsinggah->alamat =$request->alamat;;
            $rumahsinggah->mc_id = $request->kd_perusahaan;
            $rumahsinggah->prov_id = $request->id_provinsi;
            $rumahsinggah->kota_id = $request->id_kota;
            $rumahsinggah->pic_nik = $request->pic;
            $rumahsinggah->pic_kontak = $request->pic_kontak;
            $rumahsinggah->ruangan_available = $request->available;
            $rumahsinggah->biaya_ygdiperlukan =  $request->biaya;
            $rumahsinggah->membayar = $request->bayar;
            $rumahsinggah->kapasitas = $request->kapasitas;
            $rumahsinggah->keterangan = $request->keterangan;
            $rumahsinggah->jenis_kasus = $request->jenis_kasus;
            $rumahsinggah->fas_rumah_id = $request->fasilitas_rumah;
            $rumahsinggah->kriteria_id = $request->kriteria_orang;
            $file1 = $request->file;
            $file2 = $request->file2;
            $rumahsinggah->user_insert = Auth::guard('api')->user()->id;

            if(!Storage::exists('/app/public/rumahsinggah')) {
                Storage::disk('public')->makeDirectory('/rumahsinggah/');
            }

            $destinationPath = storage_path().'/app/public/rumahsinggah' ;

            $name1 = NULL;

            if ($request->file != null || $request->file != '') {
                $img1 = explode(',', $file1);
                $image1 = $img1[1];
                $filedecode1 = base64_decode($image1);
                $name1 = round(microtime(true) * 1000).'.jpg';

                Image::make($filedecode1)->resize(700, NULL, function ($constraint) {
                      $constraint->aspectRatio();
                })->save($destinationPath.'/'.$name1);

            }

            $name2 = NULL;

            if ($request->file2 != null || $request->file2 != '') {
                $img2 = explode(',', $file2);
                $image2= $img2[1];
                $filedecode2 = base64_decode($image2);
                $name2 = round(microtime(true) * 1000).'.jpg';

                Image::make($filedecode2)->resize(700, NULL, function ($constraint) {
                      $constraint->aspectRatio();
                })->save($destinationPath.'/'.$name2);

            }
            $rumahsinggah->file = $name1;
            $rumahsinggah->file2 = $name2;

        if($rumahsinggah->save()) {
            return response()->json(['status' => 200, 'message' => 'Data Berhasil Disimpan']);
        }
         else {
             return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
         }

    }

    //POST
    public function updateRumahSinggah($id,Request $request){
        $this->validate($request, [
          'nama_rumah_singgah' => 'required',
          'alamat' => 'required',
          'id_provinsi' => 'required',
          'id_kota' => 'required',
          'kapasitas' => 'required',
          'kd_perusahaan'=>'required',
            'available'=>'required',
            'keterangan'=>'required',
            'pic'=>'required',
            'pic_kontak'=>'required'
        ]);


            $rumahsinggah= TblRumahSinggah::find($id);
            $rumahsinggah->nama_rumahsinggah = $request->nama_rumah_singgah;
            $rumahsinggah->alamat =$request->alamat;;
            $rumahsinggah->mc_id = $request->kd_perusahaan;
            $rumahsinggah->prov_id = $request->id_provinsi;
            $rumahsinggah->kota_id = $request->id_kota;
            $rumahsinggah->pic_nik = $request->pic;
            $rumahsinggah->pic_kontak = $request->pic_kontak;
            $rumahsinggah->ruangan_available = $request->available;
            $rumahsinggah->biaya_ygdiperlukan =  $request->biaya;
            $rumahsinggah->membayar = $request->bayar;
            $rumahsinggah->kapasitas = $request->kapasitas;
            $rumahsinggah->keterangan = $request->keterangan;
            $rumahsinggah->jenis_kasus = $request->jenis_kasus;
            $rumahsinggah->fas_rumah_id = $request->fasilitas_rumah;
            $rumahsinggah->kriteria_id = $request->kriteria_orang;
            $file1 = $request->file;
            $file2 = $request->file2;
            $rumahsinggah->user_update = Auth::guard('api')->user()->id;

            if(!Storage::exists('/app/public/rumahsinggah')) {
                Storage::disk('public')->makeDirectory('/rumahsinggah/');
            }

            $destinationPath = storage_path().'/app/public/rumahsinggah' ;

            if (isset($request->file)){
              $name1 = NULL;

              if ($request->file != null || $request->file != '') {
                  $img1 = explode(',', $file1);
                  $image1 = $img1[1];
                  $filedecode1 = base64_decode($image1);
                  $name1 = round(microtime(true) * 1000).'.jpg';

                  Image::make($filedecode1)->resize(700, NULL, function ($constraint) {
                        $constraint->aspectRatio();
                  })->save($destinationPath.'/'.$name1);

              }
              $rumahsinggah->file = $name1;
            }

            if (isset($request->file2)){

              $name2 = NULL;

              if ($request->file2 != null || $request->file2 != '') {
                  $img2 = explode(',', $file2);
                  $image2= $img2[1];
                  $filedecode2 = base64_decode($image2);
                  $name2 = round(microtime(true) * 1000).'.jpg';

                  Image::make($filedecode2)->resize(700, NULL, function ($constraint) {
                        $constraint->aspectRatio();
                  })->save($destinationPath.'/'.$name2);

              }
              $rumahsinggah->file2 = $name2;
            }


        if($rumahsinggah->save()) {
            return response()->json(['status' => 200, 'message' => 'Data Berhasil Disimpan']);
        }
         else {
             return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
         }

    }
    //POST


}
