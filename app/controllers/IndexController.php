<?php
use Phalcon\Http\Response;
class IndexController extends ControllerBase
{

    public function indexAction()
    {

    }

    public function homeAction()
    {
        $datas = Lahanparkir::find();
        foreach($datas as $data){
            $array_data[] = array(
                "id_lahan"=>$data->id_lahan,
                "deskripsi"=>$data->deskripsi,
                "latitude"=>$data->latitude,
                "longitude"=>$data->longitude,
                "max_kapasitas_mobil"=>$data->max_kapasitas_mobil,
                "max_kapasitas_motor"=>$data->max_kapasitas_motor,
                "jam_buka"=>$data->jam_buka,
                "jam_tutup"=>$data->jam_tutup
            );
        }
        $json_data = array("data"=>$array_data);

        $response = new Response();
        $response->setJsonContent($json_data);
        return $response;
    }

    public function route404Action()
    {
        $response = new \Phalcon\Http\Response();
        $response->setStatusCode(404, "Not Found");
        $response->setContent("Oops, Something might go wrong");
        $response->send();
    }

}

