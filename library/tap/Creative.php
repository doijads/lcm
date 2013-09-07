<?php
/**
 *
 * show-off @property
 * @property int $id
 * @property int $campaignId
 * @property int $parentId
 * @property string $name
 * @property string $clickurl
 * @property string $tracking_url
 * @property int $rotateUrls
 * @property int $weight
 * @property float $weightPercentage
 * @property string $imageurl
 * @property string $adtext
 * @property int $creativeTypeId
 * @property int $sourceTypeId
 * @property int $width
 * @property int $height
 * @property int $actionTypeId
 * @property int $targetProfileId
 * @property int $statusId
 * @property string $created
 * @property string $modified
 * @property int $targetLocationTypeId
 * @property int $targetDeviceTypeId
 * @property int $targetTrafficTypeId
 * @property int $targetChannelTypeId
 * @property int $cpcBid
 * @property int $excludeSiteIds
 * @property int $iconId
 * @property int $customizePlatforms
 * @property int $excludeOperaMiniTraffic
 */
class Model_Creative extends App_Model
{
	
    protected $_tableClass = 'Model_Creative_DbTable';

    protected $_data = array(
        'id' => null,
        'campaign_id' => null,
        'parent_id' => null,
        'name' => null,
        'clickurl' => null,
        'tracking_url' => null,
        'rotate_urls' => 2,
        //'weight' => 0,
        'weight' => 1.00,
        'weight_percentage' => 0,
        'imageurl' => '',
        'adtitle' => '',
        'adtext' => '',
        'adimage' => '',
        'adheadline' => '',
        'adcopy' => '',
        'adsize' => '',
        'adtitlecolor' => '',
        'adtextcolor' => '',
        'adbordercolor' => '',
        'adbackgroundcolor' => '',
        'adtitlefont' => '',
        'adtextfont' => '',
        'adtitlefontsize' => '',
        'adtextfontsize' => '',
        'html' => '',
        'is_richmedia' => 0,
        'creative_type_id' => null,
        'source_type_id' => 1,
        'action_type_id' => null,
        'environment_type_id' => 1,
        'target_location_type_id' => 1,
        'target_device_type_id' => 1,
        'target_traffic_type_id' => 1,
        'include_wifi_traffic' => 0,
        //'exclude_opera_mini_traffic' => 0,
        'require_udid' => 0,
        'target_channel_type_id' => 1,
        'cpc_bid' => 0.1,
        'exclude_site_ids' => 0,
        'creative_icon_id' => null,
        'customize_platforms' => '2',
        'ad_alert_text' => '',
        'ad_alert_no_text' => '',
        'ad_alert_yes_text' => '',
        'status_id' => 1,        
        'created' => null,
        'modified' => null
    );


    protected $_reportRow = null;

    /**
     * @var Model_CreativeDimension_List
     */
    protected $_dimensionsList = null;

    /**
     * @return Default_Model_Report_AdvertiserRow
     */
    public function getReportRow()
    {
        if(!($this->_reportRow instanceof Default_Model_Report_AdvertiserRow)) {
            $this->_reportRow = new Default_Model_Report_AdvertiserRow();
        }

        return $this->_reportRow;
    }

    public function setReportRow(Default_Model_Report_AdvertiserRow $row)
    {
        $this->_reportRow = $row;
    }


    public function save()
    {
        $this->created = new Zend_Db_Expr('NOW()');
        return parent::save();
    }

    public function update($data = null)
    {
        $this->modified = null;
        return parent::update();
    }

    /**
     * @return Model_Campaign
     */
    public function loadCampaign()
    {
        $campaign = new Model_Campaign();
        $campaign->id = $this->campaignId;
        $campaign->find();
        return $campaign;
    }


    /**
     * @return Model_CreativeType
     */
    public function loadCreativeType()
    {
        $model = new Model_CreativeType();
        $model->id = $this->creativeTypeId;
        $model->find();

        return $model;
    }

