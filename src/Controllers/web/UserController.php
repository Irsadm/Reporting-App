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

            if ($register == 0) {
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
        $datauser = $user->getInActiveUser();
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
            $mailer = new \App\Extensions\Mailers\Mailer();
            $registers = new \App\Models\RegisterModel($this->db);

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
                $newUser = $user->register($request->getParams());
                $token = md5(openssl_random_pseudo_bytes(8));
                $findUser = $user->find('id', $newUser);
                $tokenId = $registers->setToken($newUser, $token);
                $userToken = $registers->find('id', $tokenId);

                $base = $request->getUri()->getBaseUrl();
                $keyToken = $userToken['token'];
                $activateUrl = '<a href ='.$base ."/activateaccount/".$keyToken.'><h3>ACTIVATE ACCOUNT</h3></a>';
                $content = "Thank you for registering your Reporting App account.
                To finally activate your account please click the following link. <br /> <br />"
                .$activateUrl.
                "<br /> <br /> If clicking the link doesn't work, you can copy the link into your browser window
                or type it there directly. <br /><br /> "
                .$base ."/activateaccount/".$keyToken.
                " <br /><br /> Regards, <br /><br /> Reporting App Team";

                $mail = [
                    'subject'   =>  'Reporting App - Email validation',
                    'from'      =>  'reportingmit@gmail.com',
                    'to'        =>  $findUser['email'],
                    'sender'    =>  'Reporting App',
                    'receiver'  =>  $findUser['name'],
                    'content'   =>  $content,
                ];

                $result = $mailer->send($mail);

                $this->flash->addMessage('succes', 'Register Succes,
                                Please check your email to activate your account');

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
                    // var_dump($_SESSION['login']['status']);die();
                    $this->flash->addMessage('succes', 'Congratulations you have successfully logged in as admin');
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

                if ($_SESSION['login']['status'] == 2) {
                    $_SESSION['user_group'] = $groups;

                    $this->flash->addMessage('succes', 'Welcome to the reporting app, '. $login['name']);
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
        if ($_SESSION['login']['status'] == 2) {
            session_destroy();
            return $response->withRedirect($this->router->pathFor('login'));

        } elseif ($_SESSION['login']['status'] == 1) {
            session_destroy();
            return $response->withRedirect($this->router->pathFor('login.admin'));
        }
    }

    public function viewProfile($request, $response)
    {
        $user = new UserModel($this->db);
        $guardian = new \App\Models\GuardModel($this->db);
        $group  = new \App\Models\GroupModel($this->db);
        $userGroup = new \App\Models\UserGroupModel($this->db);

        $data['guardian'] = $user->getUserGuardian($_SESSION['login']['id']);
        $data['group1'] = $group->getUserGroup($_SESSION['login']['id'],0);
        $data['group2'] = $group->getUserGroup($_SESSION['login']['id'],1);

        // var_dump($data['guardian']); die();

        return  $this->view->render($response, '/users/profile.twig', $data);
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

            return $this->view->render($response, 'users/user_item.twig', [
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
            return $this->view->render($response, 'guardian/user_item.twig', [
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

        $userItems = $item->finds('user_id', $args['id'], 'user_id', $args['id']);
        $findUser = $user->find('id', $args['id']);

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

    public function setGuardUser($request, $response, $args)
    {
        $users = new \App\Models\Users\UserModel($this->db);
        $guard = new \App\Models\GuardModel($this->db);
        $mailer = new \App\Extensions\Mailers\Mailer();

        $guardId = $_SESSION['login']['id'];
        $findUser = $guard->finds('guard_id', $guardId, 'user_id', $args['id']);

        $data = [
           'guard_id' 	=> 	$guardId,
           'user_id'	=>	$args['id'],
            ];

        $guardName = $_SESSION['login']['name'];
        $user = $users->find('id', $args['id']);

        $mail = [
            'subject'   =>  'Guardian Added You',
            'from'      =>  'reportingmit@gmail.com',
            'to'        =>  $user['email'],
            'sender'    =>  'Reporting App',
            'receiver'  =>  $user['name'],
            'content'   =>  'You are successfully added by '. $guardName,
        ];
        // var_dump($mail);die();
        if (empty($findUser[0])) {
           $addUser = $guard->createData($data);

           $result = $mailer->send($mail);

           $this->flash->addMessage('succes', 'User successfully added');

        } else {
            $this->flash->addMessage('error', 'User already exists!');
        }

        return $response->withRedirect($this->router->pathFor('list.user'));
    }

    public function listUserByGuard($request, $response)
    {
        $guard = new \App\Models\GuardModel($this->db);
        $user = new UserModel($this->db);

        $guardId = $_SESSION['login']['id'];
        $users = $guard->findAllUser($guardId);
        $find = $guard->find('guard_id', $guardId);

        return $this->view->render($response, 'guardian/list-user.twig', ['users' => $users]);
    }

    public function delGuardUser($request, $response, $args)
    {
        $guard = new \App\Models\GuardModel($this->db);

        $guardId = $_SESSION['login']['id'];
        $findId = $guard->findGuard('user_id', $args['id'], 'guard_id', $guardId);
        // var_dump($findId);die();

        if ($findId) {

            $guard->hardDelete($findId['id']);

            $users = $guard->findAllUser($guardId);

            $_SESSION['guard'] = ['user' => $users];

            $this->flash->addMessage('succes', 'User successfully deleted');
        }

        return $response->withRedirect($this->router->pathFor('list.user'));
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
        $users = new \App\Models\Users\UserModel($this->db);
        $userItems = new \App\Models\UserItem($this->db);
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

    public function searchUser($request, $response)
    {
        $user = new UserModel($this->db);

        $search = $request->getParams()['search'];

        $userId  = $_SESSION['login']['id'];

        $data['group_id'] = $request->getParams()['group'];
        $data['users'] = $user->search($search, $userId);
        $data['count'] = count($data['users']);

        if (!empty($request->getParams()['guard'])) {

            return $this->view->render($response, 'guardian/view-user-search.twig', $data);

        } elseif (!empty($request->getParams()['group'])) {

            return $this->view->render($response, 'admin/group/not-member.twig', $data);
        }
    }

    public function getItemsUser($request,$response, $args)
    {
        $users = new UserModel($this->db);
        $items = new \App\Models\Item($this->db);
        $groups = new \App\Models\GroupModel($this->db);
        $guards = new \App\Models\GuardModel($this->db);
        $userGroups = new \App\Models\UserGroupModel($this->db);

        $userId  = $_SESSION['login']['id'];
        $userGroup = $userGroups->finds('group_id', $args['id'], 'user_id', $userId);
        $userItem = $items->getUserItem($userId, $args['id']);
        $itemDone = $items->getItemDone($userId, $args['id']);
        $userGuard = $guards->finds('guard_id', $userId, 'user_id', $args['user']);
        $group = $groups->find('id', $args['id']);

        $reported = $request->getQueryParam('reported');
        $count = count($itemDone);

        if ($userGroup[0] || $userGuard[0]) {
            return $this->view->render($response, 'users/useritem.twig', [
                'items' => $userItem,
                'itemdone' => $itemDone,
                'group_id' => $args['id'],
                'group' => $group['name'],
                'reported'=> $reported,
                'count'=> $count,
            ]);

        } else {
            $this->flash->addMessage('error', 'You are not allowed to access this group!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
    }

    public function activateAccount($request, $response, $args)
    {
        $users = new UserModel($this->db);
        $registers = new \App\Models\RegisterModel($this->db);

        $userToken = $registers->find('token', $args['token']);
        $base = $request->getUri()->getBaseUrl();
        $now = date('Y-m-d H:i:s');
        // var_dump($findId);die();
        if ($userToken && $userToken['expired_date'] > $now) {

            $user = $users->setActive($userToken['user_id']);
            $registers->hardDelete($userToken['id']);

            $this->flash->addMessage('succes', 'Your account has been successfully activated');

        }elseif ($userToken['expired_date'] > $now) {
            $this->flash->addMessage('error', 'Your token has been expired');

        } else{
            $this->flash->addMessage('error', 'You have not signed up yet');
        }

        return $response->withRedirect($this->router->pathFor('login'));
    }

    public function deleteGuardian($request, $response, $args)
    {
        $guardian = new \App\Models\GuardModel($this->db);

        $findGuardian = $guardian->findGuard('guard_id', $args['id'], 'user_id', $_SESSION['login']['id']);

        if ($findGuardian) {
            $guardian->hardDelete($findGuardian['id']);

            $this->flash->addMessage('succes', 'Guardian succesfully deleted');
        }

        return $response->withRedirect($this->router->pathFor('user.profile'));

    }
}
