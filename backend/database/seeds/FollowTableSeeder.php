<?php

use Illuminate\Database\Seeder;

class FollowTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('follow')->delete();
        
        \DB::table('follow')->insert(array (
            0 => 
            array (
                'follow_id' => 70,
                'follow_by' => 6,
                'follow_to' => 5,
                'follow_on' => '2021-01-12 12:50:36',
            ),
            1 => 
            array (
                'follow_id' => 69,
                'follow_by' => 6,
                'follow_to' => 7,
                'follow_on' => '2021-01-12 12:50:33',
            ),
            2 => 
            array (
                'follow_id' => 68,
                'follow_by' => 6,
                'follow_to' => 8,
                'follow_on' => '2021-01-12 12:50:28',
            ),
            3 => 
            array (
                'follow_id' => 67,
                'follow_by' => 5,
                'follow_to' => 6,
                'follow_on' => '2021-01-12 12:24:37',
            ),
            4 => 
            array (
                'follow_id' => 66,
                'follow_by' => 5,
                'follow_to' => 7,
                'follow_on' => '2021-01-12 12:24:36',
            ),
            5 => 
            array (
                'follow_id' => 65,
                'follow_by' => 5,
                'follow_to' => 8,
                'follow_on' => '2021-01-12 12:24:34',
            ),
            6 => 
            array (
                'follow_id' => 64,
                'follow_by' => 8,
                'follow_to' => 5,
                'follow_on' => '2021-01-12 12:21:53',
            ),
            7 => 
            array (
                'follow_id' => 63,
                'follow_by' => 8,
                'follow_to' => 6,
                'follow_on' => '2021-01-12 12:21:51',
            ),
            8 => 
            array (
                'follow_id' => 62,
                'follow_by' => 8,
                'follow_to' => 7,
                'follow_on' => '2021-01-12 12:21:50',
            ),
        ));
        
        
    }
}