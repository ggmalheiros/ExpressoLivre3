<?php
/**
 * Tine 2.0
 *
 * @package     Voipmanager Management
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Thomas Wadewitz <t.wadewitz@metaways.de>
 * @copyright   Copyright (c) 2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     
 *
 */


/**
 * Asterisk meetme sql backend
 *
 * @package  Voipmanager
 */
class Voipmanager_Backend_Asterisk_Meetme extends Tinebase_Application_Backend_Sql_Abstract
{    
    /**
     * the constructor
     * 
     * @param Zend_Db_Adapter_Abstract $_db
     */
    public function __construct($_db = NULL)
    {
        parent::__construct(SQL_TABLE_PREFIX . 'asterisk_meetme', 'Voipmanager_Model_Asterisk_Meetme', $_db);
    }
    
    /**
     * add the fields to search for to the query
     *
     * @param  Zend_Db_Select $_select current where filter
     * @param  Voipmanager_Model_Asterisk_MeetmeFilter $_filter the string to search for
     */
    protected function _addFilter(Zend_Db_Select $_select, Voipmanager_Model_Asterisk_MeetmeFilter $_filter = NULL)
    {
        if(!empty($_filter->query)) {
            $_select->where($this->_db->quoteInto('(' . $this->_db->quoteIdentifier('confno') . ' LIKE ? OR ' .
                    $this->_db->quoteIdentifier('pin') . ' LIKE ? OR ' .
                    $this->_db->quoteIdentifier('adminpin') . ' LIKE ?)', '%' . $_filter->query . '%'));
        }        
    }        
}
