<?php

namespace App\Application\Generator\GeneratorBundle\AwsHelper;

use App\Application\Generator\GeneratorBundle\AwsHelper\Aws\CodeCommit;
use App\Application\Generator\GeneratorBundle\AwsHelper\Aws\Ec2;
use App\Application\Generator\GeneratorBundle\AwsHelper\Aws\Sns;
use App\Application\Generator\GeneratorBundle\AwsHelper\Aws\Sqs;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class AwsHelper
{
    public Ec2 $ec2;
    public CodeCommit $codeCommit;
    public Sns $sns;
    public Sqs $sqs;

    public function __construct(
        protected ContainerBagInterface $containerInterface,
    )
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


    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getCredentials(): array
    {
        return [
            'key' => $this->containerInterface->get('aws.key') ?
                $this->containerInterface->get('aws.key') : '',
            'secret' => $this->containerInterface->get('aws.secret') ?
                $this->containerInterface->get('aws.secret') : '',
        ];
    }









}