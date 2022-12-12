<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Bundle\Admin;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeAdmin
{
    protected string $filePath;
    protected string $baseTemplate = "/bundle/admin/";
    protected string $template = "/bundle/admin/admin.php.twig";

    protected array $sonataTypeForms = [];

    protected array $formFields = [];
    protected array $datagridFilters = [];
    protected array $listFields = [];
    protected array $showFields = [];
    protected array $namespaceRelationships = [];

    public function __construct(
        protected TwigHelper $twigHelper,
        protected string $bundleDirectory,
        protected string $baseNamespace,
        protected mixed $class,
        protected object $configuration,
    )
    {
        $this->filePath = $this->bundleDirectory . "/Admin/" . $this->class->className . "Admin.php";

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

        $this->sonataTypeForms[] = $this->class->attributes->primaryKey->sonataType->namespace;

        $this->formFields[] = $this->getBaseTemplate('components/form_fields.php.twig', [
            'attribute' => $this->class->attributes->primaryKey,
            'type' => 'primaryKey'
        ]);

        $this->datagridFilters[] = $this->getBaseTemplate('components/datagrid_filters.php.twig', [
            'attribute' => $this->class->attributes->primaryKey,
            'type' => 'primaryKey'
        ]);

        $this->listFields[] = $this->getBaseTemplate('components/list_fields.php.twig', [
            'attribute' => $this->class->attributes->primaryKey,
            'type' => 'primaryKey'
        ]);

        $this->showFields[] = $this->getBaseTemplate('components/show_fields.php.twig', [
            'attribute' => $this->class->attributes->primaryKey,
            'type' => 'primaryKey'
        ]);


        foreach ($this->class->attributes->default as $attribute){

            $this->sonataTypeForms[] = $attribute->sonataType->namespace;

            $this->formFields[] = $this->getBaseTemplate('components/form_fields.php.twig', [
                'attribute' => $attribute,
                'type' => 'default'
            ]);

            $this->datagridFilters[] = $this->getBaseTemplate('components/datagrid_filters.php.twig', [
                'attribute' => $attribute,
                'type' => 'default'
            ]);

            $this->listFields[] = $this->getBaseTemplate('components/list_fields.php.twig', [
                'attribute' => $attribute,
                'type' => 'default'
            ]);

            $this->showFields[] = $this->getBaseTemplate('components/show_fields.php.twig', [
                'attribute' =>$attribute,
                'type' => 'default'
            ]);

        }

        //dd($this->class->attributes);

        foreach ($this->class->attributes->foreignKey as $foreignKey){

            $this->sonataTypeForms[] = $foreignKey->sonataType->namespace;

          //  dd($foreignKey);

            $this->formFields[] = $this->getBaseTemplate('components/form_fields.php.twig', [
                'attribute' => $foreignKey,
                'type' => 'foreignKey'
            ]);

            $this->datagridFilters[] = $this->getBaseTemplate('components/datagrid_filters.php.twig', [
                'attribute' => $foreignKey,
                'type' => 'foreignKey'
            ]);

            $this->listFields[] = $this->getBaseTemplate('components/list_fields.php.twig', [
                'attribute' => $foreignKey,
                'type' => 'foreignKey'
            ]);

            $this->showFields[] = $this->getBaseTemplate('components/show_fields.php.twig', [
                'attribute' =>$foreignKey,
                'type' => 'foreignKey'
            ]);

        }



            $this->sonataTypeForms = array_values( array_unique($this->sonataTypeForms) );

        //dd($this->sonataTypeForms);

        $fp = fopen($this->filePath, "a+");
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
        //dd($this->configuration, $this->class);

        //dd($this->configuration->nameSpaceRelationships);
        //dd($this->class->attributes->default);
        return $this->twigHelper->getTwig()->render($this->template,[
            'baseNamespace'    => $this->baseNamespace,
            'className'        => $this->class->className,
            'primaryKey'       => $this->class->attributes->primaryKey->attributeName,
            'typeForm'         => $this->sonataTypeForms,
            'formFields'       => $this->formFields,
            'datagridFilters'  => $this->datagridFilters,
            'listFields'       => $this->listFields,
            'showFields'       => $this->showFields,
            'allAttributes'    => $this->class->allAttributes,
            'packageName'       => $this->configuration->packageName,
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