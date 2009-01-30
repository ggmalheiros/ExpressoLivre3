<?php
/**
 * Tine 2.0
 * @package     Voipmanager Management
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Thomas Wadewitz <t.wadewitz@metaways.de>
 * @copyright   Copyright (c) 2007-2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id$
 * 
 */

/**
 * backend class for Zend_Json_Server
 *
 * This class handles all Json requests for the Voipmanager Management application
 *
 * @package     Voipmanager Management
 */
class Voipmanager_Frontend_Json extends Tinebase_Application_Frontend_Json_Abstract
{
    /**
     * the internal name of the application
     *
     * @var string
     */
    protected $_applicationName = 'Voipmanager';
    
/****************************************
 * SNOM PHONE / PHONESETTINGS FUNCTIONS
 *
 * 
 * 
 */
    
    /**
     * Search for records matching given arguments
     *
     * @param string $filter json encoded
     * @param string $paging json encoded
     * @return array
     */
    public function searchSnomPhones($filter, $paging)
    {
        $result = $this->_search($filter, $paging, Voipmanager_Controller_Snom_Phone::getInstance(), 'Voipmanager_Model_Snom_PhoneFilter');
        
        foreach ($result['results'] as &$phone) {
            // resolve location and template names
            $phoneTemplate = $this->getSnomTemplate($phone['template_id']);
            $phoneLocation = $this->getSnomLocation($phone['location_id']);
            
            if($location = Voipmanager_Controller_Snom_Location::getInstance()->get($phone['location_id'])) {
                $phone['location'] = $location->name;
            }
            
            if($template = Voipmanager_Controller_Snom_Template::getInstance()->get($phone['template_id'])) {
                $phone['template'] = $template->name;
            }                            
        }
        
        return $result;
    }
    
    /**
     * get one phone identified by phoneId
     *
     * @param int $phoneId
     * @return array
     */
    public function getSnomPhone($phoneId)
    {
        $record = Voipmanager_Controller_Snom_Phone::getInstance()->get($phoneId);        
        $result = $record->toArray();      
        return $result;        
    }    
    
    /**
     * save one phone
     * -  if $phoneData['id'] is empty the phone gets added, otherwise it gets updated
     *
     * @param string $phoneData a JSON encoded array of phone properties
     * @param string $lineData
     * @param string $rightsData
     * @return array
     */
    public function saveSnomPhone($phoneData, $lineData, $rightsData)
    {
        $phoneData  = Zend_Json::decode($phoneData);
        $lineData   = Zend_Json::decode($lineData);
        $rightsData = Zend_Json::decode($rightsData);
        
        // unset if empty
        if (empty($phoneData['id'])) {
            unset($phoneData['id']);
        }
        
        $phone = new Voipmanager_Model_Snom_Phone();
        $phone->setFromArray($phoneData);
        
        $phoneSettings = new Voipmanager_Model_Snom_PhoneSettings();
        $phoneSettings->setFromArray($phoneData);
        
        $phone->lines = new Tinebase_Record_RecordSet('Voipmanager_Model_Snom_Line', $lineData, true);
        $phone->rights = new Tinebase_Record_RecordSet('Voipmanager_Model_Snom_PhoneRight', $rightsData);
        $phone->settings = $phoneSettings;
        
        if (empty($phone->id)) {
            $phone = Voipmanager_Controller_Snom_Phone::getInstance()->create($phone);
        } else {
            $phone = Voipmanager_Controller_Snom_Phone::getInstance()->update($phone);
        }
        $phone = $this->getSnomPhone($phone->getId());

        /*
        foreach($ownerData AS $owner) {
            $owner['phone_id'] = $phone['id'];
            
            $_owner = new Voipmanager_Model_Snom_PhoneOwner();   
            $_owner->setFromArray($owner);
            
            $_ownerData[] = $_owner;
        }
        */
        return $phone;         
    }     
    
    /**
     * delete multiple phones
     *
     * @param array $_phoneIDs list of phoneId's to delete
     * @return array
     * 
     * @todo use generic _delete() function
     */
    public function deleteSnomPhones($_phoneIds)
    {
        $controller = Voipmanager_Controller_Snom_Phone::getInstance();
        
        $result = array(
            'success'   => TRUE
        );
        
        $ids = Zend_Json::decode($_phoneIds);
        Voipmanager_Controller_Snom_Phone::getInstance()->delete($ids);
        
        return $result;
    }    


