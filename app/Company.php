<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class Company extends Model
{
    protected $table = 'master_company';
	protected $primaryKey = 'mc_id';
}
