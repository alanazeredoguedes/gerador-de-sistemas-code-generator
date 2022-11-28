<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Config;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeRegisterService
{
    protected string $filePath = "/config/services.yaml";
    protected string $template = "/config/services.yaml.twig";

    public function __construct(
        protected TwigHelper $twigHelper,
        protected string $projectDirectory,
        protected array $registerBundle,
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
        //dd($this->registerBundle);
        $fp = fopen($this->filePath, "w+");
        $template = $this->getTemplate();
        fwrite($fp, $template);
        fclose($fp);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function getTemplate(): string
    {
        return $this->twigHelper->getTwig()->render($this->template,[
            'bundles' => $this->registerBundle,
        ]);
    }
}