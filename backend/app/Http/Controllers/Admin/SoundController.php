<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Storage; 
use Mail;
use File;
// use FFMpeg\FFProbe;
// use FFMpeg;
use FFMpeg\Format\Video\X264;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Support\ServiceProvider;
use Owenoj\LaravelGetId3\GetId3;
class SoundController extends Controller
{   
    var $column_order = array(null,null, 'sound_id', 'title','sound_name','album','artist','active','duration'); //set column field database for datatable orderable

    var $column_search = array('title','s.sound_name','s.album','s.artist','s.active','s.duration'); //set column field database for datatable searchable

    var $order = array('s.sound_id' => 'desc'); // default order

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         $categories=DB::table('categories')
                    ->select(DB::raw('cat_id,cat_name,parent_id'))
                    ->orderBy('cat_id','ASC')
                    ->get();   
        return view("admin.sounds",compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
      
        $action = 'add';
        
        $categories = DB::table('categories')
                        ->select(DB::raw('cat_id,cat_name,parent_id'))
                        //->where('parent_id','<>',0)
                        ->orderBy('cat_id','ASC')
                        ->get();   
        if(isset($request->type) && $request->type=='s'){
            return view('admin.sounds-create',compact('action','categories'));
        }else{
            return view('admin.sounds-bulk-create',compact('action','categories'));
        }    
        
    }

