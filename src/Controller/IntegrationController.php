<?php

namespace App\Controller;

use App\Application\Generator\GeneratorBundle\Generator;
use App\Application\Generator\GeneratorBundle\Helper\AwsHelper;
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



    #[Route('/aws', name: 'app_aws')]
    public function aws(Request $request): JsonResponse
    {

        $awsHelper = new AwsHelper();

        //$awsHelper->ec2->runInstances();

        $awsHelper->ec2->describeInstances();





        return $this->json([
            'ok'
        ]);
    }



    public function convertJsonProject($json)
    {
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));
        return $serializer->decode($json, 'json');
    }



    public function sendSNS()
    {

        $client = new \Aws\Sns\SnsClient([
            'profile' => 'default',
            'region' => 'us-east-1',
            'version' => '2010-03-31'
        ]);

        $message = 'This message is sent from a Amazon SNS code sample. PHP';
        $topic = 'arn:aws:sns:us-east-1:538747456615:notifyGenerator';

        try {
            $result = $client->publish([
                'Message' => $message,
                'TopicArn' => $topic,
            ]);
            var_dump($result);
        } catch (\Aws\Exception\AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
        }

    }




    private $queueUrl = "https://sqs.us-east-1.amazonaws.com/538747456615/gds-generate-code";


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

                dd($message);

                //return $this->json($message);

                /** Remove a menssagem atual da fila **/
                /*$client->deleteMessage([
                    'QueueUrl' => $this->queueUrl,
                    'ReceiptHandle' => $result->get('Messages')[0]['ReceiptHandle']
                ]);*/

            }else{
                return $this->json('Sem Mensagens na fila');
            }

        }catch ( \Aws\Exception\AwsException $e ){
            return $this->json('Sem Mensagens na fila');

        }

        return $this->json('Sem Mensagens na fila');
    }

}
