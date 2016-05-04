<?php

PHPWS_Core::initModClass('hms','PackageDesk.php');

class PackageDeskFactory {

    public static function getPackageDesks()
    {
        $db = new PHPWS_DB('hms_package_desk');

        $result = $db->select();

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        $desks = array();

        foreach ($result as $row) {
            $desk = new RestoredPackageDesk();
            $desk->setId($row['id']);
            $desk->setName($row['name']);
            $desk->setLocation($row['location']);
            $desk->setStreet($row['street']);
            $desk->setCity($row['city']);
            $desk->setState($row['state']);
            $desk->setZip($row['zip']);

            $desks[] = $desk;
        }

        return $desks;
    }

    public static function getPackageDesksAssoc()
    {
        $desks = self::getPackageDesks();

        if (sizeof($desks) == 0) {
            // var_dump($desks);
            // exit;
            return array();
        }

        $results = array();

        foreach ($desks as $d) {
            $results[$d->getId()] = $d->getName();
        }

        return $results;
    }

    /**
     * Retrieves the package desk matching the given id from the database.
     * @return array containing one PortalRestored object
     */
    public static function getPackageDeskById($id)
    {
        $db    = PdoFactory::getPdoInstance();
        $query = 'SELECT * FROM hms_package_desk WHERE id = :deskId';
        $stmt  = $db->prepare($query);

        $params = array(
            'deskId' => $id
        );

        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'RestoredPackageDesk');

        return $stmt->fetch();
    }
}
