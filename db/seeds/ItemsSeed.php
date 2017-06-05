<?php

use Phinx\Seed\AbstractSeed;

class ItemsSeed extends AbstractSeed
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
                'name'       =>  'Membaca',
                'description'=>  'Membaca buku pelajaran',
                'group_id'   =>  '1',
                'start_date' =>  '2017-06-9',
                'recurrent'  =>  'daily',
                // 'updated_at'   =>  '2017-04-30 00:00:00',
                // 'created_at'   =>  '2017-05-30 00:00:00',
            ];

            $data[] = [
                'name'       =>  'Upacara',
                'description'=>  'Upacara bendera Hari Senin',
                'recurrent'  =>  'weekly',
                'start_date' =>  '2017-06-4',
                'group_id'   =>  '1',
                // 'updated_at'   =>  '2017-04-30 00:00:00',
                // 'created_at'   =>  '2017-05-30 00:00:00',
            ];

            $data[] = [
                'name'       =>  'Tugas Bulanan',
                'start_date' =>  '2017-05-8',
                'start_date' =>  '2017-06-4',
                'group_id'   =>  '1',
                // 'updated_at'   =>  '2017-04-30 00:00:00',
                // 'created_at'   =>  '2017-05-30 00:00:00',
            ];

            $this->insert('items', $data);
    }
}