    /**
     * send HTTP Client Info to multiple phones
     *
     * @param array $_phoneIDs list of phoneId's to send http client info to
     * @return array
     */      
    public function resetHttpClientInfo($_phoneIds)
    {
        $result = array(
            'success'   => TRUE
        );
        
        $phoneIds = Zend_Json::decode($_phoneIds);        
        
        Voipmanager_Controller_Snom_Phone::getInstance()->resetHttpClientInfo($phoneIds);
        
        return $result;
    }      
      
      
   /**
     * get one phoneSettings identified by phoneSettingsId
     *
     * @param int $phoneSettingsId
     * @return array
     */
    public function getSnomPhoneSettings($phoneSettingsId)
    {
        return $this->_get($phoneSettingsId, Voipmanager_Controller_Snom_PhoneSettings::getInstance());
    }              
    
    /**
     * save one phoneSettings
     *
     * if $phoneSettingsData['id'] is empty the phoneSettings gets added, otherwise it gets updated
     *
     * @param string $phoneSettingsData a JSON encoded array of phoneSettings properties
     * @return array
     */
    public function saveSnomPhoneSettings($phoneSettingsData)
    {
        return $this->_save($phoneSettingsData, Voipmanager_Controller_Snom_PhoneSettings::getInstance(), 'Snom_PhoneSettings', 'phone_id');       
    }

    /**
     * delete phoneSettings
     *
     * @param array $_phoneSettingsID phoneSettingsId to delete
     * @return array
     */
    public function deleteSnomPhoneSettings($_phoneSettingsId)
    {
        return $this->_delete($_phoneSettingsId, Voipmanager_Controller_Snom_PhoneSettings::getInstance());
    }
        
      
/********************************
 * SNOM LOCATION FUNCTIONS
 *
 * 
 */
    
    /**
     * Search for records matching given arguments
     *
     * @param string $filter json encoded
     * @param string $paging json encoded
     * @return array
     */
    public function searchSnomLocations($filter, $paging)
    {
        $result = $this->_search($filter, $paging, Voipmanager_Controller_Snom_Location::getInstance(), 'Voipmanager_Model_Snom_LocationFilter');
        return $result;
    }
    
    /**
     * get one location identified by locationId
     *
     * @param int $locationId
     * @return array
     */
    public function getSnomLocation($locationId)
    {
        return $this->_get($locationId, Voipmanager_Controller_Snom_Location::getInstance());
    }      


    /**
     * save one location
     *
     * if $locationData['id'] is empty the location gets added, otherwise it gets updated
     *
     * @param string $locationData a JSON encoded array of location properties
     * @return array
     */
    public function saveSnomLocation($locationData)
    {
        return $this->_save($locationData, Voipmanager_Controller_Snom_Location::getInstance(), 'Snom_Location');              
    }
     
        
    /**
     * delete multiple locations
     *
     * @param array $_locationIDs list of locationId's to delete
     * @return array
     */
    public function deleteSnomLocations($_locationIds)
    {
        return $this->_delete($_locationIds, Voipmanager_Controller_Snom_Location::getInstance());
    }        
        
      
      
/********************************
 * SNOM SOFTWARE FUNCTIONS
 *
 * 
 */
    
    /**
     * Search for records matching given arguments
     *
     * @param string $filter json encoded
     * @param string $paging json encoded
     * @return array
     */
    public function searchSnomSoftwares($filter, $paging)
    {
        $result = $this->_search($filter, $paging, Voipmanager_Controller_Snom_Software::getInstance(), 'Voipmanager_Model_Snom_SoftwareFilter');
        return $result;
    }
    
   /**
     * get one software identified by softwareId
     *
     * @param int $softwareId
     * @return array
     */
    public function getSnomSoftware($softwareId)
    {
        return $this->_get($softwareId, Voipmanager_Controller_Snom_Software::getInstance());
    }         


    /**
     * add/update software
     *
     * if $softwareData['id'] is empty the software gets added, otherwise it gets updated
     *
     * @param string $phoneData a JSON encoded array of software properties
     * @return array
     */
    public function saveSnomSoftware($softwareData)
    {
        return $this->_save($softwareData, Voipmanager_Controller_Snom_Software::getInstance(), 'Snom_Software');
    }     
      
      
    /**
     * delete multiple softwareversion entries
     *
     * @param array $_softwareIDs list of softwareId's to delete
     * @return array
     */
    public function deleteSnomSoftware($_softwareIds)
    {
        return $this->_delete($_softwareIds, Voipmanager_Controller_Snom_Software::getInstance());
    }       
    
    
    
/********************************
 * SNOM TEMPLATE FUNCTIONS
 *
 * 
 */
    
