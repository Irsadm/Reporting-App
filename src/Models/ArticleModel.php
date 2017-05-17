<?php

namespace App\Models;

class ArticleModel extends BaseModel
{
	protected $table = 'articles';
	protected $column = ['title', 'content', 'image', 'deleted'];

	public function add(array $data, $images)
	{
		$data = [
			'title' 	=> 	$data['title'],
			'content'	=>	$data['content'],
			'image'		=>	$images,
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
        // $query = $qb->execute();

        return $this;
    }

    public function search($title)
    {
    	$qb = $this->db->createQueryBuilder();

        $this->query = $qb->select($this->column)
                 ->from($this->table)
                 ->where('title LIKE "%'.$title.'%" AND deleted = 0');
        $result = $qb->execute();
        return $result->fetchAll();
    }

}

?>
