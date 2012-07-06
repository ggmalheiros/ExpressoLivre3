<?php
/**
 * Tine 2.0
 *
 * @package     Felamimail
 * @subpackage  Backend
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Cassiano Dal Pizzol <cassiano.dalpizzol@serpro.gov.br>
 * @author      Bruno Costa Vieira <bruno.vieira-costa@serpro.gov.br>
 * @author      Mario Cesar Kolling <mario.kolling@serpro.gov.br>
 * @copyright   Copyright (c) 2009-2013 Serpro (http://www.serpro.gov.br)
 *
 */

class Felamimail_Backend_Cache_Imap_Message extends Felamimail_Backend_Cache_Imap_Abstract
                                                implements Felamimail_Backend_Cache_MessageInterface
{
    
       // Probably we'll never use this attribute
//    /**
//     * Table name without prefix
//     *
//     * @var string
//     */
//    protected $_tableName = 'felamimail_cache_message';
    
    /**
     * Model name
     *
     * @var string
     */
    protected $_modelName = 'Felamimail_Model_Message';
    
    /**
    * default column(s) for count
    *
    * @var string
    */
    protected $_defaultCountCol = 'id';
    
    /**
     * foreign tables (key => tablename)
     *
     * @var array
     */
    protected $_foreignTables = array(
        'to'    => array(
            'table'     => 'felamimail_cache_message_to',
            'joinOn'    => 'message_id',
            'field'     => 'email',
            'preserve'  => TRUE,
        ),
        'cc'    => array(
            'table'  => 'felamimail_cache_message_cc',
            'joinOn' => 'message_id',
            'field'  => 'email',
            'preserve'  => TRUE,
        ),
        'bcc'    => array(
            'table'  => 'felamimail_cache_message_bcc',
            'joinOn' => 'message_id',
            'field'  => 'email',
            'preserve'  => TRUE,
        ),
        'flags'    => array(
            'table'         => 'felamimail_cache_message_flag',
            'joinOn'        => 'message_id',
            'field'         => 'flag',
            'preserve'  => TRUE,
        ),
    );
    
    // Use imap search command or imap list?
    /**
     * get type of imap command to use
     * @param type $_filter
     * @return boolean 
     */
    protected function _isSearh($_filter)
    {
        
        $return = false;
        $filters = $_filter[0]['filters'];
        foreach ($filters as $filter)
        {
            
            switch ($filter['field'])
            {
            
                // TODO: do search in case of flags???
                // TODO: we'll have cases with id, account_id, folder_id and messageuid?
                case 'query' :
                case 'subject' :
                case 'from_email' :
                case 'from_name' :
                case 'received' :
                case 'to' :
                case 'cc' :
                case 'bcc' :
                    if (!empty($filter['value']))
                    {
                        $return = true;
                    }
                    break;
            }
        }
        
        return $return;
        
    }
    
    /**
     * find existance of values recursivelly on array
     * @param array $array
     * @param type $search
     * @param type $mode
     * @return boolean
     * 
     * @todo implement it as a static method on a helper class (Tinebase_Helper)
     */
    protected function _searchNestedArray(array $array, $search, $mode = 'value') {

        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $value) {
            if ($search === ${${"mode"}})
                return true;
        }
        return false;
    }

    /**
     * get all folders globalname and accountId
     * 
     * @param type $_filter
     * @return array
     */
    protected function _processPathFilters($_filter)
    {
        $paths = array();
        $filters = $_filter[0]['filters'];
        //iterates till we only have the user and folder
        if (!empty($filters) && $this->_searchNestedArray($filters, 'path'))
        {
            foreach ($filters as $filter)
            {
                if ($filter['field'] === 'path' && !empty($filter['value']))
                {
                    $paths = array_merge($paths, $filter['value']);
                }
            }
        }
        
        $return = array();
        foreach ($paths as $tmp)
        {
            $tmp = explode(self::IMAPDELIMITER, $tmp);
            
            if (empty($tmp[0]))
            {
                array_shift($tmp);
            }
            
            $userId = array_shift($tmp);
            
            $folderId = array_pop($tmp);
            $folder = Felamimail_Controller_Folder::getInstance()->get($folderId);
            $folderArray = $folder->toArray();
            
            //list($userId, $folderId) = $path;
            
            $return[$folderId] = array($userId, $folderArray['globalname']);
        }
        
        return $return;
    }
    
    /**
     * Generate all the necessary imap filters to be processed by the search method
     * 
     * @param array $_filterArray
     * @param Tinebase_Model_Pagination $_pagination 
     * 
     * @todo implement all possible filters
     */
    protected function _generateImapFilter(array $_filterArray, Tinebase_Model_Pagination $_pagination = NULL){
        
        $paginationAttr = $_pagination->toArray();
        $filters = $_filterArray['filters'];
        Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message Search = $_pagination' . print_r($_pagination,true));
        
    }
    
    /**
     * Get filter model and pagination model and parse the rules
     * 
     * @param Tinebase_Model_Filter_FilterGroup $_filter
     * @param Tinebase_Model_Pagination $_pagination
     * @return string 
     */
    protected function _parseFilterGroup(Tinebase_Model_Filter_FilterGroup $_filter = NULL,
                                            Tinebase_Model_Pagination $_pagination = NULL)
    {        
        //setup the return
        $return = null;
        
        //get the filter array
        $filterArray = $_filter->toArray();
        // Ignorando filtros 'OR'
        if (!empty($filterArray) && !empty($filterArray[0]))
        {
            $return = array();
            $filterArray = $filterArray[0];
            if (strtolower($filterArray['condition']) === 'or')
            {
                if (!empty($filterArray['filters']))
                {
                    $filterArray = $filterArray['filters'];
                }
                else
                {
                    $filterArray = null;
                }                
            }
            
            $pathFilters = $this->_processPathFilters($filterArray);
            
            if (empty($pathFilters))
            {
                // TODO:
                // $path_filters = $this->_getAllFolders();
            }
            
            $return['paths'] = $pathFilters; // array with folder globalnames
            
            // find out if we're just listing folders or doing some search
            if ($this->_isSearh($filterArray))
            {
                $return['command'] = 'search';
                $return['filter'] = $this->_generateImapFilter($filterArray, $_pagination);
            }
            else
            {
                $return['command'] = 'list';
            }
        }
        
        return $return;
    }
    
    /**
     * create new message for the cache
     * 
     * @param array $_message
     * @param Felamimail_Model_Folder $_folder
     * @return Felamimail_Model_Message
     * 
     * @todo use controller to generate the message
     */
    protected function _createModelMessageArray(array $_messages, Felamimail_Model_Folder $_folder)
    {
        
        $return = array();
        foreach ($_messages as $uid => $msg)
        {
            
            $message = new Felamimail_Model_Message(array(
                'account_id'    => $_folder->account_id,
                'messageuid'    => $uid,
                'folder_id'     => $_folder->getId(),
                'timestamp'     => Tinebase_DateTime::now(),
                'received'      => Felamimail_Message::convertDate($msg['received']),
                'size'          => $msg['size'],
                'flags'         => $msg['flags'],
            ));

            $message->parseStructure($msg['structure']);
            $message->parseHeaders($msg['header']);
            $message->parseBodyParts();
            $message->parseSmime($msg['structure']);

            $attachments = Felamimail_Controller_Message::getInstance()->getAttachments($message);
            $message->has_attachment = (count($attachments) > 0) ? true : false;
            
            $return[$uid] = $message;

        }
        
        return $return;
    }
    
    /*************************** abstract functions ****************************/
    /**
     * Search for records matching given filter
     *
     * @param  Tinebase_Model_Filter_FilterGroup    $_filter
     * @param  Tinebase_Model_Pagination            $_pagination
     * @param  array|string|boolean                 $_cols columns to get, * per default / use self::IDCOL or TRUE to get only ids
     * @return Tinebase_Record_RecordSet|array
     *
     * @todo implement sort for more than one path
     * @todo implement other searches
     */
    public function search(Tinebase_Model_Filter_FilterGroup $_filter = NULL, Tinebase_Model_Pagination $_pagination = NULL, $_cols = '*')    
    {
/*        
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message Search = $_filter ' . print_r($_filter,true));
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message Search = $_pagination' . print_r($_filter,true));
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message Search = $_cols' . print_r($_cols,true));
*/  
        
        $return = null;
        $messagesArray = array();
        $messages = array();
        $imapFilters = $this->_parseFilterGroup($_filter, $_pagination);
        
        if (!(empty($imapFilters['paths'])))
        {
            foreach ($imapFilters['paths'] as $folderId => $path)
            {
                list($account, $mailbox) = $path;
                //$imapStream = $this->_getImapConnection($account, $mailbox);
                
                $imap = Felamimail_Backend_ImapFactory::factory($account);
                $imap->selectFolder($mailbox);
                $paginationAttr = $_pagination->toArray();
                
                // TODO: Probably we won't diferentiate from list and search
                if ($imapFilters['command'] == 'list')
                {
                    
                    // TODO: use $paginationAttr
                    // TODO: if sort method not compatible with IMAP sort extension
                    $sorted = $imap->sort(array('ARRIVAL'));
                    
                    Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Imap Sort = $sorted ' . print_r($sorted,true));
                    
                    $chunked = array_chunk($sorted, $paginationAttr['limit'], true);
                    $chunkIndex = ($paginationAttr['start']/$paginationAttr['limit']);
                    
                    $folder = Felamimail_Controller_Folder::getInstance()->get($folderId);

                    $messagesArray[$mailbox] = $this->_createModelMessageArray($imap->getSummary($chunked[$chunkIndex]), $folder);
                    
                }
                else
                {

                }
                
                // TODO: For more than one path we will have to concat and reorder

            }
            
            foreach ($messagesArray as $tmp)
            {
                $messages = array_merge($messages, $tmp);
            }
            
            // Put headers into model
            $return =  $this->_rawDataToRecordSet($messages);
            
        }
        
        if (!empty($return) && $return instanceof TineBase_Record_RecordSet)
        {
            return $return;
        }
        
        Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Could\'nt use Imap directly' . print_r($return,true));
        $aux = new Felamimail_Backend_Cache_Sql_Message();           
        $return = $aux->search($_filter,$_pagination, $_cols);
        
//Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message Search = $retorno' . print_r($retorno,true));
        
    }

    /**
     * Updates existing entry
     *
     * @param Tinebase_Record_Interface $_record
     * @throws Tinebase_Exception_Record_Validation|Tinebase_Exception_InvalidArgument
     * @return Tinebase_Record_Interface Record|NULL
     */
    public function update(Tinebase_Record_Interface $_record)
    {
/*        
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message update = $_record ' . print_r($_record,true));
*/ 
        $aux = new Felamimail_Backend_Cache_Sql_Message();        
        $retorno = $aux->update($_record);
        
//Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . 'Message update = $retorno ' . print_r($retorno,true));
        return $retorno;        
    }
    
    
    /**
     * Gets total count of search with $_filter
     * 
     * @param Tinebase_Model_Filter_FilterGroup $_filter
     * @return int
     */
    public function searchCount(Tinebase_Model_Filter_FilterGroup $_filter)
    {
/*        
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message searchCount = $_filter ' . print_r($_filter,true));
*/  
        $aux = new Felamimail_Backend_Cache_Sql_Message();
        $retorno = $aux->searchCount($_filter);
//Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . 'Message searchCount = $retorno ' . print_r($retorno,true));
        return $retorno;
    }
    
    /**
     * Gets one entry (by id)
     *
     * @param integer|Tinebase_Record_Interface $_id
     * @param $_getDeleted get deleted records
     * @return Tinebase_Record_Interface
     * @throws Tinebase_Exception_NotFound
     */
    public function get($_id, $_getDeleted = FALSE) 
    {
/*        
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message get = $_id ' . print_r($_id,true));
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message get = $_getDeleted' . print_r($_getDeleted,true));
*/ 
        $aux = new Felamimail_Backend_Cache_Sql_Message();        
        $retorno = $aux->get($_id, $_getDeleted);
        
//Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . 'Message get = get ' . print_r($retorno,true));
        return $retorno;
    }
    
     /**
      * Deletes entries
      * 
      * @param string|integer|Tinebase_Record_Interface|array $_id
      * @return void
      * @return int The number of affected rows.
      */
    public function delete($_id) 
    {
/*        
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message delete = $_id ' . print_r($_id,true));
*/ 
        $aux = new Felamimail_Backend_Cache_Sql_Message();        
        $retorno = $aux->delete($_id);
        
//Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . 'Message delete = $retorno ' . print_r($retorno,true));
        return $retorno;
    }
    
    /**
     * Get multiple entries
     *
     * @param string|array $_id Ids
     * @param array $_containerIds all allowed container ids that are added to getMultiple query
     * @return Tinebase_Record_RecordSet
     * 
     * @todo get custom fields here as well
     */
    public function getMultiple($_id, $_containerIds = NULL) 
    {
/*        
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message getMultiple = $_id ' . print_r($_id,true));
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message getMultiple = $_containerIds ' . print_r($_containerIds,true));
*/ 
        $aux = new Felamimail_Backend_Cache_Sql_Message();        
        $retorno = $aux->getMultiple($_id, $_containerIds = NULL);
        
//Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . 'Message delete = $retorno ' . print_r($retorno,true));
        return $retorno;
    }

    /**
     * Creates new entry
     *
     * @param   Tinebase_Record_Interface $_record
     * @return  Tinebase_Record_Interface
     * @throws  Tinebase_Exception_InvalidArgument
     * @throws  Tinebase_Exception_UnexpectedValue
     * 
     * @todo    remove autoincremental ids later
     */
    public function create(Tinebase_Record_Interface $_record)
    {
/*        
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message create = $_record ' . print_r($_record,true));
*/ 
        $aux = new Felamimail_Backend_Cache_Sql_Message();        
        $retorno = $aux->create($_record);
        
//Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . 'Message create = $retorno ' . print_r($retorno,true));
        return $retorno;        
    }
    
    
    /*************************** interface functions ****************************/
    /**
     * Search for records matching given filter
     *
     * @param  Tinebase_Model_Filter_FilterGroup    $_filter
     * @param  Tinebase_Model_Pagination            $_pagination
     * @return array
     */
    public function searchMessageUids(Tinebase_Model_Filter_FilterGroup $_filter = NULL, Tinebase_Model_Pagination $_pagination = NULL)    
    {
        return $this->search($_filter, $_pagination, array(Tinebase_Backend_Sql_Abstract::IDCOL, 'messageuid'));
    }
    
    /**
     * get all flags for a given folder id
     *
     * @param string|Felamimail_Model_Folder $_folderId
     * @param integer $_start
     * @param integer $_limit
     * @return Tinebase_Record_RecordSet
     */
    public function getFlagsForFolder($_folderId, $_start = NULL, $_limit = NULL)    
    {
        $filter = $this->_getMessageFilterWithFolderId($_folderId);
        $pagination = ($_start !== NULL || $_limit !== NULL) ? new Tinebase_Model_Pagination(array(
            'start' => $_start,
            'limit' => $_limit,
        ), TRUE) : NULL;
        
        return $this->search($filter, $pagination, array('messageuid' => 'messageuid', 'id' => Tinebase_Backend_Sql_Abstract::IDCOL, 'flags' => 'felamimail_cache_message_flag.flag'));
    }
    
    /**
     * add flag to message
     *
     * @param Felamimail_Model_Message $_message
     * @param string $_flag
     */
    public function addFlag($_message, $_flag)
    {
/*        
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message addFlag = $_message ' . print_r($_message,true));
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message addFlag = $_flag ' . print_r($_flag,true));
*/ 
        $aux = new Felamimail_Backend_Cache_Sql_Message();        
        $aux->addFlag($_message, $_flag);
    }
    
    /**
     * set flags of message
     *
     * @param  mixed         $_messages array of ids, recordset, single message record
     * @param  string|array  $_flags
     */
    public function setFlags($_messages, $_flags, $_folderId = NULL)
    {
/*        
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message setFlags = $_message ' . print_r($_message,true));
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message setFlags = $_flags ' . print_r($_flags,true));
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message setFlags = $_folderId ' . print_r($_folderId,true));
*/ 
        $aux = new Felamimail_Backend_Cache_Sql_Message();        
        $aux->setFlags($_messages, $_flags, $_folderId);
    }
    
    /**
     * remove flag from messages
     *
     * @param  mixed  $_messages
     * @param  mixed  $_flag
     */
    public function clearFlag($_messages, $_flag)
    {
/*        
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message clearFlag = $_message ' . print_r($_message,true));
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message clearFlag = $_flag ' . print_r($_flag,true));
*/ 
        $aux = new Felamimail_Backend_Cache_Sql_Message();        
        $aux->clearFlag($_messages, $_flag);
    }
    
    /**
     * Does nothing in this backend. It's necessary for the interface though.
     *
     * @param  mixed  $_folderId
     */
    public function deleteByFolderId($_folderId)
    {
        /**
         *TODO: remove the rest of the function  
         */
        $aux = new Felamimail_Backend_Cache_Sql_Message();        
        $aux->deleteByFolderId($_folderId);
    }

    /**
     * get count of cached messages by folder (id) 
     *
     * @param  mixed  $_folderId
     * @return integer
     */
    public function searchCountByFolderId($_folderId)
    {
/*        
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message searchCountByFolderId = $_folderId ' . print_r($_folderId,true));
*/ 
        $aux = new Felamimail_Backend_Cache_Sql_Message();        
        $retorno = $aux->searchCountByFolderId($_folderId);
        
//Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . 'Message searchCountByFolderId = $retorno ' . print_r($retorno,true));
        return $retorno;      
    }
    
    /**
     * get count of seen cached messages by folder (id) 
     *
     * @param  mixed  $_folderId
     * @return integer
     * 
     */
    public function seenCountByFolderId($_folderId)
    {
/*        
Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . ' Message seenCountByFolderId = $_folderId ' . print_r($_folderId,true));
*/ 
        $aux = new Felamimail_Backend_Cache_Sql_Message();        
        $retorno = $aux->seenCountByFolderId($_folderId);
        
//Tinebase_Core::getLogger()->alert(__METHOD__ . '#####::#####' . __LINE__ . 'Message seenCountByFolderId = $retorno ' . print_r($retorno,true));
        return $retorno;            
    }
    
    /**
     * delete messages with given messageuids by folder (id)
     *
     * @param  array  $_msguids
     * @param  mixed  $_folderId
     * @return integer number of deleted rows or false if no message are given
     */
    public function deleteMessageuidsByFolderId($_msguids, $_folderId)
    {
        $return = FALSE;        
        if (!(empty($_msguids) || !is_array($_msguids)))
        {
            Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' Logicaly delete the messages ' 
                                                                                            . print_r($_msguids, true));
            $return = count($_msguids);
        }
        //return $return;
        /**
         * TODO: remove the code below and uncomment the code above
         */
        $aux = new Felamimail_Backend_Cache_Sql_Message();        
        $retorno = $aux->deleteMessageuidsByFolderId($_msguids, $_folderId);
        return $retorno;
    }
    
