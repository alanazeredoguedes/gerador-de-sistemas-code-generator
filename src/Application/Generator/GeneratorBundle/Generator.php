<?php

namespace App\Application\Generator\GeneratorBundle;

use App\Application\Generator\GeneratorBundle\AwsHelper\AwsHelper;
use App\Application\Generator\GeneratorBundle\Helper\CommandsHelper;
use App\Application\Generator\GeneratorBundle\Helper\GitHelper;
use App\Application\Generator\GeneratorBundle\Helper\StringHelper;
use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use App\Application\Generator\GeneratorBundle\Helper\ValidateDiagram;
use App\Application\Generator\GeneratorBundle\Maker\Bundle\Admin\MakeAdmin;
use App\Application\Generator\GeneratorBundle\Maker\Bundle\Controller\MakeAdminController;
use App\Application\Generator\GeneratorBundle\Maker\Bundle\Controller\MakeApiController;
use App\Application\Generator\GeneratorBundle\Maker\Bundle\Controller\MakeAuthController;
use App\Application\Generator\GeneratorBundle\Maker\Bundle\Controller\MakeWebController;
use App\Application\Generator\GeneratorBundle\Maker\Bundle\Entity\MakeAttribute;
use App\Application\Generator\GeneratorBundle\Maker\Bundle\Entity\MakeConstructor;
use App\Application\Generator\GeneratorBundle\Maker\Bundle\Entity\MakeEntity;
use App\Application\Generator\GeneratorBundle\Maker\Bundle\MakeApplicationFileBundle;
use App\Application\Generator\GeneratorBundle\Maker\Bundle\MakeBundleDir;
use App\Application\Generator\GeneratorBundle\Maker\Bundle\Repository\MakeRepository;
use App\Application\Generator\GeneratorBundle\Maker\Bundle\Resources\Config\Routes\MakeRouterFileBundle;
use App\Application\Generator\GeneratorBundle\Maker\Bundle\Resources\Views\MakeWebViews;
use App\Application\Generator\GeneratorBundle\Maker\Config\MakeRegisterBundle;
use App\Application\Generator\GeneratorBundle\Maker\Config\MakeRegisterRoute;
use App\Application\Generator\GeneratorBundle\Maker\Config\MakeRegisterService;
use App\Application\Generator\GeneratorBundle\Maker\Config\Packages\MakeRegisterDoctrine;
use App\Application\Generator\GeneratorBundle\Maker\Config\Packages\MakeSonataAdmin;
use App\Application\Generator\GeneratorBundle\Maker\MakeBaseConfigurationClass;
use App\Application\Generator\GeneratorBundle\Maker\MakeDockerCompose;
use App\Application\Generator\GeneratorBundle\Maker\MakeEnv;
use App\Application\Generator\GeneratorBundle\Maker\MakeReadme;
use App\Application\Generator\GeneratorBundle\Maker\MakeStartProjectScript;
use App\Application\Generator\GeneratorBundle\Maker\Project\ContentBundle\MakeBaseWebViews;
use stdClass;

class Generator
{
    /** @var string Diretório onde são construídos os projetos gerados pelo sistema */
    protected string $workingDirectory;

    /** @var string Diretório do projeto a ser construído */
    protected string $projectDirectory;

    /** @var string Nome do pacote onde será construído as bundles */
    protected string $packageName = 'Schema';

    /** Helpers */
    protected GitHelper $gitHelper;
    protected CommandsHelper $commandsHelper;
    protected StringHelper $stringHelper;
    protected TwigHelper $twigHelper;
    protected ValidateDiagram $validateDiagram;

    protected string $projectName;
    protected string $projectNameBuild;
    protected string $projectDescription;
    protected array  $class;
    protected array  $relationships;

