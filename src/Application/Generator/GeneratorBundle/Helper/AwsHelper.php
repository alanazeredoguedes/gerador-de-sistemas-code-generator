<?php

namespace App\Application\Generator\GeneratorBundle\Helper;

use App\Application\Generator\GeneratorBundle\Helper\Aws\Ec2;

class AwsHelper
{
    public Ec2 $ec2;

    public function __construct()
    {
        $this->ec2 = new Ec2($this->getCredentials());
    }

    protected function getCredentials(): array
    {
        return [
            'key' => 'REDACTED_AWS_KEY',
            'secret' => 'REDACTED_AWS_SECRET',
        ];
    }



}