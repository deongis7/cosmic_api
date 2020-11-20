<?php

namespace App\Http\Controllers;

use App\Perimeter;
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
    public function getPengajuanAtestasi($id_product){
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
        [$lastweek, $twoweek, $id_product ]);

        foreach ($pengajuan as $itempengajuan) {
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

    private function getFile($id_aktifitas,$id_perusahaan){

      $data =[];

      if ($id_aktifitas != null){
      $transaksi_aktifitas_file = TrnAktifitasFile::join("transaksi_aktifitas","transaksi_aktifitas.ta_id","transaksi_aktifitas_file.taf_ta_id")
              ->where("ta_status", "<>", "2")
              ->where("taf_ta_id",$id_aktifitas)->orderBy("taf_id","desc")->limit("2")->get();

        foreach($transaksi_aktifitas_file as $itemtransaksi_aktifitas_file){

          $data[] = array(
              "id_file" => $itemtransaksi_aktifitas_file->taf_id,
              "file" => "/aktifitas/".$id_perusahaan."/".$itemtransaksi_aktifitas_file->taf_date."/".$itemtransaksi_aktifitas_file->taf_file,
              "file_tumb" => "/aktifitas/".$id_perusahaan."/".$itemtransaksi_aktifitas_file->taf_date."/".$itemtransaksi_aktifitas_file->taf_file_tumb,
            );
        }
      }
      return $data;
      }

    //POST
    public function openPerimeter(Request $request){
        $this->validate($request, [
            'id_perimeter_level' => 'required'
        ]);
        $weeks = AppHelper::Weeks();
        $startdate = $weeks['startweek'];
        $enddate = $weeks['endweek'];


        $open = TblPerimeterClosed::where('tbpc_mpml_id', $request->id_perimeter_level)
            ->where('tbpc_startdate', $startdate)
            ->where('tbpc_enddate', $enddate)->first();

        if ($open == null){
            $open= New TblPerimeterClosed();
            $open->setConnection('pgsql');
            $open->tbpc_mpml_id = $request->id_perimeter_level;
            $open->tbpc_requestor = $request->nik;
            $open->tbpc_startdate = $startdate;
            $open->tbpc_enddate = $enddate;
            $open->tbpc_status = 0;
        } else {
            $open->tbpc_requestor = $request->nik;
            $open->tbpc_startdate = $startdate;
            $open->tbpc_enddate = $enddate;
            $open->tbpc_status = 0;
        }

        //delete aktivitas
        $query_delete = "DELETE from transaksi_aktifitas WHERE ta_tpmd_id in (SELECT tpmd_id FROM table_perimeter_detail WHERE tpmd_mpml_id='".$request->id_perimeter_level."') and ta_week = '".$startdate.'-'.$enddate."'";
        DB::connection('pgsql')->update($query_delete);

        if($open->save()) {
            return response()->json(['status' => 200, 'message' => 'Data Berhasil Disimpan']);
        }
         else {
             return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
         }

    }
}