    /**
     * Search for records matching given arguments
     *
     * @param string $filter json encoded
     * @param string $paging json encoded
     * @return array
     */
    public function searchSnomTemplates($filter, $paging)
    {
        $result = $this->_search($filter, $paging, Voipmanager_Controller_Snom_Template::getInstance(), 'Voipmanager_Model_Snom_TemplateFilter');
        return $result;
    }
    
   /**
     * get one template identified by templateId
     *
     * @param int $templateId
     * @return array
     */
    public function getSnomTemplate($templateId)
    {
        return $this->_get($templateId, Voipmanager_Controller_Snom_Template::getInstance());
    }
             
    /**
     * add/update template
     *
     * if $templateData['id'] is empty the template gets added, otherwise it gets updated
     *
     * @param string $templateData a JSON encoded array of template properties
     * @return array
     */
    public function saveSnomTemplate($templateData)
    {
        return $this->_save($templateData, Voipmanager_Controller_Snom_Template::getInstance(), 'Snom_Template');               
    }     
    
    /**
     * delete multiple template entries
     *
     * @param array $_templateIDs list of templateId's to delete
     * @return array
     */
    public function deleteSnomTemplates($_templateIds)
    {
        return $this->_delete($_templateIds, Voipmanager_Controller_Snom_Template::getInstance());
    }     

/********************************
 * SNOM SETTING FUNCTIONS
 *
 * 
 */        
    
    /**
     * Search for records matching given arguments
     *
     * @param string $filter json encoded
     * @param string $paging json encoded
     * @return array
     */
    public function searchSnomSettings($filter, $paging)
    {
        $result = $this->_search($filter, $paging, Voipmanager_Controller_Snom_Setting::getInstance(), 'Voipmanager_Model_Snom_SettingFilter');
        return $result;
    }
    
   /**
     * get one setting identified by settingId
     *
     * @param int $settingId
     * @return array
     */
    public function getSnomSetting($settingId)
    {
        return $this->_get($settingId, Voipmanager_Controller_Snom_Setting::getInstance());
    }    
    
    /**
     * save one setting
     *
     * if $settingData['id'] is empty the setting gets added, otherwise it gets updated
     *
     * @param string $settingData a JSON encoded array of setting properties
     * @return array
     */
    public function saveSnomSetting($settingData)
    {
        return $this->_save($settingData, Voipmanager_Controller_Snom_Setting::getInstance(), 'Snom_Setting');
    }
    
   
    /**
     * delete multiple settings
     *
     * @param array $_settingIDs list of settingId's to delete
     * @return array
     */
    public function deleteSnomSettings($_settingIds)
    {
        return $this->_delete($_settingIds, Voipmanager_Controller_Snom_Setting::getInstance());
    }         

    
/********************************
 * ASTERISK CONTEXT FUNCTIONS
 *
 * 
 */    

    /**
     * Search for records matching given arguments
     *
     * @param string $filter json encoded
     * @param string $paging json encoded
     * @return array
     */
    public function searchAsteriskContexts($filter, $paging)
    {
        return $this->_search($filter, $paging, Voipmanager_Controller_Asterisk_Context::getInstance(), 'Voipmanager_Model_Asterisk_ContextFilter');
    }
    
   /**
     * get one context identified by contextId
     *
     * @param int $contextId
     * @return array
     */
    public function getAsteriskContext($contextId)
    {
        return $this->_get($contextId, Voipmanager_Controller_Asterisk_Context::getInstance());
    }    
    
    
    /**
     * save one context
     *
     * if $contextData['id'] is empty the context gets added, otherwise it gets updated
     *
     * @param string $contextData a JSON encoded array of context properties
     * @return array
     */
    public function saveAsteriskContext($contextData)
    {
        return $this->_save($contextData, Voipmanager_Controller_Asterisk_Context::getInstance(), 'Asterisk_Context');      
    }     
    
    
     /**
     * delete multiple contexts
     *
     * @param array $_contextIDs list of contextId's to delete
     * @return array
     */
    public function deleteAsteriskContexts($_contextIds)
    {
        return $this->_delete($_contextIds, Voipmanager_Controller_Asterisk_Context::getInstance());
    }    
       
/********************************
 * ASTERISK MEETME FUNCTIONS
 *
 * 
 */        
    
    /**
     * Search for records matching given arguments
     *
     * @param string $filter json encoded
     * @param string $paging json encoded
     * @return array
     */
    public function searchAsteriskMeetmes($filter, $paging)
    {
        return $this->_search($filter, $paging, Voipmanager_Controller_Asterisk_Meetme::getInstance(), 'Voipmanager_Model_Asterisk_MeetmeFilter');
    }
    
