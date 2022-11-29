<?php

namespace App\Application\Generator\GeneratorBundle;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeBackground
{
    protected string $filePath = "/src/Application/Project/ContentBundle/Resources/public/images/background/background-66.jpg";
    protected string $image = "/src/Application/Generator/GeneratorBundle/Resources/skeleton/background-images/";

    public function __construct(
        protected string $kernelDirectory,
        protected string $projectDirectory,
    )
    {
        $this->filePath = $this->projectDirectory . $this->filePath;
        $this->image = $this->kernelDirectory . $this->image;
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
        $image = "background-".rand(1,88).".jpg";

        copy($this->image.$image, $this->filePath );

        //chmod( $this->filePath, 0777);
        //file_put_contents($this->filePath, $this->getTemplate());
    }


}