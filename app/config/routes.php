<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 25/03/16
 * Time: 0:18
 */

use \Phalcon\Mvc\Router;

$router = new Router(false);

$router->addPost("/user/register", array('controller'=>'user', 'action'=>'register'));
$router->addPost("/user/login", array('controller'=>'user', 'action'=>'login'));

$router->addGet("/data/lahan", array('controller' => 'index', 'action'=>'home'));
$router->addPost("/data/slot", array('controller'=>'maps', 'action'=>'slot'));
$router->addPost("/data/latlng", array('controller'=>'maps', 'action'=>'postlatlng'));
$router->addPost("/data/calibrate", array('controller'=>'maps', 'action'=>'calibrate'));
$router->addPost("/temp/savetemp", array('controller'=>'maps', 'action'=>'saveTemp'));

$router->notFound(array("controller"=>"index", "action"=>"route404"));

return $router;
