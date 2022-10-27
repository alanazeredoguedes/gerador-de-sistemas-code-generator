<?php

namespace App\Application\Generator\GeneratorBundle;

use App\Application\Generator\GeneratorBundle\Helper\GitHelper;
use App\Application\Generator\GeneratorBundle\Helper\StringHelper;
use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use App\Application\Generator\GeneratorBundle\Maker\Admin\MakeAdmin;
use App\Application\Generator\GeneratorBundle\Maker\Controller\MakeAdminController;
use App\Application\Generator\GeneratorBundle\Maker\Controller\MakeApiController;
use App\Application\Generator\GeneratorBundle\Maker\Controller\MakeFrontController;
use App\Application\Generator\GeneratorBundle\Maker\Entity\MakeAttribute;
use App\Application\Generator\GeneratorBundle\Maker\Entity\MakeConstructor;
use App\Application\Generator\GeneratorBundle\Maker\Entity\MakeEntity;
use App\Application\Generator\GeneratorBundle\Maker\Entity\MakeGetter;
use App\Application\Generator\GeneratorBundle\Maker\Entity\MakeSetter;
use App\Application\Generator\GeneratorBundle\Maker\MakeApplicationFileBundle;
use App\Application\Generator\GeneratorBundle\Maker\MakeBundleDir;
use App\Application\Generator\GeneratorBundle\Maker\MakeDockerCompose;
use App\Application\Generator\GeneratorBundle\Maker\MakeEnv;
use App\Application\Generator\GeneratorBundle\Maker\MakeRouterFile;
use App\Application\Generator\GeneratorBundle\Maker\MakeSonataAdmin;
use App\Application\Generator\GeneratorBundle\Maker\Repository\MakeMethod;
use App\Application\Generator\GeneratorBundle\Maker\Repository\MakeRepository;
use Twig\Environment;

class Generator
{
    /** @var string Diretório onde são construídos os projetos gerados pelo sistema */
    protected string $workingDirectory;

    /** @var string Diretório do projeto a ser construído */
    protected string $projectDirectory;

    /** @var string Nome do pacote onde será construído as bundles */
    protected string $packageName = 'Internit';

    /** Helpers */
    protected GitHelper $gitHelper;
    protected StringHelper $stringHelper;
    protected TwigHelper $twigHelper;


    public function __construct(
        protected string $projectName,
        protected string $projectDescription,
        protected array  $class,
        protected array  $relationships,
        protected string $kernelDirectory,
    )
    {
        $this->initDependencies();

        $this->workingDirectory = $this->kernelDirectory . "/public/projects/";
        $this->projectDirectory =  $this->workingDirectory . $this->stringHelper->filterProjectDirName($projectName);

        $this->initDependencies2();
    }

    private function initDependencies(): void
    {
        $this->stringHelper = new StringHelper();
        $this->twigHelper = new TwigHelper(
            kernelDirectory: $this->kernelDirectory,
            templateDirectory: '/src/Application/Generator/GeneratorBundle/Resources/skeleton'
        );

    }

    private function initDependencies2(): void
    {
        $this->gitHelper = new GitHelper($this->kernelDirectory, $this->projectDirectory);
    }

    private function validateClass(): bool
    {

        return false;
    }

    private function validateRelationships(): bool
    {

        return false;
    }


    public final function startGenerator(): bool
    {
        $isValid = $this->validateClass();
        $isValid = $this->validateRelationships();


        /** Clona o repositório base e troca o nome do diretório conforme o projeto atual */
        //$this->gitHelper->cloneBaseRepository();

        /** Cria o arquivo docker-compose */
        $makeDockerCompose = new MakeDockerCompose(
            projectDirectory: $this->projectDirectory,
            twigHelper: $this->twigHelper,
            projectName: $this->stringHelper->filterProjectDirName($this->projectName)
        );
        //$makeDockerCompose->make();

        /** Cria o arquivo .env */
        $makeEnvFile = new MakeEnv(
            projectDirectory: $this->projectDirectory,
            twigHelper: $this->twigHelper,
        );
        //$makeEnvFile->make();

        /** Criar o arquivo de configuração do Sonata Admin. [sonata_admin.yaml] */
        $makeSonataAdmin = new MakeSonataAdmin(
            projectDirectory: $this->projectDirectory,
            twigHelper:       $this->twigHelper,
            projectName:      $this->projectName,
            projectDescription:  $this->projectDescription
        );
        //$makeSonataAdmin->make();


        /** Percorre todas as classe e cria outros arquivos do projeto */
        foreach ($this->class as $class){

            $bundleName = $this->stringHelper->createBundleName($class->className);
            $bundleDirectory = $this->projectDirectory . "/src/Application/" . $this->packageName . "/" . $bundleName;
            $baseNamespace = "App\Application\\" . $this->packageName . "\\" . "$bundleName" ;


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


           /* foreach ($class->attributes as $attribute) {

            }*/


            dump($class);


            /** Gera o arquivo de rota da bundle */
            $makeRouterFile = new MakeRouterFile();

            /** Gera o arquivo da controladora FrontEnd */
            $makeFrontController = new MakeFrontController();

            /** Gera o arquivo da controladora Administrativa */
            $makeAdminController = new MakeAdminController();

            /** Gera o arquivo da controladora Api */
            $makeApiController = new MakeApiController();

            /** Gera o arquivo de Repositório */
            $makeRepository = new MakeRepository();
            $makeMethod = new MakeMethod();

            /** Gera o arquivo Admin */
            $makeAdmin = new MakeAdmin();

            /** Gera o arquivo da Entidade */
            $makeEntity = new MakeEntity();
            $makeConstructor = new MakeConstructor();
            $makeAttribute = new MakeAttribute();
            $makeGetter = new MakeGetter();
            $makeSetter = new MakeSetter();





        }




        /** Registra a bundle no arquivo do doctrine [doctrine.yaml] */
        //registerDoctrine

        /** Registra o serviço da bundle no arquivo de serviços [services.yaml] */
        //$this->registerMenuAdmin();

        /** Registra a bundle no arquivo de rotas [routes.yaml] */
        //$this->registerRoute();

        /** Registra a bundle no arquivo de bundles [bundles.php] */
        //$this->registerBundle();






        return true;
    }

}