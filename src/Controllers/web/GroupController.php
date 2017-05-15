<?php

namespace App\Controllers\web;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\GroupModel;
use App\Models\UserGroupModel;

class GroupController extends BaseController
{
	//Get active Group
	function index($request, $response)
	{
		$group = new GroupModel($this->db);
		$article = new \App\Models\ArticleModel($this->db);
		$user = new \App\Models\Users\UserModel($this->db);
		$item = new \App\Models\Item($this->db);

		$getGroup = $group->getAll();

		$countGroup = count($getGroup);
		$countArticle = count($article->getAll());
		$countUser = count($user->getAll());
		$countItem = count($item->getAll());

		$data = $this->view->render($response, 'admin/group/index.twig', [
			'groups' => $getGroup,
			'counts'=> [
				'group' => $countGroup,
				'article' => $countArticle,
				'user' => $countUser,
				'item' => $countItem,
			]
		]);

		return $data;
	}

	//Get inactive group
	function inActive($request, $response)
	{
		$group = new GroupModel($this->db);
		$article = new \App\Models\ArticleModel($this->db);
		$user = new \App\Models\Users\UserModel($this->db);
		$item = new \App\Models\Item($this->db);

		$getGroup = $group->getInActive();

		$countGroup = count($getGroup);
		$countArticle = count($article->getAll());
		$countUser = count($user->getAll());
		$countItem = count($item->getAll());

		$data = $this->view->render($response, 'admin/group/inactive.twig', [
			'groups' => $getGroup,
			'counts'=> [
				'group' => $countGroup,
				'article' => $countArticle,
				'user' => $countUser,
				'item' => $countItem,
			]
		]);

		return $data;
	}

	//Find group by id
	function findGroup($request, $response, $args)
	{
		$group = new GroupModel($this->db);
		$userGroup = new UserGroupModel($this->db);

		$findGroup = $group->find('id', $args['id']);
		$finduserGroup = $userGroup->findUsers('group_id', $args['id']);
		$countUser = count($finduserGroup);

		$data = $this->view->render($response, 'admin/group/detail.twig', [
			'group' => $findGroup,
			'counts'=> [
				'user' => $countUser,
			]
		]);

		return $data;
	}

	//Get create group
	public function getAdd($request, $response)
	{
		return $this->view->render($response, 'admin/group/add.twig');
	}

