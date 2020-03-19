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
use \LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;



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

          case '/covidupdatecoredata':
              $data =  file_get_contents('https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/ncov_cases/FeatureServer/1/query?f=json&where=(Confirmed%3E%200)%20OR%20(Deaths%3E0)%20OR%20(Recovered%3E0)&returnGeometry=false&spatialRef=esriSpatialRelIntersects&outFields=*&orderByFields=Country_Region%20asc,Province_State%20asc&resultOffset=0&resultRecordCount=250&cacheHint=false');
              $data = json_decode($data);
              $countryData=[];
              foreach ($data->features as $value) {
                if ($value->attributes->Province_State==null) {
                  $countryData[strtolower($value->attributes->Country_Region)]['Lat']=number_format($value->attributes->Lat, 4, '.', '');
                  $countryData[strtolower($value->attributes->Country_Region)]['Long_']=number_format($value->attributes->Long_, 4, '.', '');
                }else{
                  $countryData[strtolower($value->attributes->Country_Region.'@'.$value->attributes->Province_State)]['Lat']=number_format($value->attributes->Lat, 4, '.', '');
                  $countryData[strtolower($value->attributes->Country_Region.'@'.$value->attributes->Province_State)]['Long_']=number_format($value->attributes->Long_, 4, '.', '');
                }
              }
              $myfile = fopen("countryData.json", "w") or die("Unable to open file!");
              fwrite($myfile, json_encode($countryData));
            break;

          case '/covid':
            $countryDataFile = fopen("countryData.json", "r") or die("Unable to open file!");
            $countryData = json_decode(fgets($countryDataFile), true);
            fclose($countryDataFile);
            $negara = isset($a[1]) ? $a[1] : 'indonesia';
            $parameter = $countryData[strtolower(trim($negara))];
            $data = file_get_contents('https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/ncov_cases/FeatureServer/1/query?f=json&where=(Lat%3D'.$parameter['Lat'].')%20OR%20(Long_%3D'.$parameter['Long_'].')&returnGeometry=false&spatialRef=esriSpatialRelIntersects&outFields=*&orderByFields=Country_Region%20asc,Province_State%20asc&resultOffset=0&resultRecordCount=250&cacheHint=false');
            $data= json_decode($data);
            $rawResponse = $data->features[0]->attributes;
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

                          TextComponentBuilder::builder()
                              ->setText($rawResponse->Confirmed." Kasus")
                              ->setWeight(ComponentFontWeight::BOLD)
                              ->setSize(ComponentFontSize::XL),
                          SeparatorComponentBuilder::builder()
                            ->setMargin(ComponentMargin::XXL),

                          BoxComponentBuilder::builder()
                          ->setLayout(ComponentLayout::HORIZONTAL)
                          ->setContents([
                            TextComponentBuilder::builder()
                              ->setText("positif")
                              ->setColor('#555555')
                              ->setSize(ComponentFontSize::SM),
                            TextComponentBuilder::builder()
                              ->setText($rawResponse->Active." ")
                              ->setColor('#111111')
                              ->setAlign('end')
                              ->setWeight(ComponentFontWeight::BOLD)
                              ->setSize(ComponentFontSize::SM),
                          ]),

                          BoxComponentBuilder::builder()
                          ->setLayout(ComponentLayout::HORIZONTAL)
                          ->setContents([
                            TextComponentBuilder::builder()
                              ->setText("Sembuh")
                              ->setColor('#555555')
                              ->setSize(ComponentFontSize::SM),
                            TextComponentBuilder::builder()
                              ->setText($rawResponse->Recovered." ")
                              ->setColor('#111111')
                              ->setWeight(ComponentFontWeight::BOLD)
                              ->setAlign('end')
                              ->setSize(ComponentFontSize::SM),
                          ]),
                          BoxComponentBuilder::builder()
                            ->setLayout(ComponentLayout::HORIZONTAL)
                            ->setContents([
                              TextComponentBuilder::builder()
                                ->setText("Meninggal")
                                ->setColor('#555555')
                                ->setSize(ComponentFontSize::SM),
                              TextComponentBuilder::builder()
                                ->setText($rawResponse->Deaths." ")
                                ->setColor('#111111')
                                ->setAlign('end')
                                ->setWeight(ComponentFontWeight::BOLD)
                                ->setSize(ComponentFontSize::SM),
                            ]),
                          SeparatorComponentBuilder::builder()
                            ->setMargin(ComponentMargin::XXL),
                          BoxComponentBuilder::builder()
                            ->setLayout(ComponentLayout::HORIZONTAL)
                            ->setContents([
                              TextComponentBuilder::builder()
                                ->setText("Last Update")
                                ->setColor('#aaaaaa')
                                ->setSize(ComponentFontSize::XS),
                              TextComponentBuilder::builder()
                                ->setText(date("Y-m-d H:i:s", substr( $rawResponse->Last_Update, 0, 10))." ")
                                ->setColor('#aaaaaa')
                                ->setAlign('end')
                                ->setSize(ComponentFontSize::SM),
                            ]),
                          
                          BoxComponentBuilder::builder()
                          ->setLayout(ComponentLayout::HORIZONTAL)
                          ->setContents([
                            ButtonComponentBuilder::builder()
                              ->setStyle(ComponentButtonStyle::SECONDARY)
                              ->setAction(
                                new MessageTemplateActionBuilder('Core', '/covidupdatecoredata')
                              ),
                            
                            SeparatorComponentBuilder::builder()
                              ->setMargin(ComponentMargin::SM),
                            ButtonComponentBuilder::builder()
                              ->setStyle(ComponentButtonStyle::PRIMARY)
                              ->setAction(
                                new MessageTemplateActionBuilder('Update', '/covid-'.$negara)
                              )
                          ]),
                      ])
                    )
                );
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
