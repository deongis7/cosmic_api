<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class TrnVaksin extends Model {
    protected $table = 'transaksi_vaksin';
	protected $primaryKey = 'tv_id';
	const CREATED_AT = 'tv_date_insert';
	const UPDATED_AT = 'tv_date_update';
	protected $fillable = [
	    ,'tv_mc_id'
	    ,'tv_nama'
	    ,'tv_msp_id'
	    ,'tv_nip'
	    ,'tv_unit'
	    ,'tv_mjk_id'
	    ,'tv_mkab_id'
	    ,'tv_nik'
	    ,'tv_ttl_date'
	    ,'tv_no_hp'
	    ,'tv_jml_keluarga'
	    ,'tv_nik_pasangan'
	    ,'tv_nama_pasangan'
	    ,'tv_nik_anak1'
	    ,'tv_nama_anak1'
	    ,'tv_nik_anak2'
	    ,'tv_nama_anak2'
	    ,'tv_nik_anak3'
	    ,'tv_nama_anak3'
	    ,'tv_nik_anak4'
	    ,'tv_nama_anak4'
	    ,'tv_nik_anak5'
	    ,'tv_nama_anak5'
	    ,'tv_date1'
	    ,'tv_lokasi1'
	    ,'tv_date2'
	    ,'tv_lokasi2'
	    ,'tv_date3'
	    ,'tv_lokasi3'
	    ,'tv_file1'
	    ,'tv_file1_tumb'
	    ,'tv_file2'
	    ,'tv_file2_tumb'
	    ,'tv_user_insert'
	    ,'tv_user_update'
	    ,'tv_perusahaan'
	    ,'tv_jenis_kelamin'
	    ,'tv_file3'
	    ,'tv_file3_tumb'
	    ,'tv_nomor_pegawai'
	    ,'tv_kd_vaksin'
	    ,'tv_riwayat'
	    ,'tv_alamat'
	    ,'tv_usia'
    ];
}
