<?php

namespace App\Application\Generator\GeneratorBundle\Helper;

class ValidateDiagram
{
    public mixed $classValidate = [];
    public mixed $relationshipsValidate;


    public function __construct(
        protected mixed $class,
        protected mixed $relationships,
    ){  }


    /**
     * Percorre toda estrutura e validas os dados enviados pelo cliente
     *
     * @return object
     */
    public function checkIntegrity(): object
    {
        /**
         * Requisitos necessários para criação do projeto
         *
         * --------- Classe ---------
         * Campos Obrigatórios - [ className ]
         * O nome da classe deve ser unico, não podendo ter classes com nomes iguais
         * Caso exista o nome da tabela, ele deve ser unico, não podendo ter tabelas com nomes iguais
         * A classe deve ter obrigatoriamente um atributo definido como a chave primaria
         *
         *
         * --------- Atributos ---------
         * Campos Obrigatórios - [ fieldName, type ]
         *
         *
         *
         *
         *
         * --------- Métodos ---------
         * Campos Obrigatórios - [ methodName ]
         *
         */


        return (object) [
            'status' => true,
            'errors' => [],
        ];
    }


    /** Converte os dados recebido e monta estrutura de criação. */
    public function transformData(): void
    {
        //dd($this->class);


        $defaultClass = $this->getDefaultClass();
        $associativeClass = $this->getAssociativeClass();
        $systemClass = $this->getSystemClass();


        foreach ($defaultClass as $class)
        {

            $attributes = $this->transformAttributes($class->attributes, $class);

            $this->classValidate[] = (object) [
                'className'     => $this->filterClassName($class->className),
                'tableName'     => $this->filterTableName($class->tableName),
                'description'   => $this->filterClassDescription($class->description),
                'attributes'    => $attributes,
                'allAttributes' => array_merge([$attributes->primaryKey], $attributes->default, $attributes->foreignKey),
                'methods'       => $this->transformMethods($class->methods),
            ];
        }

        //dd($this->class, $this->classValidate);

        //dd($this->classValidate);
    }

    protected function transformMethods($methods): array
    {
        $methodsFilter = [];

        foreach ($methods as $method)
        {
            $methodsFilter[] = (object)[
                'name' => $method->name,
                'description' => $method->description
            ];
        }

        return $methodsFilter;
    }


    protected function transformAttributes($attributes, $class): object
    {
        $attributesFilter = (object) [
            'primaryKey'     => null,
            'foreignKey'     => [],
            'default'        => [],
        ];

        foreach ($attributes as $attribute)
        {

            if($attribute->primaryKey){
                /** Transforma o atributo chave primaria */
                $attributesFilter->primaryKey = $this->transformAttributePrimaryKey($attribute);

            }else if($attribute->foreingKey){
                /** Transforma os atributos chaves estrangeiras */
                if($this->transformAttributeForeingKey($attribute, $class))
                    $attributesFilter->foreignKey[] = $this->transformAttributeForeingKey($attribute, $class);

            }else{
                /** Transforma os atributos normais */
                $attribute = $this->transformAttributeDefault($attribute);
                if($attribute)
                    $attributesFilter->default[] = $attribute;
            }

        }

        return $attributesFilter;
    }

    protected function transformAttributePrimaryKey($attribute): object
    {
        return (object) [
            'attributeName' => $attribute->attributeName,
            'typeDoctrine' => $attribute->type,
            'typePhp' => $this->getPhpType($attribute->type),
            'typeApi' => $this->getTypeApi($attribute->type),
            'sonataType' => $this->getSonataType($attribute->type),
            'autoGenerate' => $attribute->autoGenerate,
            'precision' => $this->retunrNullOrFloat($attribute->precision),
            'scale' => $this->retunrNullOrFloat($attribute->scale),
            'lengthMax' => $attribute->lengthMax,
            'lengthMin' => $attribute->lengthMin,
        ];
    }

