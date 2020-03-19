<?php

$data =  file_get_contents('https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/ncov_cases/FeatureServer/1/query?f=json&where=(Confirmed%3E%200)%20OR%20(Deaths%3E0)%20OR%20(Recovered%3E0)&returnGeometry=false&spatialRef=esriSpatialRelIntersects&outFields=*&orderByFields=Country_Region%20asc,Province_State%20asc&resultOffset=0&resultRecordCount=250&cacheHint=false');
$data = json_decode($data);
$countryData=[];
foreach ($data->features as $value) {
  if ($value->attributes->Province_State==null) {
    $countryData[strtolower($value->attributes->Country_Region)]['Lat']=number_format($value->attributes->Lat, 4, '.', '');
    $countryData[strtolower($value->attributes->Country_Region)]['Long_']=number_format($value->attributes->Long_, 4, '.', '');
  }else{
    $countryData[strtolower($value->attributes->Country_Region.' at '.$value->attributes->Province_State)]['Lat']=number_format($value->attributes->Lat, 4, '.', '');
    $countryData[strtolower($value->attributes->Country_Region.' at '.$value->attributes->Province_State)]['Long_']=number_format($value->attributes->Long_, 4, '.', '');
  }
}
$myfile = fopen("countryData.json", "w") or die("Unable to open file!");
fwrite($myfile, json_encode($countryData));
?>