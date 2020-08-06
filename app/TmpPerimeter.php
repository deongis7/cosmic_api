<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class TmpPerimeter extends Model
{
    protected $table = 'tmp_perimeter';
	protected $fillable = [
        'region', 'perimeter','k_perimeter','pic','nik_pic','level','fo','nik_fo','keterangan','kd_perusahaan','status',
		'c1','c2','c3','c4','c5','c6','c7','c8','c9','c10','c11','c12','c13','c14','c15','c16','c17','c18','c19','c20',
		'c21','c22','c23','c24','c25',
		'n1','n2','n3','n4','n5','n6','n7','n8','n9','n10','n11','n12','n13','n14','n15','n16','n17','n18','n19','n20',
		'n21','n22','n23','n24','n25','longitude','latitude','alamat','file','provinsi','kota'
    ];
}
