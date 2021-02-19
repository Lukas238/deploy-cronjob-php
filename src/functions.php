<?php
include('config.php');


/* FUNCTIONS
**********************/

function saveToLog($toDeployItem, $repoSettings, $output, $retval)
{
    global $logFolderName, $logBaseFileName;
    $flag = FILE_APPEND; // By default append to today log file.

    $logPath = __DIR__ . "/" . $logFolderName; //Get real log path
    $logFullName = $logPath . '/' . $logBaseFileName . '_' . date('d') . '.log';

    $outputData = implode("\n\t", $output); // Implode array output to string

    // Prepare the log data
    $data = date("Y-m-d h:i:sa") . " - " . $toDeployItem . " - " . $repoSettings['path'] . " - " . $repoSettings['command'] . " - " . $retval;
    if( $outputData ){
        $data .= "\n\t" . $outputData;
    }
    $data .= "\n";

    // if $savePath folder do not exist, create it
    if (!is_dir($logPath)) {
        mkdir($logPath);
    }

    // Check if log file is from last month
    if( date('m', @filemtime($logFullName)) != date('m') ){
        $flag = 0; // Set flag to clean the log file
    };

    // Save the log data
    file_put_contents($logFullName, $data, $flag);
}

/**
 * isValidHMAC
 * Check if the submited header hash HMAC matchs the enconded submited body
 */
function isValidHMAC($headers, $body_raw, $secret)
{
    //Get header hash HMAC from bitbucket
    $headers_hash_hmac = explode('=', $headers->HTTP_X_HUB_SIGNATURE);

    // Calculate body hash HMAC
    $body_hash_hmac = hash_hmac($headers_hash_hmac[0], $body_raw, $secret);

    // If posted header hash do not match the secret enconded body, exit.
    if (!hash_equals($headers_hash_hmac[1], $body_hash_hmac)) {
        return;
    };
    return true;
}

/**
 * repoUpdate()
 *
 * This function will run a git pull (or other custom commnad) on the repo path.
 *
 */
function repoUpdate($toDeployItem, $repoSettings)
{
    global $toDeployFolderName;

    $output = [];
    $retval = null;
    $toDeployFolder = __DIR__ . '/' . $toDeployFolderName;

    chdir($repoSettings['path'],);
    exec($repoSettings['command'], $output, $retval);

    //Remove associated to_deploy file
    unlink($toDeployFolder ."/". $toDeployItem . ".txt");

    saveToLog($toDeployItem, $repoSettings, $output, $retval);
}
