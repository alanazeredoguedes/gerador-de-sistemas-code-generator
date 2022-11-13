<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Bundle\Resources\Views;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeWebViews
{
    protected string $filePath;
    protected string $template = "/bundle/resources/views/";

    public function __construct(
        protected TwigHelper $twigHelper,
        protected string $bundleDirectory,
        protected string $baseNamespace,
        protected mixed $class,
    )
    {
        $this->filePath = $this->bundleDirectory . "/Resources/views/" . strtolower($this->class->className) . "/";

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

        /** Create File Views Create */
        $fp = fopen($this->filePath.'create.html.twig', "a+");
        $template = $this->getTemplate('create.html.twig', []);
        fwrite($fp, $template);
        fclose($fp);

        /** Create File Views Edit */
        $fp = fopen($this->filePath.'edit.html.twig', "a+");
        $template = $this->getTemplate('edit.html.twig', []);
        fwrite($fp, $template);
        fclose($fp);

        /** Create File Views Show */
        $fp = fopen($this->filePath.'show.html.twig', "a+");
        $template = $this->getTemplate('show.html.twig', []);
        fwrite($fp, $template);
        fclose($fp);

        /** Create File Views List */
        $fp = fopen($this->filePath.'list.html.twig', "a+");
        $template = $this->getTemplate('list.html.twig', []);
        fwrite($fp, $template);
        fclose($fp);

    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function getTemplate($name, $params): string
    {
        return $this->twigHelper->getTwig()->render($this->template . $name, $params );
    }
}