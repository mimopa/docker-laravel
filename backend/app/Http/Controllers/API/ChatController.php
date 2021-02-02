<?php

namespace App\Http\Controllers\API;
use DateTime;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Hash;
use App\Helpers\Common\Functions;

use Auth;
use Mail;
use Illuminate\Support\Facades\URL; 

class ChatController extends Controller
{
    private function _error_string($errArray)
    {
        $error_string = '';
    foreach ($errArray as $key) {
            $error_string.= $key."\n";
        }
        return $error_string;
    }

    public function listing(Request $request){
     
     	$validator = Validator::make($request->all(), [ 
            'login_id'          => 'required',              
            'app_token'        => 'required',    
            'user_id'       => 'required',    
        ],[ 
            'login_id.required'   => 'Id is required',
            'app_token.required'   => 'App Token is required',
            'user_id.required'  => 'User id is required'
        ]);
     
        if (!$validator->passes()) {
            return response()->json(['status'=>'error','msg'=> $this->_error_string($validator->errors()->all()) ]);
        }else{
            if(!isset($request->page) && $request->page==""){
                $page=1;
            }else{
                $page=$request->page;
            }
            $limit=20;
            $offset=($page-1)*$limit;
            $functions = new Functions();
            $token_res= $functions->validate_token($request->login_id,$request->app_token);
            if($token_res>0){
                $user_res=DB::table('chats')
                        ->select(DB::raw('id,msg,from_id,to_id,sent_on,is_read'))
                        ->whereIn('from_id', [$request->login_id,$request->user_id])
                        ->whereIn('to_id', [$request->login_id,$request->user_id]);
                $total_records=$user_res->count();
                $user_res=$user_res->orderBy('id','desc')
                        ->skip($offset) 
                        ->take($limit)
                        ->get();
               $m=array();
               $data=array('total_records'=>$total_records);
               foreach($user_res as $r){
                      $sent_on=date('h:i A', strtotime($r->sent_on));
                        $m['msgs'][] = array("chat_id" => $r->id,"msg" => $r->msg,"from_id"=>$r->from_id,"to_id"=>$r->to_id,"sent_on"=>$sent_on,"is_read"=>$r->is_read);
                      
               }
               if(!empty($m['msgs'])){
			   	$data['data'] =array_reverse($m['msgs']);
			   }else{
			   	$data['data'] =array_reverse($m);
			   }
                 
             	$data1 =array(
                    'is_read'       => '1',
                
                ); 
                DB::table('chats')->where('to_id',$request->user_id)->where('from_id',$request->login_id)->update(['is_read'=>1]);
                $response = array("status" => "success",'data' => $data);
           
            }  else{
                $response = array("status" => "error",'msg'=>'Invalid Token');
            }   
        
            return response()->json($response); 
        }
    }
    
    public function storeMsg(Request $request){
    	
		$validator = Validator::make($request->all(), [
			'login_id' => 'required',
			'app_token' => 'required',
			'user_id' => 'required',
			'msg' => 'required',
		],[
			'login_id.required' => 'Login id is required',
			'app_token.required' => 'App Token is required',
			'user_id.required' => 'User id is required',
			'msg.required' => 'Cannot send empty Message'
		]);

		if (!$validator->passes()) {
			return response()->json(['status'=>'error','msg'=> $this->_error_string($validator->errors()->all()) ]);
		}else{
			$functions = new Functions();
			$token_res = $functions->validate_token($request->login_id,$request->app_token);
			if($token_res > 0) {
				$data =array(
					'from_id' => $request->login_id,
					'to_id' => $request->user_id,
					'msg' => $request->msg,
					'sent_on' => date("Y-m-d H:i:s"),
				);
				DB::table('chats')->insert($data);
				$response = array("status" => "success",'msg'=>'message sent');
			} else{
                $response = array("status" => "error",'msg'=>'Invalid Token');
            }   
			return response()->json($response);
	    }
	}
    