    /**
     * @return Model_ActionType
     */
    public function loadActionType()
    {
        $model = new Model_ActionType();
        $model->id = $this->actionTypeId;
        $model->find();

        return $model;
    }

    public function loadDestinationsList()
    {
        $list = new Model_CreativeDestination_List(false);
        $list->setWhere('creative_id = ' . $this->id);
        $list->initDefault();

        return $list;
    }

    public function loadDimensions()
    {
        $list = new Model_CreativeDimension_List(false);
        $list->setWhere('creative_id = ' . $this->id);
        $list->initDefault();
        return $list;
    }


    public function removeDimensionsInArray(array $ids)
    {
        $model = new Model_CreativeDimension();
        $table = $model->getTable();
        $adapter = $table->getAdapter();

        $where = 'creative_id = ' . $this->id;
        $where.= ' AND dimension_type_id ' . $adapter->quoteInto('IN(?)', $ids, Zend_Db::INT_TYPE);

        return $table->delete($where);
    }


    /**
     * @return Model_CreativeDimension_List
     */
    public function getDimensionsList()
    {
        if(!($this->_dimensionsList instanceof Model_CreativeDimension_List)) {
            $this->_dimensionsList = new Model_CreativeDimension_List(false);
        }

        return $this->_dimensionsList;
    }

    public function setDimensionsList(Model_CreativeDimension_List $list)
    {
        $this->_dimensionsList = $list;
    }


    public function massUpdateStatus($status, $ids)
    {
        $table = $this->getTable();

        return $table->update(
            array('status_id' => $status),
            'id IN('. $table->getAdapter()->quoteInto('?', $ids).')'
        );
    }

    /**
     * @return bool
     */
    public function isChild()
    {
        return $this->parentId != null;
    }


    public function validateIdsArray($ids)
    {
        foreach ($ids as $key => $id) {
            $ids[$key] = (int) $id;
        }

        return $ids;
    }


    /**
     *
     * @return Model_CreativeDestination_List
     */
    public function getDestinations()
    {
        $list = new Model_CreativeDestination_List(false);
        $list->setWhere('creative_id = ' . $this->id);
        return $list->initDefault();
    }

    /**
     *
     * @return int number rows deleted
     */
    public function clearDestinations()
    {
        $model = new Model_CreativeDestination();
        return $model->getTable()->delete('creative_id = ' . $this->id);
    }

    /**
     *
     * @param int $creativeIds
     * @return array
     */
    public function deleteChildCreativesNotIn($creativeIds)
    {
        $creativeIds = is_array($creativeIds) ? $creativeIds : array();
        if(count($creativeIds) == 0) {
            return ;
        }

        $table = $this->getTable();

        $where = 'parent_id = ' . $this->id;
        $where.= ' AND id ' . $table->getAdapter()->quoteInto('NOT IN(?)', $creativeIds, Zend_Db::INT_TYPE);

        return $table->delete($where);
    }

    
    public function getMinBid()
    {
        $campaign = new Model_Campaign();
        $campaign->id = $this->campaignId;
        $campaign->find();

        $confFile = APPLICATION_PATH . '/configs/application.ini';
        $config = new Zend_Config_Ini($confFile, 'production');
        $config = $config->toArray();				

        $cpcMin = $config['tapit']['bids']['cpc']['min'];
        $cpmMin = $config['tapit']['bids']['cpm']['min'];
        $minBid = 0.1;

        switch($campaign->campaignTypeId)
        {
            case 1: // CPC
            $minBid = $cpcMin ;
            break;

            case 2: // CPM
            $minBid = $cpmMin;
            break;    	
        }

        if (App_User::isAdmin()) {
            $minBid = 0.01;
            //https://tapitmedia.atlassian.net/browse/TAPITUI-999
            if ($campaign->campaignTypeId == 2) {
                $minBid = 0.04;
            }
        }

        return $minBid;
    
    }
    
