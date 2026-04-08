<?php
require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../function-includes/bootstrap.php');

if (!function_exists('logger')){
    /**
     * logger helper for monolog. if channel is given, log file will be created for that channel
     * @param string $channel
     * @return \Psr\Log\LoggerInterface
     */
    function logger($channel = 'allocate7') : \Psr\Log\LoggerInterface {
        //set default log path
        $logPath = __DIR__ . '/../../logs';
        if(!is_dir($logPath)) mkdir($logPath);

        $logFile = $logPath . '/' . $channel . '-' .date('Y-m-d'). '.log';

        $logger =  new \Monolog\Logger($channel);
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($logFile));

        return $logger;
    }
}

if (!function_exists('pluck')){
    /**
     * plucks the values of given column in array
     * @param array $data
     * @param $arrKey
     * @param null $id
     * @return array
     */
    function pluck(array $data, $arrKey, $id = null ){
        if($id){
            return array_column($data, $arrKey, $id);
        }

        return array_column($data, $arrKey);
    }
}