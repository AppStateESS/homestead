<?php

namespace Homestead;

/**
 *
 * A generic implementation of the Report class.
 *
 * This is necessary because the Report class must be abstract, but the
 * Database and DBPager classes need a concrete class.
 *
 * @author jbooker
 * @package HMS
 */

class GenericReport extends Report {

    const friendlyName = 'Generic Report';
    const shortName    = 'GenericReport';

    // Shouldn't ever be used
    public function execute()
    {
        throw new InvalidArgumentException("Shouldn't be here...");
    }
}
