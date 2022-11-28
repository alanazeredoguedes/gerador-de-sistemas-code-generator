<?php

namespace App\Application\Generator\GeneratorBundle\Maker;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeEnv
{
    protected string $filePath = "/.env";
    protected string $template = "/env.twig";

    public function __construct(
        protected string $projectDirectory,
        protected TwigHelper $twigHelper,
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


    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function make(): void
    {
        //chmod( $this->filePath, 0777);

        if (!file_exists($this->filePath))
        {
            $fp = fopen($this->filePath, "a+");
            fwrite($fp, $this->getTemplate());
            fclose($fp);
        }else{
            file_put_contents($this->filePath, $this->getTemplate());
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
        ]);
    }
}