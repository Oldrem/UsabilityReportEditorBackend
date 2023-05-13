<?php

namespace app\repositories;

use app\config\OuterDatabase;
use app\model\Hypothesis;
use app\model\Task;
use mysqli;
class ImportRepository
{
    private mysqli $database;

    public function __construct()
    {
        $db = new OuterDatabase();
        $this->database = $db->getConnection();
    }

    public function findAllHypotheses(): array
    {
        $hypotheses = array();
        $query = "SELECT * FROM hypotheses";
        $stmt = $this->database->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            extract($row);
            $hypothesis = new Hypothesis();
            $hypothesis->id = $row["id"];
            $hypothesis->text = $row["text"];
            $hypotheses[] = $hypothesis;
        }
        return $hypotheses;
    }

    public function findAllTasks(): array
    {
        $tasks = array();

        $query = "SELECT * FROM tasks";
        $stmt = $this->database->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            extract($row);
            $task = new Task();
            $task->id = $row["id"];
            $task->text = $row["text"];
            $task->title = $row["title"];
            $tasks[] = $task;
        }
        return $tasks;
    }
}