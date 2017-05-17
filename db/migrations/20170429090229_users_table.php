<?php

use Phinx\Migration\AbstractMigration;

class UsersTable extends AbstractMigration
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
        $user = $this->table('users');
        $user->addColumn('name', 'string')
             ->addColumn('email', 'string')
             ->addColumn('username', 'string')
             ->addColumn('password', 'string')
             ->addColumn('gender', 'string')
             ->addColumn('address', 'string', ['null' => true])
             ->addColumn('phone', 'string')
             ->addColumn('image', 'string', ['default' => 'avatar.png'])
             ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP','update' => 'CURRENT_TIMESTAMP'])
             ->addColumn('status', 'integer', ['limit' => 1, 'default' => 0])
             ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
             ->addColumn('deleted', 'integer', ['default' => '0'])
             ->create();
    }
}
