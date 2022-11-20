<?php

namespace App\Application\Generator\GeneratorBundle\AwsHelper\Aws;

use Aws\Ec2\Ec2Client;
use Aws\Result as AwsResult;

class Ec2
{
    protected Ec2Client $client;
    protected string $scriptUserData;

    public function __construct(
        protected array $credentials,
    )
    {
        $this->client = new Ec2Client([
            'region' => 'us-east-1',
            'version' => '2016-11-15',
            'credentials' => $credentials,
        ]);
    }






    public function makeImage(string $projectNameBuild, string $scriptUserData = "")
    {

        $this->scriptUserData = $scriptUserData;


        /** Sobre uma instancia */
        $result = $this->runInstances($projectNameBuild);
        $instanceId = $result['Instances'][0]['InstanceId'];

        //dd($result, $instanceId);

        /** Consulta status da instancia a cada 20s durante 2m */
        $count = 0;
        while ($count !== 6){
            $count++;
            sleep(20);
            $result = $this->describeInstances($instanceId);
            $instanceStatus = $result['Reservations'][0]['Instances'][0]['State']['Name'];
            if($instanceStatus === "running")
                $count = 6;
        }

        $result =$this->AllocateElasticIpAddress();

        $elasticIpId = $result['AllocationId'];
        $publicIp = $result['PublicIp'];

        //dd($result, $elasticIpId);

       $result = $this->associateElasticIpAddressInInstance(elasticIp: $elasticIpId, instanceId: $instanceId);

       return $publicIp;
       //dd($result, $publicIp);
    }


    protected function allocateElasticIpAddress(): AwsResult
    {
        return $this->client->allocateAddress([]);
    }



    protected function associateElasticIpAddressInInstance(string $elasticIp, string $instanceId): AwsResult
    {
        return $this->client->associateAddress([
            'AllocationId' => $elasticIp,
            'InstanceId' => $instanceId,
            'AllowReassociation' => false,
        ]);
    }


    protected function runInstances($projectNameBuild): AwsResult
    {

        return $this->client->runInstances([
            'ImageId' => 'ami-0ba911ea4dee934d6',
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
                            'Key' => 'Group',
                            'Value' => 'generate-instance',
                        ],
                        [
                            'Key' => 'Name',
                            'Value' => "$projectNameBuild",
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
            'UserData' => base64_encode( $this->scriptUserData ) ,
        ]);
    }

    public function describeInstances($instanceId = ''): AwsResult
    {
        if( $instanceId )
            return $this->client->describeInstances([
               'InstanceIds' => [ $instanceId ],
            ]);

        return $this->client->describeInstances([]);
    }

    public function getInstanceByTagName()
    {
        $instances = $this->client->describeInstances([]);

        //dd($instances);
        foreach ($instances as $instance) {
            $instance = $instance['Instances'][0];
            foreach ($instance['Tags'] as $tag){

            }
        }






    }







}