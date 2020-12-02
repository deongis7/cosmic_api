<?php
namespace App\Http\Controllers;
use App\Report;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
class ReportController extends Controller {
    
    public function __construct() {
    }
    
    public function getDashboardReportBUMN($id){
        ///$datacache =  Cache::remember(env('APP_ENV', 'dev')."_get_dashreportbumn_head_".$id, 15 * 60, function()use($id) {
            $data = array();
            $dashreport_head = DB::select("select * from dashboard_reportcard_bymcid('$id')");
            
            foreach($dashreport_head as $dh){
                $data[] = array(
                    "v_id" => $dh->x_id,
                    "v_judul" => $dh->x_judul,
                    "v_jml" => $dh->x_jml
                );
            }
            return $data;
        //});
        return response()->json(['status' => 200,'data' => $data]);
    }
}
