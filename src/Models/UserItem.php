<?php

namespace App\Models;

class UserItem extends BaseModel
{
    protected $table = 'user_item';
    protected $jointTable = 'items';
    protected $column = ['item_id', 'user_id', 'status', 'group_id'];

    public function setItem(array $data, $group)
    {
        $datas =
        [
            "user_id" => $data['user_id'],
            "item_id" => $data['item_id'],
            "group_id" => $group,
        ];

        $this->createData($datas);
    }

    public function setStatusItem($id)
    {
        $qb = $this->db->createQueryBuilder();

        $qb->update($this->table)
            ->set('status', 1)
            ->where('id = ' . $id)
            ->execute();
    }

    public function findUser($column1, $val1, $column2, $val2)
    {
        $param1 = ':'.$column1;
        $param2 = ':'.$column2;
        $qb = $this->db->createQueryBuilder();
        $qb->select('*')
            ->from($this->table)
            ->setParameter($param1, $val1)
            ->setParameter($param2, $val2)
            ->where($column1 . ' = '. $param1 . '&&' . $column2 . '=' . $param2);
        $result = $qb->execute();
        return $result->fetch();
    }

    public function findAll($column1, $val1)
    {
        $param1 = ':'.$column1;
        $qb = $this->db->createQueryBuilder();
        $qb->select('*')
            ->from($this->table)
            ->setParameter($param1, $val1)
            ->where($column1 . ' = '. $param1);
        $result = $qb->execute();
        return $result->fetchAll();
    }

    public function getItemGroup($groupId, $userId)
    {
        $qb = $this->db->createQueryBuilder();

        $parameters = [
            ':user_id' => $userId,
            ':group_id' => $groupId
        ];

        $this->query = $qb->select('it.name', 'it.description', 'it.recurrent', 'it.start_date', 'it.end_date', 'it.status')
        ->from($this->jointTable, 'it')
        ->join('it', $this->table, 'ui', 'ui.item_id = it.id')
        ->where('ui.user_id = :user_id')
        ->andWhere('ui.group_id = :group_id')
        ->setParameters($parameters);

        // $result = $qb->execute();

        return $this;
    }

    public function getItem($id)
    {
        $qb = $this->db->createQueryBuilder();


        $this->query = $qb->select('it.name', 'it.description', 'it.recurrent', 'it.start_date', 'it.end_date', 'it.status')
        ->from($this->jointTable, 'it')
        ->join('it', $this->table, 'ui', 'ui.item_id = it.id')
        ->where('ui.user_id = :user_id')
        ->setParameter(':user_id', $id);

        return $this;
    }

    public function getItemInGroup($groupId, $userId)
    {
        $qb = $this->db->createQueryBuilder();

        $parameters = [
            ':user_id' => $userId,
            ':group_id' => $groupId
        ];

     $qb->select('it.name', 'ui.id', 'ui.reported_at', 'it.description', 'it.recurrent', 'it.start_date', 'it.end_date', 'it.status')
        ->from($this->jointTable, 'it')
        ->join('it', $this->table, 'ui', 'ui.item_id = it.id')
        ->where('ui.user_id = :user_id')
        ->andWhere('ui.group_id = :group_id')
        ->andWhere('ui.status = 0')
        ->setParameters($parameters);

        $result = $qb->execute();

        return $result->fetchAll();
    }

    public function getDoneItemInGroup($groupId, $userId)
    {
        $qb = $this->db->createQueryBuilder();

        $parameters = [
            ':user_id' => $userId,
            ':group_id' => $groupId
        ];

     $qb->select('it.name', 'ui.id', 'ui.reported_at', 'it.description', 'it.recurrent', 'it.start_date', 'it.end_date', 'it.status')
        ->from($this->jointTable, 'it')
        ->join('it', $this->table, 'ui', 'ui.item_id = it.id')
        ->where('ui.user_id = :user_id')
        ->andWhere('ui.group_id = :group_id')
        ->andWhere('ui.status =  1 ')
        ->setParameters($parameters);

        $result = $qb->execute();

        return $result->fetchAll();
    }

    public function setStatusItems($id)
    {
        $date = date('Y-m-d H:i:s');
        $data = [
            'status'        => 1,
            'reported_at'   => $date
        ];

        $this->updateData($data, $id);
    }

    public function resetStatusItems($id)
    {
        $date = date('Y-m-d H:i:s');
        $data = [
            'status'        => 0,
            'reported_at'   => $date
        ];

        $this->updateData($data, $id);
    }
}
