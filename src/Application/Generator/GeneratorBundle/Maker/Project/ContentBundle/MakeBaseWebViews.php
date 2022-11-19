<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Project\ContentBundle;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeBaseWebViews
{
    protected string $baseFilePath = "/src/Application/Project/ContentBundle/Resources/views/home/";
    protected string $template = "/project/contentbundle/resources/views/home/";

    public function __construct(
        protected TwigHelper $twigHelper,
        protected string $projectDirectory,
        protected array $registerBundle,
        protected string $projectName,

    )
    {
        $this->baseFilePath = $this->projectDirectory . $this->baseFilePath;
        //dd($this->filePath);
        $this->validate();
        $this->filter();
    }

    public function validate()
    {

    }

    public function filter()
    {

    }

    public function make()
    {
        /** Make Dashboard */
        $fp = fopen($this->baseFilePath . 'dashboard.html.twig', "w+");
        $template = $this->getTemplate('dashboard.html.twig', [
            'registerBundle' => $this->registerBundle,

        ]);
        fwrite($fp, $template);
        fclose($fp);


        /** Make Dashboard */
        $fp = fopen($this->baseFilePath . 'home.html.twig', "w+");
        $template = $this->getTemplate('home.html.twig', [
            'projectName' => $this->projectName,

        ]);
        fwrite($fp, $template);
        fclose($fp);

    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function getTemplate($template, $parameter = []): string
    {
        return $this->twigHelper->getTwig()->render($this->template . $template, $parameter);
    }
}