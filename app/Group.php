<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class Group  extends Model
{
    protected $table = 'app_groups';
	protected $primaryKey = 'id';
	public $timestamps = false;
}
