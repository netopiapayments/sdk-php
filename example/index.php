<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once __DIR__ . '/vendor/autoload.php';
include_once('classes/log.php');


/**
 * Load .env 
 * To read Logo , ... from .env
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


if($_ENV['DEBUGGING_MODE']){
    log::setRealTimeLog(array('CheckoutPage' => $_ENV['LOG_TXT_CHECKOUT'] ? $_ENV['LOG_TXT_CHECKOUT'] : 'Checkout is hitting' ));
}

?>
<!doctype html>
<html lang="en">
    <?php include_once("theme/inc/header.inc"); ?>
    <body class="bg-light">
        <div class="container">
            <?php include_once("theme/inc/topNav.inc"); ?>
            <?php include_once("theme/inc/nav.inc"); ?>
            <div class="row">
                <div class="tab-content w-100" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <?php include_once("theme/checkout.php"); ?>
                    </div>

                    <div class="tab-pane fade" id="realTimeLog" role="tabpanel" aria-labelledby="realTimeLog-tab">
                        <div class="panel panel-primary" id="result_panel">
                            <div class="panel-heading">
                                <h3 class="panel-title">Real time Log</h3>
                            </div>
                            <div class="list-group">
                                <span id="containerDiv" class="w-100 p-3">Real Time Log</span>
                            </div>
                            <hr>
                                <button class="btn btn-secondary btn-lg" onclick="cleanLogFile(1)">Remove Log</button>
                                <div id="logMessage-success" class="alert alert-success" style="display: none;">
                                    <p id="logSuccessMessage"></p>
                                </div>
                                <div id="logMessage-warning" class="alert alert-warning" style="display: none">
                                    <p id="logWarningMessage"></p>
                                </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php include_once("theme/inc/footer.inc"); ?>
    </body>
</html>