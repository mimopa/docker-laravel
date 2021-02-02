<?php

use Illuminate\Database\Seeder;

class ChatsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('chats')->delete();
        
        \DB::table('chats')->insert(array (
            0 => 
            array (
                'id' => 1,
                'from_id' => 5,
                'to_id' => 5,
                'msg' => 'hello',
                'sent_on' => '2021-01-12 12:16:31',
                'is_read' => 1,
                'read_on' => '2021-01-12 17:46:31',
            ),
            1 => 
            array (
                'id' => 2,
                'from_id' => 5,
                'to_id' => 6,
                'msg' => 'hi',
                'sent_on' => '2021-01-12 12:16:36',
                'is_read' => 1,
                'read_on' => '2021-01-12 17:46:36',
            ),
            2 => 
            array (
                'id' => 3,
                'from_id' => 5,
                'to_id' => 8,
                'msg' => 'ok',
                'sent_on' => '2021-01-12 12:16:54',
                'is_read' => 1,
                'read_on' => '2021-01-12 17:46:54',
            ),
            3 => 
            array (
                'id' => 4,
                'from_id' => 6,
                'to_id' => 5,
                'msg' => 'Yes',
                'sent_on' => '2021-01-12 12:17:42',
                'is_read' => 0,
                'read_on' => '2021-01-12 17:47:42',
            ),
            4 => 
            array (
                'id' => 5,
                'from_id' => 6,
                'to_id' => 6,
                'msg' => 'hello',
                'sent_on' => '2021-01-12 12:17:53',
                'is_read' => 0,
                'read_on' => '2021-01-12 17:47:53',
            ),
            5 => 
            array (
                'id' => 6,
                'from_id' => 6,
                'to_id' => 8,
                'msg' => 'hello',
                'sent_on' => '2021-01-12 12:17:58',
                'is_read' => 1,
                'read_on' => '2021-01-12 17:47:58',
            ),
            6 => 
            array (
                'id' => 7,
                'from_id' => 7,
                'to_id' => 5,
                'msg' => 'hi',
                'sent_on' => '2021-01-12 12:18:44',
                'is_read' => 0,
                'read_on' => '2021-01-12 17:48:44',
            ),
            7 => 
            array (
                'id' => 8,
                'from_id' => 7,
                'to_id' => 6,
                'msg' => 'hello',
                'sent_on' => '2021-01-12 12:18:51',
                'is_read' => 0,
                'read_on' => '2021-01-12 17:48:51',
            ),
            8 => 
            array (
                'id' => 9,
                'from_id' => 7,
                'to_id' => 8,
                'msg' => 'hi',
                'sent_on' => '2021-01-12 12:18:57',
                'is_read' => 1,
                'read_on' => '2021-01-12 17:48:57',
            ),
            9 => 
            array (
                'id' => 10,
                'from_id' => 8,
                'to_id' => 7,
                'msg' => 'Yes',
                'sent_on' => '2021-01-12 12:19:53',
                'is_read' => 0,
                'read_on' => '2021-01-12 17:49:53',
            ),
            10 => 
            array (
                'id' => 11,
                'from_id' => 8,
                'to_id' => 6,
                'msg' => 'hello',
                'sent_on' => '2021-01-12 12:19:58',
                'is_read' => 0,
                'read_on' => '2021-01-12 17:49:58',
            ),
            11 => 
            array (
                'id' => 12,
                'from_id' => 8,
                'to_id' => 5,
                'msg' => 'Okay',
                'sent_on' => '2021-01-12 12:20:03',
                'is_read' => 0,
                'read_on' => '2021-01-12 17:50:03',
            ),
        ));
        
        
    }
}