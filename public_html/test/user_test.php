<?php
require "../vendor/autoload.php";
if (defined("ROOT") === false) {
    define("ROOT", $_SERVER['DOCUMENT_ROOT']);
}

$logger = new \RCSE\Core\Logger();
$config = new \RCSE\Core\Configurator($logger);
$user = new \RCSE\Core\User($logger, $config);

$reg_res = $user->userRegister("test", "HelloWorld", "test@xya.a", "m", "1999-01-01", "MSK");
$log_res = $user->userLogin("test", "HelloWorld", true);

echo "Registering user \"test\", result: ";
var_dump($reg_res);
echo "<br>";
echo "Singing in \"test\", result: ";
var_dump($log_res);
