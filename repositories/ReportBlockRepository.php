<?php

namespace app\repositories;

use app\config\Database;
use app\model\ReportBlock;
use mysqli;
use mysqli_sql_exception;
use Pecee\SimpleRouter\Exceptions\NotFoundHttpException;

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
        $query = "SELECT * FROM " . $this->table_name . " WHERE report_id = ? ORDER BY parent_id, position";
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
                'position' => $row["position"],
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
            'position' => $node["position"],
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
        $query = "INSERT INTO " . $this->table_name . " (parent_id, report_id, position, title, content) VALUES (?, ?, ?, ?, ?)";
        $maxPositionQuery = "";
        $maxPositionStmt = null;
        if ($reportBlock->parentId == null){
            $maxPositionQuery = "SELECT MAX(position) FROM " . $this->table_name . " WHERE parent_id IS NULL AND report_id = ?";
            $maxPositionStmt = $this->database->prepare($maxPositionQuery);
            mysqli_stmt_bind_param($maxPositionStmt, "i", $reportBlock->reportId);
        }
        else {
            $maxPositionQuery = "SELECT MAX(position) FROM " . $this->table_name . " WHERE parent_id = ?";
            $maxPositionStmt = $this->database->prepare($maxPositionQuery);
            mysqli_stmt_bind_param($maxPositionStmt, "i", $reportBlock->parentId);
        }
        $stmt = $this->database->prepare($query);
        $position = 1;
        if ($maxPositionStmt->execute()){
            $result = $maxPositionStmt->get_result();
            $row = $result->fetch_assoc();
            $position = $row['MAX(position)'];
        }
        else {
            throw new mysqli_sql_exception("Database SQL exception.");
        }
        $position++;
        mysqli_stmt_bind_param($stmt, "iiiss",
            $reportBlock->parentId,
            $reportBlock->reportId,
            $position,
            $reportBlock->title,
            $reportBlock->content);


        if ($stmt->execute()){
            $reportBlock->id = $this->database->insert_id;
            $reportBlock->position = $position;
            return $reportBlock;
        }
        else{
            throw new mysqli_sql_exception("Database SQL exception.");
        }
    }

    function update($reportId, $reportBlock, $oldParent, $oldPosition): ReportBlock
    {
        $updateQuery = "UPDATE " . $this->table_name . " SET parent_id=?, report_id=?, position=?, title=?, content=? WHERE id=?";
        $updateStmt = $this->database->prepare($updateQuery);
        if ($oldPosition !== null){
            if ($reportBlock->parentId !== $oldParent){
                $this->moveItem($oldParent, $oldPosition, PHP_INT_MAX);
                $this->moveItem($reportBlock->parentId, PHP_INT_MAX, $reportBlock->position);
            }
            else{
                $this->moveItem($reportBlock->parentId, $oldPosition, $reportBlock->position);
            }
        }
        mysqli_stmt_bind_param($updateStmt, "iiissi",
            $reportBlock->parentId,
            $reportBlock->reportId,
            $reportBlock->position,
            $reportBlock->title,
            $reportBlock->content,
            $reportId);
        if ($updateStmt->execute()){
            return $reportBlock;
        }
        else {
            throw new mysqli_sql_exception("Database SQL exception.");
        }
    }

    function moveItem($parentId, $oldPosition, $newPosition)
    {
        $updatePositionQuery = "";
        $updatePositionStmt = null;
        if ($oldPosition <= $newPosition){
            $updatePositionQuery = "UPDATE " . $this->table_name . " SET position = position - 1 WHERE parent_id <=> ? AND position >= ? AND position <= ?";
            $updatePositionStmt = $this->database->prepare($updatePositionQuery);
            mysqli_stmt_bind_param($updatePositionStmt, "iii", $parentId, $oldPosition, $newPosition);
        }
        else {
            $updatePositionQuery = "UPDATE " . $this->table_name . " SET position = position + 1 WHERE parent_id <=> ? AND position >= ? AND position <= ?";
            $updatePositionStmt = $this->database->prepare($updatePositionQuery);
            mysqli_stmt_bind_param($updatePositionStmt, "iii", $parentId, $newPosition, $oldPosition);
        }

        if ($updatePositionStmt->execute()){
            return;
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
            if ($result->num_rows <= 0){
                throw new NotFoundHttpException();
            }
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

        if ($updateStmt->execute()){
            if ($deleteStmt->execute()){
                return true;
            }
            else {
                throw new mysqli_sql_exception("Database SQL exception.");
            }
        }
        else{
            throw new mysqli_sql_exception("Database SQL exception.");
        }
    }

    function deleteWithChildren($blockId): bool
    {
        //Requires MySQL 8.0 or higher
       /* $query = "WITH RECURSIVE cte AS (
                      SELECT id FROM " . $this->table_name . " WHERE id=?
                      UNION ALL
                      SELECT t.id FROM " . $this->table_name . " t
                      JOIN cte ON cte.id = t.parent_id
                    )
                  DELETE FROM " . $this->table_name . " WHERE id IN (SELECT id FROM cte)";*/

        $query = "DELETE FROM " . $this->table_name . " WHERE id =?";
        $stmt = $this->database->prepare($query);
        mysqli_stmt_bind_param($stmt, "i", $blockId);
        if ($stmt->execute()){
            return true;
        }
        else {
            throw new mysqli_sql_exception("Database SQL exception.");
        }
    }
}