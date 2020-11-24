<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class PerimeterKategori extends Model
{
    protected $table = 'master_perimeter_kategori';
	protected $primaryKey = 'mpmk_id';
	public $timestamps = false;
}
