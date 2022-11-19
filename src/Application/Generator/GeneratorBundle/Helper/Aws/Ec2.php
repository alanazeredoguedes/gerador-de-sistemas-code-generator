<?php

namespace App\Application\Generator\GeneratorBundle\Helper\Aws;

use AWS\CRT\Auth\AwsCredentials;
use \Aws\Ec2\Ec2Client as Ec2Client;

class Ec2
{
    protected Ec2Client $client;

    public function __construct(
        protected array $credentials,
    )
    {
        //$this->credentials = new AwsCredentials('REDACTED_AWS_KEY', 'REDACTED_AWS_SECRET');

        $this->client = new Ec2Client([
            'region' => 'us-east-1',
            'version' => '2016-11-15',
            //'profile' => 'default',
            'credentials' => $credentials,
        ]);
    }

    public function runInstances(){

        $result = $this->client->runInstances([
            'ImageId' => 'ami-04deaeb8bac10454e',
            'InstanceCount' => 1,
            'MaxCount' => 1,
            'MinCount' => 1,
            'KeyName' => 'gds-ssh',
            'InstanceType' => 't2.micro',
            'TagSpecifications' => [
                [
                    'ResourceType' => 'instance',
                    'Tags' => [
                        [
                            'Key' => 'Name',
                            'Value' => 'generate-instance',
                        ],
                    ],
                ],
            ],
            'NetworkInterfaces' => [
                [
                    'AssociatePublicIpAddress' => true,
                    'DeviceIndex' => 0,
                    'SubnetId' => 'subnet-0f3f994051eeda7d8',
                    'Groups' =>[ 'sg-0a65506826f5f7614' ],
                ],
            ],
            //'UserData' => '',
        ]);


        dd($result);

    }

    public function describeInstances($id){

       $result = $this->client->describeInstances([
           'InstanceIds' => ['i-05e6850dc7e4d943f'],
           /*'Filters' => [
               [
                   'Name' => '',
                   'Values' => ['',''],
               ],
           ],*/
        ]);

       dd($result);

    }


}