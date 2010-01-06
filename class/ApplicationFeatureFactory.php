<?php

class ApplicationFeatureFactory
{
	public static function getForTerm($term)
	{
		$db = new PHPWS_DB('hms_application_feature');
		$db->addWhere('term', $term);
		$result = $db->getObjects('ApplicationFeature');
		
		if(PHPWS_Error::logIfError($result)) {
			throw new DatabaseException($result->toString());
		}
		
		// TODO: Reorg so the array is indexed by the class name.
		
		return $result;
	}
}

?>