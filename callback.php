<?php

require_once("config.php");

function distance($a, $b){
    $lat1 = $a["lat"];
    $lon1 = $a["lng"];
    $lat2 = $b[0];
    $lon2 = $b[1];

    $x = deg2rad( $lon1 - $lon2 ) * cos( deg2rad( $lat1 ) );
    $y = deg2rad( $lat1 - $lat2 );
    $dist = 6371000.0 * sqrt( $x*$x + $y*$y );

    return $dist;
}

function getBikeLocationArray($dataURL, $countryID){
  $xml = simplexml_load_file($dataURL);
  $json = json_encode($xml);
  $array = json_decode($json,TRUE);
  $array = $array["country"][$countryID]["city"]["place"];
  return $array;
}

function getNextBike($location, $bikes){
  $ref = explode(';',$location);

  $distances = array_map(function($bike) use($ref) {
    $a = array_slice($bike["@attributes"], 1, 2);
    return distance($a, $ref);
  }, $bikes);

  asort($distances);

  $nextbike = $bikes[key($distances)]["@attributes"];
  while($nextbike["bikes"] < 1){
    $nextbike = next($bikes[key($distances)]);
  }

  return $nextbike["lat"] . "," .  $nextbike["lng"];
}


if(isset($_GET['location'])&&(isset($_GET['username']))){
  $location = $_GET['location'];
  $username = $_GET['username'];
} else {
  exit();
}

$bikes = getBikeLocationArray($bikedataURL, $countryID);
$nextBikeLocation = getNextBike($location, $bikes);

var_dump($bikes);

$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $apiURL);
curl_setopt($ch,CURLOPT_POST, 3);
curl_setopt($ch,CURLOPT_POSTFIELDS, "username=".$username."&api_token=".$apiToken."&location=".$nextBikeLocation);
$result = curl_exec($ch);
curl_close($ch);

?>