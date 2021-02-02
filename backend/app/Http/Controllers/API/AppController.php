<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AppController extends Controller
{
    private function _error_string($errArray)
    {
        $error_string = '';
    foreach ($errArray as $key) {
            $error_string.= $key."\n";
        }
        return $error_string;
    }

    public function index(){
     
        $app_login_page = DB::table("app_login_page")->select(DB::raw("*"))->first();
        if($app_login_page){
            if($app_login_page->logo){
                $logo= asset(Storage::url('public/uploads/logos/'.$app_login_page->logo));
            }else{
                $logo='';
            }
            $data=array("appLoginId"=>$app_login_page->app_login_page_id,
                    "logo" => $logo,
                    "title" => $app_login_page->title,
                    "description"     => $app_login_page->description,
                    "fbLogin" => $app_login_page->fb_login,
                    "googleLogin" => $app_login_page->google_login,
                    "privacyPolicy" => $app_login_page->privacy_policy   
            );
            $response = array( "status" => "success", "data" => $data ); 
        } else {
            $response = array( "status" => "failed", "msg" => "No Record" );
        }
        
        return response()->json($response);   
        // }else{
        //     $data=array("status"=>'error');
        // }
        // $response = $data;
        // return response()->json($response); 
    }


}   