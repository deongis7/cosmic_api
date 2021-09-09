<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class MstJenisIndustri extends Model {
    protected $table = 'master_jenis_industri';
	protected $primaryKey = 'id';
	protected $fillable = [
	    'jenis'
    ];
}
