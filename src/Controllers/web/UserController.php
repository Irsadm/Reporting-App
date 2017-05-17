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
            ->rule('required', ['username', 'password', 'name', 'email', 'phone', 'address', 'gender'])
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
            $register = $user->checkDuplicate($request->getParam('username'), $request->getParam('email'));

            if ($register == 1) {
                $_SESSION['old'] = $request->getParams();
                $this->flash->addMessage('warning', 'Username, already used');
                    return $response->withRedirect($this->router->pathFor('user.create'));
            } elseif ($register == 2) {
                $_SESSION['old'] = $request->getParams();
                $this->flash->addMessage('warning', 'Email, already used');
                return $response->withRedirect($this->router->pathFor('user.create'));
            } else {
                $create = $request->getParams();
                $user->createUser($create);
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
        return $this->view->render($response, 'admin/users/edit.twig',
            $data);
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
            return $response->withRedirect($this->router->pathFor('user.edit.data', ['id' => $args['id']]));
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
            $register = $user->checkDuplicate($request->getParam('username'), $request->getParam('email'));

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
                if ($_SESSION['login']['is_admin'] == 1) {
                    // $this->flash->addMessage('succes', 'Congratulations you have successfully logged in as admin');
                    return $response->withRedirect($this->router
                            ->pathFor('home'));
                } else {
                    if (isset($_SESSION['login']['is_admin'])) {
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

    public function loginAsUser($request, $response)
    {
        $user = new UserModel($this->db);
        $login = $user->find('username', $request->getParam('username'));
        $group =  new \App\Models\UserGroupModel($this->db);
        $groups = $group->findAllGroup($login['id']);

        if (empty($login)) {
            $this->flash->addMessage('warning', ' Username is not registered');
            return $response->withRedirect($this->router
                    ->pathFor('login'));
        } else {
            if (password_verify($request->getParam('password'),
                $login['password'])) {
                $_SESSION['login'] = $login;
                $_SESSION['user_group'] = $groups;

                if ($_SESSION['login']['is_admin'] == 0) {
                    $this->flash->addMessage('succes', 'Succes Login As User');
                    return $response->withRedirect($this->router
                            ->pathFor('home'));
                } else {
                    if (isset($_SESSION['login']['is_admin'])) {
                        $this->flash->addMessage('succes', 'You Are Not User');
                        return $response->withRedirect($this->router
                                ->pathFor('login'));
                    }
                }
            } else {
                $this->flash->addMessage('warning', ' Password is not registered');
                return $response->withRedirect($this->router
                        ->pathFor('login'));
            }
        }
    }

    public function logout($request, $response)
    {
        session_destroy();
        // unset($_SESSION['login']);
        return $response->withRedirect($this->router->pathFor('register'));
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
            var_dump($request->getParams());die();

            $_SESSION['old'] = $request->getParams();
            $_SESSION['errors'] = $this->validator->errors();
            return $response->withRedirect($this->router->pathFor('user.setting', ['id' => $args['id']]));
        }
    }

    public function enterGroup($request,$response, $args)
    {
        $item = new \App\Models\Item($this->db);
        $userItem = new \App\Models\UserItem($this->db);
        $userGroup = new \App\Models\UserGroupModel($this->db);

        $userId  = $_SESSION['login']['id'];
        $user = $userGroup->findUser('group_id', $args['id'], 'user_id', $userId);
        if ($user['status'] == 1) {

            $group = new \App\Models\GroupModel($this->db);
    		$userGroup = new \App\Models\UserGroupModel($this->db);

    		$findGroup = $group->find('id', $args['id']);
    		$finduserGroup = $userGroup->findUsers('group_id', $args['id']);
    		$countUser = count($finduserGroup);

    		return $this->view->render($response, 'admin/group/detail.twig', [
    			'group' => $findGroup,
    			'counts'=> [
    				'user' => $countUser,
    			]
    		]);
        } elseif ($user['status'] == 0) {
            return $this->getItemInGroup($request,$response, $args);

        } elseif ($user['status'] == 2) {
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

        $findUserItem['items'] = $userItem->getItemInGroup($args['id'], $userId);
        $findUserItem['itemdone'] = $userItem->getDoneItemInGroup($args['id'], $userId);

        $count = count($findUserItem['itemdone']);
        $reported = $request->getQueryParam('reported');

        $data = $this->view->render($response, 'users/useritem.twig', [
            'itemdone' => $findUserItem['itemdone'],
            'items' => $findUserItem['items'],
            'status'=> $user['status'],
            'group_id' => $args['id'],
            'reported'=> $reported,
            'count'=> $count,
        ]);

        return $data;
    }

    public function setItemUserStatus($request, $response, $args)
    {
        $userItem = new \App\Models\UserItem($this->db);


        $setItem = $userItem->setStatusItems($args['id']);
        $findGroup = $userItem->find('id', $args['id']);
        $groupId = $findGroup['group_id'];

        return $response->withRedirect($this->router->pathFor('user.item.group', ['id' =>$groupId]));
    }

    public function restoreItemUserStatus($request, $response, $args)
    {
        $userItem = new \App\Models\UserItem($this->db);

        $setItem = $userItem->resetStatusItems($args['id']);
        $findGroup = $userItem->find('id', $args['id']);
        $groupId = $findGroup['group_id'];

        return $response->withRedirect($this->router->pathFor('user.item.group', ['id' =>$groupId]));
    }
}
