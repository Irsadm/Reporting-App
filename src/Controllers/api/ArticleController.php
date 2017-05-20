<?php

namespace App\Controllers\api;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\ArticleModel;


class ArticleController extends BaseController
{
	public function index(Request $request, Response $response)
	{
		$article = new \App\Models\ArticleModel($this->db);

		$getArticle = $article->getAll();

		$countArticles = count($getArticle);

		if ($getArticle) {
			$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
			$get = $article->paginate($page, $getArticle, 10);
			if ($get) {
				$data = $this->responseDetail(200, 'Data Available', $get,
				 $this->paginate($countArticles, 10, $page, ceil($countArticles/10)));
			} else {
				$data = $this->responseDetail(404, 'Error', 'Data Not Found');
			}
		} else {
			$data = $this->responseDetail(204, 'Succes', 'No Content');
		}

		return $data;
	}

	public function add(Request $request, Response $response)
	{
		$rules = [
			'required' => [
				['title'],
				['content'],
				['image'],
			]
		];
		$this->validator->rules($rules);

		$this->validator->labels([
		'title' 	=>	'Title',
		'content'	=>	'Content',
		'image'		=>	'Image',
		]);

		if ($this->validator->validate()) {
			$article = new \App\Models\ArticleModel($this->db);
			$add = $article->add($request->getParsedBody());

			$findArticle = $article->find('id', $add);

			$data = $this->responseDetail(201, 'Succes Add Article', $findArticle);

		} else {
			$data = $this->responseDetail(400, 'Errors', $this->validator->errors());
		}

		return $data;
	}

	//Edit article
	public function update(Request $request, Response $response, $args)
	{
		$article = new \App\Models\ArticleModel($this->db);
		$findArticle = $article->find('id', $args['id']);
		if ($findArticle) {
			$article->updateData($request->getParsedBody(), $args['id']);

			$afterUpdate = $article->find('id', $args['id']);

			$data = $this->responseDetail(200, 'Success Update Data', $afterUpdate);
		} else {
			$data = $this->responseDetail(404, 'Error', 'Data Not Found');
		}

		return $data;
	}

	//Delete article
	public function delete(Request $request, Response $response, $args)
	{
		$article = new \App\Models\ArticleModel($this->db);
		$findArticle = $article->find('id', $args['id']);

		if ($findArticle) {
			$article->hardDelete($args['id']);

			$data = $this->responseDetail(200, 'Succes', 'Data Has Been Delete');
		} else {
			$data = $this->responseDetail(404, 'Error', 'Data Not Found');
		}

		return $data;
	}
}


?>
