<?php

namespace App\Util;

use Nexmo\Client as VonageClient;
use Nexmo\Client\Credentials\Keypair;
use Nexmo\Call\Call as VonageCall;

class VonageUtil
{
    protected $client;

    public function __construct()
    {
        $keypair = new Keypair(
            file_get_contents($_ENV['VONAGE_APPLICATION_PRIVATE_KEY_PATH']),
            $_ENV['VONAGE_APPLICATION_ID']
        );

        $this->client = new VonageClient($keypair);
    }

    public function sendPushNotification()
    {

    }

    public function sendSms($to, $from, $text)
    {
        $message = $this->client->message()->send([
            'to' => $to,
            'from' => $from,
            'text' => $text
        ]);
    }

    public function makePhoneCall($to, $from, $text)
    {
        $ncco = [
            [
              'action' => 'talk',
              'voiceName' => 'Joey',
              'text' => $text
            ]
        ];

        $call = new VonageCall();
        $call->setTo($to)
            ->setFrom($from)
            ->setNcco($ncco);

        $this->client->calls()->create($call);
    }
}