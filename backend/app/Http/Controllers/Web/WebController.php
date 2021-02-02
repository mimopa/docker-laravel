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
use Dotenv\Result\Success;
use Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Exception;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use stdClass;

class WebController extends WebBaseController
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

    public function home(Request $request)
    {
        
        $notifIds = explode(',', request()->get('notify_ids'));

        if (!empty($notifIds)) { 
            DB::table('notifications')
                ->whereIn('notify_id', $notifIds)
                ->update(['read' => 1,]);
        }

        $video = DB::table('videos')->where('video_id', intval(request()->get('videoId')))->first();
        $sharedVideoId = '';
        $sharedVideoSrc = '';

        if (Auth::guard('web')->check()) {
            $user_id= $this->authUser->user_id;
        }else{
            $user_id=0;
        }
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
        
        $videos = DB :: table('videos as v')
                    ->select(DB::raw('v.user_id, v.video_id,u.username, v.video,v.title, v.thumb, v.gif, v.total_likes, v.total_comments, v.total_views, ifnull(l.like_id, 0) as liked,uv.verified as verified,u.user_dp,CONCAT(u.fname," ",u.lname) as name,v.total_comments'))
                    // ->leftJoin('likes as l','l.')
                    ->leftJoin('likes as l', function ($join)use ($user_id){
                        $join->on('l.video_id','=','v.video_id')
                            ->whereRaw(DB::raw(" l.user_id=".$user_id ));
                        })
                    ->leftJoin('user_verify as uv','uv.user_id','=','v.user_id')
                    ->leftJoin('users as u','u.user_id','=','v.user_id')
                    ->orderBy('v.video_id','desc')
                    // ->orderBy('v.total_likes')->orderBy('v.total_views')
                    ->where(['v.active' => 1, 'v.enabled' => 1])
                    ->skip($offset)->take($limit)->get();
                    // ->paginate($limit);
        $videosCount = DB :: table('videos as v')
        ->leftJoin('likes as l', function ($join)use ($user_id){
            $join->on('l.video_id','=','v.video_id')
                ->whereRaw(DB::raw(" l.user_id=".$user_id ));
            })
        ->leftJoin('user_verify as uv','uv.user_id','=','v.user_id')
        ->leftJoin('users as u','u.user_id','=','v.user_id')
        ->orderBy('v.total_likes')->orderBy('v.total_views')
        ->where(['v.active' => 1, 'v.enabled' => 1])
        ->count();
                    // dd($videos);
        if (!empty($video)) {
            $sharedVideoId = request()->get('videoId');
            $sharedVideoSrc = asset(Storage::url('public/videos/' . $video->user_id . '/' . $video->video ));
        }
        $home_data=DB::table('home_settings')->first();
        if($request->page){
            $count=$offset;
            $html='';
            foreach($videos as $video){
            $html.='<div class="col-lg-4 col-md-6 col-12 video p-2" style="text-align:center;">
            <div style="box-shadow: 0px 2px 8px #ccc;border-radius: 5px;padding:10px;">
                <div class="row container_top_header"  onclick="openModal(\'video_'.$count.'\')">
                    <div class="col-md-3 col-3 userdp_div">';
                    
                    if($video->user_dp!=""){
                    if(strpos($video->user_dp,'facebook.com') !== false || strpos($video->user_dp,'fbsbx.com') !== false || strpos($video->user_dp,'googleusercontent.com') !== false){ 
                        $u_dp=$video->user_dp;
                     }else{
                        // $exists = Storage::disk(config('app.filesystem_driver'))->exists('public/profile_pic/'.$video->user_id.'/small/'.$video->user_dp);
                        // if($exists){ 
                            $u_dp=asset(Storage::url('public/profile_pic').'/'.$video->user_id.'/small/'.$video->user_dp) ;
                        //  }else{ 
                        // $u_dp= asset('storage/profile_pic/default.png');
                        // } 
                    }
                }else{ 
                        $u_dp= asset('default/default.png');
                        } 
                       
                $html.='<img class="img-fluid" src="'.$u_dp.'">';
                $html.='</div>';
                $html.='<div class="col-md-9 col-9 text-left pl-0">';
                $html .= '<h5 class="username_div">'.$video->name.'</h5>';
                $html .= '<p class="title_div">'.(strlen($video->title) > 22 ) ? substr($video->title, 0, 22).'...' : $video->title.'</p>';
                $html.='</div>';
                $html.='</div>';
                $html.='<div class="video-container">';
                $html.='<video muted="muted" id="video_'.$count.'" data-toggle="modal" data-target="#LeukeModal"  
                        onmouseover="hoverVideo('.$count.')" onmouseout="hideVideo('.$count.')" class="img-responsive" style="height:100%;border-radius: 8px;background: #000;"
                        loop preload="none" onclick="modelbox_open(\''.asset(Storage::url('public/videos/' . $video->user_id . '/' . $video->video )).'\', '.$video->video_id .', video_'.$count.')"
                        poster="'.asset(Storage::url('public/videos/' . $video->user_id . '/thumb/' . $video->thumb )) .'">
                        <source src="'. asset(Storage::url('public/videos/' . $video->user_id . '/' . $video->video )) .'" type="video/mp4">
                    </video>';
                    $html.='</div>';
                    $html.='<div class="user_name">';
                $html.='<div>@'.$video->username;
                     if($video->verified=='A'){ 
                        $html.='<img src="'. asset('default/verified-icon-blue.png') .'" alt="" style="width:15px;height:15px;">';
                   } 
                   $html.='</div>';
                   $html.='<div class="video_view" style="'.Functions::getTopbarColor().'"><i class="fa fa-eye"></i> '.$video->total_views.'</div>';
                   $html.='</div>';
                   $html.='<div class="views row m-1" onclick="openModal(\'video_'.$count.'\')">';
                   $html.='<div class="col-md-6 col-6 text-center">';
                    $html.='<i class="fa fa-heart-o" aria-hidden="true"></i> '.$video->total_likes ;
                    $html.='</div>';
                    $html.='<div class="col-md-6 col-6 text-center">';
                    $html.='<i class="fa fa-comment-o" aria-hidden="true"></i> '.$video->total_comments.
                    '</div>
                </div>
            </div>
           
        </div>';
        $count++;
                }
            return response()->json(['html'=> $html,'videos' => $videos, 'sharedVideoId' => $sharedVideoId, 'sharedVideoSrc' => $sharedVideoSrc,'home_data'=>$home_data,'videosCount'=>$videosCount ]);
        }
        return view('web.home', ['videos' => $videos, 'sharedVideoId' => $sharedVideoId, 'sharedVideoSrc' => $sharedVideoSrc,'home_data'=>$home_data,'videosCount'=>$videosCount ]);
    }

    public function videoInfo($videoId)
    {
        $video = DB::table('videos as v')
                ->join('users as u', 'u.user_id', 'v.user_id')
                ->leftJoin('user_verify as uv', 'uv.user_id', 'v.user_id')
                ->select(DB::raw('u.user_id,u.username,v.title, v.created_at, v.total_views, v.video_id, u.user_dp, u.login_type, CONCAT(fname," ",lname) as name, uv.verified'))
                ->where('v.video_id', $videoId)
                ->first();
            
        if(!empty($video->user_dp)){
            if((strpos($video->user_dp,'facebook.com') !== false) || (strpos($video->user_dp,'fbsbx.com') !== false) || (strpos($video->user_dp,'googleusercontent.com') !== false)){
                $video->user_dp=$video->user_dp;
            }else{
                // $exists = Storage::disk(config('app.filesystem_driver'))->exists('public/profile_pic/'.$video->user_id.'/small/'.$video->user_dp);
                // if($exists){
                // if(file_exists(public_path('storage/profile_pic').'/'.$user->user_id.'/small/'.$user->user_dp)){
                    $video->user_dp=asset(Storage::url('public/profile_pic/' . $video->user_id. '/small/' . $video->user_dp));
                // }else{
                //     $video->user_dp=asset('storage/profile_pic/default.png');
                // } 
            }  
        }else{
            $video->user_dp=asset('default/default.png');
        } 

                // dd($video);
        $comment_html = '';
        $profileImg = '';
        $userLikedVideo = 'false';
      
        $comments = DB::table('comments as c')
                    //->leftJoin('videos as v', 'c.video_id', 'v.video_id')
                    ->leftJoin('users as u', 'u.user_id', 'c.user_id')
                    ->leftJoin('user_verify as uv', 'uv.user_id', 'c.user_id')
                    
                    ->select('u.user_id', 'u.username', 'c.comment', 'c.updated_on', 'u.login_type', 'u.user_dp','uv.verified')
                    ->where('c.video_id', $videoId)
                    ->paginate(config('app.view_per_page'));
            $morePages = $comments->hasMorePages();
                    // ->limit($comments_per_page)
                    // ->get();

        if (Auth::guard('web')->check()) {
            $comment_html = view('partials.comments', ['comments' => $comments,'morePages' =>$morePages])->render();
            $u_id = $this->authUser->user_id;
        }else{
            $u_id= 0;
        }
        

        $is_follow=DB::table('follow')
                    ->where('follow_to',$video->user_id)
                    ->where('follow_by',$u_id)
                    ->exists();
                    if($is_follow){
                        $f_label="Unfollow";
                    }else{
                        $f_label="Follow";
                    }
                    $follow_html="";
                    if($video->user_id!=$u_id){
        $follow_html='<span onclick="homefollowUnfollow('.$video->user_id.');return false;"><span class="follow_btn" id="followUnfollow">'.$f_label.'</span></span>';
        // $follow_html='<span><span class="follow_btn" id="followUnfollow">'.$f_label.'</span></span>';
                    }
        $profileImg = Functions::getProfileImageUrl($this->authUser);
        // if(!empty($video->user_dp)) {
        //     if($video->login_type == 'O') {
        //        $profileImg =  asset('storage/profile_pic/' . $video->user_id) . '/small/' . $video->user_dp;
        //     } else {
        //        $profileImg = $video->user_dp; 
        //     }
        // } else {
        //     $profileImg = asset('storage/profile_pic/default.png');
        // }

        if (Auth::guard('web')->check()) {
            $userLikedVideo = DB::table('likes')->where(['user_id' => $this->authUser->user_id, 'video_id' => $videoId])->exists();
        }

        $cookieExists = Cookie :: get('videoViewed');
        // dd($cookieExists);
        if ($cookieExists == null) {
            $cookieTime = time() + 60 * 60 * 24 * 30; // 30 days
            $token = str_random(32);

            Cookie :: queue('videoViewed', $token, $cookieTime);
            DB::table('unique_users_ids')->insert(['unique_token' => $token]);

        }
        
        return response()->json(['video' => $video, 'comment_html' => $comment_html, 'profileImg' => $profileImg, 
                'userLikedVideo' => $userLikedVideo,'follow_html' => $follow_html]);
    }

    public function videoViewed($videoId, Request $request) {

        if(!Auth::guard('web')->check()){
        if($request->unique_token){
            $unique_res = DB::table('unique_users_ids')
                ->select('unique_id')
                ->where('unique_token',$request->unique_token)
                ->first();
                if($unique_res){
                    $unique_id=$unique_res->unique_id;
                }else{
                    $unique_id=0;
                }
            }
        }else{
            $unique_id=0;
        }
     
        $userId = Auth::guard('web')->check() ? $this->authUser->user_id : 0;
        $check_view =DB::select("select view_id from `video_views` where `video_id` = $videoId and (user_id=" . 
            $userId . " or unique_id=$unique_id) and DATE(`viewed_on`) = '".date('Y-m-d')."' limit 1");
        // dd($check_view);
        $views=0;
        $views_res = DB::table('videos')
        ->select(DB::raw('total_views'))
        ->where('video_id',$videoId)
        ->first();

        $views=$views_res->total_views;
        
        if(empty($check_view)){
            DB::table('video_views')->insert([
                    'user_id' => $userId,
                    'video_id'=>$videoId,
                    'viewed_on'=>date('Y-m-d H:i:s'),
                    'unique_id'=>$unique_id]);
            $views=$views+1;
            DB::table('videos')->where('video_id',$videoId)->update(['total_views' => $views]);
        }
         // dd(DB::getQueryLog()); 
        $response = array("status" => "success",'total_views'=> $views);
        return response()->json($response);

    }

    public function videoComments($videoId, $type)
    {
        $morePages = '';
        $comments =  DB::table('Comments as c')
                    ->join('users as u', 'u.user_id', 'c.user_id')
                    ->leftJoin('user_verify as uv', 'uv.user_id', 'c.user_id')
                    ->select('u.user_id', 'u.username', 'c.comment', 'c.updated_on', 'u.login_type', 'u.user_dp','uv.verified')
                    ->where('c.video_id', $videoId);
        if ($type == 'scroll') {
            
            $comments = $comments->paginate(config('app.view_per_page'));
            $morePages = $comments->hasMorePages();
        } else {
            $comments = $comments->orderBy('c.added_on', 'desc')
                        ->first();
            $comments = collect([$comments]);
        }

        $comment_html = view('partials.comments', ['comments' => $comments, 'morePages' => $morePages])->render();
        
        return response()->json(['comment_html' => $comment_html, 'morePages' => $morePages]);
    }

    public function videoLike($videoId)
    {
        $success = false;
        $liked = false;
        $msg = "Liked you video";
        $type = "L";

        $video = DB::table('videos')->where('video_id', $videoId)->first();
        if (empty($video)) {
            return response()->json(['success' => $success, 'liked' => $liked]);
        }
        
        $dt = date('Y-m-d h:i:s');
        $isVideoLiked = DB::table('likes')
                        ->where(['video_id' => $videoId, 'user_id' => $this->authUser->user_id])
                        ->exists();
        
        if (!$isVideoLiked) {
            DB::table('likes')
                ->insert([
                    'liked_on' => $dt,
                    'video_id' => $videoId,
                    'user_id' => $this->authUser->user_id,
                ]);
            DB::table('videos')->where('video_id', $videoId)->increment('total_likes');
            $success = true;
            $liked = true;
        } else {
            DB::table('likes')->where(['video_id' => $videoId, 'user_id' => $this->authUser->user_id])->delete();
            DB::table('videos')->where('video_id', $videoId)->decrement('total_likes');
            $msg = "Disliked your video";
            $type = "UL";
            $liked = false;
            $success = true;
            //change the notification status of user video liked to read.
            DB::table('notifications')
            ->where(['notify_by' => $this->authUser->user_id, 'notify_to' => $video->user_id, 'video_id' => $videoId, 'type' => 'L'])
            ->update(['read' => 1]);
        }
        
        $profileImg = Functions::getProfileImageUrl($this->authUser);
        // if(!empty($authUser->user_dp)) {
            //     if($authUser->login_type == 'O') {
                //        $profileImg =  asset('storage/profile_pic/' . $authUser->user_id) . '/small/' . $authUser->user_dp;
                //     } else {
                    //        $profileImg = $authUser->user_dp; 
                    //     }
                    // } else {
                        //     $profileImg = asset('storage/profile_pic/default.png');
                        // }
                        
        if ($video->user_id != $this->authUser->user_id) {

            $notify_ids = DB::table('notifications')
                ->insertGetId([
                    'notify_by' =>  $this->authUser->user_id,
                    'notify_to' => $video->user_id,
                    'message' => $msg,
                    'Video_id' => $videoId,
                    'type' => $type,
                    'added_on' => $dt,
                ]);
            $checkUserVerified =Functions::checkUserVerified();
            $notifications = new stdClass();
            $notifications->profileImg = $profileImg;
            $notifications->user_id = $this->authUser->user_id;
            $notifications->username = $this->authUser->username;
            $notifications->type = $type;
            $notifications->video_id = $videoId;
            $notifications->added_on = $dt;
            $notifications->message = $msg;
            $notifications->notify_to = $video->user_id;
            $notifications->notify_total = 0;
            $notifications->notify_ids = $notify_ids;
            $notifications->read = 0;
            $notifications->verified =$checkUserVerified;
            /* $notify = [
                'notifyUserName' => $this->authUser->username,
                'notifyUserId' => $this->authUser->user_id,
                // 'follow' => $follow,
                'type' => $type,
                'notify_to' => $video->user_id,
                'video_id' => $videoId,
                'msg' => $msg,
                'date' => date('d.m.Y , H:i', strtotime($dt)),
                'profileImg' => $profileImg,
            ]; */

            $notifications = collect([$notifications]);

            $notifyHtml = view('partials.notification', ['notifications' => $notifications])->render();

            // event(new MyEvent($notify, $video->user_id));
            event(new MyEvent($notifyHtml, $video->user_id));
        }

        return response()->json(['success' => $success, 'liked' => $liked]);
    }

    public function videoPostComments($videoId, Request $request)
    {
        $success = false;
        $type = "C";
        $video = DB::table('videos')->where('video_id', $videoId)->first();
        if (empty($video)) {
            return response()->json(['success' => $success]);
        }

        $rules = [
            'video_comment' => ['required', 'string'],
        ];

        $messages = [
            'video_comment.required'            => 'You cant leave field empty',
        ];
        $this->validate($request, $rules, $messages);

        $dt = date('Y-m-d h:i:s');
        $msg = "Comment on your video";

        DB::table('comments')
            ->insert([
                'video_id' => $videoId,
                'user_id' => $this->authUser->user_id,
                'added_on' => $dt,
                'updated_on' => $dt,
                'comment' => $request->video_comment
            ]);
        DB::table('videos')->where('video_id', $videoId)
            ->update([
              'total_comments'=> DB::raw('total_comments+1')
            ]);
        $profileImg = Functions::getProfileImageUrl($this->authUser);
        
        if ($video->user_id != $this->authUser->user_id) {
            $checkUserVerified =Functions::checkUserVerified();
            $notify_ids = DB::table('notifications')
                ->insertGetId([
                    'notify_by' =>  $this->authUser->user_id,
                    'notify_to' => $video->user_id,
                    'message' => $msg,
                    'Video_id' => $videoId,
                    'type' => 'VC',
                    'added_on' => $dt,
                ]);
            /* $notify = [
                'notifyUserName' => $this->authUser->username,
                'notifyUserId' => $this->authUser->user_id,
                // 'follow' => $follow,
                'type' => $type,
                'notify_to' => $video->user_id,
                'video_id' => $videoId,
                'msg' => $msg,
                'date' => date('d.m.Y , H:i', strtotime($dt)),
                'profileImg' => $profileImg,
            ]; */

            $notifications = new stdClass();
            $notifications->profileImg = $profileImg;
            $notifications->user_id = $this->authUser->user_id;
            $notifications->username = $this->authUser->username;
            $notifications->type = $type;
            $notifications->video_id = $videoId;
            $notifications->added_on = $dt;
            $notifications->message = $msg;
            $notifications->notify_to = $video->user_id;
            $notifications->notify_total = 0;
            $notifications->notify_ids = $notify_ids;
            $notifications->read = 0;
            $notifications->verified =$checkUserVerified;

            $notifications = collect([$notifications]);

            $notifyHtml = view('partials.notification', ['notifications' => $notifications])->render();

            // event(new MyEvent($notify, $video->user_id));
            event(new MyEvent($notifyHtml, $video->user_id));
        }

        return response()->json(['success' => true]);
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        return redirect()->route('web.login');
    }

}