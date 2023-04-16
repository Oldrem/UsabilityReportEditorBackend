<?php

namespace app\controllers;

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
}