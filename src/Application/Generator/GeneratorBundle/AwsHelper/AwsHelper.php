<?php

namespace App\Application\Generator\GeneratorBundle\AwsHelper;

use App\Application\Generator\GeneratorBundle\AwsHelper\Aws\CodeCommit;
use App\Application\Generator\GeneratorBundle\AwsHelper\Aws\Ec2;
use App\Application\Generator\GeneratorBundle\AwsHelper\Aws\Sns;
use App\Application\Generator\GeneratorBundle\AwsHelper\Aws\Sqs;

class AwsHelper
{
    public Ec2 $ec2;
    public CodeCommit $codeCommit;
    public Sns $sns;
    public Sqs $sqs;

    public function __construct()
    {

        $this->ec2 = new Ec2(
            credentials: $this->getCredentials(),
        );

        $this->codeCommit = new CodeCommit(
            credentials: $this->getCredentials(),
        );

        $this->sns = new Sns(
            credentials: $this->getCredentials(),
        );

        $this->sqs = new Sqs(
            credentials: $this->getCredentials(),
        );

    }























    protected function getCredentials(): array
    {
        return [
            'key' => 'REDACTED_AWS_KEY',
            'secret' => 'No08qKH1ntfsXRO219qtEkUy/NNB8BhbT26af9Cm',
        ];
    }



}