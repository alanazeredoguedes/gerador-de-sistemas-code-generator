<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Bundle\Resources\Config\Routes;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeRouterFileBundle
{
    protected string $filePath = "/Resources/config/routes/routes.yaml";
    protected string $template = "/bundle/resources/config/routes/routes.yaml.twig";

    public function __construct(
        protected TwigHelper $twigHelper,
        protected string $bundleDirectory,
        protected string $baseNamespace,
        protected string $bundleName,
        protected string $packageName,
    )
    {
        $this->filePath = $this->bundleDirectory . $this->filePath;

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

        if (!file_exists($this->filePath))
        {
            $fp = fopen($this->filePath, "a+");
            $template = $this->getTemplate();
            fwrite($fp, $template);
            fclose($fp);
        }

    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function getTemplate(): string
    {
        return $this->twigHelper->getTwig()->render($this->template,[
            'packageName' => $this->packageName,
            'bundleName' => $this->bundleName,
        ]);
    }
}