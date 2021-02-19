<?php

/* CONFIG
**********************/
date_default_timezone_set ('America/Argentina/Buenos_Aires');
$reposConfigFileName = 'repos.conf';
$secret = '1234567'; //Secret to decrypt the HMAC hash.
$toDeployFolderName = 'to_deploy';
$logFolderName= 'logs';
$logBaseFileName = 'deploy';
$defaultRepoSettings = [
    'path' => '',
    'command' => 'lss'
];

