<?php
$countryDataFile = fopen("countryData.json", "r") or die("Unable to open file!");
$countryData = json_decode(fgets($countryDataFile), true);
fclose($countryDataFile);
echo $countryData['china']
// $parameter = isset($a[1]) ? $countryData[$a[1]] : '101';
// $data = file_get_contents('https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/ncov_cases/FeatureServer/1/query?f=json&where=(OBJECTID%3D'.$parameter.')&returnGeometry=false&spatialRef=esriSpatialRelIntersects&outFields=*&orderByFields=Country_Region%20asc,Province_State%20asc&resultOffset=0&resultRecordCount=250&cacheHint=false');
// $data= json_decode($data);
// var_dump($data);
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