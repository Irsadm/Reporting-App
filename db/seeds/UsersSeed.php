<?php

use Phinx\Seed\AbstractSeed;

class UsersSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $data[] = [
            'name'     =>  'Administrator',
            'email'    =>  'admin@null.net',
            'username' =>  'admin',
            'password' =>  password_hash('admin123', PASSWORD_DEFAULT),
            'gender'   =>  'L',
            'address'  =>  'Jakarta',
            'phone'    =>  '081234567890',
            'image'    =>  'admin.jpg',
            'status' =>  1,
            'updated_at'   =>  '2017-04-30 00:00:00',
            'created_at'   =>  '2017-05-30 00:00:00',
        ];

        $data[] = [
            'name'     =>  'Budiman',
            'email'    =>  'budi@null.net',
            'username' =>  'budiman',
            'password' =>  password_hash('budi123', PASSWORD_DEFAULT),
            'gender'   =>  'L',
            'address'  =>  'Jakarta',
            'phone'    =>  '081234567891',
            'image'    =>  'avatar.png',
            'updated_at'   =>  '2017-04-30 00:00:00',
            'created_at'   =>  '2017-05-30 00:00:00',
        ];

        $data[] = [
            'name'      =>  'Caca Larasati',
            'email'     =>  'caca@null.net',
            'username'  =>  'laras',
            'password'  =>  password_hash('laras123', PASSWORD_DEFAULT),
            'gender'    =>  'P',
            'address'   =>  'Jakarta',
            'phone'     =>  '081234567819',
            'image'     =>  'avatar.png',
            'updated_at'   =>  '2017-04-30 00:00:00',
            'created_at'   =>  '2017-05-30 00:00:00',
        ];

        $data[] = [
            'name'      =>  'Dede Nurdandi',
            'email'     =>  'dede@null.net',
            'username'  =>  'deden',
            'password'  =>  password_hash('dede123', PASSWORD_DEFAULT),
            'gender'    =>  'L',
            'address'   =>  'Bogor',
            'phone'     =>  '081234567814',
            'image'     =>  'avatar.png',
            'updated_at'   =>  '2017-04-30 00:00:00',
            'created_at'   =>  '2017-05-30 00:00:00',
        ];

        $data[] = [
            'name'      =>  'Ekawati',
            'email'     =>  'eka@null.net',
            'username'  =>  'ekawati',
            'password'  =>  password_hash('eka123', PASSWORD_DEFAULT),
            'gender'    =>  'P',
            'address'   =>  'Bogor',
            'phone'     =>  '081234567814',
            'image'     =>  'avatar.png',
            'updated_at'   =>  '2017-04-30 00:00:00',
            'created_at'   =>  '2017-05-30 00:00:00',
        ];

        $data[] = [
            'name'      =>  'Fahmi',
            'email'     =>  'fahmi@null.net',
            'username'  =>  'fahmi',
            'password'  =>  password_hash('fahmi123', PASSWORD_DEFAULT),
            'gender'    =>  'L',
            'address'   =>  'Bogor',
            'phone'     =>  '081234567888',
            'image'     =>  'avatar.png',
            'updated_at'   =>  '2017-04-30 00:00:00',
            'created_at'   =>  '2017-05-30 00:00:00',
        ];


        $this->insert('users', $data);
    }
}
