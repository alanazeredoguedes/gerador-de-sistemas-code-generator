<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Bundle\Entity;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeEntity
{
    protected string $filePath;
    protected string $template = "/bundle/entity/entity.php.twig";

    protected array $uniqueAttributes = [];
    protected array $attributes = [];
    protected array $construct = [];
    protected array $gettersAndSetters = [];
    protected array $namespaceRelationships = [];

    public function __construct(
        protected TwigHelper $twigHelper,
        protected string $bundleDirectory,
        protected string $packageName,
        protected string $baseNamespace,
        protected mixed $class,
    )
    {
        $this->filePath = $this->bundleDirectory . "/Entity/" . $this->class->className . ".php";

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

        /** #########################################################################################################
         * ########## Create Primary Key */

        /** Create Primary Key */
        $makeAttribute = new MakeAttribute(
            twigHelper: $this->twigHelper,
            attribute: $this->class->attributes->primaryKey,
            typeAttribute: 'primaryKey'
        );
        $data = $makeAttribute->make();
        $this->attributes[] = $data->template;
        $this->uniqueAttributes[] = $this->class->attributes->primaryKey->attributeName;

        /** Primary Key Getter and Setter */
        $makeGetterSetter = new MakeGetterSetter(
            twigHelper: $this->twigHelper,
            attribute: $this->class->attributes->primaryKey,
            typeAttribute: 'primaryKey'
        );
        $this->gettersAndSetters[] = $makeGetterSetter->make();


        /** #########################################################################################################
         * ########## Create Default Attributes */
        foreach ($this->class->attributes->default as $attribute){

            if($attribute->unique)
                $this->uniqueAttributes[] = $attribute->attributeName;

            /** Create Default Attributes */
            $makeAttribute = new MakeAttribute(
                twigHelper: $this->twigHelper,
                attribute: $attribute,
                typeAttribute: 'default'
            );
            $data = $makeAttribute->make();
            $this->attributes[] = $data->template;

            /** Default Attributes Getter and Setter */
            $makeGetterSetter = new MakeGetterSetter(
                twigHelper: $this->twigHelper,
                attribute: $attribute,
                typeAttribute: 'default'
            );
            $this->gettersAndSetters[] = $makeGetterSetter->make();

        }


        /** #########################################################################################################
         * ########## Create Foreign Key */
        foreach ($this->class->attributes->foreignKey as $foreignKey){

            $makeAttribute = new MakeAttribute(
                twigHelper: $this->twigHelper,
                attribute: $foreignKey,
                typeAttribute: 'foreignKey'
            );
            $data = $makeAttribute->make();
            $this->attributes[] = $data->template;

            if($data->constructor)
                foreach ($data->constructor as $constructor)
                    $this->construct[] = $constructor;

            if($data->namespaceRelationships)
                foreach ($data->namespaceRelationships as $namespaceRelationships){
                    //dd($data);
                    if( in_array($data->namespaceRelationships[0], ['SonataMediaMedia', 'SonataMediaGallery']) )
                        continue;

                    $this->namespaceRelationships[] = $namespaceRelationships;
                }


            if($data->uniqueAttributes)
                foreach ($data->uniqueAttributes as $uniqueAttributes)
                    $this->uniqueAttributes[] = $uniqueAttributes;

            /** Getter and Setter */
            $makeGetterSetter = new MakeGetterSetter(
                twigHelper: $this->twigHelper,
                attribute: $foreignKey,
                typeAttribute: 'foreignKey'
            );
            $this->gettersAndSetters[] = $makeGetterSetter->make();

        }


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
        return $this->twigHelper->getTwig()->render($this->template,[
            'baseNamespace' => $this->baseNamespace,
            'className' => $this->class->className,
            'tableName' => $this->class->tableName,
            'description' => $this->class->description,
            'construct' => $this->construct,
            'attributes' => $this->attributes,
            'gettersAndSetters' => $this->gettersAndSetters,
            'uniqueAttributes' => $this->uniqueAttributes,
            'packageName' => $this->packageName,
            'namespaceRelationships' => array_values(array_unique( $this->namespaceRelationships )),
        ]);
    }
}