<?php
namespace App\Http\Controllers\Web\Auth;

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
use Laravel\Socialite\Facades\Socialite;
use App\Helpers\Common\Functions;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:web')->except('logout');
    }

    public function showLoginForm()
    {
        return view('web/auth/login');
    }

    public function login(Request $request)
    {
        // dd($request->all());
        // Validate the form data
        $rules = [
            'email'   => 'required|email|exists:users,email',
            'password' => 'required|min:6'
        ];

        $messages = [
            'email.required'            => 'You cant leave User name field empty',
            'email.email'            => 'You must enter an email address',
            'email.exists'            => 'The account with this email doesnot exists',
            'password.required'         => 'You cant leave Password field empty',
            'password.min'              => 'Password has to be 6 chars long'
        ];

        $this->validate($request, $rules, $messages);

        $remember = !empty($request->remember) ? 1 : 0;
        // Attempt to log the user in
        $user_detail=DB::table('users')->select('email_verified')->where('email',$request->email)->first();
        if(isset($user_detail) ){
            if($user_detail->email_verified==0){
                return redirect()->back()->withInput($request->only('email', 'remember'))->with('error', 'Email Not Verified.');
            }else{
                if (Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password], $remember)) {
                    //   Artisan::call('cache:clear');
                    // Artisan::call('config:clear');
                    // Artisan::call('view:clear');
                    // Artisan::call('route:clear');
                    // Artisan::call('storage:link');
                    // return redirect()->intended(route('web.home')); 
                    $user= Auth::guard('web')->user();
                    
                    return redirect()->route('web.userProfile',['id'=> $user->user_id]);
                }
            }

        }
       

        // if unsuccessful, then redirect back to the login with the form data
        return redirect()->back()->withInput($request->only('email', 'remember'))->with('error', 'Invalid login credentials');
    }   

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }  

    public function handleGoogleCallback()
    {
        try {    
            $user = Socialite::driver('google')->user();
            
            // $finduser = User::where('google_id', $user->id)->first();
            $findUser = User::where('email', $user->getEmail())->first();

            if ($findUser) {

                Auth::guard('web')->loginUsingId($findUser->user_id);
                return redirect()->route('web.home')->with('success','You are logged in successfully.');

            } else {
                // $newUser = User::create([
                //     'fname' => $user->getName(),
                //     'username' => $user->getNickname(),
                //     'email' => $user->getEmail(),
                //     'user_dp' => $user->getAvatar(),
                //     'login_type' => 'G',
                // ]);

                $newUser =array(
                    'fname' => $user->user['given_name'],
                    'lname' => $user->user['family_name'],
                    'username' => preg_replace("/\s+/", "", $user->user['name']),
                    'email' => $user->user['email'],
                    // 'user_dp' => $user->getAvatar(),
                    'user_dp' => $user->user['picture'],
                    'login_type' => 'G',
                    'active' => 1,
                    'email_verified' =>1,
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                );
               
                $id=DB::table('users')->insertGetId($newUser);
                $functions = new Functions();
                $path = $functions->_getUserFolderName($id, "public/videos");
                $path2 = $functions->_getUserFolderName($id, "public/photos");
                $path3 = $functions->_getUserFolderName($id, 'public/profile_pic');
                $path4 = $functions->_getUserFolderName($id, 'public/sounds');
                $video_gif_path = "public/videos/".$id.'/gif';        
                     
                $folderExists = Storage::exists($video_gif_path);
                if(!$folderExists){
                    Storage::makeDirectory($video_gif_path);
                }
                
                Auth::guard('web')->loginUsingId($id);
                return redirect()->route('web.home')->with('success','You are registered successfully.');
            }    
        } catch (Exception $e) {
            return redirect()->route('web.login');
        }

    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        try {
            
            $user = Socialite::driver('facebook')->user();

            $findUser = User::where('email', $user->getEmail())->first();

            if ($findUser) {

                Auth::guard('web')->loginUsingId($findUser->user_id);
                return redirect()->route('web.home')->with('success','You are Logged In successfully.');

            } else {
               
                $u_name=explode(' ',$user->getName());
                $f_name='';
                $l_name='';
                if(isset($u_name[0])){
                    $f_name=$u_name[0];
                }
                if(isset($u_name[1])){
                    $l_name=$u_name[1];
                }
                 $newUser =array(
                    'fname' => $f_name,
                    'lname' => $l_name,
                    'username' => preg_replace("/\s+/", "", $user->getName()),
                    'email' => $user->getEmail(),
                    // 'user_dp' => $user->getAvatar(),
                    'user_dp' => $user->getAvatar(),
                    'login_type' => 'F',
                    'active' => 1,
                    'email_verified' =>1,
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                );
                // dd($newUser);
                $id=DB::table('users')->insertGetId($newUser);
// dd($id);
                $functions = new Functions();
                $path = $functions->_getUserFolderName($id, "public/videos");
                $path2 = $functions->_getUserFolderName($id, "public/photos");
                $path3 = $functions->_getUserFolderName($id, 'public/profile_pic');
                $path4 = $functions->_getUserFolderName($id, 'public/sounds');
                $video_gif_path = "public/videos/".$id.'/gif';        
                     
                $folderExists = Storage::exists($video_gif_path);
                if(!$folderExists){
                    Storage::makeDirectory($video_gif_path);
                }
                
                Auth::guard('web')->loginUsingId($id);
                return redirect()->route('web.home')->with('success','You are registered successfully.');
            }
        } catch (Exception $e) {
            return redirect()->route('web.login');
        }

    }

}