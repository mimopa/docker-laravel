<?php

use Illuminate\Database\Seeder;

class HomeSettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('home_settings')->delete();
        
        \DB::table('home_settings')->insert(array (
            0 => 
            array (
                'home_setting_id' => 1,
                'heading' => 'Set The World On Fire With Your',
                'sub_heading' => 'Unique Talent',
                'img1' => '',
                'img2' => '',
                'img1_link' => '',
                'img2_link' => '',
                'comments_per_page' => 10,
                'logo' => '',
                'site_title' => 'Leuke V2.3',
                'white_logo' => '',
            'main_color' => 'background: linear-gradient(left , rgb(115, 80, 199) 7% , rgb(236, 74, 99) 86%);
background: -o-linear-gradient(left , rgb(115, 80, 199) 7% , rgb(236, 74, 99) 86%);
background: -ms-linear-gradient(left , rgb(115, 80, 199) 7% , rgb(236, 74, 99) 86%);
background: -moz-linear-gradient(left , rgb(115, 80, 199) 7% , rgb(236, 74, 99) 86%);
background: -webkit-linear-gradient(left , rgb(115, 80, 199) 7% , rgb(236, 74, 99) 86%);',
                'videos_per_page' => 12,
                'home_top_background_img' => '',
            ),
        ));
        
        
    }
}