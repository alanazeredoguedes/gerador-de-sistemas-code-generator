<?php

namespace App\Application\Generator\GeneratorBundle\Helper;

class ValidateDiagram
{
    public mixed $classValidate;
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
        $defaultClass = $this->getDefaultClass();
        $associativeClass = $this->getAssociativeClass();
        $systemClass = $this->getSystemClass();

        foreach ($defaultClass as $class)
        {
           // dd($class);

            $this->classValidate[] = (object) [
                'className'     => $this->filterClassName($class->className),
                'tableName'     => $this->filterTableName($class->tableName),
                'description'   => $this->filterClassDescription($class->description),
                'attributes'    => $this->transformAttributes($class->attributes),
                'methods'       => $this->transformMethods($class->methods),
            ];
        }

        //dd($this->classValidate);
    }

    protected function transformMethods($methods): array
    {
        $methodsFilter = [];

        foreach ($methods as $method)
        {
            $methodsFilter[] = (object)[
                'name' => '',
                'description' => ''
            ];
        }

        return $methodsFilter;
    }


    protected function transformAttributes($attributes): object
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
                $attributesFilter->foreignKey[] = $this->transformAttributeForeingKey($attribute);

            }else{
                /** Transforma os atributos normais */
                $attributesFilter->default[] = $this->transformAttributeDefault($attribute);
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
            'SonataType' => $this->getSonataType($attribute->type),
            'autoGenerate' => $attribute->autoGenerate,
            'precision' => $this->retunrNullOrFloat($attribute->precision),
            'scale' => $this->retunrNullOrFloat($attribute->scale),
            'lengthMax' => $attribute->lengthMax,
            'lengthMin' => $attribute->lengthMin,
        ];
    }

    protected function transformAttributeForeingKey($attribute): object
    {
        return (object) [
            'attributeName' => $attribute->attributeName,
        ];
    }

    protected function transformAttributeDefault($attribute): object
    {
        $attribute->type = $this->getDoctrineType($attribute->type);

        return (object) [
            'attributeName' => $attribute->attributeName,
            'typeDoctrine' => $attribute->type,
            'typePhp' => $this->getPhpType($attribute->type),
            'SonataType' => $this->getSonataType($attribute->type),
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

    protected function getClassByKey(string $key)
    {
        $data = array_values( array_filter($this->class, function ($class) use ($key) {
            return ($class->key === $key);
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

    protected function getSonataType(string $type): bool|string
    {

        return $type;
    }


}
