<?php
include '../functions.php';
$toDeployFolder = __DIR__ . '/../' . $toDeployFolderName;

/*
* SCRIPT
************************/

//Get webhook body json
$body_raw = trim(file_get_contents('php://input'));

if(!$body_raw){
    die('Nothing to do.');
}

$body = json_decode($body_raw); // Get webhook data

if(isset($_SERVER['HTTP_X_EVENT_KEY']) && $_SERVER['HTTP_X_EVENT_KEY'] == 'diagnostics:ping'){
    if(@$body->test){
        die('Success!');
    }
    die('Fail to receive test body value.');
}

// Check if HMAC match
if (!isValidHMAC($_SERVER, $body_raw, $secret)) {
    die('Invalid HMAC hash.');
}

//Generate the repo rull name
$repoFullName = $body->repository->project->key . '_' . $body->repository->name;

//Get repo updated branch name
$repoBranchName = trim(strtolower($body->changes[0]->ref->displayId));
if( !in_array($repoBranchName,['main', 'master'])){
    die('Only "main" or "master" branchs can be deployed.');
}

// if $savePath folder do not exist, create it
if (!is_dir($toDeployFolder)) {
    mkdir($toDeployFolder);
}

//Create an empty .txt file with the repo full name as filename
file_put_contents($toDeployFolder . '/' . $repoFullName . '.txt', '');

/**
 * Done!
 *
 * Now, the cronjob script will run the deploy.php script every minute to deploy
 * any repo found in the to_deploy folder.
 */

echo "Repo added to queue.";
