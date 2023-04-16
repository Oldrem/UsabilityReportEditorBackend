<?php

namespace app\repositories;

use app\config\Database;
use mysqli;

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

    function create($reportBlock): bool
    {
        $query = "INSERT INTO " . $this->table_name . " (title, author_id, text) VALUES (?, ?, ?)";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "sis", $reportBlock->author_id, $reportBlock->title, $reportBlock->text);
        if ($stmt->execute()){
            return true;
        }
        return false;
    }
}