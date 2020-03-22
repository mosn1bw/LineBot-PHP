<?php
$countryDataFile = fopen("covidData.json", "r") or die("Unable to open file!");
$countryData = json_decode(fgets($countryDataFile), true);
fclose($countryDataFile);

$datanow= $countryData[date("Y-m-d")];
echo $datanow['china']['Total Cases']
?>