<?php

use Illuminate\Database\Seeder;

class AppLoginPageTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('app_login_page')->delete();
        
        \DB::table('app_login_page')->insert(array (
            0 => 
            array (
                'app_login_page_id' => 1,
                'logo' => 'dq5berqZqmlxag7PR0RbdRbhSG9eiGJlCN6zbi4C.png',
                'title' => 'Sign Up For Leuke',
                'description' => 'Create New Account',
                'fb_login' => 1,
                'google_login' => 1,
                'privacy_policy' => 'By Signing in your agree with Leuke Terms of Service use and Privacy Policy',
            ),
        ));
        
        
    }
}