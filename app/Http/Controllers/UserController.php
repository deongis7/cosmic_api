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
		$data = array();
		$id = Auth::guard('api')->user()->id;
		$user = User::select('app_users.id','app_users.username','app_users.first_name','master_company.mc_id','master_company.mc_name','app_groups.name')
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
					
					);
			return response()->json(['status' => 200,'data' => $data]);			
		} else {
			return response()->json(['status' => 404,'message' => 'Data Tidak Ditemukan'])->setStatusCode(404);	
		}	

	}
	
	//Ubah User
	public function updateDetailUser(Request $request,$id)
    {
		
		$this->validate($request, [
            'name' => 'required',
        ]);
		
		$user = User::find($id);	
		$user->first_name = $request->name;
		$user->email = $request->email;
		
		if($user->save()){
			return response()->json(['status' => 200,'data' => $user]);	
		} else {
			return response()->json(['status' => 500,'message' => 'Gagal Menyimpan'])->setStatusCode(500);	
		}
	
	}	
	

	
	public function change_password(Request $request)
	{
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
				if ((Hash::check(request('old_password'), Auth::user()->password)) == false) {
					$arr = array("status" => 400, "message" => "Check your old password.");
				} else if ((Hash::check(request('new_password'), Auth::user()->password)) == true) {
					$arr = array("status" => 400, "message" => "Please enter a password which is not similar then current password.");
				} else {
					User::where('id', $userid)->update(['password' => Hash::make($input['new_password'])]);
					$arr = array("status" => 200, "message" => "Password updated successfully.");
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
	
}
