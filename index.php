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

          case '/covid':
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
            }else{
              $negara = isset($a[1]) ? $a[1] : 'indonesia';
              $datanow= $countryData[date("Y-m-d")][$negara];
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
                              ->setText(strtoupper($negara)."")
                              ->setWeight(ComponentFontWeight::BOLD)
                              ->setSize(ComponentFontSize::XXL),
                          SeparatorComponentBuilder::builder()
                            ->setMargin(ComponentMargin::XXL),
                          TextComponentBuilder::builder()
                              ->setText($datanow['Total Cases']." Kasus")
                              ->setWeight(ComponentFontWeight::BOLD)
                              ->setSize(ComponentFontSize::XL),
                          BoxComponentBuilder::builder()
                          ->setLayout(ComponentLayout::HORIZONTAL)
                          ->setContents([
                            TextComponentBuilder::builder()
                              ->setText("New Cases")
                              ->setColor('#555555')
                              ->setSize(ComponentFontSize::SM),
                            TextComponentBuilder::builder()
                              ->setText($datanow['New Cases']."")
                              ->setColor('#111111')
                              ->setAlign('end')
                              ->setWeight(ComponentFontWeight::BOLD)
                              ->setSize(ComponentFontSize::SM),
                          ]),
                          BoxComponentBuilder::builder()
                          ->setLayout(ComponentLayout::HORIZONTAL)
                          ->setContents([
                            TextComponentBuilder::builder()
                              ->setText("Active Cases")
                              ->setColor('#555555')
                              ->setSize(ComponentFontSize::SM),
                            TextComponentBuilder::builder()
                              ->setText($datanow['Active Cases']."")
                              ->setColor('#111111')
                              ->setAlign('end')
                              ->setWeight(ComponentFontWeight::BOLD)
                              ->setSize(ComponentFontSize::SM),
                          ]),
                          BoxComponentBuilder::builder()
                          ->setLayout(ComponentLayout::HORIZONTAL)
                          ->setContents([
                            TextComponentBuilder::builder()
                              ->setText("Total Recovered")
                              ->setColor('#555555')
                              ->setSize(ComponentFontSize::SM),
                            TextComponentBuilder::builder()
                              ->setText($datanow['Total Recovered']."")
                              ->setColor('#111111')
                              ->setWeight(ComponentFontWeight::BOLD)
                              ->setAlign('end')
                              ->setSize(ComponentFontSize::SM),
                          ]),
                          BoxComponentBuilder::builder()
                            ->setLayout(ComponentLayout::HORIZONTAL)
                            ->setContents([
                              TextComponentBuilder::builder()
                                ->setText("Total Deaths")
                                ->setColor('#555555')
                                ->setSize(ComponentFontSize::SM),
                              TextComponentBuilder::builder()
                                ->setText($datanow['Total Deaths']."")
                                ->setColor('#111111')
                                ->setAlign('end')
                                ->setWeight(ComponentFontWeight::BOLD)
                                ->setSize(ComponentFontSize::SM),
                            ]),
                          BoxComponentBuilder::builder()
                          ->setLayout(ComponentLayout::HORIZONTAL)
                          ->setContents([
                            TextComponentBuilder::builder()
                              ->setText("New Deaths")
                              ->setColor('#555555')
                              ->setSize(ComponentFontSize::SM),
                            TextComponentBuilder::builder()
                              ->setText($datanow['New Deaths']."")
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
                                ->setText(date("Y-m-d")."")
                                ->setColor('#aaaaaa')
                                ->setAlign('end')
                                ->setSize(ComponentFontSize::SM),
                            ]),
                          BoxComponentBuilder::builder()
                          ->setLayout(ComponentLayout::HORIZONTAL)
                          ->setContents([
                            ButtonComponentBuilder::builder()
                              ->setStyle(ComponentButtonStyle::PRIMARY)
                              ->setAction(new MessageTemplateActionBuilder('Update', '/covid-'.$negara))
                          ]),
                      ])
                    ));
              $result = $bot->replyMessage($event['replyToken'],$response);
            }

            return $result;
            break;
          default:
            # code...
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
            break;
        }
      }
    }
  }
});
$app->run();
