<?php

namespace App\Application\Generator\GeneratorBundle\AwsHelper\Aws;

use Aws\CodeCommit\CodeCommitClient;

class CodeCommit
{
    protected CodeCommitClient $client;

    public function __construct(
        protected array $credentials,
    ){
        /*$this->client = new CodeCommitClient([
            'region' => 'us-east-1',
            'version' => '2016-11-15',
            //'profile' => 'default',
            'credentials' => $credentials,
        ]);*/
    }









}
