<?php

namespace App\Application\Generator\GeneratorBundle\Helper;

use App\Application\Generator\GeneratorBundle\Helper\Aws\CodeCommit;
use App\Application\Generator\GeneratorBundle\Helper\Aws\Ec2;

class AwsHelper
{
    public Ec2 $ec2;
    public CodeCommit $codeCommit;

    public function __construct(
        protected string $projectDir,
    )
    {


        $this->ec2 = new Ec2(
            credentials: $this->getCredentials(),
            projectDir: $this->projectDir
        );

        $this->codeCommit = new CodeCommit(
            credentials: $this->getCredentials(),
            projectDir: $this->projectDir
        );


    }























    protected function getCredentials(): array
    {
        return [
            'key' => 'REDACTED_AWS_KEY',
            'secret' => 'REDACTED_AWS_SECRET',
        ];
    }



}