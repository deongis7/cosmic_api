<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\HasApiTokens;


class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	 
	protected $table = 'app_users';
	protected $primaryKey = 'id';
    protected $fillable = [																															
        'username', 'first_name','mc_id','active','password'
    ];
	const CREATED_AT = 'date_insert';
	const UPDATED_AT = 'date_update';
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
	
	public function AauthAcessToken(){
		return $this->hasMany('\App\OauthAccessToken');
	}

	public function roles(){
		return $this->belongsToMany('App\Group', 'app_users_groups', 'user_id', 'group_id');
	}
	
	
}
