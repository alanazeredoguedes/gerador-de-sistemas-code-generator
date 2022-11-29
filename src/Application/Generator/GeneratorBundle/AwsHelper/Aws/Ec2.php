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
            'region' => 'sa-east-1',
            'version' => '2016-11-15',
            'credentials' => $credentials,
        ]);
    }


    public function  makeImage(string $projectNameBuild, string $scriptUserData = "")
    {

        $this->scriptUserData = $scriptUserData;

        /** Faz consulta para saber se instancia existe */
        $instance = $this->getInstanceByTag("Name", $projectNameBuild);

        /** Caso exista instancia, dessasocia ip elastico e remove instancia! */
        if($instance){
            $this->terminateInstance($instance['InstanceId']);
        }

        /** Cria uma nova Instancia EC2 */
        $result = $this->runInstances($projectNameBuild);

        $instanceId = $result['Instances'][0]['InstanceId'];

        /** Consulta status da instancia a cada 20s durante 2m */
        $count = 0;
        while ($count !== 12)
        {
            $count++;
            sleep(10);
            $result = $this->describeInstances($instanceId);
            $instanceStatus = $result['Reservations'][0]['Instances'][0]['State']['Name'];
            if($instanceStatus === "running")
                $count = 12;
        }

        //dd($result);
        $publicIp = $result['Reservations'][0]['Instances'][0]['PublicIpAddress'];


        return $publicIp;
       //dd($result, $publicIp);
    }


    /** Aloca um novo ip elastico */
    public function allocateElasticIpAddress(): AwsResult
    {
        return $this->client->allocateAddress([
            'TagSpecifications' => [
                [
                    'ResourceType' => 'elastic-ip',
                    'Tags' => [
                        [
                            'Key' => 'Group',
                            'Value' => 'generate-instance',
                        ],
                    ],
                ],
            ], // End TagSpecifications
        ]);
    }

    /** Busca ip elastico pelo publicIP */
    public function findElastiIpByPublicIp($publicIp)
    {
        $data = $this->client->describeAddresses([
            'PublicIps' => [ $publicIp ],
        ]);

        return $data['Addresses'][0];
    }

    /** Dessasocia ip elastico de uma ec2 */
    public function dissociateElasticIpFromEc2($associationId): void
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
            'ImageId' => 'ami-07580b9affa35b187',
            'InstanceCount' => 1,
            'MaxCount' => 1,
            'MinCount' => 1,
            'KeyName' => 'gds-aws-ssh-sp',
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
                    'SubnetId' => 'subnet-09e138274553dc7e7',
                    'Groups' =>[ 'sg-0d08353931fbdbf77' ],
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

    public function getInstanceByTag(string $tagName, string $tagValue)
    {
        $instances = $this->client->describeInstances([]);

        foreach ($instances['Reservations'] as $instance) {
            $instance = $instance['Instances'][0];

            /** Se instancia não estiver rodando continua */
            if($instance['State']['Code'] !== 16) // running
                continue;

            /** Retorna instacia com name igual */
            foreach ($instance['Tags'] as $tag)
               if ( $tag['Key'] === $tagName && $tag['Value'] === $tagValue)
                   return $instance;
        }

        return null;
    }


    public function getInstancesByTag(string $tagName, string $tagValue): array
    {
        $instances = $this->client->describeInstances([]);

        $data = [];
        foreach ($instances['Reservations'] as $instance) {
            $instance = $instance['Instances'][0];

            /** Retorna instacia com name igual */
            foreach ($instance['Tags'] as $tag)
                if ( $tag['Key'] === $tagName && $tag['Value'] === $tagValue)
                    $data[] = $instance;
        }

        return $data;
    }


    public function deleteElasticIp(string|null $allocationId = null, string|null $publicIp = null)
    {
        $data = [];

        if($allocationId)
            $data['AllocationId'] = $allocationId;

        if($publicIp)
            $data['PublicIp'] = $publicIp;

        //dd($data);

        $result = $this->client->releaseAddress($data);
    }


    public function describeAddresses(){
        return $this->client->describeAddresses([]) ;
    }

    public function verificationStatus(array $instancesId)
    {
        $result = $this->client->describeInstanceStatus([
            'InstanceIds' => $instancesId,
        ]);

        return $result;
    }




}