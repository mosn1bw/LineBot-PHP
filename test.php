<?php

// $countryDataFile = fopen("countryData.json", "r") or die("Unable to open file!");
// $countryData = json_decode(fgets($countryDataFile), true);
// fclose($countryDataFile);
$negara = isset($a[1]) ? $a[1] : 'china';
$data = file_get_contents("https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/ncov_cases/FeatureServer/1/query?f=json&where=(Country_Region%3D'$negara')&returnGeometry=false&spatialRef=esriSpatialRelIntersects&outFields=*&orderByFields=Country_Region%20asc,Province_State%20asc&resultOffset=0&resultRecordCount=250&cacheHint=false");
$data= json_decode($data);
var_dump($data);

$total =0;
$positif =0;
$sembuh =0;
$mati =0;
foreach ( $data->features as $value) {
    $rawResponse = $value->attributes;
    $total +=$rawResponse->Confirmed;
    $positif +=$rawResponse->Active;
    $sembuh +=$rawResponse->Recovered;
    $mati +=$rawResponse->Deaths;
}
echo $data->features[0]->attributes->Last_Update;

echo $total ." ".$positif." ".$sembuh." ".$mati;
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