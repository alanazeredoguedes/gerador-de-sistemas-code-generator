<?php

namespace App\Application\Generator\GeneratorBundle\Helper;

use Symfony\Component\Process\Process;

class GitHelper
{
    protected string $organization = "geradordesistemas";
    protected string $organizationPass = "REDACTED_GITHUB_TOKEN";
    protected string $gitBaseRepository = 'https://github.com/geradordesistemas/base';

    public function __construct(
        protected string $workingDirectory,
        protected string $projectDirectory,
        protected string $projectName,
        protected string $projectNameBuild,
    )
    {
    }

    public function cloneBaseRepository(): bool
    {
        $this->removeDir($this->projectDirectory);

        $command = ['git', 'clone', $this->gitBaseRepository, $this->projectNameBuild];
        $process = new Process($command);
        $process->setWorkingDirectory($this->workingDirectory);
        $process->run();

        return $process->isSuccessful();
    }


    public function removeDir($directory): void
    {
        if( is_dir( $directory) ){
            $command = ['rm', '-rf', $directory];
            $process = new Process($command);
            $process->setWorkingDirectory($this->workingDirectory);
            $process->run();
        }
    }

    public function commitProject(): string
    {

// git config --global --add safe.directory /var/www/html/public/projects/2b24d495052a8ce66358eb576b8912c8
        $commands = [
            ['rm', '-rf', '.git'],
            ['git', 'init'],
            ['git', 'config', '--global', '--add', 'safe.directory', $this->projectDirectory ],
            ['git', 'add', '.'],
            ['git', 'commit', '-m', 'First Commit - By Gerador de Sistemas'],
            ['git', 'branch', '-M', 'main'],
            ['hub', 'delete', '-y', "$this->organization/$this->projectNameBuild"],
            ['hub', 'create'],
            ['git', 'push', "https://$this->organization:$this->organizationPass@github.com/$this->organization/$this->projectNameBuild.git"  ]
        ];

        $status = [];
        foreach ($commands as $command){
            $status[] = $this->runCommand(commands: $command, directory: $this->projectDirectory);
        }

       //$this->removeDir($this->projectDirectory);

        return "https://github.com/$this->organization/$this->projectNameBuild.git";
        //dd($status);
    }

    protected function runCommand($commands, $directory): bool
    {
        $process = new Process($commands);
        $process->setWorkingDirectory($directory);
        $process->run();

        return $process->isSuccessful();
    }

}