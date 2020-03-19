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
            $result = $bot->replyText($event['replyToken'],'lol');
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
