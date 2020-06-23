<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class TmpPerimeter extends Model
{
    protected $table = 'tmp_perimeter';
	protected $fillable = [
        'region', 'perimeter','k_perimeter','pic','nik_pic','level','fo','nik_fo','keterangan','kd_perusahaan','status',
		'c1','c2','c3','c4','c5','c6','c7','c8','c9','c10','c11','c12','c13','c14','c15','c16','c17','c18','c19','c20',
		'c21','c22','c23','c24','c25','longitude','latitude','alamat','file'
    ];
}
