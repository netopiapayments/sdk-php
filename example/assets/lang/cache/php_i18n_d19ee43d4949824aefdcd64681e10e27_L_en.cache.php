<?php class L {
const payment_api_with_card = 'Payment API with card';
const checkout_title = 'Below is a PHP example to make paymets using NETOPIA Payments API.';
const tabs_MakePayment = 'Make Payment';
public static function __callStatic($string, $args) {
    return vsprintf(constant("self::" . $string), $args);
}
}
function L($string, $args=NULL) {
    $return = constant("L::".$string);
    return $args ? vsprintf($return,$args) : $return;
}