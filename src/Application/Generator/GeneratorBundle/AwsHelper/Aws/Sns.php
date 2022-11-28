<?php

namespace App\Application\Generator\GeneratorBundle\AwsHelper\Aws;

use Aws\Sns\SnsClient;
use Aws\Result as AwsResult;
use Symfony\Component\HttpFoundation\Request;


class Sns
{
    protected SnsClient $client;

    public function __construct(
        protected array $credentials,
    ){
        $this->client = new SnsClient([
            'region' => 'sa-east-1',
            'version' => '2010-03-31',
            'credentials' => $credentials,
        ]);
    }

    public function confirmSubscribe(Request $request): void
    {
        $content =  json_decode( $request->getContent() );

        if(!isset($content) || !isset($content->Type))
            return;

        if($content->Type !== "SubscriptionConfirmation")
            return;

        $this->client->confirmSubscription([
            'Token' => $content->Token,
            'TopicArn' => $content->TopicArn,
        ]);
    }

    public function sendMessageGdsGerarSistema($message): bool
    {
        $topic = 'arn:aws:sns:sa-east-1:538747456615:gds-gerar-sistema';
        $response = $this->sendMenssage(message: $message, topic: $topic);
        $statusCode = $response['@metadata']['statusCode'];

        return $statusCode === 200;
    }

    public function sendMessageGdsSistemaGeradoRepositorio($message): bool
    {
        $topic = 'arn:aws:sns:sa-east-1:538747456615:gds-sistema-gerado-repositorio';
        $response = $this->sendMenssage(message: $message, topic: $topic);
        $statusCode = $response['@metadata']['statusCode'];

        return $statusCode === 200;
    }

    public function sendMessageGdsSistemaGeradoServidor($message): bool
    {
        $topic = 'arn:aws:sns:sa-east-1:538747456615:gds-sistema-gerado-servidor';
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