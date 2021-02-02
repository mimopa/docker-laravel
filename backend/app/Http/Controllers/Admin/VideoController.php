<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Storage; 
use App\Helpers\Common\Functions;
use Mail;
use FFMpeg\Format\Video\X264;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Support\ServiceProvider;
use App\Jobs\ConvertVideoForStreaming;
use ProtoneMedia\LaravelFFMpeg\Filesystem\Media;
use FFMpeg as FFMpeg1;
use FFProbe as FFProbe1;
use App\GifCreator;
use FFMpeg\Coordinate\TimeCode;
use Intervention\Image\ImageManagerStatic as Image;
use File;
use Illuminate\Filesystem\Filesystem;

class VideoController extends Controller
{   

     var $column_order = array(null,null,'username', 'title', 'thumb', 'video'); //set column field database for datatable orderable

    var $column_search = array('u.username','v.title','v.video'); //set column field database for datatable searchable

    var $order = array('v.video_id' => 'asc'); // default order

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view("admin.videos");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $action = 'add';
        $users = DB::table('users')
                        ->select(DB::raw('user_id,username'))
                        ->where('active',1)
                        ->where('deleted',0)
                        ->orderBy('user_id','ASC')
                        ->get();
         $sounds = DB::table('sounds')
                        ->select(DB::raw('sound_id,title'))
                        ->where('deleted',0)
                        ->where('user_id',0)
                        ->orderBy('title','ASC')
                        ->get();      
        return view('admin.videos-create',compact('action','users','sounds'));
    }

    private function _error_string($errArray)
    {
        $error_string = '';
        foreach ($errArray as $key) {
            $error_string.= $key."\n";
        }
        return $error_string;
    }

