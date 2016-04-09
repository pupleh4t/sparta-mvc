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
$router->addGet("/user/activate/{email}/{token}", array('controller'=>'user', 'action'=>'activate'));
$router->addGet("/user/resend_email/{email}", array('controller'=>'user', 'action'=>'resendEmail'));

$router->addGet("/data/lahan", array('controller' => 'index', 'action'=>'home'));
$router->addGet("/data/search/{lahan}", array('controller' => 'index', 'action' =>'search'));
$router->addPost("/data/slot", array('controller'=>'maps', 'action'=>'slot'));
$router->addPost("/data/calibrate", array('controller'=>'maps', 'action'=>'calibrate'));
$router->addPost("/data/area", array('controller'=>'maps', 'action'=>'getArea'));

$router->addPost("/temp/latlng", array('controller'=>'temp', 'action'=>'singleLatLng'));
$router->addPost("/temp/multilatlng", array('controller'=>'temp', 'action'=>'multiLatLng'));
$router->addPost("/temp/savetemp", array('controller'=>'maps', 'action'=>'saveTemp'));

$router->notFound(array("controller"=>"index", "action"=>"route404"));

return $router;
