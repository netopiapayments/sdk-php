<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . '/vendor/autoload.php';
/**
 * Load .env 
 * Read Base root , ... from .env
 * The  env var using in UI ,..
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

?>
<!doctype html>
<html lang="en">
    <?php include_once("theme/inc/header.inc"); ?>
    <body class="bg-light">
        <div class="container">
            <?php include_once("theme/inc/topNav.inc"); ?>
            <div id="message-warning-404" class="alert alert-warning" role="alert" >
                <h4 class="alert-heading" >Error!</h4>
                <p>Check if you sent all necessary parameters!.</p>
                <hr>
                <p class="mb-0" >As example, OrderID, ntpID,...</p>
            </div>
        </div>
        <?php include_once("theme/inc/footer.inc"); ?>
    </body>
</html>
