<?php

namespace app\controllers;

use app\model\ReportBlock;
use app\repositories\ReportBlockRepository;

class ReportBlockController extends Controller
{
    private ReportBlockRepository $reportBlockRepository;
    public function __construct()
    {
        parent::__construct();
        $this->reportBlockRepository = new ReportBlockRepository();
    }


    public function getAllByReportId($reportId)
    {
        $reportBlocks = $this->reportBlockRepository->findAllByReportId($reportId);
        $this->response->json([
            [
                'blocks' => $reportBlocks
            ]
        ]);
    }

    public function create()
    {
        $reportBlock = $this->getReportBlockFromBody();
        $result = $this->reportBlockRepository->create($reportBlock);
        $this->response->json([
            $result
        ]);
    }

    public function update($reportId)
    {

        $reportBlock = $this->getReportBlockFromBody();
        $result = $this->reportBlockRepository->update($reportId, $reportBlock);
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
        $reportBlock->importReportId = $this->request->reportBlock['importReportId'];
        $reportBlock->title = $this->request->reportBlock['title'];
        $reportBlock->content = $this->request->reportBlock['content'];

        return $reportBlock;
    }


}