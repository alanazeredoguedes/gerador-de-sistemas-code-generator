<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Bundle\Controller;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeWebController
{
    protected string $filePath;
    protected string $template = "/bundle/controller/web_controller.php.twig";

    public function __construct(
        protected TwigHelper $twigHelper,
        protected string $bundleDirectory,
        protected string $baseNamespace,
        protected mixed $class,
        protected string $packageName,
    )
    {
        $this->filePath = $this->bundleDirectory . "/Controller/" . $this->class->className . "WebController.php";

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
        //dd($this->bundleDirectory, $this->baseNamespace, $this->filePath,  $this->class);

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
            'className' => $this->class->className,
            'packageName' => $this->packageName,
            'attributePrimaryKey' => $this->class->attributes->primaryKey,
            'attributeForeignKey' => $this->class->attributes->foreignKey,
            'attributeDefault' => $this->class->attributes->default,
        ]);
    }
}