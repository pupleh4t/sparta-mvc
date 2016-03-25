<?php

class GeoToolsController extends \Phalcon\Mvc\Controller
{
    public function getBearingAction($lat1, $lng1, $lat2, $lng2)
    {
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $lambda1 = deg2rad($lng1);
        $lambda2 = deg2rad($lng2);

        $y = sin($lambda2-$lambda1) * cos($phi2);
        $x = ( cos($phi1) * sin($phi2) ) -
            ( sin($phi1) * cos($phi2) * cos($lambda2-$lambda1));

        $bearing = atan2($y,$x);
        $bearingDeg = rad2deg($bearing);

        return $bearingDeg;
    }

    public function getDestinationPointAction($lat, $lng, $initBearing, $distance)
    {
        $phi1 = deg2rad($lat);
        $lambda1 = deg2rad($lng);

        $bearing=deg2rad($initBearing);
        $radius = 6371000.00; // in meters
        $d = $distance/$radius;

        $phi2 = asin( (sin($phi1) * cos($d)) + (cos($phi1) * sin($d) * cos($bearing)));

        $y = sin($bearing) * sin($d) * cos($phi1);
        $x = cos($d) - sin($phi1) * sin($phi2);
        $lambda2 = $lambda1 + atan2($y,$x);

        $lat2 = rad2deg($phi2);
        $lng2 = rad2deg($lambda2);

        $lng2 = fmod($lng2+540, 360) - 180;

        $latlng = array('latitude'=>$lat2,'longitude'=>$lng2);

        return $latlng;
    }

    public function getDistanceVer2Action($lat1, $lng1, $lat2, $lng2)
    {
        $radius = 6371000; // in meters

        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $delta_phi = deg2rad($lat2-$lat1);
        $delta_lambda = deg2rad($lng2-$lng1);

        $a = (sin($delta_phi/2) * sin($delta_phi/2))
            + (cos($phi1) * cos($phi2) * sin($delta_lambda/2) * sin($delta_lambda/2));

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        $distance = $radius * $c;

        return $distance;
    }

    public function getDistanceAction($lat1, $lng1, $lat2, $lng2)
    {
        $R = 6371000;

        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $lambda1 = deg2rad($lng1);
        $lambda2 = deg2rad($lng2);

        $deltaPhi = $phi2 - $phi1;
        $deltaLambda = $lambda2 - $lambda1;

        $a = (sin($deltaPhi/2) * sin($deltaPhi/2))
            + (cos($phi1) * cos($phi2) * sin($deltaLambda/2) * sin($deltaLambda/2));

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        $d = $R * $c;

        return $d;
    }

    public function getMiddlePoint($arrayLatLng)
    {
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

        $output = array(
            "latitude" => $midLat,
            "longitude" => $midLng,
            "count" => $totalWeight
        );
        return $output;
    }
}

