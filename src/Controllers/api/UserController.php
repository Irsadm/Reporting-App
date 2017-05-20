<?php

namespace App\Controllers\api;

use App\Models\Users\UserModel;
use App\Models\Users\UserToken;
class UserController extends BaseController
{
    //Get all user
    public function index($request, $response)
    {
        $user = new UserModel($this->db);

        $getUser = $user->getAllUser();
        $countUser = count($getUser);

        if ($getUser) {
            $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');

            $get = $user->paginate($page, $getUser, 5);

            if ($get) {
                $data = $this->responseDetail(200, 'Data Available', $get,
                 $this->paginate($countUser, 5, $page, ceil($countUser/5)));
            } else {
                $data = $this->responseDetail(404, 'Error', 'Data Not Found');
            }
        } else {
            $data = $this->responseDetail(204, 'Success', 'No Content');
        }

        return $data;

    }

    public function createUser($request, $response)
    {
        $this->validator->rule('required', ['name', 'email', 'username',
                            'password', 'gender', 'address', 'phone', 'image']);
        $this->validator->rule('email', 'email');
        $this->validator->rule('alphaNum', 'username');
        $this->validator->rule('numeric', 'phone');
        $this->validator->rule('lengthMin', ['name', 'email', 'username', 'password'], 5);
        $this->validator->rule('integer', 'id');

        if ($this->validator->validate()) {
            $user = new UserModel($this->db);
            $createUsers = $user->createUser($request->getParsedBody());

            $data = $this->responseDetail(201, 'Success', 'Create User Succes',
                        $request->getParsedBody());
        } else {
            $data = $this->responseDetail(400, 'Errors', $this->validator->errors());
        }
        return $data;
    }

    //Delete user account by id
    public function deleteUser($request, $response, $args)
    {
        $user = new UserModel($this->db);
        $findUser = $user->find('id', $args['id']);

        if ($findUser) {
            $user->hardDelete($args['id']);
            $data['id'] = $args['id'];
            $data = $this->responseDetail(200, 'Succes', 'Data Has Been Deleted', $data);
        } else {
            $data = $this->responseDetail(400, 'Errors', 'Data Not Found');
        }
        return $data;
    }

    //Delete user account
    public function delAccount($request, $response)
    {
        $users = new UserModel($this->db);
        $userToken = new \App\Models\Users\UserToken($this->container->db);

		$token = $request->getHeader('Authorization')[0];

		$findUser = $userToken->find('token', $token);
        $user = $users->find('id', $findUser['user_id']);

        if ($user) {
            $users->hardDelete($user['id']);
            $data['id'] = $user['id'];
            $data = $this->responseDetail(200, 'Succes', 'Data Has Been Deleted', $data);
        } else {
            $data = $this->responseDetail(400, 'Errors', 'Data Not Found');
        }
        return $data;
    }

    //Update user account by id
    public function updateUser($request, $response, $args)
    {
        $user = new UserModel($this->db);
        $findUser = $user->find('id', $args['id']);

        if ($findUser) {
            $this->validator->rule('required', ['name', 'email', 'username',
                            'password', 'gender', 'address', 'phone', 'image']);
            $this->validator->rule('email', 'email');
            $this->validator->rule('alphaNum', 'username');
            $this->validator->rule('numeric', 'phone');
            $this->validator->rule('lengthMin', ['name', 'email', 'username', 'password'], 5);
            $this->validator->rule('integer', 'id');
            if ($this->validator->validate()) {
                $user->updateData($request->getParsedBody(), $args['id']);
                $data['update data'] = $request->getParsedBody();

                $data = $this->responseDetail(200, 'Succes', 'Update Data Succes', $data);
            } else {
                $data = $this->responseDetail(400, 'Errors', $this->validator->errors());
            }
        } else {
            $data = $this->responseDetail(400, 'Errors', 'Data Not Found');
        }
        return $data;
    }