    protected function transformAttributeForeingKey($attribute, $class)
    {
        $relationship = $this->getRelationshipByForeingKey($attribute->key);

        $fromClass = $this->getClassByKey($relationship->from);


       // dd($class, $attribute, $relationship);

        $owningSideClass = $owningSideAttributeName = $owningSideprimaryKey = $owningSideprimaryKeyTypeApi = $inverseSideprimaryKeyTypeApi =  $inverseSideClass = $inverseSideAttributeName = $inverseSideprimaryKey = '';
        $owningSideAllAttributes = $inverseSideAllAttributes = $typeApi = '';
        $owningSideAttributesSearch = $inverseSideAttributesSearch = [];
        $multiple = $tableName = $owningSideForeingKey = $inverseSideForeingKey= false;




        /** Pega as informações do lado Proprietario — owningSide */
        if($relationship->typeRelationship === "one-to-one" || $relationship->typeRelationship === "one-to-many" ){

            $owningSideClass = $this->getClassByKey($relationship->to)->className;
            $owningSideAttribute = $this->getAttributeInClass($relationship->attributeOwningSide, $relationship->to);
            $owningSideAttributeName = ($owningSideAttribute) ? $owningSideAttribute->attributeName : '';
            $owningSideprimaryKey = $this->getPrimaryKeyInClass($relationship->to);
            $owningSideprimaryKey = ($owningSideprimaryKey) ? $owningSideprimaryKey->attributeName : '';

            //$owningSideprimaryKeyTypeApi = ($owningSideprimaryKey) ? $this->getTypeApi($owningSideprimaryKey->type) : '';
            $owningSideAllAttributes = $this->getAllAttributesOfClass($relationship->to);
            $owningSideAttributesSearch = $this->getAllAttributesSearch($relationship->to);

            $inverseSideClass = $this->getClassByKey($relationship->from)->className;
            $inverseSideAttribute = $this->getAttributeInClass($relationship->attributeinverseSide, $relationship->from);
            $inverseSideAttributeName = ($inverseSideAttribute) ? $inverseSideAttribute->attributeName : '';
            $inverseSideprimaryKey = $this->getPrimaryKeyInClass($relationship->from);
            $inverseSideprimaryKey = ($inverseSideprimaryKey) ? $inverseSideprimaryKey->attributeName : '';
            //$inverseSideprimaryKeyTypeApi  = ($inverseSideprimaryKey) ? $this->getTypeApi($inverseSideprimaryKey->type) : '';
            $inverseSideAllAttributes = $this->getAllAttributesOfClass($relationship->from);
            $inverseSideAttributesSearch = $this->getAllAttributesSearch($relationship->from);

            $typeApi = $this->getPrimaryKeyInClass($relationship->from)->type;

        }else if($relationship->typeRelationship === "many-to-many"){

            $relationship = $this->getRelationshipByForeingKey($attribute->key);
            $tableAssociative = $this->getClassByKey($relationship->to);

            $multiple = true;
            $tableName = $tableAssociative->tableName;

            if($attribute->typeForeingKey === "inverseSide"){

                $inverseSideClass = $this->getClassByKey($relationship->from)->className;
                $inverseSideAttributeName = $this->getAttributeInClass($relationship->attributeOwningSide, $relationship->from)->attributeName;
                $inverseSideprimaryKey = ( $this->getPrimaryKeyInClass($relationship->from) ) ? $this->getPrimaryKeyInClass($relationship->from)->attributeName : '';
                $inverseSideprimaryKeyTypeApi = ( $this->getPrimaryKeyInClass($relationship->from) ) ?  $this->getTypeApi( $this->getPrimaryKeyInClass($relationship->from)->type ) : '';
                $inverseSideForeingKey = $this->getAttributeInClass($relationship->attributeinverseSide, $relationship->to)->fieldName;
                $inverseSideAllAttributes = $this->getAllAttributesOfClass($relationship->from);
                $inverseSideAttributesSearch = $this->getAllAttributesSearch($relationship->from);


                $attr1 = $tableAssociative->attributes[0];
                $attr2 = $tableAssociative->attributes[1];
                $owningSideForeingKey = ($inverseSideForeingKey === $attr1->fieldName) ? $attr2 : $attr1;
                $owningSideRelationship = $this->getRelationshipByForeingKey($owningSideForeingKey->key);


                $owningSideClass = $this->getClassByKey($owningSideRelationship->from)->className;
                $owningSideprimaryKey = ( $this->getPrimaryKeyInClass($owningSideRelationship->from) ) ? $this->getPrimaryKeyInClass($owningSideRelationship->from)->attributeName : '';
                $owningSideprimaryKeyTypeApi = ( $this->getPrimaryKeyInClass($owningSideRelationship->from) ) ? $this->getTypeApi( $this->getPrimaryKeyInClass($owningSideRelationship->from)->type ) : '';
                $owningSideForeingKey = $owningSideForeingKey->fieldName;
                $owningSideAllAttributes = $this->getAllAttributesOfClass($owningSideRelationship->from);
                $owningSideAttributesSearch = $this->getAllAttributesSearch($owningSideRelationship->from);
                if($relationship->typeAssociation !== "self-referencing"){
                    $owningSideAttributeName = $this->getAttributeInClass($owningSideRelationship->attributeOwningSide, $owningSideRelationship->from);
                    $owningSideAttributeName = ($owningSideAttributeName) ? $owningSideAttributeName->attributeName : '';
                }

                $typeApi = 'object';

            }else if ( $attribute->typeForeingKey === "owningSide" ){

                $owningSideClass = $this->getClassByKey($relationship->from)->className;
                $owningSideAttributeName = $this->getAttributeInClass($relationship->attributeOwningSide, $relationship->from)->attributeName;
                $owningSideprimaryKey = ( $this->getPrimaryKeyInClass($relationship->from) ) ? $this->getPrimaryKeyInClass($relationship->from)->attributeName : '';
                $owningSideprimaryKeyTypeApi = ( $this->getPrimaryKeyInClass($relationship->from) ) ? $this->getTypeApi( $this->getPrimaryKeyInClass($relationship->from)->type ) : '';
                $owningSideForeingKey = $this->getAttributeInClass($relationship->attributeinverseSide, $relationship->to)->fieldName;
                $owningSideAllAttributes = $this->getAllAttributesOfClass($relationship->from);
                $owningSideAttributesSearch = $this->getAllAttributesSearch($relationship->from);


                $attr1 = $tableAssociative->attributes[0];
                $attr2 = $tableAssociative->attributes[1];
                $inverseSideForeingKey = ($owningSideForeingKey === $attr1->fieldName) ? $attr2 : $attr1;
                $inverseSideRelationship = $this->getRelationshipByForeingKey($inverseSideForeingKey->key);

                $inverseSideClass = $this->getClassByKey($inverseSideRelationship->from)->className;;
                $inverseSideprimaryKey = ( $this->getPrimaryKeyInClass($inverseSideRelationship->from) ) ? $this->getPrimaryKeyInClass($inverseSideRelationship->from)->attributeName : '';
                $inverseSideprimaryKeyTypeApi = ( $this->getPrimaryKeyInClass($inverseSideRelationship->from) ) ? $this->getTypeApi( $this->getPrimaryKeyInClass($inverseSideRelationship->from)->type ) : '';
                $inverseSideForeingKey = $inverseSideForeingKey->fieldName;
                $inverseSideAllAttributes = $this->getAllAttributesOfClass($inverseSideRelationship->from);
                $inverseSideAttributesSearch = $this->getAllAttributesSearch($inverseSideRelationship->from);

                //$typeApi = $this->getPrimaryKeyInClass($inverseSideRelationship->from)->type;
                $typeApi = 'object';

                if($relationship->typeAssociation !== "self-referencing"){
                    $inverseSideAttributeName = $this->getAttributeInClass($inverseSideRelationship->attributeOwningSide, $inverseSideRelationship->from);
                    $inverseSideAttributeName = ($inverseSideAttributeName) ? $inverseSideAttributeName->attributeName : '';
                }


            }


        }




        /** Pega as informações do lado Inverso - inverseSide */

        $data = (object) [
            'className'=> $this->filterClassName($class->className),
            'classPrimaryKey'=> $this->getPrimaryKeyInClass($class->key)->attributeName,

            'typeForeingKey' => $attribute->typeForeingKey, // [ inverseSide, owningSide ]
            'typeAssociation' => $relationship->typeAssociation,// [ bidirectional, unidirectional, self-referencing ]
            'typeRelationship' => $relationship->typeRelationship, // [ one-to-one, one-to-many, many-to-many ]
            'tableName' => $tableName,
            'multiple' => $multiple,
            'attributeName'=> $attribute->attributeName,
            'nullable' => $attribute->nullable,
            'unique' => $attribute->unique,

            'sonataType' => (object) [
                'type' => 'ModelAutocompleteType',
                'namespace' => 'Sonata\AdminBundle\Form\Type\ModelAutocompleteType',
            ],
            'typeApi' => $this->getTypeApi($typeApi),
            'owningSide' => (object) [
                'className'=> $owningSideClass,
                'attributeName'=> $owningSideAttributeName,
                'primaryKey'=> $owningSideprimaryKey,
                'primaryKeyTypeApi'=> $owningSideprimaryKeyTypeApi,
                'foreingKey'=> $owningSideForeingKey,
                'allAttributes' => $owningSideAllAttributes,
                'attributeSearch' => $owningSideAttributesSearch,
                ],

            'inverseSide' => (object) [
                'className'=> $inverseSideClass,
                'attributeName'=> $inverseSideAttributeName,
                'primaryKey'=> $inverseSideprimaryKey,
                'primaryKeyTypeApi'=> $inverseSideprimaryKeyTypeApi,
                'foreingKey'=> $inverseSideForeingKey,
                'allAttributes' => $inverseSideAllAttributes,
                'attributeSearch' => $inverseSideAttributesSearch,
            ],
        ];


        if($fromClass->systemModel){

            if($data->inverseSide->className === "Media")
                $data->inverseSide->className = "SonataMediaMedia";

            if($data->inverseSide->className === "Galeria")
                $data->inverseSide->className = "SonataMediaGallery";

            //dd($attribute, $data);
            //return false;
        }
        //dd($attribute, $relationship,  $tableAssociative, $data);

        return $data;
    }

