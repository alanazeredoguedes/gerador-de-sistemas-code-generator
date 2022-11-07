<?php

namespace App\Application\Generator\GeneratorBundle\Helper;

use Symfony\Component\Process\Process;

class GitHelper
{
    protected string $gitBaseRepository = 'https://github.com/alanazeredoguedes/alanazeredoguedes-gerador-de-sistemas-base';

    public function __construct(
        protected string $workingDirectory,
        protected string $projectDirectory,
        protected string $projectName,
    )
    {
    }

    public function cloneBaseRepository(): bool
    {
        $this->removeDir($this->projectDirectory);

        $command = ['git', 'clone', $this->gitBaseRepository, $this->projectName];
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




}