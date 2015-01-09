<?php

namespace Docusign;

class Envelope {
	
    private $envelopeId;
    private $uri;
    private $statusDateTime;
    private $status;
    
    public function __construct($envelopeId, $uri, $statusDateTime, $status)
    {
    	$this->envelopeId = $envelopeId;
        $this->uri = $uri;
        $this->statusDateTime = $statusDateTime;
        $this->status = $status;
    }
    
    public function getUri()
    {
    	return $this->uri;
    }
    
    public function getEnvelopeId()
    {
    	return $this->envelopeId;
    }
}