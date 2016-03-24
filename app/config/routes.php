<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 25/03/16
 * Time: 0:18
 */

use \Phalcon\Mvc\Router;

$router = new Router(false);

$router->add("/update", array('controller'=>'maps', 'action'=>'update'));
$router->addPost("/user/register", array('controller'=>'user', 'action'=>'register'));
$router->addPost("/user/login", array('controller'=>'user', 'action'=>'login'));
$router->addPost("/data/slot", array('controller'=>'maps', 'action'=>'slot'));

$router->notFound(array("controller"=>"index", "action"=>"route404"));

return $router;
