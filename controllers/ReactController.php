<?php

namespace app\controllers;

class ReactController extends Controller
{
    public function run()
    {
        return $this->renderTemplate('../web/react.php');
    }
}