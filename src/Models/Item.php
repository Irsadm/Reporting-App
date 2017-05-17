<?php

namespace App\Models;

class Item extends BaseModel
{
    protected $table = 'items';
    protected $column = ['id', 'name'];
    protected $joinTable = 'groups';

    public function create($data)
    {
        $date = date('Y-m-d H:i:s');
        $data = [
            'name'        => $data['name'],
            'description' => $data['description'],
            'recurrent'   => $data['recurrent'],
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'],
            'group_id'    => $data['group_id'],
            'updated_at'  => $date
        ];

        $this->createData($data);

        return $this->db->lastInsertId();
    }

    public function update($data, $id)
    {
        $date = date('Y-m-d H:i:s');
        $data = [
            'name'        => $data['name'],
            'description' => $data['description'],
            'recurrent'   => $data['recurrent'],
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'],
            'group_id'    => $data['group_id'],
            'updated_at'  => $date
        ];

        $this->updateData($data, $id);

    }

    public function getAllItem()
    {
        $qb = $this->db->createQueryBuilder();

        $qb->select('gr.name as groups', 'it.*')
           ->from($this->table, 'it')
           ->join('it', $this->joinTable, 'gr', 'gr.id = it.group_id')
           ->where('it.deleted = 0');

           $result = $qb->execute();

           return $result->fetchAll();
    }

    public function getAllDeleted()
    {
        $qb = $this->db->createQueryBuilder();

        $qb->select('gr.name as groups', 'it.*')
           ->from($this->table, 'it')
           ->join('it', $this->joinTable, 'gr', 'gr.id = it.group_id')
           ->where('it.deleted = 1');

           $result = $qb->execute();

           return $result->fetchAll();
    }

}
