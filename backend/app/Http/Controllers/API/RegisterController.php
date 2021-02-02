<?php

namespace App\Http\Controllers\API;
use Auth;
use App\User;
use DateTime;
use App\Mail\SendMail;
use Illuminate\Http\Request;
use App\Helpers\Common\Functions;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL; 
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Validator;
class RegisterController extends Controller
{
    private function _error_string($errArray)
    {
        $error_string = '';
        foreach ($errArray as $key) {
            $error_string.= $key."\n";
        }
        return $error_string;
    }
    public function index(Request $request){
        //DB::enableQueryLog(); 
        // $userDpPath = secure_asset(config('app.profile_path'));
        // $videoStoragePath  = secure_asset(config("app.video_path"));

        $videoStoragePath = asset(Storage::url('public/videos'));
        $userDpPath = asset(Storage::url('public/profile_pic'));

        $page_size = isset($request->page_size) ? $request->page_size : 10;
        /*ifnull( case when INSTR(u.user_dp,'https://') > 0 THEN u.user_dp ELSE concat('".$userDpPath."/',s.user_id,'/small/',u.user_dp) END,'".secure_asset('imgs/music-icon.png')."') as sound_image_url*/
        $videos = DB::table("videos as v")->select(DB::raw("v.video_id,v.sound_id,'".asset('default/music-icon.png')."' as sound_image_url,v.user_id,v.description,v.title,case when u.user_dp!='' THEN case when INSTR(u.user_dp,'https://') > 0 THEN u.user_dp ELSE concat('".$userDpPath."/',v.user_id,'/small/',u.user_dp) END ELSE '' END as user_dp ,concat('".$videoStoragePath."/',v.user_id,'/',video) as video,case when thumb='' then '' else concat('".$videoStoragePath."/',v.user_id,'/thumb/',thumb) end as thumb,case when gif='' then '' else concat('".$videoStoragePath."/',v.user_id,'/gif/',gif) end as gif,ifnull(s.title,'') as sound_title,concat('@',u.username) as username,
            v.privacy,v.duration,v.user_id,v.tags,ifnull(v.created_at,'NA') as created_at,ifnull(v.updated_at,'NA') as updated_at,
            ifnull(l.like_id,0) as like_id,ifnull(f2.follow_id,0) as isFollowing,
            (CASE WHEN v.total_likes >= 1000000000
            THEN concat(FORMAT(v.total_likes/1000000000,2),' ','B')
            WHEN v.total_likes >= 1000000
            THEN concat(FORMAT(v.total_likes/1000000,2),' ','M')
            WHEN v.total_likes >= 1000
            THEN concat(FORMAT(v.total_likes/1000,2),' ','K')
            ELSE
            v.total_likes
            END) as total_likes,
            (CASE WHEN v.total_comments >= 1000000000
            THEN concat(FORMAT(v.total_comments/1000000000,2),' ','B')
            WHEN v.total_comments >= 1000000
            THEN concat(FORMAT(v.total_comments/1000000,2),' ','M')
            WHEN v.total_comments >= 1000
            THEN concat(FORMAT(v.total_comments/1000,2),' ','K')
            ELSE
            v.total_comments
            END) as total_comments,
            IF(uv.verified='A', true, false) as isVerified"))
        ->leftJoin("users as u","v.user_id","u.user_id")
        ->leftJoin("user_verify as uv","uv.user_id","u.user_id")
        ->leftJoin("sounds as s","s.sound_id","v.sound_id")
        ->leftJoin('likes as l', function ($join)use ($request){
            $join->on('l.video_id','=','v.video_id')
            ->where('l.user_id',$request->login_id);
        });
        if($request->user_id > 0  && $request->user_id == $request->login_id) {
                                                //$videos = $videos->whereRaw(DB::raw("v.privacy=1")); 
            $videos = $videos->where("v.user_id","=", $request->user_id); 
        } else {
            $videos = $videos->where("v.privacy","<>", "1");    
        }

        $videos = $videos->where("v.deleted",0)
        ->where("v.enabled",1)
        ->where("v.active",1)
        ->where("v.total_report","<",50);
        if($request->following == 1) {
            $videos = $videos->join('follow as f', function ($join)use ($request){
                $join->on('f.follow_to','=','v.user_id')
                ->where('f.follow_by',$request->login_id);
            });
        }

        if(isset($request->search) && $request->search!=""){
            $search = $request->search;
            $videos = $videos->whereRaw(DB::raw("((v.title like '%" . $search . "%') or (v.tags like '%" . $search . "%'))"));
            //where('v.title', 'like', '%' . $search . '%')->orWhere('v.tags', 'like', '%' . $search . '%')->orWhere('v.tags', 'like', '%' . $search . '%');
        }
        if(isset($request->user_id) && $request->user_id>0) {
            $videos = $videos->where('v.user_id',$request->user_id);        
        }
        if($request->video_id>0){
            $videos = $videos->orderBy(DB::raw('v.video_id='.$request->video_id),'desc');

        }

        $is_following_videos = 0;
        if($request->login_id > 0) {
            $videos = $videos->leftJoin('blocked_users as bu1', function ($join)use ($request){
                $join->on('v.user_id','=','bu1.user_id');
                $join->whereRaw(DB::raw(" ( bu1.blocked_by=".$request->login_id." OR bu1.user_id=".$request->login_id." )" ));
            });

            $videos = $videos->leftJoin('blocked_users as bu2', function ($join)use ($request){
                $join->on('v.user_id','=','bu2.blocked_by');
                $join->whereRaw(DB::raw(" ( bu2.blocked_by=".$request->login_id." OR bu2.user_id=".$request->login_id." )" ));
            });
            $videos = $videos->leftJoin('follow as f2', function ($join) use ($request){
                $join->on('v.user_id','=','f2.follow_to')
                ->where('f2.follow_by',$request->login_id);
            });
            $videos = $videos->whereRaw( DB::Raw(' bu1.block_id is null and bu2.block_id is null '));
            if($request->user_id != $request->login_id) {
                $videos = $videos->whereRaw( DB::Raw(' CASE WHEN (f2.follow_id is not null ) THEN (v.privacy=2 OR v.privacy=0) ELSE v.privacy=0 END '));
            }

            $login_id = $request->login_id;
            $followingVideos = DB::table("follow")
            ->select(DB::raw("follow_id"))
            ->where("follow_by",$request->login_id)
            ->first(); 
            if($followingVideos) {
                $is_following_videos = 1;
            }
        }  else {
            $videos = $videos->leftJoin('follow as f2', function ($join) use ($request){
                $join->on('v.user_id','=','f2.follow_to')
                ->where('f2.follow_by',$request->login_id);
            });
            $videos = $videos->where("v.privacy","<>",2);        
        }
    
        
        $videos = ($request->video_id == null || $request->video_id == 0) ? $videos->orderBy(DB::raw('RAND()')) : $videos->orderBy("v.video_id","desc");
        $videos= $videos->paginate($page_size);
        //dd(DB::getQueryLog());
        $response = array("status" => "success",'data' => $videos, 'is_following_videos' => $is_following_videos);
        return response()->json($response); 
    }
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email'          => 'required|email',
            'password'       => 'required',
        ],
        [
            'email.required'   => 'Email is required',
            'email.email'      => 'Email id is not valid',
        ]);

