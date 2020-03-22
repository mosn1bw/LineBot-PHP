<?php
$dom = new DOMDocument;
function disguise_curl($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_AUTOREFERER, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $html= curl_exec($curl);
    if($html=== false)
    {
        if($errno = curl_errno($curl)){
            $error_message = curl_strerror($errno);
            $html= "cURL error ({$errno}): {$error_message}\n";
        }
    }
    curl_close($curl);
    return $html;
}

function scrape_between($data, $start, $end){
    $data = stristr($data, $start);
    $data = substr($data, strlen($start));
    $stop = stripos($data, $end); 
    $data = substr($data, 0, $stop);
    return $data;
}

function getHTMLByID($id, $html) {
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->validateOnParse = true;
    $dom->loadHTML($html);
    $node = $dom->getElementById($id);
    if($node) {
        return $dom->saveHTML($node);
    }
    return FALSE;
}

function getHTMLByClass($class, $html, $bring_tag=false){
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->validateOnParse = true;
    $dom->loadHTML($html);
    $class_arr= array();
    $xpath= new DOMXPath($dom);
    $results = $xpath->query("//*[contains(@class, '$class')]");
    if($results->length > 0){
        foreach($results as $tag)
        {
            if($bring_tag===true)
            array_push($class_arr, $tag);
            else
            array_push($class_arr, $dom->saveHTML($tag));
        }
    }    
    return $class_arr;
}

function get_domattr($html, $tag, $attr)
{
    $attr_vals= array();
    if(!empty($html))
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->validateOnParse = true;
        $dom->loadHTML($html);
        foreach($dom->getElementsByTagName($tag) as $img)
        array_push($attr_vals, $img->getAttribute($attr));
    }
    return $attr_vals;
}

function getHTMLByTag($tag, $html) {
    $attr_vals= array();
    if(!empty($html))
    {
        global $dom;
        libxml_use_internal_errors(true);
        $dom->validateOnParse = true;
        $dom->loadHTML($html);
        
        foreach($dom->getElementsByTagName($tag) as $taghtml)
        array_push($attr_vals, $dom->saveXML($taghtml));
    }
    return $attr_vals;
}
?>