   /**
     * get one meetme identified by meetmeId
     *
     * @param int $meetmeId
     * @return array
     */
    public function getAsteriskMeetme($meetmeId)
    {
        return $this->_get($meetmeId, Voipmanager_Controller_Asterisk_Meetme::getInstance());
    }    
    
    
    /**
     * save one meetme
     *
     * if $meetmeData['id'] is empty the meetme gets added, otherwise it gets updated
     *
     * @param string $meetmeData a JSON encoded array of meetme properties
     * @return array
     */
    public function saveAsteriskMeetme($meetmeData)
    {
        return $this->_save($meetmeData, Voipmanager_Controller_Asterisk_Meetme::getInstance(), 'Asterisk_Meetme');
    }     
    
    /**
     * delete multiple meetmes
     *
     * @param array $_meetmeIDs list of meetmeId's to delete
     * @return array
     */
    public function deleteAsteriskMeetmes($_meetmeIds)
    {
        return $this->_delete($_meetmeIds, Voipmanager_Controller_Asterisk_Meetme::getInstance());
    }     
    
/********************************
 * ASTERISK SIP PEER FUNCTIONS
 *
 * 
 */    

    /**
     * Search for records matching given arguments
     *
     * @param string $filter json encoded
     * @param string $paging json encoded
     * @return array
     */
    public function searchAsteriskSipPeers($filter, $paging)
    {
        return $this->_search($filter, $paging, Voipmanager_Controller_Asterisk_SipPeer::getInstance(), 'Voipmanager_Model_Asterisk_SipPeerFilter');
    }
    
   /**
     * get one asterisk sip peer identified by sipPeerId
     *
     * @param int $sipPeerId
     * @return array
     */
    public function getAsteriskSipPeer($sipPeerId)
    {
        return $this->_get($sipPeerId, Voipmanager_Controller_Asterisk_SipPeer::getInstance());       
    }
          
             
    /**
     * add/update asterisk sip peer
     *
     * if $sipPeerData['id'] is empty the sip peer gets added, otherwise it gets updated
     *
     * @param string $sipPeerData a JSON encoded array of sipPeer properties
     * @return array
     */
    public function saveAsteriskSipPeer($sipPeerData)
    {
        return $this->_save($sipPeerData, Voipmanager_Controller_Asterisk_SipPeer::getInstance(), 'Asterisk_SipPeer');       
    }     
    

    /**
     * delete multiple asterisk sip peers
     *
     * @param array $_sipPeerIDs list of sipPeerId's to delete
     * @return array
     */
    public function deleteAsteriskSipPeers($_sipPeerIds)
    {
        return $this->_delete($_sipPeerIds, Voipmanager_Controller_Asterisk_SipPeer::getInstance());
    }     
    
/********************************
 * ASTERISK VOICEMAIL FUNCTIONS
 *
 * 
 */        
    
    /**
     * Search for records matching given arguments
     *
     * @param string $filter json encoded
     * @param string $paging json encoded
     * @return array
     */
    public function searchAsteriskVoicemails($filter, $paging)
    {
        return $this->_search($filter, $paging, Voipmanager_Controller_Asterisk_Voicemail::getInstance(), 'Voipmanager_Model_Asterisk_VoicemailFilter');
    }
    
   /**
     * get one voicemail identified by voicemailId
     *
     * @param int $voicemailId
     * @return array
     */
    public function getAsteriskVoicemail($voicemailId)
    {     
        return $this->_get($voicemailId, Voipmanager_Controller_Asterisk_Voicemail::getInstance());
    }        
    
    /**
     * save one voicemail
     *
     * if $voicemailData['id'] is empty the voicemail gets added, otherwise it gets updated
     *
     * @param string $voicemailData a JSON encoded array of voicemail properties
     * @return array
     */
    public function saveAsteriskVoicemail($voicemailData)
    {
        return $this->_save($voicemailData, Voipmanager_Controller_Asterisk_Voicemail::getInstance(), 'Asterisk_Voicemail');
    }     
    
   
    /**
     * delete multiple voicemails
     *
     * @param array $_voicemailIDs list of voicemailId's to delete
     * @return array
     */
    public function deleteAsteriskVoicemails($_voicemailIds)
    {
        return $this->_delete($_voicemailIds, Voipmanager_Controller_Asterisk_Voicemail::getInstance());
    }
}
