<?php

namespace App\Http\Controllers;

use App\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\todoRequest;

class todoController extends Controller
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
		$data = Todo::all();
		return response($data);
	}
	public function show($id){
		$data = Todo::where('id',$id)->get();
		return response ($data);
	}
	
	public function store (todoRequest $request){
		
		
		//$this->validate($request, [
		//	'name' => 'required|max:30',
		//	'activity' => 'required|max:30',
		//	'description' => 'required'
		//]);
		
		
		$data = new Todo();
		$data->activity = $request->input('activity');
		$data->description = $request->input('description');
		$data->save();

		return response('Berhasil Tambah Data');
	}

	public function apikey(){
		$random = base64_decode(Str::random(40));
		$random = Str::random(32);

		return response($random);
	}
	
	


    //
}
