<?php
require_once(dirname(__FILE__) . '/../../inscricoes/include/config.php');
require_once 'DbOperation.php';
executarLogApp('Registro Iniciado', 'log');
$response = array();

//$_GET?   executarLogApp($_GET,  'index-a-get')  : "";
$_POST?  executarLogApp($_POST, 'index-a-post') : "";
$_INPUT = file_get_contents("php://input");
$_INPUT?  executarLogApp($_INPUT, 'index-a-input')   : "";

$fws_ = file_get_contents("php://input");
$fws_?  $fws_ = json_decode($fws_, true)    : "";
$fws_?  executarLogApp($fws_, 'index-a-json-array')   : "";
 
if (is_array($fws_)) {
    $db = new DbOperation();

    $result = $db->registerDevice($fws_);

    if ($result == 0) {
        $response['error'] = false;
        $response['message'] = 'Device registered successfully';
        executarLogApp('Device registered successfully', 'log');
    } elseif ($result == 2) {
        $response['error'] = true;
        $response['message'] = 'Device already registered';
        executarLogApp('Device already registered', 'log');
    } else {
        $response['error'] = true;
        $response['message'] = 'Device not registered';
        executarLogApp('Device not registered', 'log');
    }
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid Request...';
    executarLogApp('Invalid Request...', 'log-invalid-request');
}
executarLogApp($response, 'log');
executarLogApp('Registro Finalizado', 'log');
echo json_encode($response);
