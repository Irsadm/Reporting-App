<?php
namespace App\Controllers\web;


use App\Models\PostModel;
use App\Models\UserGroupModel;

class PostController extends BaseController
{
    public function getPost($request, $response)
    {
        $requests = new RequestModel($this->db);

        $id = $_SESSION['login']['id'];

        $data['request'] = $requests->request($id, 'guard');
        // var_dump ($data);die();
        return $this->view->render($response, 'users/request.twig', $data);
    }

    public function addPost($request, $response)
    {
        $posts = new PostModel($this->db);
        $userGroups = new UserGroupModel($this->db);

        $userId = $_SESSION['login']['id'];
        $groupId = $request->getParams()['group_id'];
        $userGroup = $userGroups->finds('group_id', $groupId, 'user_id', $userId);

        $rules = ['required'  => [['content']]];

        $this->validator->rules($rules);
        $this->validator->labels([
            'content'      => 'Konten'
        ]);

        if ($this->validator->validate() && $userGroup) {
            if (!empty($_FILES['image']['name'])) {
                $storage = new \Upload\Storage\FileSystem('assets/images');
                $image = new \Upload\File('image',$storage);
                $image->setName(uniqid());
                $image->addValidations(array(
                    new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
                    'image/jpg', 'image/jpeg')),
                    new \Upload\Validation\Size('5M')
                ));

                $image->upload();
                $imageName = $image->getNameWithExtension();
            } else {
                $imageName = '';
            }

            $data = [
            'content'	=>	$request->getParams()['content'],
            'image'	    =>	$imageName,
            'group_id' 	=> 	$groupId,
            'creator'	=>	$userId,
        ];

            $addPost = $posts->createData($data);

            $this->flash->addMessage('succes', 'Kiriman baru berhasil dibuat');
        }elseif ($userGroup == NULL) {

            $this->flash->addMessage('error', 'Anda tidak diijinkan membuat kiriman di grup ini');
        }else {
            $_SESSION['errors'] = $this->validator->errors();
            $_SESSION['old']  = $request->getParams();
        }

        return $response->withRedirect($this->router
        ->pathFor('enter.group', ['id' => $groupId]));
    }

    public function delPost($request, $response, $args)
    {
        $posts = new PostModel($this->db);
        $userGroups = new UserGroupModel($this->db);

        $userId = $_SESSION['login']['id'];
        $groupId = $args['group'];
        $userGroup = $userGroups->finds('group_id', $groupId, 'user_id', $userId);
        $post = $posts->find('id', $args['id']);

        if ($userGroup[0]['status'] == 1 || $post['creator'] == $userId) {

            $posts->hardDelete($args['id']);

            $this->flash->addMessage('succes', 'Kiriman telah dihapus');
        } else {

            $this->flash->addMessage('error', 'Anda tidak diijinkan menghapus kiriman ini!');
        }

        return $response->withRedirect($this->router
        ->pathFor('enter.group', ['id' => $groupId]));
    }

}

?>
