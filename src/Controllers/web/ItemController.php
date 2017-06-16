<?php
namespace App\Controllers\web;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\Item;
use App\Models\UserItem;
class ItemController extends BaseController
{
    public function index(Request $request, Response $response)
    {
        $item = new Item($this->db);
        $getItem = $item->getAllItem();
        $data['items'] = $getItem;
        return $this->view->render($response, 'admin/item/allitem.twig', $data);
    }
    public function getAdd(Request $request, Response $response)
    {
        $group = new \App\Models\GroupModel($this->db);
        $userGroup = new \App\Models\UserGroupModel($this->db);
        $user = new \App\Models\Users\UserModel($this->db);
        $getGroup = $group->getAll();
        $getUser = $userGroup->getAll();
        $data = [
            'groups' => $getGroup,
            'users' => $getUser,
        ];

        return $this->view->render($response, 'admin/item/add.twig', $data);
    }

    public function postAdd(Request $request, Response $response)
    {
        $rules = [
            'required'  => [
                ['name'],
                ['recurrent'],
                ['description'],
                ['start_date'],
                ['end_date'],
                ['group_id'],
            ],
            'dateformat' => [
                ['start_date', 'Y-m-d H:i:s'],
                ['end_date', 'Y-m-d H:i:s'],
            ]
        ];
        $this->validator->rules($rules);
        $this->validator->labels([
            'name'         => 'Name',
            'recurrent'    => 'Recurrent',
            'start_date'   => 'Start date',
            'end_date'     => 'End date',
            'group_id'     => 'Group id',
        ]);
        if ($this->validator->validate()) {
            $item  = new Item($this->db);
            $newItem = $item->create($request->getParams());
            $this->flash->addMessage('succes', 'New item successfully added');
            return $response->withRedirect($this->router->pathFor('item.add'));
        } else {
            $_SESSION['old']  = $request->getParams();
            $_SESSION['errors'] = $this->validator->errors();
            return $response->withRedirect($this->router->pathFor('item.add'));
        }
    }
    public function getUpdateItem(Request $request, Response $response, $args)
    {
        $item = new Item($this->db);
        $group = new \App\Models\GroupModel($this->db);

        $findItem = $item->find('id', $args['id']);
        $getGroup = $group->getAll();
        $data['item'] = $findItem;
        $data['group'] = $getGroup;
        return $this->view->render($response, 'admin/item/edit.twig', $data);
    }
    public function postUpdateItem(Request $request, Response $response, $args)
    {
        $rules = [
            'required'  => [
                ['name'],
                ['recurrent'],
                ['description'],
                ['start_date'],
                ['group_id'],
            ],
            'dateformat' => [
                ['start_date', 'Y-m-d'],
            ]
        ];
        $this->validator->rules($rules);
        $this->validator->labels([
            'name'         => 'Name',
            'recurrent'    => 'Recurrent',
            'start_date'   => 'Start date',
            'end_date'     => 'End date',
            'group_id'     => 'Group id',
        ]);
        if ($this->validator->validate()) {
            $item  = new Item($this->db);
            $newItem = $item->update($request->getParams(), $args['id']);
            $this->flash->addMessage('succes', 'Item successfully updated');
            return $response->withRedirect($this->router->pathFor('item.list'));
        } else {
            $_SESSION['old']  = $request->getParams();
            $_SESSION['errors'] = $this->validator->errors();
            return $response->withRedirect($this->router
                            ->pathFor('item.update', ['id' => $args['id']]));
        }
    }
    public function getTrash(Request $request, Response $response)
    {
        $item = new Item($this->db);
        $getItem = $item->getAllDeleted();
        $data['items'] = $getItem;
        return $this->view->render($response, 'admin/item/trash.twig', $data);
    }
    public function hardDeleteItem(Request $request, Response $response, $args)
    {
        $item = new Item($this->db);
        $deleteItem = $item->hardDelete($args['id']);
        $this->flash->addMessage('succes', 'Item deleted');
        return $response->withRedirect($this->router->pathFor('item.trash'));
    }
    public function softDeleteItem(Request $request, Response $response, $args)
    {
        $item = new Item($this->db);
        $deleteItem = $item->softDelete($args['id']);
        $this->flash->addMessage('succes', 'Item deleted');
        return $response->withRedirect($this->router->pathFor('item.list'));
    }
    public function restoreItem(Request $request, Response $response, $args)
    {
        $item = new Item($this->db);
        $deleteItem = $item->restoreData($args['id']);
        $this->flash->addMessage('succes', 'Item restored');
        return $response->withRedirect($this->router->pathFor('item.trash'));
    }
    public function getSelectItem($request, $response)
    {
        $userItem = new \App\Models\UserItem($this->db);
        $userGroup = new \App\Models\UserGroupModel($this->db);
        $userId = $_SESSION['login']['id'];
        $findUserGroup = $userGroup->findUser('user_id', $userId,
                                              'group_id', $_SESSION['group']);
        $userGroupId = $findUserGroup['id'];
        // $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $item = $userItem->unselectedItem($userGroupId, $_SESSION['group']);
        $data = $this->view->render($response, 'users/additem.twig', ['item' => $item]);
        return $data;
    }
    public function setItem($request, $response)
    {
        $userItem = new UserItem($this->db);
        $item = new Item($this->db);
        $userGroup = new \App\Models\UserGroupModel($this->db);
        $userId = $_SESSION['login']['id'];
        $group = $_SESSION['user_group'];
        $userGroupId = $userGroup->findUser('user_id', $userId,
                                            'group_id', $_SESSION['group']);
        if (!empty($request->getParams()['set'])) {
            foreach ($request->getParams()['item'] as $key =>  $value ) {
                $findItem = $item->find('id', $value);
                $data = [
                    'item_id' => $value,
                    'user_group_id' => $userGroupId['id']
                ];
                $userItem->setItem($data, $userGroupId['id']);
            }
        }
        return $response->withRedirect($this->router->pathFor('user.item.all'));
    }
    public function getCreateItem($request, $response, $args)
    {
        $userGroup = new \App\Models\UserGroupModel($this->db);
        $group     = new \App\Models\GroupModel($this->db);
        $userId = $_SESSION['login']['id'];
        $findUserGroup = $userGroup->finds('group_id', $args['id'], 'user_id', $userId);
        $findGroup = $group->find('id', $args['id']);
        $data['groups'] = $findGroup['name'];
        return $this->view->render($response, 'users/createitem.twig', $data);
    }
    public function postCreateItem($request, $response)
    {
        $rules = [
            'required'  => [
                ['name'],
                // ['recurrent'],
                ['description'],
                ['start_date'],
                ['end_date'],
                ['group_id'],
            ],
            'dateformat' => [
                ['start_date', 'Y-m-d H:i:s'],
                ['end_date', 'Y-m-d H:i:s'],
            ]
        ];
        $this->validator->rules($rules);
        $this->validator->labels([
            'name'         => 'Name',
            'recurrent'    => 'Recurrent',
            'start_date'   => 'Start date',
            'end_date'     => 'End date',
            'group_id'     => 'Group id',
        ]);
        if ($this->validator->validate()) {
            $item  = new Item($this->db);
            $userItem = new UserItem($this->db);
            $userGroup = new \App\Models\UserGroupModel($this->db);
            $findUserGroup = $userGroup->findUser('user_id', $_SESSION[login]['id'],
                                                    'group_id', $_SESSION['group']);
            $newItem = $item->create($request->getParams());
            $UserGroupId = $findUserGroup['id'];
            $userItemData = [
                'item_id' => $newItem,
                'user_group_id' => $UserGroupId
            ];
            $newUserItem = $userItem->setItem($userItemData, $UserGroupId);
            $this->flash->addMessage('succes', 'New item successfully added');
            return $response->withRedirect($this->router->pathFor('user.item.create'));
        } else {
            $_SESSION['old']  = $request->getParams();
            $_SESSION['errors'] = $this->validator->errors();
            return $response->withRedirect($this->router->pathFor('user.item.create'));
        }
    }

