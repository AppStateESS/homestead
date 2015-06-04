<?php

/**
 * HMSException - Main exception class, parent to other more specific exceptions, extends PHP's exception class
 */

/**
 *
 * @author jbooker
 */
class HMSException extends Exception {

    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }
}

