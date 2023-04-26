<?php

namespace app\repositories;

use app\config\Database;
use app\model\User;
use mysqli;

class UserRepository
{
    private mysqli $database;
    private string $table_name = "users";

    public function __construct()
    {
        $db = new Database();
        $this->database = $db->getConnection();
    }

    function findAll(): array
    {
        $users = array();

        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->database->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            extract($row);
            $user = new User();
            $user->id = $row["id"];
            $user->username = $row["username"];
            $user->password = $row["password"];

            $users[] = $user;
        }


        return $users;
    }

    function findByUsername($username)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = ?";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()){
            $user = new User();
            $user->id = $row["id"];
            $user->username = $row["username"];
            $user->password = $row["password"];
        }
        return $user;
    }

    function findById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()){
            $user = new User();
            $user->id = $row["id"];
            $user->username = $row["username"];
            $user->password = $row["password"];
        }
        return $user;
    }

    function create($user): bool
    {
        $query = "INSERT INTO " . $this->table_name . " (username, password) VALUES (?, ?)";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "ss", $user->username, $user->password);
        if ($stmt->execute()){
            return true;
        }
        return false;
    }
}