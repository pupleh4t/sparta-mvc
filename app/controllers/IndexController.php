<?php

class IndexController extends ControllerBase
{

    public function indexAction()
    {

    }

    public function route404Action()
    {
        $response = new \Phalcon\Http\Response();
        $response->setStatusCode(404, "Not Found");
        $response->setContent("Oops, Something might go wrong");
        $response->send();

        //echo "Oops, something might go wrong";
    }

}

