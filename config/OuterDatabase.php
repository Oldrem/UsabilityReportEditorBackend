<?php

namespace app\config;

use app\exceptions\BadRequestHttpException;

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'ntktgjhn1');
define('DB_NAME', 'thesis');


class OuterDatabase {
    function getConnection()
    {
        static $link;
        if (!$link){

            $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
            if (!$link) {

                die("ERROR: Could not connect. " . mysqli_connect_error());
            }
        }
        return $link;
    }
}