    public function __construct(
        protected $projectData,
        protected string $kernelDirectory,
        protected AwsHelper $awsHelper,
    )
    {
        $this->projectName = $this->projectData->app->name;
        $this->projectDescription = $this->projectData->app->description;
        $this->class = $this->projectData->app->diagram->structure->class;
        $this->relationships = $this->projectData->app->diagram->structure->relationships;


        $this->initDependencies();

        $this->projectNameBuild = $this->projectData->user->id . $this->projectData->app->id;
        $this->projectNameBuild = $this->stringHelper->cleanString( hash('md5', $this->projectNameBuild) );

        $this->workingDirectory = $this->kernelDirectory . "/public/projects/";
        $this->projectDirectory =  $this->workingDirectory .  $this->projectNameBuild;

        $this->initDependencies2();

    }

    private function initDependencies(): void
    {
        $this->stringHelper = new StringHelper();
        $this->twigHelper = new TwigHelper(
            kernelDirectory:     $this->kernelDirectory,
            templateDirectory:   '/src/Application/Generator/GeneratorBundle/Resources/skeleton'
        );

    }

    private function initDependencies2(): void
    {
        $this->gitHelper = new GitHelper(
            workingDirectory:     $this->workingDirectory,
            projectDirectory:     $this->projectDirectory,
            projectName:          $this->stringHelper->filterProjectDirName($this->projectName),
            projectNameBuild: $this->projectNameBuild,
        );

        $this->commandsHelper = new CommandsHelper(
            workingDirectory:     $this->workingDirectory,
            projectDirectory:     $this->projectDirectory,
            projectName:          $this->stringHelper->filterProjectDirName($this->projectName)
        );

        $this->validateDiagram = new ValidateDiagram(
            class:                $this->class,
            relationships:        $this->relationships
        );

    }

