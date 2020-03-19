<?php

$countryDataFile = fopen("countryData.json", "r") or die("Unable to open file!");
$countryData = json_decode(fgets($countryDataFile), true);
fclose($countryDataFile);
$negara = isset($a[1]) ? $a[1] : 'china';
$parameter = $countryData[strtolower(trim($negara))];
$data = file_get_contents('https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/ncov_cases/FeatureServer/1/query?f=json&where=(Lat%3D'.$parameter['Lat'].')%20OR%20(Long_%3D'.$parameter['Long_'].')&returnGeometry=false&spatialRef=esriSpatialRelIntersects&outFields=*&orderByFields=Country_Region%20asc,Province_State%20asc&resultOffset=0&resultRecordCount=250&cacheHint=false');
$data= json_decode($data);
var_dump($data);
// $response="";
// foreach ($data->features as $value) {
//     $rawResponse = $value->attributes;
//     $response.="Negara : ".$rawResponse->Country_Region."\n".
//     "Jumlah Kasus : ".$rawResponse->Confirmed."\n".
//     "Total Terinfeksi : ".$rawResponse->Active."\n".
//     "Total Sembuh : ".$rawResponse->Recovered."\n".
//     "Total Meninggal : ".$rawResponse->Deaths."\n".
//     "Update : ".date("Y-m-d H:i:s", substr( $rawResponse->Last_Update, 0, 10))
//     ;
// }
// echo $response;
?>