    public function getItemInGroup($request,$response, $args)
    {
        $items = new \App\Models\Item($this->db);
        $groups = new \App\Models\GroupModel($this->db);
        $userGroups = new \App\Models\UserGroupModel($this->db);

        $picId  = $_SESSION['login']['id'];
        $admin  = $_SESSION['login']['status'];
        $user = $userGroups->finds('group_id', $args['id'], 'user_id', $picId);
        $groupItem = $items->getGroupItem($args['id']);
        $group = $groups->find('id', $args['id']);
        $member = $userGroups->getMember($args['id']);
        $count = count($groupItem);
        // var_dump($member);die();
        if ($user[0]['status'] == 1 || $admin == 1 ) {
            return $this->view->render($response, 'pic/groupitem.twig', [
                'items' => $groupItem,
                'groups' => $group,
                'members' => $member,
                'group_id' => $args['id'],
                'count'=> $count,
            ]);
        } else {
            $this->flash->addMessage('error', 'You are not allowed to access this group!');
            return $response->withRedirect($this->router->pathFor('home'));
        }
    }

    public function createItemByPic($request, $response)
    {
        if ($request->getParams()['user_id'] === "") {
            $user_id = NULL;
        }else {
            $user_id = $request->getParams()['user_id'];
        }

        $rules = [
            'required'  => [
                ['name'],
                ['description'],
                ['start_date']
            ],
            'dateformat' => [
                ['start_date', 'Y-m-d']
            ]
        ];
        $this->validator->rules($rules);
        $this->validator->labels([
            'name'         => 'Name',
            'recurrent'    => 'Recurrent',
            'description'  => 'Description',
            'start_date'   => 'Start date',
        ]);
        if ($this->validator->validate()) {
            if (!empty($_FILES['image']['name'])) {
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

                $image->upload();
                $imageName = $dataImg['name'];
            } else {
                $imageName = '';
            }

        $itemData = [
            'name'         => $request->getParams()['name'],
            'description'  => $request->getParams()['description'],
            'recurrent'    => $request->getParams()['recurrent'],
            'start_date'   => $request->getParams()['start_date'],
            'group_id'     => $request->getParams()['group_id'],
            'user_id'      => $user_id,
            'image'       => $imageName,
            'creator'      => $_SESSION['login']['id'],
        ];
            $item  = new Item($this->db);
            $newItem = $item->create($itemData);



            $this->flash->addMessage('succes', 'New item successfully added');

// var_dump($_SESSION['login']);die();
            if ($_SESSION['login']['status'] == 1){

                return $response->withRedirect($this->router->pathFor('get.group.item', ['id' => $request->getParams()['group_id'] ]));
            } elseif ($_SESSION['login']['status'] == 2) {

                return $response->withRedirect($this->router->pathFor('pic.item.group', ['id' => $request->getParams()['group_id'] ]));
            }
        } else {
            $_SESSION['errors'] = $this->validator->errors();
            $_SESSION['old']  = $request->getParams();
            // var_dump($_SESSION['old']); die();
            return $response->withRedirect($this->router->pathFor('pic.item.group', ['id' => $request->getParams()['group_id'] ]));
        }
    }

