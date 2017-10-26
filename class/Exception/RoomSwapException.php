<?php

namespace Homestead\Exception;

class RoomSwapException extends HMSException {

    public function __construct($message, $code = 0){
        parent::__construct($message, $code);
        \PHPWS_Core::log('Room Swap failed! : '.$message, 'error.log');
    }
}
