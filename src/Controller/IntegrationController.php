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

    #[Route('/generate', name: 'app_integration')]
    public function index(Request $request): JsonResponse
    {

        $awsHelper = new AwsHelper();

        $message = $awsHelper->sqs->getMessageCodeGenetate(true);
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


    #[Route('/', name: 'app_home')]
    public function home(Request $request): JsonResponse
    {
        $this->json('home');
    }


    public function convertJsonProject($json)
    {
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));
        return $serializer->decode($json, 'json');
    }




}
