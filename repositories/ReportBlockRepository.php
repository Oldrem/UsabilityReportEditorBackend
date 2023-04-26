<?php

namespace app\repositories;

use app\config\Database;
use app\model\ReportBlock;
use mysqli;
use mysqli_sql_exception;

class ReportBlockRepository
{
    private mysqli $database;
    private string $table_name = "report_blocks";

    public function __construct()
    {
        $db = new Database();
        $this->database = $db->getConnection();
    }

    function findAllByReportId($reportId)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE report_id = ? ORDER BY parent_id";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "i", $reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        $tree = array();

        while ($row = $result->fetch_assoc()) {
            extract($row);

            $id = $row["id"];
            $parentId = $row["parent_id"];
            $node = array(
                'id' => $id,
                'parentId' => $parentId,
                'reportId' => $row["report_id"],
                'importReportId' => $row["import_report_id"],
                'title' => $row["title"],
                'content' => $row["content"]
            );

            $tree[] = $node;

            /*if ($parentId !== null) {
                if (isset($tree[$parentId])){

                    $parentNode = $tree[$parentId];
                    $parentNode['children'][] = $node;
                }
            } else {
                $tree[$id] = $node;
            }*/

        }
        return $this->parseTreeToJson($tree);
    }

    function parseTreeToJson($tree) {
        $json = array();

        // Find the root elements (those without a parent)
        foreach ($tree as $node) {
            if ($node['parentId'] == null) {
                $json[] = $this->parseNode($node, $tree);
            }
        }

        return $json;
    }

    function parseNode($node, $tree) {
        $jsonNode = array(
            'id' => $node['id'],
            'parentId' => $node['parentId'],
            'reportId' => $node["reportId"],
            'importReportId' => $node["importReportId"],
            'title' => $node["title"],
            'content' => $node["content"],
            'children' => array()
        );

        // Find the children of the current node
        foreach ($tree as $childNode) {
            if ($childNode['parentId'] == $node['id']) {
                $jsonNode['children'][] = $this->parseNode($childNode, $tree);
            }
        }

        return $jsonNode;
    }



    /*function findAllByReportId($reportId): ReportBlock
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE $reportId = ?";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "i", $reportId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $reportBlock = new ReportBlock();
        $reportBlock->id = $row["id"];
        $reportBlock->author_id = $row["author_id"];
        $reportBlock->title = $row["title"];
        $reportBlock->text = $row["text"];

        return $reportBlock;
    }*/

    function create($reportBlock)
    {
        $query = "INSERT INTO " . $this->table_name . " (parent_id, report_id, import_report_id, title, content) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "iiiss",
            $reportBlock->parentId,
            $reportBlock->reportId,
            $reportBlock->importReportId,
            $reportBlock->title,
            $reportBlock->content);
        if ($stmt->execute()){
            $reportBlock->id = $this->database->insert_id;
            return $reportBlock;
        }
        else{
            throw new mysqli_sql_exception("Database SQL exception.");
        }
    }

    function update($reportId, $reportBlock): ReportBlock
    {
        $query = "UPDATE " . $this->table_name . " SET parent_id=?, report_id=?, import_report_id=?, title=?, content=? WHERE id=?";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "iiissi",
            $reportBlock->parentId,
            $reportBlock->reportId,
            $reportBlock->importReportId,
            $reportBlock->title,
            $reportBlock->content,
            $reportId);
        if ($stmt->execute()){
            return $reportBlock;
        }
        else {
            throw new mysqli_sql_exception("Database SQL exception.");
        }
    }

    function delete($blockId): bool
    {
        $parent_id = null;
        $selectStmt = $this->database->prepare("SELECT parent_id FROM " . $this->table_name . " WHERE id = ?");
        $selectStmt->bind_param("i", $blockId);
        if ($selectStmt->execute()){
            $result = $selectStmt->get_result();
            $row = $result->fetch_assoc();
            $parent_id = $row['parent_id'];
        }
        else{
            throw new mysqli_sql_exception("Database SQL exception.");
        }

        $updateStmt = $this->database->prepare("UPDATE " . $this->table_name . " SET parent_id = ? WHERE parent_id = ?");
        $updateStmt->bind_param("ii", $parent_id, $blockId);

        $deleteStmt = $this->database->prepare("DELETE FROM " . $this->table_name . " WHERE id =?");
        $deleteStmt->bind_param("i", $blockId);

        if ($updateStmt->execute() && $deleteStmt->execute()){
            return true;
        }
        else{
            throw new mysqli_sql_exception("Database SQL exception.");
        }
    }

    function deleteWithChildren($reportId): bool
    {
        $query = "WITH RECURSIVE cte AS (
                      SELECT id FROM " . $this->table_name . " WHERE id=?
                      UNION ALL
                      SELECT t.id FROM " . $this->table_name . " t
                      JOIN cte ON cte.id = t.parent_id
                    )
                  DELETE FROM " . $this->table_name . " WHERE id IN (SELECT id FROM cte)";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "i", $reportId);
        if ($stmt->execute()){
            return true;
        }
        else {
            throw new mysqli_sql_exception("Database SQL exception.");
        }
    }
}