private function _form_validation($request){
    //   exit();
        // $validator = Validator::make($request->all(), [ 
        $rules = ['user_id'          => 'required',              
            // 'title'          => 'required', 
            // 'video'          => 'mimes:mp4,mov,ogg,qt',              
            'video'          => 'required',              
        ];
        $messages = [ 
            'user_id.required'   => 'User Id  is required.',
            // 'title.required'   => 'Title is required.',
            'video.mimes'   => 'Video Type is invalid',
        ];

        $this->validate($request, $rules, $messages);
        //dd($request->all());
        // if (!$validator->passes()) {
        //     // $file=$request->file('video');
        //     // $ext=$file->getClientOriginalExtension();
        //     return response()->json(['status'=>'error','msg'=> $this->_error_string($validator->errors()->all()) ]);
        // }else{
           
            $functions = new Functions();
            $hashtags='';
            $storage_path=config('app.filesystem_driver');

            if(isset($request->description)) {
                if(stripos($request->description,'#')!==false) {
                $str = $request->description;

                    preg_match_all('/#([^\s]+)/', $str, $matches);
                    
                    $hashtags = implode(',', $matches[1]);
                    
                    //var_dump($hashtags);
                
                }else{
                    $hashtags='';
                }
            }
            
            if($request->id>0){
                if($request->hasFile('video')){
                    $time_folder=time();
                    $videoPath = 'public/videos/'.$request->user_id;
                                   
                    $videoFileName=$this->CleanFileNameMp4($request->file('video')->getClientOriginalName());
                    $request->video->storeAs("public/videos/".$request->user_id, $videoFileName);
                    Storage::setVisibility($videoPath . '/' . $videoFileName, 'public');

                    if($request->sound_id>0){
                        $soundName = DB::table("sounds")
                                    ->select(DB::raw("sound_name,user_id"))
                                    ->where("sound_id",$request->sound_id)
                                    ->first();
                            
                                    if($soundName->user_id>0){
                                        $soundPath = 'public/sounds/'.$soundName->user_id.'/'. $soundName->sound_name;
                                    }else{
                                        $soundPath = 'public/sounds/'. $soundName->sound_name;
                                    }
                    
                        FFMpeg::fromDisk($storage_path)
                            ->open([$videoPath.'/'.$videoFileName, $soundPath])
                            ->export()
                            ->addFormatOutputMapping(new X264('libmp3lame', 'libx264'), Media::make($storage_path, 'public/videos/'.$request->user_id.'/'.$time_folder.'/'.$videoFileName), ['0:v', '1:a'])
                            ->save();

                            Storage::setVisibility($videoPath . '/' . $time_folder.'/'.$videoFileName, 'public');
                    }else{
                        
                    $format = new X264('aac', 'libx264');
					
					FFMpeg::fromDisk($storage_path)
							->open($videoPath.'/'.$videoFileName)
							->export()
							->toDisk($storage_path)
							->inFormat($format)
							//->inFormat(new \FFMpeg\Format\Audio\Aac)
							->save('public/sounds/'.$request->user_id.'/'.$time_folder.'.aac');
                        
                        Storage::setVisibility($videoPath . '/' . $time_folder.'.aac', 'public');
						$audio_media = FFMpeg::open('public/sounds/'.$request->user_id.'/'.$time_folder.'.aac');

						$audio_duration = $audio_media->getDurationInSeconds();
						$audioData = array(
							'user_id' => $request->user_id,
							'cat_id' => 0,
							'title' => $request->user_id.'_'.$time_folder,
							'sound_name' => $time_folder.'.aac',
							'tags'     => $hashtags,
							'duration' =>$audio_duration,
							'created_at' => date('Y-m-d H:i:s')
						); 
						
                        DB::table('sounds')->insert($audioData);
                        
                        $videoFileName=$request->file('video')->getClientOriginalName();
                        $request->video->storeAs("public/videos/".$request->user_id.'/'.$time_folder, $videoFileName);
                        Storage::setVisibility($videoPath . '/' . $time_folder.'/'.$videoFileName, 'public');
                    }
                    
                    $file_path= "public/videos/".$request->user_id.'/'. $time_folder.'/'.$videoFileName;
                    // $c_path=  $this->getCleanFileName($time_folder.'/master.m3u8');
                
                    
                    FFMpeg::fromDisk($storage_path)
                            ->open($videoPath.'/'.$videoFileName)
                            ->getFrameFromSeconds(0)
                            ->export()
                            ->toDisk($storage_path)
                            ->save('public/videos/'.$request->user_id.'/thumb/'.$time_folder.'.jpg');
                    Storage::setVisibility($videoPath . '/thumb/' . $time_folder.'.jpg', 'public');
                     $v_path=asset(Storage::url("public/videos/".$request->user_id.'/'.$videoFileName));
                    
                     $gif_path=asset(Storage::url("public/videos/".$request->user_id."/gif"));
                     $gif_storage_path=$gif_path.'/'.$time_folder.'.gif';
            
                    $media = FFMpeg::open('public/videos/'.$request->user_id.'/'.$videoFileName);
                    $duration = $media->getDurationInSeconds();
                    
                    // $ffmpeg = FFMpeg1\FFMpeg::create();
                    // $video = $ffmpeg->open($v_path);
    
                    // // This array holds our "points" that we are going to extract from the
                    // // video. Each one represents a percentage into the video we will go in
                    // // extracitng a frame. 0%, 10%, 20% ..
                    // $points = range(0,100,50);
                    // //dd($points);
                    // $temp = asset(Storage::url("thumb"));
                    // // This will hold our finished frames.
                    // $frames = [];
    
                    // foreach ($points as $point) {
    
                    //     // Point is a percent, so get the actual seconds into the video.
                    //     $time_secs = floor($duration * ($point / 100));
    
                    //     // Created a var to hold the point filename.
                    //     $point_file = "$temp/$point.jpg";
    
                    //     // Extract the frame.
                    //     $frame = $video->frame(TimeCode::fromSeconds($time_secs));
                    //     $frame->save($point_file);
    
                    //     // If the frame was successfully extracted, resize it down to
                    //     // 320x200 keeping aspect ratio.
                    //     if (file_exists($point_file)) {
                    //         $img = Image::make($point_file)->resize(400, 300, function ($constraint) {
                    //             $constraint->aspectRatio();
                    //             $constraint->upsize();
                    //         });
    
                    //         $img->save($point_file, 40);
                    //         $img->destroy();
                    //     }
    
                    //     // If the resize was successful, add it to the frames array.
                    //     if (file_exists($point_file)) {
                    //         $frames[] = $point_file;
                    //     }
                    // }
    
                    // // If we have frames that were successfully extracted.
                    // if (!empty($frames)) {
    
                    //     // We show each frame for 100 ms.
                    //     $durations = array_fill(0, count($frames), 25);
    
                    //     // Create a new GIF and save it.
                    //     $gc = new GifCreator();
                    //     $gc->create($frames, $durations, 0);
                    //     file_put_contents($gif_storage_path, $gc->getGif());
    
                    //     // Remove all the temporary frames.
                    //     foreach ($frames as $file) {
                    //         Storage::delete($file);
                    //     }
                    // }

                    Storage::delete("public/videos/" . $request->user_id.'/'.$videoFileName);
                    // unlink(storage_path()."/app/public/videos/".$request->user_id.'/'.$videoFileName);
                    if($request->sound_id==0 || $request->sound_id==null){
                        $sound_id=0;
                    }else{
                        $sound_id=$request->sound_id;
                    }


                    $data =array(
                        'user_id'       => $request->user_id,
                        'video'         => $time_folder.'/'.$videoFileName,
                        'thumb'         => $time_folder.'.jpg',
                        'gif'         => $time_folder.'.gif',
                        'title' => ($request->title==null)?'' : $request->title,
                        'description' => ($request->description==null)? '' : $request->description,
                        'duration'    => $duration,
                        'sound_id'     => $sound_id,
                        'tags'      => $hashtags,
                        'enabled' => 1,
                        // 'master_video' => $c_path,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    );
                    DB::table('videos')->where('video_id',$id)->update($data); 
                    //$v_id=DB::table('videos')->insertGetId($data);  
                    // $video = array(
                    //     'disk'          => $storage_path,
                    //     'original_name' => $request->video->getClientOriginalName(),
                    //     'path'          => $file_path,
                    //     // 'c_path'        => $c_path,
                    //     'title'         => $request->title,
                    //     'video_id'      => $id,
                    //     'user_id'       => $request->user_id
                    // );
                    
                    // ConvertVideoForStreaming::dispatch($video);
    
                    return $data;
                }else{
                    // $fileName=$request->old_video;
            
                    // $thumb_name=$request->old_thumb;
                    // $gif_name=$request->old_gif;
                    // $duration=$request->old_duration;
                    if($request->sound_id==0 || $request->sound_id==null){
                        $sound_id=0;
                    }else{
                        $sound_id=$request->sound_id;
                    }
                    $data =array(
                        
                        'title' => ($request->title==null)?'' : $request->title,
                        'description' => ($request->description==null)? '' : $request->description,
                        'sound_id'     => $sound_id,
                        'tags'      => $hashtags,
                        'enabled' => 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    );
                   return $data; 
                }
                
            }else{
      
                if($request->hasFile('video')){
                    $time_folder=time();
                    $videoPath = 'public/videos/'.$request->user_id;
                   
    
                    $videoFileName=$this->CleanFileNameMp4($request->file('video')->getClientOriginalName());
                    // dd($videoFileName);
                    $request->video->storeAs("public/videos/".$request->user_id, $videoFileName);
                    Storage::setVisibility($videoPath . '/' . $videoFileName, 'public');

                    if($request->sound_id>0){
                        $soundName = DB::table("sounds")
                                    ->select(DB::raw("sound_name,user_id"))
                                    ->where("sound_id",$request->sound_id)
                                    ->first();
                                    if($soundName->user_id>0){
                                        $soundPath = 'public/sounds/'.$soundName->user_id.'/'. $soundName->sound_name;
                                    }else{
                                        $soundPath = 'public/sounds/'. $soundName->sound_name;
                                    }
                                    // dd(Storage::url($soundPath));
                        FFMpeg::fromDisk($storage_path)
                        ->open([$videoPath.'/'.$videoFileName, $soundPath])
                        ->export()
                        ->addFormatOutputMapping(new X264('libmp3lame', 'libx264'), Media::make($storage_path, 'public/videos/'.$request->user_id.'/'.$time_folder.'/'.$videoFileName), ['0:v', '1:a'])
                        ->save();
                        Storage::setVisibility($videoPath . '/' . $time_folder.'/'.$videoFileName, 'public');
                        // dd(Storage::url($videoPath . '/' . $time_folder.'/'.$videoFileName));

                    }else{

                        $format = new X264('aac', 'libx264');
					
                        // FFMpeg::fromDisk($storage_path)
                        //         ->open($videoPath.'/'.$videoFileName)
                        //         ->export()
                        //         ->toDisk($storage_path)
                        //         ->inFormat($format)
                        //         //->inFormat(new \FFMpeg\Format\Audio\Aac)
                        //         ->save('public/sounds/'.$request->user_id.'/'.$time_folder.'.aac');
                        //     Storage::setVisibility('public/sounds/'.$request->user_id.'/'.$time_folder.'.aac', 'public');
                        //     // dd('public/sounds/'.$request->user_id.'/'.$time_folder.'.aac');
                        //     $audio_media = FFMpeg::open('public/sounds/'.$request->user_id.'/'.$time_folder.'.aac');

                        //     $audio_duration = $audio_media->getDurationInSeconds();
                        //     $audioData = array(
                        //         'user_id' => $request->user_id,
                        //         'cat_id' => 0,
                        //         'title' => $request->user_id.'_'.$time_folder,
                        //         'sound_name' => $time_folder.'.aac',
                        //         'tags'     => $hashtags,
                        //         'duration' =>$audio_duration,
                        //         'created_at' => date('Y-m-d H:i:s')
                        //     ); 
                            
						// DB::table('sounds')->insert($audioData);
                        $videoFileName=$this->CleanFileNameMp4($request->file('video')->getClientOriginalName());
                        $request->video->storeAs("public/videos/".$request->user_id.'/'.$time_folder, $videoFileName);
                        Storage::setVisibility($videoPath . '/' . $time_folder.'/'.$videoFileName, 'public');
                    }
                    
                    $file_path= "public/videos/".$request->user_id.'/'. $time_folder.'/'.$videoFileName;
                    // $c_path=  $this->getCleanFileName($time_folder.'/master.m3u8');
                // echo asset(Storage::url('public/videos/"'.$request->user_id.'"/gif"'))."<br />";
                //     dd(storage_path("app/public/videos/".$request->user_id."/gif"));
                    FFMpeg::fromDisk($storage_path)
                            ->open($videoPath.'/'. $time_folder.'/'.$videoFileName)
                            ->getFrameFromSeconds(0)
                            ->export()
                            ->toDisk($storage_path)
                            ->save('public/videos/'.$request->user_id.'/thumb/'.$time_folder.'.jpg');
                    Storage::setVisibility($videoPath . '/thumb/'.$time_folder.'.jpg', 'public');
                     $v_path=asset(Storage::url($videoPath.'/'. $time_folder.'/'.$videoFileName));
                    // dd($v_path);
                     $gif_path=asset(Storage::url("public/videos/".$request->user_id."/gif"));
                     $gif_storage_path=$gif_path.'/'.$time_folder.'.gif';
            
                    $media = FFMpeg::open($videoPath.'/'. $time_folder.'/'.$videoFileName);
                    $duration = $media->getDurationInSeconds();
                    
                    // $ffmpeg = FFMpeg1\FFMpeg::create();
                    
                    // $video = $ffmpeg->open($v_path);
    
                    // // This array holds our "points" that we are going to extract from the
                    // // video. Each one represents a percentage into the video we will go in
                    // // extracitng a frame. 0%, 10%, 20% ..
                    // $points = range(0,100,50);
                    // //dd($points);
                    // $temp = asset(Storage::url("thumb"));
                    // // This will hold our finished frames.
                    // $frames = [];
    
                    // foreach ($points as $point) {
    
                    //     // Point is a percent, so get the actual seconds into the video.
                    //     $time_secs = floor($duration * ($point / 100));
    
                    //     // Created a var to hold the point filename.
                    //     $point_file = "$temp/$point.jpg";
    
                    //     // Extract the frame.
                    //     $frame = $video->frame(TimeCode::fromSeconds($time_secs));
                    //     $frame->save($point_file);
    
                    //     // If the frame was successfully extracted, resize it down to
                    //     // 320x200 keeping aspect ratio.
                    //     if (file_exists($point_file)) {
                    //         $img = Image::make($point_file)->resize(400, 300, function ($constraint) {
                    //             $constraint->aspectRatio();
                    //             $constraint->upsize();
                    //         });
    
                    //         $img->save($point_file, 40);
                    //         $img->destroy();
                    //     }
    
                    //     // If the resize was successful, add it to the frames array.
                    //     if (file_exists($point_file)) {
                    //         $frames[] = $point_file;
                    //     }
                    // }
                    // // If we have frames that were successfully extracted.
                    // if (!empty($frames)) {
    
                    //     // We show each frame for 100 ms.
                    //     $durations = array_fill(0, count($frames), 25);
    
                    //     // Create a new GIF and save it.
                    //     $gc = new GifCreator();
                    //     $gc->create($frames, $durations, 0);
                    //     file_put_contents($gif_storage_path, $gc->getGif());
                    //     // Remove all the temporary frames.
                    //     foreach ($frames as $file) {
                    //         Storage::delete($file);
                    //     }
                    // }
                    Storage::delete("public/videos/" . $request->user_id.'/'.$videoFileName);
                    // unlink(storage_path()."/app/public/videos/".$request->user_id.'/'.$videoFileName);

                    if($request->sound_id==0 || $request->sound_id==null){
                        $sound_id=0;
                    }else{
                        $sound_id=$request->sound_id;
                    }


                    $data =array(
                        'user_id'       => $request->user_id,
                        'video'         => $time_folder.'/'.$videoFileName,
                        'thumb'         => $time_folder.'.jpg',
                        'gif'         => $time_folder.'.gif',
                        'title' => ($request->title==null)?'' : $request->title,
                        'description' => ($request->description==null)? '' : $request->description,
                        'duration'    => $duration,
                        'sound_id'     => $sound_id,
                        // 'master_video' => $c_path,
                        'tags'      => $hashtags,
                        'enabled' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                        'active' => 1,
                        'deleted' => 0
                    );
                   
                    $v_id=DB::table('videos')->insertGetId($data);  
                    // $video = array(
                    //     'disk'          => $storage_path,
                    //     'original_name' => $request->video->getClientOriginalName(),
                    //     'path'          => $file_path,
                    //     'c_path'        => $c_path,
                    //     'title'         => $request->title,
                    //     'video_id'      => $v_id,
                    //     'user_id'       => $request->user_id
                    // );
                    
                    //ConvertVideoForStreaming::dispatch($video);
    
                    return $data;

                }else{
                    redirect( config('app.admin_url').'/videos')->with('error','You can\'t leave Video field empty');
                }
            }
        // }
       
    }
    
      private function getCleanFileName($filename){
        return preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename) . '.m3u8';
    }

    private function CleanFileNameMp4($filename){
        $fname= preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename) . '.mp4';
        return str_replace(' ', '-', $fname);
    }
    // private function _form_validation($request){
      
    //     $validator = Validator::make($request->all(), [ 
    //         'user_id'          => 'required',              
    //         'title'          => 'required', 
    //         'video'          => 'mimes:mp4,mov,ogg,qt',              
    //     ],[ 
    //         'user_id.required'   => 'User Id  is required.',
    //         'title.required'   => 'Title is required.',
    //         'video.mimes'   => 'Video Type is invalid',
    //     ]);
    //     //dd($request->all());
    //     if (!$validator->passes()) {
    //         // $file=$request->file('video');
    //         // $ext=$file->getClientOriginalExtension();
    //         return response()->json(['status'=>'error','msg'=> $this->_error_string($validator->errors()->all()) ]);
    //     }else{
           
    //         $functions = new Functions();
    //         if($request->id>0 || isset($request->id)){
    //             if($request->hasFile('video')){
    //                 $path = "public/videos/".$request->user_id;
              
    //                 $Filevideo = $request->file('video');
    //                 //$t_path = "public/videos/".$request->user_id."/thumb";        
    //                 $videoname = date('Ymdhis').'_'.$Filevideo->getClientOriginalName();
                
    //                 $filenametostore = $request->file('video')->storeAs($path,$videoname);  
                           
    //                 Storage::setVisibility($filenametostore, 'public');
    //                 $fileArray = explode('/',$filenametostore);  
                    
    //                 $fileName = array_pop($fileArray); 
    //                 $file_path = url("storage/videos/".$request->user_id."/".$fileName);
    //                 $thumb= explode(".",$fileName);
    //                 $thumb_name=$thumb[0].".jpg";
    //                 $gif_name=$thumb[0].".gif";
                    
    //                 $thumb_path=storage_path("app/public/videos/".$request->user_id."/thumb");
    //                 $thumb_storage_path=$thumb_path.'/'.$thumb_name;
                    
    //                 $ffmpeg = FFMpeg\FFMpeg::create();
    //                 $video = $ffmpeg->open($file_path);
    //                 $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(0));
    //                 $frame->save($thumb_storage_path);
    
    //                 $gif_path=storage_path("app/public/videos/".$request->user_id."/gif");
    //                 $gif_storage_path=$gif_path.'/'.$gif_name;
    
    //                 $ffprobe = FFMpeg\FFProbe::create();
    //                 $duration = (int) $ffprobe->format($file_path)->get('duration');
    
    //                 // The gif will have the same dimension. You can change that of course if needed.
    //                 $dimensions = $ffprobe->streams($file_path)->videos()->first()->getDimensions();
                
    //                 $gifPath = $gif_storage_path;
                    
    //                 // Transform
    //                 //$ffmpeg = FFMpeg\FFMpeg::create();
    //                 $ffmpegVideo = $ffmpeg->open($file_path);
                    
    //                 $ffmpegVideo->gif(FFMpeg\Coordinate\TimeCode::fromSeconds(0), $dimensions, 3)->save($gifPath);
                
    //             }else{
    //                 $fileName=$request->old_video;
            
    //                 $thumb_name=$request->old_thumb;
    //                 $gif_name=$request->old_gif;
    //                 $duration=$request->old_duration;
    //             }
                
    //         }else{
        
    //             if($request->hasFile('video')){
    //                 $path = "public/videos/".$request->user_id;
              
    //                 $Filevideo = $request->file('video');
    //                 //$t_path = "public/videos/".$request->user_id."/thumb";        
    //                 $videoname = date('Ymdhis').'_'.$Filevideo->getClientOriginalName();
                
    //                 $filenametostore = $request->file('video')->storeAs($path,$videoname);  
                           
    //                 Storage::setVisibility($filenametostore, 'public');
    //                 $fileArray = explode('/',$filenametostore);  
                    
    //                 $fileName = array_pop($fileArray); 
    //                 $file_path = url("storage/videos/".$request->user_id."/".$fileName);
    //                 $thumb= explode(".",$fileName);
    //                 $thumb_name=$thumb[0].".jpg";
    //                 $gif_name=$thumb[0].".gif";
                    
    //                 $thumb_path=storage_path("app/public/videos/".$request->user_id."/thumb");
    //                 $thumb_storage_path=$thumb_path.'/'.$thumb_name;
                    
    //                 $ffmpeg = FFMpeg\FFMpeg::create();
    //                 $video = $ffmpeg->open($file_path);
    //                 $frame = $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(0));
    //                 $frame->save($thumb_storage_path);
    
    //                 $gif_path=storage_path("app/public/videos/".$request->user_id."/gif");
    //                 $gif_storage_path=$gif_path.'/'.$gif_name;
    
    //                 $ffprobe = FFMpeg\FFProbe::create();
    //                 $duration = (int) $ffprobe->format($file_path)->get('duration');
    
    //                 // The gif will have the same dimension. You can change that of course if needed.
    //                 $dimensions = $ffprobe->streams($file_path)->videos()->first()->getDimensions();
                
    //                 $gifPath = $gif_storage_path;
                    
    //                 // Transform
    //                 //$ffmpeg = FFMpeg\FFMpeg::create();
    //                 $ffmpegVideo = $ffmpeg->open($file_path);
                    
    //                 $ffmpegVideo->gif(FFMpeg\Coordinate\TimeCode::fromSeconds(0), $dimensions, 3)->save($gifPath);
    //             }else{
    //                 redirect( config('app.admin_url').'/videos')->with('error','You can\'t leave Video field empty');
    //             }
    //         }
               

    //             if($request->title==''){
				// 		$title='';
				// 	}else{
				// 		$title=$request->title;
				// 	}
    //             $postData =array(
    //                 'user_id'    => $request->user_id,
    //                 'video'      => $fileName,
    //                 'thumb'      => $thumb_name,
    //                 'gif'      => $gif_name,
    //                 'title'      => $request->title,
    //                 'duration' => $duration,
    //                 'created_at' => date('Y-m-d H:i:s')                                   
    //             );
                  
    //             return $postData;
    //     }
       
    //     //return $postData;
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $this->_form_validation($request);
        //DB::table('videos')->insert($data);
        return redirect( config('app.admin_url').'/videos')->with('success','Video submitted successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {   
        $users = DB::table('users')
                        ->select(DB::raw('user_id,username'))
                        ->where('active',1)
                        ->where('deleted',0)
                        ->orderBy('user_id','ASC')
                        ->get();
        $sounds = DB::table('sounds')
                        ->select(DB::raw('sound_id,title'))
                        ->where('deleted',0)
                        ->orderBy('title','ASC')
                        ->get();   
        return view("admin.categories",compact('users','sounds'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $action = 'edit';
        $users = DB::table('users')
                        ->select(DB::raw('user_id,username'))
                        ->where('active',1)
                        ->where('deleted',0)
                        ->orderBy('user_id','ASC')
                        ->get();
        $sounds = DB::table('sounds')
                        ->select(DB::raw('sound_id,title'))
                        ->where('deleted',0)
                        ->orderBy('title','ASC')
                        ->get();   
        $video = DB::table('videos')->select(DB::raw("*"))->where('video_id','=',$id)->first();
       // dd( $video);
        return view('admin.videos-create',compact('video','id','action','users','sounds'));
    }

  
    public function view($id)
    {
        $action = 'view';
        $users = DB::table('users')
            ->select(DB::raw('user_id,username'))
            ->where('active',1)
            ->where('deleted',0)
            ->orderBy('user_id','ASC')
            ->get();
         $sounds = DB::table('sounds')
                        ->select(DB::raw('sound_id,title'))
                        ->where('deleted',0)
                        ->orderBy('title','ASC')
                        ->get();   
        $video = DB::table('videos')->select(DB::raw("*"))->where('video_id','=',$id)->first();
    
        return view('admin.videos-create',compact('video','id','action','users','sounds'));
    }

   
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $this->_form_validation($request);
        //DB::table('videos')->where('video_id',$id)->update($data);
        return redirect( config('app.admin_url').'/videos')->with('success','Video updated successfully');
    }

  
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   
     public function serverProcessing(Request $request)
    {
        $currentPath = url(config('app.admin_url')).'/videos/';

        $list = $this->get_datatables($request);
        $data = array();
        $no = $request->start;
        foreach ($list as $category) {
            $no++;
            $row = array();
            //<a class="edit" href="'.$currentPath.$category->video_id.'/edit"><i class="fa fa-edit"></i></a>;
            $row[] = '<a class="view" href="'.$currentPath.$category->video_id.'/'.'view"><i class="fa fa-search"></i></a><a class="delete deleteSelSingle" style="cursor:pointer;" data-val="'.$category->video_id.'"><i class="fa fa-trash"></i></a>';
            $row[] = '<div class="align-center"><input id="cb'.$no.'" name="key_m[]" class="delete_box blue-check" type="checkbox" data-val="'.$category->video_id.'"><label for="cb'.$no.'"></label></div>';
            $row[] = $category->username;
            $row[] = $category->title;
            // if(file_exists('storage/videos/'.$category->user_id.'/'.$category->video)){
            // $exists = Storage::disk(config('app.filesystem_driver'))->exists('public/videos/'.$category->user_id.'/'.$category->video);
            // if($exists){ 
                $html="<i class='fa fa-play-circle-o video_play' aria-hidden='true'></i>";
            // }else{
            //     $html='';
            // }
            $row[] = "<div style='position:relative;text-align:center;'>".$html."<img src=".asset(Storage::url('public/videos/'.$category->user_id.'/thumb/'.$category->thumb))." height=200 data-toggle='modal' data-target='#homeVideo' class='video_thumb' id='".asset(Storage::url('public/videos/'.$category->user_id.'/'.$category->video))."'/></div>";
            if($category->active==1){
                $active="checked";
            }else{
                $active="";
            }
            $row[] = '<input type="checkbox" class="active_toggle" '.$active.' data-id="'.$category->video_id.'" data-toggle="toggle" data-on="Yes" data-off="No" data-onstyle="success" data-offstyle="danger" >';
            if($category->flag==1){
                $checked="checked";
            }else{
                $checked="";
            }
            $row[] = '<input type="checkbox" class="flaged_toggle" '.$checked.' data-id="'.$category->video_id.'" data-toggle="toggle" data-on="Yes" data-off="No" data-onstyle="success" data-offstyle="danger" >';
            $data[] = $row;
        }

        $output = array(
            "draw" => $request->draw,
            "recordsTotal" => $this->count_all($request),
            "recordsFiltered" => $this->count_filtered($request),
            "data" => $data,
        );
        echo json_encode($output);
    }

	private function _get_datatables_query($request)
    {            
        $keyword = $request->search['value'];
        $order = $request->order;
        $candidateRS = DB::table('videos as v')
                        ->leftJoin('users as u' , 'u.user_id','=','v.user_id')
                       ->select(DB::raw("v.*,u.username"));
                        
        $strWhere = " v.deleted=0 ";
        $strWhereOr = "";
        $i = 0;

        foreach ($this->column_search as $item) // loop column
        {
            if($keyword) // if datatable send POST for search{
            	$strWhereOr = $strWhereOr." $item like '%".$keyword."%' or ";
                //$candidateRS = $candidateRS->orWhere($item, 'like', '%' . $keyword . '%') ;
        }
        $strWhereOr = trim($strWhereOr, "or ");
        if($strWhereOr!=""){
	        $candidateRS = $candidateRS->whereRaw(DB::raw($strWhere." and (".$strWhereOr.")"));
	    }else{
			$candidateRS = $candidateRS->whereRaw(DB::raw($strWhere	));
		}
        

        if(isset($order)) // here order processing
        {
            $candidateRS = $candidateRS->orderBy($this->column_order[$request->order['0']['column']], $request->order['0']['dir']);
        } 
        else if(isset($this->order))
        {
            $orderby = $this->order;
            $candidateRS = $candidateRS->orderBy(key($orderby),$orderby[key($orderby)]);
        }
       
        return $candidateRS;
    }

    function get_datatables($request)
    {
        $candidateRS = $this->_get_datatables_query($request);
        if($request->length != -1){
            $candidateRS = $candidateRS->limit($request->length);
            if($request->start != -1){
                $candidateRS = $candidateRS->offset($request->start);
            }
        }
        
        $candidates = $candidateRS->get();
        return $candidates;
    }

    function count_filtered($request)
    {
        $candidateRS = $this->_get_datatables_query($request);
        return $candidateRS->count();
    }

    public function count_all($request)
    {
        $candidateRS = DB::table('videos')->select(DB::raw("count(*) as total"))->where('active',1)->first();
        return $candidateRS->total;
    }

    public function delete(Request $request){
        $rec_exists = array();
        $del_error = '';
        $ids = explode(',',$request->ids);
        foreach ($ids as $id) {
             $videoRes = DB::table('videos')->select(DB::raw("video,user_id"))->where('video_id',$id)->first();
            $video_name=explode('/',$videoRes->video);
            $folder_name=$videoRes->user_id.'/'.$video_name[0];
            $f_name=explode('.',$video_name[0]);
            $thumb_name=$videoRes->user_id.'/thumb/'.$f_name[0].'.jpg';
            $gif_name=$videoRes->user_id.'/gif/'.$f_name[0].'.gif';
            
            Storage::deleteDirectory("public/videos/".$folder_name);
            Storage::Delete("public/videos/".$thumb_name);
            Storage::Delete("public/videos/".$gif_name);
            DB::table('videos')->where('video_id', $id)->delete();
        }
        
        if($del_error == 'error'){
            $request->session()->put('error',$msg );
            return response()->json(['status' => 'error',"rec_exists"=>$rec_exists]);
        }else{
            if( count($ids) > 1){
                $msg = "Video deleted successfully";
            }else{
                $msg = "Video deleted successfully";
            }
            $request->session()->put('success', $msg);
            return response()->json(['status' => 'success',"rec_exists"=>$rec_exists]);
        }
        return redirect()->back();
    }

    public function copyContent($id)
    {
        $action = 'copy';
        $parent_categories = DB::table('categories')
            ->select(DB::raw('cat_id,cat_name,parent_id'))
            ->where('parent_id',0)
            ->orderBy('cat_id','ASC')
            ->get();
        $categories = DB::table('categories')
                ->select(DB::raw('cat_id,cat_name,parent_id'))
                ->where('parent_id','!=',0)
                ->orderBy('cat_id','ASC')
                ->get();
        $sound = DB::table('sounds')->select(DB::raw("*"))->where('sound_id','=',$id)->first();
        return view('admin.sounds-create',compact('id','sound','action','parent_categories','categories'));
    }
    public function flaged_video(Request $request){
        // dd($request->all());
        DB::table('videos')->where('video_id', $request->id)->update(['flag'=>$request->status,'enabled'=>$request->enabled]);
        if($request->status=='1'){
            return 'Video Flaged';
        }else{
            return 'Video Unflaged';
        }
    }
      
    public function active_video(Request $request){
        DB::table('videos')->where('video_id', $request->id)->update(['active'=>$request->active]);
        if($request->active=='1'){
            return 'Video Activated Successfully';
        }else{
            return 'Video Inactivated Successfully!';
        }
    }
    
}
