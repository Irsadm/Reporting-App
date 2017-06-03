<?php
namespace App\Controllers\web;


use App\Models\RequestModel;
use App\Models\GuardModel;

class RequestController extends BaseController
{public function getRequest($request, $response)
{
    $requests = new RequestModel($this->db);

    $id = $_SESSION['login']['id'];

    $data['request'] = $requests->request($id, 'guard');
    // var_dump ($data);die();
    return $this->view->render($response, 'users/request.twig', $data);
}

public function sendRequestUser($request, $response)
{
    $guard = new GuardModel($this->db);

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

}

?>
