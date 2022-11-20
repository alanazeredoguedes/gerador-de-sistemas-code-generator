<?php

namespace App\Application\Generator\GeneratorBundle\Maker;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeStartProjectScript
{
    protected string $filePath = "/start_project.sh";
    protected string $template = "/start_project.sh.twig";

    public function __construct(
        protected TwigHelper $twigHelper,
        protected string $projectDirectory,
        protected array $registerBundle,
        protected string $userName,
        protected string $email,
        protected string $password,

    )
    {
        $this->filePath = $this->projectDirectory . $this->filePath;

        //dd($this->filePath);
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
            'userName' => $this->userName,
            'email' => $this->email,
            'password' => $this->password,
        ]);
    }
}