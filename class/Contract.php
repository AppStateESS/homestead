<?php

namespace Homestead;

use \Homestead\Exception\InvalidConfigurationException;
use \Homestead\Docusign\EnvelopeFactory;
use \Homestead\Docusign\Client;

class Contract {

    protected $id;
    protected $banner_id;
    protected $term;

    protected $envelope_id;
    protected $envelope_status; // String - see below for constants defining the possible values
    protected $envelope_status_time;

    // Class constants for DocuSign envelope statuses in $this->envelope_status
    const STATUS_SENT       = 'sent'; // Aka "created"
    const STATUS_VOIDED     = 'voided';
    const STATUS_DELIVERED  = 'delivered'; // Sent via email
    const STATUS_COMPLETED  = 'completed'; // This is the "signed" and done/success status
    const STATUS_DECLINED   = 'declined';

    // TODO: make first parameter an instance of $student
    public function __construct($student, $term, $envelopeId, $envelopeStatus, $envelopeStatusTime)
    {
    	$this->banner_id = $student->getBannerId();
        $this->term = $term;
        $this->envelope_id = $envelopeId;

        $this->envelope_status = $envelopeStatus;
        $this->envelope_status_time = $envelopeStatusTime;
    }

    public function updateEnvelope()
    {
        $docusignUsername = \PHPWS_Settings::get('hms', 'docusign_username');
        if ($docusignUsername === null || $docusignUsername == '') {
            throw new InvalidConfigurationException('Missing docusign username.');
        }

        $docusignPassword = \PHPWS_Settings::get('hms', 'docusign_password');
        if ($docusignPassword === null || $docusignPassword == '') {
            throw new InvalidConfigurationException('Missing docusign password.');
        }

        $docusignKey = \PHPWS_Settings::get('hms', 'docusign_key');
        if ($docusignKey === null || $docusignKey == '') {
            throw new InvalidConfigurationException('Missing docusign key.');
        }

        $docusignEnv = \PHPWS_Settings::get('hms', 'docusign_env');
        if ($docusignEnv === null || $docusignEnv == '') {
            throw new InvalidConfigurationException('Missing docusign key.');
        }

        $docusignClient = new Client($docusignKey, $docusignUsername, $docusignPassword, $docusignEnv);

        $envelope = EnvelopeFactory::getEnvelopeById($docusignClient, $this->envelope_id);

        $this->envelope_status = $envelope->getStatus();
        $this->envelope_status_time = $envelope->getStatusDateTimeUnixTimestamp();
    }

    public function getId()
    {
    	return $this->id;
    }

    public function setId($id)
    {
    	$this->id = $id;
    }

    public function getBannerId()
    {
    	return $this->banner_id;
    }

    public function getTerm()
    {
    	return $this->term;
    }

    public function getEnvelopeId()
    {
    	return $this->envelope_id;
    }

    public function getEnvelopeStatus()
    {
        return $this->envelope_status;
    }

    /**
     * TODO: Check for a valid status string.
     */
    public function setEnvelopeStatus($status)
    {
        $this->envelope_status = $status;
    }

    public function getEnvelopeStatusTime()
    {
        return $this->envelope_status_time;
    }

    /**
     * Sets the envelope status time field.
     *
     * @param int $time Unix timestamp when envelope status was last updated.
     */
    public function setEnvelopeStatusTime($time)
    {
        $this->envelope_status_time = $time;
    }
}

class ContractRestored extends Contract {
	public function __construct(){} // Empty constructor for loading from DB
}
