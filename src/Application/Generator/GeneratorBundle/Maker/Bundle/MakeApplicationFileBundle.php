<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Bundle;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeApplicationFileBundle
{
    protected string $filePath;
    protected string $template = "/bundle/application_bundle.php.twig";

    public function __construct(
        protected TwigHelper $twigHelper,
        protected string $bundleDirectory,
        protected string $baseNamespace,
        protected string $bundleName,
        protected string $packageName,
    )
    {
        $this->filePath = $this->bundleDirectory . "/Application" . $packageName . $bundleName . ".php";

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
            'baseNamespace' => $this->baseNamespace,
            'packageName' => $this->packageName,
            'bundleName' => $this->bundleName,
        ]);
    }
}