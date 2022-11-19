<?php

namespace App\Application\Generator\GeneratorBundle\AwsHelper\Aws;

use Aws\Sns\SnsClient;
use Aws\Result as AwsResult;


class Sns
{
    protected SnsClient $client;

    public function __construct(
        protected array $credentials,
    ){
        $this->client = new SnsClient([
            'region' => 'us-east-1',
            'version' => '2010-03-31',
            'credentials' => $credentials,
        ]);
    }


    public function sentToNotifyGenerator($message): bool
    {
        $topic = 'arn:aws:sns:us-east-1:538747456615:notifyGenerator';
        $response = $this->sendMenssage(message: $message, topic: $topic);
        $statusCode = $response['@metadata']['statusCode'];

        return $statusCode === 200;
    }



    public function sendMenssage($message, $topic): AwsResult
    {
        return $this->client->publish([
            'Message' => $message,
            'TopicArn' => $topic,
        ]);
    }


}