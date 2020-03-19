<?php
require __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;

$pass_signature = true;

$channel_access_token = getenv("catheroku");
$channel_secret = getenv("csheroku");

$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
$configs =  [
  'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);
$app->get('/', function($req, $res){
  echo "Welcome at Slim Framework";
});

$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature){
  $body        = file_get_contents('php://input');
  $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';
  file_put_contents('php://stderr', 'Body: '.$body);
  if($pass_signature === false){
    if(empty($signature)){
      return $response->withStatus(400, 'Signature not set');
    }
    if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
      return $response->withStatus(400, 'Invalid signature');
    }
  }
  $data = json_decode($body, true);
  if(is_array($data['events'])){
    foreach ($data['events'] as $event){
      if ($event['type'] == 'message'){
        $userId     = $event['source']['userId'];
        $groupId     = $event['source']['groupId'];
        $getprofile = $bot->getProfile($userId);
        $profile    = $getprofile->getJSONDecodedBody();
        $greetings  = new TextMessageBuilder("Halo, ".$profile['displayName']);
        $a = (explode('-',$event['message']['text']));
        switch ($a[0]) {
          case '/userid':
            $result = $bot->replyText($event['replyToken'], $userId);
            break;

          case '/groupid':
            $result = $bot->replyText($event['replyToken'], $event['source']['groupId']);
            break;

          case '/covid':
            $countryDataFile = fopen("countryData.json", "r") or die("Unable to open file!");
            $countryData = json_decode(fgets($countryDataFile), true);
            fclose($countryDataFile);
            $parameter = isset($a[1]) ? $countryData[$a[1]] : '101';
            $data = file_get_contents('https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/ncov_cases/FeatureServer/1/query?f=json&where=(OBJECTID%3D'.$parameter.')&returnGeometry=false&spatialRef=esriSpatialRelIntersects&outFields=*&orderByFields=Country_Region%20asc,Province_State%20asc&resultOffset=0&resultRecordCount=250&cacheHint=false');
            $data= json_decode($data);
            $response="";
            foreach ($data->features as $value) {
                $rawResponse = $value->attributes;
                // $response.="Negara : ".$rawResponse->Country_Region."\n".
                // "Jumlah Kasus : ".$rawResponse->Confirmed."\n".
                // "Total Terinfeksi : ".$rawResponse->Active."\n".
                // "Total Sembuh : ".$rawResponse->Recovered."\n".
                // "Total Meninggal : ".$rawResponse->Deaths;
            $response='
            {
              "type": "bubble",
              "body": {
                "type": "box",
                "layout": "vertical",
                "contents": [
                  {
                    "type": "text",
                    "text": "Covid-19",
                    "weight": "bold",
                    "color": "#1DB446",
                    "size": "sm"
                  },
                  {
                    "type": "text",
                    "text": "'.$rawResponse->Country_Region.'",
                    "weight": "bold",
                    "size": "xxl",
                    "margin": "md"
                  },
                  {
                    "type": "separator",
                    "margin": "xxl"
                  },
                  {
                    "type": "box",
                    "layout": "vertical",
                    "margin": "xxl",
                    "spacing": "sm",
                    "contents": [
                      {
                        "type": "box",
                        "layout": "horizontal",
                        "contents": [
                          {
                            "type": "text",
                            "text": "Jumlah Kasus",
                            "size": "sm",
                            "color": "#555555",
                            "flex": 0
                          },
                          {
                            "type": "text",
                            "text": "'.$rawResponse->Confirmed.'",
                            "size": "sm",
                            "color": "#111111",
                            "align": "end"
                          }
                        ]
                      },
                      {
                        "type": "box",
                        "layout": "horizontal",
                        "contents": [
                          {
                            "type": "text",
                            "text": "Total Terinfeksi",
                            "size": "sm",
                            "color": "#555555",
                            "flex": 0
                          },
                          {
                            "type": "text",
                            "text": "'.$rawResponse->Active.'",
                            "size": "sm",
                            "color": "#111111",
                            "align": "end"
                          }
                        ]
                      },
                      {
                        "type": "box",
                        "layout": "horizontal",
                        "contents": [
                          {
                            "type": "text",
                            "text": "Jumlah Sembuh",
                            "size": "sm",
                            "color": "#555555",
                            "flex": 0
                          },
                          {
                            "type": "text",
                            "text": "'.$rawResponse->Recovered.'",
                            "size": "sm",
                            "color": "#111111",
                            "align": "end"
                          }
                        ]
                      },
                      {
                        "type": "box",
                        "layout": "horizontal",
                        "contents": [
                          {
                            "type": "text",
                            "text": "Total Meninggal",
                            "size": "sm",
                            "color": "#555555",
                            "flex": 0
                          },
                          {
                            "type": "text",
                            "text": "'.$rawResponse->Deaths.'",
                            "size": "sm",
                            "color": "#111111",
                            "align": "end"
                          }
                        ]
                      }
                    ]
                  },
                  {
                    "type": "separator",
                    "margin": "xxl"
                  },
                  {
                    "type": "box",
                    "layout": "horizontal",
                    "margin": "md",
                    "contents": [
                      {
                        "type": "text",
                        "text": "Last Update",
                        "size": "xs",
                        "color": "#aaaaaa",
                        "flex": 0
                      },
                      {
                        "type": "text",
                        "text": "'.date("Y-m-d H:i:s", substr( $rawResponse->Last_Update, 0, 10)).'",
                        "color": "#aaaaaa",
                        "size": "xs",
                        "align": "end"
                      }
                    ]
                  }
                ]
              },
              "styles": {
                "footer": {
                  "separator": true
                }
              }
            }
            
            ';
            }
            
            $result = $bot->replyText($event['replyToken'],$response);
            break;
          
          default:
            # code...
            break;
        }
      }
    }
  }
});
$app->run();
