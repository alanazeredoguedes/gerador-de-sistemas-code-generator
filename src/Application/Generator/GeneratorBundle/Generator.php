<?php

namespace App\Application\Generator\GeneratorBundle;

use App\Application\Generator\GeneratorBundle\Helper\GitHelper;
use App\Application\Generator\GeneratorBundle\Helper\StringHelper;
use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use App\Application\Generator\GeneratorBundle\Maker\MakeApplicationFileBundle;
use App\Application\Generator\GeneratorBundle\Maker\MakeBundleDir;
use App\Application\Generator\GeneratorBundle\Maker\MakeRouterFile;
use App\Application\Generator\GeneratorBundle\Maker\MakeSonataAdmin;
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



        //$this->gitHelper->cloneBaseRepository();


        /** Criar o Arquivo de configuração do Sonata Admin */
        $makeSonataAdmin = new MakeSonataAdmin(
            projectDirectory: $this->projectDirectory,
            twigHelper:       $this->twigHelper,
            projectName:      $this->projectName,
            projectDescription:  $this->projectDescription
        );
        //$makeSonataAdmin->make();



        foreach ($this->class as $class){

            $bundleName = $this->stringHelper->createBundleName($class->className);
            $bundleDirectory = $this->projectDirectory . "/src/Application/" . $this->packageName . "/" . $bundleName;
            $baseNamespace = "App\Application\\" . $this->packageName . "\\" . "$bundleName" ;


            /** Cria o diretório da bundle  */
            $makeBundleDir = new MakeBundleDir(
                projectDirectory: $this->projectDirectory,
                bundleDirectory:  $bundleDirectory,
                className:        $class->className,
                twigHelper:       $this->twigHelper,
            );
            $makeBundleDir->make();


            /** Gera o arquivo de registro da bundle  */
            $makeApplicationFileBundle = new MakeApplicationFileBundle(
                twigHelper:  $this->twigHelper,
                bundleDirectory:  $bundleDirectory,
                baseNamespace:  $baseNamespace,
                bundleName:  $bundleName,
                packageName:  $this->packageName
            );
            $makeApplicationFileBundle->make();


            $makeRouterFile = new MakeRouterFile();




            /** Gera o arquivo de rota da bundle */
            //$this->createRouteFile();

            /** Gera o arquivo da controladora FrontEnd */
            //$this->createFrontControllerFile();

            /** Gera o arquivo da controladora Administrativa */
            //$this->createAdminControllerFile();

            /** Gera o arquivo da controladora Api */
            //$this->createApiControllerFile();

            /** Gera o arquivo de Repositório */
            //$this->createRepositoryFile();

            /** Gera o arquivo Admin */
            //$this->createAdminFile();

            /** Gera o arquivo da Entidade */
            //$this->createEntityFile();



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