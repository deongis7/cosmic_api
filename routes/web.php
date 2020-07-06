<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});



$router->get('/todo', 'todoController@index');
$router->get('/key', 'todoController@apikey');
$router->get('/todo/{id}', 'todoController@show');
$router->post('/todo', 'todoController@store');

$router->group(['prefix' => 'api/v1'], function () use ($router) {
	
	//Perimeter
	$router->get('/perimeter/count/{id}', 'PerimeterController@getCountPerimeter');
	$router->get('/perimeter/map/{id}', 'PerimeterController@getPerimeterMap');
	$router->get('/perimeter/{id}', 'PerimeterController@getPerimeter');
	$router->get('/perimeter/region/{id}', 'PerimeterController@getPerimeterbyRegion');
	
	//TaskForce
	$router->get('/taskforce/count/{id}', 'PerimeterController@getCountTaskForce');
	$router->get('/taskforce/{id}', 'PerimeterController@getTaskForce');
	$router->get('/taskforce/region/{id}', 'PerimeterController@getTaskForcebyRegion');
	
	//Cluster Ruangan
	$router->get('/cluster/perimeter/{id}', 'PerimeterController@getClusterbyPerimeter');
	$router->get('/cluster/perimeter_level/{id}', 'PerimeterController@getClusterbyPerimeterLevel');
	
	//Protokol
	$router->get('/protokol/{id}', 'ProtokolController@protokol');
	$router->post('/protokol/upload', 'ProtokolController@uploadProtokol');
	$router->post('/protokol/upload_json', 'ProtokolController@uploadProtokolJSON');
	
	//Temporary Perimeter
    $router->get('/tmp_perimeter', 'TmpPerimeterController@index');
    $router->get('/parsingperimeter', 'TmpPerimeterController@parsingPerimeter');
	$router->post('/import', 'ImportController@import');
	
	//Data_detail
	$router->get('/terpapar/laporan_home/{id}', 'TerpaparController@getDataHome');
	$router->get('/terpapar/laporan_detail/{id}', 'TerpaparController@getDatadetail');
});