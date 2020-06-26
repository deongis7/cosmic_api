<?php

namespace App;


use Illuminate\Database\Eloquent\Model;


class ClusterRuangan  extends Model
{
    protected $table = 'master_cluster_ruangan';
	protected $primaryKey = 'mcr_id';
	const CREATED_AT = 'mcr_date_insert';
	const UPDATED_AT = 'mcr_date_update';
}
