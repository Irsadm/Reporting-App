<?php

use Phinx\Seed\AbstractSeed;

class UserItemSeed extends AbstractSeed
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
            'item_id' =>  1,
            'user_id' =>  2,
            'group_id' =>  2,
        ];

        $data[] = [
            'item_id' =>  2,
            'user_id' =>  2,
            'group_id' =>  2,
        ];

        $data[] = [
            'item_id' =>  1,
            'user_id' =>  3,
            'group_id' =>  2,
        ];

        $data[] = [
            'item_id' =>  2,
            'user_id' =>  3,
            'group_id' =>  2,
        ];

        $data[] = [
            'item_id' =>  1,
            'user_id' =>  4,
            'group_id' =>  2,
        ];

        $data[] = [
            'item_id' =>  2,
            'user_id' =>  4,
            'group_id' =>  2,
        ];

        $this->insert('user_item', $data);
    }
}
