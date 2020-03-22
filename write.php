<?php
$countryDataFile = fopen("covidData.json", "r") or die("Unable to open file!");
$countryData = json_decode(fgets($countryDataFile), true);
fclose($countryDataFile);
if (!isset($countryData[date("Y-m-d")])){
    require 'extend.php';
    $url= "https://www.worldometers.info/coronavirus/";
    $page_html= disguise_curl($url);
    $result_html= getHTMLByClass('table table-bordered table-hover main_table_countries', $page_html);
    $datatr= getHTMLByTag('tr', $result_html[0]);
    $alldata =[];
    for ($i=1; $i <count($datatr); $i++) {
        $raw= getHTMLByTag('td', $datatr[$i]);
        $dataCountry = [];
        $dataCountry['Total Cases'] = trim(strip_tags($raw[1]));
        $dataCountry['New Cases'] = trim(strip_tags($raw[2]));
        $dataCountry['Total Deaths'] = trim(strip_tags($raw[3]));
        $dataCountry['New Deaths'] = trim(strip_tags($raw[4]));
        $dataCountry['Total Recovered'] = trim(strip_tags($raw[5]));
        $dataCountry['Active Cases'] = trim(strip_tags($raw[6]));
        $alldata[str_replace(':','',strtolower(strip_tags($raw[0])))] =$dataCountry;
    }
    $countryData[date("Y-m-d")]=$alldata;
    $myfile = fopen("covidData.json", "w") or die("Unable to open file!");
    fwrite($myfile, json_encode($countryData));
    fclose($myfile);
}
?>