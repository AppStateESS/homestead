<?php

PHPWS_Core::initModClass('hms', 'Contract.php');
PHPWS_Core::initModClass('hms', 'PdoFactory.php');
PHPWS_Core::initModClass('hms', 'DocusignClientFactory.php');
PHPWS_Core::initModClass('hms', 'Docusign/EnvelopeFactory.php');

class ContractFactory {


    public static function getContractById($id)
    {
        // TODO
    }

    public static function getContractByStudentTerm(Student $student, $term)
    {
        PHPWS_Core::initModClass('hms', 'Contract.php');
        $db = PdoFactory::getPdoInstance();

        $query = 'SELECT * FROM hms_contract WHERE banner_id = :bannerId AND term = :term';

        $stmt = $db->prepare($query);

        $params = array('bannerId' => $student->getBannerId(),
                        'term' => $term);
        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'ContractRestored');

        return $stmt->fetch();
    }

    public static function getContractsForStudent(Student $student)
    {
        // TODO
    }

    public static function save(Contract $contract)
    {
        $db = PdoFactory::getPdoInstance();

        $id = $contract->getId();

        if (isset($id)) {
            $query = "UPDATE hms_contract
                SET (banner_id, term, envelope_id, envelope_status, envelope_status_time) =
                (:bannerId, :term, :envelopeId, :envelopeStatus, :envelopeStatusTime) WHERE id = :id";

            $params = array(
                'id' => $contract->getId(),
                'bannerId' => $contract->getBannerId(),
                'term' => $contract->getTerm(),
                'envelopeId' => $contract->getEnvelopeId(),
                'envelopeStatus' => $contract->getEnvelopeStatus(),
                'envelopeStatusTime' => $contract->getEnvelopeStatusTime()
            );

        }else{
            // Insert
            $query = "INSERT INTO hms_contract (id, banner_id, term, envelope_id, envelope_status, envelope_status_time) VALUES (nextval('hms_contract_seq'), :bannerId, :term, :envelopeId, :envelopeStatus, :envelopeStatusTime)";

            $params = array(
                'bannerId' => $contract->getBannerId(),
                'term' => $contract->getTerm(),
                'envelopeId' => $contract->getEnvelopeId(),
                'envelopeStatus' => $contract->getEnvelopeStatus(),
                'envelopeStatusTime' => $contract->getEnvelopeStatusTime()
            );
        }

        //var_dump($params);
        //var_dump($query);exit;

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        //var_dump($db->errorInfo());exit;

        // Update ID for a new object
        if (!isset($id)) {
            $contract->setId($db->lastInsertId('hms_contract_seq'));
        }
    }


    public static function sendContractOver18($student, $term)
    {
        // Double check that we weren't given an under 18 student
        if($student->isUnder18()){
            throw new \Exception('Student is under 18, so cannot use Over 18 contract template.');
        }

        // Get a term object
        $termObj = new Term($term);

        // Get the configured template ID for the given term
        $templateId = $termObj->getDocusignTemplate();

        // Setup the template roles
        $templateRoles = array(
            array(
                "roleName" => 'Student',
                "email" => $student->getEmailAddress(),
                "name" => $student->getLegalName()
                //"clientUserId" => $student->getBannerId()
            )
        );

        return self::sendContract($student, $term, $templateId, $templateRoles);
    }

    public static function sendContractUnder18($student, $term, $parentName, $parentEmail)
    {
        // Double check that we weren't given an over 18 student
        if(!$student->isUnder18()){
            throw new \Exception('Student is over, so cannot use Under 18 contract template.');
        }

        // Get a term object
        $termObj = new Term($term);

        // Get the under 18 envelope template id
        $under18TemplateId = $termObj->getDocusignUnder18Template();

        // Setup the template roles
        $templateRoles = array(
            array(
                "roleName" => 'Student',
                "email" => $student->getEmailAddress(),
                "name" => $student->getLegalName()
                //"clientUserId" => $student->getBannerId()
            ),
            array(
                "roleName" => 'Parent',
                "email" => $parentEmail,
                "name" => $parentName
            )
        );

        return self::sendContract($student, $term, $under18TemplateId, $templateRoles);
    }

    protected static function sendContract($student, $term, $envelopeTemplateId, $templateRoles)
    {
        // Create a DocusignClient object and Guzzle HTTP client
        $docusignClient = DocusignClientFactory::getClient();

        // Create the envelope
        $envelope = Docusign\EnvelopeFactory::createEnvelopeFromTemplate($docusignClient, $envelopeTemplateId, 'University Housing Contract', $templateRoles, Contract::STATUS_SENT, $student->getBannerId());

        // Create the corresponding Contract object and save it
        $contract = new Contract($student, $term, $envelope->getEnvelopeId(), $envelope->getStatus(), strtotime($envelope->getStatusDateTime()));
        ContractFactory::save($contract);

        // Return the contract object that was sent
        return $contract;
    }

    /**
     * Deletes the given contract.
     * NB: This does nothing to the corresponding DocuSign envelope. It only deletes our reference to an envelope.
     * NB: You should log this separately using the HMS_Activity_Log class.
     *
     * @param Contract $contract The contract to delete.
     */
    public static function deleteContract(Contract $contract)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "DELETE FROM hms_contract WHERE id = :id AND banner_id = :bannerId AND term = :term";

        $params = array(
            'id' => $contract->getId(),
            'bannerId' => $contract->getBannerId(),
            'term' => $contract->getTerm()
        );

        $stmt = $db->prepare($query);
        $stmt->execute($params);
    }
}
