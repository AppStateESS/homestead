<?php

function homesteadRunDbMigration($fileName)
{
    $db = new \PHPWS_DB();
    $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/hms/boost/updates/' . $fileName);
    if (\PEAR::isError($result)) {
        throw new \Exception($result->toString());
    }
}

/**
 *
 * @author theFergus <kevin at tux dot appstate dot edu>
 */
function hms_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
        case version_compare($currentVersion, '0.4.92', '<') :
            PHPWS_Core::initModClass('users', 'Permission.php');
            Users_Permission::registerPermissions('hms', $content);
            homesteadRunDbMigration('00-04-92.sql');
        case version_compare($currentVersion, '0.5.0', '<') :
            homesteadRunDbMigration('00-05-00.sql');
        case version_compare($currentVersion, '0.5.1', '<') :
            homesteadRunDbMigration('00-05-01.sql');
        case version_compare($currentVersion, '0.5.2', '<') :
            homesteadRunDbMigration('00-05-02.sql');
        case version_compare($currentVersion, '0.5.3', '<') :
            PHPWS_Core::initCoreClass('Module.php');
            $module = new \PHPWS_Module('pulse', false);
            $pulse_version = $module->getVersion();
            if (version_compare($pulse_version, '2.0.0', '<')) {
                $content[] = '<p style="color:red;font-weight:bold">Pulse needs to be upgraded.</p>';
            }
            homesteadRunDbMigration('00-05-03.sql');
        case version_compare($currentVersion, '0.5.4', '<') :
            homesteadRunDbMigration('00-05-04.sql');
        case version_compare($currentVersion, '0.5.5', '<') :
            homesteadRunDbMigration('00-05-05.sql');
        case version_compare($currentVersion, '0.5.6', '<') :
            homesteadRunDbMigration('00-05-06.sql');
        case version_compare($currentVersion, '0.5.7', '<') :
            homesteadRunDbMigration('00-05-07.sql');
        case version_compare($currentVersion, '0.5.8', '<'):
            homesteadRunDbMigration('00-05-08.sql');
        case version_compare($currentVersion, '0.5.9', '<'):
            homesteadRunDbMigration('00-05-09.sql');
        case version_compare($currentVersion, '0.5.10', '<'):
            homesteadRunDbMigration('00-05-10.sql');
        case version_compare($currentVersion, '0.5.11', '<'):
            homesteadRunDbMigration('00-05-11.sql');
        case version_compare($currentVersion, '0.5.12', '<'):
            homesteadRunDbMigration('00-05-12.sql');
        case version_compare($currentVersion, '0.5.13', '<'):
            homesteadRunDbMigration('00-05-13.sql');
        case version_compare($currentVersion, '0.5.21', '<'):
            homesteadRunDbMigration('00-05-21.sql');
    }

    return true;
}
