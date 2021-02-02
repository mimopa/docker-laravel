<?php
namespace App\Http\Controllers\Web;

use App\Events\MyEvent;
use App\Helpers\Common\Functions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Storage;
use Auth;
use Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Exception;
use FFMpeg\Filters\Video\VideoFilters;
use FFMpeg\Format\Video\X264;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Owenoj\LaravelGetId3\GetId3;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use stdClass;
use FFMpeg as FFMpeg1;
use FFProbe as FFProbe1;
use Illuminate\Support\Facades\Hash;
use File;

class UserController extends WebBaseController
{

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    //protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function editUserProfile($userId)
    {
        $user = DB::table('users')->where('user_id', intval($userId))->first();
        $authUserId = $this->authUser->user_id;

        if (empty($user) || $userId != $authUserId) {
            return redirect()->route('web.home');
        }

        return view('web.auth.editUser', ['user' => $user]);
    }

    public function updateUserProfile($userId, Request $request)
    {
        $user = User::find(intval($userId));

        if (empty($user) || $user->user_id != $this->authUser->user_id) {
            Auth::guard('web')->logout();
        }
        $before_date = date('Y-m-d', strtotime('-13 years'));
        $rules = [
            'username' => ['required', 'string', 'max:255'],
            'fname' => ['required', 'string', 'max:255'],
            'lname' => ['string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId . ',user_id'],
            // 'mobile' => ['numeric', 'nullable'],
            // 'gender' => ['required', 'string', 'max:255'],
            // 'dob' => ['required', 'date', 'before_or_equal:' . $before_date],
            // 'image' => ['nullable', 'mimes:jgp,png,mpeg'],
        ];

        $messages = [
            'email.required'            => 'You cant leave User name field empty',
            'email.email'            => 'You must enter an email address',
            'email.unique'            => 'The email must be unique',
            'password.required'         => 'You cant leave Password field empty',
            'password.min'              => 'Password has to be 6 chars long'
        ];
        $this->validate($request, $rules, $messages);
        
        $user->fname = $request->get('fname');
        $user->lname = $request->get('lname');
        $user->username = $request->get('username');
        $user->gender = $request->get('gender');
        $user->mobile = !empty($request->get('mobile')) ? $request->get('mobile') : '';
        $user->dob = $request->get('dob');
        $user->email = $request->get('email');
        $user->login_type = 'O';
        $user->save();
     
        if ($request->hasFile('profile_pic')) {
            try {
                $image_file = $request->file('profile_pic');

                if($image_file->isvalid()) {
                    $functions = new Functions();
                
                $path = 'public/profile_pic/'.$user->user_id;
                
                $filenametostore = request()->file('profile_pic')->store($path);  
                Storage::setVisibility($filenametostore, 'public');
                $fileArray = explode('/',$filenametostore);  
                $fileName = array_pop($fileArray); 
                // dd(asset(Storage::url('public/profile_pic/'.$user->user_id.'/'.$fileName)));
                $functions->_cropImage($image_file,300,300,0,0,$path.'/small',$fileName);
                // dd(66);
                $file_path = asset(Storage::url('public/profile_pic/'.$user->user_id."/".$fileName));
               
                $small_file_path = asset(Storage::url('public/profile_pic/'.$user->user_id."/small/".$fileName));
                if($file_path==""){
                    $file_path=asset(config('app.profile_path')).'default-user.png';
                }
                if($small_file_path==""){
                    $small_file_path=asset(config('app.profile_path')).'default-user.png';
                }
                
                $data =array(
                    'user_id'       => $user->user_id,
                    'image'         => $fileName				
                ); 
                
                DB::table('users')
                ->where('user_id', $user->user_id)
                ->update(['user_dp'=>$fileName]);
                }
            } Catch(Exception $ex) {
                dd($ex->getMessage());
                return redirect()->back();
            }
        }

        Session :: flash('success', 'Update successfull.');
        return redirect()->back();
    }

    public function userProfile(Request $request,$userId)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('web.login');
        }
        $user = User::find(intval($userId));
        $followed = false;
        $authUserId = $this->authUser->user_id ?? 0;

