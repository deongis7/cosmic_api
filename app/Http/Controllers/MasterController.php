<?php

namespace App\Http\Controllers;


use App\ClusterRuangan;
use App\Kota;
use App\PerimeterKategori;
use App\Provinsi;

use App\Helpers\AppHelper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;

use DB;
use App\Company;
use App\MstStsKasus;
use App\MstStsPegawai;
use App\MstSosialisasiKategori;
use App\MstFasilitasRumah;
use App\MstKriteriaOrang;
use Intervention\Image\ImageManagerStatic as Image;


class MasterController extends Controller
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

	public function getAllKota(){
		$datacache = Cache::remember(env('APP_ENV', 'prod')."_get_all_kota", 360 * 60, function() {
			$kota = Kota::join('master_provinsi','mpro_id','mkab_mpro_id')->orderBy('mkab_name','asc')->get();

			foreach($kota as $itemkota){
				$data[] = array(
					"id_kota" => $itemkota->mkab_id,
					"kota" => $itemkota->mkab_name,
					"id_provinsi" => $itemkota->mkab_mpro_id,
					"provinsi" => $itemkota->mpro_name,

				);
			}
			return $data;
		});
		return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getKotaByProvinsi($id_provinsi){
		$datacache = Cache::remember(env('APP_ENV', 'prod')."_get_kota_by_prov_".$id_provinsi, 360 * 60, function() use ($id_provinsi) {
			$kota = Kota::join('master_provinsi','mpro_id','mkab_mpro_id')
							->where('mkab_mpro_id',$id_provinsi)
							->orderBy('mkab_name','asc')->get();

			foreach($kota as $itemkota){
				$data[] = array(
					"id_kota" => $itemkota->mkab_id,
					"kota" => $itemkota->mkab_name,
					"id_provinsi" => $itemkota->mkab_mpro_id,
					"provinsi" => $itemkota->mpro_name,

				);
			}
			return $data;
		});
		return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getAllProvinsi(){
		$datacache = Cache::remember(env('APP_ENV', 'prod')."_get_all_prov", 360 * 60, function() {
			$prov = Provinsi::orderBy('mpro_name','asc')->get();

			foreach($prov as $itemprov){
				$data[] = array(

					"id_provinsi" => $itemprov->mpro_id,
					"provinsi" => $itemprov->mpro_name,

				);
			}
			return $data;
		});
		return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getAllCompany(Request $request){
      $search = null;
	    $Path = '/foto_bumn/';
      $str ="_get_all_company";

      if(isset($request->search)){
            $str = $str.'_searh_'. str_replace(' ','_',$request->search);
            $search=$request->search;
      }
	    $datacache = Cache::remember(env('APP_ENV', 'prod').$str, 360 * 60, function() use($Path,$search  ) {
        $data=[];
        if(isset($search)) {
            $company = Company::selectRaw('master_company.*,(master_company.mc_id)::varchar as mc_idx')->where(DB::raw("lower(TRIM(master_company.mc_name))"),'like','%'.strtolower(trim($search)).'%')
                            ->orWhere(DB::raw("lower(TRIM(master_company.mc_name2))"),'like','%'.strtolower(trim($search)).'%')->orderBy('mc_name')->get();
        } else {
          $company = Company::selectRaw('master_company.*,(master_company.mc_id)::varchar as mc_idx')->orderBy('mc_name')->get();
        }
        //dd($company);
	        foreach($company as $com){
	            $data[] = array(
	                "id_perusahaan" => strval($com->mc_idx),
	                "kd_perusahaan" => $com->mc_code,
	                "nm_perusahaan" => $com->mc_name,
	                "foto" =>(( $com->mc_foto == null)?null:$Path.$com->mc_foto)
	            );
	        }
	        return $data;
	    });

	    return response()->json(['status' => 200,'data' => $datacache]);
	}

	public function getDetailCompany($id) {
	    $Path = '/foto_bumn/';

	    $datacache = Cache::remember(env('APP_ENV', 'prod')."_get_company_by_mcid_".$id, 360 * 60, function() use ($id,$Path) {
	        $company = Company::leftJoin('master_sektor','ms_id','mc_msc_id')
	        ->where('mc_id',$id)->where('ms_type','CCOVID')->get();
            $data=[];
	        foreach($company as $com){
	            $data[] = array(
	                "id_perusahaan" => $com->mc_id,
	                "kd_perusahaan" => $com->mc_code,
	                "nm_perusahaan" => $com->mc_name,
	                "foto" => $Path.$com->mc_foto,
	                "sektor" => $com->ms_name
	            );
	        }
	        return $data;
	    });
        return response()->json(['status' => 200,'data' => $datacache]);
	}

    //Upload Foto
    public function uploadFotoBUMN(Request $request){
        $this->validate($request, [
            'kd_perusahaan' => 'required',
            'file_foto' => 'required',
        ]);


        $company = Company::where('mc_id','=',($request->kd_perusahaan))->first();
        if($company==null){
            return response()->json(['status' => 404,'message' => 'Data Perusahaan Tidak Ditemukan'])->setStatusCode(404);
        }
        $kd_perusahaan = $company->mc_id;
        $file = $request->file_foto;

        $user_id = $request->user_id;
        $tanggal= Carbon::now()->format('Y-m-d');


        if(!Storage::exists('/public/foto_bumn')) {
            Storage::disk('public')->makeDirectory('/foto_bumn');
        }

        //$destinationPath = base_path("storage\app\public\aktifitas/").$kd_perusahaan.'/'.$tanggal;
        $destinationPath = storage_path().'/app/public/foto_bumn';
        $name1 = round(microtime(true) * 1000).'.jpg';


        if ($file != null || $file != '') {
            $img1 = explode(',', $file);
            $image1 = $img1[1];
            $filedecode1 = base64_decode($image1);


            Image::make($filedecode1)->resize(700, NULL, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$name1);

        }
        $company->mc_foto = $name1;
        $company->save();
        //$company=Company::update(['mc_id'=> $kd_perusahaan],['mc_foto'=>$name1,'mc_date_update'=> (Carbon::now()->format('Y-m-d h:m:s'))]);

        if($company) {
            return response()->json(['status' => 200,'message' => 'Data Berhasil Disimpan']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
        }
    }

    public function getAllStsKasus(){
        $datacache = Cache::remember(env('APP_ENV', 'prod')."_get_all_mskasus", 360 * 60, function() {
            $data=[];
            $mststskasus = MstStsKasus::all();

            foreach($mststskasus as $msk){
                $data[] = array(
                    "id" => $msk->msk_id,
                    "name" => $msk->msk_name,
                    "name2" => $msk->msk_name2,
                );
            }
            return $data;
        });
        return response()->json(['status' => 200,'data' => $datacache]);
    }

    public function getAllStsKasus2(){
        //$datacache = Cache::remember(env('APP_ENV', 'prod')."_get_all_mskasus2", 360 * 60, function() {
            $data=[];
            $mststskasus = DB::select("SELECT msk_id, msk_name, msk_name2
                                    FROM master_status_kasus msk
                                    WHERE msk.msk_id IN (1,2,3)
    								UNION ALL
    								SELECT 0, 'SEMUA', 'SEMUA'");

            foreach($mststskasus as $msk){
                $data[] = array(
                    "id" => $msk->msk_id,
                    "name" => $msk->msk_name,
                    "name2" => $msk->msk_name2,
                );
            }
            return $data;
       // });
            return response()->json(['status' => 200,'data' => $data]);
    }

    public function getAllStsPegawai(Request $request){
        //var_dump();die;
        $data=[];
        if(isset($request->type)){
            if($request->type=='kasus' || $request->type=='vaksin'){
                $datacache = Cache::remember(env('APP_ENV', 'prod')."_get_all_mspegawai_".$request->type, 360 * 60, function() {
                    $mststspegawai = DB::connection('pgsql3')->select("SELECT msp_id, msp_name, msp_name2
                        FROM master_status_pegawai
                        WHERE msp_id IN (1,2,3,7,8)");
                    foreach($mststspegawai as $msp){
                        $data[] = array(
                            "id" => $msp->msp_id,
                            "name" => $msp->msp_name,
                        );
                    }
                    return $data;
                });
            }else if($request->type=='vaksinlansia'){
                $datacache = Cache::remember(env('APP_ENV', 'prod')."_get_all_mspegawai_".$request->type, 360 * 60, function() {
                    $mststspegawai = DB::connection('pgsql3')->select("SELECT msp_id, msp_name, msp_name2
                            FROM master_status_pegawai
                            WHERE msp_id IN (5,6)");
                    foreach($mststspegawai as $msp){
                        $data[] = array(
                            "id" => $msp->msp_id,
                            "name" => $msp->msp_name,
                        );
                    }
                    return $data;
                });
            }
        }else{
            $datacache = Cache::remember(env('APP_ENV', 'prod')."_get_all_mspegawai", 360 * 60, function() {
                $mststspegawai = DB::connection('pgsql3')->select("SELECT msp_id, msp_name, msp_name2
                            FROM master_status_pegawai");
                foreach($mststspegawai as $msp){
                    $data[] = array(
                        "id" => $msp->msp_id,
                        "name" => $msp->msp_name,
                    );
                }
                return $data;
            });
        }
        return response()->json(['status' => 200,'data' => $datacache]);
    }

    public function getAllSosialisasiKategori(){
        $datacache = Cache::remember(env('APP_ENV', 'prod')."_get_all_sosialisasikategori", 360 * 60, function() {
            $mstsosialisasikategori = MstSosialisasiKategori::all();

            foreach($mstsosialisasikategori as $mslk){
                $data[] = array(
                    "id" => $mslk->mslk_id,
                    "name" => $mslk->mslk_name,
                );
            }
            return $data;
        });
            return response()->json(['status' => 200,'data' => $datacache]);
    }

    public function getKategoriPerimeter(){

        $data=[];
        $datacache = Cache::remember(env('APP_ENV', 'prod')."_get_all_perimeter_kategori", 360 * 60, function()  {
            $kat = PerimeterKategori::orderBy("mpmk_name","asc")->get();

            foreach($kat as $itemkat){
                $data[] = array(
                    "id_kategori_perimeter" => $itemkat->mpmk_id,
                    "nama_kategori_perimeter" => $itemkat->mpmk_name,

                );
            }
            return $data;
        });

        return response()->json(['status' => 200,'data' => $datacache]);
    }

    public function getClusterRuangan(){

        $data=[];
        $datacache = Cache::remember(env('APP_ENV', 'prod')."_get_all_cluster_ruangan", 360 * 60, function()  {
            $mcr = ClusterRuangan::orderBy("mcr_name","asc")->get();

            foreach($mcr as $itemmcr){
                $data[] = array(
                    "id_cluster_ruangan" => $itemmcr->mcr_id,
                    "cluster_ruangan" => $itemmcr->mcr_name,

                );
            }
            return $data;
        });

        return response()->json(['status' => 200,'data' => $datacache]);
    }

    public function getWeekList(){
       $datacache = Cache::remember(env('APP_ENV', 'prod').'_get_weeklist_', 360 * 60, function() {
        $weeks = AppHelper::Months();
        $crweektdate = $weeks['startmonth'].'-'.$weeks['endmonth'];
        
        //var_dump($crweektdate);die;
        $weeksday =  DB::select("SELECT * , CONCAT(v_awal,' s/d ', v_akhir) tgl
              FROM list_aktivitas_week()
              ORDER BY v_rownum DESC");
          //var_dump($weeksday);die;
          foreach ($weeksday as $itemweeksday) {
                $data[] = array(
                    "week" => $itemweeksday->v_week ,
                    "week_name" => 'Week '.$itemweeksday->v_rownum.'( '.$itemweeksday->tgl.' )',

                    "start_date" => $itemweeksday->v_awal,
                    "last_date" => $itemweeksday->v_akhir,

                    "rownum" => $itemweeksday->v_rownum,
                    "range_date" => $itemweeksday->tgl,
                    "current_week" => $itemweeksday->v_week == $crweektdate ? true:false,

                );
            }
            return array('status' => 200, 'data' => $data);
      });
      return $datacache;
    }

    public function getKriteriaOrang(){
        $datacache = Cache::remember(env('APP_ENV', 'prod')."_get_all_kriteria_orang", 360 * 60, function() {
            $data=[];
            $mstkriteriaorang = MstKriteriaOrang::all();

            foreach($mstkriteriaorang as $msk){
                $data[] = array(
                    "id" => $msk->id,
                    "jenis" => $msk->jenis,
                );
            }
            return $data;
        });
        return response()->json(['status' => 200,'data' => $datacache]);
    }

    public function getFasilitasRumah(){
        $datacache = Cache::remember(env('APP_ENV', 'prod')."_get_all_fasilitas_rumah", 360 * 60, function() {
            $data=[];
            $mstfasilitasrumah = MstFasilitasRumah::all();

            foreach($mstfasilitasrumah as $msk){
                $data[] = array(
                    "id" => $msk->id,
                    "jenis" => $msk->jenis,
                );
            }
            return $data;
        });
        return response()->json(['status' => 200,'data' => $datacache]);
    }
}
