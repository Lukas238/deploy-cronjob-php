<?php
include 'functions.php';

$toDeployFolder = __DIR__ . '/' . $toDeployFolderName;

// Get list of replo deploy files

$toDeployList = glob($toDeployFolder . '/*.txt');
if (count($toDeployList) <= 0) {
    die('Nothing to do.'); // Nothing to deploy
}

// Remove the file extension
$toDeployList = array_map(function ($val) {
    global $toDeployFolder;
    return preg_replace('/^' . preg_quote($toDeployFolder, '/') . '\/(.+)\..{3}$/i', '$1', $val);
}, $toDeployList);


// Get configuration JSON
$config_json = @file_get_contents($reposConfigFileName) or die('Missing configuration file.');
$projectsJSON = json_decode($config_json);

if(!$projectsJSON){
    die('Configuration file is empty or is not valid.');
}

// Make an array with projectnaem_reponame as key with the repo configuration inside
$reposList = [];
foreach ($projectsJSON as $projecName => $project) {
    foreach ($project as $repoName => $repo) {
        $reposList[$projecName . '_' . $repoName] = $repo;
    }
}

// For each repo to deploy
foreach ($toDeployList as $toDeployItem) {
    // If there is not a matching repo in the configuration list, skip it
    if (!array_key_exists($toDeployItem, $reposList)) {
        continue;
    }

    // Normalize the repo settings
    if (is_array($reposList[$toDeployItem])) {
        $repoSettings = array_merge($defaultRepoSettings, $reposList[$toDeployItem]);
    } else {
        $repoSettings = array_merge($defaultRepoSettings, ['path' => $reposList[$toDeployItem]]);
    }

    $repoSettings['path'] = realpath($repoSettings['path']);

    // Check if the path is valid
    if (!$repoSettings['path']) {
        continue;
    }

    repoUpdate($toDeployItem, $repoSettings);
}


