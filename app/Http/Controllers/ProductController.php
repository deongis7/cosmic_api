<?php

namespace App\Http\Controllers;

use App\Perimeter;
use App\TblPengajuanAtestasi;
use App\TblStatusPengajuanAtestasi;
use App\TblPengajuanSertifikasi;
use App\Helpers\AppHelper;
use App\PerimeterPedulilindungi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;


use DB;
use function Complex\negative;

class ProductController extends Controller
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
    public function getListRiwayatProduk(Request $request){
        $str = "_daftar_riwayat_produk_";
        $search = null;
        $mc_id = null;
        if(isset($request->search)){
            $str = $str.'_searh_'. str_replace(' ','_',$request->search);
            $search=$request->search;
        }

        if(isset($request->mc_id)){
            $str = $str.'_mc_id_'. str_replace(' ','_',$request->mc_id);
            $mc_id=$request->mc_id;
        }
        
        $datacache = Cache::remember(env('APP_ENV', 'prod').$str, 0 * 60, function()use($search, $mc_id) {
            $data = array();
            $weeks = AppHelper::Weeks();
            $startdate = $weeks['startweek'];
            $enddate = $weeks['endweek'];
            $lastweek = Carbon::parse($startdate)->subWeeks(1)->format('Y-m-d').'-'.Carbon::parse($enddate)->subWeeks(1)->format('Y-m-d');
            $twoweek = Carbon::parse($startdate)->subWeeks(2)->format('Y-m-d').'-'.Carbon::parse($enddate)->subWeeks(2)->format('Y-m-d');
            //var_dump($twoweek);die;
//             $weeks = AppHelper::Months();
//             $startdate = $weeks['startmonth'];
//             $enddate = $weeks['endmonth'];
            
            if($search==""){
                $pengajuan = DB::connection('pgsql2')->select( "select a.* from
                    ((select 
                        tps.tbps_id id, 'Sertifikasi CHSE' layanan, master_company.mc_id, mc_name, tps.tbps_date_insert date_insert, tps.tbps_status status, '2' jenis
                        from master_company 
                        left join master_provinsi on master_provinsi.mpro_id = master_company.mc_prov_id
                        left join app_users on master_company.mc_user_update_status = app_users.id
                        join table_pengajuan_sertifikasi tps on master_company.mc_id = tps.tbps_mc_id 
                        where master_company.mc_id='".$mc_id."'
                        order by tbps_id desc limit 5 offset 0)
                        union
                        (select tps.tbpa_id id, 'Atestasi SIBV' layanan, mc_id, mc_name, tbpa_date_insert, tbpa_status, '1' jenis
                            from master_company 
                            join table_pengajuan_atestasi tps on master_company.mc_id = tps.tbpa_mc_id 
                            where mc_id='".$mc_id."'
                            order by tbpa_id desc
                            limit 5 offset 0
                    )) as a"
                );
            } else {
                $pengajuan = DB::connection('pgsql2')->select( "select a.* from
                    ((select 
                        tps.tbps_id id, 'Sertifikasi CHSE' layanan, master_company.mc_id, mc_name, tps.tbps_date_insert date_insert, tps.tbps_status status, '2' jenis
                        from master_company 
                        left join master_provinsi on master_provinsi.mpro_id = master_company.mc_prov_id
                        left join app_users on master_company.mc_user_update_status = app_users.id
                        join table_pengajuan_sertifikasi tps on master_company.mc_id = tps.tbps_mc_id 
                        where master_company.mc_id='".$mc_id."'
                        order by tbps_id desc limit 10 offset 0)
                        union
                      (select tps.tbpa_id id, 'Atestasi SIBV' layanan, mc_id, mc_name, tbpa_date_insert, tbpa_status, '1' jenis
                          from master_company 
                          join table_pengajuan_atestasi tps on master_company.mc_id = tps.tbpa_mc_id 
                          where mc_id='".$mc_id."'
                          order by tbpa_id desc
                          limit 10 offset 0)
                      ) as a where jenis='$search'"
            );
        }    

        foreach ($pengajuan as $field) {
            if($field->status=="1"){
                $status = "Disetujui";
            }elseif ($field->status=="2") {
                $status = "Menunggu Persetujuan";
            }elseif($field->status=="0"){
                $status = "Belum Disetujui";
            }elseif($field->status==""){
                $status = "Belum Disetujui";
            }elseif($field->status=="3"){
                $status = "Belum Disetujui";
            }elseif($field->status=="4"){
                $status = "Di Tolak";
            }

            $data[] = array(
                "jenis"=>$field->jenis,
                "layanan" => $field->layanan,
                "id" => $field->id,
                "mc_id" => $field->mc_id,
                "nama_perusahaan" => $field->mc_name,
                "created" => $field->date_insert,
                "status" => $status
            );
        }
        return $data;

        /*if($search=="sertifikasi"){
            $pengajuan = DB::connection('pgsql2')->select( "select master_company.mc_id as mc_idnya, mc_name, mc_user_update_date, mc_status_sertifikasi, username, mc_nama_pic_sertifikasi, tps.tbps_date_insert, tps.tbps_id, tps.tbps_date_verifikasi, tps.tbps_nama_verifikasi, tps.tbps_status
                from master_company 
                left join master_provinsi on master_provinsi.mpro_id = master_company.mc_prov_id
                left join app_users on master_company.mc_user_update_status = app_users.id
            join table_pengajuan_sertifikasi tps on master_company.mc_id = tps.tbps_mc_id 
            where master_company.mc_id=?
            ORDER BY tps.tbps_id LIMIT 10",[$mc_id]);

            foreach ($pengajuan as $field) {
              if($field->tbps_status=="1"){
                $status = "Disetujui";
              }elseif ($field->tbps_status=="2") {
                  $status = "Menunggu Persetujuan";
              }elseif($field->tbps_status=="0"){
                  $status = "Belum Disetujui";
              }elseif($field->tbps_status==""){
                  $status = "Belum Disetujui";
              }elseif($field->tbps_status=="3"){
                  $status = "Belum Disetujui";
              }elseif($field->tbps_status=="4"){
                  $status = "Di Tolak";
              }

                $data[] = array(
                    "jenis"=>2,
                    "layanan" => "Sertifikasi",
                    "id" => $field->tbps_id,
                    "mc_id" => $field->mc_idnya,
                    "nama_perusahaan" => $field->mc_name,
                    "created" => $field->tbps_date_insert,
                    "status" => $status
                  );
            }
              return $data;
          }else{
            $pengajuan = DB::connection('pgsql2')->select( "select master_company.mc_name, master_company.mc_id, mc_user_update_date,
                mc_nama_pic, mc_status_atestasi, mc_update_date_atestasi, mc_nama_pic_atestasi,
                tps.tbpa_status, tps.tbpa_date_insert, tps.tbpa_nama_pj, tps.tbpa_id,
                tps.tbpa_date_update 
                from master_company 
            join table_pengajuan_atestasi tps on master_company.mc_id = tps.tbpa_mc_id 
            where master_company.mc_id=?
            ORDER BY tps.tbpa_id LIMIT 10",[$mc_id]);

            foreach ($pengajuan as $field) {
              $dataperimeter=[];
                if($field->tbpa_status=="1"){
                    $status = "Disetujui";
                }elseif ($field->tbpa_status=="2") {
                    $status = "Menunggu Persetujuan";
                }elseif($field->tbpa_status=="0"){
                    $status = "Belum Disetujui";
                }elseif($field->tbpa_status=="3"){
                    $status = "Belum Disetujui";
                }elseif($field->tbpa_status=="4"){
                    $status = "Di Tolak";
                }else{
                    $status = "Belum Disetujui";
                }

                $data[] = array(
                    "jenis"=>1,
                    "id" => $field->tbpa_id,
                    "layanan" => "Atestasi",
                    "mc_id" => $field->mc_id,
                    "nama_perusahaan" => $field->mc_name,
                    "created" => $field->tbpa_date_insert,
                    "status" => $status
                  );
            }
              return $data;
          }    */
       });
       return response()->json(['status' => 200, 'data' => $datacache]);
    }

    //GET DETAIL ATESTASI/SERTIFIKASI BY ID
    public function getPengajuanById(Request $request){
       $jenis = null;
       $id_produk = null;
       $str = "";
       if(isset($request->jenis)){
            $str = $str.'_jenis_'. str_replace(' ','_',$request->jenis);
            $jenis=$request->jenis;
        }

        if(isset($request->id_produk)){
            $str = $str.'_idProduk_'. str_replace(' ','_',$request->id_produk);
            $id_produk=$request->id_produk;
        }

        $datacache =Cache::remember(env('APP_ENV', 'prod')."getPengajuanById". $str, 5 * 60, function()use($jenis, $id_produk) {

            $data = array();
            $weeks = AppHelper::Weeks();
            $startdate = $weeks['startweek'];
            $enddate = $weeks['endweek'];
//             $weeks = AppHelper::Months();
//             $startdate = $weeks['startmonth'];
//             $enddate = $weeks['endmonth'];
            $lastweek = Carbon::parse($startdate)->subWeeks(1)->format('Y-m-d').'-'.Carbon::parse($enddate)->subWeeks(1)->format('Y-m-d');
            $twoweek = Carbon::parse($startdate)->subWeeks(2)->format('Y-m-d').'-'.Carbon::parse($enddate)->subWeeks(2)->format('Y-m-d');

            //atestasi
            if($jenis==1){
                $pengajuan = DB::connection('pgsql2')->select( "select tpa.tbpa_id ,tpa.tbpa_mlp_id,mlp.mlp_name,tpa.tbpa_nama_pj ,
                    tpa.tbpa_no_tlp_pj,tpa.tbpa_email_pj , mc.mc_id, mc.mc_name, ms.ms_id,ms.ms_name,rci1.rci_cosmic_index as cosmic_index_lastweek,rci2.rci_cosmic_index as cosmic_index_twoweek,
                    tpa.tbpa_perimeter, tpa.tbpa_date_insert  from table_pengajuan_atestasi tpa
                    left join master_layanan_produk mlp on mlp.mlp_id = tpa.tbpa_mlp_id
                    join master_company mc on mc.mc_id = tpa.tbpa_mc_id
                    left join master_sektor ms on ms.ms_id = mc.mc_msc_id and ms.ms_type ='CCOVID'
                    left join report_cosmic_index rci1 on rci1.rci_mc_id = mc.mc_id and rci1.rci_week = ?
                    left join report_cosmic_index rci2 on rci2.rci_mc_id = mc.mc_id and rci2.rci_week = ?
                    where tpa.tbpa_id=?",
                [$lastweek, $twoweek, $id_produk ]);

                foreach ($pengajuan as $itempengajuan) {
                    $dataperimeter=[];
                    $perimeter = DB::connection('pgsql2')->select( "select * from list_perimeter_by_id_pengajuan(?,?)",
                      [ $itempengajuan->tbpa_id,$lastweek]);
                    foreach ($perimeter as $itemperimeter) {
                        $dataperimeter[] = array(
                            "id_perimeter" => $itemperimeter->v_mpm_id,
                            "nama_perimeter" => $itemperimeter->v_mpm_name,
                            "alamat" => $itemperimeter->v_mpm_alamat,
                            "jml_level" => $itemperimeter->v_jml_mpml,
                            "persen_monitoring" => $itemperimeter->v_mpm_persen
                        );
                    }
                    
                    $data[] = array(
                        "id_pengajuan" => $itempengajuan->tbpa_id,
                        "tgl_pengajuan" => Carbon::parse($itempengajuan->tbpa_date_insert)->format('Y-m-d'),
                        //"id_produk" => $itempengajuan->tbpa_mlp_id,
                        // "nama_produk" => $itempengajuan->mlp_name,
                        // "kd_perusahaan" => $itempengajuan->mc_id,
                        // "nama_perusahaan" => $itempengajuan->mc_name,
                        // "kd_sektor" => $itempengajuan->ms_id,
                        // "nama_sektor" => $itempengajuan->ms_name,
                        "lastweek_cosmic_index" => $itempengajuan->cosmic_index_lastweek,
                        "twoweek_cosmic_index" => $itempengajuan->cosmic_index_twoweek,
                        "nama_penanggung_jawab" => $itempengajuan->tbpa_nama_pj,
                        "no_telp_penanggung_jawab" => $itempengajuan->tbpa_no_tlp_pj,
                        "email_penggung_jawab" => $itempengajuan->tbpa_email_pj,
                        "perimeter" => $dataperimeter
                    );
                }
                return $data;
            }else{
                //sertifikasi
                $pengajuan = DB::connection('pgsql2')->select( "select tps.tbps_id ,tps.tbps_mlp_id,tps.tbps_nama_pj ,
                    tps.tbps_no_tlp_pj,tps.tbps_email_pj , mc.mc_id, mc.mc_name, rci1.rci_cosmic_index as cosmic_index_lastweek,rci2.rci_cosmic_index as cosmic_index_twoweek, ms.ms_id, ms_name,
                    tps.tbps_perimeter, tps.tbps_date_insert 
                    from table_pengajuan_sertifikasi tps
                    join master_company mc on mc.mc_id = tps.tbps_mc_id
                    left join master_sektor ms on ms.ms_id = mc.mc_msc_id and ms.ms_type ='CCOVID'
                    left join report_cosmic_index rci1 on rci1.rci_mc_id = mc.mc_id and rci1.rci_week = ?
                    left join report_cosmic_index rci2 on rci2.rci_mc_id = mc.mc_id and rci2.rci_week = ?
                    where tps.tbps_id=?",
                [$lastweek, $twoweek, $id_produk ]);

                foreach ($pengajuan as $itempengajuan) {
                    $dataperimeter=[];
                    $perimeter = DB::connection('pgsql2')->select( "select * from list_perimeter_by_id_pengajuan(?,?)",
                    [$itempengajuan->tbps_id,$lastweek]);
                    foreach ($perimeter as $itemperimeter) {
                        $dataperimeter[] = array(
                            "id_perimeter" => $itemperimeter->v_mpm_id,
                            "nama_perimeter" => $itemperimeter->v_mpm_name,
                            "alamat" => $itemperimeter->v_mpm_alamat,
                            "jml_level" => $itemperimeter->v_jml_mpml,
                            "persen_monitoring" => $itemperimeter->v_mpm_persen
                        );
                    }
                    $data[] = array(
                      "id_pengajuan" => $itempengajuan->tbps_id,
                      "tgl_pengajuan" => Carbon::parse($itempengajuan->tbps_date_insert)->format('Y-m-d'),
                      /*"id_produk" => $itempengajuan->tbps_mlp_id,
                      "nama_produk" => "Sertifikasi",
                      "kd_perusahaan" => $itempengajuan->mc_id,
                      "nama_perusahaan" => $itempengajuan->mc_name,
                      "kd_sektor" => $itempengajuan->ms_id,
                      "nama_sektor" => $itempengajuan->ms_name,*/
                      "lastweek_cosmic_index" => $itempengajuan->cosmic_index_lastweek,
                      "twoweek_cosmic_index" => $itempengajuan->cosmic_index_twoweek,
                      "nama_penanggung_jawab" => $itempengajuan->tbps_nama_pj,
                      "no_telp_penanggung_jawab" => $itempengajuan->tbps_no_tlp_pj,
                      "email_penggung_jawab" => $itempengajuan->tbps_email_pj,
                      "perimeter" => $dataperimeter
                    );
                }
            return $data;
            }    
        });
        return response()->json(['status' => 200, 'data' => $datacache]);
    }

    //Get Status Monitoring Perimeter Level
    public function getPengajuanAtestasi($id_produk){
        $datacache =Cache::remember(env('APP_ENV', 'prod')."_layanan_produk_by_". $id_produk, 5 * 60, function()use($id_produk) {

            $data = array();
            $weeks = AppHelper::Weeks();
            $startdate = $weeks['startweek'];
            $enddate = $weeks['endweek'];
            $lastweek  =Carbon::parse($startdate)->subWeeks(1)->format('Y-m-d').'-'.Carbon::parse($enddate)->subWeeks(1)->format('Y-m-d');
            $twoweek  =Carbon::parse($startdate)->subWeeks(2)->format('Y-m-d').'-'.Carbon::parse($enddate)->subWeeks(2)->format('Y-m-d');
    
    
            $pengajuan = DB::connection('pgsql2')->select( "select tpa.tbpa_id ,tpa.tbpa_mlp_id,mlp.mlp_name,tpa.tbpa_nama_pj ,
                tpa.tbpa_no_tlp_pj,tpa.tbpa_email_pj , mc.mc_id, mc.mc_name, ms.ms_id,ms.ms_name,rci1.rci_cosmic_index as cosmic_index_lastweek,rci2.rci_cosmic_index as cosmic_index_twoweek,
                tpa.tbpa_perimeter, tpa.tbpa_date_insert  from table_pengajuan_atestasi tpa
                join master_layanan_produk mlp on mlp.mlp_id = tpa.tbpa_mlp_id
                join master_company mc on mc.mc_id = tpa.tbpa_mc_id
                left join master_sektor ms on ms.ms_id = mc.mc_msc_id and ms.ms_type ='CCOVID'
                left join report_cosmic_index rci1 on rci1.rci_mc_id = mc.mc_id and rci1.rci_week = ?
                left join report_cosmic_index rci2 on rci2.rci_mc_id = mc.mc_id and rci2.rci_week = ?
                where mlp.mlp_id=?",
            [$lastweek, $twoweek, $id_produk ]);

            foreach ($pengajuan as $itempengajuan) {
                $dataperimeter=[];
                $perimeter = DB::connection('pgsql2')->select( "select * from list_perimeter_by_id_pengajuan(?,?)",
                    [ $itempengajuan->tbpa_id,$lastweek]);
                    foreach ($perimeter as $itemperimeter) {
                        $dataperimeter[] = array(
                            "id_perimeter" => $itemperimeter->v_mpm_id,
                            "nama_perimeter" => $itemperimeter->v_mpm_name,
                            "alamat" => $itemperimeter->v_mpm_alamat,
                            "jml_level" => $itemperimeter->v_jml_mpml,
                            "persen_monitoring" => $itemperimeter->v_mpm_persen
                        );
                    }
                $data[] = array(
                    "id_pengajuan" => $itempengajuan->tbpa_id,
                    "tgl_pengajuan" => Carbon::parse($itempengajuan->tbpa_date_insert)->format('Y-m-d'),
                    "id_produk" => $itempengajuan->tbpa_mlp_id,
                    "nama_produk" => $itempengajuan->mlp_name,
                    "kd_perusahaan" => $itempengajuan->mc_id,
                    "nama_perusahaan" => $itempengajuan->mc_name,
                    "kd_sektor" => $itempengajuan->ms_id,
                    "nama_sektor" => $itempengajuan->ms_name,
                    "lastweek_cosmic_index" => $itempengajuan->cosmic_index_lastweek,
                    "twoweek_cosmic_index" => $itempengajuan->cosmic_index_twoweek,
                    "nama_penanggung_jawab" => $itempengajuan->tbpa_nama_pj,
                    "no_telp_penanggung_jawab" => $itempengajuan->tbpa_no_tlp_pj,
                    "email_penggung_jawab" => $itempengajuan->tbpa_email_pj,
                    "perimeter" => $dataperimeter
                );
            }
            return $data;
       });
       return response()->json(['status' => 200, 'data' => $datacache]);
    }

    //Get Layanan Produk
    public function getLayananProduk(){
        $id_produk=0;
        $datacache =Cache::remember(env('APP_ENV', 'prod')."_layanan_produk_All_".$id_produk, 5 * 60, function()use($id_produk) {
            $data = array();
            $product = DB::connection('pgsql2')->select( "select mlp.* from  master_layanan_produk mlp
                order by mlp.mlp_id asc");

            foreach ($product as $itemproduct) {
                $data[] = array(
                    "id_produk" => $itemproduct->mlp_id,
                    "nama_produk" => $itemproduct->mlp_name,
                    "kd_perusahaan" => $itemproduct->mlp_mc_id,
                    "nama_perusahaan_jasa" => $itemproduct->mlp_by,
                    "deskripsi" => $itemproduct->mlp_desc,
                    "syarat_ketentuan" => $itemproduct->mlp_file_syarat_ketentuan,
                    "file" => $itemproduct->mlp_filename,
                    "status" => ($itemproduct->mlp_active='t'?true:false),
                );
            }
            return $data;
        });
        return response()->json(['status' => 200, 'data' => $datacache]);
    }

    //POST
    public function addPengajuanAtestasi($id_produk,Request $request){
        $this->validate($request, [
            'nama_pj' => 'required',
            'no_telp_pj' => 'required',
            'email_pj' => 'required',
            'kd_perusahaan'=>'required'
        ]);
        
        $pengajuan= New TblPengajuanAtestasi();
        $pengajuan->setConnection('pgsql');
        $pengajuan->tbpa_mc_id = $request->kd_perusahaan;
        $pengajuan->tbpa_mlp_id = $id_produk;
        $pengajuan->tbpa_nama_pj = $request->nama_pj;
        $pengajuan->tbpa_no_tlp_pj = $request->no_telp_pj;
        $pengajuan->tbpa_email_pj = $request->email_pj;
        $pengajuan->tbpa_perimeter = $request->perimeter;
        $pengajuan->tbpa_user_insert = $request->user_id;

        $pengajuan->tbpa_status = 0;

        if($pengajuan->save()) {
            return response()->json(['status' => 200, 'message' => 'Data Berhasil Disimpan']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
        }
    }
    
    //POST
    public function addPengajuanLayanan($id_produk,Request $request){
        $this->validate($request, [
            'nama_pj' => 'required',
            'no_telp_pj' => 'required',
            'email_pj' => 'required',
            'kd_perusahaan'=>'required'
        ]);

        if($id_produk=='1'){
            $pengajuan= New TblPengajuanAtestasi();
            $pengajuan->setConnection('pgsql');
            $pengajuan->tbpa_mc_id = $request->kd_perusahaan;
            $pengajuan->tbpa_mlp_id = $id_produk;
            $pengajuan->tbpa_nama_pj = $request->nama_pj;
            $pengajuan->tbpa_no_tlp_pj = $request->no_telp_pj;
            $pengajuan->tbpa_email_pj = $request->email_pj;
            $pengajuan->tbpa_perimeter = $request->perimeter;
            $pengajuan->tbpa_user_insert = $request->user_id;
            $pengajuan->tbpa_status = 0;
            
            if($pengajuan->save()) {
                return response()->json(['status' => 200, 'message' => 'Data Berhasil Disimpan']);
            } else {
                return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
            }
        } else if($id_produk=='2'||$id_produk=='3'){
            $pengajuan= New TblPengajuanSertifikasi();
            $pengajuan->setConnection('pgsql');
            $pengajuan->tbps_mc_id = $request->kd_perusahaan;
            $pengajuan->tbps_mlp_id = $id_produk;
            $pengajuan->tbps_nama_pj = $request->nama_pj;
            $pengajuan->tbps_no_tlp_pj = $request->no_telp_pj;
            $pengajuan->tbps_email_pj = $request->email_pj;
            $pengajuan->tbps_user_insert = $request->user_id;
            $pengajuan->tbps_status = 0;
            
            if($pengajuan->save()) {
                return response()->json(['status' => 200, 'message' => 'Data Berhasil Disimpan']);
            } else {
                return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
            }
        } else {
            return response()->json(['status' => 404,'message' => 'Data Layanan Tidak DItemukan'])->setStatusCode(404);
        }
    }
    
    public function addPelaporanMandiri($id_produk,Request $request){
        $this->validate($request, [
            'tgl_terbit' => 'required',
            'tgl_berlaku' => 'required',
            'nomor_sertifikat' => 'required',
            'kd_perusahaan'=>'required'
        ]);

        if($id_produk=='1'){
            $pengajuan= New TblPengajuanAtestasi();
            $pengajuan->setConnection('pgsql');
            $pengajuan->tbpa_mc_id = $request->kd_perusahaan;
            $pengajuan->tbpa_mlp_id = $id_produk;
            $pengajuan->tbpa_nama_pj = '';
            $pengajuan->tbpa_no_tlp_pj = '';
            $pengajuan->tbpa_email_pj ='';
            $pengajuan->tbpa_perimeter = $request->perimeter;
            $pengajuan->tbpa_user_insert = $request->user_id;
            $pengajuan->tbpa_tgl_terbit = $request->tgl_terbit;
            $pengajuan->tbpa_tgl_berlaku = $request->tgl_berlaku;
            $pengajuan->tbpa_nomor_sertifikat = $request->nomor_sertifikat;
            $pengajuan->tbpa_status = 1;
            
            if($pengajuan->save()) {
                $idastetasi = $pengajuan->tbpa_id;
                $myArray = explode(',', $request->perimeter);
                
                foreach($myArray as $itemArray){
                    $stpengajuan= New TblStatusPengajuanAtestasi();
                    $stpengajuan->setConnection('pgsql');
                    $stpengajuan->tbspa_tbpa_id = $idastetasi;
                    $stpengajuan->tbspa_mpm_id = $itemArray;
                    $stpengajuan->tbspa_status = 1;
                    $stpengajuan->save();
                }
                return response()->json(['status' => 200, 'message' => 'Data Berhasil Disimpan']);
            } else {
                return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
            }
        } else if($id_produk=='2'||$id_produk=='3'){
            $pengajuan= New TblPengajuanSertifikasi();
            $pengajuan->setConnection('pgsql');
            $pengajuan->tbps_mc_id = $request->kd_perusahaan;
            $pengajuan->tbps_mlp_id = $id_produk;
            $pengajuan->tbps_nama_pj = '';
            $pengajuan->tbps_no_tlp_pj = '';
            $pengajuan->tbps_email_pj = '';
            $pengajuan->tbps_user_insert = $request->user_id;
            $pengajuan->tbps_tgl_terbit = $request->tgl_terbit;
            $pengajuan->tbps_tgl_berlaku = $request->tgl_berlaku;
            $pengajuan->tbps_nomor_sertifikat = $request->nomor_sertifikat;
            $pengajuan->tbps_status = 1;
              
            if($pengajuan->save()) {
                return response()->json(['status' => 200, 'message' => 'Data Berhasil Disimpan']);
            } else {
                return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
            }
        } else {
            return response()->json(['status' => 404,'message' => 'Data Layanan Tidak DItemukan'])->setStatusCode(404);
        }
    }
    
    public function updatePerimeterPL($id, Request $request) {
        date_default_timezone_set('Asia/Jakarta');
        $this->validate($request, [
            'kd_perusahaan' => 'required',
            'nama' => 'required',
            'alamat' => 'required',
            'kategori' => 'required',
            'provinsi' => 'required',
            'kabupaten' => 'required',
            'lantai' => 'required',
            'kapasitas' => 'required',
            'maps' => 'required',
            'pic' => 'required',
            'no_hp' => 'required',
            'email' => 'required',
            'qr' => 'required',
        ]);
        
        $r_user_id = $request->user_id;
        $r_kd_perusahaan = $request->kd_perusahaan;
        $r_nama = $request->nama;
        $r_alamat = $request->alamat;
        $r_kategori = $request->kategori;
        $r_provinsi = $request->provinsi;
        $r_kabupaten = $request->kabupaten;
        $r_lantai = $request->lantai;
        $r_kapasitas = $request->kapasitas;
        $r_maps = $request->maps;
        $r_pic = $request->pic;
        $r_no_hp = $request->no_hp;
        $r_email = $request->email;
        $r_qr = $request->qr;
        
        $datpl = PerimeterPedulilindungi::find($id);
        $datpl->mppl_mc_id = $r_kd_perusahaan;
        $datpl->mppl_name = $r_nama;
        $datpl->mppl_alamat = $r_alamat;
        $datpl->mppl_mpmk_id = $r_kategori;
        $datpl->mppl_mpro_id = $r_provinsi;
        $datpl->mppl_mkab_id = $r_kabupaten;
        $datpl->mppl_jml_lantai = $r_lantai;
        $datpl->mppl_kapasitas = $r_kapasitas;
        $datpl->mppl_gmap = $r_maps;
        $datpl->mppl_pic = $r_pic;
        $datpl->mppl_no_hp = $r_no_hp;
        $datpl->mppl_email = $r_email;
        $datpl->mppl_qr = $r_qr;
        $datpl->mppl_date_update = date('Y-m-d H:i:s');
        if(isset($r_user_id)){
            $datpl->mppl_user_update = $r_user_id;
        }else{
            $datpl->mppl_user_update = Auth::guard('api')->user()->id;
        }
        $datpl->save();
        
        if($datpl->save()) {
            return response()->json(['status' => 200,'message' => 'Data Perimeter Berhasil diUpdate']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Perimeter Gagal diUpdate'])->setStatusCode(500);
        }
    }
    
    public function insertPerimeterPL(Request $request) {
        date_default_timezone_set('Asia/Jakarta');
        $this->validate($request, [
            'kd_perusahaan' => 'required',
            'nama' => 'required',
            'alamat' => 'required',
            'kategori' => 'required',
            'provinsi' => 'required',
            'kabupaten' => 'required',
            'lantai' => 'required',
            'kapasitas' => 'required',
            'maps' => 'required',
            'pic' => 'required',
            'no_hp' => 'required',
            'email' => 'required',
            'qr' => 'required',
        ]);
        
        $r_user_id = $request->user_id;
        $r_kd_perusahaan = $request->kd_perusahaan;
        $r_nama = $request->nama;
        $r_alamat = $request->alamat;
        $r_kategori = $request->kategori;
        $r_provinsi = $request->provinsi;
        $r_kabupaten = $request->kabupaten;
        $r_lantai = $request->lantai;
        $r_kapasitas = $request->kapasitas;
        $r_maps = $request->maps;
        $r_pic = $request->pic;
        $r_no_hp = $request->no_hp;
        $r_email = $request->email;
        $r_qr = $request->qr;
        
        $datpl = new PerimeterPedulilindungi();
        $datpl->mppl_mc_id = $r_kd_perusahaan;
        $datpl->mppl_name = $r_nama;
        $datpl->mppl_alamat = $r_alamat;
        $datpl->mppl_mpmk_id = $r_kategori;
        $datpl->mppl_mpro_id = $r_provinsi;
        $datpl->mppl_mkab_id = $r_kabupaten;
        $datpl->mppl_jml_lantai = $r_lantai;
        $datpl->mppl_kapasitas = $r_kapasitas;
        $datpl->mppl_gmap = $r_maps;
        $datpl->mppl_pic = $r_pic;
        $datpl->mppl_no_hp = $r_no_hp;
        $datpl->mppl_email = $r_email;
        $datpl->mppl_qr = $r_qr;
        $datpl->mppl_date_update = date('Y-m-d H:i:s');
        if(isset($r_user_id)){
            $datpl->mppl_user_update = $r_user_id;
        }else{
            $datpl->mppl_user_update = Auth::guard('api')->user()->id;
        }
        $datpl->save();
        
        if($datpl->save()) {
            return response()->json(['status' => 200,'message' => 'Data Perimeter Berhasil diInsert']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Perimeter Gagal diInsert'])->setStatusCode(500);
        }
    }
    
    public function getCardPerimeterQR($id){
        $data = array();
        $perimeter_qr = DB::connection('pgsql2')->select("SELECT * 
                FROM perimeter_qrpedulilindungi_bymcid('$id')");
        
        foreach($perimeter_qr as $qr){
            $data[] = array(
                "v_judul" => $qr->v_judul,
                "v_jml" => $qr->v_jml
            );
        }
        return response()->json(['status' => 200,'data' =>$data]);
    }
}