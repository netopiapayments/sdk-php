<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . '/vendor/autoload.php';
include_once('classes/log.php');
use Netopia\Paymentsv2\Status;

/**
 * Load .env 
 * Read Base root , ... from .env
 * The  env var using in UI ,..
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


/**
 * Define status class
 * Set the parameters
 * the "apiKey","isLive", "posSignature" can be set statically or read from DB, File, ...
 */
$statusPayment = new Status();
$statusPayment->apiKey              = 'YOUR-API-KEY-XXXX-YOUR-API-KEY--YOUR-API-KEY--YOUR-API-KEY--';   // Your Api key here
$statusPayment->posSignature        = 'AAAA-BBBB-CCCC-DDDD-EEEE';                                       // Your signiture ID here
$statusPayment->ntpID               = '123456789';                                                      // Your ntpID here
$statusPayment->orderID             = 'YourUniqueOrderId';                                              // Your unique order ID here
$statusPayment->isLive              = false;                                                            // Set false for Sandbox & true for Live


/**
 * Validate parameters for status
 */
$statusPayment->validateParam();

/**
 * Set params for /operation/status
 */
$jsonStatusPayment = $statusPayment->setStatus();

/**
 * Get payment status
 */
$jsonStatusResult  = $statusPayment->getStatus($jsonStatusPayment);

$paymentStatustArr = json_decode($jsonStatusResult);

?>
<!doctype html>
<html lang="en">
    <?php include_once("theme/inc/header.inc"); ?>
    <body class="bg-light">
        <div class="container">
            <?php include_once("theme/inc/topNav.inc"); ?>
            <div class="row">
                <?php include_once("theme/statusForm.php"); ?>
            </div>
        </div>
        <?php include_once("theme/inc/footer.inc"); ?>
    </body>
</html>
