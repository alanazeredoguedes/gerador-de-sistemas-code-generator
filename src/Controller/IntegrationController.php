<?php

namespace App\Controller;

use App\Application\Generator\GeneratorBundle\Generator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Twig\Environment;

class IntegrationController extends AbstractController
{

    #[Route('/', name: 'app_integration')]
    public function index(Request $request): JsonResponse
    {
        $jsonStructureDir = $this->getParameter('kernel.project_dir') . '/public/data.json';
        $jsonStructure = json_decode( file_get_contents($jsonStructureDir) );

        //$requestBody =  $request->getContent();
        //$requestBody = json_decode($requestBody);


        $kernelDirectory = $this->getParameter('kernel.project_dir');

        $generator = new Generator(
            projectName: $jsonStructure->name,
            projectDescription: $jsonStructure->description,
            class: $jsonStructure->class,
            relationships: $jsonStructure->relationships,
            kernelDirectory: $kernelDirectory,
        );

        $status = $generator->startGenerator();


        return $this->json([
            'message' => 'Welcome to your new controller!',
            'status' => $status,
            //'request' => $requestBody,
        ]);
    }















    public function convertJsonProject($json)
    {
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));
        return $serializer->decode($json, 'json');
    }


    /*

     private $queueUrl = "https://url_da_fila_sqs";

    public function readSQS(): JsonResponse
    {

        $client = new \Aws\Sqs\SqsClient([
            'profile' => 'default',
            'region' => 'us-east-1',
            'version' => '2012-11-05'
        ]);

        try{

            $result = $client->receiveMessage([
                'QueueUrl' => $this->queueUrl,
            ]);

            if( !empty($result->get('Messages')) ){


                $message = $result->get('Messages')[0]['Body'];

                //return $this->json($message);

                /** Remove a menssagem atual da fila *
                $client->deleteMessage([
                    'QueueUrl' => $this->queueUrl,
                    'ReceiptHandle' => $result->get('Messages')[0]['ReceiptHandle']
                ]);

            }else{
                return $this->json('Sem Mensagens na fila');
            }

        }catch ( \Aws\Exception\AwsException $e ){
            return $this->json('Sem Mensagens na fila');

        }

        return $this->json('Sem Mensagens na fila');
    }*/
}
