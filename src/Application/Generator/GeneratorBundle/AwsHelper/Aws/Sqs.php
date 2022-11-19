<?php

namespace App\Application\Generator\GeneratorBundle\AwsHelper\Aws;

use Aws\Sqs\SqsClient;
use Aws\Result as AwsResult;
use \Aws\Exception\AwsException;;

class Sqs
{
    protected SqsClient $client;

    public function __construct(
        protected array $credentials,
    ){
        $this->client = new SqsClient([
            'region' => 'us-east-1',
            'version' => '2012-11-05',
            'credentials' => $credentials,
        ]);
    }



    public function getMessageCodeGenetate(bool $deleteMessage = true): array
    {
        $queueUrl = "https://sqs.us-east-1.amazonaws.com/538747456615/gds-generate-code";

        return $this->getMessage(queueUrl: $queueUrl, deleteMessage: $deleteMessage);
    }



    public function getMessage(string $queueUrl, bool $deleteMessage = true): array
    {

        try{
            $result = $this->client->receiveMessage([
                'QueueUrl' => $queueUrl,
            ]);

            if( empty( $result->get('Messages') ) )
                return [ 'status' => false, 'message' => 'Sem mensagens'];;

            $message = $result->get('Messages')[0]['Body'];

            if($deleteMessage)
                $response = $this->client->deleteMessage([
                    'QueueUrl' => $queueUrl,
                    'ReceiptHandle' => $result->get('Messages')[0]['ReceiptHandle']
                ]);

            return [
                'status' => true,
                'message' => $message
            ];

        }catch (AwsException $exception){
            return [
                'status' => false,
                'message' => 'Erro ao consultar mensagens'
            ];
        }

    }

}