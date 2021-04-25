<?php

namespace App\Http\Controllers;

use App\Perimeter;
use App\TblPengajuanAtestasi;
use App\TblStatusPengajuanAtestasi;
use App\TblPengajuanSertifikasi;
use App\Helpers\AppHelper;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
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
    public function getPengajuanAtestasi($id_produk){
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

    }

    //Get Layanan Produk
    public function getLayananProduk(){
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
        }
         else {
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
          }
           else {
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
          }
           else {
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
          }
           else {
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
          }
           else {
               return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
           }
        } else {
           return response()->json(['status' => 404,'message' => 'Data Layanan Tidak DItemukan'])->setStatusCode(404);
        }

    }
}