    protected function transformAttributeDefault($attribute): object|bool
    {
        if($attribute->attributeName == "" || $attribute->attributeName == null)
            return false;

        if($attribute->type == "" || $attribute->type == null)
            return false;

        $attribute->type = $this->getDoctrineType($attribute->type);

        return (object) [
            'attributeName' => $attribute->attributeName,
            'typeDoctrine' => $attribute->type,
            'typePhp' => $this->getPhpType($attribute->type),
            'sonataType' => $this->getSonataType($attribute->type),
            'typeApi' => $this->getTypeApi($attribute->type),
            'Symfony' => '',
            'nullable' => $attribute->nullable,
            'unique' => $attribute->unique,
            'precision' => $this->retunrNullOrFloat($attribute->precision),
            'scale' => $this->retunrNullOrFloat($attribute->scale),
            'lengthMax' => $this->retunrNullOrInt($attribute->lengthMax),
            'lengthMin' => $this->retunrNullOrInt($attribute->lengthMin),
        ];
    }

    protected function retunrNullOrInt($data): ?int
    {
       return ( $data === "" || is_null($data) || $data === false  ) ? null : (int) $data;
    }

    protected function retunrNullOrFloat($data): ?float
    {
        return ( $data === "" || is_null($data) || $data === false ) ? null : (float) $data;
    }







