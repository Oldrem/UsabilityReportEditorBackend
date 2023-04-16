<?php

namespace app\controllers;

use app\repositories\ReportRepository;

class ReportController extends Controller
{
    private ReportRepository $reportRepository;
    public function __construct()
    {
        parent::__construct();
        $this->reportRepository = new ReportRepository();
    }
    public function getAllReports()
    {
        $reports = $this->reportRepository->findAll();
        $this->response->json([
            [
                'reports' => $reports
            ]
        ]);
    }
}