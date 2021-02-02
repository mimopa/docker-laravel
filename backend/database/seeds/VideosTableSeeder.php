<?php

use Illuminate\Database\Seeder;

class VideosTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('videos')->delete();
        
        \DB::table('videos')->insert(array (
            0 => 
            array (
                'video_id' => 30,
                'enabled' => 1,
                'user_id' => 6,
                'sound_id' => 0,
                'title' => 'Test Video 3',
                'description' => 'Test Video 3',
                'master_video' => '',
                'video' => '1610456040/TikTok - Make Your Day_9.mp4',
                'thumb' => '1610456040.jpg',
                'gif' => NULL,
                'tags' => NULL,
                'location' => '',
                'duration' => 0,
                'deleted' => 0,
                'created_at' => '2021-01-12 12:54:18',
                'updated_at' => '2021-01-12 12:54:29',
                'active' => 1,
                'flag' => 0,
                'privacy' => 0,
                'total_likes' => 0,
                'total_comments' => 0,
                'total_views' => 0,
                'total_report' => 0,
            ),
            1 => 
            array (
                'video_id' => 31,
                'enabled' => 1,
                'user_id' => 5,
                'sound_id' => 0,
                'title' => 'Test Video 4',
                'description' => 'Test Description',
                'master_video' => '',
                'video' => '1610456123/TikTok - Make Your Day_13.mp4',
                'thumb' => '1610456123.jpg',
                'gif' => NULL,
                'tags' => NULL,
                'location' => '',
                'duration' => 0,
                'deleted' => 0,
                'created_at' => '2021-01-12 12:55:31',
                'updated_at' => '2021-01-12 12:55:49',
                'active' => 1,
                'flag' => 0,
                'privacy' => 0,
                'total_likes' => 0,
                'total_comments' => 0,
                'total_views' => 0,
                'total_report' => 0,
            ),
            2 => 
            array (
                'video_id' => 32,
                'enabled' => 1,
                'user_id' => 7,
                'sound_id' => 0,
                'title' => 'Test Video 5',
                'description' => 'Test Description',
                'master_video' => '',
                'video' => '1610456225/TikTok - Make Your Day_12.mp4',
                'thumb' => '1610456225.jpg',
                'gif' => NULL,
                'tags' => NULL,
                'location' => '',
                'duration' => 0,
                'deleted' => 0,
                'created_at' => '2021-01-12 12:57:15',
                'updated_at' => '2021-01-12 12:57:28',
                'active' => 1,
                'flag' => 0,
                'privacy' => 0,
                'total_likes' => 0,
                'total_comments' => 0,
                'total_views' => 0,
                'total_report' => 0,
            ),
        ));
        
        
    }
}