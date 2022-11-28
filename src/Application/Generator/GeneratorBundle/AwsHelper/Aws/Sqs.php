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
            'region' => 'sa-east-1',
            'version' => '2012-11-05',
            'credentials' => $credentials,
        ]);
    }

    public function getMessageGdsGerarSistema(bool $deleteMessage = true): object
    {
        $queueUrl = "https://sqs.sa-east-1.amazonaws.com/538747456615/gds-gerar-sistema";

        return $this->getMessage(queueUrl: $queueUrl, deleteMessage: $deleteMessage);
    }

    public function getMessageGdsSistemaGeradoRepositorio(bool $deleteMessage = true): object
    {
        $queueUrl = "https://sqs.sa-east-1.amazonaws.com/538747456615/gds-sistema-gerado-repositorio";

        return $this->getMessage(queueUrl: $queueUrl, deleteMessage: $deleteMessage);
    }

    public function getMessageGdsSistemaGeradoServidor(bool $deleteMessage = true): object
    {
        $queueUrl = "https://sqs.sa-east-1.amazonaws.com/538747456615/gds-sistema-gerado-servidor";

        return $this->getMessage(queueUrl: $queueUrl, deleteMessage: $deleteMessage);
    }

    public function getMessage(string $queueUrl, bool $deleteMessage = true): object
    {

        try{
            $result = $this->client->receiveMessage([
                'QueueUrl' => $queueUrl,
            ]);

            if( empty( $result->get('Messages') ) )
                return (object) [ 'status' => false, 'message' => 'Sem mensagens'];

            $message = json_decode( $result->get('Messages')[0]['Body'] );

            if($deleteMessage)
                $this->client->deleteMessage([
                    'QueueUrl' => $queueUrl,
                    'ReceiptHandle' => $result->get('Messages')[0]['ReceiptHandle']
                ]);

            return (object) [ 'status' => true, 'message' => $message];

        }catch (AwsException $exception){
            return (object) [ 'status' => false, 'message' => 'Erro ao consultar mensagens'];
        }

    }



}