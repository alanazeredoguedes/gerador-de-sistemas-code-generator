<?php

namespace App\Controller;

use App\Application\Generator\GeneratorBundle\AwsHelper\AwsHelper;
use App\Application\Generator\GeneratorBundle\Generator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class IntegrationController extends AbstractController
{

    #[Route('/', name: 'app_integration')]
    public function index(Request $request): JsonResponse
    {
        //$jsonStructureDir = $this->getParameter('kernel.project_dir') . '/public/data.json';
        //$jsonStructure = json_decode( file_get_contents($jsonStructureDir) );


        //$requestBody =  $request->getContent();
        //$requestBody = json_decode($requestBody);

        $awsHelper = new AwsHelper();

        $message = $awsHelper->sqs->getMessageCodeGenetate(false);
        if(!$message['status'])
            return $this->json(['status' => false, 'message' => 'Sem dados para processar!' ]);

        $project = json_decode($message['message']);

        $kernelDirectory = $this->getParameter('kernel.project_dir');

        $generator = new Generator(
            projectData: $project,
            kernelDirectory: $kernelDirectory,
        );

        $status = $generator->startGenerator();

        return $this->json([
            'status' => $status
        ]);
    }

    public function convertJsonProject($json)
    {
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));
        return $serializer->decode($json, 'json');
    }




}