    /**
     * ######################################################################################################
     * Métodos de Filtragem de Dados
     */


    /** Traz Todas as classes criadas pelo usuário */
    protected function getDefaultClass()
    {
        return array_filter($this->class, function ($class){
            return ($class->systemModel !== true) && ($class->associativeModel !== true);
        });

    }

    /** Traz Todas as classes criadas padrões do sistema */
    protected function getSystemClass()
    {
        return array_filter($this->class, function ($class){
            return $class->systemModel === true;
        });

    }

    /** Traz Todas as classes/tabelas associativas criadas pelo usuário */
    protected function getAssociativeClass()
    {
        return array_filter($this->class, function ($class){
            return ($class->systemModel !== true) && ($class->associativeModel === true);
        });
    }

    protected function getPrimaryKeyInClass(string $classKey)
    {
        $data = false;
        foreach ($this->class as $class)
            if($class->key === $classKey)
                foreach ($class->attributes as $attribute)
                    if($attribute->primaryKey)
                        $data = $attribute;

        return $data;
    }

    protected function getAttributeInClass($attributeKey, $classKey)
    {
        if(!$attributeKey || !$classKey)
            return false;

        $data = false;
        foreach ($this->class as $class)
            if($class->key === $classKey)
                foreach ($class->attributes as $attribute)
                    if($attribute->key === $attributeKey)
                        $data = $attribute;

        //dd($data);
        return $data;
    }

