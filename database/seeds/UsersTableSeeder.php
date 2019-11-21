<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array(
            array(
                'name' => 'Admin Vccorp',
                'email' => 'admin@gmail.com',
                'password'=> '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm',// secret
                'created_at' => now()
            ),
            array(
                'name' => 'Test',
                'email' => 'a@gmail.com',
                'password'=> '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm',// secret
                'created_at' => now()
            ),
        );
        DB::table('users')->insert($data);
        factory(App\Models\User::class, 10)->create();
    }
}
