<?php

PHPWS_Core::initModClass('hms', 'exception/HMSException.php');

class MissingDataException extends HMSException {
	public $data;
	
	public function __construct($message, array $data, $code = 0) {
		parent::__construct($message, $code);
		$this->data = $data;
	}

    public function getJSON()
    {
        foreach($this as $key=>$value)
        {
            $json->$key = $value;
        }
        return json_encode($json);
    }
}
