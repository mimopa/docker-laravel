<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
      
       
       
        
        
        $this->call(AdminTableSeeder::class);
        $this->call(AdSettingsTableSeeder::class);
        $this->call(CategoriesTableSeeder::class);
        $this->call(ChatsTableSeeder::class);
        $this->call(FollowTableSeeder::class);
        $this->call(HomeSettingsTableSeeder::class);
        $this->call(NsfwSettingsTableSeeder::class);
        $this->call(PagesTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(SocialMediaLinksTableSeeder::class);
        $this->call(SoundsTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(VideosTableSeeder::class);
        $this->call(AppLoginPageTableSeeder::class);
    }
}
