<?php
$data =  file_get_contents('https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/ncov_cases/FeatureServer/1/query?f=json&where=(Confirmed%3E%200)%20OR%20(Deaths%3E0)%20OR%20(Recovered%3E0)&returnGeometry=false&spatialRef=esriSpatialRelIntersects&outFields=*&orderByFields=Country_Region%20asc,Province_State%20asc&resultOffset=0&resultRecordCount=250&cacheHint=false');
$data = json_decode($data);
$countryData=[];
foreach ($data->features as $value) {
    $countryData[strtolower($value->attributes->Country_Region)]['Lat']=(int)$value->attributes->Lat;
    $countryData[strtolower($value->attributes->Country_Region)]['Long_']=(int)$value->attributes->Long_;
}
// var_dump($countryData);
// echo $countryData['indonesia'];


$myfile = fopen("countryData.json", "w") or die("Unable to open file!");
// $txt = "John Doe\n";
fwrite($myfile, json_encode($countryData));
// // $txt = "Jane Doe\n";
// // fwrite($myfile, $txt);
// fclose($myfile);



// $countryDataFile = fopen("countryData.json", "r") or die("Unable to open file!");
// $countryData = json_decode(fgets($countryDataFile), true);
//  echo ($countryData['indonesia']);
// fclose($countryDataFile);

?>