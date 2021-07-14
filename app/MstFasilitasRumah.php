<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class MstFasilitasRumah extends Model {
    protected $table = 'master_fasilitas_rumah';
	protected $primaryKey = 'id';
	protected $fillable = [
	    'jenis'
    ];
}
