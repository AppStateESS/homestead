<?php
class Contract {

    protected $id;
    protected $banner_id;
    protected $term;

    protected $envelope_id;
    protected $envelope_status;
    protected $envelope_status_time;

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
        PHPWS_Core::initModClass('hms', 'Docusign/EnvelopeFactory.php');

        $docusignUsername = PHPWS_Settings::get('hms', 'docusign_username');
        if ($docusignUsername === null || $docusignUsername == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign username.');
        }

        $docusignPassword = PHPWS_Settings::get('hms', 'docusign_password');
        if ($docusignPassword === null || $docusignPassword == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign password.');
        }

        $docusignKey = PHPWS_Settings::get('hms', 'docusign_key');
        if ($docusignKey === null || $docusignKey == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign key.');
        }

        $docusignEnv = PHPWS_Settings::get('hms', 'docusign_env');
        if ($docusignEnv === null || $docusignEnv == '') {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('Missing docusign key.');
        }

        $docusignClient = new Docusign\Client($docusignKey, $docusignUsername, $docusignPassword, $docusignEnv);

        $envelope = \Docusign\EnvelopeFactory::getEnvelopeById($docusignClient, $this->envelope_id);

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

    public function getEnvelopeStatusTime()
    {
        return $this->envelope_status_time;
    }

}

class ContractRestored extends Contract {
	public function __construct(){} // Empty constructor for loading from DB
}
