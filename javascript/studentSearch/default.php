<?php

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

$studentSearch = PHPWS_SOURCE_HTTP . 'mod/hms/javascript/studentSearch/script.js';
\Layout::addJSHeader('<script type="text/javascript" src="' . PHPWS_SOURCE_HTTP . 'mod/hms/bower_components/typeahead.js/dist/typeahead.bundle.js"></script>');
\Layout::addJSHeader("<script type='text/javascript' src='$studentSearch'></script>");