    public final function startGenerator(): bool
    {
//        $validate = $this->validateDiagram->checkIntegrity();
//        if(!$validate->status)
//            dd($validate);

        $this->validateDiagram->transformData();
        $this->class = $this->validateDiagram->classValidate;

        //dd( $this->class );


        /** Clona o repositório base e troca o nome do diretório conforme o projeto atual */
        $this->gitHelper->cloneBaseRepository();

        /** Cria o arquivo docker-compose */
        $makeDockerCompose = new MakeDockerCompose(
            projectDirectory: $this->projectDirectory,
            twigHelper: $this->twigHelper,
            projectName: $this->stringHelper->filterProjectDirName($this->projectName)
        );
        $makeDockerCompose->make();

        /** Cria o arquivo .env */
        $makeEnvFile = new MakeEnv(
            projectDirectory: $this->projectDirectory,
            twigHelper: $this->twigHelper,
        );
        $makeEnvFile->make();

        /** Criar o arquivo de configuração do Sonata Admin. [sonata_admin.yaml] */
        $makeSonataAdmin = new MakeSonataAdmin(
            projectDirectory:    $this->projectDirectory,
            twigHelper:          $this->twigHelper,
            projectName:         $this->projectName,
            projectDescription:  $this->projectDescription
        );
        $makeSonataAdmin->make();

        /** Criar o arquivo de documentação do repositorio. [.README.md] */
        $makeReadme = new MakeReadme(
            projectDirectory:    $this->projectDirectory,
            twigHelper:          $this->twigHelper,
            projectName:         $this->projectName,
            projectDescription:  $this->projectDescription,
        );
        $makeReadme->make();

        $makeBackground = new MakeBackground(
            kernelDirectory:    $this->kernelDirectory,
            projectDirectory:    $this->projectDirectory,
        );
        $makeBackground->make();



        /** Array com definiçaão de todas as bunldes para ser usado para gerar arquivos de registro no final do script */
        $registerBundle = [ /* "packageName" => "", //"bundleName" => "", //"className" => "" */ ];


        /** REMOVER DEPOIS */
//        $packageDir = $this->projectDirectory . "/src/Application/" . $this->packageName . '/';
//        $this->commandsHelper->recursiveRemoveDir($packageDir);
//        sleep(3);

        /** Percorre todas as classe e cria outros arquivos do projeto */
        foreach ($this->class as $class){

            $configurationClass = new MakeBaseConfigurationClass(
                stringHelper: $this->stringHelper,
                packageName: $this->packageName,
                projectDirectory: $this->projectDirectory,
                class: $class,
            );
            $configuration = $configurationClass->getAllConfiguration();
            //dd($configuration);





            /** Bundle Name = ExemploBundle */
            $bundleName = $this->stringHelper->createBundleName($class->className);

            /** Bundle Directory = /var/www/public/projects/project-dir/src/Application/Package/ExempleBundle */
            $bundleDirectory = $this->projectDirectory . "/src/Application/" . $this->packageName . "/" . $bundleName;

            /** Bundle Namespace = App\Application\Package\ExempleBundle */
            $baseNamespace = "App\Application\\" . $this->packageName . "\\" . "$bundleName" ;




            /** Utilizado para Realizar registro em arquivos de configuração no final do script */
            $registerBundle[] = [
                "packageName" => $this->packageName,
                "bundleName" => $bundleName,
                "className" => $class->className,
            ];

            /** Cria a estrutura de diretórios da bundle da classe atual  */
            $makeBundleDir = new MakeBundleDir(
                projectDirectory: $this->projectDirectory,
                bundleDirectory:  $bundleDirectory,
                className:        $class->className,
                twigHelper:       $this->twigHelper,
            );
            $makeBundleDir->make();

            /** Cria o arquivo de registro da bundle atual */
            $makeApplicationFileBundle = new MakeApplicationFileBundle(
                twigHelper:  $this->twigHelper,
                bundleDirectory:  $bundleDirectory,
                baseNamespace:  $baseNamespace,
                bundleName:  $bundleName,
                packageName:  $this->packageName
            );
            $makeApplicationFileBundle->make();

            /** Gera o arquivo de rota da bundle */
            $makeRouterFileBundle = new MakeRouterFileBundle(
                twigHelper:  $this->twigHelper,
                bundleDirectory:  $bundleDirectory,
                baseNamespace:  $baseNamespace,
                bundleName:  $bundleName,
                packageName: $this->packageName
            );
            $makeRouterFileBundle->make();

            /** Gera o arquivo de Repositório */
            $makeRepository = new MakeRepository(
                twigHelper:  $this->twigHelper,
                bundleDirectory:  $bundleDirectory,
                baseNamespace:  $baseNamespace,
                class:  $class,
            );
            $makeRepository->make();

            /** Gera o arquivo da controladora Administrativa */
            $makeAdminController = new MakeAdminController(
                twigHelper:  $this->twigHelper,
                bundleDirectory:  $bundleDirectory,
                baseNamespace:  $baseNamespace,
                class:  $class,
            );
            $makeAdminController->make();

            /** Gera o arquivo da controladora Api */
            $makeApiController = new MakeApiController(
                twigHelper:  $this->twigHelper,
                bundleDirectory:  $bundleDirectory,
                baseNamespace:  $baseNamespace,
                class:  $class,
                configuration: $configuration,
            );
            $makeApiController->make();

            /** Gera o arquivo da controladora Auth — Caso seja uma classe provedora de usuário */
//            $makeAuthController = new MakeAuthController();
//            $makeAuthController->make();


            /** Gera o arquivo da controladora FrontEnd */
            $makeWebController = new MakeWebController(
                twigHelper:  $this->twigHelper,
                bundleDirectory:  $bundleDirectory,
                baseNamespace:  $baseNamespace,
                class:  $class,
                packageName: $this->packageName,
            );
            $makeWebController->make();

            /** Gera o arquivo de visualização web - [ create, edit, show, list ] */
            $makeWebViews = new MakeWebViews(
                twigHelper:  $this->twigHelper,
                bundleDirectory:  $bundleDirectory,
                baseNamespace:  $baseNamespace,
                class:  $class,
            );
            $makeWebViews->make();

            /** Gera o arquivo Admin */
            $makeAdmin = new MakeAdmin(
                twigHelper:  $this->twigHelper,
                bundleDirectory:  $bundleDirectory,
                baseNamespace:  $baseNamespace,
                class:  $class,
                configuration: $configuration,
            );
            $makeAdmin->make();

            /** Gera o arquivo da Entidade */
            $makeEntity = new MakeEntity(
                twigHelper: $this->twigHelper,
                bundleDirectory: $bundleDirectory,
                packageName: $this->packageName,
                baseNamespace: $baseNamespace,
                class: $class,
            );
            $makeEntity->make();

            /** Gera o arquivo Form Type */
            //$makeForm = new MakeFormType();
        }

        /** Registra a bundle no arquivo de bundles [bundles.php] */
        $makeStartProjectScript = new MakeStartProjectScript(
            twigHelper:  $this->twigHelper,
            projectDirectory: $this->projectDirectory,
            registerBundle: $registerBundle,
            userName: $this->projectData->user->username,
            email: $this->projectData->user->email,
            password: $this->projectNameBuild
        );
        $makeStartProjectScript->make();

        $makeBaseWeb = new MakeBaseWebViews(
            twigHelper:  $this->twigHelper,
            projectDirectory: $this->projectDirectory,
            registerBundle: $registerBundle,
            projectName: $this->projectName
        );
        $makeBaseWeb->make();

        /** Registra a bundle no arquivo de bundles [bundles.php] */
        $makeRegisterBundle = new MakeRegisterBundle(
            twigHelper:  $this->twigHelper,
            projectDirectory: $this->projectDirectory,
            registerBundle: $registerBundle,
        );
        $makeRegisterBundle->make();

        /** Registra a bundle no arquivo do doctrine [doctrine.yaml] */
        $makeRegisterDoctrine = new MakeRegisterDoctrine(
            twigHelper:  $this->twigHelper,
            projectDirectory: $this->projectDirectory,
            registerBundle: $registerBundle,
        );
        $makeRegisterDoctrine->make();

        /** Registra a bundle no arquivo de rotas [routes.yaml] */
        $makeRegisterRoute = new MakeRegisterRoute(
            twigHelper:  $this->twigHelper,
            projectDirectory: $this->projectDirectory,
            registerBundle: $registerBundle,
        );
        $makeRegisterRoute->make();

        /** Registra o serviço da bundle no arquivo de serviços [services.yaml] */
        $makeRegisterService = new MakeRegisterService(
            twigHelper:  $this->twigHelper,
            projectDirectory: $this->projectDirectory,
            registerBundle: $registerBundle,
        );
        $makeRegisterService->make();


        /** ************************************************************************
         * Integração Do Projeto Gerado */



        dd("Stop Integration");
        exit;


        $fp = fopen("$this->projectDirectory/end.txt","wb");
        fwrite($fp,'');
        fclose($fp);
        sleep(15);

        /** Faz commit do projeto no gitHub e retorna url do repositório */
        $repositoryUrl = $this->gitHelper->commitProject();


        /** Notifica sistema sobre geração do repositório */
        $this->awsHelper->sns->sendMessageGdsSistemaGeradoRepositorio(json_encode([
            'client' => $this->projectData->user->id,
            'app' => $this->projectData->app->id,
            'repository' => $repositoryUrl,
        ]));


        /** Cria instancia da ec2 e coloca o projeto em produção  */
        $publicIp = $this->awsHelper->ec2->makeImage(
           projectNameBuild:  $this->projectNameBuild,
            scriptUserData:  $this->twigHelper->getTwig()->render('/script_start_ec2.txt.twig',
                [ 'repositoryUrl' => $repositoryUrl ]
            ),
        );

        //sleep(90);

        /** Notifica sistema sobre geração do servidor */
        $this->awsHelper->sns->sendMessageGdsSistemaGeradoServidor(json_encode([
            'client' => $this->projectData->user->id,
            'app' => $this->projectData->app->id,
            'url' => $publicIp,
            'email' => $this->projectData->user->email,
            'password' => $this->projectNameBuild,
        ]));


        dd($repositoryUrl);

        return true;
    }

}