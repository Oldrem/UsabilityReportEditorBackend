<?php

namespace app\controllers;

use app\model\Report;
use app\repositories\ReportRepository;

class ReportController extends Controller
{
    private ReportRepository $reportRepository;
    public function __construct()
    {
        parent::__construct();
        $this->reportRepository = new ReportRepository();
    }
    public function getAllUserReports()
    {
        $reports = $this->reportRepository->findByAuthor($this->request->uid);
        $this->response->json([
            $reports
        ]);
    }

    public function createReport()
    {
        $report = new Report();
        $report->author_id = $this->request->uid;
        $report->title = $this->request->report['title'];
        $report->text = $this->request->report['text'];

        $result = $this->reportRepository->create($report);
        $this->response->json([
            $result
        ]);
    }
    public function updateReport($reportId)
    {
        $report = new Report();
        $report->id = $this->request->report['id'];
        $report->author_id = $this->request->report['author_id'];
        $report->title = $this->request->report['title'];
        $report->text = $this->request->report['text'];

        $result = $this->reportRepository->update($reportId, $report);
        $this->response->json([
            $result
        ]);
    }
    public function deleteReport($reportId)
    {
        $result = $this->reportRepository->delete($reportId);
        $this->response->json([
            $result
        ]);
    }
}