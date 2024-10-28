<?php 
include_once('../lib/start.php');

class Request extends Start {
    public $authenticationToken;
    public $ntpID;
    public $jsonRequest;

    function __construct(){
        parent::__construct();
    }

    public function setConfig($configData) {
        $config = array(
            'emailTemplate' => (string) isset($configData['emailTemplate']) ? $configData['emailTemplate'] : 'confirm',
            'notifyUrl'     => (string) $configData['notifyUrl'],
            'redirectUrl'   => (string) $configData['redirectUrl'],
            'language'      => (string) isset($configData['language']) ? $configData['language'] : 'RO'
        );
        return $config;
    }

    public function setPayment($cardData, $threeDSecusreData) {
        $threeDSecusreData = json_decode($threeDSecusreData);
        $threeDSecusreData->IP_ADDRESS = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "127.0.0.1";
        
        $payment = array(
            'options' => [
                'installments' => (int) 1,
                'bonus'        => (int) 0
            ],
            'instrument' => [
                'type'          => (string) "card",
                'account'       => (string) $cardData['account'],
                'expMonth'      => (int) $cardData['expMonth'],
                'expYear'       => (int) $cardData['expYear'],
                'secretCode'    => (string) $cardData['secretCode'],
                'token'         => null
            ],
            'data' =>  $threeDSecusreData
        );
        return $payment;
    }

    /**
     * Set the order
     */
    public function setOrder($orderData) {
        $order = array(
            'ntpID'         => (string) null, 
            'posSignature'  => (string) $this->posSignature,
            'dateTime'      => (string) date("c", strtotime(date("Y-m-d H:i:s"))),
            'description'   => (string) $orderData->description,
            'orderID'       => (string) $orderData->orderID,
            'amount'        => (float)  $orderData->amount,
            'currency'      => (string) $orderData->currency,
            'billing'       => [
                'email'         => (string) $orderData->billing->email,
                'phone'         => (string) $orderData->billing->phone,
                'firstName'     => (string) $orderData->billing->firstName,
                'lastName'      => (string) $orderData->billing->lastName,
                'city'          => (string) $orderData->billing->city,
                'country'       => (int)    $orderData->billing->country,
                'state'         => (string) $orderData->billing->state,
                'postalCode'    => (string) $orderData->billing->postalCode,
                'details'       => (string) $orderData->billing->details
            ],
            'shipping'      => [
                'email'         => (string) $orderData->shipping->email,
                'phone'         => (string) $orderData->shipping->phone,
                'firstName'     => (string) $orderData->shipping->firstName,
                'lastName'      => (String) $orderData->shipping->lastName,
                'city'          => (string) $orderData->shipping->city,
                'country'       => (int)    $orderData->shipping->country,
                'state'         => (string) $orderData->shipping->state,
                'postalCode'    => (string) $orderData->shipping->postalCode,
                'details'       => (string) $orderData->shipping->details
            ],
            'products' => $orderData->products,
            'installments'  => array(
                                    'selected'  => (int) 1,
                                    'available' => [(int) 0]
                            ),
            'data'       => null
        );
        return $order;
    }


    /**
     * Set the request to payment
     * @output json
     */
    public function setRequest($configData, $cardData, $orderData, $threeDSecusreData = null) {
        $startArr = array(
          'config'  => $this->setConfig($configData),
          'payment' => $this->setPayment($cardData, $threeDSecusreData),
          'order'   => $this->setOrder($orderData)
      );
      
      // make json Data 
      return json_encode($startArr);
    }

    public function startPayment(){
      $result = $this->sendRequest($this->jsonRequest);
      return($result);
    }    
}