    protected function getAllAttributesOfClass(string $classkey)
    {
        $data = [ ];
        foreach ($this->class as $class)
            if($class->key === $classkey)
                foreach ($class->attributes as $attribute)
                    if(!$attribute->foreingKey)
                        $data[] = $attribute->attributeName;

        return $data;
    }

    protected function getAllAttributesSearch(string $classkey)
    {
        $data = [ ];
        foreach ($this->class as $class)
            if($class->key === $classkey)
                foreach ($class->attributes as $attribute)
                    if (isset($attribute->attributeSearch) && $attribute->attributeSearch )
                        $data[] = $attribute->attributeName;

        return $data;
    }



    protected function getClassByKey(string $key)
    {
        $data = array_values( array_filter($this->class, function ($class) use ($key) {
            return ($class->key === $key);
        }) );

        return ( isset($data[0]) )? $data[0]: null;
    }

    protected function getRelationshipByForeingKey(string $keyForeingKey)
    {
        $data = array_values( array_filter($this->relationships, function ($relationship) use ($keyForeingKey) {
            if( isset($relationship->attributeOwningSide) && ($relationship->attributeOwningSide === $keyForeingKey) )
                return $relationship;

            if( isset($relationship->attributeinverseSide) && ($relationship->attributeinverseSide === $keyForeingKey) )
                return $relationship;

            return false;
        }) );

        return ( isset($data[0]) )? $data[0]: null;
    }




    /**
     * ######################################################################################################
     * Metódos de Verificação de Dominio
     */

    protected function existClass()
    {

    }


    protected function existPkInClass()
    {

    }



    /**
     * ######################################################################################################
     * Métodos de Filtragem de ‘string’
     */

    protected function filterClassName(string $className): string
    {
        return ucfirst( $className );
    }

    protected function filterTableName(string $tableName): string
    {
        return strtolower( $tableName);
    }

    protected function filterClassDescription(string $description): string
    {
        return trim( $description );
    }


    /**
     * ######################################################################################################
     * Métodos converção de dados do projeto
     */




    protected function getDoctrineType(string $type): string
    {
        return match ($type) {
            'smallint' => 'smallint',
            'integer' => 'integer',
            'bigint' => 'bigint',
            'decimal' => 'decimal',
            'string' => 'string',
            'text' => 'text',
            'float' => 'float',
            'date' => 'date',
            'datetime' => 'datetime',
            'datetimetz' => 'datetimetz',
            'time' => 'time',
            'json' => 'json',
            'array' => 'array',
            'simple_array' => 'simple_array',
            'object' => 'object',
            'boolean' => 'boolean',
            //'guid' => 'guid',
            //'binary' => 'binary',
            //'blob' => 'blob',
            default => 'string',
        };
    }

