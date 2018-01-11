<?php

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

$studentSearch = PHPWS_SOURCE_HTTP . 'mod/hms/javascript/studentSearch/script.js';
\Layout::addJSHeader('<script type="text/javascript" src="' . PHPWS_SOURCE_HTTP . 'mod/hms/node_modules/corejs-typeahead/dist/typeahead.bundle.js"></script>');
\Layout::addJSHeader("<script type='text/javascript' src='$studentSearch'></script>");
