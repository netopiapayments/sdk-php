<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once('classes/log.php');
include_once('../lib/start.php');
include_once('../lib/status.php');


include_once __DIR__ . '/vendor/autoload.php';
/**
 * Load .env 
 * Read Base root , ... from .env
 * The  env var using in UI ,..
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

/**
 * if Session is expired
 * redirect to 404
 * */ 
if(empty($_COOKIE['orderID']) || empty($_COOKIE['ntpID'])) {
    $url = $_ENV['PROJECT_SERVER_ADDRESS'].$_ENV['PROJECT_BASE_ROOT'].$_ENV['PROJECT_404_PAGE'];
    header("Location: $url");
    exit;
}

/**
 * Define status class
 * Set the parameters
 * the "apiKey","isLive", "posSignature" can be set statically or read from DB, File, ...
 */
$statusPayment = new Status();
$statusPayment->posSignature        = $_ENV['NETOPIA_SIGNATURE'];   // Your signiture ID here
$statusPayment->apiKey              = $_ENV['NETOPIA_API_KEY'];     // Your  Api key

$statusPayment->ntpID               = $_COOKIE['ntpID'];
$statusPayment->orderID             = $_COOKIE['orderID'];
$statusPayment->isLive              = $_ENV['PAYMENT_LIVE_MODE'];


/**
 * Get payment status
 */
$statusPayment->validateParam();
$jsonStatusPayment = $statusPayment->setStatus();
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
