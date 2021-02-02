<?php 
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
   public function index(Request $request){
        return '<script>
        (function() {
          var app = {
            launchApp: function() {
              window.location.replace("/open-app");
              this.timer = setTimeout(this.openWebApp, 100);
            },
        
            openWebApp: function() {
              window.location.replace("market://details?id=com.bidnite.android");
            }
          };
        
          app.launchApp();
        })();
        </script>';
    } 
    
    
    public function showVideo(Request $request){
        
        $video_de=base64_decode($request->video_id);
       
        $video_id=str_ireplace("yfmtythd84n4h","",$video_de);
		    $video_res = DB::table('videos as v')
                        ->select(DB::raw('v.video,v.thumb,v.user_id,u.username,v.title,v.description'))
                        ->leftJoin('users as u', 'u.user_id', '=', 'v.user_id')
                        ->where('video_id',$video_id)
						   ->first();
						   if($video_res){
						       $video=$video_res->video;
    						   $thumb=$video_res->thumb; 
    						   $user_id=$video_res->user_id; 
    						   $title=$video_res->title; 
    						   $description=$video_res->description;
    						   $username=$video_res->username; 
                  return view('show-video',compact('video','thumb','user_id','username','description','title'));
						   }
						   
  }

    public function front() {
      $videos = DB :: table('videos')
                ->select('user_id', 'video_id', 'video', 'thumb', 'gif', 'total_likes', 'total_comments', 'total_views')
                ->orderBy('total_likes')->orderBy('total_views')
                ->where(['active' => 1, 'enabled' => 1])
                ->where('video_id', '>=', 145)
                ->get()->take(5);
                // dd($videos);
      return view('home', compact('videos'));
    }

    public function videoInfo($videoId) {

      $video = DB::table('videos as v')
                ->join('users as u', 'u.user_id', 'v.user_id')
                ->select('v.total_views', 'u.username')
                ->where('v.video_id', $videoId)
                ->first();
      $comments = DB::table('comments as c')
                  ->join('users as u', 'u.user_id', 'c.user_id')
                  ->select('u.username', 'c.comment_id', 'c.comment')
                  ->where(['c.active' => 1, 'c.video_id' => $videoId])
                  ->get();
      $comment_html = view('partials.comments', ['comments' => $comments])->render();
      
      return response()->json(['success' => true, 'video' => $video, 'comment_html' => $comment_html]);
    }

    public function videoViewed($videoId) {

      DB::table('videos')
          ->increment('total_views');

      //change the user_id as per the logged in user via the auth()->guard() or not logged in user set to 0
      $uniqueViewExists = DB::table('video_views')
                          ->where(['user_id' => 1, 'video_id' => $videoId, 'viewed_on' => date('Y-m-d')])
                          ->exists();
      
      //set unique id as per cookie/loggedin user
      if (!$uniqueViewExists) {
        DB::table('video_views')
            ->insert(['user_id' => 1, 'video_id' => $videoId, 'viewed_on' => date('Y-m-d'), 'unique_id' => 1]);
      }

      return response()->json(['success' => true]);

    }
    
}