        $notify_ids[] = explode(',', request()->get('notify_ids'));

        if (!empty($notify_ids)) {
            DB::table('notifications')
                ->whereIn('notify_id', $notify_ids)
                ->update(['read' => 1]);
        }
        
        if (empty($user)) {
            return redirect()->route('web.home');
        }

        $canFollow = $authUserId != $userId;
        $followed = DB::table('follow')
                    ->where(['follow_to' => $userId, 'follow_by' => $authUserId])->exists();

        $userInfo = DB::table('users as u')
                    ->leftJoin('videos as v', function($join) {
                        $join->on('v.user_id', 'u.user_id');
                    })
                    ->leftJoin('user_verify as uv','uv.user_id','=','v.user_id')
                    ->select('u.user_id', 'u.username', 'u.user_dp', 'u.login_type', 'u.fname', 'u.lname','uv.verified as verified')
                    ->selectRaw('count(v.video_id) as total_videos')
                    ->selectRaw('sum(v.total_likes) as total_likes')
                    ->groupBy('v.user_id', 'u.user_id', 'u.username', 'u.user_dp', 'u.login_type')
                    ->where('u.user_id', $userId)
                    ->first();
        $followers = DB::table('follow')->where('follow_to', $userId)->count();
        $limit=config('app.videos_per_page');
        if($request->page){
            if($request->type){
                $offset=0;
                $limit=$request->page * $limit;
            }else{
                $offset=($request->page-1) * $limit;
            }
        }else{
            $offset=0;
        }
        $videos = DB::table('videos')
                    ->select('user_id', 'video_id', 'video', 'title', 'thumb', 'gif', 'total_likes', 'total_comments', 'total_views')
                    ->orderBy('video_id','desc')
                    // ->orderBy('total_likes')->orderBy('total_views')
                    ->where(['user_id' => $userId, 'active' => 1, 'enabled' => 1])
                    ->skip($offset)->take($limit)->get();
                    // ->paginate($limit);
        $videosCount = DB :: table('videos')
                    ->orderBy('video_id','desc')
                    ->where(['user_id' => $userId, 'active' => 1, 'enabled' => 1])
                    ->count();
        if($request->page){
            $count=$offset;
            $x=$offset+1;
            $html='';
            foreach($videos as $video){
                $html.='<div class="col-lg-3 col-md-6 col-12 video p-2" style="text-align:center;">';
                $html.='<div style="box-shadow: 0px 2px 8px #ccc;border-radius: 5px;padding:10px;">';
                $html.='<div class="row container_top_header" onclick="openModal(\'video_'.$count.'\')">';
                $html.='<div class="col-md-3 col-3 userdp_div">';
                      
                        if($userInfo->user_dp!=""){
                            if(strpos($userInfo->user_dp,'facebook.com') !== false || strpos($userInfo->user_dp,'fbsbx.com') !== false || strpos($userInfo->user_dp,'googleusercontent.com') !== false){ 
                                $u_dp=$userInfo->user_dp;
                            }else{
                             $u_dp=asset(Storage::url('public/profile_pic').'/'.$userInfo->user_id.'/small/'.$userInfo->user_dp) ;
                      
                            }
                        }else{ 
                                $u_dp= asset('default/default.png');
                            } 
                $html.='<img class="img-fluid" src="'.$u_dp.'">';
                $html.='</div>';
                $html .='<div class="col-md-7 col-7 text-left pl-0">';
                $html .='<h5 class="username_div">'.$userInfo->fname.' '.$userInfo->lname.'</h5>';
                $html .= '<p class="title_div">'.(strlen($video->title) > 20 ) ? substr($video->title, 0, 20).'...' : $video->title.'</p>';
                $html .='</div>';
                $html.='<div class="col-md-1 col-1">';
                    if (auth()->guard('web')->user()->user_id == $userInfo->user_id){
                        $html.='<input type="checkbox" value="'.$video->video_id.'" class="checkbox_'. $x .'">';
                    }
                $html.='</div>';
                $html.='</div>';
                $html.='<div class="video-container">';
                $html.='<video muted="muted" id="video_'.$count.'" data-toggle="modal" data-target="#LeukeModal" 
                            onmouseover="hoverVideo('.$count.')" onmouseout="hideVideo('.$count.')" class="" style="width:100%;height:100%;background: #000;border-radius: 8px;" 
                            loop preload="none" onclick="modelbox_open(\''.asset(Storage::url('public/videos/' . $video->user_id . '/' . $video->video )).'\', '.$video->video_id .')"
                            poster="'. asset(Storage::url('public/videos/' . $video->user_id . '/thumb/' . $video->thumb )) .'">
                            <source src="'. asset(Storage::url('public/videos/' . $video->user_id . '/' . $video->video )) .'" type="video/mp4">
                            </video>';
                $html.='</div>';
                $html.='<div class="user_name">';
                $html.='<div>@'.$userInfo->username.'</div>';
                $html.='<div class="video_view"><i class="fa fa-eye"></i> '.$video->total_views.'</div>';
                $html.='</div>';
              
                $html.='<div class="views row m-1" onclick="openModal(\'video_'.$count.'\')">
                        <div class="col-md-6 col-6 text-center">
                            <i class="fa fa-heart-o" aria-hidden="true"></i> '. $video->total_likes .'
                        </div>
                        <div class="col-md-6 col-6 text-center">
                            <i class="fa fa-comment-o" aria-hidden="true"></i> '. $video->total_comments .'
                        </div>
                    </div>
                </div>
            </div>';
            $count++;
            $x++;
            }
            return response()->json(['html'=> $html,'videosCount'=>$videosCount,'userInfo' => $userInfo, 'videos' => $videos, 'canFollow' => $canFollow, 'followed' => $followed,
            'followers' => $followers ]);
        }
        return view('web.userProfile', ['videosCount'=>$videosCount ,'userInfo' => $userInfo, 'videos' => $videos, 'canFollow' => $canFollow, 'followed' => $followed,
                    'followers' => $followers]);
    }

    public function followUnfollowUser($userId, Request $request)   
    {
        $success = false;
        $follow = false;
        $dt = date('Y-m-d h:i:s');
        $userExists = DB::table('users')
                        ->where(['user_id' => intval($userId), 'active' => 1])
                        ->exists();
                        // dd($userExists);
        if ($userExists && $userId != $this->authUser->user_id) {
            $userFollowed = DB::table('follow')
                                ->where(['follow_to' => $userId, 'follow_by' => $this->authUser->user_id])->exists();
            if ($userFollowed) {
                //user follow row gets deleted.
                DB::table('follow')
                    ->where(['follow_to' => $userId, 'follow_by' => $this->authUser->user_id])->delete();
                //change the notification status of user followed to read.
                DB::table('notifications')
                    ->where(['notify_by' => $this->authUser->user_id, 'notify_to' => $userId, 'type' => 'F'])
                    ->update(['read' => 1]);
            } else {
                DB::table('follow')
                ->insert([
                    'follow_by' => $this->authUser->user_id,
                    'follow_to' => $userId,
                    'follow_on' => $dt,
                    ]);
                $follow = true;
            }
            $success = true;
        } else {
            return response()->json(['success' => $success]);
        }

        if ($follow) {
            $msg = 'Follow you.';
            $type = 'F';
        } else {
            $msg = 'Unfollow you.';
            $type = 'UF';
        }

        $profileImg = Functions::getProfileImageUrl($this->authUser);
        // $profileImg = '';
        // if(!empty($authUser->user_dp)) {
        //     if($authUser->login_type == 'O') {
        //        $profileImg =  asset('storage/profile_pic/' . $authUser->user_id) . '/small/' . $authUser->user_dp;
        //     } else {
        //        $profileImg = $authUser->user_dp; 
        //     }
        // } else {
        //     $profileImg = asset('storage/profile_pic/default.png');
        // }

        $notify_ids = DB::table('notifications')
            ->insertGetId([
                'notify_by' =>  $this->authUser->user_id,
                'notify_to' => $userId,
                'message' => $msg,
                'video_id' => 0,
                'type' => $type,
                'added_on' => $dt,
            ]);
        /* $notify = [
            'notifyUserName' => $this->authUser->username,
            'notifyUserId' => $this->authUser->user_id,
            // 'follow' => $follow,
            'type' => $type,
            'notify_to' => $userId,
            'video_id' => 0,
            'msg' => $msg,
            'date' => date('d.m.Y , H:i', strtotime($dt)),
            'profileImg' => $profileImg,
        ]; */

        $notifications = new stdClass();
        $notifications->profileImg = $profileImg;
        $notifications->user_id = $this->authUser->user_id;
        $notifications->username = $this->authUser->username;
        $notifications->type = $type;
        $notifications->video_id = 0;
        $notifications->added_on = $dt;
        $notifications->message = $msg;
        $notifications->notify_to = $userId;
        $notifications->notify_total = 0;
        $notifications->notify_ids = $notify_ids;
        $notifications->read = 0;

        $notifications = collect([$notifications]);

        $notifyHtml = view('partials.notification',  ['notifications' => $notifications])->render();

        // event(new MyEvent($notify, $userId));
        event(new MyEvent($notifyHtml, $userId));
        
        return response()->json(['success' => $success, 'follow' => $follow]);
			
    }
    
    public function userNotifications()
    {
        $authUserId = $this->authUser->user_id;

        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();

        $notifications = DB::table('notifications as n')
        ->join('users as u', 'u.user_id', 'n.notify_by')
        ->select('n.*', 'u.user_id', 'u.username', 'u.user_dp', 'u.login_type', DB::raw("GROUP_CONCAT(n.notify_id SEPARATOR ',') as notify_ids,uv.verified"),
                DB::raw("DATE_FORMAT(n.added_on, '%Y-%m-%d') as date"))
        ->leftJoin('user_verify as uv','uv.user_id','u.user_id')
        ->selectRaw('count(n.type) as notify_total')
        ->where([['n.notify_to',  $authUserId ?? 0]])
        ->whereNotIn('n.type', ['UF', 'UL'])
        ->groupBy('date', 'n.type')
        ->paginate(2);
        // dd($notifications);
        
        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();

        return view('web.notifications',['notifications' => $notifications]);
    }

    public function notificationStatus()
    {
        $notificationHtml = '';
        $success = false;
        $type = request()->get('type');

        if (!empty($type)) {
            DB::table('notifications')->where('notify_to', $this->authUser->user_id)->update(['read' => 1]);
        } else {
            $notifications = $this->commonNotifications();
            $notificationHtml = view('partials.notification', ['notifications' => $notifications])->render();
        }

        $success = true;

        return response()->json(['success' => $success, 'notificationHtml' => $notificationHtml]);

    }

    public function uploadVideo()
    {
        return view('web.uploadVideo');
    }

    public function insertVideo(Request $request)
    {
        // dd($request->all());
        $rules = [ 
            // 'description' => 'required',
            'file'          => 'required|mimes:mp4,mov,ogg,qt',  
        ];
        $messages = [ 
            // 'description.required'   => 'Description is required',
            'file.required'   => 'Video is required',
        ];
        $this->validate($request, $rules, $messages);

            $format = new X264('aac', 'libx264');
            $storage_path=config('app.filesystem_driver');
            
            $time_folder=time();
            $videoPath = 'public/videos/'. $this->authUser->user_id;
            $videoFileName=$request->file('file')->getClientOriginalName();
            $watermark = DB::table('settings')->first();

            $videoFileName = $request->file('file')->getClientOriginalName();
            $tempFileName =  'temp_' . $request->file('file')->getClientOriginalName();
            $request->file->storeAs($videoPath . '/' . $time_folder, $tempFileName);
            Storage::setVisibility($videoPath . '/' . $time_folder.'/'.$tempFileName, 'public');
            // dd(Storage::url($videoPath . '/' . $time_folder.'/'.$tempFileName));
        //    dd(Storage::url($videoPath . '/' . $time_folder));
            // dd($watermark);
            // watermark
            if($watermark){
                $watermark_img = $watermark->watermark;
            }else{
                $watermark_img=""; 
            }
                if($watermark_img!="") {
                    FFMpeg::fromDisk($storage_path)
                    ->open($videoPath . '/' . $time_folder . '/' . $tempFileName)
                    ->addFilter(function (VideoFilters $filters) use($watermark_img){
                        $filters->watermark("storage/uploads/logos/" . $watermark_img, [
                            'position' => 'relative',
                            'top' => 10,
                            'right' => 10,
                        ]);
                     })
                    ->export()
                    ->toDisk($storage_path)
                    ->inFormat($format)
                    //->inFormat(new \FFMpeg\Format\Audio\Aac)
                    ->save($videoPath . '/' . $time_folder. '/' . $videoFileName);

                    //thumbnail image
                    FFMpeg::fromDisk($storage_path)
                        ->open($videoPath . '/' . $time_folder . '/' . $tempFileName)
                        ->getFrameFromSeconds(0)
                        ->export()
                        ->toDisk($storage_path)
                        ->save($videoPath . '/thumb/' . $time_folder . '.jpg');

                        Storage::delete("public/videos/" . $this->authUser->user_id . '/' . $time_folder . '/' . $tempFileName);
                    // unlink(storage_path() . "/app/public/videos/" . $this->authUser->user_id . '/' . $time_folder . '/' . $tempFileName);
                } 
            //     else {
            //         $videoFileName = $request->file('file')->getClientOriginalName();
            //         $request->file->storeAs("public/videos/".$request->user_id.'/'.$time_folder, $videoFileName);
            //     }
            // }
            else{
                // dd(Storage::url($videoPath . '/' . $time_folder . '/' . $tempFileName));
                FFMpeg::fromDisk($storage_path)
                ->open($videoPath . '/' . $time_folder . '/' . $tempFileName)
                ->export()
                ->toDisk($storage_path)
                ->inFormat($format)
                //->inFormat(new \FFMpeg\Format\Audio\Aac)
                ->save($videoPath . '/' . $time_folder. '/' . $videoFileName);
                Storage::setVisibility($videoPath . '/' . $time_folder. '/' . $videoFileName, 'public');
                // dd(Storage::url($videoPath . '/' . $time_folder. '/' . $videoFileName));
                
        
                FFMpeg::fromDisk($storage_path)
                ->open($videoPath . '/' . $time_folder . '/' . $tempFileName)
                ->getFrameFromSeconds(0)
                ->export()
                ->toDisk($storage_path)
                ->save($videoPath . '/thumb/' . $time_folder . '.jpg');
                Storage::setVisibility($videoPath . '/thumb/' . $time_folder . '.jpg', 'public');
              
                // dd(Storage::url( "public/videos/" . $this->authUser->user_id . '/' . $time_folder . '/' . $tempFileName));
                Storage::delete("public/videos/" . $this->authUser->user_id . '/' . $time_folder . '/' . $tempFileName);
                //unlink(Storage::url( "public/videos/" . $this->authUser->user_id . '/' . $time_folder . '/' . $tempFileName));
            }

            // $description = $request->get('description');

            $data = [
                'enabled' => 0,
                'user_id' => $this->authUser->user_id,
                //'title' => implode(' ', explode(' ', $description, 3)),
                // 'description' => $description,
                'video' => $time_folder . '/' . $videoFileName,
                'thumb' => $time_folder . '.jpg',
                'created_at' => date('Y-m-d h:i:s')
            ];

            $v_id=DB::table('videos')
                ->insertGetId($data);
                return response()->json(["status" => "success", "data" => $data,"v_id" => $v_id]);
            // return redirect()->route('web.userProfile', $this->authUser->user_id);

    }
    public function videoInfoUpdate($id){
        return view('web.videoDetail', ['id' => $id, 'user_id' => $this->authUser->user_id]);
    }
    public function videoInfoSubmit(Request $request){
    //   dd($request->all());
        $rules = [ 
            'description' => 'required',
            'title' => 'required',
            // 'hashtag' => 'required'
        
        ];
        $messages = [ 
            'description.required'   => 'Description is required',
            'title.required'   => 'Title is required',
            // 'hashtag.required'   => 'Hashtag is required',
            
        ];
        $this->validate($request, $rules, $messages);
        
        $data = [
            'enabled' => 1,
            'title' => $request->title,
            'description' => $request->description,
            'tags' => ($request->tags) ? $request->tags : null,
            'updated_at' => date('Y-m-d h:i:s')
        ];

        DB::table('videos')
            ->where('video_id',$request->id)
            ->where('user_id',$this->authUser->user_id)
            ->update($data);
        return redirect()->route('web.userProfile', $this->authUser->user_id);

    }
    public function deleteVideo()
    {
        $success = false;
        $videoIds = request()->get('videos');

        foreach($videoIds as $key=>$v_id){

            $video_detail=DB::table('videos')
                ->where('user_id', $this->authUser->user_id)
                ->where('video_id', $v_id)
                ->first();
        
                $name=$video_detail->thumb;
                $f_name=explode('.',$name);

                $folder_name=$this->authUser->user_id.'/'.$f_name[0];
                $thumb_name=$this->authUser->user_id.'/thumb/'.$f_name[0].'.jpg';
                $gif_name=$this->authUser->user_id.'/gif/'.$f_name[0].'.gif';
               
                Storage::deleteDirectory("public/videos/" . $folder_name);
                Storage::delete("public/videos/" . $thumb_name);
               
        }
        

        DB::table('videos')
            ->where('user_id', $this->authUser->user_id)
            ->whereIn('video_id', $videoIds)
            ->update(['active' => 0, 'deleted' => 1]);
        $success = true;
        return response()->json(['success' => $success]);
    }

    public function leukeSearch()
    {
        $success = false;
        $result = [];
        $search_term = request()->get('term');

        $users = DB::table('users')
                    ->where(function($query) use ($search_term) {
                        $query->where('username', 'like', '%'. $search_term . '%')
                            ->orWhere('fname', 'like', '%' . $search_term . '%')
                            ->orWhere('lname', 'like', '%' . $search_term . '%')
                            ->orWhere('email', 'like', '%' . $search_term . '%');
                    })
                    ->select('user_id', 'username', 'user_dp', 'login_type')
                    ->orderBy('username', 'asc')
                    ->get();
        $videos = DB::table('videos')
                    ->where(function($query) use ($search_term) {
                        $query->where('title', 'like', '%'. $search_term . '%')
                            ->orWhere('description', 'like', '%' . $search_term . '%');
                    })
                    ->select('video_id', 'user_id', 'title', 'thumb')
                    ->where('active',1)
                    ->where('deleted',0)
                    ->where('flag',0)
                    ->orderBy('title', 'asc')
                    ->get();

        if ($users->count() > 0) {
            foreach ($users as $user) {
                $row['label'] = $user->username;
                $row['imgSrc'] = Functions::getProfileImageUrl($user);
                $row['url'] = route('web.userProfile', $user->user_id);
                $result[] = $row;
            }
        }

        if ($videos->count() > 0) {
            foreach ($videos as $video) {
                $row['label'] = $video->title;
                $row['imgSrc'] = Functions::getVideoThumbUrl($video);
                $row['url'] = route('web.home', ['videoId' => $video->video_id]);
                $result[] = $row;
            }
        }

//         $result['success'] = true;
// dd($result);
        return json_encode(['result' => $result]);
    }

    public function changePassword()
    {
        return view('web.changePassword');
    }

    public function updatePassword(Request $request)
    {
        $user = $this->authUser;
        $rules = [
            'old_password' => ['required', function($attribute, $value, $fail) use ($user) {
                                if (!Hash :: check($value, $user->password)) {
                                    $fail('Your old password does not match');
                                }                
                            }],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:old_password'],
        ];
        $messages = [
            'password.different' => 'The old and the new password must be different',
        ];
        $this->validate($request, $rules, $messages);
        
        $user->fill([
            'password' => Hash::make($request->password)
            ])->save();
        
        Session :: flash('success', 'Password update successfull');

        return back();
    }

}