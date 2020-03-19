<?php
require __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use \LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\SeparatorComponentBuilder;


use \LINE\LINEBot\Constant\Flex\ComponentLayout;
use \LINE\LINEBot\Constant\Flex\ComponentFontWeight;
use \LINE\LINEBot\Constant\Flex\ComponentSpacing;
use \LINE\LINEBot\Constant\Flex\ComponentAlign;
use \LINE\LINEBot\Constant\Flex\ComponentButtonStyle;
use \LINE\LINEBot\Constant\Flex\ComponentFontSize;
use \LINE\LINEBot\Constant\Flex\ComponentMargin;

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
        $a = (explode('-',$event['message']['text']));
        switch ($a[0]) {
          case '/userid':
            $userId     = $event['source']['userId'];
            $result = $bot->replyText($event['replyToken'], $userId);
            break;

          case '/groupid':
            $groupId     = $event['source']['groupId'];
            $result = $bot->replyText($event['replyToken'], $groupId);
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
                $response = FlexMessageBuilder::builder()
                ->setAltText('test')
                ->setContents(
                    BubbleContainerBuilder::builder()
                    ->setBody(
                        BoxComponentBuilder::builder()
                        ->setLayout(ComponentLayout::VERTICAL)
                        ->setSpacing(ComponentSpacing::SM)
                        ->setContents([
                          TextComponentBuilder::builder()
                              ->setText('Covid-19')
                              ->setSize(ComponentFontSize::SM)
                              ->setWeight(ComponentFontWeight::BOLD)
                              ->setColor('#1DB446'),
                          TextComponentBuilder::builder()
                              ->setText($rawResponse->Country_Region)
                              ->setWeight(ComponentFontWeight::BOLD)
                              ->setSize(ComponentFontSize::XXL),
                          SeparatorComponentBuilder::builder()
                            ->setMargin(ComponentMargin::XXL),

                          BoxComponentBuilder::builder()
                            ->setLayout(ComponentLayout::HORIZONTAL)
                            ->setContents([
                              TextComponentBuilder::builder()
                                ->setText("Jumlah Kasus")
                                ->setColor('#555555')
                                ->setSize(ComponentFontSize::SM)
                            ]),
                      ])
                    )
                );
            }
            $result = $bot->replyMessage($event['replyToken'],$response);
            return $result;
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
