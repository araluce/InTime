<?php
//This requires the phar to have been extracted successfully.
require_once ('vendor/autoload.php');

//Use the Composer classes
use Composer\Console\Application;
use Composer\Command\UpdateCommand;
use Symfony\Component\Console\Input\ArrayInput;

// change out of the webroot so that the vendors file is not created in
// a place that will be visible to the intahwebz
chdir('../');

//Create the commands
$input = new ArrayInput(array('command' => 'install'));

//Create the application and run it with the commands
$application = new Application();
$application->run($input);

?>