<?php
/*
|--------------------------------------------------------------------------
| Detect The Application Environment
|--------------------------------------------------------------------------
*/

/*
if(isset($_SERVER['HTTP_HOST']))
    $url = $_SERVER['HTTP_HOST'];
else
    $url = '';


$env_file = '.live.env';
if(strripos($url, 'dev.flip.taxi') !== false)
    $env_file = '.dev.env';

if(strripos($url, 'demo.flip.taxi') !== false)
    $env_file = '.demo.env';

$envPath = trim(__DIR__.'/../'.$env_file);
$setEnv = trim(file_get_contents($envPath));
if (file_exists($envPath))
{
    putenv("APP_ENV=$setEnv");
    if (getenv('APP_ENV') && file_exists($envPath)) {
        $res = $app->loadEnvironmentFrom($env_file);
    }
}
*/

?>