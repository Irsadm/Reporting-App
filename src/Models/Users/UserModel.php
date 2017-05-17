<?php

namespace App\Models\Users;

use App\Models\BaseModel;

class UserModel extends BaseModel
{
    protected $table = 'users';
    protected $column = ['id', 'name', 'email', 'username', 'password', 'gender', 'address', 'phone', 'image', 'updated_at', 'created_at', 'is_admin'];

    public function createUser(array $data, $images = 'avatar.png')
    {
        $data = [
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'gender' => $data['gender'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'image' => $images,
        ];

        $this->createData($data);
        return $this->db->lastInsertId();
    }

    public function register(array $data)
    {
        $data = [
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'gender' => $data['gender'],
            'phone' => $data['phone'],
        ];

        $this->createData($data);
        return $this->db->lastInsertId();
    }

    public function update(array $data, $images, $id)
    {
        $data = [
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'gender' => $data['gender'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'image' => $images,
        ];
        $this->updateData($data, $id);
    }

    public function updateUser(array $data, $id)
    {
        $data = [
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'gender' => $data['gender'],
            'address' => $data['address'],
            'phone' => $data['phone'],
        ];
        $this->updateData($data, $id);
    }

    public function getAllUser()
        {
            $qb = $this->db->createQueryBuilder();
                $qb->select('*')
                         ->from($this->table)
                         ->where('is_admin = 0 && deleted = 0');
                $query = $qb->execute();
                return $query->fetchAll();
        }

    public function checkDuplicate($username, $email)
    {
        $checkUsername = $this->find('username', $username);
        $checkEmail = $this->find('email', $email);
        if ($checkUsername) {
            return 1;
        } elseif ($checkEmail) {
            return 2;
        }
        return false;
    }

}
