<?php

namespace App\Controllers\web;
use App\Models\Users\UserModel;

class UserController extends BaseController
{
    public function listUser($request, $response)
    {
        $user = new UserModel($this->db);
        $datauser = $user->getAllUser();
        $data['user'] = $datauser;
        return $this->view->render($response, 'admin/users/list.twig', $data);
    }

    public function getCreateUser($request, $response)
    {
        return  $this->view->render($response, 'admin/users/add.twig');
    }

    public function postCreateUser($request, $response)
    {
        $storage = new \Upload\Storage\FileSystem('assets/images');
        $image = new \Upload\File('image',$storage);
        $image->setName(uniqid());
        $image->addValidations(array(
            new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
            'image/jpg', 'image/jpeg')),
            new \Upload\Validation\Size('5M')
        ));
        $data = array(
          'name'       => $image->getNameWithExtension(),
          'extension'  => $image->getExtension(),
          'mime'       => $image->getMimetype(),
          'size'       => $image->getSize(),
          'md5'        => $image->getMd5(),
          'dimensions' => $image->getDimensions()
        );
        $user = new UserModel($this->db);
        $this->validator
            ->rule('required', ['username', 'password', 'name', 'email',
                    'phone', 'address', 'gender'])
            ->message('{field} must not be empty')
            ->label('Username', 'password', 'name', 'Password', 'Email', 'Address');
        $this->validator
            ->rule('integer', 'id');
        $this->validator
            ->rule('email', 'email');
        $this->validator
            ->rule('alphaNum', 'username');
        $this->validator
             ->rule('lengthMax', [
                'username',
                'name',
                'password'
             ], 30);
        $this->validator
             ->rule('lengthMin', [
                'username',
                'name',
                'password'
             ], 5);

        if ($this->validator->validate()) {
            $image->upload();
            $register = $user->checkDuplicate($request->getParam('username'),
                        $request->getParam('email'));

            if ($register == 1) {
                $_SESSION['old'] = $request->getParams();
                $this->flash->addMessage('warning', 'Username, already used');

                return $response->withRedirect($this->router->pathFor('user.create'));
            } elseif ($register == 2) {
                $_SESSION['old'] = $request->getParams();
                $this->flash->addMessage('warning', 'Email, already used');
                return $response->withRedirect($this->router->pathFor('user.create'));
            } else {
                $user->createUser($request->getParams(), $data['name']);
                $this->flash->addMessage('succes', 'Create Data Succes');

                return $response->withRedirect($this->router->pathFor('user.list.all'));
            }
        } else {
            $_SESSION['errors'] = $this->validator->errors();
            $_SESSION['old'] = $request->getParams();

            return $response->withRedirect($this->router
                    ->pathFor('user.create'));
        }
    }

    public function getUpdateData($request, $response, $args)
    {
        $user = new UserModel($this->db);
        $profile = $user->find('id', $args['id']);
        $data['data'] = $profile;
        return $this->view->render($response, 'admin/users/edit.twig', $data);
    }

    public function postUpdateData($request, $response, $args)
    {
        $user = new UserModel($this->db);
        $this->validator
            ->rule('required', ['username', 'name', 'email', 'phone', 'address', 'gender'])
            ->message('{field} must not be empty')
            ->label('Username', 'name', 'Password', 'Email', 'Address');
        $this->validator
            ->rule('integer', 'id');
        $this->validator
            ->rule('email', 'email');
        $this->validator
            ->rule('alphaNum', 'username');
        $this->validator
             ->rule('lengthMax', [
                'username',
                'name',
                'password'
             ], 30);
        $this->validator
             ->rule('lengthMin', [
                'username',
                'name',
                'password'
             ], 5);
        if ($this->validator->validate()) {
            if (!empty($_FILES['image']['name'])) {
                $storage = new \Upload\Storage\FileSystem('assets/images');
                $image = new \Upload\File('image', $storage);
                $image->setName(uniqid());
                $image->addValidations(array(
                    new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
                    'image/jpg', 'image/jpeg')),
                    new \Upload\Validation\Size('5M')
                ));
                $data = array(
                    'name'       => $image->getNameWithExtension(),
                    'extension'  => $image->getExtension(),
                    'mime'       => $image->getMimetype(),
                    'size'       => $image->getSize(),
                    'md5'        => $image->getMd5(),
                    'dimensions' => $image->getDimensions()
                );
                $image->upload();
                $user->update($request->getParams(), $data['name'], $args['id']);
            } else {
                $user->updateUser($request->getParams(), $args['id']);
            }
            return $response->withRedirect($this->router->pathFor('user.list.all'));
        } else {
            $_SESSION['old'] = $request->getParams();
            $_SESSION['errors'] = $this->validator->errors();
            return $response->withRedirect($this->router
                ->pathFor('user.edit.data', ['id' => $args['id']]));
        }
    }

    public function softDelete($request, $response, $args)
    {
        $user = new UserModel($this->db);
        $sofDelete = $user->softDelete($args['id']);
        $this->flash->addMessage('remove', '');
        return $response->withRedirect($this->router
                        ->pathFor('user.list.all'));
    }

    public function hardDelete($request, $response, $args)
    {
        $user = new UserModel($this->db);
        $hardDelete = $user->hardDelete($args['id']);
        $this->flash->addMessage('delete', '');
        return $response->withRedirect($this->router
                        ->pathFor('user.trash'));
    }

    public function trashUser($request, $response)
    {
        $user = new UserModel($this->db);
        $datauser = $user->trash();
        $data['usertrash'] = $datauser;
        return $this->view->render($response, 'admin/users/trash.twig', $data);
    }

    public function restoreData($request, $response, $args)
    {
        $user = new UserModel($this->db);
        $restore = $user->restoreData($args['id']);
        $this->flash->addMessage('restore', '');
        return $response->withRedirect($this->router
                        ->pathFor('user.trash'));
    }

    public function getRegister($request, $response)
    {
        return  $this->view->render($response, 'templates/auth/register.twig');
    }

    public function postRegister($request, $response)
    {

        $this->validator
            ->rule('required', ['username', 'password', 'name', 'email', 'phone', 'gender'])
            ->message('{field} must not be empty')
            ->label('Username', 'password', 'name', 'Password', 'Email', 'Address');
        $this->validator
            ->rule('integer', 'id');
        $this->validator
            ->rule('email', 'email');
        $this->validator
            ->rule('alphaNum', 'username');
        $this->validator
             ->rule('lengthMax', [
                'username',
                'name',
                'password'
             ], 30);

        $this->validator
             ->rule('lengthMin', [
                'username',
                'name',
                'password'
             ], 5);

        if ($this->validator->validate()) {
            $user = new UserModel($this->db);
            $register = $user->checkDuplicate($request->getParam('username'),
                        $request->getParam('email'));

            if ($register == 1) {
                $_SESSION['old'] = $request->getParams();
                $this->flash->addMessage('warning', 'Username, already used');
                    return $response->withRedirect($this->router->pathFor('register'));
            } elseif ($register == 2) {
                $_SESSION['old'] = $request->getParams();
                $this->flash->addMessage('warning', 'Email, already used');
                return $response->withRedirect($this->router->pathFor('register'));
            } else {
                $registers = $request->getParams();
                $user->register($registers);
                $this->flash->addMessage('succes', 'Register Succes, Please Login');
                return $response->withRedirect($this->router->pathFor('register'));
            }
        } else {
            $_SESSION['errors'] = $this->validator->errors();
            $_SESSION['old'] = $request->getParams();

            $this->flash->addMessage('info');
            return $response->withRedirect($this->router
                    ->pathFor('register'));
        }
    }

    public function getLoginAsAdmin($request, $response)
    {
        return  $this->view->render($response, 'templates/auth/login-admin.twig');
    }

    public function loginAsAdmin($request, $response)
    {
        $user = new UserModel($this->db);
        $login = $user->find('username', $request->getParam('username'));
        if (empty($login)) {
            $this->flash->addMessage('warning', ' Username is not registered');
            return $response->withRedirect($this->router
                    ->pathFor('login.admin'));
        } else {
            if (password_verify($request->getParam('password'),
                $login['password'])) {
                $_SESSION['login'] = $login;
                if ($_SESSION['login']['status'] == 1) {
                    // $this->flash->addMessage('succes', 'Congratulations you have successfully logged in as admin');
                    return $response->withRedirect($this->router->pathFor('home'));
                } else {
                    if (isset($_SESSION['login']['status'])) {
                        $this->flash->addMessage('error', 'You are not admin');
                        return $response->withRedirect($this->router
                                ->pathFor('login.admin'));
                    }
                }
            } else {
                $this->flash->addMessage('warning', ' Password is not registered');
                return $response->withRedirect($this->router
                        ->pathFor('login.admin'));
            }
        }
    }

    public function getLogin($request, $response)
    {
        return  $this->view->render($response, 'templates/auth/register.twig');
    }

    public function login($request, $response)
    {
        // var_dump($request->getParams()['optlogin']);die();
        $user = new UserModel($this->db);
        $group =  new \App\Models\UserGroupModel($this->db);
        $guardian =  new \App\Models\GuardModel($this->db);

        $login = $user->find('username', $request->getParam('username'));
        $users = $guardian->findAllUser($login['id']);
        $groups = $group->findAllGroup($login['id']);

        if (empty($login)) {
            $this->flash->addMessage('warning', 'Username is not registered!');
            return $response->withRedirect($this->router
            ->pathFor('login'));
        } else {
            if (password_verify($request->getParam('password'),$login['password'])) {

                $_SESSION['login'] = $login;
                if ($_SESSION['login']['status'] == 0 &&
                $request->getParam('optlogin') == 'user') {
                    $_SESSION['user_group'] = $groups;

                    $this->flash->addMessage('succes', 'Successfully logged in as User');
                    return $response->withRedirect($this->router->pathFor('home'));
                }
                elseif ($_SESSION['login']['status'] == 0 &&
                $request->getParam('optlogin') == 'guard') {
                    $_SESSION['guard'] = [
                        'user' => $users,
                        'status'=> $request->getParam('optlogin'),
                    ];

                    $this->flash->addMessage('succes', 'Successfully logged in as Guardian');
                    return $response->withRedirect($this->router->pathFor('home'));
                } else {
                    $this->flash->addMessage('warning', 'You Are Not User');
                    return $response->withRedirect($this->router->pathFor('login'));
                }

            } else {
                $this->flash->addMessage('warning', 'Password incorrect!');
                return $response->withRedirect($this->router->pathFor('login'));
            }
        }
    }

    public function logout($request, $response)
    {
        if ($_SESSION['login']['status'] == 0) {
            session_destroy();
            return $response->withRedirect($this->router->pathFor('login'));

        } elseif ($_SESSION['login']['status'] == 1) {
            session_destroy();
            return $response->withRedirect($this->router->pathFor('login.admin'));
        }
    }

    public function viewProfile($request, $response)
    {
        return  $this->view->render($response, '/users/profile.twig');
    }

    public function getSettingAccount($request, $response)
    {
        return  $this->view->render($response, '/users/setting.twig');
    }

    public function settingAccount($request, $response)
    {
        $user = new UserModel($this->db);
        $this->validator
            ->rule('required', ['username', 'name', 'email', 'phone', 'address', 'gender'])
            ->message('{field} must not be empty');
            // ->label('Username', 'Name', 'Password', 'Email', 'Address');
        $this->validator
            ->rule('integer', 'id');
        $this->validator
            ->rule('email', 'email');
        $this->validator
            ->rule('alphaNum', 'username');
        $this->validator
             ->rule('lengthMax', [
                'username',
                'name',
                'password'
             ], 30);
        $this->validator
             ->rule('lengthMin', [
                'username',
                'name',
                'password'
             ], 5);

        if ($this->validator->validate()) {
            if (!empty($_FILES['image']['name'])) {
                $storage = new \Upload\Storage\FileSystem('assets/images');
                $image = new \Upload\File('image', $storage);
                $image->setName(uniqid());
                $image->addValidations(array(
                    new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
                    'image/jpg', 'image/jpeg')),
                    new \Upload\Validation\Size('5M')
                ));
                $data = array(
                    'name'       => $image->getNameWithExtension(),
                    'extension'  => $image->getExtension(),
                    'mime'       => $image->getMimetype(),
                    'size'       => $image->getSize(),
                    'md5'        => $image->getMd5(),
                    'dimensions' => $image->getDimensions()
                );
                $image->upload();
                $user->update($request->getParams(), $data['name'],$request->getParams()['id']);
            } else {
                $user->updateUser($request->getParams(), $request->getParams()['id']);
            }

            $login = $user->find('id', $request->getParams()['id']);
            $_SESSION['login'] = $login;

            return $response->withRedirect($this->router->pathFor('user.setting'));
        } else {

            $_SESSION['old'] = $request->getParams();
            $_SESSION['errors'] = $this->validator->errors();

            return $response->withRedirect($this->router
                            ->pathFor('user.setting', ['id' => $args['id']]));
        }
    }

    public function enterGroup($request,$response, $args)
    {
        $userGroup = new \App\Models\UserGroupModel($this->db);

        $userId  = $_SESSION['login']['id'];
        $user = $userGroup->findUser('group_id', $args['id'], 'user_id', $userId);

        $_SESSION['group'] = $user['group_id'];
        $reported = $request->getQueryParam('reported');

        if ($user['status'] == 1 && $reported) {
            return $this->getItemInGroup($request,$response, $args);

        } elseif ($user['status'] == 1) {
            $_SESSION['pic'] = $user['group_id'];

            return $response->withRedirect($this->router
                            ->pathFor('pic.group.detail', ['id' => $args['id']]));
        } elseif ($user['status'] == 0) {
            return $this->getItemInGroup($request,$response, $args);
        }
    }

    public function getItemInGroup($request,$response, $args)
    {
        $item = new \App\Models\Item($this->db);
        $userItem = new \App\Models\UserItem($this->db);
        $userGroup = new \App\Models\UserGroupModel($this->db);

        $userId  = $_SESSION['login']['id'];
        $user = $userGroup->findUser('group_id', $args['id'], 'user_id', $userId);

        if ($user) {

        $findUserItem['items'] = $userItem->getItemInGroup($user['id']);
        $findUserItem['itemdone'] = $userItem->getDoneItemInGroup($user['id']);

            $count = count($findUserItem['itemdone']);
            $reported = $request->getQueryParam('reported');

            return $this->view->render($response, 'users/useritem.twig', [
                'itemdone' => $findUserItem['itemdone'],
                'items' => $findUserItem['items'],
                'status'=> $user['status'],
                'group_id' => $args['id'],
                'reported'=> $reported,
                'count'=> $count,
            ]);
        } else {
            $this->flash->addMessage('error', 'You are not allowed to access this group!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
    }

    public function getItemUser($request,$response, $args)
    {
        $user = new UserModel($this->db);
        $item = new \App\Models\Item($this->db);
        $guard = new \App\Models\GuardModel($this->db);
        $userItem = new \App\Models\UserItem($this->db);

        $guardId = $_SESSION['login']['id'];
        $userItems = $userItem->getItem($args['id']);
        $userGuard = $guard->findGuard('guard_id', $guardId, 'user_id', $args['id']);
        $findUser = $user->find('id', $args['id']);
        // var_dump($userItems);die();

        if ($userGuard && $_SESSION['guard']['status'] == 'guard' ) {
            return $this->view->render($response, 'guardian/useritem.twig', [
                'items' => $userItems,
                'user' => $findUser,
                'count'=> count($userItems),
            ]);

        } else {
            $this->flash->addMessage('error', 'You are not allowed to access this user!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
    }
    public function getItemByadmin($request,$response, $args)
    {
        $user = new UserModel($this->db);
        $item = new \App\Models\Item($this->db);
        $userItem = new \App\Models\UserItem($this->db);

        $userItems = $userItem->getItem($args['id']);
        $findUser = $user->find('id', $args['id']);
        // var_dump($userItems);die();

        if ( $_SESSION['login']['status'] == '1' ) {
            return $this->view->render($response, 'guardian/useritem.twig', [
                'items' => $userItems,
                'user' => $findUser,
                'count'=> count($userItems),
            ]);

        } else {
            $this->flash->addMessage('error', 'You are not allowed to access this user!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
    }

    public function getNotUser($request, $response, $args)
	{
		$guard = new \App\Models\GuardModel($this->db);
        $user = new UserModel($this->db);

        $guardId = $_SESSION['login']['id'];
        $find = $guard->find('guard_id', $guardId);
        $status = $_SESSION['guard']['status'];

        if ($_SESSION['login']['id'] == $args['id'] && $_SESSION['guard']['status'] == 'guard') {
            if ($find) {
                $users = $guard->notUser($args['id'])->fetchAll();
            } else {
                $users = $user->getAllUser();
            }

            $guardUser = $guard->findAllUser($guardId);

            $_SESSION['guard'] = [
                'user' => $guardUser,
                'status'=> $status,
                ];

            return $this->view->render($response, 'guardian/not-user.twig', [
                'users' => $users,
                'guard_id'	=> $args['id']
            ]);

        } else {
            $this->flash->addMessage('error', 'You can only add user to your own!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
	}

    public function setGuardUser($request, $response)
    {
        $guard = new \App\Models\GuardModel($this->db);

        $guardId = $_SESSION['login']['id'];
        $status = $_SESSION['guard']['status'];

        if (!empty($request->getParams()['setuser'])) {
            foreach ($request->getParam('user') as $value) {

                $data = [
                'guard_id' 	=> 	$guardId,
                'user_id'	=>	$value,
                ];

                $addUser = $guard->createData($data);
            }

            $users = $guard->findAllUser($guardId);

            $_SESSION['guard'] = [
                'user' => $users,
                'status'=> $status,
                ];
        }

        return $response->withRedirect($this->router
        ->pathFor('get.user.add', ['id' => $guardId]));
    }

    public function delGuardUser($request, $response, $args)
    {
        $guard = new \App\Models\GuardModel($this->db);

        $guardId = $_SESSION['login']['id'];
        $status = $_SESSION['guard']['status'];
        $findId = $guard->findGuard('user_id', $args['id'], 'guard_id', $guardId);
        // var_dump($findId);die();

        if ($findId && $status == 'guard') {

            $guard->hardDelete($findId['id']);

            $users = $guard->findAllUser($guardId);

            $_SESSION['guard'] = [
                'user' => $users,
                'status'=> $status,
                ];
        }

        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function testMail($request, $response)
    {
        $name = 'MIT SChool';
        $data = [
			'subject' 	=>	'Test mail',
            'from'      =>	'nurud13@gmail.com',
            'to'	    =>	'reportingmit@gmail.com',
            'sender'	=>	'administrator',
            'receiver'	=>	'admin',
			'content'	=>	'Testing swift mail with slim framework by '. $name,
		];

        $mailer = new \App\Extensions\Mailers\Mailer();

        $result = $mailer->send($data);
        var_dump($result);die();


    }

    public function setItemUserStatus($request, $response, $args)
    {
        $items = new \App\Models\Item($this->db);
        $mailer = new \App\Extensions\Mailers\Mailer();
        $guards = new \App\Models\GuardModel($this->db);
        $userItems = new \App\Models\UserItem($this->db);
        $users = new \App\Models\Users\UserModel($this->db);
        $userGroups = new \App\Models\UserGroupModel($this->db);

        $groupId = $_SESSION['group'];
        $userId  = $_SESSION['login']['id'];
        $username  = $_SESSION['login']['name'];
        $user = $userGroups->findUser('group_id', $groupId, 'user_id', $userId);
        $item = $items->find('id', $args['id']);
        $guardian = $guards->find('user_id', $userId);
        $guard = $users->find('id', $guardian['guard_id']);
        $picGroup = $userGroups->findUser('group_id', $groupId, 'status', 1);
        $pic = $users->find('id', $picGroup['user_id']);
        // var_dump($pic);die();
        $setItem = $userItems->setStatusItems($args['id']);
        $date = date('d M Y H:i:s');
        $report = $username .' has completed '. $item['name'] .' on '. $date;

        if ($guard) {
            $dataGuard = [
                'subject' 	=>	$username.' item report',
                'from'      =>	'reportingmit@gmail.com',
                'to'	    =>	$guard['email'],
                'sender'	=>	'administrator',
                'receiver'	=>	$guard['name'],
                'content'	=>	$report,
            ];

            $this->sendWebNotif($report, $guard['id']);
            $mailer->send($dataGuard);
        }

        if ($pic && $pic['id'] != $guard['id']) {
            $data = [
                'subject' 	=>	$username.' item report',
                'from'      =>	'reportingmit@gmail.com',
                'to'	    =>	$pic['email'],
                'sender'	=>	'administrator',
                'receiver'	=>	$pic['name'],
                'content'	=>	$report,
            ];

            $this->sendWebNotif($report, $pic['id']);
            $mailer->send($data2);

        }

        if ($user['status'] == 1) {

            return $response->withRedirect($this->router
            ->pathFor('pic.item.group', ['id' =>$groupId]));
        } elseif ($user['status'] == 0) {

            return $response->withRedirect($this->router
            ->pathFor('user.item.group', ['id' =>$groupId]));
        }
    }

    public function restoreItemUserStatus($request, $response, $args)
    {
        $userItem = new \App\Models\UserItem($this->db);
        $userGroup = new \App\Models\UserGroupModel($this->db);

        $setItem = $userItem->resetStatusItems($args['id']);
        // $findGroup = $userItem->find('id', $args['id']);
        $groupId = $_SESSION['group'];
        $userId  = $_SESSION['login']['id'];
        $user = $userGroup->findUser('group_id', $groupId, 'user_id', $userId);

        if ($user['status'] == 1) {

            return $response->withRedirect($this->router
            ->pathFor('pic.item.group', ['id' =>$groupId]));
        } elseif ($user['status'] == 0) {

            return $response->withRedirect($this->router
            ->pathFor('user.item.group', ['id' =>$groupId]));
        }
    }

    public function getChangePassword($request, $response)
    {
        return  $this->view->render($response, '/users/change.twig');
    }

    public function changePassword($request, $response, $args)
    {
        $user = new UserModel($this->db);
        $this->validator
            ->rule('required', 'password')
            ->message('{field} must not be empty');
        $this->validator
             ->rule('lengthMax', [
                'password'
             ], 30);
        $this->validator
             ->rule('equals', 'new_password', 'retype_password');
        $this->validator
             ->rule('lengthMin', [
                'password'
             ], 5);

        if ($this->validator->validate()) {

            if (password_verify($request->getParam('password'), $_SESSION['login']['password'])) {

            $user->changePassword($request->getParams(), $_SESSION['login']['id']);
            return $response->withRedirect($this->router->pathFor('user.setting'));
            } else {

                $this->flash->addMessage('warning', 'The old password you have entered is incorrect');
                return $response->withRedirect($this->router->pathFor('user.change.password'));
            }
        } else {

            $_SESSION['old'] = $request->getParams();
            $_SESSION['errors'] = $this->validator->errors();

            return $response->withRedirect($this->router->pathFor('user.change.password', ['id' => $args['id']]));
        }
    }
}
