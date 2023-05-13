<?php

namespace app\controllers;

use app\exceptions\NotAuthorizedHttpException;
use app\model\ReportBlock;
use app\repositories\ReportBlockRepository;
use app\repositories\ReportRepository;

class ReportBlockController extends Controller
{
    private ReportBlockRepository $reportBlockRepository;
    private ReportRepository $reportRepository;
    public function __construct()
    {
        parent::__construct();
        $this->reportBlockRepository = new ReportBlockRepository();
        $this->reportRepository = new ReportRepository();
    }


    public function getAllByReportId($reportId)
    {
        $reportBlocks = $this->reportBlockRepository->findAllByReportId($reportId);
        $report = $this->reportRepository->findById($reportId);
        $this->response->json([
            [
                'blocks' => $reportBlocks,
                'authorId' => $report->author_id
            ]
        ]);
    }

    public function create()
    {
        $reportBlock = $this->getReportBlockFromBody();
        if (!$this->isOwner($reportBlock->reportId)){
            throw new NotAuthorizedHttpException("Not authorized to work with this report");
        }
        $result = $this->reportBlockRepository->create($reportBlock);
        $this->response->json([
            $result
        ]);
    }

    public function update($reportId)
    {

        $reportBlock = $this->getReportBlockFromBody();
        if (!$this->isOwner($reportBlock->reportId)){
            throw new NotAuthorizedHttpException("Not authorized to work with this report");
        }
        $result = $this->reportBlockRepository->update($reportId, $reportBlock, $this->request->reportBlock['oldParent'], $this->request->reportBlock['oldPosition']);
        $this->response->json([
            $result
        ]);
    }

    public function delete($reportId)
    {

        $result = $this->reportBlockRepository->delete($reportId);
        $this->response->json([
            $result
        ]);
    }

    public function deleteWithChildren($reportId)
    {

        $result = $this->reportBlockRepository->deleteWithChildren($reportId);
        $this->response->json([
            $result
        ]);
    }

    private function getReportBlockFromBody()
    {
        $reportBlock = new ReportBlock();
        if (isset($this->request->reportBlock['id'])){
            $reportBlock->id = $this->request->reportBlock['id'];
        }
        $reportBlock->parentId = $this->request->reportBlock['parentId'];
        $reportBlock->reportId = $this->request->reportBlock['reportId'];
        $reportBlock->position = $this->request->reportBlock['position'];
        $reportBlock->title = $this->request->reportBlock['title'];
        $reportBlock->content = $this->request->reportBlock['content'];
        return $reportBlock;
    }

    private function isOwner($reportId){
        $report = $this->reportRepository->findById($reportId);
        return $report->author_id == $this->request->uid;
    }

}