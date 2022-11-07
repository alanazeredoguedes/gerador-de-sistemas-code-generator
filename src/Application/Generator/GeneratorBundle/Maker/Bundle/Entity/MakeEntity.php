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

    public function __construct(
        protected TwigHelper $twigHelper,
        protected string $bundleDirectory,
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

      foreach ($this->class->attributes as $attribute) {


          if($attribute->unique)
              $this->uniqueAttributes[] = ($attribute->fieldName) ?
                  str_replace(' ', '',  $attribute->fieldName) :
                  str_replace(' ', '',  $attribute->attributeName);


          $makeAttribute = new MakeAttribute(
              twigHelper: $this->twigHelper,
              attribute: $attribute,
          );

          $this->attributes[] = $makeAttribute->make();

      }



/*        $makeGetter = new MakeGetter();
        $makeSetter = new MakeSetter();

        $makeConstructor = new MakeConstructor();*/


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
            'description' => $this->class->description,
            'tableName' => $this->class->tableName,
            'attributes' => $this->attributes,
            'construct' => $this->construct,
            'gettersAndSetters' => $this->gettersAndSetters,
            'uniqueAttributes' => $this->uniqueAttributes,

        ]);
    }
}