    protected function getPhpType(string $type): bool|string
    {
        return match ($type) {
            'smallint', 'integer' => 'int',
            'bigint', 'decimal', 'string', 'text', => 'string',
            'float' => 'float',
            'boolean' => 'bool',
            'date', 'datetime', 'datetimetz', 'time', => 'DateTime',
            'json', => 'mixed',
            'array', 'simple_array', => 'array',
            'object' => 'object',
            //'guid' => 'string',
            //'binary' => 'resource',
            //'blob' => 'resource',
            default => false,
        };
    }

    protected function getTypeApi(string $type): string
    {
        return match ($type) {
            'smallint', 'integer' => 'integer',
            'float', 'bigint', 'decimal' => 'number',
            'boolean' => 'boolean',
            'array', 'simple_array', 'json',
            'date', 'datetime', 'datetimetz', 'time',
            'text', 'string', => 'string',
            'object' => 'array',
            default => 'string',
        };
    }

    protected function getSonataType(string $type): object
    {
        return match ($type) {
            'smallint', 'integer', 'bigint' => (object) [
                'type' => 'IntegerType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            ],
            'decimal', 'float' => (object) [
                'type' => 'NumberType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            ],
            'boolean' => (object) [
                'type' => 'CheckboxType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
            ],
            'text', 'json', 'array', 'simple_array', 'object'  => (object) [
                'type' => 'TextareaType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
            ],
            'date' => (object) [
                'type' => 'DateType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\DateType',
            ],
            'datetime', 'datetimetz' => (object) [
                'type' => 'DateTimeType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            ],
            'time' => (object) [
                'type' => 'TimeType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
            ],
            //'guid' => 'guid',
            //'binary' => 'binary',
            //'blob' => 'blob',
            default => (object) [
                'type' => 'TextType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            ],
        };
    }

    protected function getSymfonyType($type): string
    {

    }






    protected function allSonataTypes()
    {
        $types = [
            'AclMatrixType' => (object) [
                'type' => 'AclMatrixType',
                'namespace' => 'Sonata\AdminBundle\Form\Type\AclMatrixType',
            ],
            'AdminType' => (object) [
                'type' => 'AdminType',
                'namespace' => 'Sonata\AdminBundle\Form\Type\AdminType',
            ],
            'ChoiceFieldMaskType' => (object) [
                'type' => 'ChoiceFieldMaskType',
                'namespace' => 'Sonata\AdminBundle\Form\Type\ChoiceFieldMaskType',
            ],
            'CollectionType' => (object) [
                'type' => 'CollectionType',
                'namespace' => 'Sonata\AdminBundle\Form\Type\CollectionType',
            ],
            'ModelAutocompleteType' => (object) [
                'type' => 'ModelAutocompleteType',
                'namespace' => 'Sonata\AdminBundle\Form\Type\ModelAutocompleteType',
            ],
            'ModelHiddenType' => (object) [
                'type' => 'ModelHiddenType',
                'namespace' => 'Sonata\AdminBundle\Form\Type\ModelHiddenType',
            ],
            'ModelListType' => (object) [
                'type' => 'ModelListType',
                'namespace' => 'Sonata\AdminBundle\Form\Type\ModelListType',
            ],
            'ModelReferenceType' => (object) [
                'type' => 'ModelReferenceType',
                'namespace' => 'Sonata\AdminBundle\Form\Type\ModelReferenceType',
            ],
            'ModelType' => (object) [
                'type' => 'ModelType',
                'namespace' => 'Sonata\AdminBundle\Form\Type\ModelType',
            ],
            'TemplateType' => (object) [
                'type' => 'TemplateType',
                'namespace' => 'Sonata\AdminBundle\Form\Type\TemplateType',
            ],
        ];
    }

    protected function allSymfonyTypes()
    {
        $types = [
            'BaseType' => (object) [
                'type' => 'BaseType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\BaseType',
            ],
            'BirthdayType' => (object) [
                'type' => 'BirthdayType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\BirthdayType',
            ],
            'ButtonType' => (object) [
                'type' => 'ButtonType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\ButtonType',
            ],
            'CheckboxType' => (object) [
                'type' => 'CheckboxType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\CheckboxType',
            ],
            'ChoiceType' => (object) [
                'type' => 'ChoiceType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
            ],
            'CollectionType' => (object) [
                'type' => 'CollectionType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\CollectionType',
            ],
            'ColorType' => (object) [
                'type' => 'ColorType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\ColorType',
            ],
            'CountryType' => (object) [
                'type' => 'CountryType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\CountryType',
            ],
            'CurrencyType' => (object) [
                'type' => 'CurrencyType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\CurrencyType',
            ],
            'DateIntervalType' => (object) [
                'type' => 'DateIntervalType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\DateIntervalType',
            ],
            'DateTimeType' => (object) [
                'type' => 'DateTimeType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\DateTimeType',
            ],
            'DateType' => (object) [
                'type' => 'DateType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\DateType',
            ],
            'EmailType' => (object) [
                'type' => 'EmailType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
            ],
            'EnumType' => (object) [
                'type' => 'EnumType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\EnumType',
            ],
            'FileType' => (object) [
                'type' => 'FileType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\FileType',
            ],
            'FormType' => (object) [
                'type' => 'FormType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\FormType',
            ],
            'HiddenType' => (object) [
                'type' => 'HiddenType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\HiddenType',
            ],
            'IntegerType' => (object) [
                'type' => 'IntegerType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\IntegerType',
            ],
            'LanguageType' => (object) [
                'type' => 'LanguageType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\LanguageType',
            ],
            'LocaleType' => (object) [
                'type' => 'LocaleType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\LocaleType',
            ],
            'MoneyType' => (object) [
                'type' => 'MoneyType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\MoneyType',
            ],
            'NumberType' => (object) [
                'type' => 'NumberType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\NumberType',
            ],
            'PasswordType' => (object) [
                'type' => 'PasswordType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
            ],
            'PercentType' => (object) [
                'type' => 'PercentType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\PercentType',
            ],
            'RadioType' => (object) [
                'type' => 'RadioType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\RadioType',
            ],
            'RangeType' => (object) [
                'type' => 'RangeType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\RangeType',
            ],
            'RepeatedType' => (object) [
                'type' => 'RepeatedType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\RepeatedType',
            ],
            'ResetType' => (object) [
                'type' => 'ResetType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\ResetType',
            ],
            'SearchType' => (object) [
                'type' => 'SearchType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\SearchType',
            ],
            'SubmitType' => (object) [
                'type' => 'SubmitType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\SubmitType',
            ],
            'TelType' => (object) [
                'type' => 'TelType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\TelType',
            ],
            'TextareaType' => (object) [
                'type' => 'TextareaType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\TextareaType',
            ],
            'TextType' => (object) [
                'type' => 'TextType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
            ],
            'TimeType' => (object) [
                'type' => 'TimeType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\TimeType',
            ],
            'TimezoneType' => (object) [
                'type' => 'TimezoneType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\TimezoneType',
            ],
            'TransformationFailureExtension' => (object) [
                'type' => 'TransformationFailureExtension',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\TransformationFailureExtension',
            ],
            'UlidType' => (object) [
                'type' => 'UlidType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\UlidType',
            ],
            'UrlType' => (object) [
                'type' => 'UrlType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\UrlType',
            ],
            'UuidType' => (object) [
                'type' => 'UuidType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\UuidType',
            ],
            'WeekType' => (object) [
                'type' => 'WeekType',
                'namespace' => 'Symfony\Component\Form\Extension\Core\Type\WeekType',
            ],
        ];

    }

}