<?php

namespace App\Models;

class RegisterModel extends BaseModel
{
	protected $table = 'registers';
	protected $column = ['user_id', 'token', 'expired_date'];

	public function add(array $data)
	{
		$data = [
			'user_id'	 	=> 	$data['user_id'],
			'token'			=>	$data['token'],
			'expired_date'	=>	$data['expired'],
		];
		$this->createData($data);

		return $this->db->lastInsertId();
	}

	public function update(array $data, $images, $id)
	{
		$data = [
			'title' 	=> 	$data['title'],
			'content'	=>	$data['content'],
			'image'		=>	$images,
		];
		$this->updateData($data, $id);
	}

	public function getArticle()
    {
        $qb = $this->db->createQueryBuilder();

		$this->query =$qb->select('*')
            ->from($this->table)
            ->where('deleted = 0');

        return $this;
    }

    public function search($title)
    {
    	$qb = $this->db->createQueryBuilder();
        $this->query = $qb->select('*')
                 ->from($this->table)
                 ->where('title LIKE :title')
                 ->andWhere('deleted = 0')
                 ->setParameter('title', '%'.$title.'%');

        $result = $this->query->execute();

        return $result->fetchAll();
    }

}

?>
