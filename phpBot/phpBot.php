<?php
// Set error reporting
error_reporting( E_ALL );

// Set configuration file
$config = $argv[1];

// Include Bot Class
include("phpBotClass.php");

// Instantiate Bot Object
$bot = new phpBot();

// Run Bot
$bot->run();