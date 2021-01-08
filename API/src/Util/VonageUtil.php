<?php

namespace App\Util;

use Vonage\Call\Call as VonageCall;
use Vonage\Client as VonageClient;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Keypair;
use Vonage\SMS\Message\SMS;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\NCCO\NCCO;
use Vonage\Voice\NCCO\Action\Talk;
use Vonage\Voice\OutboundCall;
use Vonage\Voice\Webhook;

class VonageUtil
{
    /** @var VonageClient */
    protected $smsClient;

    /** @var VonageClient */
    protected $voiceClient;

    public function __construct()
    {
        $basic = new Basic(
            $_ENV['VONAGE_API_KEY'],
            $_ENV['VONAGE_API_SECRET']
        );

        $this->smsClient = new VonageClient($basic);

        $keypair = new Keypair(
            file_get_contents($_ENV['VONAGE_APPLICATION_PRIVATE_KEY_PATH']),
            $_ENV['VONAGE_APPLICATION_ID']
        );

        $this->voiceClient = new VonageClient($keypair);
    }

    public function sendSms($to, $from, $text): bool
    {
        $response = $this->smsClient->sms()->send(
            new SMS($to, $from, $text)
        );

        $message = $response->current();

        if ($message->getStatus() == 0) {
            return true;
        }

        return false;
    }

    public function makePhoneCall($to, $from, $text)
    {
        $outboundCall = new OutboundCall(
            new Phone($to),
            new Phone($from)
        );

        $ncco = new NCCO();
        $ncco->addAction(new Talk($text));
        $outboundCall->setNCCO($ncco);

        $this->voiceClient->voice()->createOutboundCall($outboundCall);
    }
}