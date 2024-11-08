<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once('classes/log.php');
include_once('../lib/verifyAuth.php');
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
 * if Session / cookie is expired / not exist
 * redirect to 404
 * */ 
if(empty($_COOKIE['orderID']) || empty($_COOKIE['ntpID'])) {
    $url = $_ENV['PROJECT_SERVER_ADDRESS'].$_ENV['PROJECT_BASE_ROOT'].$_ENV['PROJECT_404_PAGE'];
    header("Location: $url");
    exit;
}

/**
* Get Payment Status
* the "apiKey","isLive", "posSignature" can be set statically or read from DB,File, ...
*/
$statusPayment = new Status();
$statusPayment->posSignature        = $_ENV['NETOPIA_SIGNATURE'];
$statusPayment->apiKey              = $_ENV['NETOPIA_API_KEY']; // Your Api KEY
$statusPayment->ntpID               = $_COOKIE['ntpID'];
$statusPayment->orderID             = $_COOKIE['orderID'];
$statusPayment->isLive              = $_ENV['PAYMENT_LIVE_MODE'];

/**
 * Validate, set parameters & get payment status
 */
$statusPayment->validateParam();
$jsonStatusPayment = $statusPayment->setStatus(); 
$jsonStatusResult  = $statusPayment->getStatus($jsonStatusPayment);
$paymentStatustObj = json_decode($jsonStatusResult);
$payReturnArrData = $paymentStatustObj;

log::setBackendLog($jsonStatusResult);
log::setRealTimeLog(array("paymentStatus" => $paymentStatustObj->message));
log::setRealTimeLog(array("paymentStatus" => $paymentStatustObj->data->error->message));

switch ($paymentStatustObj->data->error->code) {
    case "100":
        if($paymentStatustObj->data->payment->status == 15) {
            /**
             * Define verifyAuth
             */

            $verifyAuth = new VerifyAuth();
            $verifyAuth->apiKey              = $_ENV['NETOPIA_API_KEY'];                                                                // Your Api KEY
            $verifyAuth->authenticationToken = isset($_COOKIE['authenticationToken']) ? $_COOKIE['authenticationToken'] : null;
            $verifyAuth->ntpID               = isset($_COOKIE['ntpID']) ? $_COOKIE['ntpID'] : null;
            $verifyAuth->postData            = $_POST;                                                                                  // Parameter passed by bank ,...
            $verifyAuth->isLive              = $_ENV['PAYMENT_LIVE_MODE'];

            /**
             * Set params for /payment/card/verify-auth
             * Send request to /payment/card/verify-auth
             * @return 
             *  Json string
             */
            $jsonAuthParam = $verifyAuth->setVerifyAuth();
            $paymentResult = $verifyAuth->sendRequestVerifyAuth($jsonAuthParam);
            $paymentResultArr = json_decode($paymentResult);
            $payReturnArrData = $paymentResultArr;
            log::setLog("100", $paymentResult, array("backurl - status 100 - verifyAuth result" => $paymentResult));
        }
    break;
    default:
        # code...
        // die("Do Error Handelling ;-) ");
    break;
}

?>
<!doctype html>
<html lang="en">
    <?php include_once("theme/inc/header.inc"); ?>
    <body class="bg-light">
        <div class="container">
            <?php include_once("theme/inc/topNav.inc"); ?>
            <div class="row">
                <?php include_once("theme/returnUrlUI.php"); ?>
            </div>
        </div>
        <?php include_once("theme/inc/footer.inc"); ?>
    </body>
</html>