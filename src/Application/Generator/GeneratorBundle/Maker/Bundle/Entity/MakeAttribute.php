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

        $attributeName = str_replace(' ', '', $this->attribute->attributeName);
        $fieldName = str_replace(' ', '',  $this->attribute->fieldName);
        $type = $this->convertType($this->attribute->type);

        /** Generate Primary Key */
        if($this->attribute->primaryKey)
            return $this->getTemplate('primary_key.php.twig', [
                'attribute' => $this->attribute,
                'attributeName' => $attributeName,
                'fieldName' => $fieldName,
                'type' => $type
            ]);

        /** Generate Foreing Key Key */


        /** Generate Default Attribute */
        return $this->getTemplate('attribute.php.twig', [
            'attribute' => $this->attribute,
            'attributeName' => $attributeName,
            'fieldName' => $fieldName,
            'type' => $type
        ]);



        //return '';
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

    protected function convertType(string $type): bool|object
    {
        $phpType = match ($type) {
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

        if(!$phpType)
            return false;

        return (object) [
            'doctrine' => $type,
            'php' => $phpType,
        ];

      // $phpType = [ 'mixed', 'int', 'string', 'float', 'bool', 'array', 'object', '\DateTime'];
    }

}