<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Bundle\Entity;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeGetterSetter
{
    protected string $filePath;
    protected string $template = "/bundle/entity/";

    public function __construct(
        protected TwigHelper $twigHelper,
        protected mixed $attribute,
        protected string $typeAttribute,
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

    public function make(): string
    {
        //dd($this->attribute);

        if($this->typeAttribute === 'primaryKey'){

            /** Generate Primary Key */
            return $this->getTemplate('getter_setter/primary_key.php.twig', [
                'attribute' => $this->attribute
            ]);

        }elseif ($this->typeAttribute === 'foreignKey') {

            /** Generate Foreign Key */


        }elseif ($this->typeAttribute === 'default') {

            /** Generate Default Attribute */
            return $this->getTemplate('getter_setter/attribute.php.twig', [
                'attribute' => $this->attribute
            ]);

        }

        return '';
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function getTemplate(string $name, array $context): string
    {
        return $this->twigHelper->getTwig()->render($this->template . $name, $context);
    }

}