    public function chatHistory(Request $request){
     
     	$validator = Validator::make($request->all(), [ 
            'login_id'          => 'required',              
            'app_token'        => 'required'
        ],[ 
            'login_id.required'   => 'Id is required',
            'app_token.required'   => 'App Token is required'
        ]);
     
        if (!$validator->passes()) {
            return response()->json(['status'=>'error','msg'=> $this->_error_string($validator->errors()->all()) ]);
        }else{
            if(!isset($request->page) && $request->page==""){
                $page=1;
            }else{
                $page=$request->page;
            }
            $limit=20;
            $offset=($page-1)*$limit;
            $functions = new Functions();
            $token_res= $functions->validate_token($request->login_id,$request->app_token);
            if($token_res>0){
                $user_res = DB::select("SELECT t1.*
                                FROM chats AS t1
                                INNER JOIN
                                (
                                    SELECT
                                        LEAST(from_id, to_id) AS user_id,
                                        GREATEST(from_id, to_id) AS recipient_to,
                                        MAX(id) AS max_id
                                    FROM chats
                                    GROUP BY
                                        LEAST(from_id, to_id),
                                        GREATEST(from_id, to_id)
                                ) AS t2
                                    ON LEAST(t1.from_id, t1.to_id) = t2.user_id AND
                                       GREATEST(t1.from_id, t1.to_id) = t2.recipient_to AND
                                       t1.id = t2.max_id
                                    WHERE t1.from_id = ".$request->login_id." OR t1.to_id = ".$request->login_id." order by id desc limit $offset,$limit");
               
               $m=array();
               $totalRecords=0;
               foreach($user_res as $r){
                   $userId=$r->to_id;
                   if( $r->to_id == $request->login_id ) {
                       $userId=$r->from_id;
                   }
                   $user = DB::table("users")->select("user_id","username","user_dp")->where('user_id',$userId)->first();
                   if(stripos($user->user_dp,'https://')!==false){
                        $file_path=$user->user_dp;
                    }else{
                        $file_path = asset(Storage::url('public/profile_pic/'.$user->user_id."/small/".$user->user_dp));
                        if($file_path==""){
                            $file_path=asset('default/default.png');
                        }
                    }
                   $totalRecords++;
                      $sent_on= Functions::time_elapsed_string($r->sent_on);
                        $m['msgs'][] = array("chat_id" => $r->id,"msg" => $r->msg,"from_id"=>$r->from_id,"to_id"=>$userId,"sent_on"=>$sent_on,"username"=>"@".$user->username,"user_dp"=>$file_path,'is_read'=>$r->is_read);
                      
               }
               $data=array('total_records'=>$totalRecords);
               if(!empty($m['msgs'])){
			   	$data['data'] =$m['msgs'];
			   }
               $response = array("status" => "success",'data' => $data);
           
            }  else{
                $response = array("status" => "error",'msg'=>'Invalid Token');
            }   
        
            return response()->json($response); 
        }
    }
	
	public function friendsList(Request $request){
		$validator = Validator::make($request->all(), [ 
			'user_id'          => 'required',
			'app_token'        => 'required',
		],[ 
			'user_id.required'      => 'User id is required',
			'app_token.required'    => 'App Token is required',
		]);
		
		if (!$validator->passes()) {
			return response()->json(['status'=>'error','msg'=> $this->_error_string($validator->errors()->all()) ]);
		}else{
			$functions = new Functions();
			$token_res= $functions->validate_token($request->user_id,$request->app_token);
			if($token_res>0) {
                // $userDpPath = secure_asset(config('app.profile_path'));
                $userDpPath = asset(Storage::url('public/profile_pic'));
				$limit = 10;
				$follwoing = DB::table("users as u")->select(DB::raw("u.user_id,
					case when u.user_dp !='' THEN case when INSTR(u.user_dp,'https://') > 0 THEN u.user_dp ELSE concat('".$userDpPath."/',u.user_id,'/small/',u.user_dp)  END ELSE '' END as user_dp,
					concat('@',u.username) as username,u.fname,u.lname, case when f.follow_id > 0 THEN 'Following' ELSE 'Follow' END as followText"))
				->join('follow as f', function ($join) use ($request){
					$join->on('u.user_id','=','f.follow_to')
					->where('f.follow_by',$request->user_id);
				})
				->where("u.deleted",0)
				->where("u.active",1);
				// dd($request->user_id);
				if(isset($request->search) && $request->search!=""){
					$search = $request->search;
					$follwoing = $follwoing->where('u.username', 'like', '%' . $search . '%')->orWhere('u.fname', 'like', '%' . $search . '%')->orWhere('u.lname', 'like', '%' . $search . '%');
				}
				
				$users = DB::table("users as u")->select(DB::raw("u.user_id,
					case when u.user_dp !='' THEN case when INSTR(u.user_dp,'https://') > 0 THEN u.user_dp ELSE concat('".$userDpPath."/',u.user_id,'/small/',u.user_dp)  END ELSE '' END as user_dp,
					concat('@',u.username) as username,u.fname,u.lname, case when f2.follow_id > 0 THEN 'Following' ELSE 'Follow' END as followText"))
				->join('follow as f', function ($join) use ($request){
					$join->on('u.user_id','=','f.follow_by')
					->where('f.follow_to',$request->user_id);
				})
				->leftJoin('follow as f2', function ($join) use ($request){
					$join->on('u.user_id','=','f2.follow_to')
					->where('f2.follow_by',$request->user_id);
				})
				->where("u.deleted",0)
				->where("u.active",1);
				
				$users->union($follwoing);
				if(isset($request->search) && $request->search!=""){
					$search = $request->search;
					$users = $users->where('u.username', 'like', '%' . $search . '%')->orWhere('u.fname', 'like', '%' . $search . '%')->orWhere('u.lname', 'like', '%' . $search . '%');
				}
				$users = $users->orderBy('user_id','desc');
				$users= $users->paginate($limit);
				$total_records=$users->total();   
				
				$response = array("status" => "success",'data' => $users,'total_records'=>$total_records);
			} else {
				return response()->json([
					"status" => "error", "msg" => "Unauthorized user!"
				]);
			}
		} 
		return response()->json($response); 
	}

}   