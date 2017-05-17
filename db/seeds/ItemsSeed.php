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
            'recurrent'  =>  'daily',
            'start_date' =>  '2017-05-13 00:00:01',
            'end_date'   =>  '2017-05-13 00:00:00',
            'group_id'   =>  '1',
            'updated_at'   =>  '2017-04-30 00:00:00',
            'created_at'   =>  '2017-05-30 00:00:00',
        ];

        $data[] = [
            'name'       =>  'Upacara',
            'description'=>  'Upacara bendera',
            'recurrent'  =>  'weekly',
            'start_date' =>  '2017-05-8 07:00:00',
            'end_date'   =>  '2017-05-8 07:45:00',
            'group_id'   =>  '1',
            'updated_at'   =>  '2017-04-30 00:00:00',
            'created_at'   =>  '2017-05-30 00:00:00',
        ];

        $data[] = [
            'name'       =>  'Tugas Bulanan',
            'description'=>  'Tugas akhir bulan',
            'recurrent'  =>  'monthly',
            'start_date' =>  '2017-05-8 07:00:00',
            'end_date'   =>  '2017-05-8 07:45:00',
            'group_id'   =>  '1',
            'updated_at'   =>  '2017-04-30 00:00:00',
            'created_at'   =>  '2017-05-30 00:00:00',
        ];

        $this->insert('items', $data);
    }
}
