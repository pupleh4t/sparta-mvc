<?php
use Phalcon\Http\Response;
class TempController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

    }

    public function saveTempAction(){
        $json_data = $this->request->getJsonRawBody();
        $id_lahan = $json_data->id_lahan;

        $response = new Response();
        $geoTools = new GeoToolsController();

        $conditions = "id_lahan = :id_lahan:";
        $parameters = array("id_lahan"=>$id_lahan);

        // Crawl record Tempslotlahanparkir based on id_lahan
        $arrayLatLng = Tempslotlahanparkir::find(array($conditions, "bind"=>$parameters));

        $status = false;

        // Clone data from Temp... to Slot...
        foreach($arrayLatLng as $latLng){
            $slot = new Slotlahanparkir();
            $slot->id_lahan = $id_lahan;
            $slot->latitude = $latLng->latitude;
            $slot->longitude = $latLng->longitude;
            $slot->status = "FREE";
            $status = $slot->create();
        }
        if($status == false){
            $response->setJsonContent(array("error"=>true,"error_msg"=>"Error in copying temp data to main data"));
        }
        else{
            // Calibrate middle point of all record Slot in Lahan...
            $slots = Slotlahanparkir::find(array($conditions, "bind"=>$parameters));
            $midPoints = $geoTools->getMiddlePoint($slots);

            $lahan = Lahanparkir::findFirst(array($conditions, "bind"=>$parameters));
            $lahan->latitude = $midPoints["latitude"];
            $lahan->longitude = $midPoints["longitude"];
            $lahan->max_kapasitas_mobil = $midPoints["count"];
            $status = $lahan->save();

            if($status == false){
                $json_response = array("error"=>true,"error_msg"=>"Error calibrating the mid point");
                $response->setJsonContent($json_response);
            }
            else{

                // Delete all Tempslot... based on id_lahan
                $templahans = Tempslotlahanparkir::find(array($conditions, "bind"=>$parameters));
                $status = $templahans->delete();

                if($status == true){
                    $response->setJsonContent(array('error'=>false,'error_msg'=>''));
                }
                else{
                    $response->setJsonContent(array('error'=>true,'error_msg'=>'Error adding in database'));
                }
            }
        }
        return $response;
    }

    public function singleLatLngAction()
    {
        $json_data = $this->request->getJsonRawBody();
        $lat = $json_data->latitude;
        $lng = $json_data->longitude;

        $latlng = new TempLatlng();
        $latlng->latitude=$lat;
        $latlng->longitude=$lng;

        $response = new Response();
        if($latlng->save() == true){
            $response->setJsonContent(array("error"=>false, "error_msg"=>""));
        }
        else{
            $response->setJsonContent(array("error"=>true, "error_msg"=>"Failed to save to database"));
        }
        return $response;
    }

    public function multiLatLngAction()
    {
        $json_data = $this->request->getJsonRawBody();
        $id_lahan = $json_data->id_lahan;
        $lat1 = $json_data->fromLatitude;
        $lng1 = $json_data->fromLongitude;
        $lat2 = $json_data->toLatitude;
        $lng2 = $json_data->toLongitude;
        $numIntermediatePoint = $json_data->numPoints;

        $status = false;

        $geoTools = new GeoToolsController();
        $distance = $geoTools->getDistanceAction($lat1, $lng1, $lat2, $lng2);
        $distancePoint = $distance/($numIntermediatePoint-1);
        $interval=$distancePoint;

        $initialBearing = $geoTools->getBearingAction($lat1, $lng1, $lat2, $lng2);

        $response = new Response();

        $array_data[] = array("latitude"=>$lat1,"longitude"=>$lng1);

        if($numIntermediatePoint>2){
            for($i=0; $i<$numIntermediatePoint-1; $i++){
                $array_data[] =$geoTools->getDestinationPointAction($lat1, $lng1, $initialBearing, $interval);
                $interval = $interval+$distancePoint;
            }

            foreach($array_data as $data){
                $temp_slot =  new Tempslotlahanparkir();
                $temp_slot->id_lahan = $id_lahan;
                $temp_slot->latitude = $data["latitude"];
                $temp_slot->longitude = $data["longitude"];
                $status = $temp_slot->create();
            }

            if($status == true){
                $response->setJsonContent(array('error'=>false,'error_msg'=>''));
            }
            else{
                $response->setJsonContent(array('error'=>true,'error_msg'=>'Error adding in database'));
            }
        }
        else{
            if($numIntermediatePoint==2){
                $array_data[] = array("latitude"=>$lat2, "longitude"=>$lng2);
                foreach($array_data as $data){
                    $temp_slot = new Tempslotlahanparkir();
                    $temp_slot->id_lahan = $id_lahan;
                    $temp_slot->latitude = $data["latitude"];
                    $temp_slot->longitude = $data["longitude"];
                    $status = $temp_slot->create();
                }
                if($status == true){
                    $response->setJsonContent(array('error'=>false,'error_msg'=>''));
                }
                else{
                    $response->setJsonContent(array('error'=>true,'error_msg'=>'Error adding in database'));
                }
            }
            else{
                $response->setJsonContent(array('error'=>true,'error_msg'=>'minimal points is 2'));
            }
        }
        return $response;
    }
}