    //Create item in group by user
    public function createItemByUser($request, $response)
    {
        // var_dump($request->getParams()['group_id']);die();
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
        $rules = ['required' => [
            ['name'],
            ['start_date']
        ],
        'dateformat' => [
            ['start_date', 'Y-m-d'],
        ]
    ];
    $this->validator->rules($rules);
    $this->validator->labels([
        'name' 			=>	'Name',
        'description'	=>	'Description',
        'start_date'	=>	'Start date',
    ]);
    $userId  = $_SESSION['login']['id'];
    if ($this->validator->validate()) {
        if (!empty($_FILES['image']['name'])) {
            $image->upload();
            $imgName = $dataImg['name'];
        } else {
            $imgName = '';
        }
        $data = [
            'name' 			=>	$request->getParams()['name'],
            'description'	=>	$request->getParams()['description'],
            'recurrent'	    =>	$request->getParams()['recurrent'],
            'start_date'	=>	$request->getParams()['start_date'],
            'group_id'	    =>	$request->getParams()['group_id'],
            'user_id'	    =>	$request->getParams()['user_id'],
            'creator'	    =>	$userId,
            'image'			=>	$imgName,
            ];
            $items = new Item($this->db);
            $addItems = $items->createData($data);
            $this->flash->addMessage('succes', 'Item successfully created');
            return $response->withRedirect($this->router
            ->pathFor('user.item.group', ['id' => $request->getParams()['group_id']]));
        } else {
            $_SESSION['old'] = $request->getParams();
            $_SESSION['errors'] = $this->validator->errors();
            return $response->withRedirect($this->router
            ->pathFor('user.item.group', ['id' => $request->getParams()['group_id']]));
        }
    }

    //Create item in group by user
    public function reportItem($request, $response)
    {
        $items = new Item($this->db);
        $mailer = new \App\Extensions\Mailers\Mailer();
        $guards = new \App\Models\GuardModel($this->db);
        $users = new \App\Models\Users\UserModel($this->db);
        $itemDone = new \App\Models\ReportedItem($this->db);
        $userGroups = new \App\Models\UserGroupModel($this->db);
        $storage = new \Upload\Storage\FileSystem('assets/images');
        $image = new \Upload\File('image',$storage);

        if (!empty($_FILES['image']['name'])) {

            $image->setName(uniqid());
            $image->addValidations(array(
            new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
            'image/jpg', 'image/jpeg')),
            new \Upload\Validation\Size('5M')
            ));