    //Update user account
    public function editAccount($request, $response)
    {
        $users = new UserModel($this->db);
        $userToken = new \App\Models\Users\UserToken($this->container->db);

		$token = $request->getHeader('Authorization')[0];
		$user = $userToken->find('token', $token);
        $findUser = $users->find('id', $user['user_id']);

        if ($findUser) {
            $this->validator->rule('required', ['name', 'email', 'username',
                            'password', 'gender', 'address', 'phone', 'image']);
            $this->validator->rule('email', 'email');
            $this->validator->rule('alphaNum', 'username');
            $this->validator->rule('numeric', 'phone');
            $this->validator->rule('lengthMin', ['name', 'email', 'username', 'password'], 5);
            $this->validator->rule('integer', 'id');
            if ($this->validator->validate()) {
                $users->updateData($request->getParsedBody(), $user['user_id']);
                $data['update data'] = $request->getParsedBody();

                $data = $this->responseDetail(200, 'Succes', 'Update Data Succes', $data);
            } else {
                $data = $this->responseDetail(400, 'Errors', $this->validator->errors());
            }
        } else {
            $data = $this->responseDetail(400, 'Errors', 'Data Not Found');
        }
        return $data;
    }

    //Find User by id
    public function findUser($request, $response, $args)
    {
        $user = new UserModel($this->db);
        $findUser = $user->find('id', $args['id']);

        if ($findUser) {
            $data = $this->responseDetail(200, 'Succes', 'Data available', $findUser);
        } else {
            $data = $this->responseDetail(400, 'Errors', 'Data Not Found');
        }

        return $data;
    }

    //Find User by id
    public function detailAccount($request, $response)
    {
        $users = new UserModel($this->db);
        $userToken = new \App\Models\Users\UserToken($this->container->db);

		$token = $request->getHeader('Authorization')[0];
		$user = $userToken->find('token', $token);
        $findUser = $users->find('id', $user['user_id']);

        if ($findUser) {
            $data = $this->responseDetail(200, 'Succes', 'Data available', $findUser);
        } else {
            $data = $this->responseDetail(400, 'Errors', 'Data Not Found');
        }

        return $data;
    }

    //User login
    public function login($request, $response)
    {
        $user = new UserModel($this->db);
        $login = $user->find('username', $request->getParam('username'));

        if (empty($login)) {
            $data = $this->responseDetail(401, 'Errors', 'username is not registered');
        } else {
            $check = password_verify($request->getParam('password'), $login['password']);
            if ($check) {
                $token = new UserToken($this->db);
                $token->setToken($login['id']);
                $getToken = $token->find('user_id', $login['id']);

                $key = [
                'key' => $getToken,
                ];
                $data = $this->responseDetail(201, 'Login Succes', $login, $key);
            } else {
                $data = $this->responseDetail(401, 'Errors', 'Wrong Password');
            }
        }
        return $data;
    }

    //Set item to user in group
    public function setItemUser($request, $response, $args)
    {
        $user = new UserModel($this->db);
        $findUser = $user->find('id', $request->getParsedBody()['user_id']);
        $group = new \App\Models\GroupModel($this->db);
        $findGroup = $group->find('id', $args['group']);

        $token = $request->getHeader('Authorization')[0];

        $userToken = new \App\Models\Users\UserToken($this->db);

        if ($findUser && $findGroup) {
            $data['user_id'] = $findUser['id'];
            $item = new \App\Models\UserItem($this->db);
            // $findUserGroup = $item->findUser('user_id', $args['id'], 'group_id', $args['group']);

            $this->validator->rule('required', ['item_id', 'user_id']);
            $this->validator->rule('integer', ['id']);

            if ($this->validator->validate()) {
                $item->setItem($request->getParsedBody(), $args['group']);
                $data = $request->getParsedBody();


                $data = $this->responseDetail(201, 'Succes managed to select the item', $data, $findUser);
            } else {
                $data['status_code'] = 400;
                $data['status_message'] = "Error";
                $data['data'] = $this->validator->errors();

                $data = $this->responseDetail(400, 'Errors', $this->validator->errors());
            }

            return $data;

            $items = $user->find('id', $args['id']);
            $item = $request->getParsedBody();

            $data = $this->responseDetail(201, 'user Succes Purchased', $items, $item);
        } else {
            $data = $this->responseDetail(404, 'Error', 'user Not Found');
        }

        return $data;

    }

     public function logout($request, $response )
        {
            $token = $request->getHeader('Authorization')[0];

            $userToken = new UserToken($this->db);
            $findUser = $userToken->find('token', $token);

            $userToken->delete('user_id',$findUser['user_id']);
            return $this->responseDetail(200, 'Success', 'Logout Success');
        }
}
