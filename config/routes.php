<?php

use app\exceptions\{BadRequestHttpException, NotAuthorizedHttpException};
use app\middlewares\{AuthenticationMiddleware, RequestBodyMiddleware};
use Pecee\{Http\Request, SimpleRouter\Exceptions\NotFoundHttpException, SimpleRouter\SimpleRouter as Router};

const PROD = false;

Router::setDefaultNamespace('app\controllers');

Router::get('/', 'ReactController@run');


Router::group([
    'prefix' => 'api/',
    'middleware' => [
        RequestBodyMiddleware::class
    ]
], function () {
    Router::post('/auth/login', 'AuthenticationController@login');
    Router::post('/auth/register', 'AuthenticationController@register');
    Router::get('/blocks/reports/{id}', 'ReportBlockController@getAllByReportId')
        ->where(['id' => '[\d]+']);
    Router::post('/blocks', 'ReportBlockController@create');
    Router::put('/blocks/{id}', 'ReportBlockController@update')
        ->where(['id' => '[\d]+']);
    // authenticated routes
    Router::group([
        'middleware' => [
            AuthenticationMiddleware::class
        ]
    ], function () {
        Router::get('/auth/user', 'AuthenticationController@getUserInfo');
        Router::delete('/blocks/{id}', 'ReportBlockController@delete')
            ->where(['id' => '[\d]+']);
        Router::delete('/blocks/{id}/children', 'ReportBlockController@deleteWithChildren')
            ->where(['id' => '[\d]+']);

        //Router::get('/reports', 'ReportController@getAllReports');
        /*Router::post('/project/update/{id}', 'ProjectController@update')
            ->where(['id' => '[\d]+']);*/
    });
});

Router::get('/controller', 'ReactController@run')
    ->setMatch('/\/([\w]+)/');

Router::options('/controller', 'AuthenticationController@setCors')
    ->setMatch('/\/([\w]+)/');

Router::error(function(Request $request, Exception $exception) {
    $response = Router::response();
    switch (get_class($exception)) {
        case BadRequestHttpException::class: {
            $response->httpCode(400);
            break;
        }
        case NotAuthorizedHttpException::class: {
            $response->httpCode(401);
            break;
        }
        case NotFoundHttpException::class: {
            $response->httpCode(404);
            break;
        }
        case Exception::class: {
            $response->httpCode(500);
            break;
        }
    }
});