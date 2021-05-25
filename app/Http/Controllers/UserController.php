<?php

namespace App\Http\Controllers;


use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use \Firebase\JWT\JWT;

use App\TrnAktifitasFile;
use App\User;
use App\UserGroup;

use App\TrnAktifitas;
use App\KonfigurasiCAR;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Auth;
use Validator;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Intervention\Image\ImageManagerStatic as Image;

class UserController extends Controller
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

	//Detail User
	public function getDetailUser(){
	    $Path = '/profile/';
	    $PathCompany = '/foto_bumn/';
		$data = array();
		$id = Auth::guard('api')->user()->id;
		$user = User::select('app_users.id','app_users.username','app_users.first_name',
		    'master_company.mc_id','master_company.mc_name','app_groups.name',
		    'app_users.no_hp','app_users.divisi','app_users.email','app_users.foto','master_company.mc_foto','master_company.mc_flag')
					->leftjoin('master_company','master_company.mc_id','app_users.mc_id')
					->join('app_users_groups','app_users_groups.user_id','app_users.id')
					->join('app_groups','app_users_groups.group_id','app_groups.id')
					->where('app_users.id',$id)
					->first();

		if($user->count()>0){
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
			        "foto" => $Path.$user->foto,
			        "foto_bumn" => $PathCompany.$user->mc_foto,
              "group_company" => $user->mc_flag,
					);
			return response()->json(['status' => 200,'data' => $data]);
		} else {
			return response()->json(['status' => 404,'message' => 'Data Tidak Ditemukan'])->setStatusCode(404);
		}
	}

	//Ubah User
	public function updateDetailUser(Request $request,$id) {
		$this->validate($request, [
            'name' => 'required',
        ]);

		$user = User::find($id);
		$user->email = $request->email;
		$user->no_hp = $request->no_hp;
		$user->divisi = $request->divisi;

		if($user->save()){
			return response()->json(['status' => 200,'data' => $user]);
		} else {
			return response()->json(['status' => 500,'message' => 'Gagal Menyimpan'])->setStatusCode(500);
		}
	}

	public function change_password(Request $request) {
		$input = $request->all();
		$userid = Auth::guard('api')->user()->id;
        $user= Auth::guard('api')->user();
		$rules = array(
			'old_password' => 'required',
			'new_password' => 'required|min:6',
			'confirm_password' => 'required|same:new_password',
		);
		//dd($input);
		$validator = Validator::make($input, $rules);
		if ($validator->fails()) {
			$arr = array("status" => 400, "message" => $validator->errors()->first());
		} else {
			try {
			    if ((Hash::check($request->old_password, Auth::user()->password)) == false) {
			        $arr = array("status" => 400,
			            "message" => "Check your old password.");
			    } else if ((Hash::check($request->new_password, Auth::user()->password)) == true) {
			        $arr = array("status" => 400,
			            "message" => "Please enter a password which is not similar then current password.");
			    } else if ((Hash::check($request->new_password, '$2y$10$eyLOnXfci/PAI.KuNIULTOJTkluadpdj7FtlzkwhKqasnAHrYdkmq')) == true) {
			        $arr = array("status" => 400,
			            "message" => "Please enter a new password which is not similar then default password.");
			    } else {
			        $user->password = Hash::make($input['new_password']);
			        $user->save();
			        $arr = array("status" => 200,
			            "message" => "Profile & Password updated successfully.");
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

	public function logout()
	{
		if (Auth::check()) {
		   $user = Auth::user()->AauthAcessToken();

			if($user->delete()){
				return response()->json(['status' => 200,'message' => 'Berhasil Logout']);
			} else {
				return response()->json(['status' => 500,'message' => 'Gagal Logout'])->setStatusCode(500);
			}
		}
	}

	public function updateFirstDetailUser(Request $request, $id) {
	    $input = $request->all();
	    $user = User::find($id);
	    $user->email = $request->email;
	    $user->no_hp = $request->no_hp;
	    $user->divisi = $request->divisi;

	    if(($request->new_password!='') or ($request->old_password!='')
	        or ($request->confirm_password!='')
            or ((Hash::check('P@ssw0rd', Auth::user()->password)) == true)){
            $rules = array(
                'old_password' => 'required',
                'new_password' => 'required|min:6',
            );
            $validator = Validator::make($input, $rules);
            if ($validator->fails()) {
                $arr = array("status" => 400, "message" => $validator->errors()->first());
            } else {
                try {
                    if ((Hash::check($request->old_password, Auth::user()->password)) == false) {
                        $arr = array("status" => 400,
                            "message" => "Check your old password.");
                    } else if ((Hash::check($request->new_password, Auth::user()->password)) == true) {
                        $arr = array("status" => 400,
                            "message" => "Please enter a password which is not similar then current password.");
                    } else if ((Hash::check($request->new_password, '$2y$10$eyLOnXfci/PAI.KuNIULTOJTkluadpdj7FtlzkwhKqasnAHrYdkmq')) == true) {
                        $arr = array("status" => 400,
                            "message" => "Please enter a new password which is not similar then default password.");
                    } else {
                        $user->password = Hash::make($input['new_password']);
                        $user->save();
                        $arr = array("status" => 200,
                            "message" => "Profile & Password updated successfully.");
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
        }else{
            if($user->save()){
                $arr = array("status" => 200, "message" => "Profile updated successfully.");
            }else{
                $arr = array("status" => 500, "message" => "Profile not updated.");
            }
        }

        return response()->json($arr)->setStatusCode($arr['status']);
	}

    public function setActivityLog(Request $request){
        //dd($request);
        $modul = $request->modul;
        $modul_id = $request->modul_id;
        $action = $request->action;
        $description = $request->description;
        $username = $request->username;
        //dd($username);
        $helper= AppHelper::setActivityLog($modul, $modul_id, $action, $description, $username);
//dd($tes);
        if($helper){
            return response()->json(['status' => 200,'message' => 'Data Tersimpan']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Gagal Tersimpan'])->setStatusCode(500);
        }
    }

    //Upload Foto
    public function uploadFotoProfile(Request $request){
        $this->validate($request, [
            'file_foto' => 'required',
        ]);
        $id = Auth::guard('api')->user()->id;

        $user = User::where('id','=',$id)->first();
        if($user==null){
            return response()->json(['status' => 404,'message' => 'Data User Tidak Ditemukan'])->setStatusCode(404);
        }

        $file = $request->file_foto;




        if(!Storage::exists('/public/profile')) {
            Storage::disk('public')->makeDirectory('/profile');
        }

        //$destinationPath = base_path("storage\app\public\aktifitas/").$kd_perusahaan.'/'.$tanggal;
        $destinationPath = storage_path().'/app/public/profile';
        $name1 = round(microtime(true) * 1000).'.jpg';


        if ($file != null || $file != '') {
            $img1 = explode(',', $file);
            $image1 = $img1[1];
            $filedecode1 = base64_decode($image1);


            Image::make($filedecode1)->resize(700, NULL, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$name1);

        }
        $user->foto = $name1;
        $user->save();

        if($user) {
            return response()->json(['status' => 200,'message' => 'Data Berhasil Disimpan']);
        } else {
            return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
        }
    }
    public function tokenUpdate(Request $request, $id) {
	    $this->validate($request, [
            'token' => 'required',
        ]);

		$user = User::find($id);
		$user->token = $request->token;

		if($user->save()){
			//return response()->json(['status' => 200,'data' => $user]);
			return response()->json(['status' => 200,'message' => 'Update Token Succesfully']);
		} else {
			return response()->json(['status' => 500,'message' => 'Failed Token'])->setStatusCode(500);
		}
	}

    public function postCekUser(Request $request) {
        $user= new User();
        $user->setConnection('pgsql2');
        $user = User::JOIN('master_company as mc','mc.mc_id','app_users.mc_id')
                  ->where("app_users.mc_id",$request->kd_perusahaan)
                  ->whereRaw("trim(lower(username))='". trim(strtolower($request->username))."'")->first();
                //  dd($user);
          if ($user){
            $data[] = array(
                      "kd_perusahaan" => $user->mc_id,
                      "nama_perusahaan" => $user->mc_name,
                      "nama" => $user->first_name,
                      "username" => $user->username
                    );

              return response()->json(['status' => 200, 'data' => $data]);
          }else{
              return response()->json(['status' => 404, 'message' => 'User Tidak Ditemukan'])->setStatusCode(404);
          }
    }

    public function postResetPassword(Request $request) {
      $this->validate($request, [
          'username' => 'required',
          'kd_perusahaan' => 'required',
      ]);
      $user= new User();
      $user->setConnection('pgsql');
      $user = $user->whereRaw("trim(lower(username))='". trim(strtolower($request->username))."'")->first();

      if($user != null){
        $user_id = $user->id;
        $user->password = Hash::make('P@ssw0rd');
        if(	$user->save()){
            return response()->json(['status' => 200,  "message" => "Password Telah Direset"]);
        }else{
            return response()->json(['status' => 404, 'message' => 'User Tidak Ditemukan'])->setStatusCode(404);
        }
      }else{
          return response()->json(['status' => 404, 'message' => 'User Tidak Ditemukan'])->setStatusCode(404);
      }


    }

    public function sendFirebase(Request $request, $id){

    	$this->validate($request, [
            'body' => 'required',
        ]);

    	$token = "AAAAIOJgA7s:APA91bGsiFlggeNexu_qv7QdxyEKeudNqJatbkZaMkMjI9dKJHjPDcQQdXOeCmlGiDsepZ2HkuLCFxzU6DiYMxn-2ZoueHFnGNTXlwY4krhF9HZ207WocMTamycUzk_vMQsz6wlLvasW";
    	$headers = [
            'Authorization' => 'Key=' . $token,
            'Accept'        => 'application/json',
            'Content-Type' => 'application/json'
        ];

        /*{
			 "to" : "YOUR_FCM_TOKEN_WILL_BE_HERE",
			 "collapse_key" : "type_a",
			 "notification" : {
			     "body" : "Body of Your Notification",
			     "title": "Title of Your Notification"
			 },
			 "data" : {
			     "body" : "Body of Your Notification in Data",
			     "title": "Title of Your Notification in Title",
			     "key_1" : "Value for key_1",
			     "key_2" : "Value for key_2"
			 }
			}*/

   		$token_device = "testtoken";
        $data_param = [
        	"to" => $token_device,
            "notification" => [
                  "body" => $request->body,
                  "title" =>  $request->title
              ]
          ];

        $header_params = json_encode($data_param);
        // print_r($header_params);
        $client    = new Client();
        $request = $client->request('POST', 'https://fcm.googleapis.com/fcm/send', [
                  'headers' => $headers,
                  'body' => $header_params
            ]);

        $response = $request->getBody()->getContents();
        $result   = json_decode($response, true);
        return response()->json(['status' => 200,'data' => $result]);

    }

     public function get_token(){
    	$key = "AAAAIOJgA7s:APA91bGsiFlggeNexu_qv7QdxyEKeudNqJatbkZaMkMjI9dKJHjPDcQQdXOeCmlGiDsepZ2HkuLCFxzU6DiYMxn-2ZoueHFnGNTXlwY4krhF9HZ207WocMTamycUzk_vMQsz6wlLvasW";



    	$privateKey = "d79fb75acdb1d1650745699a252993e34745aa6c";

		$publicKey = "<<<EOD
		-----BEGIN PUBLIC KEY-----
		MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC8kGa1pSjbSYZVebtTRBLxBz5H
		4i2p/llLCrEeQhta5kaQu/RnvuER4W8oDH3+3iuIYW4VQAzyqFpwuzjkDI+17t5t
		0tyazyZ8JXw+KgXTxldMPEL95+qVhgXvwtihXC1c5oGbRlEDvDF6Sa53rcFVsYJ4
		ehde/zUxo6UvS7UrBQIDAQAB
		-----END PUBLIC KEY-----
		EOD";


		$payload = array(
		    "iss" => "https://securetoken.google.com/cosmic-a2227",
		    "aud" => "firebase-adminsdk-vepka@cosmic-a2227.iam.gserviceaccount.com",
		    "iat" => 1356999524,
		    "nbf" => 1357000000
		);

		$jwt = JWT::encode($payload, $key);
		$decoded = JWT::decode($jwt, $key, array('HS256'));
		echo $jwt;

		// $jwt = JWT::encode($payload, $privateKey, 'RS256');
		// echo "Encode:\n" . print_r($jwt, true) . "\n";


    }

    //Get Notif PIC
    function getNotifpic($nik){
        /*$token = "fYXTze1sRPyDDyUZwurszk:APA91bFwCJtFF0tyT2BSfG0UGgal8pCrgRtQyEsrcegBf_HB_BeoVreUG0iLNeMhWGH3_p-bXA1xpLjRIS8b0XueHMpW15WTwS1jtxz7mZbMmIJzoPZDYgC-5OsWaqrsdyiPW5rcSDgi";
        $body = "tbody";
        $title = "ttitle";

        $weeks = AppHelper::sendFirebase($token, $body, $title);
        print_r($weeks);die;*/
        // return response()->json(['status' => 200,'data' => $nik]);
        $data = array();

        $weeks = AppHelper::Weeks();
        $startdate = $weeks['startweek'];
        $enddate = $weeks['endweek'];
        //get token
        $user= new User();
        $user->setConnection('pgsql2');
        $user = $user->whereRaw("trim(lower(username))='". trim(strtolower($nik))."'")->first();
        $token="";
        if($user != null){
            $token = $user->token;
        }else{
            return response()->json(['status' => 404, 'message' => 'User Tidak Ditemukan'])->setStatusCode(404);
        }

        $notif = DB::connection('pgsql2')->select( "select mp.mpm_name,mp.mpm_mc_id,mpl.mpml_id, mpl.mpml_name, mcr.mcr_name,tpd.tpmd_order,mcar.mcar_name, ta.ta_tpmd_id,ta.ta_kcar_id,ta.ta_id, ta.ta_status, ta.ta_ket_tolak, au.first_name , coalesce(tbpc_status,0)tbpc_status
        from transaksi_aktifitas ta
        join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
        join master_cluster_ruangan mcr on mcr.mcr_id = kc.kcar_mcr_id
        join master_car mcar on mcar.mcar_id = kcar_mcar_id
        join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id
        join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
        join master_perimeter mp on mp.mpm_id = mpl.mpml_mpm_id
        left join table_status_perimeter tsp on tsp.tbsp_tpmd_id=tpd.tpmd_id
        left join app_users au on au.username = mpl.mpml_me_nik
        left join table_perimeter_closed tpc on tpc.tbpc_mpml_id = tpd.tpmd_mpml_id
        where ta.ta_status = 0 and mpl.mpml_pic_nik = ?
        order by ta_date_update asc", [$nik]);
        if(count($notif)>0){
            foreach($notif as $itemnotif){
            //dd($this->getOneFile($itemnotif->ta_id,$itemnotif->mpm_mc_id)['file_tumb']);
                $data[] = array(
                    "id_perimeter_level" => $itemnotif->mpml_id,
                    "id_perimeter_cluster" => $itemnotif->ta_tpmd_id,
                    "id_konfig_cluster_aktifitas" => $itemnotif->ta_kcar_id,
                    "perimeter" => $itemnotif->mpm_name,
                    "level" => $itemnotif->mpml_name,
                    "cluster" => $itemnotif->mcr_name. " ". $itemnotif->tpmd_order,
                    "aktifitas" => $itemnotif->mcar_name,
                    "id_aktifitas" => $itemnotif->ta_id,
                    "status" => $itemnotif->tbpc_status,
                    "fo_name" => $itemnotif->first_name,
                    "file" => $this->getFile($itemnotif->ta_id,$itemnotif->mpm_mc_id)
                );
            }
        }
        return response()->json(['status' => 200,'data' => $data]);

    }

    //Get File Tolak
    private function getFile($id_aktifitas,$id_perusahaan){
        config(['database.default' => 'pgsql3']);
        $data =[];
        if ($id_aktifitas != null){
        $transaksi_aktifitas_file = TrnAktifitasFile::join("transaksi_aktifitas","transaksi_aktifitas.ta_id","transaksi_aktifitas_file.taf_ta_id")
                        ->where("ta_status", "=", "2")
                        ->where("taf_ta_id",$id_aktifitas)->orderBy("taf_id","desc")->limit("2")->get();

            foreach($transaksi_aktifitas_file as $itemtransaksi_aktifitas_file){

                $data[] = array(
                        "id_file" => $itemtransaksi_aktifitas_file->taf_id,
                        "file" => "/aktifitas/".$id_perusahaan."/".$itemtransaksi_aktifitas_file->taf_date."/".$itemtransaksi_aktifitas_file->taf_file,
                        "file_tumb" => "/aktifitas/".$id_perusahaan."/".$itemtransaksi_aktifitas_file->taf_date."/".$itemtransaksi_aktifitas_file->taf_file_tumb,
                    );
            }
        }
        return $data;
    }

    //Validasi
    public function validasiMonitoring(Request $request){
        // config(['database.default' => 'pgsql']);
        $this->validate($request, [
            'id_perimeter_cluster' => 'required',
            'id_konfig_cluster_aktifitas' => 'required',
            'status' => 'required',
        ]);

        if(date('w')==6 OR date('w')==7){
            return response()->json(['status' => 200,'message' => 'Mohon maaf, untuk monitoring hanya bisa dilakukan di hari Senin - Jumat']);
        }else{
            $id_perimeter_cluster = $request->id_perimeter_cluster;
            $id_konfig_cluster_aktifitas = $request->id_konfig_cluster_aktifitas;
            $weeks = AppHelper::Weeks();
      
            $trn_aktifitas= TrnAktifitas::where('ta_tpmd_id',$id_perimeter_cluster)
                                        ->where('ta_kcar_id',$id_konfig_cluster_aktifitas)
                                        ->where('ta_week',$weeks['weeks'])->first();
            if($trn_aktifitas != null){
                    $trn_aktifitas->ta_status = $request->status;
                    if($request->status==2){
                        $trn_aktifitas->ta_ket_tolak = $request->keterangan;
                    }
        
                    if($trn_aktifitas->save()) {
                        //pushnotif utk yg reject - status=2
                        if($request->status==2){
                            //get data perimeter
                            $get_perimeter = DB::connection('pgsql2')->select( "select mpl.mpml_name, mcr.mcr_name, mpl.mpml_me_nik, au.first_name, au.token from transaksi_aktifitas ta
                            join table_perimeter_detail tpd on tpd.tpmd_id = ta.ta_tpmd_id and tpd.tpmd_cek = true
                            join master_perimeter_level mpl on mpl.mpml_id = tpd.tpmd_mpml_id
                            join konfigurasi_car kc on kc.kcar_id = ta.ta_kcar_id
                            join master_cluster_ruangan mcr on mcr.mcr_id = kc.kcar_mcr_id
                            join app_users au on au.username = mpl.mpml_me_nik
                            where tpd.tpmd_id = ?
                            group by mpl.mpml_name, mcr.mcr_name, mpl.mpml_me_nik, au.first_name, au.token ", [$id_perimeter_cluster]);
                            //dd($get_perimeter[0]->mpml_name);
                        
                            //lempar ke helper firebase
                            $token = $get_perimeter[0]->token;
                            $body = $get_perimeter[0]->mpml_name."<br /> Field Officer : ". !empty($get_perimeter[0]->first_name)?$get_perimeter[0]->first_name:$get_perimeter[0]->mpml_me_nik;
                            $title = $get_perimeter[0]->mcr_name;
                            $role="FO";
                            $weeks = AppHelper::sendFirebase($token, $body, $title, $role);
                        }
                        return response()->json(['status' => 200,'message' => 'Data Berhasil Disimpan']);
                    } else {
                        return response()->json(['status' => 500,'message' => 'Data Gagal disimpan'])->setStatusCode(500);
                    }
            } else {
                return response()->json(['status' => 404,'message' => 'Data Tidak Ditemukan'])->setStatusCode(404);
            }
        }
    }
}