    public function getImageUrl($creativeIds,$forCampaign = false) {
        $creativesUrl = array();
        if (!is_array($creativeIds) || empty($creativeIds)) { 
            return $creativesUrl;
        }
        
        $creativesList = new Model_Creative_List(false);
        $creativesList->setWhere('id in (' . implode(',', $creativeIds) . ')');
        $creativesList->initDefault();
        $creativesList->loadCreativeDimensions();                
        
        $imgUrl = array();
        foreach ($creativesList as $creative) {
            if ($creative->getDimensionsList()->isEmpty() || $creative->getDimensionsList()->getMaxSizeImage($creative) == null) {
                $imgUrl = '';
                if($forCampaign){
                    $imgUrl['text'] = $creative->adtext;
                    $imgUrl['ad_alert_text'] = $creative->ad_alert_text;
                    $imgUrl['ad_alert_no_text'] = $creative->ad_alert_no_text;
                    $imgUrl['ad_alert_yes_text'] = $creative->ad_alert_yes_text;
                    $imgUrl['clickurl'] = $creative->clickurl;
                    $imgUrl['dimension'] = null;
                    $imgUrl['url'] = null;
                    $imgUrl['creative_type_id'] = $creative->creative_type_id;
                }
            } else {
                $campaign = new Model_Campaign();
                $campaign->id = $creative->campaignId;
                $maxDimension = $creative->getDimensionsList()->getMaxSizeImage($creative);
                $imgUrl['url']        = $maxDimension->getImageUrl($campaign, $creative, $maxDimension->getDimensionType());                
                $imgUrl['dimension']  = $maxDimension->getDimensionType()->width.'x'.$maxDimension->getDimensionType()->height; 
                if($forCampaign){
                    $imgUrl['clickurl'] = $creative->clickurl;
                    $imgUrl['text'] = null;
                    $imgUrl['ad_alert_text'] = null;
                    $imgUrl['ad_alert_no_text'] = null;
                    $imgUrl['ad_alert_yes_text'] = null;
                    $imgUrl['creative_type_id'] = $creative->creative_type_id;
                }
            }                       
                        
            $creativesUrl[$creative->id] = $imgUrl;
            
        }
                        
        return $creativesUrl;
    }

    function updateWeight($creative_id, $weight){
        $table = $this->getTable();

        return $table->update(
            array('weight' => $weight),
            'id ='. $table->getAdapter()->quoteInto('?', $creative_id)
        );
    }
    
    /**
     *
     * @param int $creativeId
     * @return int $campaignId
     */
    public function fetchCampaignId($creativeId)
    {
        $campaignId = null;
        $table = $this->getTable();
        $select = $table->select();
        $select->from($table, array('campaign_id'));
        
        $select->where('id = ?', $creativeId);

        $row = $table->fetchRow($select);
        if (count($row)) {
            $campaignId = $row->campaign_id;
        }
        
        return $campaignId;
    }
    
    public static function getReportingCreativeData( $user_id = null )
    {
      $where = !empty( $user_id ) ? 'WHERE campaigns.user_id = '.$params ['user_id'] : '';
    
      $sql = "SELECT 
         DISTINCT
         creatives.id, 
         TRIM(creatives.name), 
         TRIM(campaigns.name) as campaign_name, 
         CONCAT(TRIM(users.fname), ' ', TRIM(users.lname)) as user_name,
         campaigns.id as campaign_id 
         FROM creatives 
         INNER JOIN dashboard_creatives_daily ON dashboard_creatives_daily.creative_id=creatives.id 
         INNER JOIN campaigns ON creatives.campaign_id=campaigns.id 
         INNER JOIN users ON users.id = campaigns.user_id
         $where
         ORDER BY users.fname ASC, users.lname, campaigns.name ASC, creatives.name ASC";
         
      $db  = Zend_Registry::get('read');
      $conn = $db->getConnection();
      $stmt = $conn->query($sql);
      
      return $stmt->fetchAll(PDO::FETCH_OBJ);    
    
    }


}