        if(!$validator->passes()) {
            return response()->json(['status'=>'error','msg'=> $this->_error_string($validator->errors()->all())]);
        }else{
            $functions = new Functions();
            
            
            $existingRecord = DB::table('users')
            ->select(DB::raw("user_id,password,username,fname,lname,email,user_dp"))
            ->whereRaw(DB::raw("email = '".$request->email."'"))
            ->first();
            if($existingRecord) {
                $checkIfActive = DB::table('users')
                ->select(DB::raw("user_id,app_token,active"))
                ->whereRaw(DB::raw("email = '".$request->email."' and email_verified= 1 "))
                ->first();
                if($checkIfActive){
                    if($checkIfActive->active==1){
                        if(Hash::check($request->password, $existingRecord->password)) {
                            $user_token = Hash::make($functions->_password_generate(20));
                            $now  = date("Y-m-d H:i:s");
                            $data = array(
                                'app_token'         => $user_token,
                                'time_zone'         => $request->time_zone,
                                'updated_at'        => $now
                            );
                            DB::table('users')
                            ->where('user_id', $existingRecord->user_id)
                            ->update($data);
                            $data  = array( 'user_id' => $existingRecord->user_id, 'app_token' => $user_token,'username' =>$existingRecord->username,'email'=>$existingRecord->email,'fname'=>$existingRecord->fname,'lname'=>$existingRecord->lname,'user_dp'=>$existingRecord->user_dp );
                        }else{
                            $msg = "Invalid password.";
                            $response = array("status" => "error",'msg'=>$msg );      
                            return response()->json($response); 
                        }
                    }else{
                        $msg = "this account is deactivated";
                        $response = array("status" => "error",'msg'=>$msg );      
                        return response()->json($response); 
                    }
                    
                }else{
                    $data = DB::table('users')
                    ->select(DB::raw("user_id,app_token"))->where('email',$request->email)->first();
                    $msg = "You have not verified your registered email for the account.";
                    $response = array("status" => "email_not_verified", 'msg'=>$msg ,'content'=>$data);      
                    return response()->json($response); 
                }
            }else{
                $msg = "Your Account does not exist";
                $response = array("status" => "error",'msg'=>$msg );      
                return response()->json($response); 
            }

            $msg = "User logged in successfully";
            $response = array("status" => "success",'msg'=>$msg ,'content' => $data);      
            return response()->json($response); 
        }
    }
    
    public function resendOtp(Request $request){

        $validator = Validator::make($request->all(), [
            'user_id'          => 'required'
        ],[
            'user_id.required' => 'User is required'
        ]);
        
        if (!$validator->passes()) {
            return response()->json(['status'=>'error','msg'=> $this->_error_string($validator->errors()->all())]);
        }else{
                $userRecord = DB::table('users')
                    ->select("user_id","mobile","email")
                    ->where('user_id',$request->user_id)
                    ->first();

                if($userRecord) {
                    $otp= mt_rand(100000, 999999);
                    $now  = date("Y-m-d H:i:s");
                    DB::table('users')
                        ->where('user_id', $userRecord->user_id)
                        ->update(['verification_code' => $otp, 'verification_time' => $now]);
                    $site_title =Functions::getSiteTitle();
            
            
            $mailBody = '
            <p>Dear <b>'.  $userRecord->email .'</b>,</p>
            <p style="font-size:16px;color:#333333;line-height:24px;margin:0">Use the OTP to verify your email address.</p>
            <h3 style="color:#333333;font-size:24px;line-height:32px;margin:0;padding-bottom:23px;margin-top:20px;text-align:center">'
            .$otp.'</h3>
            <br/><br/>
            <p style="color:#333333;font-size:16px;line-height:24px;margin:0;padding-bottom:23px">Thank you<br /><br/>'.$site_title.'</p>
            ';
            // dd($mailBody);
            // $ref_id
            $array = array('subject'=>'OTP Email Verification - '.$site_title,'view'=>'emails.site.company_panel','body' => $mailBody);
            Mail::to($userRecord->email)->send(new SendMail($array));  
                    /*if($userRecord->mobile!='') {
                        Functions::sendSMS("91".$userRecord->mobile, $otp.' is your OTP to verify your account with LeukeVideos. Valid for 10 minutes. Do not share with anyone');    
                    }*/
                    
                    $response = array( "status" => "success", "msg" => "An OTP has been sent to your Mobile or Email", 'user_id' => $userRecord->user_id ); 
                } else {
                    $response = array( "status" => "failed", "msg" => "Invalid user!" );
                }

                
            return response()->json($response);   
        }
    }

    public function fetchUserInformation(Request $request){
		$validator = Validator::make($request->all(), [
            'user_id'          => 'required'
        ],[
            'user_id.required' => 'User is required'
        ]);
        
        if (!$validator->passes()) {
            return response()->json(['status'=>'error','msg'=> $this->_error_string($validator->errors()->all())]);
        }else{
	        $userRecord = DB::table('users')
	            ->select(DB::Raw("*,ifnull(dob,'".date('Y-m-d', strtotime('-13 years'))."') as dob,ifnull(bio,'') as bio"))
	            ->where('user_id',$request->user_id)
	            ->first();
	            if(stripos($userRecord->user_dp,'https://')!==false){
	                $file_path=$userRecord->user_dp;
	                $small_file_path=$userRecord->user_dp;
	            }else{
	                $file_path = asset(Storage::url('public/profile_pic/'.$request->user_id."/".$userRecord->user_dp));
                     $small_file_path = asset(Storage::url('public/profile_pic/'.$request->user_id."/small/".$userRecord->user_dp));
                
                if($file_path==""){
                    $file_path=asset('default/default.png');
                }
                if($small_file_path==""){
                    $small_file_path=asset('default/default.png');
                }
	            }
            
                
	        if($userRecord) {
	            $custom = collect(['large_pic'=>$file_path,'small_pic' => $small_file_path]);
                $userRecord = $custom->merge($userRecord);
	            $response = array( "status" => "success", "content" => $userRecord , 'large_pic' => $file_path ,'small_pic' => $small_file_path ); 
	        } else {
	            $response = array( "status" => "failed", "msg" => "Invalid user!" );
	        }
	        
            return response()->json($response);   
        }
	}

    public function updateUserInformation(Request $request){
		$validator = Validator::make($request->all(), [
            'app_token'          => 'required',
            'username'          => 'required',
            'user_id'          => 'required',
            'name'          => 'required',
            'email'          => 'required',
            'mobile'          => 'required',
            'gender'          => 'required',
            'dob'          => 'required',
        ],[
            'username.required' => 'Username is required',
            //'username.unique' => 'Username is already taken, try another one.',
            'user_id.required' => 'User Id is required',
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'mobile.required' => 'Mobile is required',
            'gender.required' => 'Gender is required',
            'dob.required' => 'DOB is required',
        ]);
        
        if (!$validator->passes()) {
            return response()->json(['status'=>'error','msg'=> $this->_error_string($validator->errors()->all())]);
        }else{
            $functions = new Functions();
            $token_res= $functions->validate_token($request->user_id,$request->app_token);
            if($token_res>0){
    	        $userRecord = DB::table('users')
    	            ->select("*")
    	            ->where('user_id',$request->user_id)
    	            ->first();
    			
    	        if($userRecord) {
    	        	$nameArr = explode(" ",$request->name);
    	        	$fname = $nameArr[0];
    	        	if( isset($nameArr[1]) ) {
    	        	    $lname = $nameArr[1];    
    	        	} else {
    	        	    $lname = '';
    	        	}
    	        	
    				DB::table('users')
                            ->where('user_id', $userRecord->user_id)
                            ->update(['username' => $request->username,'fname' => $fname,'lname' => $lname,'email' => $request->email,'mobile' => $request->mobile,'bio' => $request->bio,'gender' => $request->gender,'dob' => date('Y-m-d',strtotime($request->dob))]);
                            
                    $user = DB::table('users')
    	            ->select("*")
    	            ->where('user_id',$userRecord->user_id)
    	            ->first();
                    $user_content = array(
                        'user_id'           => $user->user_id,
                        'username'          => $user->username,
                        'fname'             => $user->fname,
                        'lname'             => $user->lname,
                        'email'             => $user->email,
                        'mobile'            => ($user->mobile !=null) ? $user->mobile : '',
                        'dob'               => $user->dob,
                        'active'            => $user->active,
                        'gender'            => $user->gender,
                        'user_dp'           => $user->user_dp,                    
                        'app_token'         => $user->app_token,
                        'country'           => $user->country,
                        'languages'         => $user->languages,
                        'player_id'         => '',
                        'timezone'          => $user->time_zone,
                        'login_type'        => $user->login_type,
                        'ios_uuid'         => $user->ios_uuid,
                        'last_active'       => Functions::fSafeChar($user->last_active),
                    );
    	            $response = array( "status" => "success", "msg" => "User information updated successfully.", "data" => $user_content ); 
    	        } else {
    	            $response = array( "status" => "failed", "msg" => "Invalid user!" );
    	        }
    	        
                return response()->json($response);   
            }else{
                return response()->json([
                    "status" => "error", "msg" => "Unauthorized user!"
                ]);
            }
	}
}
    public function socialLogin(Request $request){
        // print_r($request->all());
        // exit;
        $email = $request->email;
        $ios_email = $request->ios_email;
        $functions = new Functions();
        $isRecord = false;
        $user = DB::table("users")->whereRaw(DB::raw("(email='".$email."' or email='".$ios_email."') and email<>''"))->first();
        if($user){
            $isRecord = true;  
        } else {
            
            if($request->login_type == "A") {
                if($request->ios_uuid!="" && $request->ios_uuid!=null) {
                    $user = DB::table("users")->whereRaw(DB::raw("ios_uuid='".$request->ios_uuid."' OR email='.$request->ios_email.'"))->first();
                    if($user) {
                        echo "asdd"; exit;
                        $isRecord = true;
                    } else {
                        $isRecord = false;
                    }
                }
            }
            
        }
        if($isRecord){
            $ios_uuid = "";
            if($request->login_type == "A") {
                $ios_uuid = $request->ios_uuid;    
            }
            $uniques_user_id_res = DB::table("unique_users_ids")->select("unique_id","user_id","unique_token")->where('unique_token',$request->unique_token)->first();
            if($uniques_user_id_res){
                DB::table('unique_users_ids')
                ->where('unique_token',$request->unique_token)
                ->update(['user_id'=>$user->user_id]);
                DB::table('video_views')
                ->where('unique_id',$uniques_user_id_res->unique_id)
                ->where('user_id',0)
                ->update(['user_id'=>$user->user_id]);
            }else{
                DB::table('unique_users_ids')->insert(['unique_token'=>$request->unique_token,'user_id'=>$user->user_id]);   
            }
            // echo $user->login_type;
            $user_content = array(
                'user_id'           => $user->user_id,
                'username'          => $user->username,
                'fname'             => $user->fname,
                'lname'             => $user->lname,
                'email'             => $user->email,
                'mobile'            => ($user->mobile !=null) ? $user->mobile : '',
                'dob'               => $user->dob,
                'active'            => $user->active,
                'gender'            => $user->gender,
                'user_dp'           => $user->user_dp,                    
                'app_token'         => $user->app_token,
                'country'           => $user->country,
                'languages'         => $user->languages,
                'player_id'         => Functions::fSafeChar($request->player_id),
                'timezone'          => $user->time_zone,
                'login_type'        => $request->login_type,
                'ios_uuid'         => $ios_uuid,
                'last_active'       => Functions::fSafeChar($user->last_active),
            );
            if($request->login_type != "A") {
                // DB::table('users')
                //     ->where('user_id', $user->user_id)
                //     ->update(['fname' => $request->fname,'lname' => $request->lname, 'email' => $request->email,'login_type' => $request->login_type,'user_dp' => Functions::fSafeChar($request->user_dp)]);    
            }
             $is_following_videos=0;
            $followingVideos = DB::table("follow")
            ->select(DB::raw("follow_id"))
            ->where("follow_by",$user->user_id)
            ->first(); 
            if($followingVideos) {
                $is_following_videos = 1;
            }
            $user_content['is_following_videos']=$is_following_videos;
        
            $response = array("status" => "success",'msg'=>'Social login successfully' ,'content' => $user_content); 
        }
        else{
            $max_id = 1001;
            $username = "user" . $max_id;
            while(DB::table("users")->select("user_id")->where("username",$username)->first())
            {
                $max_id++;
                $username = "user" . $max_id;
            }
            $user_token = Hash::make($functions->_password_generate(20));
            $now  = date("Y-m-d H:i:s");
            $ios_uuid = "";
            if($request->login_type == "A") {
                $ios_uuid = $request->ios_uuid;    
            }
            $gender = "";
            if($request->gender != null && $request->gender != "") {
                if(strtolower($request->gender) == "male" || strtolower($request->gender) == "m") {
                    $gender = "m";
                } else if(strtolower($request->gender) == "female" || strtolower($request->gender) == "f") {
                    $gender = "f";
                } else {
                    $gender = "ot";
                }
            }
            $data = array(
                'username'          => $username,
                'fname'             => $request->fname,
                'lname'             => $request->lname,
                
                'active'            => '1',
                'gender'            => $gender,
                'app_token'         => $user_token,
                'country'           => Functions::fSafeChar($request->country),
                'languages'         => Functions::fSafeChar($request->languages),
                'time_zone'         => Functions::fSafeChar($request->timezone),
                'user_dp'           => Functions::fSafeChar($request->user_dp),
                'login_type'        => $request->login_type,
                // 'login_type'        => 'FB',
                'created_at'        => $now,
                'updated_at'        => $now,
                'ios_uuid'         => $ios_uuid,
                'email_verified'   => 1
            );

            if(isset($request->dob) && $request->dob!=''){
                $data['dob']     =  date("Y-m-d", strtotime($request->dob));
            }
            if(isset($request->email)){
                $data['email'] = $request->email;
            }

            if(isset($request->mobile)){
                $data['mobile'] = $request->mobile;
            }

            $id = DB::table('users')->insertGetId($data);
            $path = $functions->_getUserFolderName($id, "public/videos");
            // $path = $functions->_getUserFolderName($id, "public/videos/gif");
            $path2 = $functions->_getUserFolderName($id, "public/photos");
            $path3 = $functions->_getUserFolderName($id, 'public/profile_pic');
            $path3 = $functions->_getUserFolderName($id, 'public/sounds');
             $video_gif_path = "public/videos/".$id.'/gif';        
            // $profile_path = "public/profile_pic/".$id;        
            // $sound_path = "public/sounds/".$id;        
            $folderExists = Storage::exists($video_gif_path);
            if(!$folderExists){
                Storage::makeDirectory($video_gif_path);
            }
            // $folderExists1 = Storage::exists($profile_path);
            // if(!$folderExists1){
            //     Storage::makeDirectory($profile_path);
            // }
            // $folderExists3 = Storage::exists($sound_path);
            // if(!$folderExists3){
            //     Storage::makeDirectory($sound_path);
            // }
            
          
            $uniques_user_id_res = DB::table("unique_users_ids")->select("unique_id","user_id","unique_token")->where('unique_token',$request->unique_token)->first();
            if($uniques_user_id_res){
                DB::table('unique_users_ids')
                ->where('unique_token',$request->unique_token)
                ->update(['user_id'=>$id]);
                DB::table('video_views')
                ->where('unique_id',$uniques_user_id_res->unique_id)
                ->where('user_id',0)
                ->update(['user_id'=>$id]);
            }else{
                DB::table('unique_users_ids')->insert(['unique_token'=>$request->unique_token,'user_id'=>$id]);   
            }
        $user_content = array(
            'user_id'           => $id,
            'username'          => $username,
            'fname'             => $request->fname,
            'lname'             => $request->lname,
            'email'             => $request->email,
            'mobile'            => $request->mobile,
            
            'active'            => 1,
            'gender'            => ($request->gender == 'male') ? 'm' : 'f',
            'user_dp'           => $request->user_dp,                    
            'app_token'         => $user_token,
            'country'           => $request->country,
            'languages'         => $request->languages,
            'player_id'         => Functions::fSafeChar($request->player_id),
            'time_zone'         => $request->timezone,
            'user_dp'           => $request->user_dp,                    
            'last_active'       => $now, 
            'ios_uuid'         => $ios_uuid,
        );
         if(isset($request->dob) && $request->dob!=''){
                $user_content ['dob']     =  date("Y-m-d", strtotime($request->dob));
            }
            $is_following_videos=0;
            $followingVideos = DB::table("follow")
            ->select(DB::raw("follow_id"))
            ->where("follow_by",$id)
            ->first(); 
            if($followingVideos) {
                $is_following_videos = 1;
            }
            $user_content['is_following_videos']=$is_following_videos;
        $response = array("status" => "success",'msg'=>'Social login successfully' ,'content' => $user_content); 
    }
         
    return response()->json($response); 
    
}

