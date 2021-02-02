<?php

use Illuminate\Database\Seeder;

class NsfwSettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('nsfw_settings')->delete();
        
        \DB::table('nsfw_settings')->insert(array (
            0 => 
            array (
                'ns_id' => 1,
                'api_key' => '1122776968',
                'api_secret' => '2uSorbXFkDKGCPFGySxJ',
                'status' => 1,
                'wad' => 0,
                'offensive' => 0,
                'nudity' => 1,
            ),
        ));
        
        
    }
}