<?php

namespace app\repositories;

use app\config\Database;
use app\model\Report;
use app\model\ReportBlock;
use mysqli;
use mysqli_sql_exception;

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

    function findByAuthor($author_id): array
    {
        $reports = array();

        $query = "SELECT * FROM " . $this->table_name . " WHERE author_id = ?";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "i", $author_id);
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

    function create($report)
    {
        $query = "INSERT INTO " . $this->table_name . " (author_id, title, text) VALUES (?, ?, ?)";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "iss", $report->author_id, $report->title, $report->text);
        if ($stmt->execute()){
            $report->id = $this->database->insert_id;

            return $report;
        }
        else {
            throw new mysqli_sql_exception("Database SQL exception.");
        }
    }

    function update($reportId, $report): Report
    {
        $query = "UPDATE " . $this->table_name . " SET author_id=?, title=?, text=? WHERE id=?";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "issi",
            $report->author_id,
            $report->title,
            $report->text,
            $reportId);
        if ($stmt->execute()){
            return $report;
        }
        else {
            throw new mysqli_sql_exception("Database SQL exception.");
        }
    }

    function delete($reportId): bool
    {
        $stmt = $this->database->prepare("DELETE FROM " . $this->table_name . " WHERE id =?");
        $stmt->bind_param("i", $reportId);
        if ($stmt->execute()){
            return true;
        }
        else{
            throw new mysqli_sql_exception("Database SQL exception.");
        }
    }
}