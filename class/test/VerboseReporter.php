<?php
require_once('simpletest/reporter.php');

class VerboseReporter extends HtmlReporter {
    function paintPass($message) {
        parent::paintPass($message);
        print '<div style="padding: 8px; margin-top: 1em; background-color: green; color: white;"><strong>Pass </strong>';
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print implode("-&gt;", $breadcrumb);
        print "-&gt;$message</div>\n";
    }

    function _getCss() {
        return parent::_getCss() . ' .pass { color: green; }';
    }
}