/********************************************** protected functions ***************************************************/

    /**
     * converts raw data from adapter into a single record
     *
     * @param  array $_rawData
     * @return Tinebase_Record_Abstract
     */
    protected function _rawDataToRecord(array $_rawData)
    {
        if (isset($_rawData['structure'])) {
            $_rawData['structure'] = Zend_Json::decode($_rawData['structure']);
        }
        
        $result = parent::_rawDataToRecord($_rawData);
                
        return $result;
    }
    
    /**
     * converts raw data from adapter into a set of records
     *
     * @param  array $_rawDatas of arrays
     * @return Tinebase_Record_RecordSet
     */
    protected function _rawDataToRecordSet(array $_rawDatas)
    {
        
        $result = parent::_rawDataToRecordSet($_rawDatas);
        
        return $result;
    }
    
    /**
     * converts record into raw data for adapter
     *
     * @param  Tinebase_Record_Abstract $_record
     * @return array
     */
    protected function _recordToRawData($_record)
    {
        $result = parent::_recordToRawData($_record);
        
        if(isset($result['structure'])) {
            $result['structure'] = Zend_Json::encode($result['structure']);
        }
        
        return $result;
    }  
    
    /**
     * get folder id message filter
     * 
     * @param mixed $_folderId
     * @return Felamimail_Model_MessageFilter
     */
    protected function _getMessageFilterWithFolderId($_folderId)
    {
        $folderId = ($_folderId instanceof Felamimail_Model_Folder) ? $_folderId->getId() : $_folderId;
        $filter = new Felamimail_Model_MessageFilter(array(
            array('field' => 'folder_id', 'operator' => 'equals', 'value' => $folderId)
        ));
        
        return $filter;
    }
}