<?php

$appConfig = [];

$appConfig['debug']  = true;
$appConfig['defres'] = 'Backend Public - Api Server v1.0';

$appConfig['infopage'] = '/';
$appConfig['oldintra'] = 'https://svilmonopoli.aams.it/index.php';
$appConfig['pwchange'] = 'http://26.2.64.54/Passport/';

$appConfig['frontend'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/nuovaintranet/public/frontend/build/index.html';
$appConfig['backend']  = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/nuovaintranet/public/backend/index.php';

$appConfig['logopng']    = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/nuovaintranet/public/frontend/build/resources/img/ADM_logo_152.png';
$appConfig['logofooter'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/nuovaintranet/public/frontend/build/resources/img/ADM_mono_152.png';

$appConfig['publicpath'] = '/opt/web/php/nuovaintranet/public/backend/src/SharedLibs';

if ($appConfig['debug'] === TRUE) {
    error_reporting(E_ALL);
    ini_set('display_errors', true);

        $appConfig['appconfig'] = [
                'settings' => [
                        'displayErrorDetails'    => true,
                        'addContentLengthHeader' => true
                ],
        ];
} else {
    ini_set('display_errors', false);

        $appConfig['appconfig'] = [
                'settings' => [
                        'displayErrorDetails'    => false,
                        'addContentLengthHeader' => false
                ],
        ];
}
?>
