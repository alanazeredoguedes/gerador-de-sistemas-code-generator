<?php

namespace App\Application\Generator\GeneratorBundle\Helper;

use Twig\Environment;

class TwigHelper
{
    protected Environment $twig;

    public function __construct(
        protected string $kernelDirectory,
        protected string $templateDirectory,
    )
    {
        $loader = new \Twig\Loader\FilesystemLoader($kernelDirectory . $templateDirectory);
        $this->twig = new \Twig\Environment($loader,[]);
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }


}