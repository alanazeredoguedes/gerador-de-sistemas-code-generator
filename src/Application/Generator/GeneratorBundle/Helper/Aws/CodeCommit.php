<?php

namespace App\Application\Generator\GeneratorBundle\Helper\Aws;

use \Aws\Result as AwsResult;
use \Aws\CodeCommit\CodeCommitClient as CodeCommitClient;

class CodeCommit
{
    protected CodeCommitClient $client;

    public function __construct(
        protected array $credentials,
        protected string $projectDir,
    ){
        $this->client = new CodeCommitClient([
            'region' => 'us-east-1',
            'version' => '2016-11-15',
            //'profile' => 'default',
            'credentials' => $credentials,
        ]);
    }

    public function make(): void
    {

    }



}