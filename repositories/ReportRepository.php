<?php

namespace app\repositories;

use app\config\Database;
use app\model\Report;
use mysqli;

class ReportRepository
{
    private mysqli $database;
    private string $table_name = "reports";

    public function __construct()
    {
        $db = new Database();
        $this->database = $db->getConnection();
    }

    function findAll(): array
    {
        $reports = array();

        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->database->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            extract($row);
            $report = new Report();
            $report->id = $row["id"];
            $report->author_id = $row["author_id"];
            $report->title = $row["title"];
            $report->text = $row["text"];

            $reports[] = $report;
        }


        return $reports;
    }

    function findByAuthor($author_id): Report
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE author_id = ?";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "i", $author_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $report = new Report();
        $report->id = $row["id"];
        $report->author_id = $row["author_id"];
        $report->title = $row["title"];
        $report->text = $row["text"];

        return $report;
    }

    function create($report): bool
    {
        $query = "INSERT INTO " . $this->table_name . " (title, author_id, text) VALUES (?, ?, ?)";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "sis", $report->author_id, $report->title, $report->text);
        if ($stmt->execute()){
            return true;
        }
        return false;
    }
}