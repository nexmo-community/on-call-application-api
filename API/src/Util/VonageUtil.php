<?php

namespace App\Util;

use Vonage\Client;
use Vonage\SMS\Message\SMS;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\NCCO\NCCO;
use Vonage\Voice\NCCO\Action\Talk;
use Vonage\Voice\OutboundCall;

class VonageUtil
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function sendSms(string $to, string $from, string $text): bool
    {
        $response = $this->client->sms()->send(
            new SMS($to, $from, $text)
        );

        $message = $response->current();

        if ($message->getStatus() == 0) {
            return true;
        }

        return false;
    }

    public function makePhoneCall(string $to, string $from, string $text)
    {
        $outboundCall = new OutboundCall(
            new Phone($to),
            new Phone($from)
        );

        $ncco = new NCCO();
        $ncco->addAction(new Talk($text));
        $outboundCall->setNCCO($ncco);

        $this->client->voice()->createOutboundCall($outboundCall);
    }
}