            $image->upload();
            $imgName = $image->getNameWithExtension();
        } else {
            $imgName = '';
        }

        $itemId = $request->getParams()['item_id'];
        $username  = $_SESSION['login']['name'];
        $userId  = $_SESSION['login']['id'];
        $dateNow = date('Y-m-d H:i:s');
        $item = $items->find('id', $itemId);
        $guardian = $guards->find('user_id', $userId);
        $guard = $users->find('id', $guardian['guard_id']);
        $picGroup = $userGroups->finds('group_id', $item['group_id'], 'status', '1');
        $pic = $users->find('id', $picGroup[0]['user_id']);

        $data = [
            'description'	=>	$request->getParams()['description'],
            'image'			=>	$imgName,
            'reported_at'	=>	$dateNow,
            'status'		=>	1,
        ];

        if (!empty($request->getParams()['group'])) {

            $newItem = $items->create($item);
            $addItems = $items->updateData($data, $newItem);

            $itemDone->createData([
                'item_id' 	=>	$newItem,
                'user_id'	=>	$userId,
            ]);

        } elseif (!empty($request->getParams()['user'])) {

            $addItems = $items->updateData($data, $itemId);

        }

        $itemDone->createData([
            'item_id' 	=>	$itemId,
            'user_id'	=>	$userId,
        ]);

        $date = date('d M Y H:i:s');
        $content = $username .' has completed '. $item['name'] .' on '. $date;

        if ($guard) {
            $dataGuard = [
                'subject' 	=>	$username.' item report',
                'from'      =>	'reportingmit@gmail.com',
                'to'	    =>	$guard['email'],
                'sender'	=>	'Reporting App',
                'receiver'	=>	$guard['name'],
                'content'	=>	$content,
            ];

            $this->sendWebNotif($report, $guard['id']);
            $mailer->send($dataGuard);
        }

        if ($pic && $pic['id'] != $guard['id']) {
            $dataPic = [
                'subject' 	=>	$username.' item report',
                'from'      =>	'reportingmit@gmail.com',
                'to'	    =>	$pic['email'],
                'sender'	=>	'Reporting App',
                'receiver'	=>	$pic['name'],
                'content'	=>	$content,
            ];

            $this->sendWebNotif($report, $pic['id']);
            $mailer->send($dataPic);
        }

        $this->flash->addMessage('succes', 'Item successfully reported');

        return $response->withRedirect($this->router
        ->pathFor('user.item.group', ['id' => $request->getParams()['group_id']]));

    }

    public function deleteItemByPic($request, $response, $args)
    {
        $item = new Item($this->db);
        $itemDone = new \App\Models\ReportedItem($this->db);
        $userGroups = new \App\Models\UserGroupModel($this->db);
        var_dump($_SESSION['login']);die();

        $userId  = $_SESSION['login']['id'];
        $findItem = $item->find('id', $args['id']);
        $findItemDone = $itemDone->finds('item_id', $args['id'], 'user_id', $userId);
        $userGroup = $userGroups->finds('group_id', $findItem['group_id'], 'user_id', $userId);

        if ($userGroup[0]['status'] == 1) {

            $deleteItem = $item->hardDelete($args['id']);

            if ($findItemDone) {
                $itemDone->hardDelete($findItemDone[0]['id']);
            }

            $this->flash->addMessage('succes', 'Item deleted');
        } else {

            $this->flash->addMessage('error', 'You are not allowed to delete this item');
        }
        if ($_SESSION['login']['status'] == 1){

            return $response->withRedirect($this->router->pathFor('get.group.item', ['id' => $findItem['group_id'] ]));
        } else {

            return $response->withRedirect($this->router->pathFor('pic.item.group', ['id' => $findItem['group_id'] ]));
        }
        // return $response->withRedirect($this->router->pathFor('pic.item.group',
        // ['id' => $findItem['group_id'] ]));
    }

    public function deleteItemByUser($request, $response, $args)
    {
        $item = new Item($this->db);
        $itemDone = new \App\Models\ReportedItem($this->db);
        $userGroups = new \App\Models\UserGroupModel($this->db);

        $userId  = $_SESSION['login']['id'];
        $findItem = $item->find('id', $args['id']);
        $findItemDone = $itemDone->finds('item_id', $args['id'], 'user_id', $userId);
        $userGroup = $userGroups->finds('group_id', $findItem['group_id'], 'user_id', $userId);

        if ($userGroup[0] || $findItem['creator'] == $userId) {

            $deleteItem = $item->hardDelete($args['id']);

            if ($findItemDone) {
                $itemDone->hardDelete($findItemDone[0]['id']);
            }

            $this->flash->addMessage('succes', 'Item deleted');

        } else {

            $this->flash->addMessage('error', 'You are not allowed to delete this item');
        }

        return $response->withRedirect($this->router->pathFor('user.item.group',
        ['id' => $findItem['group_id'] ]));
    }
}
