<?php
/**
 * Tine 2.0 - http://www.tine20.org
 * 
 * @package     Tinebase
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 * @version     $Id$
 */

/**
 * Test helper
 */
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';
if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Tinebase_User_AllTests::main');
}
class Tinebase_User_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Tine 2.0 Tinebase All User Tests');
        $suite->addTestSuite('Tinebase_User_SqlTest');
        $suite->addTestSuite('Tinebase_User_RegistrationTest');
        $suite->addTestSuite('Tinebase_User_ModelTest');
        $suite->addTestSuite('Tinebase_User_AbstractTest');
        
        $imapConfig = Tinebase_Config::getInstance()->getConfigAsArray('Felamimail_Imap_Config', 'Felamimail');
        if (! empty($imapConfig) && ucfirst($imapConfig['backend']) == Tinebase_EmailUser::DBMAIL) {
            $suite->addTestSuite('Tinebase_User_EmailTest');
        }
        return $suite;
    }
}
if (PHPUnit_MAIN_METHOD == 'Tinebase_User_AllTests::main') {
    Tinebase_User_AllTests::main();
}
