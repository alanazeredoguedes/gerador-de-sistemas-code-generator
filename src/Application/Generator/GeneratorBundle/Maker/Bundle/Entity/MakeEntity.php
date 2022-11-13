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
    protected string $construct = '';
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

        /** Create Primary Key */
        $makeAttribute = new MakeAttribute(
            twigHelper: $this->twigHelper,
            attribute: $this->class->attributes->primaryKey,
            typeAttribute: 'primaryKey'
        );
        $this->attributes[] = $makeAttribute->make();
        $this->uniqueAttributes[] = $this->class->attributes->primaryKey->attributeName;

        /** Primary Key Getter and Setter */
        $makeGetterSetter = new MakeGetterSetter(
            twigHelper: $this->twigHelper,
            attribute: $this->class->attributes->primaryKey,
            typeAttribute: 'primaryKey'
        );
        $this->gettersAndSetters[] = $makeGetterSetter->make();



        /** Create Default Attributes */
        foreach ($this->class->attributes->default as $attribute){

            if($attribute->unique)
                $this->uniqueAttributes[] = $attribute->attributeName;

            /** Create Default Attributes */
            $makeAttribute = new MakeAttribute(
                twigHelper: $this->twigHelper,
                attribute: $attribute,
                typeAttribute: 'default'
            );
            $this->attributes[] = $makeAttribute->make();

            /** Default Attributes Getter and Setter */
            $makeGetterSetter = new MakeGetterSetter(
                twigHelper: $this->twigHelper,
                attribute: $attribute,
                typeAttribute: 'default'
            );
            $this->gettersAndSetters[] = $makeGetterSetter->make();

        }


        /** Create ForeignKey Key */
        foreach ($this->class->attributes->foreignKey as $foreignKey){

            if($foreignKey->typeForeingKey === "inverseSide"){

                if($this->class->className !== $foreignKey->owningSide->className)
                    $this->namespaceRelationships[] = $foreignKey->owningSide->className;

            }elseif($foreignKey->typeForeingKey === "owningSide"){

                if($foreignKey->unique || $foreignKey->typeRelationship === "one-to-one" ){
                    $this->uniqueAttributes[] = $foreignKey->attributeName;
                }

                if($this->class->className !== $foreignKey->inverseSide->className)
                    $this->namespaceRelationships[] = $foreignKey->inverseSide->className;

            }

            $makeAttribute = new MakeAttribute(
                twigHelper: $this->twigHelper,
                attribute: $foreignKey,
                typeAttribute: 'foreignKey'
            );
            $this->attributes[] = $makeAttribute->make();


            /** ForeignKey Attributes Getter and Setter */
            $makeGetterSetter = new MakeGetterSetter(
                twigHelper: $this->twigHelper,
                attribute: $foreignKey,
                typeAttribute: 'foreignKey'
            );
            $this->gettersAndSetters[] = $makeGetterSetter->make();


        }

       // dd($this->uniqueAttributes);

        $this->namespaceRelationships = array_values(array_unique($this->namespaceRelationships));
        //dd($this->attributes);


/*      $makeConstructor = new MakeConstructor();*/


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
            'namespaceRelationships' => $this->namespaceRelationships,
        ]);
    }
}