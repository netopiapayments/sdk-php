<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . '/vendor/autoload.php';
include_once('classes/log.php');
use Netopia\Paymentsv2\Request;

/**
 * Load .env 
 * To read Logo , ... from .env
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$request = new Request();
$request->posSignature  = 'AAAA-BBBB-CCCC-DDDD-EEEE';                                       // Your signiture ID hear
$request->apiKey        = 'YOUR-API-KEY-XXXX-YOUR-API-KEY--YOUR-API-KEY--YOUR-API-KEY--';   // Your Api key here
$request->isLive        = false;
$request->notifyUrl     = $_ENV['PROJECT_SERVER_ADDRESS'].$_ENV['PROJECT_BASE_ROOT'].'/ipn.php';                  // Your IPN URL
$request->redirectUrl   = $_ENV['PROJECT_SERVER_ADDRESS'].$_ENV['PROJECT_BASE_ROOT'].'/backUrl.php';              // Your backURL


/**
 * Prepare json for start action
 */

 /** - Config section  */
 $configData = [
    'emailTemplate' => $_POST['emailTemplate'],
    'notifyUrl'     => $request->notifyUrl,
    'redirectUrl'   => $request->redirectUrl,
    'language'      => $_POST['language']
    ];
 
 /** - Payment section  */
 $cardData = [
    'account'       => $_POST['account'],
    'expMonth'      => $_POST['expMonth'],
    'expYear'       => $_POST['expYear'],
    'secretCode'    => $_POST['secretCode']
 ]; 

 /** - 3DS section  */
 $threeDSecusreData =  $_POST['clientInfo']; 

 /** - Order section  */
$orderData = new \StdClass();
 
$orderData->description             = isset($_POST['description']) ?  $_POST['description'] :  "DEMO API FROM WEB - V3";
$orderData->orderID                 = $_POST['orderID'];
$orderData->amount                  = $_POST['amount'];
$orderData->currency                = $_POST['currency'];

$orderData->billing                 = new \StdClass();
$orderData->billing->email          = $_POST['billingEmail'];
$orderData->billing->phone          = $_POST['billingPhone'];
$orderData->billing->firstName      = $_POST['billingFirstName'];
$orderData->billing->lastName       = $_POST['billingLastName'];
$orderData->billing->city           = $_POST['billingCity'];
$orderData->billing->country        = $_POST['billingCountry'];
$orderData->billing->state          = $_POST['billingState'];
$orderData->billing->postalCode     = $_POST['billingZip'];
$orderData->billing->details        = isset($_POST['details']) ?  $_POST['details'] : "Fara Detalie";

$orderData->shipping                = new \StdClass();
$orderData->shipping->email         = $_POST['shippingEmail'];
$orderData->shipping->phone         = $_POST['shippingPhone'];
$orderData->shipping->firstName     = $_POST['shippingFirstName'];
$orderData->shipping->lastName      = $_POST['shippingLastName'];
$orderData->shipping->city          = $_POST['shippingCity'];
$orderData->shipping->country       = $_POST['shippingCountry'];
$orderData->shipping->state         = $_POST['shippingState'];
$orderData->shipping->postalCode    = $_POST['shippingZip'];
$orderData->shipping->details       = isset($_POST['details']) ?  $_POST['details'] : "Fara Detalie";

$orderData->products                = setProducts($_POST['products']);

/**
 * Assign values and generate Json
 */
$request->jsonRequest = $request->setRequest($configData, $cardData, $orderData, $threeDSecusreData);

/**
 * Log Start Request
 */
if($_ENV['DEBUGGING_MODE']){
    log::logStartRequest($request->jsonRequest, "Request");
    log::setRealTimeLog(array('Start Request' => $_ENV['LOG_TXT_START_REQUEST'] ? $_ENV['LOG_TXT_START_REQUEST'] : 'Start request json created.' ));
}


/**
 * Send Json to Start action 
 */
$startResult = $request->startPayment();

/**
 * Log Start Result
 */
if($_ENV['DEBUGGING_MODE']){
    log::logStartRequest($startResult, "Result" );
    log::setRealTimeLog(array('Start Request send' => $_ENV['LOG_TXT_START_SEND'] ? $_ENV['LOG_TXT_START_SEND'] : 'Start request Sent.' ));
}


/**
 * display result of start action in jason format
 * to be use in the UI, ...
 */
echo $startResult;


/**
 * Depend on status :
 *  - set 'authenticationToken' , 'ntpID' & 'authorizeUrl' in cookies
 */
$resultObj = json_decode($startResult);
// print_r($resultObj);

if($resultObj->status){
    switch ($resultObj->data->error->code) {
        case 100:
            /**
             * Set authenticationToken & ntpID to cookies 
             */
            if($resultObj->data->customerAction->type == "Authentication3D") {
                setcookie("authorizeUrl", $resultObj->data->customerAction->url, time() + 10 * 60, "/");
            }

            setcookie("authenticationToken", $resultObj->data->customerAction->authenticationToken, time() + 10 * 60, "/");
            setcookie("ntpID", $resultObj->data->payment->ntpID, time() + 10 * 60, "/");
        break;
        case 0:
            /**
             * Card has no 3DS
             */
            setcookie("ntpID", $resultObj->data->payment->ntpID, time() + 10 * 60, "/");
            setcookie("token", $resultObj->data->payment->token, time() + 10 * 60, "/");
            setcookie("orderID", $orderData->orderID, time() + 10 * 60, "/");
        break;
        case 56:
            /**
             * duplicated Order ID 
             */
        break;
        case 99:
            /**
             * There is another order with a different price
             */
        break;
        case 19:
            // Expire Card Error
        break;
        case 20:
            // Founduri Error
        break;
        case 21:
            // CVV Error
        break;
        case 22:
            // CVV Error
        break;
        case 34:
            // Card Tranzactie nepermisa Error
        break;
        default:
            //
    }
}else {
    /**
     * There is an error / problem
     * the message error is handeling in UI, by bootstrap Alert
     */
}

/**
 * Log Start Result Error code
 */
if($_ENV['DEBUGGING_MODE']){
    log::setRealTimeLog(array('Result Error code ' => $resultObj->data->error->code ));
}

/**
 * Set the Product Items
 */
function setProducts($productList)
    {
        foreach ($productList as $productItem) {
            $proArr[] = [
                'name'     => (string) $productItem['pName'],
                'code'     => (string) $productItem['pCode'],
                'category' => (string) $productItem['pCategory'],
                'price'    => (int) $productItem['pPrice'],
                'vat'      => (int) $productItem['pVat']
            ];
        }
        return $proArr;
    }

?>