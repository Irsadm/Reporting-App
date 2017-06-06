<?php

namespace App\Models;

class GroupModel extends BaseModel
{
	protected $table = 'groups';
	protected $column = ['name', 'description', 'image', 'deleted'];

	function add(array $data)
	{
		$data = [
			'name' 			=> 	$data['name'],
			'description'	=>	$data['description'],
			'image'			=>	$data['image'],
			'creator'		=>	$data['creator'],
		];
		$this->createData($data);

		return $this->db->lastInsertId();
	}

	public function getInActive()
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('*')
            ->from($this->table)
            ->where('deleted = 1');
        $query = $qb->execute();
        return $query->fetchAll();
    }

	public function restore($id)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->update($this->table)
		   ->set('deleted', 0)
		   ->where('id = ' . $id)
		   ->execute();
	}
}
?>
