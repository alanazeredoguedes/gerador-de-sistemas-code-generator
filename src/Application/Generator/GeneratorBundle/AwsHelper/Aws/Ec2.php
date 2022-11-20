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

        /** Faz consulta para saber se instancia existe */
        $instance = $this->getInstanceByTagName($projectNameBuild);

        $elastictIp = false;

        /** Caso exista instancia, dessasocia ip elastico e remove instancia! */
        if($instance){
            //dd($instance['PublicIpAddress']);

            $elastictIp = $this->findElastiIpByPublicIp($instance['PublicIpAddress']);

            $this->dissociateElasticIpFromEc2($elastictIp['AssociationId']);

            $this->terminateInstance($instance['InstanceId']);

           // dd($instance);
        }

        if(!$elastictIp)
            $elastictIp = $this->AllocateElasticIpAddress();


        $elasticIpId = $elastictIp['AllocationId'];
        $publicIp = $elastictIp['PublicIp'];


        /** Cria uma nova Instancia EC2 */
        $result = $this->runInstances($projectNameBuild);
        $instanceId = $result['Instances'][0]['InstanceId'];


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

        //dd($result, $elasticIpId);

       $result = $this->associateElasticIpAddressInInstance(elasticIpId: $elasticIpId, instanceId: $instanceId);

       return $publicIp;
       //dd($result, $publicIp);
    }


    /** Aloca um novo ip elastico */
    protected function allocateElasticIpAddress(): AwsResult
    {
        return $this->client->allocateAddress([]);
    }

    /** Busca ip elastico pelo publicIP */
    protected function findElastiIpByPublicIp($publicIp)
    {
        $data = $this->client->describeAddresses([
            'PublicIps' => [ $publicIp ],
        ]);

        return $data['Addresses'][0];
    }

    /** Dessasocia ip elastico de uma ec2 */
    protected function dissociateElasticIpFromEc2($associationId): void
    {
        $this->client->disassociateAddress([
            'AssociationId' => $associationId,
        ]);
    }




    /** Associa ip elastico a uma instancia ec2 */
    protected function associateElasticIpAddressInInstance(string $elasticIpId, string $instanceId): AwsResult
    {
        return $this->client->associateAddress([
            'AllocationId' => $elasticIpId,
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

    public function terminateInstance($instanceId)
    {
        $result = $this->client->terminateInstances([
            'InstanceIds' => [$instanceId], // REQUIRED
        ]);
        //dd($result);
    }

    public function describeInstances($instanceId = ''): AwsResult
    {
        if( $instanceId )
            return $this->client->describeInstances([
               'InstanceIds' => [ $instanceId ],
            ]);

        return $this->client->describeInstances([]);
    }

    public function getInstanceByTagName($name)
    {
        $instances = $this->client->describeInstances([]);

        foreach ($instances['Reservations'] as $instance) {
            $instance = $instance['Instances'][0];

            /** Se instancia não estiver rodando continua */
            if($instance['State']['Code'] !== 16) // running
                continue;

            /** Retorna instacia com name igual */
            foreach ($instance['Tags'] as $tag)
               if ( $tag['Key'] === 'Name' && $tag['Value'] === $name)
                   return $instance;
        }

        return null;
    }







}