    private function _form_validation($request){
     
        $rules = [
            'cat_id' => 'required',
            'sound_name' => 'required_if:id,0',
            'title' => 'required_if:bulk,0|required_unless:id,0',
            
        ];
        $messages = [
            'cat_id.required' => 'You can\'t leave Category name field empty',
            'sound_name.required_if'    => 'You can\'t leave Sound field empty',
            'title.required_if'    => 'You can\'t leave Title field empty',
            'title.required_unless'    => 'You can\'t leave Title field empty',
            
        ]; 
        $this->validate($request,$rules,$messages);
        
        $title="";
        $album="";
        $artist="";
        $storage_path=config('app.filesystem_driver');

        if($request->id>0){

            $file_arr=$request->file('sound_name');
            $ext_arr=array("mp3","mpeg");
            if(isset($request->cat_id)){
                $cat= $request->cat_id;
            }else{
                 $cat=0;
            }
            
            $category="";
            if(is_array($cat)){
                if(count($cat)>0){
                    foreach($cat as $val){
                        $category.=",".$val;
                    }
                    $category=substr($category,1);
                }
            }else{
                $category=$cat;
            }
            if($file_arr!=null){
                for($x=0;$x<count($file_arr);$x++){
                    $file_ext=$file_arr[$x]->getClientOriginalExtension();
                    if(in_array($file_ext,$ext_arr)){
                        $path = 'public/sounds';  
                        $Fileaudio = $file_arr[$x];
                        //$audioname = time().'_'.$Fileaudio->getClientOriginalName();
                         $name=explode('.',$Fileaudio->getClientOriginalName());
                        $time_folder=time();
                        $audioname =$time_folder.'.'.$file_ext;
                        $audiopath =$Fileaudio->storeAs($path, $audioname);
                        $audi_file_path=asset(Storage::url("public/sounds/".$audioname));
                        Storage::setVisibility($path . '/' . $audioname, 'public');
                        // dd($audi_file_path);
                        $audio_file=explode('.',$audioname);
                        $file=$time_folder.'.aac';
                        $file_path=asset(Storage::url("public/sounds/".$file));
                   
                    $format = new X264('aac', 'libx264');
                             
                    
                    FFMpeg::fromDisk($storage_path)
                            ->open('public/sounds/'.$audioname)
                            ->export()
                            ->toDisk($storage_path)
                            ->inFormat($format)
                           ->save('public/sounds/'.$file);
                            Storage::setVisibility($path . '/' . $file, 'public');
                        //    dd($file_path);
                            $media = FFMpeg::open('public/sounds/'.$audioname);
                            $duration = $media->getDurationInSeconds();
    
                            $track = new GetId3($file_arr[$x]);
                      
                            $title=$track->getTitle();
                            $album=$track->getAlbum();
                            $artist=$track->getArtist();
                       
                        Storage::delete("public/sounds/" . $audioname);
                        // unlink(storage_path()."/app/public/sounds/".$audioname); 
                        if(count($file_arr)==1 && $request->bulk==0){
                            $title=$request->title;
                        }
                        $postData = array(
                            'cat_id' => $category,
                            'title' => ($title!=null) ? $title : $name[0],
                            'sound_name' => $file,
                            // 'tags'     => $request->tags,
                            'album' => ($album!=null)? $album : "",
                            'artist' =>($artist!=null) ? $artist : "",
                            'active' => $request->active,
                            'duration' =>$duration,
                            'created_at' => date('Y-m-d H:i:s')
                        );
                        DB::table('sounds')->where('sound_id',$request->id)->update($postData);
                        
                    }else{
                        redirect( config('app.admin_url').'/sounds')->with('error',' Sound Type is not valid.');
                    }
                }
                return redirect( config('app.admin_url').'/sounds')->with('success','Sound updated successfully');
            }else{
                $file=$request->old_sound_name;
                $duration=$request->old_duration;
                $title=$request->title;
                $album=$request->album;
                $artist=$request->artist;
                $postData = array(
                    'cat_id' => $category,
                    'title' => ($title!=null) ? $title : "",
                    'sound_name' => $file,
                    // 'tags'     => $request->tags,
                    'album' => ($album!=null)? $album : "",
                    'artist' =>($artist!=null) ? $artist : "",
                    'active' => $request->active,
                    'duration' =>$duration,
                    'created_at' => date('Y-m-d H:i:s')
                );
                DB::table('sounds')->where('sound_id',$request->id)->update($postData);
                return redirect( config('app.admin_url').'/sounds')->with('success','Sound updated successfully');
            }
        }else{
            $file_arr=$request->file('sound_name');
            $ext_arr=array("mp3","mpeg");           
            $cat= $request->cat_id;
            $category="";
            if(is_array($cat)){
                if(count($cat)>0){
                    foreach($cat as $val){
                        $category.=",".$val;
                    }
                    $category=substr($category,1);
                }
            }else{
                $category=$cat;
            }

            for($x=0;$x<count($file_arr);$x++){
                $file_ext=$file_arr[$x]->getClientOriginalExtension();
              
                if(in_array($file_ext,$ext_arr)){
                    $path = 'public/sounds';    
               
                    $Fileaudio = $file_arr[$x];
                    //$audioname = time().'_'.$Fileaudio->getClientOriginalName();
                     $name=explode('.',$Fileaudio->getClientOriginalName());
                    // dd($name);
                    $time_folder=time();
                    $audioname =$time_folder.'.'.$file_ext;
                    $audiopath =$Fileaudio->storeAs($path, $audioname);
                    Storage::setVisibility($path . '/' . $audioname, 'public');
                    $audi_file_path=asset(Storage::url("public/sounds/".$audioname));

                    $audio_file=explode('.',$audioname);
                    $file=$time_folder.'.aac';
                    $file_path=asset(Storage::url("public/sounds/".$file));
                
                    $format = new X264('aac', 'libx264');
                   
                    FFMpeg::fromDisk($storage_path)
                        ->open('public/sounds/'.$audioname)
                        ->export()
                        ->toDisk($storage_path)
                        ->inFormat($format)
                        ->save('public/sounds/'.$file);
                    Storage::setVisibility($path . '/' . $file, 'public');
                    $media = FFMpeg::open('public/sounds/'.$audioname);

                    $duration = $media->getDurationInSeconds();
                   
                        $track = new GetId3($Fileaudio);
                        //dd($track->getArtist());
                        if(count($file_arr)==1 && $request->bulk==0){
                            $title=$request->title;
                            $album=$request->album;
                            $artist=$request->artist;
                        }else{
                            $title=$name[0];
                            $album=$track->getAlbum();
                            $artist=$track->getArtist();
                        }
                      
                        Storage::delete("public/sounds/" . $audioname);
                    // unlink(storage_path()."/app/public/sounds/".$audioname);
                   
                    
                    $postData = array(
                        'cat_id' => $category,
                        'title' => ($title!=null) ? $title : $name[0],
                        'sound_name' => $file,
                        // 'tags'     => $request->tags,
                        'album' => ($album!=null)? $album : "",
                        'artist' =>($artist!=null) ? $artist : "",
                        'active' => $request->active,
                        'duration' =>$duration,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    DB::table('sounds')->insert($postData);
                   
                }else{
                    redirect( config('app.admin_url').'/sounds')->with('error',' Sound Type is not valid.');
                }
            }
            return redirect( config('app.admin_url').'/sounds')->with('success','Sound submitted successfully');
        }

    }
    
    // private function _form_validation($request){
      
    //     $rules = [
    //         // 'parent_id' => 'required',
    //         'cat_id' => 'required',
    //         // 'title'  => 'required',
    //         'sound_name' => 'mimes:audio/mpeg,mpga,mp3',
            
            
    //     ];
    //     $messages = [
    //         // 'parent_id.required' => 'You can\'t leave category name field empty',
    //         'cat_id.required' => 'You can\'t leave Sub category name field empty',
    //         //  'title.required'    => 'You can\'t leave Title field empty',
    //          'sound_name.mimes'    => 'Sound field type error',
            
    //     ]; 
    //     $this->validate($request,$rules,$messages);
        
    //     if($request->id>0 || isset($request->id)){
    //         if($request->hasFile('sound_name')){
    //             $path = 'public/sounds';    
    //             $Fileaudio = $request->file('sound_name');
    //             $audioname = date('Ymdhis').'_'.$Fileaudio->getClientOriginalName();
    //             $audiopath =$Fileaudio->storeAs($path, $audioname);
    //             $audi_file_path=url("storage/sounds/".$audioname);
                
    //             $audio_file=explode('.',$audioname);
    //             $file=$audio_file[0].'.aac';
    //             $file_path=url("storage/sounds/".$file);
                
    //             $format = new X264('aac', 'libx264');
    //                       //$format->setKiloBitrate(5);
                
    //             FFMpeg::fromDisk('local')
    //                     ->open('public/sounds/'.$audioname)
    //                     ->export()
    //                     ->toDisk('local')
    //                      ->inFormat($format)
    //                     //->inFormat(new \FFMpeg\Format\Audio\Aac)
    //                     ->save('public/sounds/'.$file);

    //                 $media = FFMpeg::open('public/sounds/'.$audioname);
    //                 $duration = $media->getDurationInSeconds();
                    
    //                 $track = new GetId3($request->file('sound_name'));
    //                 $title=$track->getTitle();
    //                 $album=$track->getAlbum();
    //                 $artist=$track->getArtist();
    //             // $ffprobe = FFProbe::create();
    //             // $duration = (int) $ffprobe->format($audi_file_path)->get('duration');
    //             unlink(storage_path()."/app/public/sounds/".$audioname);
    //         }else{
    //             $file=$request->old_sound_name;
    //             $duration=$request->old_duration;
    //             $title=$request->old_title;
    //             $album=$request->old_album;
    //             $artist=$request->old_artist;
    //         }
    //     }else{
    //         if($request->hasFile('sound_name')){
    //             $path = 'public/sounds';    
                
    //             $Fileaudio = $request->file('sound_name');
    //             $audioname = date('Ymdhis').'_'.$Fileaudio->getClientOriginalName();
    //             $audiopath =$Fileaudio->storeAs($path, $audioname);
    //             $audi_file_path=url("storage/sounds/".$audioname);

    //             $audio_file=explode('.',$audioname);
    //             $file=$audio_file[0].'.aac';
    //             $file_path=url("storage/sounds/".$file);
                
    //             $format = new X264('aac', 'libx264');
    //             //$format->setKiloBitrate(80);

    //             FFMpeg::fromDisk('local')
    //                     ->open('public/sounds/'.$audioname)
    //                     ->export()
    //                     ->toDisk('local')
    //                     ->inFormat($format)
    //                     //->inFormat(new \FFMpeg\Format\Audio\Aac)
    //                     ->save('public/sounds/'.$file);
                
    //                 $media = FFMpeg::open('public/sounds/'.$audioname);

    //                 $duration = $media->getDurationInSeconds();
                    
    //                 $track = new GetId3($request->file('sound_name'));
    //                 $title=$track->getTitle();
    //                 $album=$track->getAlbum();
    //                 $artist=$track->getArtist();
    //             // $ffprobe = FFProbe::create();
    //             // $duration = (int) $ffprobe->format($audi_file_path)->get('duration');
    //             unlink(storage_path()."/app/public/sounds/".$audioname);
    //         }else{
    //             redirect( config('app.admin_url').'/sounds')->with('error','You can\'t leave Sound field empty');
    //         }
    //     }

    //     $cat= $request->cat_id;
           
    //     $category="";
    //     if(is_array($cat)){
    //         if(count($cat)>0){
    //             foreach($cat as $val){
    //                 $category.=",".$val;
    //             }
    //             $category=substr($category,1);
    //         }
    //     }else{
    //         $category=$cat;
    //     }

    //     $postData = array(
    //         // 'parent_id' => $request->parent_id,
    //         'cat_id' => $category,
    //         'title' => ($title!=null) ? $title : "",
    //         'sound_name' => $file,
    //         'tags'     => $request->tags,
    //         'album' => ($album!=null)? $album : "",
    //         'artist' =>($artist!=null) ? $artist : "",
    //         'duration' =>$duration,
    //         'created_at' => date('Y-m-d H:i:s')
    //     ); 
    //     return $postData;
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
        //DB::table('sounds')->insert($data);
        return redirect( config('app.admin_url').'/sounds')->with('success','Sound submitted successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {   
        $categories = DB::table('categories')
                    ->select(DB::raw('cat_id,category'))
                    ->orderBy('cat_id','DESC')
                    ->orderBy('rank','DESC')
                    ->get();
       
        return view("admin.categories",compact('categories','type'));
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
        $categories = DB::table('categories')
                        ->select(DB::raw('cat_id,cat_name,parent_id'))
                        //->where('parent_id','!=',0)
                        ->orderBy('cat_id','ASC')
                        ->get();
        $sound = DB::table('sounds')->select(DB::raw("*"))->where('sound_id','=',$id)->first();
        return view('admin.sounds-edit',compact('sound','id','action','categories'));
    }

    public function select_cat(Request $request){
        $parent_id=$request->main_cat;
        $sub_cat = DB::table('categories')
                        ->select(DB::raw('cat_id,cat_name'))
                        ->where('parent_id',$parent_id)
                        ->orderBy('cat_id','ASC')
                        ->get();
        $html="";
        $html.="<option value='0'>---Select---</option>";
        foreach($sub_cat as $val){
            $html.="<option value='".$val->cat_id."'>".$val->cat_name."</option>";
        }
        echo $html;
    }
    public function view($id)
    {
        $action = 'view';
      
        $categories = DB::table('categories')
                        ->select(DB::raw('cat_id,cat_name,parent_id'))
                        //->where('parent_id','!=',0)
                        ->orderBy('cat_id','ASC')
                        ->get();
        $sound = DB::table('sounds')->select(DB::raw("*"))->where('sound_id','=',$id)->first();
        return view('admin.sounds-edit',compact('sound','id','action','categories'));
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
        //DB::table('sounds')->where('sound_id',$id)->update($data);
        return redirect( config('app.admin_url').'/sounds')->with('success','Sound updated successfully');
    }

  
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   
     public function serverProcessing(Request $request)
    {
        $currentPath = url(config('app.admin_url')).'/sounds/';
        $list = $this->get_datatables($request);
        $data = array();
        $no = $request->start;
        foreach ($list as $category) {
            $no++;
            $row = array();
            // $row[] = '<a class="view" href="'.$currentPath.$category->sound_id.'/'.'view"><i class="fa fa-search"></i></a><a class="edit" href="'.$currentPath.$category->sound_id.'/edit"><i class="fa fa-edit"></i></a><a class="delete deleteSelSingle" style="cursor:pointer;" data-val="'.$category->sound_id.'"><i class="fa fa-trash"></i></a>';
            $row[] = '<a class="view" href="'.$currentPath.$category->sound_id.'/'.'view"><i class="fa fa-search"></i></a><a class="edit" href="'.$currentPath.$category->sound_id.'/edit"><i class="fa fa-edit"></i></a>';
            $row[] = '<div class="align-center"><input id="cb'.$no.'" name="key_m[]" class="delete_box blue-check" type="checkbox" data-val="'.$category->sound_id.'"><label for="cb'.$no.'"></label></div>';
            $row[] = $category->sound_id;
            $row[] = $category->title;
            $row[] = $category->cat_name;
            $row[] = '<div class="audio_file_'.$category->sound_id.'" onClick=audio_file('.$category->sound_id.')><i class="fa fa-play"></i></div>';
            
            // $row[] = $category->tags;
            $row[] = $category->album;
            $row[] = $category->artist;
            $row[] = $category->active;
            $row[] = $category->duration;
            // add new column after duration
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
        $candidateRS = \DB::table("sounds as s")
                       ->select(\DB::raw("s.sound_id,s.title,s.sound_name,s.duration,s.artist,s.album,s.active,s.cat_id"),\DB::raw("GROUP_CONCAT(c.cat_name) as cat_name"))
                       ->leftjoin("categories as c",\DB::raw("FIND_IN_SET(c.cat_id,s.cat_id)"),">",\DB::raw("'0'"));
        if($request->category!="" && $request->active!="" ){
            $strWhere = " s.deleted=0 and find_in_set(". $request->category.",s.cat_id) <> 0 and s.active=".$request->active;
        }elseif($request->catgory!=null || $request->category!="" ){
            $strWhere = " s.deleted=0 and find_in_set(". $request->category.",s.cat_id) <> 0 ";
        }elseif($request->active!=null || $request->active!="" ){
            $strWhere = " s.deleted=0 and s.active=".$request->active." and s.cat_id <> 0";
        }
        else{
            $strWhere = " s.deleted=0 and s.cat_id <> 0";
        }      
        $strWhereOr = "";
        $i = 0;

        foreach ($this->column_search as $item) // loop column
        {
            if($keyword) // if datatable send POST for search{
            	$strWhereOr = $strWhereOr." $item like '%".$keyword."%' or ";
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
              $candidateRS->groupBy(\DB::raw("s.sound_id,s.title,s.sound_name,s.duration,s.artist,s.album,s.active,s.cat_id"))
                    ->orderBy("s.sound_id","desc");
        
       
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
        return $candidateRS->get()->count();
    }

    public function count_all($request)
    {
        $candidateRS = DB::table('sounds')->select(DB::raw("count(*) as total"))->where('deleted',0)->where('user_id',0)->first();
        return $candidateRS->total;
    }

    public function audio_play(Request $request){

            $soundRes = DB::table('sounds')->select(DB::raw("sound_name,user_id"))->where('sound_id',$request->id)->first();
            if( $soundRes->user_id>0){
                $audio_path=asset(Storage::url('public/sounds/'.$soundRes->user_id.'/'.$soundRes->sound_name));
            }else{
                $audio_path=asset(Storage::url('public/sounds/'.$soundRes->sound_name));
            }
            
           $html = '<audio controls autoplay><source src="'.$audio_path.'" type="audio/mpeg"></audio>';
        
            echo $html;
        
    }
    
    public function delete(Request $request){
        $rec_exists = array();
        $del_error = '';
        $ids = explode(',',$request->ids);
        foreach ($ids as $id) {
             $soundRes = DB::table('sounds')->select(DB::raw("sound_name,user_id,cat_id"))->where('sound_id',$id)->first();
                if( $soundRes->cat_id ==0){
                    $file_name=$soundRes->user_id.'/'.$soundRes->sound_name;
                }else{
                    $file_name=$soundRes->sound_name;
                }
            
            Storage::Delete("public/sounds/".$file_name);
            DB::table('sounds')->where('sound_id', $id)->delete();
        }
    }


    public function deleteEmptyFiles(Request $request){
        $rec_exists = array();
        $del_error = '';

        $sounds = DB::table('sounds')->select(\DB::raw("GROUP_CONCAT(sound_id) as sounds"))->where('cat_id','0')->whereNotIn('sound_id', [3163,3168])->first()->sounds;
        $ids = explode(',',$sounds);
        // dd($ids);
        foreach ($ids as $id) {
             $soundRes = DB::table('sounds')->select(DB::raw("sound_name,user_id,cat_id"))->where('sound_id',$id)->first();
                if( $soundRes->cat_id ==0){
                    $file_name=$soundRes->user_id.'/'.$soundRes->sound_name;
                }else{
                    $file_name=$soundRes->sound_name;
                }
            
            Storage::Delete("public/sounds/".$file_name);
            DB::table('sounds')->where('sound_id', $id)->delete();
        }
        
        if($del_error == 'error'){
            $request->session()->put('error',$msg );
            return response()->json(['status' => 'error',"rec_exists"=>$rec_exists]);
        }else{
            if( count($ids) > 1){
                $msg = "Sound deleted successfully";
            }else{
                $msg = "Sound deleted successfully";
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
}
