<?php
namespace App\Controllers\web;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\ArticleModel;
class ArticleController extends BaseController
{
    public function getActiveArticle($request, $response, $arg)
    {
        $article = new ArticleModel($this->db);
        $data['article'] = $article->getAll();
        return $this->view->render($response, 'admin/article/article-list-active.twig', $data);
    }

    public function getInactiveArticle($request, $response, $arg)
    {
        $article = new ArticleModel($this->db);
        $article_list = $article->getInactive();
        $data['article'] = $article_list;
        return $this->view->render($response, 'admin/article/article-list-inactive.twig',
        	$data);
    }

    public function setActive($request, $response, $args)
	{
		$article = new ArticleModel($this->db);
		$article_restore = $article->restoreData($args['id']);
		return $response->withRedirect($this->router
						->pathFor('article-list-inactive'));
	}

    public function setInactive($request, $response)
	{
        foreach ($request->getParam('inactive') as $key => $value) {
            $article = new ArticleModel($this->db);
            $article_del = $article->softDelete($value);
        }
		return $response->withRedirect($this->router
						->pathFor('article-list-active'));
	}

    public function getAdd(Request $request, Response $response)
	{
		return $this->view->render($response, 'admin/article/article-add.twig');
	}

    public function add(Request $request, Response $response)
	{
		$storage = new \Upload\Storage\FileSystem('assets/images');
		$file = new \Upload\File('image', $storage);
        $file->setName(uniqid());
        $file->addValidations(array(
            new \Upload\Validation\Mimetype(array('image/gif', 'image/jpg',
            'image/jpeg')),

            new \Upload\Validation\Size('5M')

        ));

        $data = array(
            'name'       => $file->getNameWithExtension(),
            'extension'  => $file->getExtension(),
            'mime'       => $file->getMimetype(),
            'size'       => $file->getSize(),
            'md5'        => $file->getMd5(),
            'dimensions' => $file->getDimensions()
        );
        $article = new ArticleModel($this->db);
		$rules = [
			'required' => [
				['title'],
				['content'],
				// ['image'],
			]
		];

		$this->validator->rules($rules);
		$this->validator->labels([
		'title' 	=>	'Title',
		'content'	=>	'Content',
		'image'		=>	'Image',
		]);
		if ($this->validator->validate()) {
			// Try to upload file
			try {
			    // Success!
			    $file->upload();
			} catch (\Exception $e) {
			    // Fail!
			    $errors = $file->getErrors();

			    $this->flash->addMessage('error', 'Format foto harus JPG, JPEG atau GIF');

			    return $response->withRedirect($this->router->pathFor('article-add'));
			}
			$article->add($request->getParams(), $data['name']);

			$this->flash->addMessage('succes', 'Artikel berhasil dibuat');
			return $response->withRedirect($this->router->pathFor('article-list-active'));
		} else {
			$_SESSION['old'] = $request->getParams();
			$_SESSION['errors'] = $this->validator->errors();
			return $response->withRedirect($this->router->pathFor('article-add'));
		}
	}

    //Read article
	public function readArticle(Request $request, Response $response, $args)
	{
		$article = new ArticleModel($this->db);
        $data['article'] = $article->find('id', $args['id']);
// var_dump($data);die();
		if (!empty($data)) {
			return $this->view->render($response , 'admin/article/article-read.twig', $data);
		} else {
			$this->flash->addMessage('error', 'Artikel tidak ditemukan!');
            return $response->withRedirect($this->router->pathFor('home'));
		}

	}

    //Edit article
	public function getUpdate(Request $request, Response $response, $args)
	{
		$article = new ArticleModel($this->db);
        $data['article'] = $article->find('id', $args['id']);
		return $this->view->render($response, 'admin/article/article-edit.twig', $data);
	}

    public function update(Request $request, Response $response, $args)
	{
		$article = new ArticleModel($this->db);
		$rules = [
			'required' => [
				['title'],
				['content'],
				// ['image'],
			]
		];
		$this->validator->rules($rules);
		$this->validator->labels([
		'title' 	=>	'Title',
		'content'	=>	'Content',
		'image'		=>	'Image',
		]);
		if ($this->validator->validate()) {
			if (!empty($_FILES['image']['name'])) {
				$storage = new \Upload\Storage\FileSystem('assets/images');
				$file = new \Upload\File('image', $storage);
		        $file->setName(uniqid());
		        $file->addValidations(array(

		            new \Upload\Validation\Mimetype(array('image/gif',
		            'image/jpg', 'image/jpeg')),
		            new \Upload\Validation\Size('5M')
		        ));
		        $data = array(
		            'name'       => $file->getNameWithExtension(),
		            'extension'  => $file->getExtension(),
		            'mime'       => $file->getMimetype(),
		            'size'       => $file->getSize(),
		            'md5'        => $file->getMd5(),
		            'dimensions' => $file->getDimensions()
		        );

				// Try to upload file
				try {
				    // Success!
				    $file->upload();
				} catch (\Exception $e) {
				    // Fail!
				    $errors = $file->getErrors();

				    $this->flash->addMessage('error', 'Format foto harus JPG, JPEG atau GIF');

				    return $response->withRedirect($this->router->pathFor('article-edit', ['id' => $args['id']]));
				}

		        $article->update($request->getParams(), $data['name'], $args['id']);
			} else {
				$article->updateData($request->getParams(), $args['id']);
			}
			return $response->withRedirect($this->router->pathFor('article-list-active'));
		} else {
			$_SESSION['old'] = $request->getParams();
			$_SESSION['errors'] = $this->validator->errors();
			return $response->withRedirect($this->router->pathFor('article-edit', ['id' => $args['id']]));
		}
	}

    //Delete article
    public function setDelete($request, $response)
	{
         foreach ($request->getParam('article') as $key => $value) {
            $article = new ArticleModel($this->db);
            $article_del = $article->hardDelete($value);
        }
		return $response->withRedirect($this->router
						->pathFor('article-list-inactive'));
	}
	//Search article
	public function search($request, $response)
	{
		$article = new ArticleModel($this->db);
		$data['search'] = $request->getQueryParam('search');

		$data['article'] = $article->search($request->getQueryParam('search'));

		return $this->view->render($response, 'admin/article/timeline.twig', $data);
	}
}

?>
