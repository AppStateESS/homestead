<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class JsonError
{
    protected $status;
    protected $message;
    protected $exceptionId;

    public function __construct($status)
    {
        $this->setStatus($status);

        $this->exceptionId = null;
    }

    public function setStatus($status)
    {
        $this->status = "{$_SERVER['SERVER_PROTOCOL']} $status";
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setExceptionId($exceptionId)
    {
        $this->exceptionId = $exceptionId;
    }

    public function encode()
    {
        return json_encode(array(
            'status'      => $this->status,
            'message'     => $this->message,
            'exceptionId' => $this->exceptionId
        ));
    }

    public function renderStatus()
    {
        header($this->status);
    }
}

?>