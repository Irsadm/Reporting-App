<?php

use Phinx\Migration\AbstractMigration;

class RequestTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        // $request = $this->table('requests');
        // $request->addColumn('user_id', 'integer')
        //         ->addColumn('user2_id', 'integer')
        //         ->addColumn('group_id', 'integer')
        //         ->addColumn('message', 'string')
        //         ->addColumn('status', 'integer', ['default' => '0'])
        //         ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP','update' => 'CURRENT_TIMESTAMP'])
        //         ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
        //         ->addForeignKey('user2_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
        //         ->addForeignKey('group_id', 'groups', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
        //         ->create();
    }
}
