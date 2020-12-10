<?php 

    require __DIR__ . '/../vendor/autoload.php';

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Factory\AppFactory;

    use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\StickerMessage;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
    use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
    use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
    use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
    use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
    use LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
    use LINE\LINEBot\SignatureValidator as SignatureValidator;

    $pass_signature = true;

    $channel_access_token = "fDjwD7jbcCj/FOBS3Ff2nkLBKcmoUX23LceGllYMRaJeP8L0WVjYvAp0n6hwTfj2ODYGIoHE2iC0nP/4bBibC0WsA2rpUtmGOcYYLcMSqL4fKQt1wjpxNlFpXwRqCNhLfnylId+1jb+XJgHIiHE8WQdB04t89/1O/w1cDnyilFU=";
    $channel_secret = "cd6704f32bb1f9ae16d3ac3bda61189f";

    $httpClient = new CurlHTTPClient($channel_access_token);
    $bot = new LINEBot($httpClient, ["channelSecret" => $channel_secret]);

    $app = AppFactory::create();
    $app->setBasePath('/public');

    $app->get('/', function(Request $request, Response $response, $args) {
        $response->getBody()->write('Selamar Datang di NAZIBOT');
        return $response;
    });

    $app->post('/webhook', function(Request $request, Response $response) use ($channel_secret, $bot, $pass_signature, $httpClient) {
        $body = $request->getBody();
        $signature = $request->getHeaderLine('HTTP_X_LINE_SIGNATURE');

        file_put_contents('php://stderr', 'Body: ' . $body);

        if($pass_signature === false) {
            if(empty($signature)) {
                return $response->withStatus(400, 'Signatur Not Set');
            };

            if(!SignatureValidator::validateSignature($body, $channel_secret, $signature)) {
                return $response->withStatus(400, 'Invalid Signature');
            }
        }
        
        // reply message
        $data = json_decode($body, true);

        if(is_array($data['events'])){
            foreach($data['events'] as $event) {
                if($event['type'] === 'message') {
                    if($event['message']['type'] === 'text') {
                        $textMessageBuilder = new TextMessageBuilder('Aku tahu, kamu pasti mau ngomong ? '. $event['message']['text']);
                        $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                        $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                        return $response
                            ->withHeader('Content-Type', 'application/json')
                            ->withStatus($result->getHTTPStatus());
                    }
                }
            }
        }
    });

    $app->get('/pushmessage', function(Request $request, Response $response) use ($bot) {
        $userId = "Ud6dbd897bda0efc122d39fd1aec64f7f";

        $textMessageBuilder = new TextMessageBuilder('hallo');
        $result = $bot->pushMessage($userId, $textMessageBuilder);

        $response->getBody()->write("Pesan berhsasil dikirim");
        return $response
            // ->withHeader('Content-Type', 'application/json')
            ->withStatus($result->getHTTPStatus());
    });

    $app->run();

?>