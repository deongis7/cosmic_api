<?php

namespace App\Http\Controllers;



use App\User;
use App\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Http\File;
use Auth;
use Validator;
use DB;
use Illuminate\Support\Facades\Storage;

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
		$data = array();
		$id = Auth::guard('api')->user()->id;
		$user = User::select('app_users.id','app_users.username','app_users.first_name',
		    'master_company.mc_id','master_company.mc_name','app_groups.name',
		    'app_users.no_hp','app_users.divisi','app_users.email','app_users.foto')
					->join('master_company','master_company.mc_id','app_users.mc_id')
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
}
