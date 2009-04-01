<?php
/**
 * Tine 2.0
 *
 * @package     ExampleApplication
 * @subpackage  Backend
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Philipp Schuele <p.schuele@metaways.de>
 * @copyright   Copyright (c) 2009 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id:Category.php 5576 2008-11-21 17:04:48Z p.schuele@metaways.de $
 */


/**
 * backend for ExampleRecords
 *
 * @package     ExampleApplication
 * @subpackage  Backend
 */
class ExampleApplication_Backend_ExampleRecord extends Tinebase_Backend_Sql_Abstract
{
    /**
     * Table name without prefix
     *
     * @var string
     */
    protected $_tableName = 'application_record';
    
    /**
     * Model name
     *
     * @var string
     */
    protected $_modelName = 'ExampleApplication_Model_ExampleRecord';

    /**
     * if modlog is active, we add 'is_deleted = 0' to select object in _getSelect()
     *
     * @var boolean
     */
    protected $_modlogActive = TRUE;

    /************************ overwritten functions *******************/  
    
    /************************ helper functions ************************/
}
