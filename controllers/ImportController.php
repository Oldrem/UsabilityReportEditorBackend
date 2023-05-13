<?php

namespace app\controllers;

use app\repositories\ImportRepository;

class ImportController extends Controller
{
    private ImportRepository $importRepository;
    public function __construct()
    {
        parent::__construct();
        $this->importRepository = new ImportRepository();
    }


    public function getAllHypotheses()
    {
        $hypotheses = $this->importRepository->findAllHypotheses();
        $this->response->json(
            $hypotheses
        );
    }

    public function getAllTasks()
    {
        $tasks = $this->importRepository->findAllTasks();
        $this->response->json(
            $tasks
        );
    }
}