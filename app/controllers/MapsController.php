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

    public function calibrateAction()
    {
        $json_data = $this->request->getJsonRawBody();
        $id_lahan = $json_data->id_lahan;
        $response = new Response();
        $geoTools = new GeoToolsController();

        $conditions = "id_lahan = :id_lahan:";
        $parameters = array("id_lahan"=>$id_lahan);
        $slots = Slotlahanparkir::find(array($conditions, "bind"=>$parameters));

        if (count($slots) == 0){
            $json_response = array(
                'error' => true,
                'error_msg' => "No data for id_lahan = $id_lahan"
            );
            $response->setJsonContent($json_response);
        }else{
            $conditions = "id_lahan = :id_lahan:";
            $parameters = array("id_lahan"=>$id_lahan);
            $midPoints = $geoTools->getMiddlePoint($slots);

            $lahan = Lahanparkir::findFirst(array($conditions, "bind"=>$parameters));
            $lahan->latitude = $midPoints["latitude"];
            $lahan->longitude = $midPoints["longitude"];
            $lahan->max_kapasitas_mobil = $midPoints["count"];
            $status = $lahan->save();

            if($status == true){
                $response->setJsonContent(array('error' => false,'error_msg' => ""));
            }
            else{
                $response->setJsonContent(array('error' => true, 'error_msg' => "Error calibrating the mid point"));
            }
        }
        return $response;
    }

}

