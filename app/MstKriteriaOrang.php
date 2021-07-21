<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class MstKriteriaOrang extends Model {
    protected $table = 'master_kriteria_orang';
	protected $primaryKey = 'id';
	protected $fillable = [
	    'jenis'
    ];
}
