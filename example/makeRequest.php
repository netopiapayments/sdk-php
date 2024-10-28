<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once('classes/log.php');
include_once('../lib/request.php');
include_once __DIR__ . '/vendor/autoload.php';
/**
 * Load .env 
 * Read Base root , ... from .env
 * The  env var using in UI ,..
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$request = new Request();

$request->posSignature  = $_ENV['NETOPIA_SIGNATURE'];             // Your signiture ID hear
$request->apiKey        = $_ENV['NETOPIA_API_KEY'];               // Your Api key Sandbox
$request->isLive        = $_ENV['PAYMENT_LIVE_MODE'];             // true || false
$request->notifyUrl     = $_ENV['PAYMENT_NOTIFY_URL'];            // Your IPN URL
$request->redirectUrl   = $_ENV['PAYMENT_REDIRECT_URL'];          // Your backURL

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
 * Send Json to Start action 
 */
$startResult = $request->startPayment();

/**
 * Set Order ID in Cookie
 */
setcookie('orderID', $orderData->orderID);



/**
 * Depend on status :
 *  - set 'authenticationToken' , 'ntpID' & 'authorizeUrl' in session
 */
$resultObj = json_decode($startResult);
log::setBackendLog($resultObj);

if($resultObj->status){
    switch ($resultObj->data->error->code) {
        case 100:
            /**
             * Set authenticationToken & ntpID to session 
             */
            if($resultObj->data->customerAction->type == "Authentication3D") {
                setcookie('authorizeUrl', $resultObj->data->customerAction->url);
            }

            /**
             * Thg authenticationToken is not exist in response always
             */
            if(isset($resultObj->data->customerAction->authenticationToken) ) {
                setcookie('authenticationToken', $resultObj->data->customerAction->authenticationToken);
            } else {
                setcookie('authenticationToken', "", -1);
            }
            
            setcookie('ntpID', $resultObj->data->payment->ntpID);
        break;
        case 101:
            /**
            * Card has no 3DS
            */
            setcookie('ntpID', $resultObj->data->payment->ntpID);
            // setcookie('token', $resultObj->data->payment->token);
        break;
        case 0:
            /**
             * Card has no 3DS
             */
            setcookie('ntpID', $resultObj->data->payment->ntpID);
            setcookie('token', $resultObj->data->payment->token);
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

/**
 * display result of start action in jason format
 * to be use in the UI, ...
 */
echo $startResult;
?>