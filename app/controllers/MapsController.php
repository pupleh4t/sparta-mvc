<?php
use Phalcon\Http\Response;

class MapsController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

    }

    public function slotAction()
    {
        $json_data = $this->request->getJsonRawBody();
        $id_lahan = $json_data->id_lahan;

        $conditions = "id_lahan = :id_lahan:";
        $parameters = array("id_lahan"=>$id_lahan);
        $slots = Slotlahanparkir::find(array($conditions, "bind"=>$parameters));

        $data = array();
        foreach ($slots as $slot) {
            $data[] = array(
                'id_slot'=> $slot->id_slot,
                'latitude'=> $slot->latitude,
                'longitude'=> $slot->longitude,
                'status'=>$slot->status
            );
        }
        $json_data = array('slot_data'=>$data);

        $response = new Response();
        $response->setJsonContent($json_data);
        return $response;
    }

    public function updateAction()
    {
        $response = new Response();
        $json = $this->request->getJsonRawBody();
        $response->setJsonContent(array(
            "id_lahan"=>$json->id_lahan
        ));
        return $response;
    }

}

