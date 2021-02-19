<?php
include '../functions.php';
$toDeployFolder = __DIR__ . '/../' . $toDeployFolderName;


/*
* SCRIPT
************************/

//Get webhook body json
$body = trim(file_get_contents('php://input'));
// $body_raw = trim(file_get_contents('../post_body.json'));

//Get webhook headers
$headers = json_encode($_SERVER);
// $headers = json_decode(file_get_contents('../post_headers.json'));

// Check if HMAC match
if (!isValidHMAC($headers, $body_raw, $secret)) {
    echo "error";
    return;
}

$body = json_decode($body_raw); // Get webhook data

//Generate the repo rull name
$repoFullName = $body->repository->project->key . '_' . $body->repository->name;

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