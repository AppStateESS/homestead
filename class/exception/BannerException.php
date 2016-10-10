<?php

PHPWS_Core::initModClass('hms', 'exception/HMSException.php');

class BannerException extends HMSException {

    public function __construct($message, $code = 0, $functionName, $params){
        parent::__construct($message, $code);
        $errorMsg = $functionName . ' ' . $code . ' ' . print_r($params, true);
        PHPWS_Core::log($errorMsg, 'soapError.log', ('BannerError'));
    }
}
