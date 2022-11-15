<?php

namespace App\Application\Generator\GeneratorBundle\Maker;

use App\Application\Generator\GeneratorBundle\Helper\StringHelper;
use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;

class MakeBaseConfigurationClass
{
    protected string $bundleName;
    protected string $bundleDirectory;
    protected string $baseNamespace;
    protected array $allAttributes = [];

    protected array $nameSpaceRelationships = [];

    public function __construct(
        protected StringHelper $stringHelper,
        protected string $packageName,
        protected string $projectDirectory,
        protected object $class,

    )
    {

    }




    public function getAllConfiguration(): object
    {
        $this->createBasePaths();

        $this->aux();
        $this->getAllAttributes();

       return (object) [
           'bundleName' => $this->bundleName,
           'bundleDirectory' => $this->bundleDirectory,
           'baseNamespace' => $this->baseNamespace,
           'packageName' => $this->packageName,
           'projectDirectory' => $this->projectDirectory,
           'nameSpaceRelationships' => array_values(array_unique( $this->nameSpaceRelationships )),
           'allAttributes' => $this->allAttributes,
       ];


    }


    protected function createBasePaths(): void
    {
        $this->bundleName = $this->stringHelper->createBundleName($this->class->className);
        $this->bundleDirectory = $this->projectDirectory . "/src/Application/" . $this->packageName . "/" . $this->bundleName;
        $this->baseNamespace = "App\Application\\" . $this->packageName . "\\" . "$this->bundleName" ;
    }

    protected function getAllAttributes(): void
    {

       // dd($this->class);
        foreach ($this->class->attributes as $attribute){
            //dd($attribute);
            if(isset($attribute->attributeName)){
                $this->allAttributes[] = $attribute->attributeName;

            }
        }

    }






    protected function aux(): void
    {
        foreach ( $this->class->attributes->foreignKey as $attributeFK ){
            if( $attributeFK->typeRelationship === "one-to-one" ){

                if($attributeFK->typeAssociation === "unidirectional")
                    $this->nameSpaceRelationships[] = $attributeFK->inverseSide->className;

                if($attributeFK->typeAssociation === "self-referencing"){}

                if($attributeFK->typeAssociation === "bidirectional"){

                    if($attributeFK->typeForeingKey === "owningSide")
                        $this->nameSpaceRelationships[] = $attributeFK->inverseSide->className;


                    if($attributeFK->typeForeingKey === "inverseSide")
                        $this->nameSpaceRelationships[] = $attributeFK->owningSide->className;

                }

            }

            if( $attributeFK->typeRelationship === "one-to-many" ){

                if($attributeFK->typeAssociation === "unidirectional")
                    $this->nameSpaceRelationships[] = $attributeFK->inverseSide->className;

                if($attributeFK->typeAssociation === "self-referencing"){}

                if($attributeFK->typeAssociation === "bidirectional"){

                    if($attributeFK->typeForeingKey === "owningSide")
                        $this->nameSpaceRelationships[] = $attributeFK->inverseSide->className;

                    if($attributeFK->typeForeingKey === "inverseSide")
                        $this->nameSpaceRelationships[] = $attributeFK->owningSide->className;

                }

            }

            if ( $attributeFK->typeRelationship === "many-to-many" ){

                if($attributeFK->typeAssociation === "unidirectional")
                    $this->nameSpaceRelationships[] = $attributeFK->inverseSide->className;

                if($attributeFK->typeAssociation === "self-referencing"){}

                if($attributeFK->typeAssociation === "bidirectional"){

                    if($attributeFK->typeForeingKey === "owningSide")
                        $this->nameSpaceRelationships[] = $attributeFK->inverseSide->className;

                    if($attributeFK->typeForeingKey === "inverseSide")
                        $this->nameSpaceRelationships[] = $attributeFK->owningSide->className;

                }

            }

        }


    }



}