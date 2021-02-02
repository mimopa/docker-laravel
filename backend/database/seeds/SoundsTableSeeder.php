<?php

use Illuminate\Database\Seeder;

class SoundsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('sounds')->delete();
        
        \DB::table('sounds')->insert(array (
            0 => 
            array (
                'sound_id' => 3306,
                'title' => 'I Love You',
                'sound_name' => '1610451904.aac',
                'user_id' => 0,
                'cat_id' => '2',
                'parent_id' => 0,
                'duration' => 32,
                'album' => 'Bodyguard',
                'artist' => '',
                'tags' => NULL,
                'used_times' => 0,
                'deleted' => 0,
                'created_at' => '2021-01-12 11:45:08',
                'active' => 1,
            ),
            1 => 
            array (
                'sound_id' => 3307,
                'title' => 'Mere Brother Ki Dulhan',
                'sound_name' => '1610451959.aac',
                'user_id' => 0,
                'cat_id' => '2',
                'parent_id' => 0,
                'duration' => 32,
                'album' => 'Mere Brother Ki Dulhan',
                'artist' => '',
                'tags' => NULL,
                'used_times' => 0,
                'deleted' => 0,
                'created_at' => '2021-01-12 11:46:02',
                'active' => 1,
            ),
            2 => 
            array (
                'sound_id' => 3308,
                'title' => 'love me like you do',
                'sound_name' => '1610452012.aac',
                'user_id' => 0,
                'cat_id' => '4',
                'parent_id' => 0,
                'duration' => 30,
                'album' => 'English Songs',
                'artist' => '',
                'tags' => NULL,
                'used_times' => 0,
                'deleted' => 0,
                'created_at' => '2021-01-12 11:46:54',
                'active' => 1,
            ),
            3 => 
            array (
                'sound_id' => 3309,
                'title' => 'uu na na na',
                'sound_name' => '1610452063.aac',
                'user_id' => 0,
                'cat_id' => '4',
                'parent_id' => 0,
                'duration' => 23,
                'album' => 'Sugor',
                'artist' => '',
                'tags' => NULL,
                'used_times' => 0,
                'deleted' => 0,
                'created_at' => '2021-01-12 11:47:45',
                'active' => 1,
            ),
            4 => 
            array (
                'sound_id' => 3310,
                'title' => 'Hum Sarif Kya Hue',
                'sound_name' => '1610452096.aac',
                'user_id' => 0,
                'cat_id' => '5',
                'parent_id' => 0,
                'duration' => 7,
                'album' => 'Dilwale Dialogue',
                'artist' => '',
                'tags' => NULL,
                'used_times' => 0,
                'deleted' => 0,
                'created_at' => '2021-01-12 11:48:50',
                'active' => 1,
            ),
            5 => 
            array (
                'sound_id' => 3311,
                'title' => 'Baaz Ki Nazar Aur Bajirao Ki Talvar',
                'sound_name' => '1610452150.aac',
                'user_id' => 0,
                'cat_id' => '5',
                'parent_id' => 0,
                'duration' => 8,
                'album' => 'Bajirao Mastani',
                'artist' => '',
                'tags' => NULL,
                'used_times' => 0,
                'deleted' => 0,
                'created_at' => '2021-01-12 11:49:36',
                'active' => 1,
            ),
            6 => 
            array (
                'sound_id' => 3312,
                'title' => 'Bachpan Me Chicks Better Thi',
                'sound_name' => '1610452216.aac',
                'user_id' => 0,
                'cat_id' => '6',
                'parent_id' => 0,
                'duration' => 10,
                'album' => 'Main Tera Hero',
                'artist' => '',
                'tags' => NULL,
                'used_times' => 0,
                'deleted' => 0,
                'created_at' => '2021-01-12 11:51:07',
                'active' => 1,
            ),
            7 => 
            array (
                'sound_id' => 3313,
                'title' => 'Main Six Pack Abs Dikhane Aaya Tha',
                'sound_name' => '1610452218.aac',
                'user_id' => 0,
                'cat_id' => '6',
                'parent_id' => 0,
                'duration' => 8,
                'album' => 'Main Tera Hero',
                'artist' => '',
                'tags' => NULL,
                'used_times' => 0,
                'deleted' => 0,
                'created_at' => '2021-01-12 11:50:46',
                'active' => 1,
            ),
        ));
        
        
    }
}