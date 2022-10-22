<?php

namespace App\Controller;

use App\Application\Generator\GeneratorBundle\Generator;
use App\Entity\GitHelper;
use App\Entity\StringHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Twig\Environment;

class IntegrationController extends AbstractController
{

    #[Route('/', name: 'app_integration')]
    public function index(): JsonResponse
    {
        $jsonStructureDir = $this->getParameter('kernel.project_dir') . '/public/data.json';
        $jsonStructure = json_decode( file_get_contents($jsonStructureDir) );

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
        ]);
    }



    private function generateStructure($structure)
    {
        //dd( $structure );


        /** Registra as informações do projeto [sonata_admin.yaml] */
        //$this->editSonataAdminFile($projectDirectory, $projectName, $projectDescription);

//        foreach ($structure->class as $class) {
//            //dd($class);
//
//            /** Gera o nome da Bundle */
//            $bundleName = trim( ucfirst( $class->className ) ) .'Bundle';
//
//            /** Criar o diretório da bundle */
//            $this->createBundleDir($projectDirectory, $packageName, $bundleName, $class->className );
//
//            /** Registra a bundle no arquivo do doctrine [doctrine.yaml] */
//            $this->registerDoctrine($projectDirectory,$packageName, $bundleName);
//
//            /** Registra o serviço da bundle no arquivo de serviços [services.yaml] */
//            $this->registerMenuAdmin();
//
//            /** Registra a bundle no arquivo de rotas [routes.yaml] */
//            $this->registerRoute();
//
//            /** Registra a bundle no arquivo de bundles [bundles.php] */
//            $this->registerBundle();
//
//
//
//
//            /** Gera o arquivo de registro da bundle */
//            $this->createApplicationFile();
//
//            /** Gera o arquivo de rota da bundle */
//            $this->createRouteFile();
//
//            /** Gera o arquivo da controladora FrontEnd */
//            $this->createFrontControllerFile();
//
//            /** Gera o arquivo da controladora Administrativa */
//            $this->createAdminControllerFile();
//
//            /** Gera o arquivo da controladora Api */
//            $this->createApiControllerFile();
//
//            /** Gera o arquivo de Repositório */
//            $this->createRepositoryFile();
//
//            /** Gera o arquivo Admin */
//            $this->createAdminFile();
//
//            /** Gera o arquivo da Entidade */
//            $this->createEntityFile();
//
//        }





    }


    /**
     * @param $projectDir = Diretorio do projeto
     * @param $name = nome do projeto
     * @param $description = descrição do projeto
     * @return void
     */
    private function editSonataAdminFile($projectDir, $name, $description): void
    {
        $sonataAdminFile = $projectDir . '/config/packages/sonata_admin.yaml';

        $edit = array("Project-Base", "Project-Description");
        $values = array($name, $description);

        $file = file_get_contents($sonataAdminFile);
        $newFile = str_replace( $edit, $values, $file);

        file_put_contents($sonataAdminFile, $newFile);
    }


    private function createBundleDir($projectDir, $packageName, $bundleName, $entity)
    {
        $bundleDirectory = $projectDir . '/src/Application/' . $packageName . '/' . $bundleName;

        $registerDirectory = [
            '/Admin/',
            '/Controller/',
            '/Entity/',
            '/Repository/',
            '/Form/',
            // Resources Directory
            '/Resources/config/routes/',
            '/Resources/public/css/',
            '/Resources/public/js/',
            '/Resources/public/fonts/',
            '/Resources/public/images/',
            // Views Directory
            //'/Resources/views/' . strtolower($entity),
            '/Resources/views/' . strtolower($entity) . '/macros/',
            '/Resources/views/' . strtolower($entity) . '/template/',
            '/Resources/views/' . strtolower($entity) . '/components/',
        ];

        foreach ($registerDirectory as $register){
            if ( !file_exists($bundleDirectory . $register) ) {
                $directory = $bundleDirectory . $register;
                mkdir($directory, 0777, true);

                if ( !file_exists($directory . '.gitignore' . $register) ) {
                    $fp = fopen($directory . '.gitignore', "a+");
                    fwrite($fp, '');
                    fclose($fp);
                }

            }
        }

    }

    private function registerDoctrine($projectDir, $packageName, $bundleName)
    {
        $filePath = $projectDir . "/config/packages/doctrine.yaml";

        if (file_exists($filePath))
        {
            $fp = fopen($filePath, "a+");
            $text = $this->twig->render('base.html.twig');
           dd($text);


            /*$text =  $this->templateHelper->getRegisterDoctrineTemplate($packageName, $bundleName);
            $text = $this->inFile(file_get_contents($filePath), $text);
            fwrite($fp, $text);
            fclose($fp);*/
            //$this->triggerHelper->addFileGit( str_replace($projectDir.'/','', $filePath) );
        }
    }

    private function registerMenuAdmin()
    {

    }

    private function registerRoute()
    {

    }

    private function registerBundle()
    {

    }

    private function createApplicationFile()
    {

    }

    private function createRouteFile()
    {

    }

    private function createFrontControllerFile()
    {

    }

    private function createAdminControllerFile()
    {

    }

    private function createApiControllerFile()
    {

    }

    private function createRepositoryFile()
    {

    }

    private function createAdminFile()
    {

    }

    private function createEntityFile()
    {

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
