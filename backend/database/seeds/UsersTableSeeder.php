<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'user_id' => 5,
                'username' => 'unify',
                'fname' => 'Unify',
                'lname' => 'Demo',
                'email' => 'demo@unifysofttech.com',
                'mobile' => '7878787878',
                'gender' => 'f',
                'bio' => NULL,
                'user_dp' => 'skvnU81woJYv4zyp0dMVfW2NlF9iiWZ2FUOAdDaG.jpg',
                'password' => '$2y$10$cawJ9CPDiHpylmSTLQIlv.einZ/NHoz8Y9psPxNkHkRy118KqfyC2',
                'dob' => NULL,
                'country' => '',
                'languages' => '',
                'app_token' => '',
                'login_type' => 'O',
                'time_zone' => '',
                'player_id' => '',
                'ios_uuid' => '',
                'verification_code' => '',
                'verification_time' => NULL,
                'active' => 1,
                'deleted' => 0,
                'last_active' => NULL,
                'created_at' => '2020-12-09 13:08:30',
                'updated_at' => '2021-01-12 12:16:10',
                'email_verified' => 1,
                'active_status' => 0,
                'dark_mode' => 0,
                'messenger_color' => '#2180f3',
                'remember_token' => NULL,
            ),
            1 => 
            array (
                'user_id' => 6,
                'username' => 'unify1',
                'fname' => 'Unify',
                'lname' => 'SoftTech',
                'email' => 'demo1@unifysofttech.com',
                'mobile' => '7878787878',
                'gender' => 'm',
                'bio' => NULL,
                'user_dp' => 'ygG3um6Z75Nlog3txcCuzXek10sW8p92aYBiO4c6.jpg',
                'password' => '$2y$10$JtNAK3/P3k4389kMUYY6c.yaSNhsN0H6J0XhdZNY75jgpDs12Xz2i',
                'dob' => NULL,
                'country' => '',
                'languages' => '',
                'app_token' => '',
                'login_type' => 'O',
                'time_zone' => '',
                'player_id' => '',
                'ios_uuid' => '',
                'verification_code' => '',
                'verification_time' => NULL,
                'active' => 1,
                'deleted' => 0,
                'last_active' => NULL,
                'created_at' => '2020-12-09 13:09:43',
                'updated_at' => '2020-12-09 13:09:43',
                'email_verified' => 1,
                'active_status' => 0,
                'dark_mode' => 0,
                'messenger_color' => '#2180f3',
                'remember_token' => NULL,
            ),
            2 => 
            array (
                'user_id' => 7,
                'username' => 'unify2',
                'fname' => 'Demo',
                'lname' => 'User1',
                'email' => 'demo2@unifysofttech.com',
                'mobile' => '7878787878',
                'gender' => 'f',
                'bio' => NULL,
                'user_dp' => 'dfxpCO7e7AKPMYrJLAdkdDyzHJGnb0JkdJQL8Q8A.jpg',
                'password' => '$2y$10$uY8Tl88U4BAHxBmBbBoK6uDL2bLOQjcsOxSt2x0CkTTV2CK.Oo5hq',
                'dob' => NULL,
                'country' => '',
                'languages' => '',
                'app_token' => '',
                'login_type' => 'O',
                'time_zone' => '',
                'player_id' => '',
                'ios_uuid' => '',
                'verification_code' => '',
                'verification_time' => NULL,
                'active' => 1,
                'deleted' => 0,
                'last_active' => NULL,
                'created_at' => '2020-12-09 13:10:45',
                'updated_at' => '2021-01-12 12:18:27',
                'email_verified' => 1,
                'active_status' => 0,
                'dark_mode' => 0,
                'messenger_color' => '#2180f3',
                'remember_token' => NULL,
            ),
            3 => 
            array (
                'user_id' => 8,
                'username' => 'unify3',
                'fname' => 'Unify',
                'lname' => 'Tech',
                'email' => 'demo3@unifysofttech.com',
                'mobile' => '7878787878',
                'gender' => 'm',
                'bio' => NULL,
                'user_dp' => 'kuhfyHdMih2rsLt8C4LHMoUbuGi9liWIrgk93C1A.jpg',
                'password' => '$2y$10$njXb24EZlH8vZXYKAL1FR./bMCovXJheyd4syu9D2ExY3v.ChjrxO',
                'dob' => NULL,
                'country' => '',
                'languages' => '',
                'app_token' => '',
                'login_type' => 'O',
                'time_zone' => '',
                'player_id' => '',
                'ios_uuid' => '',
                'verification_code' => '',
                'verification_time' => NULL,
                'active' => 1,
                'deleted' => 0,
                'last_active' => NULL,
                'created_at' => '2020-12-09 13:11:40',
                'updated_at' => '2020-12-09 13:11:40',
                'email_verified' => 1,
                'active_status' => 0,
                'dark_mode' => 0,
                'messenger_color' => '#2180f3',
                'remember_token' => NULL,
            ),
        ));
        
        
    }
}