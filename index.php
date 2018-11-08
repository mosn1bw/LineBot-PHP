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

// set false for production
$pass_signature = true;

// set LINE channel_access_token and channel_secret
$channel_access_token = getenv("catheroku");
$channel_secret = getenv("csheroku");

// inisiasi objek bot
//include 'codenya.php';
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
$configs =  [
  'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);
// buat route untuk url homepage
$app->get('/', function($req, $res)
{
  echo "Welcome at Slim Framework";
});

// buat route untuk webhook
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature)
{
  // get request body and line signature header
  $body        = file_get_contents('php://input');
  $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';

  // log body and signature
  file_put_contents('php://stderr', 'Body: '.$body);

  if($pass_signature === false)
  {
    // is LINE_SIGNATURE exists in request header?
    if(empty($signature)){
      return $response->withStatus(400, 'Signature not set');
    }

    // is this request comes from LINE?
    if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
      return $response->withStatus(400, 'Invalid signature');
    }
  }
  $data = json_decode($body, true);
  if(is_array($data['events'])){
    foreach ($data['events'] as $event)
    {
      if ($event['type'] == 'message')
      {
        $userId     = $event['source']['userId'];
        $groupId     = $event['source']['groupId'];
        $getprofile = $bot->getProfile($userId);
        $profile    = $getprofile->getJSONDecodedBody();
        $greetings  = new TextMessageBuilder("Halo, ".$profile['displayName']);
        $a = (explode('-',$event['message']['text']));
        if($a[0] == "/help"){
          $phpnya="<?php\necho \"tulis aja disini kode phpnya\";";
          $carouselTemplateBuilder = new CarouselTemplateBuilder([
            new CarouselColumnTemplateBuilder("Menu", "Menu FoneBot","https://farkhan.000webhostapp.com/b1.jpg",[
              new MessageTemplateActionBuilder('-','/'),
              new MessageTemplateActionBuilder('-','/'),
              new MessageTemplateActionBuilder('Jadwal Sholat','/jadwal'),
            ]),
            new CarouselColumnTemplateBuilder("Menu", "Menu FoneBot ","https://farkhan.000webhostapp.com/b1.jpg",[
              new MessageTemplateActionBuilder('PHP',$phpnya),
              new MessageTemplateActionBuilder('UserID','/userid'),
              new MessageTemplateActionBuilder('GroupID','/groupid'),
            ]),
            new CarouselColumnTemplateBuilder("Developer", "Farkhan Azmi Filkom UB","https://farkhan.000webhostapp.com/b2.jpg",[
              new UriTemplateActionBuilder('Line',"http://line.me/ti/p/~foneazm"),
              new UriTemplateActionBuilder('Github',"http://github.com/foneazmi/"),
              new UriTemplateActionBuilder('LinkedIn',"https://linkedin.com/in/farkhanazmi/"),
            ]),
          ]);
          $templateMessage = new TemplateMessageBuilder('Help FoneBot',$carouselTemplateBuilder);
          $result = $bot->replyMessage($event['replyToken'], $templateMessage);
        }
        else if ($a[0]=="/userid") {
          $result = $bot->replyText($event['replyToken'], $userId);
        }
        else if ($a[0]=="/groupid") {
          $result = $bot->replyText($event['replyToken'], $event['source']['groupId']);
        }
        else if ($a[0]=="/jadwal") {
          $kota=(isset($a[1])) ? $a[1] : "malang";
          $stored = file_get_contents("http://api.aladhan.com/v1/timingsByCity?city=$kota&country=indonesia&method=11");
          $datanya = json_decode($stored, TRUE);
          $jadwalsholat=$datanya['data']['timings'];
          $hijri=$datanya['data']['date'];
          $hasilnya="Jadwal Sholat \nWilayah ".$kota.", ".$hijri['readable']
          ."\n================"
          ."\nImsak : ".$jadwalsholat['Imsak']
          ."\nSubuh : ".$jadwalsholat['Fajr']
          ."\nDhuhur : ".$jadwalsholat['Dhuhr']
          ."\nAshar : ".$jadwalsholat['Asr']
          ."\nMaghrib : ".$jadwalsholat['Maghrib']
          ."\nIsha' : ".$jadwalsholat['Isha']
          ."\n================"
          ."\n".$hijri['hijri']['day']." ".$hijri['hijri']['month']['en']." ".$hijri['hijri']['year'];
          $hasilnya=(isset($a[1])) ? $hasilnya : $hasilnya."\n================\nuntuk wilayah lain gunakan\n/jadwal-namawilayah";
          $result = $bot->replyText($event['replyToken'],$hasilnya);
        }
        else if (substr($event['message']['text'],0,5)=='<?php') {
          $data = array(
            'php' => $event['message']['text']
          );
          $babi=file_get_contents('http://farkhan.000webhostapp.com/nutshell/babi.php?'.http_build_query($data));
          $result = $bot->replyText($event['replyToken'], $babi);
        }
        else if($a[0]=="/IPK"){
          if (isset($a[1])) {
            include 'ScrapingSIAM/potong.php';
            $gg = new DataSiam();
            $hasil=$gg->get_data($a[1]);
          }else{
            $hasil="untuk menggunakan SiamBot\n/IPK-nim";
          }
          $result = $bot->replyText($event['replyToken'],$hasil);
        }
        // if(
        //   $event['source']['type'] == 'group' or
        //   $event['source']['type'] == 'room'
        // ){
         
        // }
      }
    }
  }
});
$app->run();
