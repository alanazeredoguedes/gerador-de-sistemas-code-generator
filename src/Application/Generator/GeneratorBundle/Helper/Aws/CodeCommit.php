<?php

namespace App\Application\Generator\GeneratorBundle\Helper\Aws;

use \Aws\Result as AwsResult;
use \Aws\CodeCommit\CodeCommitClient as CodeCommitClient;

use Symfony\Component\Process\Process;

class CodeCommit
{
    protected CodeCommitClient $client;

    public function __construct(
        protected array $credentials,
        protected string $projectDir,
    ){
        /*$this->client = new CodeCommitClient([
            'region' => 'us-east-1',
            'version' => '2016-11-15',
            //'profile' => 'default',
            'credentials' => $credentials,
        ]);*/
    }

    public function make(): void
    {

        $this->initRepository('teste-repository');

        //$this->createRepository();



    }




    protected string $organization = "geradordesistemas";
    protected string $organizationPass = "REDACTED_GITHUB_TOKEN";






    public function initRepository($projectName)
    {
        $dir = $this->projectDir . "/public/projects/$projectName";
        //dd($dir);

        $commands = [
            ['rm', '-rf', '.git'],
            ['git', 'init'],
            ['git', 'add', '.'],
            ['git', 'commit', '-m', 'First Commit - By Gerador de Sistemas'],
            ['git', 'branch', '-M', 'main'],
            ['hub', 'delete', '-y', "$this->organization/$projectName"],
            ['hub', 'create'],
            ['git', 'push', "https://$this->organization:$this->organizationPass@github.com/$this->organization/$projectName.git"  ]
        ];

        $status = [];
        foreach ($commands as $command){
            $status[] = $this->runCommand(commands: $command, directory: $dir);
        }

        dd($status);

    }

    public function createRepository(): bool
    {



    }


    protected function runCommand($commands, $directory)
    {
        $process = new Process($commands);
        $process->setWorkingDirectory($directory);
        $process->run();

        return $process->isSuccessful();
    }

}
