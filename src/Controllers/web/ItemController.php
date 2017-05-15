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
        // $count = count($getItem);
        $data['items'] = $getItem;

        // var_dump($data);
        // die();
        // $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        // $data['items'] = $item->paginate($page, $getItem, 10);

        // var_dump($paginate);
        // die();


        return $this->view->render($response, 'admin/item/allitem.twig', $data);
    }

    public function getAdd(Request $request, Response $response)
    {
        $group = new \App\Models\GroupModel($this->db);
        $getGroup = $group->getAll();

        $data['group'] = $getGroup;

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
        // var_dump($request->getParams());
        // die();
    }

    public function getUpdateItem(Request $request, Response $response, $args)
    {
        $item = new Item($this->db);
        $findItem = $item->find('id', $args['id']);
        $group = new \App\Models\GroupModel($this->db);
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
            $newItem = $item->update($request->getParams(), $args['id']);

            $this->flash->addMessage('succes', 'Item successfully updated');

            return $response->withRedirect($this->router->pathFor('index'));
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
        // $count = count($getItem);
        $data['items'] = $getItem;

        // var_dump($data);
        // die();
        // $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        // $data['items'] = $item->paginate($page, $getItem, 10);

        // var_dump($paginate);
        // die();


        return $this->view->render($response, 'admin/item/trash.twig', $data);
    }

    public function hardDeleteItem(Request $request, Response $response, $args)
    {
        $item = new Item($this->db);
        $deleteItem = $item->hardDelete($args['id']);
        $this->flash->addMessage('succes', 'Item deleted');

        return $response->withRedirect($this->router->pathFor('index'));
    }

    public function softDeleteItem(Request $request, Response $response, $args)
    {
        $item = new Item($this->db);
        $deleteItem = $item->softDelete($args['id']);
        $this->flash->addMessage('succes', 'Item deleted');

        return $response->withRedirect($this->router->pathFor('index'));
    }

    public function restoreItem(Request $request, Response $response, $args)
    {
        $item = new Item($this->db);
        $deleteItem = $item->restoreData($args['id']);
        $this->flash->addMessage('succes', 'Item restored');

        return $response->withRedirect($this->router->pathFor('item.trash'));
    }

}
