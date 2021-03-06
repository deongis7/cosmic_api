<?php

namespace App\Http\Controllers;


use App\ClusterRuangan;
use App\Region;
use App\Perimeter;
use App\PerimeterLevel;
use App\PerimeterDetail;
use App\PerimeterKategori;
use App\User;
use App\UserGroup;
use App\Helpers\AppHelper;
use App\TblPerimeterDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Cache;
use Validator;
use DB;
use Illuminate\Support\Facades\Config;

class PerimeterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */


    public function __construct()
    {
        //
    }

	public function index(){

	}
	public function show($id){

	}

	public function store (Request $request){

	}

	//Jumlah Perimeter
	public function getCountPerimeter($id){
		$data = array();
		$region =  Cache::remember(env('APP_ENV', 'dev')."_count_region_by_company_id_". $id, 30 * 60, function()use($id) {
      $reg = new Region;
      $reg->setConnection('pgsql3');
			return count($reg->select('mr_id')->join('master_perimeter','master_perimeter.mpm_mr_id','master_region.mr_id')
      ->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
      ->where('mr_mc_id',$id)->where('master_perimeter.mpm_lockdown',0)->groupBy('mr_id')->get());
		});
		//$region =count(Region::select('mr_id')->join('master_perimeter','master_perimeter.mpm_mr_id','master_region.mr_id')->where('mr_mc_id',$id)->groupBy('mr_id')->get());
		$user =  Cache::remember(env('APP_ENV', 'dev')."_count_userpic_by_company_id_". $id, 30 * 60, function()use($id) {
			return count(DB::connection('pgsql3')->select('select au.username from app_users au
					join master_perimeter_level mpl on mpl.mpml_pic_nik = au.username
					where au.mc_id = ?
					group by au.username',[$id]));
		});
		$perimeter = Cache::remember(env('APP_ENV', 'dev')."_count_perimeter_by_company_id_". $id, 30 * 60, function()use($id) {
      $prm= new Perimeter;
      $prm->setConnection('pgsql3');
      return $prm->join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
					->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
					->where('master_region.mr_mc_id',$id)
					->count();
		});

			$data[] = array(
					"jml_perimeter" => $perimeter,
					"jml_pic" => $user,
					"jml_region" => $region

					);

		return response()->json(['status' => 200,'data' => $data]);

	}


	//Peta Sebaran Perimeter
	public function getPerimeterMap($id){
    $datacache = Cache::remember(env('APP_ENV', 'dev')."_get_perimeter_map_by_company_id_". $id, 30 * 60, function()use($id) {
		$data = array();
    $perimeter = new Perimeter;
    $perimeter->setConnection('pgsql2');
		$perimeter = $perimeter->select('master_perimeter.mpm_id','master_perimeter.mpm_name','master_perimeter.mpm_alamat','master_perimeter.mpm_longitude','master_perimeter.mpm_latitude')
					->join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
					->where('master_region.mr_mc_id',$id)
					->get();
		foreach($perimeter as $itemperimeter){
			$data[] = array(
					"id_perimeter" => $itemperimeter->mpm_id,
					"nama_perimeter" => $itemperimeter->mpm_name,
					"alamat" => $itemperimeter->mpm_alamat,
					"longitude" => str_replace("'","",$itemperimeter->mpm_longitude),
					"latitude" => str_replace("'","",$itemperimeter->mpm_latitude),
				);
		}
    return $data;
  });
		return response()->json(['status' => 200,'data' => $datacache]);

	}

	//Get Perimeter by Kode Perusahaan
	public function getPerimeter($id){
		$datacache = Cache::remember(env('APP_ENV', 'dev')."_get_perimeter_by_company_id_". $id, 10 * 60, function()use($id) {
		$dashboard = array("total_perimeter"=> 0,"sudah_dimonitor"=>0,"belum_dimonitor"=>0,);
		$data = array();
    $perimeter = new Perimeter;
    $perimeter->setConnection('pgsql2');
			//Perimeter::select('master_region.mr_id','master_region.mr_name','master_perimeter_level.mpml_id',
			$perimeter = $perimeter->select('master_region.mr_id','master_region.mr_name','master_perimeter_level.mpml_id',
		    'master_perimeter.mpm_name','master_perimeter.mpm_alamat',
		    'master_perimeter_level.mpml_name','master_perimeter_level.mpml_ket',
		    'master_perimeter_kategori.mpmk_name','userpic.username as nik_pic',
		    'userpic.first_name as pic','userfo.username as nik_fo','userfo.first_name as fo',
		    'master_provinsi.mpro_name', 'master_kabupaten.mkab_name'
		    )
					->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
					->join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
					->join('master_perimeter_kategori','master_perimeter_kategori.mpmk_id','master_perimeter.mpm_mpmk_id')
					->leftjoin('app_users as userpic','userpic.username','master_perimeter_level.mpml_pic_nik')
					->leftjoin('app_users as userfo','userfo.username','master_perimeter_level.mpml_me_nik')
					->leftjoin('master_provinsi','master_provinsi.mpro_id','master_perimeter.mpm_mpro_id')
					->leftjoin('master_kabupaten','master_kabupaten.mkab_id','master_perimeter.mpm_mkab_id')
					->where('master_region.mr_mc_id',$id)
					->orderBy('master_region.mr_name', 'asc')
					->orderBy('master_perimeter.mpm_name', 'asc')
					->orderBy('master_perimeter_level.mpml_name', 'asc')
					->where('master_perimeter.mpm_lockdown',0)
					->get();

		//});
			$totalperimeter = $perimeter->count();
			$totalpmmonitoring = 0;

		foreach($perimeter as $itemperimeter){
      $cluster = new TblPerimeterDetail;
      $cluster->setConnection('pgsql3');
			$cluster = $cluster->where('tpmd_mpml_id',$itemperimeter->mpml_id)->where('tpmd_cek',true)->count();

			$status = $this->getStatusMonitoring($itemperimeter->mpml_id,$cluster);
			$data[] = array(
					"id_region" => $itemperimeter->mr_id,
					"region" => $itemperimeter->mr_name,
					"id_perimeter_level" => $itemperimeter->mpml_id,
					"nama_perimeter" => $itemperimeter->mpm_name.' - '.$itemperimeter->mpml_name,
					"level" => $itemperimeter->mpml_name,
					"keterangan" => $itemperimeter->mpml_ket,
					"alamat" => $itemperimeter->mpm_name,
					"kategori" => $itemperimeter->mpmk_name,
					"nik_pic" => $itemperimeter->nik_pic,
					"pic" => $itemperimeter->pic,
					"nik_fo" => $itemperimeter->nik_fo,
					"fo" => $itemperimeter->fo,
					"status_monitoring" =>($status['status']),
					"percentage" =>($status['percentage']),
			        "provinsi" => $itemperimeter->mpro_name,
			        "kabupaten" => $itemperimeter->mkab_name,
				);
			if ($status['status'] == true ){ $totalpmmonitoring++; }
		}
		//dashboard
				$dashboard = array (
							"total_perimeter"=> $totalperimeter,
							"sudah_dimonitor"=> $totalpmmonitoring,
							"belum_dimonitor"=> $totalperimeter - $totalpmmonitoring
							);
		return array('status' => 200,'data_dashboard' => $dashboard ,'data' => $data);
		});
		return response()->json($datacache);

	}

	//Get Perimeter per Region
	public function getPerimeterbyRegion($id,Request $request){
        $limit = null;
        $page = null;
        $search = null;
        $endpage = 1;
        $str = "_get_perimeter_by_region_". $id;
        if(isset($request->limit)){
            $str = $str.'_limit_'. $request->limit;
            $limit=$request->limit;
            if(isset($request->page)){
                $str = $str.'_page_'. $request->page;
                $page=$request->page;
            }
        }
        if(isset($request->search)){
            $str = $str.'_searh_'. str_replace(' ','_',$request->search);
            $search=$request->search;
        }

        $datacache = Cache::remember(env('APP_ENV', 'dev').$str, 10 * 60, function()use($id,$limit,$page,$endpage,$search) {
            $data = array();
            $perimeter = new Perimeter;
            $perimeter->setConnection('pgsql2');
            $perimeter = $perimeter->select('master_region.mr_id', 'master_region.mr_name',
                'master_perimeter_level.mpml_id', 'master_perimeter.mpm_name',
                'master_perimeter.mpm_alamat', 'master_perimeter_level.mpml_name',
                'master_perimeter_level.mpml_ket', 'master_perimeter_kategori.mpmk_name',
                'userpic.username as nik_pic', 'userpic.first_name as pic',
                'userfo.username as nik_fo', 'userfo.first_name as fo',
                'master_provinsi.mpro_name', 'master_kabupaten.mkab_name')
                ->join('master_perimeter_level', 'master_perimeter_level.mpml_mpm_id', 'master_perimeter.mpm_id')
                ->join('master_region', 'master_region.mr_id', 'master_perimeter.mpm_mr_id')
                ->join('master_perimeter_kategori', 'master_perimeter_kategori.mpmk_id', 'master_perimeter.mpm_mpmk_id')
                ->leftjoin('app_users as userpic', 'userpic.username', 'master_perimeter_level.mpml_pic_nik')
                ->leftjoin('app_users as userfo', 'userfo.username', 'master_perimeter_level.mpml_me_nik')
                ->leftjoin('master_provinsi', 'master_provinsi.mpro_id', 'master_perimeter.mpm_mpro_id')
                ->leftjoin('master_kabupaten', 'master_kabupaten.mkab_id', 'master_perimeter.mpm_mkab_id')
                ->where('master_region.mr_id', $id);
            if(isset($search)) {
                $perimeter = $perimeter->where(DB::raw("lower(TRIM(mpm_name))"),'like','%'.strtolower(trim($search)).'%');
            }
            $perimeter = $perimeter->orderBy('master_perimeter.mpm_name', 'asc')
                ->orderBy('master_perimeter_level.mpml_name', 'asc');
            $jmltotal=($perimeter->count());
            if(isset($limit)) {
                $perimeter = $perimeter->limit($limit);
                $endpage = (int)(ceil((int)$jmltotal/(int)$limit));
                if (isset($page)) {
                    $offset = ((int)$page -1) * (int)$limit;
                    $perimeter = $perimeter->offset($offset);
                }
            }
            $perimeter =$perimeter->get();

            foreach ($perimeter as $itemperimeter) {
                $data[] = array(
                    "id_perimeter_level" => $itemperimeter->mpml_id,
                    "nama_perimeter" => $itemperimeter->mpm_name . ' - ' . $itemperimeter->mpml_name,
                    "level" => $itemperimeter->mpml_name,
                    "keterangan" => $itemperimeter->mpml_ket,
                    "alamat" => $itemperimeter->mpm_name,
                    "kategori" => $itemperimeter->mpmk_name,
                    "nik_pic" => $itemperimeter->nik_pic,
                    "pic" => $itemperimeter->pic,
                    "nik_fo" => $itemperimeter->nik_fo,
                    "fo" => $itemperimeter->fo,
                    "provinsi" => $itemperimeter->mpro_name,
                    "kabupaten" => $itemperimeter->mkab_name,
                );
            }
            return array('page_end' => $endpage,'data' => $data);
        });
		return response()->json(['status' => 200,'page_end' => $datacache['page_end'],'data' => $datacache['data']]);

	}



	//Get Perimeter by Kota
	public function getPerimeterbyKota($kd_perusahaan,$id_kota){

		$data = array();
    $perimeter = new Perimeter;
    $perimeter->setConnection('pgsql3');
		$perimeter = $perimeter->select('master_perimeter.mpm_id','master_perimeter.mpm_name','master_perimeter.mpm_alamat','master_perimeter.mpm_longitude','master_perimeter.mpm_latitude')
                            ->where('master_perimeter.mpm_mc_id',$kd_perusahaan);
		if($id_kota != 0){
				$perimeter = $perimeter->where('master_perimeter.mpm_mkab_id',$id_kota);}
		$perimeter = $perimeter->get();
		foreach($perimeter as $itemperimeter){
			$data[] = array(
					"id_perimeter" => $itemperimeter->mpm_id,
					"nama_perimeter" => $itemperimeter->mpm_name,
					"alamat" => $itemperimeter->mpm_alamat,
					"longitude" => str_replace("'","",$itemperimeter->mpm_longitude),
					"latitude" => str_replace("'","",$itemperimeter->mpm_latitude),
				);
		}
		return response()->json(['status' => 200,'data' => $data]);

	}

	//Get Level/Lantai by Perimeter
	public function getLevelbyPerimeter($id_perimeter){
		$data = array();
    $perimeter = new PerimeterLevel;
    $perimeter->setConnection('pgsql3');
		$perimeter = $perimeter->join('master_perimeter','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
					->where('mpml_mpm_id',$id_perimeter)
					->get();
		foreach($perimeter as $itemperimeter){
			$data[] = array(
					"id_perimeter" => $itemperimeter->mpml_mpm_id,
					"nama_perimeter" => $itemperimeter->mpm_name,
					"id_perimeter_level" => $itemperimeter->mpml_id,
					"level" => 'Lantai '.$itemperimeter->mpml_name,

				);
		}
		return response()->json(['status' => 200,'data' => $data]);

	}

	//Get Cluster per Perimeter Level
	public function getClusterbyPerimeter($id){

		$data = array();

			$perimeter = DB::connection('pgsql3')->select( "select mpm.mpm_id,mpl.mpml_id,tpd.tpmd_id,mcr.mcr_id, mpm.mpm_name, mpk.mpmk_name, mpl.mpml_name,mcr.mcr_name,tpmd_order,mpl.mpml_pic_nik as nikpic,mpl.mpml_me_nik as nikfo from master_perimeter_level mpl
					join master_perimeter mpm on mpm.mpm_id = mpl.mpml_mpm_id
					join master_perimeter_kategori mpk on mpk.mpmk_id = mpm.mpm_mpmk_id
					join table_perimeter_detail tpd on tpd.tpmd_mpml_id = mpl.mpml_id and tpd.tpmd_cek=true
					join master_cluster_ruangan mcr on mcr.mcr_id = tpd.tpmd_mcr_id
					where mpl.mpml_id = ?
					order by mcr.mcr_name asc, tpmd_order asc", [$id]);
			foreach($perimeter as $itemperimeter){


				$status = $this->getStatusMonitoringCluster($itemperimeter->tpmd_id);
				$data[] = array(
						"id_perimeter_level" => $itemperimeter->mpml_id,
						"level" => $itemperimeter->mpml_name,
						"id_perimeter_cluster" => $itemperimeter->tpmd_id,
						"id_cluster" => $itemperimeter->mcr_id,
						"cluster_ruangan" => (($itemperimeter->tpmd_order > 1)? ($itemperimeter->mcr_name.' - '.$itemperimeter->tpmd_order) :$itemperimeter->mcr_name),
						"order" => $itemperimeter->tpmd_order,
						"status" => $status,
					);
			}
			return response()->json(['status' => 200,'data' => $data]);


	}

	//Jumlah Task Force
	public function getCountTaskForce($id){
		$data = array();
    $taskforce = new User;
    $taskforce->setConnection('pgsql2');
		$taskforce = $taskforce->join('master_company','master_company.mc_id','app_users.mc_id')
					->join('app_users_groups','app_users_groups.user_id','app_users.id')
					->where('master_company.mc_id', '=', $id)
					->where(function($query){
							 $query->where('app_users_groups.group_id', '=', 3);
							 $query->orWhere('app_users_groups.group_id', '=', 4);
						 })
					->count();

			$data[] = array(
					"jml_taskforce" => $taskforce,

					);

		return response()->json(['status' => 200,'data' => $data]);

	}

	//Get Task Force per Region
	public function getTaskForce($id,Request $request){
		$param = [];
        $limit = null;
        $page = null;
        $search = null;
        $endpage = 1;
        if(isset($request->limit)){
            $limit=$request->limit;
            if(isset($request->page)){
                $page=$request->page;
            }
        }
        if(isset($request->search)){

            $search=$request->search;
        }
		$querycache = "_get_taskforce_by_company_id_". $id;
		$query = "select app.username,app.first_name, (case when (a1.mpm_mr_id is null) then a2.mpm_mr_id else a1.mpm_mr_id end) as mpm_mr_id,
			(case when (a1.mpm_mr_id is null) then a2.mr_name else a1.mr_name end) as mr_name,app.mc_id,aug.name,
			( CASE WHEN ( a1.mpm_mr_id IS NULL ) AND ( a2.mpm_mr_id IS NULL ) THEN TRUE ELSE FALSE END ) AS unassigned
		from app_users app
		left JOIN (select mp1.mpm_mr_id , mr1.mr_name, mpl1.mpml_pic_nik, mkab1.mkab_id,mkab1.mkab_name,mp1.mpm_mc_id  from master_perimeter_level mpl1
				join master_perimeter mp1 on mpl1.mpml_mpm_id = mp1.mpm_id
				join master_region mr1 on mr1.mr_id = mp1.mpm_mr_id
				left join master_kabupaten mkab1 on mkab1.mkab_id = mp1.mpm_mkab_id) a1 on a1.mpml_pic_nik = app.username and a1.mpm_mc_id = app.mc_id
		left JOIN (select mp2.mpm_mr_id, mr2.mr_name, mpl2.mpml_me_nik, mkab2.mkab_id,mkab2.mkab_name,mp2.mpm_mc_id from master_perimeter_level mpl2
				join master_perimeter mp2  on mpl2.mpml_mpm_id = mp2.mpm_id
				join master_region mr2 on mr2.mr_id = mp2.mpm_mr_id
				left join master_kabupaten mkab2 on mkab2.mkab_id = mp2.mpm_mkab_id) a2 on a2.mpml_me_nik = app.username and a2.mpm_mc_id = app.mc_id
		join app_users_groups aup on aup.user_id = app.id ";
		//cek role
		//dd($request->id_kota);
		if(isset($request->id_role)){
			$querycache = $querycache ."_role_". $request->id_role;
			$query = $query . " and aup.group_id=?";
			$param[] = $request->id_role;
		} else {
			$query = $query . " and (aup.group_id=3 or aup.group_id=4)";
		}
		//klausul where
		$query = $query .  " join  app_groups aug on aup.group_id = aug.id  where app.mc_id = ?";
		$param[] = $id;

		//cek kota
		if(isset($request->id_kota) && $request->id_kota <> 'null'&& $request->id_kota <> ''){
			$querycache = $querycache ."_kota_". $request->id_kota;
			$query = $query . " and ((a1.mkab_id=? or a2.mkab_id=?) or (( a1.mpm_mr_id IS NULL ) AND ( a2.mpm_mr_id IS NULL )))";
			$param[] = $request->id_kota;
			$param[] = $request->id_kota;
		}
        if(isset($search)) {
            $querycache = $querycache.'_searh_'. str_replace(' ','_',$request->search);
            $query = $query ." and (lower(TRIM(app.username)) like ? or lower(TRIM(app.first_name)) like ?) ";
            $param[] = '%'.strtolower(trim($search)).'%';
            $param[] = '%'.strtolower(trim($search)).'%';

        }

		$query=$query ." GROUP BY app.username,app.first_name, (case when (a1.mpm_mr_id is null) then a2.mpm_mr_id else a1.mpm_mr_id end) ,
			(case when (a1.mpm_mr_id is null) then a2.mr_name else a1.mr_name end),app.mc_id,aug.name,
			( CASE WHEN ( a1.mpm_mr_id IS NULL ) AND ( a2.mpm_mr_id IS NULL ) THEN TRUE ELSE FALSE END )
			order by
			( CASE WHEN ( a1.mpm_mr_id IS NULL ) AND ( a2.mpm_mr_id IS NULL ) THEN TRUE ELSE FALSE END ) desc, aug.name desc,app.first_name asc ";
        $jmltotal=count(DB::connection('pgsql2')->select( $query , $param));

        if(isset($limit)) {
           $query=$query ." limit ". $limit;
           $endpage = (int)(ceil((int)$jmltotal/(int)$limit));

            if (isset($page)) {
                $offset = ((int)$page -1) * (int)$limit;
                $query=$query ." offset ". $offset;

            }
        }
		//$datacache = Cache::remember($querycache, 1 * 60, function()use($query,$param) {
			$data = array();
			$taskforce = DB::connection('pgsql2')->select( $query , $param);

			foreach($taskforce as $itemtaskforce){
				$data[] = array(
						"kd_perusahaan" => $itemtaskforce->mc_id,
						"kd_region" => $itemtaskforce->mpm_mr_id,
						"region" => $itemtaskforce->mr_name,
						"nik" => $itemtaskforce->username,
						"username" => $itemtaskforce->username,
						"nama" => $itemtaskforce->first_name,
						"role" => $itemtaskforce->name,
						"unassigned" => $itemtaskforce->unassigned,

						);
			}
			 $data;
			//return $data;
		//});
		return response()->json(['status' => 200,'page_end' => $endpage,'data' => $data]);

	}

		//Get Task Force per Region
	public function getTaskForcebyRegion($id){
		$data = array();
		$taskforce = DB::connection('pgsql3')->select( "select app.username,app.first_name, (case when (a1.mpm_mr_id is null) then a2.mpm_mr_id else a1.mpm_mr_id end) as mpm_mr_id,
			(case when (a1.mpm_mr_id is null) then a2.mr_name else a1.mr_name end) as mr_name,app.mc_id
		from app_users app
		left JOIN (select mp1.mpm_mr_id , mr1.mr_name, mpl1.mpml_pic_nik from master_perimeter_level mpl1
				join master_perimeter mp1  on mpl1.mpml_mpm_id = mp1.mpm_id
				join master_region mr1 on mr1.mr_id = mp1.mpm_mr_id) a1 on a1.mpml_pic_nik = app.username
		left JOIN (select mp2.mpm_mr_id, mr2.mr_name, mpl2.mpml_me_nik from master_perimeter_level mpl2
				join master_perimeter mp2  on mpl2.mpml_mpm_id = mp2.mpm_id
				join master_region mr2 on mr2.mr_id = mp2.mpm_mr_id) a2 on a2.mpml_me_nik = app.username
		join app_users_groups aup on aup.user_id = app.id and (aup.group_id=3 or aup.group_id=4)
		where a1.mpm_mr_id = ? or a2.mpm_mr_id = ?
		GROUP BY app.username,app.first_name, (case when (a1.mpm_mr_id is null) then a2.mpm_mr_id else a1.mpm_mr_id end) ,
			(case when (a1.mpm_mr_id is null) then a2.mr_name else a1.mr_name end),app.mc_id
		order by mpm_mr_id asc,app.first_name asc", [$id,$id]);

		foreach($taskforce as $itemtaskforce){
			$data[] = array(
					"kd_perusahaan" => $itemtaskforce->mc_id,
					"kd_region" => $itemtaskforce->mpm_mr_id,
					"region" => $itemtaskforce->mr_name,
					"nik" => $itemtaskforce->username,
					"nama" => $itemtaskforce->first_name,
					);
		}

		return response()->json(['status' => 200,'data' => $data]);

	}

	//Update Primeter
	public function addTaskForce(Request $request){
		$this->validate($request, [
            'username' => 'required',
            'name' => 'required',
            'kd_perusahaan' => 'required',
            'id_role' => 'required'
        ]);

		$username = $request->username;

		$user= User::where(DB::raw("TRIM(username)"),'=',trim($username))->first();
		if($user == null){
			$user = new User();
			$user->username = $username;
			$user->first_name = $request->name;
			$user->mc_id = $request->kd_perusahaan;
			$user->password =  Hash::make('P@ssw0rd');
			$user->active = 1;

			if($user->save()) {
				$usergroup= UserGroup::updateOrCreate(['user_id' =>$user->id],['group_id' => $request->id_role]);
				return response()->json(['status' => 200,'message' => 'Data Berhasil Disimpan']);
			} else {
				return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
			}
		} else {
			return response()->json(['status' => 403,'message' => 'Data Username Sudah Ada'])->setStatusCode(403);
		}

	}

	//Get Status Monitoring per Cluster
	private function getStatusMonitoringCluster($id_perimeter_cluster){

		$data = array();
        $weeks = AppHelper::Weeks();
		$startdate = $weeks['startweek'];
		$enddate = $weeks['endweek'];

		$clustertrans = DB::connection('pgsql2')->select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
		join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
		join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
		join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
		where tpd.tpmd_id = ? and ta.ta_status = 1 and (ta.ta_date >= ? and ta.ta_date <= ? )
		group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter_cluster, $startdate, $enddate]);


		if ( count($clustertrans)>0) {
			return true;
		} else {
			return false;
		}
	}

	public function getExecutionReport($id){
		Config::set('database.default', 'pgsql3');
		$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_exec_report_". $id, 0 * 60, function()use($id) {
			$data = array();
			$execution = DB::select("
						SELECT *, CASE
                        WHEN CAST(v_persen AS INT) >=100 THEN '#33cc33'
                        WHEN CAST(v_persen AS INT) <50 THEN '#cc2900'
                        ELSE '#ff9933' END as v_color
                        FROM mv_execution_report('$id') 
							");

			foreach($execution as $exec){
				$data[] = array(
					"id" => $exec->v_id,
					"judul" => $exec->v_judul,
					"desc" => $exec->v_desc,
					"color" => $exec->v_color,
					"persen" => $exec->v_persen,
				    "date_update" => $exec->v_update,
				    "update_every" => 'Data Cosmic Index diupdate setiap 2 Jam Sekali'
				);
			}
			return $data;
	    });
	    return response()->json(['status' => 200,'data' => $datacache]);
	}

	//Get Status Monitoring
	private function getStatusMonitoring($id_perimeter_level, $cluster){

		$data = array();
		$weeks = AppHelper::Weeks();
		$startdate = $weeks['startweek'];
		$enddate = $weeks['endweek'];


		$clustertrans = DB::connection('pgsql2')->select( "select tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id from transaksi_aktifitas ta
			join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
			join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
			join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
			where ta.ta_status = 1 and tpd.tpmd_mpml_id = ? and (ta.ta_date >= ? and ta.ta_date <= ? ) and kc.kcar_ag_id = 4
			group by tpd.tpmd_id, tpd.tpmd_mpml_id, tpd.tpmd_mcr_id ", [$id_perimeter_level, $startdate, $enddate]);

		//dd(count($clustertrans));


		if ($cluster <> 0){
			if (($cluster <= count($clustertrans))) {
				//return true;
				return array(
								"status" => true,
								"percentage" => 1);
			} else {
				//return false;
				return array(
								"status" => false,
								"percentage" => round((count($clustertrans)/$cluster),2));
			}
		} else {
			//return false;
			return array(
							"status" => false,
							"percentage" => 0);
		}


	}

	//Get Task Force Detail
	public function getTaskForceDetail($nik){
		$data = array();
    $taskforce = new User;
    $taskforce->setConnection('pgsql3');
		$taskforce = $taskforce->select("app_users.username","app_users.first_name","app_groups.name","app_users_groups.group_id")
					->join("app_users_groups","app_users.id","app_users_groups.user_id")
					->join("app_groups","app_groups.id","app_users_groups.group_id")
					->where(DB::raw("TRIM(app_users.username)"),'=',trim($nik))->first();
		if ($taskforce != null){
			$perimeter = Perimeter::select('master_perimeter_level.mpml_id','master_perimeter.mpm_name',
				'master_perimeter_level.mpml_name', 'master_perimeter_level.mpml_ket','master_perimeter_level.mpml_me_nik', 'master_perimeter_level.mpml_pic_nik')
				->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id');
			if($taskforce->group_id==3){
				$perimeter = $perimeter->where(DB::raw("TRIM(master_perimeter_level.mpml_pic_nik)"),'=',trim($nik));
			} else 	{
				$perimeter = $perimeter->where(DB::raw("TRIM(master_perimeter_level.mpml_me_nik)"),'=',trim($nik));
			}
			$perimeter = $perimeter->orderBy('master_perimeter.mpm_name','asc')
						->orderBy('master_perimeter_level.mpml_name','asc')->get();
			$dataperimeter = array();

			foreach($perimeter as $itemperimeter){
			$dataperimeter[] = array(
					"id_perimeter_level" => $itemperimeter->mpml_id,
					"nama_perimeter" => $itemperimeter->mpm_name,
					"level" => $itemperimeter->mpml_name,
					"keterangan" => $itemperimeter->mpml_ket

					);
			}

			$data = array (
							"username"=>$taskforce->username,
							"name"=> $taskforce->first_name,
							"role"=>  $taskforce->name,
							"task" =>$dataperimeter
			);
			return response()->json(['status' => 200,'data' => $data]);
		} else {
			return response()->json(['status' => 404,'message' => 'User Tidak Ditemukan'])->setStatusCode(404);
		}


	}

    //Detail User
    public function getTaskForceDetailUser($nik){
        $Path = '/profile/';
        $PathCompany = '/foto_bumn/';
        $data = array();
        $user = new User;
        $user->setConnection('pgsql3');
        $user = $user->select('app_users.id','app_users.username','app_users.first_name',
            'master_company.mc_id','master_company.mc_name','app_groups.name',
            'app_users.no_hp','app_users.divisi','app_users.email','app_users.foto','master_company.mc_foto')
            ->join('master_company','master_company.mc_id','app_users.mc_id')
            ->join('app_users_groups','app_users_groups.user_id','app_users.id')
            ->join('app_groups','app_users_groups.group_id','app_groups.id')
            ->where('app_users.username','=',$nik)
            ->first();


        if($user!=null){
            $data[] = array(
                "id" => $user->id,
                "username" => $user->username,
                "name" => $user->first_name,
                "kd_perusahaan" => $user->mc_id,
                "nm_perusahaan" => $user->mc_name,
                "role" => $user->name,
                "no_hp" => $user->no_hp,
                "divisi" => $user->divisi,
                "email" => $user->email,
                "foto" => ($user->foto==null)?null:$Path.$user->foto,
                "foto_bumn" => ($user->mc_foto==null)?null:$PathCompany.$user->mc_foto,
            );
            return response()->json(['status' => 200,'data' => $data]);
        } else {
            return response()->json(['status' => 404,'message' => 'Data Tidak Ditemukan'])->setStatusCode(404);
        }
    }

    public function changePasswordTaskForce($nik,Request $request) {
        $input = $request->all();
        $user= User::where('username',$nik)->first();
        $rules = array(
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        );
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $arr = array("status" => 400, "message" => $validator->errors()->first());
        } else {
            try {
                if ((Hash::check($request->old_password, $user->password)) == false) {
                    $arr = array("status" => 400,
                        "message" => "Check your old password.");
                } else if ((Hash::check($request->new_password, $user->password)) == true) {
                    $arr = array("status" => 400,
                        "message" => "Please enter a password which is not similar then current password.");
                } else if ((Hash::check($request->new_password, '$2y$10$eyLOnXfci/PAI.KuNIULTOJTkluadpdj7FtlzkwhKqasnAHrYdkmq')) == true) {
                    $arr = array("status" => 400,
                        "message" => "Please enter a new password which is not similar then default password.");
                } else {
                    $user->password = Hash::make($input['new_password']);
                    $user->save();
                    $arr = array("status" => 200,
                        "message" => "Profile & Password telah diupdate.");
                }
            } catch (\Exception $ex) {
                if (isset($ex->errorInfo[2])) {
                    $msg = $ex->errorInfo[2];
                } else {
                    $msg = $ex->getMessage();
                }
                $arr = array("status" => 400, "message" => $msg);
            }
        }
        //dd($arr['status']);
        return response()->json($arr)->setStatusCode($arr['status']);
    }

    public function resetPasswordTaskForce($nik) {

        $user= User::where('username',$nik)->first();
        $user->password = Hash::make('P@ssw0rd');
        if($user->save()) {
            return response()->json(['status' => 200,'message' => 'Password telah Direset']);
        } else {
            return response()->json(['status' => 500,'message' => 'Password gagal Direset'])->setStatusCode(500);
        }
    }

    public function deleteTaskForce($nik) {

      $user= User::where('username',$nik)->first();
      if($user!= null){
        if($user->delete()) {
              return response()->json(['status' => 200,'message' => 'User telah Dihapus']);
          } else {
              return response()->json(['status' => 500,'message' => 'User gagal Dihapus'])->setStatusCode(500);
          }
      } else {
        return response()->json(['status' => 404,'message' => 'User Tidak DItemukan'])->setStatusCode(404);

      }

    }


	//Get Perimeter Detail
	public function getDetailPerimeter($id_perimeter_level){
		$data = array();
        $datacluster=[];
        $perimeter = new Perimeter;
        //test pindah ke master
        $perimeter->setConnection('pgsql2');
		$perimeter = $perimeter->select('master_region.mr_id','master_region.mr_name','master_perimeter_level.mpml_id',
		    'master_perimeter.mpm_name','master_perimeter.mpm_alamat',
		    'master_perimeter_level.mpml_name','master_perimeter_level.mpml_ket','master_perimeter_kategori.mpmk_id',
		    'master_perimeter_kategori.mpmk_name','userpic.username as nik_pic',
		    'userpic.first_name as pic','userfo.username as nik_fo','userfo.first_name as fo',
		    'master_provinsi.mpro_name', 'master_kabupaten.mkab_name','master_provinsi.mpro_id', 'master_kabupaten.mkab_id',
            DB::raw("(CASE WHEN tpc.tbpc_status is null THEN 0 ELSE tpc.tbpc_status END) AS status_perimeter"),"tpc.tbpc_alasan"
		    )
					->join('master_perimeter_level','master_perimeter_level.mpml_mpm_id','master_perimeter.mpm_id')
					->join('master_region','master_region.mr_id','master_perimeter.mpm_mr_id')
					->join('master_perimeter_kategori','master_perimeter_kategori.mpmk_id','master_perimeter.mpm_mpmk_id')
					->leftjoin('app_users as userpic','userpic.username','master_perimeter_level.mpml_pic_nik')
					->leftjoin('app_users as userfo','userfo.username','master_perimeter_level.mpml_me_nik')
					->leftjoin('master_provinsi','master_provinsi.mpro_id','master_perimeter.mpm_mpro_id')
					->leftjoin('master_kabupaten','master_kabupaten.mkab_id','master_perimeter.mpm_mkab_id')
                    ->leftjoin("table_perimeter_closed as tpc", function($join)
                    {
                        $join->on("tpc.tbpc_mpml_id","=", "master_perimeter_level.mpml_id");
                        $join->on("tpc.tbpc_startdate","<=",DB::raw("'".Carbon::now()->format("Y-m-d")."'"));
                        $join->on("tpc.tbpc_enddate",">=",DB::raw("'".Carbon::now()->format("Y-m-d")."'"));

                    })
                    ->where('master_perimeter_level.mpml_id',$id_perimeter_level)
					->first();

		if ($perimeter != null){
            $cluster = DB::connection('pgsql2')->select( "SELECT tpd.tpmd_mpml_id,mcr.mcr_id, mcr.mcr_name,count(tpd.tpmd_order) as jumlah FROM  master_cluster_ruangan mcr
                    inner join table_perimeter_detail tpd on tpd.tpmd_mcr_id =mcr.mcr_id and tpd.tpmd_cek=true
                    where tpd.tpmd_mpml_id = ?
                    group by tpd.tpmd_mpml_id,mcr.mcr_id, mcr.mcr_name
                    order by tpd.tpmd_mpml_id asc, mcr.mcr_name asc", [$id_perimeter_level]);

            foreach($cluster as $itemcluster){
                $datacluster[] = array (
                    "id_perimeter_level" => $id_perimeter_level,
                    "id_cluster_ruangan" => $itemcluster->mcr_id,
                    "cluster_ruangan" => $itemcluster->mcr_name,
                    "jumlah" => $itemcluster->jumlah,
                );
            }

			$data = array (
					"id_region" => $perimeter->mr_id,
					"region" => $perimeter->mr_name,
					"id_perimeter_level" => $perimeter->mpml_id,
					"nama_perimeter" => $perimeter->mpm_name.' - '.$perimeter->mpml_name,
					"level" => $perimeter->mpml_name,
					"keterangan" => $perimeter->mpml_ket,
					"alamat" => $perimeter->mpm_name,
					"id_kategori" => $perimeter->mpmk_id,
					"kategori" => $perimeter->mpmk_name,
					"nik_pic" => $perimeter->nik_pic,
					"pic" => $perimeter->pic,
					"nik_fo" => $perimeter->nik_fo,
					"fo" => $perimeter->fo,
			        "id_provinsi" => $perimeter->mpro_id,
			        "provinsi" => $perimeter->mpro_name,
			        "id_kota" => $perimeter->mkab_id,
			        "kabupaten" => $perimeter->mkab_name,
			        "status_perimeter" => $perimeter->status_perimeter,
			        "alasan" => $perimeter->tbpc_alasan,
			        "cluster" => $datacluster
			);
			return response()->json(['status' => 200,'data' => $data]);
		} else {
			return response()->json(['status' => 404,'message' => 'User Tidak Ditemukan'])->setStatusCode(404);
		}


	}

	//Update Primeter
	public function updateDetailPerimeterLevel(Request $request){
		$this->validate($request, [
            'id_perimeter_level' => 'required'
        ]);

		$id_perimeter_level = $request->id_perimeter_level;

		$perimeter_level= PerimeterLevel::find($id_perimeter_level);
		if($perimeter_level != null){

			if(isset($request->nik_pic)){
				$perimeter_level->mpml_pic_nik = $request->nik_pic;
			}
			if(isset($request->nik_fo)){
				$perimeter_level->mpml_me_nik = $request->nik_fo;
			}

			if($perimeter_level->save()) {
				return response()->json(['status' => 200,'message' => 'Data Berhasil Disimpan']);
			} else {
				return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
			}
		} else {
			return response()->json(['status' => 404,'message' => 'Data Tidak Ditemukan'])->setStatusCode(404);
		}

	}
	
	public function getTaskForceBUMN($id,Request $request){
	    $user = User::where('username',$nik)->first();
	    $auth_mc_id =Auth::guard('api')->user()->mc_id;
	    $param = [];
	    $limit = null;
	    $page = null;
	    $search = null;
	    $endpage = 1;
	    if(isset($request->limit)){
	        $limit=$request->limit;
	        if(isset($request->page)){
	            $page=$request->page;
	        }
	    }
	    if(isset($request->search)){
	        
	        $search=$request->search;
	    }
	    $querycache = "_get_taskforce_by_company_id_". $id;
	    $query = "select app.username,app.first_name, (case when (a1.mpm_mr_id is null) then a2.mpm_mr_id else a1.mpm_mr_id end) as mpm_mr_id,
			(case when (a1.mpm_mr_id is null) then a2.mr_name else a1.mr_name end) as mr_name,app.mc_id,aug.name,
			( CASE WHEN ( a1.mpm_mr_id IS NULL ) AND ( a2.mpm_mr_id IS NULL ) THEN TRUE ELSE FALSE END ) AS unassigned
		from app_users app
		left JOIN (select mp1.mpm_mr_id , mr1.mr_name, mpl1.mpml_pic_nik, mkab1.mkab_id,mkab1.mkab_name,mp1.mpm_mc_id  from master_perimeter_level mpl1
				join master_perimeter mp1 on mpl1.mpml_mpm_id = mp1.mpm_id
				join master_region mr1 on mr1.mr_id = mp1.mpm_mr_id
				left join master_kabupaten mkab1 on mkab1.mkab_id = mp1.mpm_mkab_id) a1 on a1.mpml_pic_nik = app.username and a1.mpm_mc_id = app.mc_id
		left JOIN (select mp2.mpm_mr_id, mr2.mr_name, mpl2.mpml_me_nik, mkab2.mkab_id,mkab2.mkab_name,mp2.mpm_mc_id from master_perimeter_level mpl2
				join master_perimeter mp2  on mpl2.mpml_mpm_id = mp2.mpm_id
				join master_region mr2 on mr2.mr_id = mp2.mpm_mr_id
				left join master_kabupaten mkab2 on mkab2.mkab_id = mp2.mpm_mkab_id) a2 on a2.mpml_me_nik = app.username and a2.mpm_mc_id = app.mc_id
		join app_users_groups aup on aup.user_id = app.id ";
	    //cek role
	    //dd($request->id_kota);
	    if(isset($request->id_role)){
	        $querycache = $querycache ."_role_". $request->id_role;
	        $query = $query . " and aup.group_id=?";
	        $param[] = $request->id_role;
	    } else {
	        $query = $query . " and (aup.group_id=3 or aup.group_id=4)";
	    }
	    //klausul where
	    $query = $query .  " join  app_groups aug on aup.group_id = aug.id  where app.mc_id = ?";
	    $param[] = $auth_mc_id;
	    
	    //cek kota
	    if(isset($request->id_kota) && $request->id_kota <> 'null'&& $request->id_kota <> ''){
	        $querycache = $querycache ."_kota_". $request->id_kota;
	        $query = $query . " and ((a1.mkab_id=? or a2.mkab_id=?) or (( a1.mpm_mr_id IS NULL ) AND ( a2.mpm_mr_id IS NULL )))";
	        $param[] = $request->id_kota;
	        $param[] = $request->id_kota;
	    }
	    if(isset($search)) {
	        $querycache = $querycache.'_searh_'. str_replace(' ','_',$request->search);
	        $query = $query ." and (lower(TRIM(app.username)) like ? or lower(TRIM(app.first_name)) like ?) ";
	        $param[] = '%'.strtolower(trim($search)).'%';
	        $param[] = '%'.strtolower(trim($search)).'%';
	        
	    }
	    
	    $query=$query ." GROUP BY app.username,app.first_name, (case when (a1.mpm_mr_id is null) then a2.mpm_mr_id else a1.mpm_mr_id end) ,
			(case when (a1.mpm_mr_id is null) then a2.mr_name else a1.mr_name end),app.mc_id,aug.name,
			( CASE WHEN ( a1.mpm_mr_id IS NULL ) AND ( a2.mpm_mr_id IS NULL ) THEN TRUE ELSE FALSE END )
			order by
			( CASE WHEN ( a1.mpm_mr_id IS NULL ) AND ( a2.mpm_mr_id IS NULL ) THEN TRUE ELSE FALSE END ) desc, aug.name desc,app.first_name asc ";
	    $jmltotal=count(DB::connection('pgsql2')->select( $query , $param));
	    
	    if(isset($limit)) {
	        $query=$query ." limit ". $limit;
	        $endpage = (int)(ceil((int)$jmltotal/(int)$limit));
	        
	        if (isset($page)) {
	            $offset = ((int)$page -1) * (int)$limit;
	            $query=$query ." offset ". $offset;
	            
	        }
	    }
	    //$datacache = Cache::remember($querycache, 1 * 60, function()use($query,$param) {
	    $data = array();
	    $taskforce = DB::connection('pgsql2')->select( $query , $param);
	    
	    foreach($taskforce as $itemtaskforce){
	        $data[] = array(
	            "kd_perusahaan" => $itemtaskforce->mc_id,
	            "kd_region" => $itemtaskforce->mpm_mr_id,
	            "region" => $itemtaskforce->mr_name,
	            "nik" => $itemtaskforce->username,
	            "username" => $itemtaskforce->username,
	            "nama" => $itemtaskforce->first_name,
	            "role" => $itemtaskforce->name,
	            "unassigned" => $itemtaskforce->unassigned,
	            
	        );
	    }
	    $data;
	    //return $data;
	    //});
	    return response()->json(['status' => 200,'page_end' => $endpage,'data' => $data]);
	    
	}
}
