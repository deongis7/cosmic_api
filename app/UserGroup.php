<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class UserGroup  extends Model
{
    protected $table = 'app_users_groups';
	protected $primaryKey = 'id';
	public $timestamps = false;
}
