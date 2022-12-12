<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Bundle\Form;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeFormType
{
    protected string $filePath;
    protected string $baseTemplate = "/bundle/form/";
    protected string $template = "/bundle/form/form_type.php.twig";

    protected array $formFields = [];
    protected array $sonataTypeForms = [];
    protected array $namespaceRelationships = [];

    public function __construct(
        protected TwigHelper $twigHelper,
        protected string $bundleDirectory,
        protected string $baseNamespace,
        protected mixed $class,
        protected object $configuration,
    )
    {
        $this->filePath = $this->bundleDirectory . "/Form/" . $this->class->className . "Type.php";

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
        //dd($this->class);


        $this->formFields[] = $this->getBaseTemplate('components/form_fields.php.twig', [
            'attribute' => $this->class->attributes->primaryKey,
            'type' => 'primaryKey'
        ]);

        foreach ($this->class->attributes->default as $attribute){

            $this->sonataTypeForms[] = $attribute->sonataType->namespace;


            $this->formFields[] = $this->getBaseTemplate('components/form_fields.php.twig', [
                'attribute' => $attribute,
                'type' => 'default'
            ]);

        }

        foreach ($this->class->attributes->foreignKey as $foreignKey){

            $this->sonataTypeForms[] = $foreignKey->sonataType->namespace;

            $this->formFields[] = $this->getBaseTemplate('components/form_fields.php.twig', [
                'attribute' => $foreignKey,
                'type' => 'foreignKey'
            ]);

        }


        $this->sonataTypeForms = array_values( array_unique($this->sonataTypeForms) );


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
        return $this->twigHelper->getTwig()->render($this->template, [
            'packageName'       => $this->configuration->packageName,
            'baseNamespace'     => $this->baseNamespace,
            'className'         => $this->class->className,
            'formFields'        => $this->formFields,
            'typeForm'          => $this->sonataTypeForms,
            'namespaceRelationships' => $this->configuration->nameSpaceRelationships,

        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function getBaseTemplate(string $name, array $context): string
    {
        return $this->twigHelper->getTwig()->render($this->baseTemplate . $name, $context);
    }
}