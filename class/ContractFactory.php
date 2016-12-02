<?php

PHPWS_Core::initModClass('hms', 'Contract.php');
PHPWS_Core::initModClass('hms', 'PdoFactory.php');

class ContractFactory {


    public static function getContractById($id)
    {
    	//TODO
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

    public static function save($contract)
    {
    	$db = PdoFactory::getPdoInstance();

      $id = $contract->getId();

      if (isset($id)) {
        $query = "UPDATE hms_contract SET (banner_id, term, envelope_id, envelope_status, envelope_status_time) = (:bannerId, :term, :envelopeId, :envelopeStatus, :envelopeStatusTime) WHERE id = :id";

        $params = array(
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
}
