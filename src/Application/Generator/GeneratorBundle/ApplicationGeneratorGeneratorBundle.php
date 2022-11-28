<?php  
         
namespace App\Application\Generator\GeneratorBundle;
                
use Symfony\Component\HttpKernel\Bundle\Bundle;
                
class ApplicationGeneratorGeneratorBundle extends Bundle
{
    /** {@inheritdoc} */
    public function getParent()
    {
        return 'ApplicationGeneratorGeneratorBundle';
    }
}