<?php

PHPWS_Core::initModClass('hms', 'exception/HMSException.php');

class SOAPException extends HMSException {
    
    public function __construct($message, $code = 0, $functionName, $params){
        parent::__construct($message, $code);

        $errorMsg = $functionName . '(' . implode(',', $params) . ')';
        PHPWS_Core::log($errorMsg, 'soapError.log', ('SoapFault'));
    }
}

?>
