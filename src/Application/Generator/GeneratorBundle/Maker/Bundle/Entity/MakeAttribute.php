<?php

namespace App\Application\Generator\GeneratorBundle\Maker\Bundle\Entity;

use App\Application\Generator\GeneratorBundle\Helper\TwigHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeAttribute
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

    public function make(): object
    {
        //dd($this->attribute);

        /** #########################################################################################################
         * ########## Create Primary Key */
        if($this->typeAttribute === 'primaryKey'){

            /** Generate Primary Key */
            $template = $this->getTemplate('primary_key.php.twig', [
                'attribute' => $this->attribute
            ]);

            return (object)[
                'template' => $template,
                'constructor' => false,
                'nameSpaceClass' => false,
                'uniqueAttributes' => false,
            ];

        /** #########################################################################################################
         * ########## Create Foreign Key */
        }elseif ($this->typeAttribute === 'foreignKey') {

            //dd($this->attribute);
            $constructor = $nameSpaceClass = $uniqueAttributes = [];;

            /** ####################################
             * Create Relationships one-to-one */
            if($this->attribute->typeRelationship === "one-to-one"){

                $template = $this->getTemplate('relationships/one_to_one.php.twig', [
                    'attribute' => $this->attribute
                ]);

                if($this->attribute->typeAssociation == "unidirectional"){

                    $uniqueAttributes[] = $this->attribute->attributeName;
                    $nameSpaceClass[] = $this->attribute->inverseSide->className;

                }else if($this->attribute->typeAssociation == "self-referencing"){

                    $uniqueAttributes[] = $this->attribute->attributeName;

                }else if($this->attribute->typeAssociation == "bidirectional"){

                    if($this->attribute->typeForeingKey == "owningSide"){

                        $uniqueAttributes[] = $this->attribute->attributeName;
                        $nameSpaceClass[] = $this->attribute->inverseSide->className;

                    }else if($this->attribute->typeForeingKey == "inverseSide"){
                        $nameSpaceClass[] = $this->attribute->owningSide->className;

                    }
                }

                return (object)[
                    'template' => $template,
                    'constructor' => $constructor,
                    'namespaceRelationships' => $nameSpaceClass,
                    'uniqueAttributes' => $uniqueAttributes,
                ];

            /** ####################################
             * Create Relationships one-to-many */
            }else if( $this->attribute->typeRelationship === "one-to-many" ){

                $template = $this->getTemplate('relationships/one_to_many.php.twig', [
                    'attribute' => $this->attribute
                ]);


                if($this->attribute->typeAssociation == "unidirectional"){

                    $nameSpaceClass[] = $this->attribute->inverseSide->className;
                    if($this->attribute->unique)
                        $uniqueAttributes[] = $this->attribute->attributeName;

                }else if($this->attribute->typeAssociation == "self-referencing"){

                    $constructor[] = "list". ucfirst( $this->attribute->attributeName );
                    if($this->attribute->unique)
                        $uniqueAttributes[] = $this->attribute->attributeName;

                }else if($this->attribute->typeAssociation == "bidirectional"){

                    if($this->attribute->typeForeingKey == "owningSide"){
                        $nameSpaceClass[] = $this->attribute->inverseSide->className;
                        if($this->attribute->unique)
                            $uniqueAttributes[] = $this->attribute->attributeName;

                    }else if($this->attribute->typeForeingKey == "inverseSide"){
                        $constructor[] = $this->attribute->inverseSide->attributeName;
                        $nameSpaceClass[] = $this->attribute->owningSide->className;

                    }
                }


                return (object) [
                    'template' => $template,
                    'constructor' => $constructor,
                    'namespaceRelationships' => $nameSpaceClass,
                    'uniqueAttributes' => $uniqueAttributes,
                ];

            /** ####################################
             * Create Relationships one-to-many */
            }else if ( $this->attribute->typeRelationship === "many-to-many" )
            {

                //dd($this->attribute);


                $template = $this->getTemplate('relationships/many_to_many.php.twig', [
                    'attribute' => $this->attribute
                ]);


                if($this->attribute->typeAssociation == "unidirectional"){

                    $constructor[] = $this->attribute->owningSide->attributeName;
                    $nameSpaceClass[] = $this->attribute->inverseSide->className;
                    if($this->attribute->unique)
                        $uniqueAttributes[] = $this->attribute->attributeName;

                }else if($this->attribute->typeAssociation == "self-referencing"){

                    $constructor[] = "list". ucfirst( $this->attribute->attributeName );
                    $constructor[] = $this->attribute->attributeName;

                    if($this->attribute->unique)
                        $uniqueAttributes[] = $this->attribute->attributeName;

                }else if($this->attribute->typeAssociation == "bidirectional"){
                    if($this->attribute->typeForeingKey == "owningSide"){

                        $constructor[] = $this->attribute->owningSide->attributeName;
                        $nameSpaceClass[] = $this->attribute->inverseSide->className;

                        if($this->attribute->unique)
                            $uniqueAttributes[] = $this->attribute->attributeName;

                    }else if($this->attribute->typeForeingKey == "inverseSide"){

                        $constructor[] = $this->attribute->inverseSide->attributeName;
                        $nameSpaceClass[] = $this->attribute->owningSide->className;

                    }
                }



                return (object) [
                    'template' => $template,
                    'constructor' => $constructor,
                    'namespaceRelationships' => $nameSpaceClass,
                    'uniqueAttributes' => $uniqueAttributes,
                ];
            }

        /** #########################################################################################################
         * ########## Create Defaults Attributes */
        }elseif ($this->typeAttribute === 'default') {

            /** Generate Default Attribute */
            $template = $this->getTemplate('attribute.php.twig', [
                'attribute' => $this->attribute
            ]);

            return (object)[
                'template' => $template,
                'constructor' => false,
                'nameSpaceClass' => false,
                'uniqueAttributes' => false,
            ];

        }

        return (object)[
            'template' => '',
            'constructor' => false,
            'nameSpaceClass' => false,
            'uniqueAttributes' => false,
        ];
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