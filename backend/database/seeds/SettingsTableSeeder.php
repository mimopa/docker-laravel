<?php

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('settings')->delete();
        
        \DB::table('settings')->insert(array (
            0 => 
            array (
                'setting_id' => 1,
                'site_name' => 'Unify SoftTech',
                'site_address' => '<p>India</p>',
                'site_email' => 'support@unifysofttech.com',
                'site_phone' => '7878787878',
                'site_logo' => 'PX9X3aj5x9k9ZdMcq8ti1X9wUhkn7QX70zI6A5Am.png',
                'watermark' => 'nU7n9E8V5WRCPLn3W4CgZkdAJZxBreHA5W5Awayc.png',
                'cur_version' => 'v2.2',
                'updated_at' => '2021-01-25 11:32:26',
            ),
        ));
        
        
    }
}