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
            'email'    =>  'reportingmit@gmail.com',
            'username' =>  'admin',
            'password' =>  password_hash('admin123', PASSWORD_DEFAULT),
            'gender'   =>  'Laki-laki',
            'address'  =>  'DKI Jakarta',
            'phone'    =>  '+6281234567890',
            'image'    =>  'admin.jpg',
            'status' =>  1,
            // 'updated_at'   =>  '2017-04-30 00:00:00',
            // 'created_at'   =>  '2017-05-30 00:00:00',
        ];

        $data[] = [
            'name'     =>  'Budiman',
            'email'    =>  'nurud@gmx.com',
            'username' =>  'budiman',
            'password' =>  password_hash('budi123', PASSWORD_DEFAULT),
            'gender'   =>  'Laki-laki',
            'address'  =>  'Bandung, Jawa Barat',
            'phone'    =>  '+6281234567891',
            // 'updated_at'   =>  '2017-04-30 00:00:00',
            // 'created_at'   =>  '2017-05-30 00:00:00',
        ];

        $data[] = [
            'name'      =>  'Caca Larasati',
            'email'     =>  'caca@null.net',
            'username'  =>  'laras',
            'password'  =>  password_hash('laras123', PASSWORD_DEFAULT),
            'gender'    =>  'Perempuan',
            'address'   =>  'Cirebon, Jawa Barat',
            'phone'     =>  '+6281234567819',
            // 'updated_at'   =>  '2017-04-30 00:00:00',
            // 'created_at'   =>  '2017-05-30 00:00:00',
        ];

        $data[] = [
            'name'      =>  'Dede Nurdandi',
            'email'     =>  'nurud13@gmail.com',
            'username'  =>  'dandi',
            'password'  =>  password_hash('dandi123', PASSWORD_DEFAULT),
            'gender'    =>  'Laki-laki',
            'address'   =>  'Depok, Jawa Barat',
            'phone'     =>  '+6281234567814',
            // 'updated_at'   =>  '2017-04-30 00:00:00',
            // 'created_at'   =>  '2017-05-30 00:00:00',
        ];

        $data[] = [
            'name'      =>  'Ekawati',
            'email'     =>  'eka@null.net',
            'username'  =>  'ekawati',
            'password'  =>  password_hash('eka123', PASSWORD_DEFAULT),
            'gender'    =>  'Perempuan',
            'address'   =>  'Semarang, Jawa Tengah',
            'phone'     =>  '+6281234567814',
            // 'updated_at'   =>  '2017-04-30 00:00:00',
            // 'created_at'   =>  '2017-05-30 00:00:00',
        ];

        $data[] = [
            'name'      =>  'Fahmi',
            'email'     =>  'fahmi@null.net',
            'username'  =>  'fahmi',
            'password'  =>  password_hash('fahmi123', PASSWORD_DEFAULT),
            'gender'    =>  'Laki-laki',
            'address'   =>  'Pangandaran, Jawa Barat',
            'phone'     =>  '+6281234567888',
            // 'updated_at'   =>  '2017-04-30 00:00:00',
            // 'created_at'   =>  '2017-05-30 00:00:00',
        ];


        $this->insert('users', $data);
    }
}