	//Post create group
	public function add($request, $response)
	{
        $storage = new \Upload\Storage\FileSystem('assets/images');
        $image = new \Upload\File('image',$storage);
        $image->setName(uniqid());
        $image->addValidations(array(
            new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
            'image/jpg', 'image/jpeg')),
            new \Upload\Validation\Size('5M')
        ));

        $dataImg = array(
          'name'       => $image->getNameWithExtension(),
          'extension'  => $image->getExtension(),
          'mime'       => $image->getMimetype(),
          'size'       => $image->getSize(),
          'md5'        => $image->getMd5(),
          'dimensions' => $image->getDimensions()
        );
		$rules = ['required' => [['name'], ['description']] ];
		$this->validator->rules($rules);

		$this->validator->labels([
			'name' 			=>	'Name',
			'description'	=>	'Description',
			'image'			=>	'Image',
		]);

		$data = [
			'name' 			=>	$request->getParams()['name'],
			'description'	=>	$request->getParams()['description'],
			'image'			=>	$dataImg['name'],
		];

		if ($this->validator->validate()) {
			$image->upload();
			$group = new GroupModel($this->db);
			$addGroup = $group->add($data);

			$this->flash->addMessage('succes', 'Data successfully added');

			return $response->withRedirect($this->router
							->pathFor('create.group.get'));
		} else {
			$_SESSION['old'] = $request->getParams();
			$_SESSION['errors'] = $this->validator->errors();
			return $response->withRedirect($this->router->pathFor('create.group.get'));
		}
	}

	//Get edit group
	public function getUpdate($request, $response, $args)
	{
		$group = new GroupModel($this->db);
        $data['group'] = $group->find('id', $args['id']);
		return $this->view->render($response, 'admin/group/edit.twig', $data);
	}

	//Post Edit group
	public function update($request, $response, $args)
	{
		$group = new GroupModel($this->db);
		$rules = ['required' => [['name'], ['description']] ];

		$this->validator->rules($rules);
		$this->validator->labels([
						'name' 			=>	'Name',
						'description'	=>	'Description',
						'image'			=>	'Image',
						]);

		if ($this->validator->validate()) {
			if (!empty($file)) {

				$storage = new \Upload\Storage\FileSystem('assets/images');
				$file = new \Upload\File('image', $storage);
				$file->setName(uniqid());
				$file->addValidations(array(
				new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
				'image/jpg', 'image/jpeg')),
				new \Upload\Validation\Size('5M')
				));

				$dataImg = array(
				'name'       => $file->getNameWithExtension(),
				'extension'  => $file->getExtension(),
				'mime'       => $file->getMimetype(),
				'size'       => $file->getSize(),
				'md5'        => $file->getMd5(),
				'dimensions' => $file->getDimensions()
				);

				$data = [
				'name' 			=>	$request->getParams()['name'],
				'description'	=>	$request->getParams()['description'],
				'image'			=>	$dataImg['name'],
				];

				$file->upload();
				$group->updateData($data, $args['id']);
			} else {
				$group->updateData($request->getParams(), $args['id']);
			}
			return $response->withRedirect($this->router->pathFor('group.list'));
		} else {
			$_SESSION['old'] = $request->getParams();
			$_SESSION['errors'] = $this->validator->errors();
			return $response->withRedirect($this->router
			->pathFor('edit.group.get', ['id' => $args['id']]));
		}
	}

	//Set inactive/soft delete group
	public function setInactive($request, $response)
	{
		foreach ($request->getParam('group') as $key => $value) {
			$group = new GroupModel($this->db);
			$group_del = $group->softDelete($value);
		}

		return $response->withRedirect($this->router->pathFor('group.list'));
	}

	//Set active/restore group
	public function setActive($request, $response)
	{
		if (!empty($request->getParams()['restore'])) {
			foreach ($request->getParam('group') as $key => $value) {
				$group = new GroupModel($this->db);
				$group_del = $group->restore($value);
			}
		} elseif (!empty($request->getParams()['delete'])) {
			foreach ($request->getParam('group') as $key => $value) {
				$group = new GroupModel($this->db);
				$group_del = $group->hardDelete($value);
			}
		}

		return $response->withRedirect($this->router->pathFor('group.inactive'));
	}

	//Set user as member, PIC or guardian of group
	public function setUserGroup($request, $response)
	{
		$userGroup = new UserGroupModel($this->db);
		$groupId = $request->getParams()['id'];

		if (!empty($request->getParams()['pic'])) {
			foreach ($request->getParam('user') as $key => $value) {
				$finduserGroup = $userGroup->findUser('id', $value, 'group_id', $groupId);
				$userGroup->setPic($finduserGroup['id']);
			}
		} elseif (!empty($request->getParams()['member'])) {
			foreach ($request->getParam('user') as $key => $value) {
				$finduserGroup = $userGroup->findUser('id', $value, 'group_id', $groupId);
				$userGroup->setUser($finduserGroup['id']);
			}
		} elseif (!empty($request->getParams()['guard'])) {
			foreach ($request->getParam('user') as $key => $value) {
				$finduserGroup = $userGroup->findUser('id', $value, 'group_id', $groupId);
				$userGroup->setGuardian($finduserGroup['id']);
			}
		} elseif (!empty($request->getParams()['delete'])) {
			foreach ($request->getParam('user') as $key => $value) {
				$finduserGroup = $userGroup->findUser('id', $value, 'group_id', $groupId);
				$userGroup->hardDelete($finduserGroup['id']);
			}
		}

		return $response->withRedirect($this->router
		->pathFor('user.group.get', ['id' => $groupId]));
	}

	//Get all user in group
	public function getMemberGroup($request, $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);

		$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$users = $userGroup->findAll($args['id'])->setPaginate($page, 10);

		$data = $this->view->render($response, 'admin/group/member.twig', [
		'users' => $users['data'],
		'group_id'	=> $args['id']
		]);
		return $data;
	}

	//Get all user in group
	public function getNotMember($request, $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);

		$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$users = $userGroup->notMember($args['id'])->setPaginate($page, 10);

		$data = $this->view->render($response, 'admin/group/not-member.twig', [
		'users' => $users['data'],
		'group_id'	=> $args['id']
		]);
		return $data;
	}

	//Set user as member of group
	public function setMemberGroup($request, $response)
	{
		$userGroup = new UserGroupModel($this->db);

		$groupId = $request->getParams()['group_id'];


		if (!empty($request->getParams()['member'])) {
			foreach ($request->getParam('user') as $key => $value) {

				$data = [
				'group_id' 	=> 	$groupId,
				'user_id'	=>	$value,
				];

				$addMember = $userGroup->add($data);
			}
		}

		return $response->withRedirect($this->router
		->pathFor('all.users.get', ['id' => $groupId]));
	}
}

?>
