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
        $json_data = $this->request->getJsonRawBody();
        $id_lahan = $json_data->id_lahan;
        $response = new Response();

        if (Slotlahanparkir::count("id_lahan=$id_lahan")==0){
            $json_response = array(
                'error' => true,
                'error_msg' => "No data for id_lahan = $id_lahan"
            );
            $response->setJsonContent($json_response);
        }else{
            $conditions = "id_lahan = :id_lahan:";
            $parameters = array("id_lahan"=>$id_lahan);
            $slots = Slotlahanparkir::find(array($conditions, "bind"=>$parameters));

            $totalWeight = 0;
            $X = $Y = $Z = 0;

            foreach($slots as $marker){
                $radLat = deg2rad($marker->latitude);
                $radLng = deg2rad($marker->longitude);

                $x = cos($radLat)*cos($radLng);
                $y = cos($radLat)*sin($radLng);
                $z = sin($radLat);

                $X = $X + $x;
                $Y = $Y + $y;
                $Z = $Z + $z;
                $totalWeight++;
            }

            $X = $X/$totalWeight;
            $Y = $Y/$totalWeight;
            $Z = $Z/$totalWeight;

            $radMidLat = atan2($Y,$X);
            $hyp = sqrt(($X*$X)+($Y*$Y));
            $radMidLng = atan2($Z, $hyp);

            $midLat = rad2deg($radMidLat);
            $midLng = rad2deg($radMidLng);

            $lahan = Lahanparkir::findFirst("id_lahan = $id_lahan");
            $lahan->latitude = $midLat;
            $lahan->longitude = $midLng;
            $lahan->max_kapasitas_mobil = $totalWeight;
            $status = $lahan->save();

            if($status == true){
                $json_response = array(
                    'error' => false,
                    'error_msg' => ""
                );
                $response->setJsonContent($json_response);
            }
            else{
                $json_response = array(
                    'error' => true,
                    'error_msg' => "failed to update the new LatLng Coordinate"
                );
                $response->setJsonContent($json_response);
            }
        }
        return $response;
    }

    public function postlatlngAction()
    {
        $json_data = $this->request->getJsonRawBody();
        $lat = $json_data->latitude;
        $lng = $json_data->longitude;

        $latlng = new TempLatlng();
        $latlng->latitude=$lat;
        $latlng->longitude=$lng;

        $response = new Response();
        if($latlng->save() == true){
            $response->setJsonContent(
                array(
                    "status"=>true
                )
            );
        }
        else{
            $response->setJsonContent(
                array(
                    "status"=>false
                )
            );
        }

        return $response;
    }

    public function saveTempAction(){
        $json_data = $this->request->getJsonRawBody();
        $id_lahan = $json_data->id_lahan;

        $conditions = "id_lahan = :id_lahan:";
        $parameters = array("id_lahan"=>$id_lahan);
        $arrayLatLng = Tempslotlahanparkir::find(array($conditions, "bind"=>$parameters));

        $response = new Response();

        $status = false;
        foreach($arrayLatLng as $latLng){
            $slot = new Slotlahanparkir();
            $slot->id_lahan = $id_lahan;
            $slot->latitude = $latLng->latitude;
            $slot->longitude = $latLng->longitude;
            $slot->status = "FREE";
            $status = $slot->create();
        }
        if($status == false){
            $json_response = array("error"=>true,"error_msg"=>"Error in copying temp data to main data");
            $response->setJsonContent($json_response);
        }
        else{
            $X = $Y = $Z = 0;
            $totalWeight = 0;
            foreach($arrayLatLng as $latLng){
                $radLat = deg2rad($latLng->latitude);
                $radLng = deg2rad($latLng->longitude);

                $x = cos($radLat)*cos($radLng);
                $y = cos($radLat)*sin($radLng);
                $z = sin($radLat);

                $X = $X + $x;
                $Y = $Y + $y;
                $Z = $Z + $z;
                $totalWeight++;
            }

            $X = $X/$totalWeight;
            $Y = $Y/$totalWeight;
            $Z = $Z/$totalWeight;

            $radMidLat = atan2($Y,$X);
            $hyp = sqrt(($X*$X)+($Y*$Y));
            $radMidLng = atan2($Z, $hyp);

            $midLat = rad2deg($radMidLat);
            $midLng = rad2deg($radMidLng);

            $lahan = Lahanparkir::findFirst(array($conditions, "bind"=>$parameters));
            $lahan->latitude = $midLat;
            $lahan->longitude = $midLng;
            $lahan->max_kapasitas_mobil = $totalWeight;
            $status = $lahan->save();

            if($status == false){
                $json_response = array("error"=>true,"error_msg"=>"Error calibrating the mid point");
                $response->setJsonContent($json_response);
            }
            else{
                $templahans = Tempslotlahanparkir::find(array($conditions, "bind"=>$parameters));
                $status = $templahans->delete();

                if($status == true){
                    $response->setJsonContent(array(
                        'error'=>false,
                        'error_msg'=>''
                    ));
                }
                else{
                    $response->setJsonContent(array(
                        'error'=>true,
                        'error_msg'=>'Error adding in database'
                    ));
                }
            }
        }
        return $response;
    }
}

