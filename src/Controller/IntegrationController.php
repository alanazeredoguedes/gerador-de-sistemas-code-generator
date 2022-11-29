<?php

namespace App\Controller;

use App\Application\Generator\GeneratorBundle\AwsHelper\AwsHelper;
use App\Application\Generator\GeneratorBundle\Generator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class IntegrationController extends AbstractController
{
    protected AwsHelper $awsHelper;

    public function __construct(
        protected ContainerBagInterface $containerInterface,
    )
    {
        $this->awsHelper = new AwsHelper($this->containerInterface);
    }

    #[Route('/generate', name: 'app_integration')]
    public function index(Request $request): JsonResponse
    {
        //dd($this->getParameter('kernel.project_dir'));

        $message = $this->awsHelper->sqs->getMessageGdsGerarSistema(true);
        if(!$message->status)
            return $this->json(['status' => false, 'message' => 'Sem dados para processar!' ]);

        $generator = new Generator(
            projectData: $message->message,
            kernelDirectory: $this->getParameter('kernel.project_dir'),
            awsHelper: $this->awsHelper,
        );
        $status = $generator->startGenerator();

        return $this->json([
            'status' => $status
        ]);
    }


    #[Route('/', name: 'app_home')]
    public function home(Request $request): JsonResponse
    {
        return $this->json('home');
    }

    #[Route('/removeInstancesBase', name: 'remove_instance_base')]
    public function removeInstanceBase(Request $request): JsonResponse
    {
        $ec2 = $this->awsHelper->ec2;
        $instances = $ec2->getInstancesByTag("Group", "generate-instance");

        //$result = $ec2->verificationStatus(['i-04163805e044e4cc0','i-0806e8ed62926f85a' ]);

        foreach ( $instances as $instance ){

            /** Se instancia não estiver rodando continua */
            if($instance['State']['Name'] === "terminated") // terminated
                continue;

            /** Remove a instancia */
            $ec2->terminateInstance($instance['InstanceId']);
        }

        return $this->json('remove');
    }


    public function convertJsonProject($json)
    {
        $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));
        return $serializer->decode($json, 'json');
    }




}
