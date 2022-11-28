<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Bundle\Repository;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeRepository
{
    protected string $filePath;
    protected string $template = "/bundle/repository/repository.php.twig";

    public function __construct(
        protected TwigHelper $twigHelper,
        protected string $bundleDirectory,
        protected string $baseNamespace,
        protected mixed $class,
    )
    {
        $this->filePath = $this->bundleDirectory . "/Repository/" . $this->class->className . "Repository.php";

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
        //dd($this->class);
        return $this->twigHelper->getTwig()->render($this->template, [
            'baseNamespace' => $this->baseNamespace,
            'className' => $this->class->className,
            'methods' => $this->class->methods
        ]);
    }
}