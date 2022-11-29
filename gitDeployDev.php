<?php

$organization = "geradordesistemas";
$organizationPass = "REDACTED_GITHUB_TOKEN";
$gitBaseRepository = 'https://github.com/geradordesistemas/base';

$projectsDirectory = "public/projects/";

$projects = array_diff(scandir($projectsDirectory), array('..', '.', '.gitignore'));

foreach ($projects as $projectName){

    $dir = scandir("$projectsDirectory/$projectName");
    if(!in_array("end.txt", $dir))
        continue;

    $commands = [
        'rm -rf .git',
        'git init',
        'git add .',
        'git commit -m "First Commit - By Gerador de Sistemas"',
        'git branch -M main',
        "hub delete -y $organization/$projectName",
        'hub create',
        "git push https://$organization:$organizationPass@github.com/$organization/$projectName.git",
        //"rm -rf $projectsDirectory$projectName",
    ];

    foreach ($commands as $command) {
        $output = shell_exec("(cd $projectsDirectory$projectName/ && $command)");
        //echo "<pre>$output</pre>";
    }

    shell_exec("rm -rf  $projectsDirectory$projectName");

    //var_dump($project);
    //echo "<pre>$output</pre>";

}








