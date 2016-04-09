<?php
use Phalcon\Http\Response;
use Phalcon\Mvc\Url;
require APP_PATH."/app/library/vendor/autoload.php";
use Mailgun\Mailgun;
use Http\Adapter\Guzzle6\Client;

class IndexController extends ControllerBase
{
    public function indexAction()
    {

    }

    public function searchAction($name)
    {
        $lahans = Lahanparkir::find(array("deskripsi LIKE :name:", "bind"=>array("name"=>'%'.$name.'%')));
        unset($array_lahan);
        $array_lahan = array();
        foreach($lahans as $lahan){
            $sisa_mobil = Slotlahanparkir::count(array("id_lahan = :id_lahan: AND status = 'FREE'", "bind"=>array("id_lahan"=>$lahan->id_lahan)));
            $array_lahan[] = array(
                "id_lahan"=>$lahan->id_lahan,
                "deskripsi"=>$lahan->deskripsi,
                "alias"=>$lahan->alias,
                "latitude"=>$lahan->latitude,
                "longitude"=>$lahan->longitude,
                "sisa_kapasitas_mobil"=>"$sisa_mobil",
                "max_kapasitas_mobil"=>$lahan->max_kapasitas_mobil,
                "max_kapasitas_motor"=>$lahan->max_kapasitas_motor,
                "jam_buka"=>$lahan->jam_buka,
                "jam_tutup"=>$lahan->jam_tutup,
                "link_gambar"=>$lahan->link_gambar
            );
        }
        $json_data = array("data"=>$array_lahan);
        $response = new Response();
        $response->setJsonContent($json_data);
        return $response;
    }

    public function homeAction()
    {
        $datas = Lahanparkir::find();
        foreach($datas as $data){
            $sisa_mobil = Slotlahanparkir::count(array("id_lahan = :id_lahan: AND status = 'FREE'", "bind"=>array("id_lahan"=>$lahan->id_lahan)));
            $array_data[] = array(
                "id_lahan"=>$data->id_lahan,
                "deskripsi"=>$data->deskripsi,
                "alias"=>$data->alias,
                "latitude"=>$data->latitude,
                "longitude"=>$data->longitude,
                "sisa_kapasitas_mobil"=>"$sisa_mobil",
                "max_kapasitas_mobil"=>$data->max_kapasitas_mobil,
                "max_kapasitas_motor"=>$data->max_kapasitas_motor,
                "jam_buka"=>$data->jam_buka,
                "jam_tutup"=>$data->jam_tutup,
                "link_gambar"=>$data->link_gambar
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

