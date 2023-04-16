<?php

namespace app\controllers;
use app\config\Database;
use Pecee\Http\Request;
use Pecee\Http\Response;
use Pecee\SimpleRouter\SimpleRouter as Router;
abstract class Controller
{
    protected $response;
    protected $request;
    protected $database;
    public function __construct()
    {
        $this->request = Router::router()->getRequest();
        $this->response =  new Response($this->request);
        $this->setCors();
    }

    public function renderTemplate($template) {
        ob_start();
        include $template;
        return ob_get_clean();
    }

    public function setCors()
    {
        $this->response->header('Access-Control-Allow-Origin: *');
        $this->response->header('Access-Control-Request-Method: POST, GET, OPTIONS, PUT, DELETE, HEAD');
        $this->response->header("Access-Control-Allow-Headers: *");
        $this->response->header('Access-Control-Allow-Credentials: true');
        $this->response->header('Access-Control-Max-Age: 1728000');
    }
}