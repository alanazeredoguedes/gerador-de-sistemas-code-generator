<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Config\Packages;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeSonataAdmin
{
    protected string $filePath = "/config/packages/sonata_admin.yaml";
    protected string $template = "/config/packages/sonata_admin.yaml.twig";

    public function __construct(
        protected string $projectDirectory,
        protected TwigHelper $twigHelper,
        protected string $projectName,
        protected string $projectDescription,
    )
    {
        $this->filePath = $this->projectDirectory . $this->filePath;
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
        //chmod( $this->filePath, 0777);
        file_put_contents($this->filePath, $this->getTemplate());
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function getTemplate(): string
    {
        return $this->twigHelper->getTwig()->render($this->template,[
            'projectName' => $this->projectName,
            'projectDescription' => $this->projectDescription
        ]);
    }
}