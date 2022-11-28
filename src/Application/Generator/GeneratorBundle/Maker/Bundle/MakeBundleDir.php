<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Bundle;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;

class MakeBundleDir
{

    public function __construct(
        protected string $projectDirectory,
        protected string $bundleDirectory,
        protected string $className,
        protected TwigHelper $twigHelper,
    )
    {
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

        $registerDirectory = [
            '/Admin/',
            '/Controller/',
            '/Entity/',
            '/Repository/',
            '/Form/',
            '/Tests/',

            // Resources Directory
            '/Resources/config/routes/',
            '/Resources/public/css/',
            '/Resources/public/js/',
            '/Resources/public/fonts/',
            '/Resources/public/images/',

            // Views Directory
            '/Resources/views/' . strtolower($this->className) . '/macros/',
            '/Resources/views/' . strtolower($this->className) . '/template/',
            '/Resources/views/' . strtolower($this->className) . '/components/',
        ];


        foreach ($registerDirectory as $register)
        {

            if ( !file_exists($this->bundleDirectory . $register) )
            {

                $directory = $this->bundleDirectory . $register;
                mkdir($directory, 0777, true);

                if ( !file_exists($directory . '.gitignore') )
                {
                    $fp = fopen($directory . '.gitignore', "a+");
                    fwrite($fp, '');
                    fclose($fp);
                }

            }
        }

    }

}