public function verifyOtp(Request $request){
    $otp = $request->otp;
    if(strlen($otp)<=6){
        $user_id= $request->user_id;
        $app_token = $request->app_token;
        $chk = DB::table("users")->select(DB::raw("user_id,user_dp,app_token,fname,lname,mobile,email,gender,ifnull(dob,'NA') as dob,verification_time,verification_code"))->where("user_id",$user_id)->whereNotNull("verification_time")->first();

        if($chk){
            $now = date('Y-m-d H:i:s');
            $datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $chk->verification_time);
            $datetime->modify('+10 minutes');
            $expiryTime= $datetime->format('Y-m-d H:i:s');
            $datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $chk->verification_time);
            $datetime->modify('+10 minutes');
            $expiryTime= $datetime->format('Y-m-d H:i:s');
            if(strtotime($now) > strtotime($expiryTime)){
                $response = array("status" => "error",'msg'=>'Otp Expired');      
            }else{
                if(($chk->verification_code) != trim($otp)){
                    $response = array("status" => "error",'msg'=>'Otp doesn\'t match.');      
                }else{
                    DB::table("users")->where("user_id",$user_id)->update(array("active"=>'1',"email_verified"=>'1','verification_code'=>'','verification_time'=>null));
                    $response = array("status" => "success",'msg'=>'Profile activated successfully. Proceed to Login', 'content' => json_decode(json_encode($chk), true));      
                }
            }
        }else{
            $response = array("status" => "error",'msg'=>'OTP expired');      
        }
    }else{
        $response = array("status" => "error",'msg'=>'Otp should be of 6 digits');      
    }
    
    return response()->json($response);

}

}   