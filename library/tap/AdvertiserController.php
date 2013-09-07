<?php

class AdvertiserController extends App_Controller_Default
{

    public $contexts = array(
        'csv-export' => array('csv')
    );

    
    public function init()
    {
        parent::init();

        $this->_helper->contextSwitch()->addContext(
            'csv',
            array(
                'suffix' => 'csv',
                'headers' => array(
                    'Content-Type' => 'application/csv',
                    'Content-Disposition:' => 'attachment; filename="report.csv"')))->initContext('csv');
        
    }

    
    public function getReportAction($name = 'default')
    {
    	$campaign = new Model_Campaign();
    	$campaign->userId = App_User::getUserId();
  		
    	$data = $campaign->generateAdvertiserReport($name, 'today');     
    	
    }


    public function createCampaignAction() 
    {
        $userStatus = App_User::getLoggedUser();
        //Redirect to home page if user status is pending
        if ($userStatus->status_id == 1) {
            $this->_redirect('/');
            return;
        }
        $advertiserId = $this->getRequest()->getParam('adv_id');

        $userId = App_User::getUserId();
        $request = $this->getRequest();
        $form = new Default_Form_CampaignCreate(array( 'userId' => $userId , 'advertiserId' => $advertiserId ));
        Model_UserAccessRule::filterForm($form);
        if (!App_User::fullAccess() && !App_User::changeStatus()) {
            $this->_redirect('/');
        }
        
        $form->populateCategories();
        
        $post = $request->getPost();
        
        $isAdmin = App_User::isAdmin();
        
        //if respective goal type is not selected empty the variable
        if ($isAdmin) {
            if ((isset($post['goal_type_id']) && !empty($post['goal_type_id']))) {
                if ($post['goal_type_id'] == '1')
                    $post['clickgoal'] = 0;
                else
                    $post['impgoal'] = 0;
            }
            if ((isset($post['performance_type_id']) && !empty($post['performance_type_id']))) {
                if ($post['performance_type_id'] == '3')
                    $post['convgoal'] = 0;
                else
                    $post['ctrgoal'] = 0;
            }
        }
        
        //remove conversion element validations.
        $dailyBudgetError = false;
        if ($request->isPost()) {
            $postData = $request->getPost();
            if (empty($postData['campaign_type_id']) || $postData['campaign_type_id'] != 3) {
                $converionElement = $form->getElement('campaign_conversions');
                if ($converionElement) {
                    $converionElement->removeValidator('Zend_Validate_Float');
                    $converionElement->removeValidator('Zend_Validate_Between');
                    $converionElement->setRequired(false);
                }
            }
            
            //remove validators if corresponding spend cap is not selected
            if (isset($postData['spend_cap']) && $postData['spend_cap'] == '1') {
                $campaignBudgetElement = $form->getElement('total_budget');
                $campaignBudgetElement->setRequired(false);
                $campaignBudgetElement->removeValidator('Zend_Validate_Between');

                $endDateElement = $form->getElement('end_date');
                $endDateElement->setRequired(false);
            } else {
                $dailyBudgetElement = $form->getElement('daily_budget');
                $dailyBudgetElement->setRequired(false);
                $dailyBudgetElement->removeValidator('Zend_Validate_Between');
                
                $totalBudget = $postData['total_budget'];
                $startDate = $postData['start_date'];
                $endDate = $postData['end_date'];
                $dailyMinBudget = Model_Campaign::getDailyMinBudget();
                $dailyMaxBudget = Model_Campaign::getDailyMaxBudget();
                $elem = $form->getElement('total_budget');
                if ($endDate && $startDate && $elem->isValid($totalBudget)) {
                    $flightPeriod = CH_Date::getDatesDiff($endDate, $startDate) + 1;
                    if ($flightPeriod <= 0) {
                        $calDailyBudget = 0;
                    } else {
                        $calDailyBudget = number_format( ($totalBudget / $flightPeriod), 2, '.', '' );
                    }
                    if ($calDailyBudget < $dailyMinBudget) {
                        $dailyBudgetError = true;
                        $elem->setErrors(array('invalidBudget' => "Your budget is too low for the flight period. Either raise your budget or decrease your flight dates so that your total daily spend is at least $".$dailyMinBudget));
                        $form->populate($postData);
                    } else if ($calDailyBudget > $dailyMaxBudget) {
                        $dailyBudgetError = true;
                        $elem->setErrors(array('invalidBudget' => "Your budget is too high for the flight period. Either decrease your budget or raise your flight dates so that your total daily spend is at max $".$dailyMaxBudget));
                        $form->populate($postData);
                    }
                }
            }
        }
        
        if (!$dailyBudgetError && $request->isPost() && $form->isValid($request->getPost())) {
            
            $dateError = false;        
            

            $endDate = $form->getValue('end_date');
            $startDate = $form->getValue('start_date');

            if ($endDate && CH_Date::getDatesDiff($startDate, $endDate) > 0) {
                /* @var $elem Zend_Form_Element */
                $elem = $form->end_date;
                $elem->setErrors(array('invalidDate' => 'Campaign end date should be greater than or equal to start date.'));
                $dateError = true;
            }

            if (!$dateError) {
                $campaign = new Model_Campaign($form->getValues());                                
                
                $campaign->userId = App_User::getUserId();
                
                $campaign->endDate = empty($endDate) ? '0000-00-00 00:00:00' : date('Y-m-d 23:59:00', strtotime($endDate));
                $campaign->campaignLevelTargeting = 1; // to differentiate targeting level - campaign or creatives 
                
                //set default bid value
                if($campaign->campaignTypeId == '2'){     

                    if($isAdmin){
                        $campaign->cpcBid = 0.3;
                    }else{
                        $campaign->cpcBid = 0.6;
                    }
                } else if ($campaign->campaignTypeId == '3') { // CPA campaigns
                    if ($isAdmin) { // Admin only
                        $campaign->cpcBid = 0.6;
                    }
                }
                                  
                // Reassignment of Campigns
                if ($isAdmin) {
                    if (!empty($post['assign_to'])) {
                        $campaign->userId = $post['assign_to'];
                    }
                }    
                
                //removing budget value if corresponding spend cap is not selected
                if ($postData['spend_cap'] == '1') {
                    $campaign->total_budget = 0.0000;
                } else  {
                    $dailyBudget = 0;
                    if ($postData['total_budget'] && $postData['start_date'] && $postData['end_date']) {
                        $dailyBudget = $postData['total_budget']/(1+Tapit_Utils::daysBetweenDates($postData['start_date'], $postData['end_date']));
                    }
                    $campaign->daily_budget = $dailyBudget;
                }
                
                $campaign->save();

                $dp = new Model_CampaignDayparting();

                if ($post['day_parting'] == 1) {
                    $dp->replaceForCampaign($campaign, $post['day_part'], $post['timezone']);
                } else {
                    $dp->replaceForCampaign($campaign, array());
                }

                //save values for pacing indicator and performance indicator
                if ($isAdmin) {
                    if ((isset($post['goal_type_id']) && !empty($post['goal_type_id']))  || (isset($post['performance_type_id']) && !empty($post['performance_type_id']))) {                       
                        $campaignGoal = new Model_CampaignGoal();
                        if(isset($post['goal_type_id']) && !empty($post['goal_type_id'])){ //pacing indicator   
                            $campaignGoal->campaign_id = $campaign->id;
                            $campaignGoal->goalTypeId = $post['goal_type_id'];
                            $campaignGoal->goal = ($post['goal_type_id'] == '1') ? $post['impgoal'] : $post['clickgoal'];
                            $campaignGoal->save();
                        }
                        
                        if (isset($post['performance_type_id']) && !empty($post['performance_type_id'])) { //performance Indicator
                            $campaignGoal->campaign_id = $campaign->id;
                            $campaignGoal->goalTypeId = $post['performance_type_id'];
                            $campaignGoal->goal = ($post['performance_type_id'] == '3') ? $post['ctrgoal'] : $post['convgoal'];
                            $campaignGoal->save();
                        }
                    }

                    // Save Extended Categories                    
                    if (isset($post['category_ids'])) {

                        $categoryGroup = $post['category_ids'];

                        //unset primary category id from extended if found
                        if (($key = array_search($campaign->category_id, $categoryGroup)) !== false) {
                            unset($categoryGroup[$key]);
                        }

                        if (count($categoryGroup)) {
                            $categories = new Model_CampaignCategory();
                            $categories->updateCampaignCategories($campaign, $categoryGroup);
                        }
                    }
                }

                //allow update
                $allowBluekaiUpdate = new Zend_Session_Namespace('AudienceTargeting');
                $allowBluekaiUpdate->allow_update_{$campaign->id} = true;
                
                //$this->_redirect('/advertiser/create-campaign-creatives/id/' . $campaign->id);
                $this->_redirect('/advertiser/campaign-targeting/id/' . $campaign->id);
            }
        }
        $form->getElement('daypart')->setValue(json_encode(@$post['day_part']));
        $this->view->form = $form;
    }

    public function createCampaignCreativesAction()
    {
        $request = $this->getRequest();
        $campaignId = (int) $request->getParam('id');
        $campignTypeId = new Model_CampaignType();
        $campaign = new Model_Campaign();
        $campaign->id = $campaignId;
        
        if($request->getParam('back')) {
            $referer = $request->getParam('referer');
            if(strpos($referer, 'campaign-targeting') !== false)
                $this->_redirect('/advertiser/campaign-targeting/id/' . $campaign->id);
            else
                $this->_redirect('/advertiser/campaign-edit-overview/id/' . $campaign->id);
        }
        
        if($campaign->target_device_type_id == '2'){
            $campaignDevices = new Model_CampaignIncludeDevice();
            $campaignDevices->campaignId = $campaignId;

            $this->view->totalSelectedDevices = $campaignDevices->countDevicesSelected();
        }

        if(!$campaign->find() || ($campaign->userId != App_User::getUserId() && !App_User::allow($campaign->userId, false, true)) ) {

            $this->_redirect('/');
        }
	$this->view->minBid = $campaign->getMinBid($campaign->campaign_type_id);			
	$this->view->campaignType  = $campignTypeId->getCampaignType( $campaign->campaign_type_id );						
        $manager = new Default_Form_Manager_Creative();
        $manager->setRequest($request);
        $elements = $manager->getCreateFormElements($campaign);
        
        $form = new Default_Form_Creative(array('elements' => $elements, 'campaignTypeId' => $campaign->campaign_type_id));
        Model_UserAccessRule::filterForm($form);
        
        $manager->setFormValues($form);
        
        
        $form->adsize->setValue('2');
        $form->adtextfont->setValue('2');
        $form->adtitlefont->setValue('2');
        $form->adtitlefontsize->setValue('18');
        $form->adtextfontsize->setValue('16');
        $initialLoad = 1;
        if($request->isPost())
        {
            $postValues =   $request->getParams();
           
            if($postValues['action_type_id']==2)
            {
                $request->setParam("clickurl","http://");
            }  
            else
            {
                $request->setParam("phone","000000");
            }
            
            if (($postValues['creative_type_id'] != 3) && ($postValues['creative_type_id'] != 8)) {
                $request->setParam("source_type_id", "1");
            } 
            
            //removes script validator for ad type HTML
            if ($postValues['creative_type_id'] != 8){
                $htmlElement = $form->getElement('html');
                $htmlElement->removeValidator('App_Validator_Script');
            }
            
            //remove destination url validation vast url is selected
            if ($postValues['action_type_id'] == 'video') {
                if ($postValues['source'] == '2') {
                    $clickurl = $form->getElement('clickurl');
                    $clickurl->setRequired(false);
                }
            } else if ($form->allowVideoAds()){
                $delivery = $form->getElement('delivery');
                $delivery->setRequired(false);
                
                $placement = $form->getElement('placement');
                $placement->setRequired(false);
            }
            
            //remove vast url validation if source is file
            if (isset($postValues['source']) && $postValues['source'] != '2'){
                $vastUrlElement = $form->getElement('vast_url');
                $vastUrlElement->setRequired(false);
            }
            
            $initialLoad = 0;
        }

        if($request->isPost() && $form->isValid($request->getParams())) {
                 $fields = $form->getValues();	
                 
            if($postValues['action_type_id']==2)
            {
                $request->setParam("clickurl","");
                $fields['clickurl']  =   "";
            }  
            else
            {
                $request->setParam("phone","");
                $fields['phone']  =   "";
            }
            $form->setDefaults($fields);

            $creative = new Model_Creative($form->getValues());
            $creative->campaignId = $campaign->id;
            $creative->creativeIconId = $form->ad_image_id->getValue();
            
            $patterns = array('/(=\s)/','/(\s=)/','/(\?\s)/','/(\s\?)/');
            $replacements = array('=','=','?','?');

            $creative->clickurl = trim($creative->clickurl);
            $creative->clickurl = preg_replace($patterns, $replacements, $creative->clickurl);
            $creative->clickurl = str_replace(' ', '%20', $creative->clickurl);
            
            if($creative->creative_type_id != 9){
                $creative->adsize = '';
                $creative->adtextfont = '';
                $creative->adtitlefont = '';
                $creative->adtitlefontsize = '';
                $creative->adtextfontsize = '';
                $creative->adtextcolor = '';
                $creative->adtitlecolor = '';
                $creative->adbordercolor = '';
                $creative->adbackgroundcolor = '';
            }
            if($creative->creative_type_id != 10){
                $creative->ad_alert_text = '';
                $creative->ad_alert_no_text = '';
                $creative->ad_alert_yes_text = '';
            }
            if ($creative->creative_type_id != 3 && $creative->creative_type_id != 8) {
                $creative->html = '';
            }

            $creative->adheadline = strip_tags($creative->adheadline,'<font></font><b></b><i><i><u></u><br><br /><strike></strike>');
            $creative->adcopy = strip_tags($creative->adcopy,'<font></font><b></b><i><i><u></u><br><br /><strike></strike>');
                
            //set default bid value
            $isAdmin = App_User::isAdmin();

            if($campaign->campaignTypeId == '2'){                   
               
                if($isAdmin){
                    $creative->cpcBid = 0.3;
                }else{
                    $creative->cpcBid = 0.6;
                }              
            } else if ($campaign->campaignTypeId == '3') { // CPA campaigns
                if ($isAdmin) { // Admin only
                    $creative->cpcBid = 0.6;
                }
            }
            
            //we are storing phone number in click url 
            //https://tapitmedia.atlassian.net/browse/TAPITUI-786
            $fields['phone'] = trim($fields['phone']);
            $fields['phone'] = strip_tags($fields['phone']);
            if (isset($fields['action_type_id']) && ($fields['action_type_id'] == 2)) {
                $fields['clickurl'] = 'tel:' . $fields['phone'];
                $creative->clickurl = $fields['clickurl'];
            }
            
            if ($postValues['action_type_id'] == 'video') {
                $creative->action_type_id = '1';
                $creative->creative_type_id = 6;
                if ($postValues['vast_url']) {
                    $creative->source_type_id = 3;
                    $creative->html = $postValues['vast_url'];
                }
            }
            
            $creative->save();
            
            $html_dimension = $this->getRequest()->getParam( 'html_dimensions', null );

            if(!empty($html_dimension) && ($creative->creative_type_id == 3 || $creative->creative_type_id == 8)){
                $manager->saveHTMLDimension($html_dimension, $creative->id);
            }
            
            $manager->saveDestinationURL($creative, $form);
            
            if($creative->adsize) {
                $manager->saveIcon($creative, $form);
            }else{            
                $manager->saveBanners($creative, $form);
                $manager->saveFullScreenBanner($creative, $form);
            }
            
            //save video ad
            $transcodeError = false;
            if ($creative->creative_type_id == 6) {                
                $videoResult = $manager->saveVideo($creative, $form);
                if($videoResult['error']){
                    $transcodeError = true;
                }
                
                $manager->saveCreativeEvents($creative, $request->getParams());
            }
            
            //save Interstitial Ad
            if ($form->creative_type_id->getValue() == 11 || ($creative->creative_type_id == 6 && $form->closing_frame->getValue() == 1)) {
                $manager->saveInterstitialImage($creative, $form);
            }
                       
            //save ad Prompt
            if( $form->creative_type_id->getValue() == 10 ){  
                
                $manager->saveAdpromptDimension($creative, $form);
            }
            
            $manager->saveCreatives($creative, $form);
            $campaign->updateCreativeWeights($campaignId);
            
            //cleanup uploaded image directory.
            $userImageDirPath = PUBLIC_PATH . "/media/{$campaign->userId}/";
            Tapit_Utils::cleanUploadedImage($userImageDirPath);
            
            //if transcode fails user should be able to edit the creative to upload again
            if ($transcodeError) {
                $this->_helper->flashMessenger->addMessage('Transcoding failed due to some reason. Please edit your creative to upload again.');
                $this->_redirect('/advertiser/creative-edit-overview/id/' . $creative->id);
            }
            
            //$this->_redirect('/advertiser/edit-creative-targeting-bid/creative_id/' . $creative->id);
            // submit another creative
            if($request->getParam('another')) {
                $this->_redirect('/advertiser/create-campaign-creatives/id/' . $campaign->id);
            }

            // redirect to campaign dashboard
            if($request->getParam('finish')) {
                $this->_redirect('/advertiser/campaign-edit-overview/id/'. $campaign->id);
            }
        }
        
        $this->view->initialLoad = $initialLoad;

        $this->view->campaign = $campaign;

        $this->view->form = $form;
				
        //session getting swap
        $token = App_User::getLoggedUser()->getToken();
        if (App_User::isChildUser()) {
            $mainUser = new Model_User();
            $mainUser->id = App_User::getMainUserId();
            $mainUser->find();
            $token = $mainUser->getToken();
        }
        $this->view->token = $token;
    }

    public function createCampaignTargetingBidAction()
    {
        $request = $this->getRequest();
        $creativeId = (int) $request->getParam('id', 0);
        $creative = new Model_Creative();
        $creative->id = $creativeId;

        if(!$creative->find()) {
            $this->_redirect('/');
        }

        $campaign = new Model_Campaign();
        $campaign->id = $creative->campaignId;

        if(!$campaign->find() || $campaign->userId != App_User::getUserId()) {
            $this->_redirect('/');
        }


        $manager = new App_Form_CreativeTargetingManager();

        $options = $manager->getDefaultElements(App_User::getLoggedUser(), $creative->id);

        $form = new Default_Form_CreativeTargeting($options);
        $manager->addDefaultValues($request, $form);

        if($request->isPost() && $form->isValid($request->getPost())) {

            $manager->saveCreativeData($form, $creative);
            //$manager->saveProfile($form, App_User::getLoggedUser());

            if($request->getParam('back')) {
                $this->_redirect('/advertiser/creative-edit-overview/id/' . $creative->id);
            }

            if($request->getParam('another')) {
                $this->_redirect('/advertiser/create-campaign-creatives/id/' . $campaign->id);
            }


            if($request->getParam('finish')) {
                $this->_redirect('/advertiser/dashboard');
            }

        }
        $this->view->campaign = $campaign;
        $this->view->creative = $creative;

        $this->view->form = $form;
    }

    public function campaignTargetingAction() 
    {        
        $request = $this->getRequest();
        $campaignId = (int) $request->getParam('id', 0);

        $campaign = new Model_Campaign();
        $campaign->id = $campaignId;        

        if (!$campaign->find()) {
            $this->_redirect('/');
        }

        $campignTypeId = new Model_CampaignType();        
        //get campign type Name from id				
        $this->view->campaignType = $campignTypeId->getCampaignType($campaign->campaign_type_id);
        $user = $this->getUser($campaign->userId);

        /**
         * @todo refactor strange logic
         */
        if (!App_User::allow($campaign->userId, false, true) && App_User::getUserId() != $campaign->userId) {
            $this->_redirect('/');
        }


        $manager = new App_Form_CampaignTargetingManager();

        $options = $manager->getDefaultElements(App_User::getLoggedUser(), $campaignId);
        
        $form = new Default_Form_CreativeTargeting($options);
        Model_UserAccessRule::filterForm($form);
        
        $manager->addDefaultValues($request, $form);
        $manager->addCampaignValues($form, $campaign);
        $manager->updateBidRange($form, $campaign); 
        
        // load devices
        $deviceIds = array();
        $devices = $campaign->loadCampignIncludedDevices();
        foreach ($devices as $device) {
            $deviceIds[] = $device->device_id;
        }
        $form->device_ids->setValue(implode(',', $deviceIds));

        // load included sites
        $included_vals = array();
        $included_sites = $campaign->loadCampignIncludedSitesWithNames();
        
        $includeSiteNames = array();
        $countr =   0;
        foreach ($included_sites as $site) {
          
            // only display entries that are not assigned to admin, unless you are an admin
            if (App_User::isAdmin() || $site->user_role != 5) {
                $includeSiteNames[$countr]['si']    =   $site->site_id;
                $includeSiteNames[$countr]['sn']    =   $site->name;
                
                
                $form->includes_site_ids->addMultiOption($site->site_id, $site->site_id . '[' . $site->user_role . ']',array("boom"=>"boom"));
                $included_vals[] = $site->site_id;
                $countr++;
            }
        }
       
        //$sub->setAttrib('id', 'channels_and_bids');
        $form->includes_site_ids->setAttrib('site-values', json_encode($includeSiteNames));
        $form->includes_site_ids->setValue($included_vals);
        
        // load excluded sites
        $excluded_vals = array();
        $excluded_sites = $campaign->loadCampignExcludedSitesWithNames();
        $excludeSiteNames = array();
        $countr         =   0;
        foreach ($excluded_sites as $site) {
            // only display entries that are not assigned to admin, unless you are an admin
            if (App_User::isAdmin() || $site->user_role != 5) {
                $excludeSiteNames[$countr]['si']    =   $site->site_id;
                 $excludeSiteNames[$countr]['sn']    =  $site->name;
                
                $form->excludes_site_ids->addMultiOption($site->site_id, $site->site_id . '[' . $site->user_role . ']');
                $excluded_vals[] = $site->site_id;
                $countr++;
            }
        }
        
        $form->excludes_site_ids->setAttrib('site-values', json_encode($excludeSiteNames));
        $form->excludes_site_ids->setValue($excluded_vals);
        
        //targeting publishers
        if (Default_Form_CreativeTargeting::allowTargetPublisher() && !$request->isPost()) {
            $form->target_publisher_type_id->setValue($campaign->target_publisher_type_id);
        }
        if ($request->isPost()) {
            $postData = $request->getPost();
            Default_Form_CreativeTargeting::resetLocValidators($form, $postData);
            Default_Form_CreativeTargeting::resetDeviceValidator($form, $postData);
            Default_Form_CreativeTargeting::registerAudienceValidator($form, $postData, $campaign->id);
        }
        
        if ($request->isPost() && $form->isValid($request->getPost())) {                           
            $newRecord = ($campaign->created === $campaign->modified);
            
            $manager->saveProfile($form, $user);

            $manager->removeCampaignSettings($form, $campaign);
            
            //  Save Campaign Settings            
            $manager->saveCampaignData($form, $campaign);
            
            //  Update Campaign
            $campaign->targetLocationTypeId = $form->target_location_type_id->getValue();
            $campaign->targetDeviceTypeId   = $form->target_device_type_id->getValue();
            $campaign->targetTrafficTypeId  = $form->target_traffic_type_id->getValue();
            $campaign->cpcBid               = $form->cpc_bid->getValue();
            $campaign->targetChannelTypeId  = $form->target_channel_type_id->getValue();
                            
           
                        
            $campaign->environmentTypeId = $form->environment_type_id->getValue();
            if (Default_Form_CreativeTargeting::allowTargetPublisher()) {
                $campaign->target_publisher_type_id = $form->target_publisher_type_id->getValue();
            }
            
            $campaign->require_udid = 0;
            if (count($form->udid_type_id->getValue()) > 0) {
                $campaign->require_udid = 1;
            }
            
            $campaign->update();      

            //allow update
            $allowBluekaiUpdate = new Zend_Session_Namespace('AudienceTargeting');
            unset($allowBluekaiUpdate->allow_update_{$campaign->id});
            
            // submit another campaign
            if ($request->getParam('another')) {
                $this->_redirect('/advertiser/create-campaign-creatives/id/' . $campaign->id);
            }
            
            // redirect to campaign dashboard
            if ($request->getParam('finish')) {
                if (strpos($form->referer->getValue(), 'create-campaign') !== false)
                    $this->_redirect('/advertiser/create-campaign-creatives/id/' . $campaign->id);
                else
                    $this->_redirect('/advertiser/campaign-edit-overview/id/' . $campaign->id);
            }
        } else if ($request->isPost()) {
            //form rule needs to show selected categories.
            if ($form->allowAudienceTargeting() && $form->allowAudienceTargetingUpdate($campaign->id)) {
                if ($form->audience_targeting_is_enabled->getValue()) {
                    $source = $form->audience_targeting_source_ids->getValue();
                    if (!empty($source)) {
                        $catIds = $form->audience_targeting_ids->getValue();
                        if (!empty($catIds)) {
                            $categoryIds = explode(',', $catIds);
                            $bluekaiTreeInfo = Model_BluekaiCategory::getInfo($categoryIds);
                            //set values.
                            $values = array('audience_targeting_is_enabled' => 1,
                                'audience_targeting_source_ids' => array(1),
                                'audience_targeting_ids' => implode(',', $categoryIds),
                                'audience_targeting_ids_info' => json_encode($bluekaiTreeInfo));
                            $form->populate($values);
                        }
                    }
                }
            }
        }
        
        $this->view->minBid = $campaign->getMinBid();
        $this->view->campaign = $campaign;
        $this->view->form = $form;
        
        //session getting swap
        $token = App_User::getLoggedUser()->getToken();
        if (App_User::isChildUser()) {
            $mainUser = new Model_User();
            $mainUser->id = App_User::getMainUserId();
            $mainUser->find();
            $token = $mainUser->getToken();
        }
        
        $mainUserId = App_User::getMainUserId();
        $accessRules = Model_UserAccessRule::getRules($mainUserId);
        $campaignTargetingPageInfo = array(
            'main_user_id' => $mainUserId,
            'partner_id' => App_PArtner::get('id'),
            'campaign_id' => $campaign->id,
            'bluekai_root_node_id' => 344,
            'allow_audience_targeting' => $form->allowAudienceTargeting(),
            'allow_audience_targeting_update' => $form->allowAudienceTargetingUpdate($campaign->id),
            "access_rules" => json_encode($accessRules),
            "status_id" => $campaign->statusId,
	    'is_allow_hyperlocal_targeting' => $form->allowHyperlocalTargeting(),
            'is_allow_zipcode_targeting' => $form->allowZipcodeTargeting(),
            'is_form_submitted' => $request->isPost(),
            'user_token' => $token,
            'redirect_to' => $campaign->id );
        $this->view->campaignTargetingPageInfo = $campaignTargetingPageInfo;
    }

    public function editCreativeTargetingBidAction()
    {
        $request = $this->getRequest();
        $creativeId = (int) $request->getParam('creative_id', 0);

        $creative = new Model_Creative();
        $creative->id = $creativeId;

        if(!$creative->find()) {
            $this->_redirect('/');
        }

        $campaign 			= new Model_Campaign();
				$campignTypeId  = new Model_CampaignType();
        $campaign->id   = $creative->campaignId;
        $campaign->find();
				//get campign type Name from id				
				$this->view->campaignType  = $campignTypeId->getCampaignType( $campaign->campaign_type_id );						
        $user = $this->getUser($campaign->userId);

        /**
         * @todo refactor strange logic
         */
        if(!App_User::allow($campaign->userId, false, true) && App_User::getUserId() != $campaign->userId) {
            $this->_redirect('/');
        }


        $manager = new App_Form_CreativeTargetingManager();

        $options = $manager->getDefaultElements(App_User::getLoggedUser(), $creativeId);
        $form = new Default_Form_CreativeTargeting($options);
        $manager->addDefaultValues($request, $form);
        $manager->addCreativeValues($form, $creative);
        $manager->updateBidRange($form, $creative); // need no more, I think...
	
        if ($request->isPost()) {
            Default_Form_CreativeTargeting::resetLocValidators($form, $request->getPost());
            Default_Form_CreativeTargeting::resetDeviceValidator($form, $request->getPost());
        }

        if($request->isPost() && $form->isValid($request->getPost())) 
        {    
           	$newRecord = ($creative->created === $creative->modified);

	// set campaign to pending if cpc values are changed
	//if( $creative->cpcBid != $form->cpc_bid->getValue() || $creative->targetChannelTypeId != $form->target_channel_type_id->getValue() )
	//{
	//	$creative->statusId = 1; // back to pending
	//}

            //$manager->saveProfile($form, $user);
            
            //  Save Creative Settings
            $manager->saveCreativeData($form, $creative);    

            //  Update Creative

            $creative->targetLocationTypeId = $form->target_location_type_id->getValue();
            $creative->targetDeviceTypeId = $form->target_device_type_id->getValue();
            $creative->targetTrafficTypeId = $form->target_traffic_type_id->getValue();
            $creative->cpcBid = $form->cpc_bid->getValue();
            $creative->targetChannelTypeId = $form->target_channel_type_id->getValue();

            $creative->require_udid = $form->require_udid->getValue();
            $creative->environmentTypeId = $form->environment_type_id->getValue();

            $creative->update();
            

           /*
          	if($newRecord)
          	{
            	$campaign->sendSetupNotification();
            }
            */

				
            if($request->getParam('back')) {
                $this->_redirect('/advertiser/creative-edit-overview/id/' . $creative->id);
            }

				// submit another campaign
            if($request->getParam('another')) {
                $this->_redirect('/advertiser/create-campaign-creatives/id/' . $campaign->id);
            }

				// redirect to campaign dashboard
            if($request->getParam('finish')) {
                $this->_redirect('/advertiser/campaign-edit-overview/id/'. $campaign->id);
            }
        }

	$this->view->minBid = $campaign->getMinBid();			
        $this->view->campaign = $campaign;
        $this->view->creative = $creative;

        $this->view->form = $form;

    }

    public function deprecatedDashboardAction() {
        $request = $this->getRequest();
        $params = $request->getParams();
        $jsMsg = "";
        
        
        
        if ($this->getRequest()->getParam('dismiss') == '1') {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $sobj = new Zend_Session_Namespace('fundMessage');
            $sobj->messageshow = 2;            
            $this->view->messageshow = $sobj->messageshow;            
            return;
        }


        $modelUser = new Model_User();                      
        
        if ($this->getRequest()->getParam('id') != null) {
            if (!App_User::allow($request->id, false, true)) {
                //App_User::accessDenied();                      
                $session = new Zend_Session_Namespace('access');
                $session->noAccess = 1;
                $this->_redirect('/');
            }
            $user = new Model_User;
            $user->id = $request->id;
            $modelUser->id = $this->getRequest()->getParam('id');
        } else {
            $user = App_User::getLoggedUser();
            $modelUser->id = App_User::getUserId();
        }
        if (!$user->created && $user->id) {
            $user->find();
        }
       
        //get params, FIXME :
        $params = array('user_id' => $user->id);        
                
        $status = @$_COOKIE['status'];
        $statusMapper =
                array('runningselector' => 6,
                    'pausedselector' => 7,
                    'pendingselector' => 1,
                    'declinedselector' => 4,
                    'deletedselector' => 9,
                    'approvedselector' => 3,
                    'allselector' => 0);

        $statusIds = array();
        if ($status && array_key_exists($status, $statusMapper)) {
            if ($statusMapper[$status]) { 
                $params += array('status_id' => array($statusMapper[$status]));
                $statusIds = array($statusMapper[$status]);
            }
        } else {
            $statusIds = array(1, 3, 6, 10);
            $params += array('status_id' => $statusIds);
        }
        
        $period =  @$_COOKIE['pass'] ? $_COOKIE['pass'] : "today";
        $startDate = @$_COOKIE['sdate']? date('Ymd', strtotime($_COOKIE['sdate'])) : date("Ymd");
        $endDate = @$_COOKIE['edate'] ? date('Ymd', strtotime($_COOKIE['edate'])) : date("Ymd");
	$isExpToCSV = $request->getParam('exp_csv_adv_dash');
        
        $list = new Model_Campaign_List(false);
        $campaigns = $list->loadCampaigns($params);
        
        //get all the live campaigns ( status id = 6)		        
        $liveCampaigns = $list->loadLiveCampaigns(array(6), $user);
        $totalLiveCmps = count($liveCampaigns->toArray());
        $statuses = new Model_Status_List(false);
        $statuses->initDefault();
        
        $campaignIds = array();
        if ( is_a($campaigns, 'Model_Campaign_List') ) {
            $campaignIds = $campaigns->getPropertyArray('id');
        }
        
        //migrated to stats library
        //$summary = new Model_AdvertiserReportRow();
        //$summary->loadDailySummary($user, null, null, null, $statusIds);
        $summary = Tapit_Stats::getAdvertiserSummary( $period, $user->id, array(), $startDate, $endDate, array() );
        $this->view->summary = $summary;
        
        //migrated towards stats library
        //$campaignsReport = new Default_Model_Report_List_Advertiser(false);
        //$campaignDailyStats = $campaignsReport->loadCampaignDailyStats($campaigns, $user->created, $statusIds);
        //$this->view->campaignsReport = $campaignDailyStats;
                
        $campaignDailyStats = Tapit_Stats::getCampaignDailyStats($period, $campaignIds, $startDate, $endDate, $statusIds );
        $this->view->campaignsReport = $campaignDailyStats;
        
        $quickStats = array();
        $quickStats['account_balance'] = '$' . number_format($user->balance, 2);
        $quickStats['adv_name'] = $user->fname . ' ' . $user->lname;
        $quickStats['live_cmpg'] = $totalLiveCmps;
        
        //migrated to stats library.
        //$quickStats['impression'] = $summary->getFormattedImpressions();
        //$quickStats['clicks'] = $summary->getFormattedClicks();
        //$quickStats['ctr'] = $summary->getFormattedCTR();
        //$quickStats['avg_cpc'] = $summary->getFormattedAvgCPC();
        //$quickStats['cost'] = $summary->getFormattedCosts();
        //$quickStats['conversions'] = $summary->getFormattedConversions();
        //$quickStats['ecpm'] = $summary->getFormattedECMP();
        //$quickStats['profit'] = $summary->getFormattedProfit();
        //$quickStats['rpm'] = $summary->getFormattedRpm();
        //$quickStats['epc'] = $summary->getFormattedEpc();
        $statsProperties = array(
           'impressions',
            'clicks',
            'ctr',
            'avg_cpc',
            'costs',
            'conversions',
            'ecpm',
            'profit',
            'rpm',
            'epc'
        );
        foreach ( $statsProperties as $pro ) {
             $quickStats[$pro] = $summary->$pro;
        }
        
        $this->view->quickStats = $quickStats;
        $this->view->campaigns = $campaigns;
        $this->view->statuses = $statuses;
        $this->view->summary = $summary;

        //get user created date.
        $this->view->user_created_date = date('Y-m-d', strtotime($user->created));

        if ($user->balance < 50) {
            $this->view->advfunds = $user->balance;
            $sesbj = new Zend_Session_Namespace('fundMessage');
            if (!isset($sesbj->messageshow)) {
                $sesbj->messageshow = 1;
                $jsMsg = 1;
            }
            $this->view->messageshow = $sesbj->messageshow;
        }
        
        //set message if validation email for new user sent.				
        $currentTime = time();
        $createdTime = strtotime($user->created);
        $oneDayAfter = strtotime("+1 day", $createdTime);
        $sessObj = new Zend_Session_Namespace('mailSend');
        
        //test cases only
        //$oneDayAfter = strtotime( "+2 minutes", $createdTime );

        $emailMsgShow = $repeatEmailMsgShow = false;
        if ($user->status_id == 1) {
            if ($currentTime <= $oneDayAfter) {
                $emailMsgShow = true;
                //if user come from registreation process.
                if (isset($sessObj->isSend)) {
                    $emailMsgShow = $sessObj->isSend;
                }
            } else {
                $repeatEmailMsgShow = 1;
                $sessObj->setExpirationSeconds(1);
            }
        } else {
            $sessObj->setExpirationSeconds(1);
        }
        
        $campaignsArr = $campaigns->toArray();  
        $this->view->campArrCnt = count($campaignsArr);

        $objCampaign = new Model_Campaign();
        //information for js
        $jsInfo = array("user_id" => $user->id,
            "date_created" => date('Y-m-d', strtotime($user->created)),
            "payment_info_updated" => true,
            "message_show" => $jsMsg,
            "repeat_msg" => $repeatEmailMsgShow,
            "email_msg_show" => $emailMsgShow,
            "adv_funds" => $user->balance,
            "request_id" => $this->getRequest()->getParam('id'),
            "minCPCBid" => $objCampaign->getMinBid(1),
            "minCPMBid" => $objCampaign->getMinBid(2),
            "pagesize" => $request->getParam('size')
        );  
        $this->view->jsInfo = json_encode($jsInfo);
        
        //we don't like to show message for advertiser
        //$isPaymentInfoUpdated = $this->_isPaymentInfo();                
        $this->view->isPaymentInfoUpdated = true;        
        $this->view->userStatus = $user->status_id;
        $this->view->userId = $user->id; 
        
        /* CSV export functionality for publisher dashboard page START */
        if ($isExpToCSV) {
            $tableReport = new Model_AdvertiserReport_List();
            $i = 0;
            $j = 0;
            $exportCols = array('campaign_name', 'status','dailyBudget', 'impressions', 'clicks' ,'conversions', 'cpa', 'ctr', 'cpc_bid','costs');

            $exportReport = array();
            $exportColumns = $tableReport->getColumnDef($exportCols, false);
            foreach ($exportColumns as $key => $value) {
                $exportColumnsArr[$exportCols[$j]] = $exportColumns[$exportCols[$j]];
                $j++;
            }
            
            foreach ($campaigns as $campaign) {
                $reportRow = $campaignDailyStats[$campaign->id];
                if (is_object($reportRow)) {
                    $reportRow = (array) $reportRow;
                }
                $exportReport[$i] = (array) $reportRow;
                $exportReport[$i]['status'] = $statuses->getStatusName($campaign->statusId);
                $exportReport[$i]['campaign_name'] = $campaign->name;
                $exportReport[$i]['dailyBudget'] = $campaign->dailyBudget;
                if($campaign->campaign_level_targeting == '1'){
                    $exportReport[$i]['cpc_bid'] = $campaign->cpc_bid;
                }
                else{
                    $exportReport[$i]['cpc_bid'] = $reportRow['avg_cpc'];
                }
                $i++;
            }
            
            Tapit_Utils::exportToCSV($exportColumnsArr, $exportReport, 'Advertiser_Dashboard');
        }
    }
    
    public function dashboardAction() {

        $request = $this->getRequest();
        $params = $request->getParams();
        $isExpToCSV = $request->getParam('exp_csv_adv_dash');

        if ($this->getRequest()->getParam('dismiss') == '1') {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $sobj = new Zend_Session_Namespace('fundMessage');
            $sobj->messageshow = 2;
            $this->view->messageshow = $sobj->messageshow;
            return;
        }

        $advertiserId = null;
        $modelUser = new Model_User();
        if ($this->getRequest()->getParam('id') != null) {
            if (!App_User::allow($request->id, false, true)) {
                //App_User::accessDenied();                      
                $session = new Zend_Session_Namespace('access');
                $session->noAccess = 1;
                $this->_redirect('/');
            }
            $user = new Model_User;
            $user->id = $request->id;
            $modelUser->id = $this->getRequest()->getParam('id');
            $advertiserId = $user->id;
        } else {
            $user = App_User::getLoggedUser();
            $modelUser->id = App_User::getUserId();
        }
        if (!$user->created && $user->id) {
            $user->find();
        }
        $this->view->userId = $user->id;
        $this->view->advertiserId = $advertiserId;
        $this->view->userStatus = $user->status_id;
        
        $dashboardData = array(
            'live_campaign_cnt' => Model_Campaign::getCampaignsCount($user->id, array(6)),
            'advertiser_name' => $user->fname . ' ' . $user->lname,
            'user_account_balance' => '$' . number_format($user->balance, 2)
        );	        
        $this->view->dashboardData = $dashboardData;
        
        $jsMsg = "";
        if ($user->balance < 50) {
            $this->view->advfunds = $user->balance;
            $sesbj = new Zend_Session_Namespace('fundMessage');
            if (!isset($sesbj->messageshow)) {
                $sesbj->messageshow = 1;
                $jsMsg = 1;
            }
            $this->view->messageshow = $sesbj->messageshow;
        }

        //set message if validation email for new user sent.				
        $currentTime = time();
        $createdTime = strtotime($user->created);
        $oneDayAfter = strtotime("+1 day", $createdTime);
        $sessObj = new Zend_Session_Namespace('mailSend');

        //test cases only
        //$oneDayAfter = strtotime( "+2 minutes", $createdTime );

        $emailMsgShow = $repeatEmailMsgShow = false;
        if ($user->status_id == 1) {
            if ($currentTime <= $oneDayAfter) {
                $emailMsgShow = true;
                //if user come from registreation process.
                if (isset($sessObj->isSend)) {
                    $emailMsgShow = $sessObj->isSend;
                }
            } else {
                $repeatEmailMsgShow = 1;
                $sessObj->setExpirationSeconds(1);
            }
        } else {
            $sessObj->setExpirationSeconds(1);
        }

        //complete user registrations.
        $mainUserId = App_User::getMainUserId();
        $continueRegistration = !Model_User::isRegistrationCompleted($mainUserId);
        $request = $this->getRequest();
        if ($request->isPost() && $this->getRequest()->getParam('submit_register_form')) {
            $captcha = $request->getParam('captcha');
            if ($captcha) {
                $captchaId = $captcha['id'];
                $captchaInput = $captcha['input'];
                $captchaSession = new Zend_Session_Namespace('Zend_Form_Captcha_' . $captchaId);
                $captchaIterator = $captchaSession->getIterator();
                $captchaWord = null;
                if (isset($captchaIterator) && isset($captchaIterator['word'])) {
                    $captchaWord = $captchaIterator['word'];
                }
                if ($captchaInput == $captchaWord) {
                    App_User::completeUserRegistration($request->getPost(), $mainUserId);
                    $url = App_User::getUserHomePage();
                    $this->_redirect($url ? $url : '/' );
                } else {
                    $this->view->captchaError = 1;
                    $sesRegister = new Zend_Session_Namespace('RegisterDetail');
                    $data = $request->getParams();
                    unset($data['password']);
                    unset($data['confirm_password']);
                    $sesRegister->register = $data;
                }
            }
        }
        
        $objCampaign = new Model_Campaign();

        //information for js
        $advertiserDashboardPageInfo = array(
            "user_id" => $user->id,
            "user_created_date" => date('Y-m-d', strtotime($user->created)),
            "payment_info_updated" => true,
            "message_show" => $jsMsg,
            "repeat_msg" => $repeatEmailMsgShow,
            "email_msg_show" => $emailMsgShow,
            "adv_funds" => $user->balance,
            "request_id" => $this->getRequest()->getParam('id'),
            "minCPCBid" => $objCampaign->getMinBid(1),
            "minCPMBid" => $objCampaign->getMinBid(2),
            "minCPABid" => $objCampaign->getMinBid(3),
            "page_size" => $request->getParam('size'),
            'min_daily_budget' => Model_Campaign::getDailyMinBudget(),
            'max_daily_budget' => Model_Campaign::getDailyMaxBudget(),
            'continue_registration' => $continueRegistration,
            'main_user_id' => $mainUserId,
            'logged_in_user_id' => App_User::getUserId(),
            'access_rules' => json_encode(Model_UserAccessRule::getRules($mainUserId))
        );
        $this->view->mainUserId = $mainUserId;
        $this->view->advertiserDashboardPageInfo = $advertiserDashboardPageInfo;
        $this->view->dashboardForm = new Default_Form_Dashboard_Filter();
    }
   
    public function exportDashboardAction() {
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        
        //collect the parameters
        $request = $this->getRequest();

        $params = array();
        $criteria = $request->getParam('export_csv_criteria');
        parse_str($criteria, $params);

        $userId = App_User::getUserId();
        if (!empty($params['user_id'])) {
            $userId = $params['user_id'];
        }

        //validate user access
        if ($userId && !App_User::allow($userId, false, true)) {
            $userId = App_User::getUserId();
        }

        $statusIds = array();
        $statusIdString = $request->getParam('status_id');
        if (!empty($params['status_id'])) {
            $statusIds = explode(',', $params['status_id']);
        }
        $period = $startDate = $endDate = null;
        if (!empty($params['period'])) {
            $period = $params['period'];
        }
        if (!empty($params['start_date'])) {
            $startDate = $params['start_date'];
        }
        if (!empty($params['end_date'])) {
            $endDate = $params['end_date'];
        }

        $orderBy = array('id' => array('field_name' => 'created', 'direction' => 'desc'));

        //get actual page data
        $campaigns = Tapit_AdvertiserStats::getDashboardCampaigns($userId, $statusIds, $period, $startDate, $endDate, $orderBy);
        
        $reportCols = array(
            'name' => array('label' => 'Campaigns'),
            'status_name' => array('label' => 'Status'),
            'daily_budget' => array('label' => 'Daily Budget'),
            'impressions' => array('label' => 'Impressions'),
            'clicks' => array('label' => 'Clicks'),
            'conversions' => array('label' => 'Conversions'),
            'cpa' => array('label' => 'CPA'),
            'ctr' => array('label' => 'CTR'),
            'bid' => array('label' => 'Bid'),
            'costs' => array('label' => 'Costs'));

        $exportReport = array();
        foreach ($campaigns as $campaign) {
            if (empty($campaign['campaign_level_targeting'])) {
                $campaign['bid'] = $campaign['cpc'];
            }
            $exportReport[] = $campaign;
        }
        
        Tapit_Utils::exportToCSV($reportCols, $exportReport, 'Advertiser_Dashboard');
    }
    
    public function exportToCSV($exportColumns, $exportData, $expName){
        return Tapit_Utils::exportToCSV($exportColumns, $exportData, $expName);
    }
    
    public function _isPaymentInfo() {
        $isPaymentInfoUpdate = false;
        $request = $this->getRequest();
        $user = new Model_User;
        if (isset($request->id) && App_User::allow($request->id)) {
            $user->id = $request->id;
        } else {
            $user->id = App_User::getLoggedUser()->id;
        }
        $user->find();
        $userData = array('user_id' => $user->id);
        $paymentInfoTax = new Model_PaymentInfoTax($userData);
        $paymentInfoWire = new Model_PaymentInfoWire($userData);
        $paymentInfoPayPal = new Model_PaymentInfoPaypal($userData);
        $paymentInfoCheck = new Model_PaymentInfoCheck($userData);
        if ($paymentInfoPayPal->initFromField('user_id')) {
            $isPaymentInfoUpdate = true;
        }

        if ($paymentInfoWire->initFromField('user_id')) {
            $isPaymentInfoUpdate = true;
        }

        if ($paymentInfoCheck->initFromField('user_id')) {
            $isPaymentInfoUpdate = true;
        }

        return $isPaymentInfoUpdate;
    }

    public function reportsAction() 
    {              
      //get the partner id
      $partnerId = Model_Partner::getValidPartnerId('pid');
        
      $managers  = Model_User::getAccountManagers($partnerId);    
      $campaigns = Model_Campaign::getReportingCampaigns( App_User::isAdmin() ? NULL : App_User::getUserId(), $partnerId ); 
      
      $this->view->managers         = $managers;
      $this->view->campaigns        = $campaigns;
      $this->view->admin            = App_User::isAdmin();
      $this->view->token            = $this->view->admin ? '4cb7a0f9554f8a6d634618255412d1fb' : App_User::getLoggedUser()->api_token;
      $this->view->partnerId        = $partnerId;
      
      $reportsPageInfo = array('allow_scheduling' => ($this->allowScheduling()) ? '1' : '0');
        
      $this->view->reportsPageInfo = $reportsPageInfo;
      
      $this->view->headScript()->appendFile('/js/jquery.json.js');
      $this->view->headScript()->appendFile('/js/api.js');
      
      $this->_helper->_layout->setLayout('default/layout_jsbottom');
    }

    public function uploadCreativeAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        $video = null;
        if($this->getRequest()->getParam('video')){
            $video = 1;
        }

        $form = new Default_Form_CreativeUpload(array('video' => $video));

        if($form->isValid($this->getRequest()->getParams())) {
            $this->view->images = $form->recieveImages();
        } else {
            $this->view->errors = $form->getMessages();
        }


        $this->asJSON();
    }
    


    public function uploadIconAction()
    {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        $request = $this->getRequest();


        $campaignId = (int) $request->getParam('id', 0);

        $campaign = new Model_Campaign();
        $campaign->id = $campaignId;

        if($campaign->find() && $campaign->userId == App_User::getUserId()){

            $creativesForm = new Default_Form_CreativeCreate($request);
            $iconElement = $creativesForm->getElement('adImage_upload');

            if( $iconElement->isValid($request->getParam('Filename')) ){

                $confFile = APPLICATION_PATH . '/configs/application.ini';
                $config = new Zend_Config_Ini($confFile, 'production');
                $config = $config->toArray();

                $dir = '/' . $config['upload']['iconFolder'] . '/';

                $this->view->fileName =
                        $dir . $creativesForm->recieveIcon($campaignId . microtime(), $dir);

            } else {

                $this->view->error = $iconElement->getErrorMessages();
            }
        }

        $this->asJSON();

    }


    public function uploadAdIconAction()
    {
        $request = $this->getRequest();
        $form = new Default_Form_AdIconUpload();
        
        if($form->isValid($request->getParams())) {

            $model = new Model_CreativeIcon();
            $model->userId = App_User::getUserId();
            $model->save();
            
            $form->recieveImage($model->id);
            $this->view->id = $model->id;
        } else {
            $this->view->errors = $form->getMessages();
        }


        $this->asJSON();
    }
    
    public function uploadAdIconTextAction()
    {
        $request = $this->getRequest();
        $form = new Default_Form_AdIconTextUpload();
        
        if($form->isValid($request->getParams())) {
            $imagedetail = $form->recieveImage();
            $this->view->id = $imagedetail[0];
            $this->view->imgpath = $imagedetail[1];
        } else {
            if($request->getParam('adsize') == '1' || $request->getParam('adsize') == '8') {
                $dim = 76;
            } elseif($request->getParam('adsize') == '2' || $request->getParam('adsize') == '3') {
                $dim = 38;
            } else {
                $dim = 32;
            }
            $this->view->errors = 'Please Upload Icon with size '.$dim.'x'.$dim.'px';//$form->getMessages();
        }


        $this->asJSON();
    }


    public function deprecatedCampaignEditOverviewAction() {
        $request = $this->getRequest();
        
        $campaign = new Model_Campaign;
        $campignTypeId = new Model_CampaignType();
        $campaign->id = (int) $request->getParam('id', 0);
        
        if (!$campaign->find()) {
            $this->_redirect('/');
        } else if ($campaign->userId != App_User::getUserId() && !App_User::allow($campaign->userId, false, true)) {
            $this->_redirect('/');
        }
        
        //session is set to check the user account viewed in account info/summary pages
        $iId = null;
        if(isset($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'],'/advertiser/dashboard/id') !== FALSE)){
            $iId = array_pop(explode('/', $_SERVER['HTTP_REFERER']));
        }
        elseif ($campaign->userId != App_User::getUserId() && isset($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'],'/network/dashboard') !== FALSE)){
            $iId = $campaign->userId;
        }
        
        if($iId){
            $session = new Zend_Session_Namespace('view_user');
            $session->viewUserId = $iId;
        }else{
            $session = new Zend_Session_Namespace('view_user');
            if(isset($session->viewUserId)){
                unset($session->viewUserId);
            }
        }
        //save creative weights
        if($request->isPost()) 
        {
            $objCreative = new Model_Creative;
            $postVariables = $request->getParams();
          
            foreach ($postVariables as $key => $value){
                if(strpos($key, 'weight') !== FALSE){
                    list($unused, $creativeId) = explode('_', $key);
                    $creativeWeight = $value;
                    $objCreative->updateWeight($creativeId, $creativeWeight);
                }
            }
            $campaign->updateCreativeWeights($campaign->id);
        }
        //$campaign->campaign_type_id  = $campignTypeId->getCampaignType( $campaign->campaign_type_id );						

        $this->view->campaignType = $campignTypeId->getCampaignType($campaign->campaign_type_id);

        $user = new Model_User();
        $user->id = $campaign->userId;
        $user->find();
        $userId = $user->id;
        
        /**
         * @todo Please refactor this block move to campaign method
         */
        //get params, FIXME :
        $params = array('user_id' => $campaign->userId);
        $status = @$_COOKIE['status'];
        $statusMapper =
                array('runningselector' => 6,
                    'pausedselector' => 7,
                    'pendingselector' => 1,
                    'declinedselector' => 4,
                    'deletedselector' => 9,
                    'approvedselector' => 3,
                    'allselector' => 0);

        $statusId = null;
        if ($status && array_key_exists($status, $statusMapper)) {
            $statusId = $statusMapper[$status];
            if ($statusId)
                $params += array('status_id' => $statusMapper[$status]);
        } else {
            $statusId = array(1, 3,4, 6, 10);  
            $params += array('status_id' => $statusId);
        }
        $period =  @$_COOKIE['pass'] ? $_COOKIE['pass'] : "today";
        $startDate = @$_COOKIE['sdate']? date('Ymd', strtotime($_COOKIE['sdate'])) : date("Ymd");
        $endDate = @$_COOKIE['edate'] ? date('Ymd', strtotime($_COOKIE['edate'])) : date("Ymd");
        $isExpToCSVCamp = $request->getParam('exp_csv_camp_dash');
        //migrated to stats library.
        //$summary = new Model_AdvertiserReportRow();
        //$summary->loadDailySummary($user, $campaign->id, null, null, $statusId);
      
        $campaignIds = array($campaign->id);
        $summary = Tapit_Stats::getAdvertiserSummary( $period, $user->id, $campaignIds, $startDate, $endDate, $statusId );
        $this->view->summary = $summary;

        $creatives = $campaign->getEmptyCreativesList()->loadOrderedForDashBoard( $params );
        $creatives->loadCreativeDimensions();
        
        //migrated to stats library.
        //$creatives->loadDailyReport();
        $this->view->creatives = $creatives;
        
        
        $creativeDailyStats = Tapit_Stats::getCreativeDailyStats($period, $creatives->getPropertyArray('id'), $startDate, $endDate);
        $this->view->creativeDailyStats = $creativeDailyStats;

        $statuses = new Model_Status_List(false);
        $statuses->initDefault();

        $this->view->statuses = $statuses;

        //find Campaign spend
	$list = new Model_Campaign_List(false);   
	$campaignSpend = null;
	if( App_User::isAdmin() ){ 
            $campaignSpend = $list->getCampaignSpend( $campaign->id );                            
	}                         
	$form = new Default_Form_CampaignEdit( array( 'campaignID' => $campaign->id ,'campaignStatus' => $campaign->statusId, 'campaignSpend' => $campaignSpend, 'userId' => $userId) );               						
        
        $form->populateCategories();

        $data = $campaign->toArray();

        $ex = explode(' ', $data['end_date']);
        $data['end_date'] = $ex[0];

        $ex = explode(' ', $data['start_date']);
        $data['start_date'] = $ex[0];

        if ($data['end_date'] == '0000-00-00') {
            $data['end_date'] = '';
        }
        if ($data['start_date'] == '0000-00-00') {
           $data['start_date'] = '';
        }  
        
        $form->populate($data);
        /* day parting */
        $dp = new Model_CampaignDayparting();
        $this->view->campaign = $campaign;


        // re-apply timezone
        if (!$data['timezone']) {
            $time = new DateTime('now', new DateTimeZone('GMT'));
        } else {
            $time = new DateTime('now', new DateTimeZone($data['timezone']));
        }

        $dateparts = $dp->getForCampaign($campaign)->toArray();
        $cnt = count($dateparts);
        for ($k = 0; $k < $cnt; $k++) {
            $dateparts[$k]['start_hour'] = date('G', strtotime($dateparts[$k]['time_start']));
            $dateparts[$k]['end_hour'] = date('G', strtotime($dateparts[$k]['time_end']));

        }
        $form->getElement('daypart')->setValue(json_encode($dateparts));        
        $form->day_parting->setValue(count($dateparts) > 0 ? 1 : 0);
        $values = array();      
        

        /* 
        * added in campaign targeting
        //populate exclude sites in quick block
        $excluded_site_vals = array();
        $excluded_sites = $campaign->loadCampignExcludedSites();

        foreach ($excluded_sites as $site) {
            $form->excludes_site_ids->addMultiOption($site->site_id, $site->site_id . '[' . $site->user_role . ']');
            $excluded_site_vals[] = $site->site_id;
        }
        //set the excluded sites values to form
        $form->excludes_site_ids->setValue($excluded_site_vals);
        */
        //populate exclude device in quick block
        $excluded_device_vals = array();
        $excluded_devices = $campaign->loadCampignExcludedDevices();

        foreach ($excluded_devices as $device) {

            $form->excludes_handset_ids->addMultiOption($device->device_id, $device->device_id . '[' . $device->user_role . ']');
            $excluded_device_vals[] = $device->device_id;
        }
        //set the excluded handset values to form
        $form->excludes_handset_ids->setValue($excluded_device_vals);
        
        /* 
         * added in campaign targeting
        //populate include sites in quick adds
        $included_site_vals = array();
        $included_sites = $campaign->loadCampignIncludedSites();

        foreach ($included_sites as $site) {
            $form->includes_site_ids->addMultiOption($site->site_id, $site->site_id . '[' . $site->user_role . ']');
            $included_site_vals[] = $site->site_id;
        }
        //set the included sites values to form
        $form->includes_site_ids->setValue($included_site_vals);
        */

        /*
         * added in campaign targeting
        //populate include device in quick adds
        $included_device_vals = array();
        $included_devices = $campaign->loadCampignIncludedDevices();

        foreach ($included_devices as $device) {

            $form->includes_handset_ids->addMultiOption($device->device_id, $device->device_id . '[' . $device->user_role . ']');
            $included_device_vals[] = $device->device_id;
        }
        //set the included handset values to form
        $form->includes_handset_ids->setValue($included_device_vals);
         * 
         */

        if (App_User::isAdmin()) {
            $cats = new Model_CampaignCategory;
            $list = $cats->fetchByCampaign($campaign->id);
            $values[$campaign->categoryId] = $campaign->categoryId; // get first category from main table
            foreach ($list as $cat) {
                $values[$cat['category_id']] = (int) $cat['category_id'];
            }
            $form->category_ids->setValue($values);
            
            $campaignGoal = new Model_CampaignGoal();
            $goalDetail = $campaignGoal->getForCampaign($campaign->id)->toArray();
            if (!empty($goalDetail)) {
                if ($goalDetail[0]['goal_type_id'] == '1' || $goalDetail[0]['goal_type_id'] == '2') {
                    $goalArr = $goalDetail[0];
                    if (!empty($goalDetail[1])){
                        $performanceArr = $goalDetail[1];
                    }
                }else {
                    if (!empty($goalDetail[1])){
                        $goalArr = $goalDetail[1];
                    }
                    $performanceArr = $goalDetail[0];
                }
                
                if(!empty($goalArr)){
                    $goalVal = (int) $goalArr['goal'];
                    $form->goal_type_id->setValue($goalArr['goal_type_id']);
                    if ($goalArr['goal_type_id'] == '1') {
                        $form->impression_goal->setValue($goalVal);
                    } else {
                        $form->click_goal->setValue($goalVal);
                    }
                }
                
                if (!empty($performanceArr)) {
                    $form->performance_type_id->setValue($performanceArr['goal_type_id']);
                    if ($performanceArr['goal_type_id'] == '3') {
                        $form->ctr_goal->setValue($performanceArr['goal']);
                    } else {
                        $form->conversion_goal->setValue($performanceArr['goal']);
                    }
                }
            }
        }

        $form->daily_budget->setValue(number_format($form->daily_budget->getValue(), 2, '.', ''));
        $form->total_budget->setValue(number_format($form->total_budget->getValue(), 2, '.', ''));
        
        if ($form->total_budget->getValue() > 0) {
            $form->spend_cap->setValue('2');
        } else {
            $form->spend_cap->setValue('1');
        }
        
        $form->campaign_conversions->setValue(number_format($form->campaign_conversions->getValue(), 2, '.', ''));
        
        $this->view->form = $form;

        //migrated to stats library.
        //$quickStats = new Default_Model_QuickStats_Dashboard();
        //$quickStats->loadDaily($campaign->id, $statusId);
        $quickStats = array();
        $statsProperties = array(
           'revenue',
            'ecpm',
            'avg_cpc',            
            'conversions',
            'costs',
            'ecpm',
            'profit',
            'rpm',
            'epc'
        );
        foreach ( $statsProperties as $pro ) {
             $quickStats[$pro] = $summary->$pro;
        }
        $this->view->quickStats = $quickStats;

        $cStatus = $statuses->findItemByProperty('id', $campaign->statusId);
        if ($cStatus instanceof Model_Status) {
            $this->view->cStatus = $cStatus;
        } else {
            $this->view->cStatus = new Model_Status();
        }
        
        //user information for js
        $userInfo = array("id"=>$user->id, "name"=>$user->fname." ".$user->lname, "created"=>date('Y-m-d', strtotime($user->created)));
        $form->getElement('user_data')->setValue(json_encode($userInfo));
        //get user created date.
        $this->view->user_created_date = date('Y-m-d', strtotime($user->created));
        $this->view->userId = $campaign->userId;
        $this->view->user = $user;
        $this->view->partner = Model_Partner::getUserPartner($user->id);
        
        $creativesArr = $creatives->toArray();  

        /* CSV export functionality for publisher dashboard page START */
        if ($isExpToCSVCamp) {
            $i = 0;
            $j=0;
            $exportReport = array();
            
            $tableReport = new Model_AdvertiserReport_List();
            $exportCols = array('creative_name', 'status', 'weight_percentage', 'weight', 'impressions', 'clicks' ,'conversions', 'cpa', 'ctr', 'avg_bid','costs');

            $exportColumns = $tableReport->getColumnDef($exportCols, false);
            foreach ($exportColumns as $key => $value) {
                $exportColumnsArr[$exportCols[$j]] = $exportColumns[$exportCols[$j]];
                $j++;
            }
            
            foreach ($creativesArr as $i => $creative) {
                $reportRow = $creativeDailyStats[$creative['id']];
                if (is_object($reportRow)) {
                    $reportRow = (array) $reportRow;
                }
                if($campaign->campaign_type_id != '1'){
                    $impr = str_replace(',','', $reportRow['impressions']);
                    if($impr > 0){
                        $avgbid = number_format((($reportRow['costs'] / $impr) * 1000),2);
                    }else{
                        $avgbid = 0;
                    }
                }else{
                    $avgbid = $reportRow['avg_cpc'];
                }
                $exportReport[$i] = $creative;

                $exportReport[$i] = array_merge_recursive($exportReport[$i], $reportRow);
                $exportReport[$i]['status'] = $statuses->getStatusName($creative['status_id']);
                $exportReport[$i]['creative_name'] = $creative['name'];
                $exportReport[$i]['avg_bid'] = $avgbid;
                $i++;
            }
            Tapit_Utils::exportToCSV($exportColumnsArr, $exportReport, 'Advertiser_Campaign_Dashboard');
        }
    }

    public function campaignEditOverviewAction() {
        $request = $this->getRequest();
        
        $campaign = new Model_Campaign;
        $campignTypeId = new Model_CampaignType();
        $campaign->id = (int) $request->getParam('id', 0);
        
        if (!$campaign->find()) {
            $this->_redirect('/');
        } else if ($campaign->userId != App_User::getUserId() && !App_User::allow($campaign->userId, false, true)) {
            $this->_redirect('/');
        }
        
        //session is set to check the user account viewed in account info/summary pages
        $iId = null;
        if(isset($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'],'/advertiser/dashboard/id') !== FALSE)){
            $iId = array_pop(explode('/', $_SERVER['HTTP_REFERER']));
        }
        elseif ($campaign->userId != App_User::getUserId() && isset($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'],'/network/dashboard') !== FALSE)){
            $iId = $campaign->userId;
        }
        
        if($iId){
            $session = new Zend_Session_Namespace('view_user');
            $session->viewUserId = $iId;
        }else{
            $session = new Zend_Session_Namespace('view_user');
            if(isset($session->viewUserId)){
                unset($session->viewUserId);
            }
        }

        $this->view->campaignType = $campignTypeId->getCampaignType($campaign->campaign_type_id);

        $user = new Model_User();
        $user->id = $campaign->userId;
        $user->find();
        $userId = $user->id;
        
        $creatives = $campaign->getCreativeIds( $campaign->id );
        $this->view->totalCreatives = count($creatives);
        
        $creativeVideos = $campaign->getCreativeIds($campaign->id, true);
        $campaignWithVideo = 0;
        if (count($creativeVideos) > 0) {
            $campaignWithVideo = 1;
        }
        
        $statuses = new Model_Status_List(false);
        $statuses->initDefault();

        //find Campaign spend
        $list = new Model_Campaign_List(false);   
        $campaignSpend = null;
        if( App_User::isAdmin() ){ 
            $campaignSpend = $list->getCampaignSpend( $campaign->id );                            
        }    
        
        $campaignValues = array( 
            'campaignID' => $campaign->id ,
            'campaignStatus' => $campaign->statusId, 
            'campaignSpend' => $campaignSpend, 
            'userId' => $userId,
            'priorityTypeId' => $campaign->priorityTypeId );
        
        $form = new Default_Form_CampaignEdit( $campaignValues );     
        Model_UserAccessRule::filterForm($form);
        
        
        $form->populateCategories();

        $data = $campaign->toArray();

        $ex = explode(' ', $data['end_date']);
        $data['end_date'] = $ex[0];

        $ex = explode(' ', $data['start_date']);
        $data['start_date'] = $ex[0];

        if ($data['end_date'] == '0000-00-00') {
            $data['end_date'] = '';
        }
        if ($data['start_date'] == '0000-00-00') {
           $data['start_date'] = '';
        }
        
        //if start date or end date is less than current date then disable
        if ($data['start_date'] && (strtotime($data['start_date']) < strtotime(date('Y-m-d')))) {
            $form->start_date->setAttrib('readonly', 'readonly');
        }
        if ($data['end_date'] && (strtotime($data['end_date']) < strtotime(date('Y-m-d')))) {
            $form->end_date->setAttrib('readonly', 'readonly');
        }
        
        $form->populate($data);
        /* day parting */
        $dp = new Model_CampaignDayparting();
        $this->view->campaign = $campaign;


        // re-apply timezone
        if (!$data['timezone']) {
            $time = new DateTime('now', new DateTimeZone('GMT'));
        } else {
            $time = new DateTime('now', new DateTimeZone($data['timezone']));
        }

        $dateparts = $dp->getForCampaign($campaign)->toArray();
        $cnt = count($dateparts);
        for ($k = 0; $k < $cnt; $k++) {
            $dateparts[$k]['start_hour'] = date('G', strtotime($dateparts[$k]['time_start']));
            $dateparts[$k]['end_hour'] = date('G', strtotime($dateparts[$k]['time_end']));

        }
        $form->getElement('daypart')->setValue(json_encode($dateparts));        
        $form->day_parting->setValue(count($dateparts) > 0 ? 1 : 0);
        $values = array();      
        

        //populate exclude device in quick block
        $excluded_device_vals   = array();
        //$excluded_devices       = $campaign->loadCampignExcludedDevices();
        $excluded_devices       = $campaign->loadCampignExcludedDevicesWithNames();

        $excludeHandsetNames    = array();
        $countr                 = 0;
        foreach ($excluded_devices as $device) {
            $excludeHandsetNames[$countr]['i']  =   $device->id;
            $excludeHandsetNames[$countr]['n']  =   $device->name;
            $excludeHandsetNames[$countr]['b']  =   $device->brand_id;
            $form->excludes_handset_ids->addMultiOption($device->device_id, $device->device_id . '[' . $device->user_role . ']');
            $excluded_device_vals[] = $device->device_id;
            $countr++;
        }
        //set the excluded handset values to form
        $form->excludes_handset_ids->setAttrib('device-values', json_encode($excludeHandsetNames));
        $form->excludes_handset_ids->setValue($excluded_device_vals);
        $hasExtendedCategories = false;
        if (App_User::isAdmin()) {
            $cats = new Model_CampaignCategory;
            $list = $cats->fetchByCampaign($campaign->id);
            $values[$campaign->categoryId] = $campaign->categoryId; // get first category from main table
            foreach ($list as $cat) {
                $values[$cat['category_id']] = (int) $cat['category_id'];
            }
            $hasExtendedCategories = (count($values) > 0) ? true : false;
            $form->category_ids->setValue($values);
            
            $campaignGoal = new Model_CampaignGoal();
            $goalDetail = $campaignGoal->getForCampaign($campaign->id)->toArray();
            if (!empty($goalDetail)) {
                if ($goalDetail[0]['goal_type_id'] == '1' || $goalDetail[0]['goal_type_id'] == '2') {
                    $goalArr = $goalDetail[0];
                    if (!empty($goalDetail[1])){
                        $performanceArr = $goalDetail[1];
                    }
                }else {
                    if (!empty($goalDetail[1])){
                        $goalArr = $goalDetail[1];
                    }
                    $performanceArr = $goalDetail[0];
                }
                
                if(!empty($goalArr)){
                    $goalVal = (int) $goalArr['goal'];
                    $form->goal_type_id->setValue($goalArr['goal_type_id']);
                    if ($goalArr['goal_type_id'] == '1') {
                        $form->impression_goal->setValue($goalVal);
                    } else {
                        $form->click_goal->setValue($goalVal);
                    }
                }
                
                if (!empty($performanceArr)) {
                    $form->performance_type_id->setValue($performanceArr['goal_type_id']);
                    if ($performanceArr['goal_type_id'] == '3') {
                        $form->ctr_goal->setValue($performanceArr['goal']);
                    } else {
                        $form->conversion_goal->setValue($performanceArr['goal']);
                    }
                }
            }
        }

        $form->daily_budget->setValue(number_format($form->daily_budget->getValue(), 2, '.', ''));
        $form->total_budget->setValue(number_format($form->total_budget->getValue(), 2, '.', ''));
        
        if ($form->total_budget->getValue() > 0) {
            $form->spend_cap->setValue('2');
        } else {
            $form->spend_cap->setValue('1');
        }
        
        $form->campaign_conversions->setValue(number_format($form->campaign_conversions->getValue(), 2, '.', ''));
        
        $this->view->form = $form;

        $cStatus = $statuses->findItemByProperty('id', $campaign->statusId);
        if ($cStatus instanceof Model_Status) {
            $this->view->cStatus = $cStatus;
        } else {
            $this->view->cStatus = new Model_Status();
        }
        
        //user information for js
        $userInfo = array("id"=>$user->id, "name"=>$user->fname." ".$user->lname, "created"=>date('Y-m-d', strtotime($user->created)));
        $form->getElement('user_data')->setValue(json_encode($userInfo));
        //get user created date.
        $this->view->user_created_date = date('Y-m-d', strtotime($user->created));
        $this->view->userId = $campaign->userId;
        $this->view->user = $user;
        $this->view->partner = Model_Partner::getUserPartner($user->id);
        $this->view->mainUserId = App_User::getMainUserId();
        
        $campaignEditOverviewPageInfo = array(
            'main_user_id' => $this->view->mainUserId,
            'logged_in_user_id' => App_User::getUserId(),
            "access_rules" => json_encode(Model_UserAccessRule::getRules($this->view->mainUserId)),
            'campaign_id' => $campaign->id,
            'campaign_name' => $campaign->name,
            'has_extended_categories' => $hasExtendedCategories,
            'campaign_level_targeting' => $campaign->campaignLevelTargeting,
            'campaign_type' => $campaign->campaign_type_id,
            'campaign_with_video' => $campaignWithVideo);
        $this->view->campaignEditOverviewPageInfo = $campaignEditOverviewPageInfo;
        
    }

    public function exportCampaignDashboardAction() {
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        
        //collect the parameters
        $request = $this->getRequest();

        $params = array();
        $criteria = $request->getParam('export_csv_criteria');
        parse_str($criteria, $params);

        $userId = App_User::getUserId();
        if (!empty($params['user_id'])) {
            $userId = $params['user_id'];
        }

        //validate user access
        if ($userId && !App_User::allow($userId, false, true)) {
            $userId = App_User::getUserId();
        }

        $campaignIds = array();
        if (!empty($params['campaign_id'])) {
            $campaignIds = explode(',', $params['campaign_id']);
        }
        $statusIds = array();
        if (!empty($params['status_id'])) {
            $statusIds = explode(',', $params['status_id']);
        }
        $period = $startDate = $endDate = null;
        if (!empty($params['period'])) {
            $period = $params['period'];
        }
        if (!empty($params['start_date'])) {
            $startDate = $params['start_date'];
        }
        if (!empty($params['end_date'])) {
            $endDate = $params['end_date'];
        }

        $orderBy = array('id' => array('field_name' => 'created', 'direction' => 'desc'));

        //get actual page data
        $creatives = Tapit_AdvertiserStats::getDashboardCreatives($campaignIds, $statusIds, $period, $startDate, $endDate, $orderBy);
        
        $reportCols = array(
            'name' => array('label' => 'Creative'),
            'status_name' => array('label' => 'Status'),
            'weight_percentage' => array('label' => 'Weight %'),
            'weight' => array('label' => 'Weight'),
            'impressions' => array('label' => 'Impressions'),
            'clicks' => array('label' => 'Clicks'),
            'conversions' => array('label' => 'Conversions'),
            'cpa' => array('label' => 'CPA'),
            'ctr' => array('label' => 'CTR %'),
            'avg_cpc' => array('label' => 'Avg. Bid'),
            'costs' => array('label' => 'Cost'));

        $exportReport = array();
        foreach ($creatives as $creative) {
            $exportReport[] = $creative;
        }
        
        Tapit_Utils::exportToCSV($reportCols, $exportReport, 'Advertiser_Campaign_Dashboard');
    }
    
    public function creativeEditOverviewAction()
    {

        $request = $this->getRequest();

        $creative = new Model_Creative();
        $creative->id = $request->getParam('id');
        
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
      
        if (!$creative->find()) {
            $this->_redirect('/');
        }
       
        $campaign = new Model_Campaign();
        $campignTypeId = new Model_CampaignType();
        $campaign->id = $creative->campaignId;

        if($campaign->target_device_type_id == '2'){
            $campaignDevices = new Model_CampaignIncludeDevice();
            $campaignDevices->campaignId = $campaignId;

            $this->view->totalSelectedDevices = $campaignDevices->countDevicesSelected();
        }
        
        $campaign->find();
        $this->view->minBid = $campaign->getMinBid($campaign->campaign_type_id);
        //get campign type Name from id				
        //$campaign->campaign_type_id  = $campignTypeId->getCampaignType( $campaign->campaign_type_id );										
        $this->view->campaignType = $campignTypeId->getCampaignType($campaign->campaign_type_id);
        $user = $this->getUser($campaign->userId);
        /**
         * @todo refactor strange logic
         */
        if (!App_User::allow($campaign->userId, false, true) && App_User::getUserId() != $campaign->userId) {
            $this->_redirect('/');
        }
				
        $manager = new Default_Form_Manager_Creative();
        $manager->setRequest($request);
        $elements = $manager->getEditFormElements($campaign, $creative);
                
        $form = new Default_Form_Creative(array('elements' => $elements, 'campaignTypeId' => $campaign->campaign_type_id));
        Model_UserAccessRule::filterForm($form);
        
        $manager->setFormValues($form);
        $manager->populateCreativeData($form);

        $form->populate($creative->toArray());
        
        if($creative->creative_type_id == '6' && $form->allowVideoAds()) {
            $this->view->videoEvents = Model_CreativeVideoEvent::getForCreative($creative->id);
            
            $form->action_type_id->setValue('video');
            $form->additional_pixels->setValue(2);
            if(count($this->view->videoEvents) > 0){
                $form->additional_pixels->setValue(1);
            }
            
            $form->source->setValue(1);
            if ($creative->source_type_id == '3') {
                $form->source->setValue(2);
                $form->vast_url->setValue($creative->html);
            } else {

                $videoForm = new Default_Form_VideoUpload();
                $this->view->video_preview = $videoForm->getVideoUrl($creative->campaign_id, $creative->id, 320, 480);
            }
        }
        
        //we are storing phone number in click url 
        //https://tapitmedia.atlassian.net/browse/TAPITUI-786
        if ($creative->actionTypeId== 2 ) {
            $phone = $creative->clickurl;
            if ($phone) {
                $phone = preg_replace('/[^\d]/', '', $phone );
            }
            $form->phone->setValue($phone);
            $form->clickurl->setValue('');
        }
        
        $form->ad_image_id->setValue($creative->creativeIconId);
        $manager->populateIcon($form);
                
        
        if($creative->adtextcolor == '' ){
            $form->adtextcolor->setValue('4b4c4d');
        }
        if($creative->adtitlecolor == '' ){
            $form->adtitlecolor->setValue('3b5898');
        }                
        if($creative->adbackgroundcolor == '' ){
            $form->adbackgroundcolor->setValue('ffffff');
        }
        if($creative->adbordercolor == '' ){
            $form->adbordercolor->setValue('3b5898');
        }
        if($creative->adsize == '' || $creative->adsize == '0'){
            $form->adsize->setValue('2');
        }
        if($creative->adtextfont == '' || $creative->adtextfont == '0'){
            $form->adtextfont->setValue('2');
        }
        if($creative->adtitlefont == '' || $creative->adtitlefont == '0'){
            $form->adtitlefont->setValue('2');
        }
        if($creative->adtitlefontsize == '' || $creative->adtitlefontsize == '0'){
            $form->adtitlefontsize->setValue('18');
        }
        if($creative->adtextfontsize == '' || $creative->adtextfontsize == '0'){
            $form->adtextfontsize->setValue('16');
        }
		
                
        if($request->isPost())
        {
            $postValues =   $request->getParams();
           
            if($postValues['action_type_id']==2)
            {
                $request->setParam("clickurl","http://");

            }  
            else
            {
                $request->setParam("phone","000000");
            }
            
            if ($postValues['creative_type_id'] != 3 && $postValues['creative_type_id'] != 8) {
                $request->setParam("source_type_id", "1");
            }     
            
            //removes script validator for ad type HTML
            if ($postValues['creative_type_id'] != 8){
                $htmlElement = $form->getElement('html');
                $htmlElement->removeValidator('App_Validator_Script');
            }
            
            //remove destination url validation if vast url is selected
            if ($postValues['action_type_id'] == 'video') {
                if ($postValues['source'] == '2') {
                    $clickurl = $form->getElement('clickurl');
                    $clickurl->setRequired(false);
                }
            } else if ($form->allowVideoAds()){
                $delivery = $form->getElement('delivery');
                $delivery->setRequired(false);
                
                $placement = $form->getElement('placement');
                $placement->setRequired(false);
            }
            
            //remove vast url validation if source is file
            if (isset($postValues['source']) && $postValues['source'] != '2'){
                $vastUrlElement = $form->getElement('vast_url');
                $vastUrlElement->setRequired(false);
            }
        }
        
        if($request->isPost() && $form->isValid($request->getParams())) {

            $fields = $form->getValues();	
                 
            if($postValues['action_type_id']==2)
            {
                $request->setParam("clickurl","");
               $fields['clickurl']  =   "";
            }  
            else
            {
                $request->setParam("phone","");
                $fields['phone']  =   "";
            }
            $form->setDefaults($fields);
            /**
             * @todo check for security
             */                       
        
            $updateResult = $manager->updateCreative($form, $request);
            $manager->saveCreativeEvents($creative, $request->getParams());
            
            //cleanup uploaded image directory.
            $userImageDirPath = PUBLIC_PATH . "/media/{$campaign->userId}/";
            Tapit_Utils::cleanUploadedImage($userImageDirPath);
            
            //if transcode fails user should be able to edit the creative to upload again
            if ($updateResult['error']) {
                $this->_helper->flashMessenger->addMessage('Transcoding failed due to some reason. Please edit your creative to upload again.');
                $this->_redirect('/advertiser/creative-edit-overview/id/' . $creative->id);
            }
            
            //$this->_redirect('/advertiser/edit-creative-targeting-bid/creative_id/' . $creative->id);
            if($request->getParam('back')) {
                $this->_redirect('/advertiser/campaign-edit-overview/id/' . $campaign->id);
            }

            // submit another creative
            if($request->getParam('another')) {
                $this->_redirect('/advertiser/create-campaign-creatives/id/' . $campaign->id);
            }

            // redirect to campaign dashboard
            if($request->getParam('finish')) {
                $this->_redirect('/advertiser/campaign-edit-overview/id/'. $campaign->id);
            }
        }
								
        $this->view->campaign = $campaign;
        $this->view->form = $form;
        
        //session getting swap
        $token = App_User::getLoggedUser()->getToken();
        if (App_User::isChildUser()) {
            $mainUser = new Model_User();
            $mainUser->id = App_User::getMainUserId();
            $mainUser->find();
            $token = $mainUser->getToken();
        }
        $this->view->token = $token;
    }


    public function updateCreativesStatusAction()
    {
        $request = $this->getRequest();
        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }
        
        //validate ajax call
        if (!App_User::isValidAjaxCall($request, 'advertiser-update-creative-status')) {
            echo json_encode(array());
            exit(0);
        }
        
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
       
        $ids = $request->getParam('creative_ids', '');
        $statusId = $request->getParam('status_id', '');

		
        $validStatuses = array(4,6,7,8,9);

        $idsArray = explode('_', $ids);
        foreach ($idsArray as $key => $value) {
            $idsArray[$key] = (int) $value;
        }
        $objCreative = new Model_Creative;
        $campaignId = $objCreative->fetchCampaignId($idsArray[$key]);
        //non-admin user should not make creative pending-runing.
        $filterStatusChanges = array(
            6 => array(2, 3, 7),
            7 => array(2, 3, 6),
            8 => array(6));
        if (array_key_exists($statusId, $filterStatusChanges) && !App_User::isAdmin()) {
            $creativeList = new Model_Creative_List(false);
            $creativeList->setWhere('id IN(' . implode(',', $idsArray) . ')');
            $creativeList->initDefault();
            $idsArray = array();
            foreach ($creativeList as $creative) {
                if (in_array($creative->statusId, $filterStatusChanges[$statusId])) {
                    $idsArray[] = $creative->id;
                }
            }
        }
        
        if(in_array($statusId, $validStatuses) && count($idsArray) > 0) {

            $list = new Model_Creative_List(false);
            $list->updateStatus(App_User::getLoggedUser(), $statusId, $idsArray);
            $list->initByPrimaryArray('id', $idsArray);

            $statuses = new Model_Status_List(false);
            $statuses->initDefault();

            $output = array();
            foreach ($list as $item) {

                $elem = array();
                $elem['id'] = $item->id;
                //error_log($item->statusId);
                $elem['status_id'] = $item->statusId;
                $status = $statuses->findItemByProperty('id', $item->statusId);

                if($status) {
                    $elem['status'] = $status->name;
                }
                $output[] = $elem;
            }
            $this->view->items = $output;
        }
        
        $objCampaign = new Model_Campaign;
        $objCampaign->updateCreativeWeights($campaignId);
        $this->view->weights = $objCampaign->getCreativesWeightPercentage($campaignId);

        $this->asJSON();
    }


    public function renameCreativeAction()
    {
        $request = $this->getRequest();
        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }
        
        //validate ajax call
        if (!App_User::isValidAjaxCall($request, 'advertiser-rename-creative')) {
            echo json_encode(array());
            exit(0);
        }
        
        $creativeId = $request->getParam('id');
        $name = (string) $request->getParam('name');

        $creative = new Model_Creative();
        $creative->id = $creativeId;

        if($creative->find()) {
            $campaign = $creative->loadCampaign();
            if($campaign->userId == App_User::getUserId()) {

                $form = new Default_Form_Creative();
                if($form->name->isValid($name)) {
                    $creative->name = $name;
                    $this->view->updated = $creative->update();
                }
            }
        }


        $this->asJSON();
    }

    public function renameCampaignAction()
    {
        $request = $this->getRequest();
        
        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }
        
        //validate ajax call
        if (!App_User::isValidAjaxCall($request, 'advertiser-rename-campaign')) {
            echo json_encode(array());
            exit(0);
        }

        $id = $request->getParam('id');
        $name = (string) $request->getParam('name');

        $campaign = new Model_Campaign();
        $campaign->id = $id;

        if($campaign->find()) {

            if($campaign->userId == App_User::getUserId() || App_User::allow($campaign->userId, false, true)) {

                $form = new Form_Campaign();
                if($form->name->isValid($name)) {
                    $campaign->name = $name;
                    $this->view->updated = $campaign->update();
                }
            }
        }


        $this->asJSON();
    }


    public function updateCampaignStatusAction()
    {
        //make sure to validate AJAX request.
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        //validate ajax call
        if (!App_User::isValidAjaxCall($request, 'advertiser-update-campaign-status')) {
            echo json_encode(array());
            exit(0);
        }
        
        $ids = $request->getParam('campaign_id', '');
        $statusId = $request->getParam('status_id', '');
        
        $validStatuses = array( 4,6, 7, 8, 9 );

        $idsArray = explode('_', $ids);
        foreach ($idsArray as $key => $value) {
            $idsArray[$key] = (int) $value;
        }
        
        //non-admin user should not make pending-runing.
        $filterStatusChanges = array(
            6 => array(2, 3, 7),
            7 => array(2, 3, 6, 10),
            8 => array(6));
        if (array_key_exists($statusId, $filterStatusChanges) && !App_User::isAdmin()) {
            $campaignList = new Model_Campaign_List(false);
            $campaignList->setWhere('id IN(' . implode(',', $idsArray) . ')');
            $campaignList->initDefault();
            $idsArray = array();
            foreach ($campaignList as $camp) {
                if (in_array($camp->statusId, $filterStatusChanges[$statusId])) {
                    $idsArray[] = $camp->id;
                }
            }
        }
        
        if(in_array($statusId, $validStatuses) && count($idsArray) > 0) {

            //get the user.
            if ($userId = $request->getParam('user_id', '')) {
                $user = new Model_User();
                $user->id = $userId;
                $user->find();
            } else {
                $user = App_User::getLoggedUser();
            }

            $list = new Model_Campaign_List(false);
            
            $list->updateStatus($user, $statusId, $idsArray);
            $list->initByPrimaryArray('id', $idsArray);

            $statuses = new Model_Status_List(false);
            $statuses->initDefault();

            $output = array();
            foreach ($list as $item) {

                $elem = array();
                $elem['id'] = $item->id;
                $elem['status_id'] = $item->statusId;
                $status = $statuses->findItemByProperty('id', $item->statusId);

                if($status) {
                    $elem['status'] = $status->name;
                }
                $output[] = $elem;
            }
            $this->view->items = $output;
        }

        $this->asJSON();
    }


    public function updateCampaignAction()
    {
        $request = $this->getRequest();
        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        
        $post = $request->getPost();
        $campaignId = (int) $request->getParam('id', 0);
        $userId = App_User::getUserId();
        
        $campaign = new Model_Campaign();
        $campaign->id = $campaignId;
        //compare below to change bid value
        $campaign->find();
        $campaignType = $campaign->campaignTypeId;
        $isAdmin = App_User::isAdmin();
        $categoryGroup = isset($_POST['category_ids']) ? $_POST['category_ids'] : array();
        
        $campaignValues = array(
            'campaignID' => $campaign->id,
            'campaignStatus' => $campaign->statusId,
            'userId' => $campaign->userId,
            'priorityTypeId' => $campaign->priorityTypeId);
        
        $form = new Default_Form_CampaignEdit($campaignValues);
        $form->populateCategories();
        
        //empties the goal values if corresponding option is not selected
        if ($isAdmin) {
            if ((isset($post['goal_type_id']) && !empty($post['goal_type_id']))) {
                if ($post['goal_type_id'] == '1')
                    $post['clickgoal'] = 0;
                else
                    $post['impgoal'] = 0;
            }
            if ((isset($post['performance_type_id']) && !empty($post['performance_type_id']))) {
                if ($post['performance_type_id'] == '3')
                    $post['convgoal'] = 0;
                else
                    $post['ctrgoal'] = 0;
            }
        }
        
        //remove conversion element validations.
        $dailyBudgetError = $dateError = false;
        if ($request->isPost()) {
            $postData = $request->getPost();
            if (empty($postData['campaign_type_id']) || $postData['campaign_type_id'] != 3) {
                $converionElement = $form->getElement('campaign_conversions');
                if ($converionElement) {
                    $converionElement->removeValidator('Zend_Validate_Float');
                    $converionElement->removeValidator('Zend_Validate_Between');
                    $converionElement->setRequired(false);
                }
            }
            
            $startDateElement = $form->getElement('start_date');            
            $endDateElement = $form->getElement('end_date');
            
            //remove >current date validator if existing start date is less than current date
            
            if (strtotime($campaign->start_date) && (strtotime($campaign->start_date) < strtotime(date('Y-m-d')))) {
                $startDateElement->removeValidator('App_Validator_NoPastDate');
            }
            //remove >current date validator if existing end date is less than current date
            if (strtotime($campaign->end_date) && (strtotime($campaign->end_date) < strtotime(date('Y-m-d')))) {
                $endDateElement->removeValidator('App_Validator_NoPastDate');
            }
            
            //remove validators if corresponding spend cap is not selected
            if ($postData['spend_cap'] == '1') {
                $campaignBudgetElement = $form->getElement('total_budget');
                $campaignBudgetElement->setRequired(false);
                $campaignBudgetElement->removeValidator('Zend_Validate_Between');

                $endDateElement->setRequired(false);
            } else {
                $dailyBudgetElement = $form->getElement('daily_budget');
                $dailyBudgetElement->setRequired(false);
                $dailyBudgetElement->removeValidator('Zend_Validate_Between');
                
                $totalBudget = $postData['total_budget'];
                $startDate = $postData['start_date'];
                $endDate = $postData['end_date'];
                $dailyMinBudget = Model_Campaign::getDailyMinBudget();
                $dailyMaxBudget = Model_Campaign::getDailyMaxBudget();
                $elem = $form->getElement('total_budget');
                if ($endDate && $startDate && $elem->isValid($totalBudget)) {
                    $flightPeriod = CH_Date::getDatesDiff($endDate, $startDate) + 1;
                    if ($flightPeriod <= 0) {
                        $calDailyBudget = 0;
                    } else {
                        $calDailyBudget = number_format( ($totalBudget / $flightPeriod), 2, '.', '' );
                    }
                    if ($calDailyBudget < $dailyMinBudget) {
                        $dailyBudgetError = true;
                        $elem->setErrors(array('invalidBudget' => "Your budget is too low for the flight period. Either raise your budget or decrease your flight dates so that your total daily spend is at least $".$dailyMinBudget));
                        $form->populate($postData);
                    } else if ($calDailyBudget > $dailyMaxBudget) {
                        $dailyBudgetError = true;
                        $elem->setErrors(array('invalidBudget' => "Your budget is too high for the flight period. Either decrease your budget or raise your flight dates so that your total daily spend is at max $".$dailyMaxBudget));
                        $form->populate($postData);
                    }
                }
            }
            
            $endDate = $startDate = null;
            if (isset($postData['end_date'])) {
                $endDate = $postData['end_date'];
            }
            if (isset($postData['start_date'])) {
                $startDate = $postData['start_date'];
            }
            if ($endDate && CH_Date::getDatesDiff($startDate, $endDate) > 0) {
                /* @var $elem Zend_Form_Element */
                $dateError = true;
                $elem = $form->end_date;
                $elem->setErrors(array('invalidDate' => 'Campaign end date should be greater than or equal to start date.'));
            }
            
            //start date should not been less than created
            $startDate = null;
            if (isset($postData['start_date'])) {
                $startDate = $postData['start_date'];
            }
            if ($startDate && CH_Date::getDatesDiff($campaign->created, $startDate) > 0) {
                /* @var $elem Zend_Form_Element */
                $dateError = true;
                $form->start_date->setErrors(array('invalidDate' => 'Campaign start date should be greater than or equal to created : ' . date( 'd M, Y', strtotime($campaign->created) )) );
            }
        }
            
        if (!$dateError && !$dailyBudgetError && $request->isPost() && $campaign->find() && ($campaign->userId == $userId || $isAdmin) && $form->isValid($request->getPost())) {
            
            $campaign->setOptions($form->getValues());	
            
            //$campaign->updateExcludedSites( $form->excludes_site_ids->getValue() );								
            $campaign->updateExcludedDevice( $form->excludes_handset_ids->getValue() );		
            //$campaign->updateIncludedSites( $form->includes_site_ids->getValue() );								
            //$campaign->updateIncludedDevice( $form->includes_handset_ids->getValue() );
            $newCampaignType = $form->campaign_type_id->getValue();
            if($newCampaignType != $campaignType){                
                if($newCampaignType == '2'){     
                    if($isAdmin){
                        $campaign->cpcBid = 0.3;
                    }else{
                        $campaign->cpcBid = 0.6;
                    }
                } else if ($newCampaignType == '3') { // CPA campaigns
                    if ($isAdmin) { // Admin only
                        $campaign->cpcBid = 0.6;
                    }
                } else {
                    $campaign->cpcBid = 0.1;
                }
            }
                     
            // date parting update
            $dp  = new Model_CampaignDayparting();
             
            
             if($post['day_parting'] == 1)
             {                	                
             	$dp->replaceForCampaign($campaign, $post['day_part'], $post['timezone']);
             
             }
             else
             {                    
             	$dp->replaceForCampaign($campaign, array());
                //set the status to Running if dap parting disabled for schedule campaigns only
                if( $campaign->statusId == 10 )
                {
                    $campaign->statusId  = 6;
                }
             }
              
                                       
            // Reassignment of Campigns
            if ($isAdmin) {
                if (!empty($post['assign_to'])) {
                    $campaign->userId = $post['assign_to'];
                }
            }   
            
            //removing budget value if corresponding spend cap is not selected
            if ($postData['spend_cap'] == '1') {
                $campaign->total_budget = 0.0000;
                $campaign->daily_budget = $form->daily_budget->getValue();
            } else {
                $campaign->total_budget = $form->total_budget->getValue();
                $dailyBudget = 0;
                if ($postData['total_budget'] && $postData['start_date'] && $postData['end_date']) {
                    $dailyBudget = $postData['total_budget'] / (1+Tapit_Utils::daysBetweenDates($postData['start_date'], $postData['end_date']));
                }
                $campaign->daily_budget = $dailyBudget;
            }
            $campaign->endDate = empty($endDate) ? '0000-00-00 00:00:00' : date('Y-m-d 23:59:00', strtotime($endDate));
            $campaign->update();    
              
            // only update if set, different for advertisers vs admins
            //added $isAdmin condition so that admin can delete from db if none are selected
            if($isAdmin || isset($_POST['category_ids']))
            {
                //unset primary category id from extended if found
                if (($key = array_search($campaign->category_id, $categoryGroup)) !== false) {
                    unset($categoryGroup[$key]);
                }
                
            	$categories = new Model_CampaignCategory();
            	$categories->updateCampaignCategories($campaign, $categoryGroup);
            }

            //save values for pacing indicator and performance indicator
            if ($isAdmin) {
                if ((isset($post['goal_type_id']) && !empty($post['goal_type_id']))  || (isset($post['performance_type_id']) && !empty($post['performance_type_id']))) {
                    
                    $campaignGoal = new Model_CampaignGoal();
                    $campaignGoal->remove($campaign->id);
                    
                    if(isset($post['goal_type_id']) && !empty($post['goal_type_id'])){ //pacing indicator 
                        $campaignGoal->campaign_id = $campaign->id;
                        $campaignGoal->goalTypeId = $post['goal_type_id'];
                        $campaignGoal->goal = ($post['goal_type_id'] == '1') ? $post['impgoal'] : $post['clickgoal'];
                        $campaignGoal->save();
                    }

                    if (isset($post['performance_type_id']) && !empty($post['performance_type_id'])) { //performance Indicator
                        $campaignGoal->campaign_id = $campaign->id;
                        $campaignGoal->goalTypeId = $post['performance_type_id'];
                        $campaignGoal->goal = ($post['performance_type_id'] == '3') ? $post['ctrgoal'] : $post['convgoal'];
                        $campaignGoal->save();
                    }
                }
            }

            $this->view->updated = true;

        } else {
            $this->view->error = true;
            $this->view->errors = $form->getMessages();
        }

        $this->asJSON();
    }

    public function getAllCreativesAction()
    {
        $request = $this->getRequest();
        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }
        
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();


        $list = new Model_Carrier_List(false);
        $list->loadForTargetingForm();

        $countries = new Model_Country_List(false);
        $countries->initDefault();

        $this->view->carriers = $list->toArray();
        $this->view->countries = $countries->toArray();


        $this->asJSON();
    }


    public function getAllDevicesAction()
    {
        $request = $this->getRequest();
        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        $devices = new Model_Device_List(false);
        $devices->initDefault();

        $brands = new Model_Brand_List(false);
        $brands->initDefault();


        $this->view->devices = $devices->toArray();
        $this->view->brands = $brands->toArray();

        $this->asJSON();
    }


    public function getReportTableAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        $request = $this->getRequest();

        $form = new Default_Form_AdvertiserAdvancedOptions();

        $items = $request->getParam('items');
        if(is_array($items)) {
            foreach ($items as $itemId) {
                $form->items->addMultiOption((int) $itemId);
            }
        }

        $subItems = $request->getParam('sub_items');
        if(is_array($subItems)) {
            foreach ($subItems as $subItemId) {
                $form->sub_items->addMultiOption((int) $subItemId);
            }
        }

        if($form->isValid($request->getPost())) {

            $list = new Model_AdvertiserTable_List();
            $list->loadFromForm($form);

            $this->view->report = $list->toArray();
            $this->view->currentPage = $list->page;
            $this->view->pageRange = $list->paginatorInstance()->count();

        } else {
            CH_Logger::log('errors:');
            CH_Logger::log($form->getMessages());
        }


        $this->asJSON();
    }


    public function saveReportAction()
    {
        $request = $this->getRequest();

        $form = new Default_Form_ReportFilterAdvertiser();
        $form->removeElement('user_report_id');
        $reportName = new Zend_Form_Element_Text('report_name');
        $reportName->setRequired();
        $reportName->addFilter(new Zend_Filter_StripTags());

        if($form->isValid($request->getPost()) && $reportName->isValid($request->getParam('report_name', ''))) {

            $report = new Model_UserReport();
            $report->userId = App_User::getUserId();
            $report->name = $reportName->getValue();
            $report->type = Model_UserReport::TYPE__ADVERTISER;
            if($report->loadFromFields(array('userId', 'name', 'type'))) {

                $report->details = serialize($form->getValues());
                $report->update();

            } else {

                $report->details = serialize($form->getValues());
                $report->save();
                $this->view->id = $report->id;

            }
        }

        $this->asJSON();
    }

    public function getUserReportAction()
    {
        $request = $this->getRequest();
        $id = (int) $request->getParam('id');

        $report = new Model_UserReport();
        $report->id = $id;

        if($report->find() && $report->userId == App_User::getUserId()) {

            $this->view->reportDetails = unserialize($report->details);

        }


        $this->asJSON();
    }

    public function csvExportAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();

        $request = $this->getRequest();
        $form = new Default_Form_ReportFilterAdvertiser();

        if($form->isValid($request->getPost())) {

            $list = new Model_AdvertiserTable_List(false);
            $csv = $list->toCSV($form);
            echo $csv;
        }
    }

    public function copyCreativesAction()
    {
        /**
         * @todo please refactor this method
         */
        $request = $this->getRequest();
        
        //make sure to validate AJAX request.        
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }
        
        //validate ajax call
        if (!App_User::isValidAjaxCall($request, 'advertiser-copy-creatives')) {
            echo json_encode(array());
            exit(0);
        }
                 
        $ids = (string) $request->getParam('ids', null);
        $idsArray = explode('_', $ids);
        foreach ($idsArray as $key => $value) {
            $idsArray[$key] = (int) $value;
        }

        $list = new Model_Creative_List(false);
        $list->initByPrimaryArray('id', $idsArray);
        $list->loadCreativeDimensions();
        $copyList = $list;
        $newCreatives = new Model_Creative_List(false);
        foreach ($list as $creative) {

            /* @var $creative Model_Creative */

            $source = clone $creative;
            $sourceId = $creative->id;
            $creative->id = null;
            $creative->statusId = 1;
            $creative->name = 'Copy of ' . $creative->name;
            $creative->created = null;
            $creative->save();            
            $this->copyDestinationURL($creative, $sourceId);  
            $objCampaign = new Model_Campaign();
            $objCampaign->updateCreativeWeights($creative->campaign_id);
            $this->copyDimensionCreatives($creative, $sourceId);            
            
            if($creative->creative_type_id == '6'){
                $objMedia = new Model_CreativeMedia();
                $objMedia->copyCreativeMedia($source, $creative);
                $objMediaEncoding = new Model_CreativeMediaEncoding();
                $objMediaEncoding->copyCreativeMediaEncoding($source, $creative);
            }
            
            $arr = array($sourceId => $creative->toArray());
        }
        
        $this->view->creatives = $arr;
        $this->asJSON();
    }

    /**
     * Copy of the rotate url
     */
    public function copyDestinationURL( $creative, $sourceId )
    {
               
        if ($creative->rotateUrls != 1) {
            return;
        }
        
        $creativeDestination = new Model_CreativeDestination();            
        $urlData =  $creativeDestination->getUrls( $sourceId ) ;            
        foreach( $urlData as $destinationUrl )
        {                                   
           $creativeDestination->creativeId =  $creative->id ;
           $creativeDestination->url        =  $destinationUrl->url ;
           $creativeDestination->weight     =  $destinationUrl->weight ;
           $creativeDestination->created    =  null;
           $creativeDestination->modified   =  null;
           $creativeDestination->save();
        }
                   
    }       
            
    public function copyCampaignAction() {
        
        $request = $this->getRequest();
        
        $userId = $this->getRequest()->getParam('user_id');
        
        //admin is not able to copy the campaigns issue START
        if ($userId) {
            if (!App_User::allow($userId, false, true)) {
                if ($request->isXmlHttpRequest()) {
                    echo json_encode(array());
                    exit(0);
                } else {
                    $this->_redirect('/');
                }
                $session = new Zend_Session_Namespace('access');
                $session->noAccess = 1;
            }
            $user = new Model_User();
            $user->id = $userId;
            $user->find();
        } else {
            $user = App_User::getLoggedUser();
        }
        //END
        
        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }
        
        //validate ajax call
        if (!App_User::isValidAjaxCall($request, 'advertiser-copy-campaign')) {
            echo json_encode(array());
            exit(0);
        }
        
        
        $ids = (string) $request->getParam('ids', null);
        $idsArray = explode('_', $ids);
        foreach ($idsArray as $key => $value) {
            $idsArray[$key] = (int) $value;
        }

        $list = new Model_Campaign_List(false);
        $list->loadUserCampaigns($idsArray, $user);

        $output = array();
        foreach ($list as /* @var $campaign Model_Campaign */ $campaign) {
            $originalId = $campaign->id;

            $campaign->id = null;
            $campaign->statusId = 1;
            $campaign->name = 'Copy of ' . $campaign->name;
            $campaign->created = null;
            $campaign->modified = null;
            $campaign->start_date = date("Y-m-d");
            
            if ((strtotime($campaign->end_date) < strtotime($campaign->start_date)) && ($campaign->end_date != '0000-00-00 00:00:00')) {
                $campaign->end_date = date("Y-m-d").' 23:59:00';
            }
            $campaign->save();

            $campaign->created = null;

            $c = new Model_Campaign();
            $c->id = $originalId;
            @$this->copyCreatives($campaign, $c);

            //$excSite = new Model_CampaignExcludeSite();
            //$excSite->copyCampaign($c, $campaign);
            //$incSite = new Model_CampaignIncludeSite();
            //$incSite->copyCampaign($c, $campaign);

            $excDevice = new Model_CampaignExcludeDevice();
            $excDevice->copyCampaign($c, $campaign);

            //copy campaign goals
            $campaignGoal = new Model_CampaignGoal();
            $campaignGoal->copyCampaignGoal($c, $campaign);

            //$incDevice = new Model_CampaignIncludeDevice();
            //$incDevice->copyCampaign($c, $campaign);

            $campaign->copyTargeting($c);
            
            //copy day parting
            $campaignDayParting = new Model_CampaignDayparting();
            $campaignDayParting->copyCampaign($c, $campaign);
            
            //copy extended categories
            $extCategories = new Model_CampaignCategory();
            $extCategories->copyCampaignCategories($c, $campaign);

            $output[$originalId][] = $campaign->toArray();
        }
        $this->view->campaigns = $output;
        $this->asJSON();
    }

    public function excludeSiteAction() {
        $request = $this->getRequest();
        $ids = $request->getParam('ids', null);
        $ids = $this->clearArrayEmpty($ids);

        $creativeId = $request->getParam('creativeId', 0);
        $creative = new Model_Creative();
        $creative->id = $creativeId;
        if ($creative->find()) {
            $creative->excludeSiteIds = (0 != count($ids));
            $creative->update();
            $this->view->result = $creative->excludeSiteIds;
        } else {
            $this->view->result = 0;
        }

        $this->asJSON(true);
    }

    public function updateBidsAction() {
        
        $request = $this->getRequest();
        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }
        
        $this->_helper->layout->disableLayout();
        $creative = new Model_Creative();
        $creative->id = $request->getParam('creativesId');

        if ($creative->find()) {
            $manager = new App_Form_CreativeTargetingManager();
            $options = $manager->getDefaultElements(App_User::getLoggedUser(), $creative->id);
            $form = new Default_Form_CreativeTargeting($options);
            $manager->addDefaultValues($request, $form);
            $manager->addCreativeValues($form, $creative);
            $manager->updateBidRange($form, $creative); 
            $updateBidsForm = $form->getChannelsAndBidsForm();
            
            /**/
            
            $campaign = new Model_Campaign();
            $campaign->id = $creative->campaignId;
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
				
				if(App_User::isAdmin()) $minBid = 0.01;

            /**/
            
            $this->view->minBid = $minBid;
            
            $updateBidsForm->setAttrib('class', '{id:' . $creative->id . '}');
            
        }
        if ($request->getParam('save', 0)) {


            if ($request->isPost() && $updateBidsForm->isValid($request->getPost())) {
                $manager->saveChannelsFromUpdateForm($updateBidsForm, $creative);
                $this->view->result = true;
            } 
            else
             {
                $this->view->result = false;
                $valid = $updateBidsForm->getValidValues($request->getPost());
                $errors = array();                              
                
                foreach ($updateBidsForm->getErrors() as $key => $value) {
                    if (!isset($valid[$key]))
                        $errors[] = $key;
                }
       
                
                $this->view->error = $errors;
            } $this->asJSON(true);
        } else {
            $this->view->form = $updateBidsForm;
        }
    }


    /**
     *
     * @param int $userId
     * @return Model_User
     */
    public function getUser($userId)
    {
        /* @var $user Model_User */
        $user = App_User::getLoggedUser();

        if($user->isAdmin()) {
            return $user;
        } else {
            $user = new Model_User();
            $user->id = $userId;
            $user->find();

            return $user;
        }
    }


    public function copyCreatives(Model_Campaign $newCampaign, Model_Campaign $campaign)
    {

        $creatives = $campaign->getEmptyCreativesList()->loadOrderedForDashBoard();
        $creatives->loadCreativeDimensions();

        

        $parentCreative = null;

        foreach ($creatives as /* @var $creative Model_Creative */ $creative) {
            $newItem = new Model_Creative();
            $newItem->setOptions($creative->toArray());
            $newItem->campaignId = $newCampaign->id;
            /* @var $creative Model_Creative */
            if($creative->parentId == $creative->id) {
                $parentCreative = $newItem;
            } else {
                if($parentCreative) {
                    $newItem->parentId = $parentCreative->id;
                } else {
                    $newItem->parentId = null;
                }
            }


            $newItem->id = null;
            $newItem->created = null;
            $newItem->modified = null;
            $newItem->name = $creative->name;
            $newItem->save();
						
	    $this->copyDestinationURL($newItem, $creative->id);  
            $newItem->setDimensionsList($creative->getDimensionsList());
            $this->copyCampaignDimensionCreatives($newItem, $creative->id, $campaign->id);
            
            
            if($creative->creative_type_id == '6'){
                $objMedia = new Model_CreativeMedia();
                $objMedia->copyCreativeMedia($creative, $newItem);
                $objMediaEncoding = new Model_CreativeMediaEncoding();
                $objMediaEncoding->copyCreativeMediaEncoding($creative, $newItem);
            }

        }
    }
    
    public function copyCampaignDimensionCreatives($creative, $sourceId, $campaignid)
    {

        $amazoneService = new App_AmazonS3();


        foreach ($creative->getDimensionsList() as /* @var $dimension Model_CreativeDimension */ $dimension) {

            $model = new Model_CreativeDimension();
            $model->creativeId = $creative->id;
            $model->dimensionTypeId = $dimension->dimensionTypeId;
            $model->format = $dimension->format;
            $model->created = null;
            $model->save();

            $filePath = $model->getImageSrcUri(
                    $campaignid, $sourceId, $dimension->getDimensionType()
            );

            $amazoneService->copyImage($filePath, $creative->campaignId, $creative->id);
        }
    }

    public function copyDimensionCreatives($creative, $sourceId)
    {

        $amazoneService = new App_AmazonS3();


        foreach ($creative->getDimensionsList() as /* @var $dimension Model_CreativeDimension */ $dimension) {

            $model = new Model_CreativeDimension();
            $model->creativeId = $creative->id;
            $model->dimensionTypeId = $dimension->dimensionTypeId;
            $model->format = $dimension->format;
            $model->created = null;
            $model->save();

            $filePath = $model->getImageSrcUri(
                    $creative->campaignId, $sourceId, $dimension->getDimensionType()
            );

            $amazoneService->copyImage($filePath, $creative->campaignId, $creative->id);
        }
    }
    
    
    private function downloadCsv($headers, $data)
    {
    	$head = '';
    	$body = '';
    	$items = array();
    
   		foreach($headers as $column)
   		{
        	$items[] = sprintf('"%s"', current(preg_split("/\|/",$column['label'])));
       	}     
       	
       	$head = implode(',',$items);   
       	
       	$names = array_keys($headers);
       	
       	foreach($data as $row)
       	{
       		$cells = array();
       		
       		foreach($names as $name)
       		{       	
       			$cell = '';
       			$number = false;
       		
				if($name == 'timeslice')
				{
					if(preg_match("/^\d{6}$/",$row[$name]))
					{
						preg_match("/(\d{4})(\d{2})/", $row['timeslice'], $parts);
						$cell = date('M Y', mktime(0,0,0, $parts[2],1, $parts[1]));
					}
					else
					{
						$cell = preg_replace("/(\d{4})(\d{2})(\d{2})/","$2/$3/$1",$row[$name]);
					}
				}    		 
				elseif(is_numeric($row[$name]))
				{
					$number = true;
					
					if(preg_match("/ctr|ecpm|cpc|conv/",$name))
					{
	    		 		$cell = number_format($row[$name],2);
	    		 	}
	    		 	else
	    		 	{
	    		 		$cell = $row[$name];
	    		 	}	    		 	
	    		}
	    		else
	    		{
					$cell = $row[$name]; 
				}  
				
				$cell = $number ? $cell : sprintf('"%s"',str_replace('"','\\"',$cell));  
				$cells[] = $cell;    	
			}
			
			$body .= implode(',',$cells)."\n";
       	}
       	
       	$output = sprintf("%s\n%s",$head,$body);
       	
       	return $output;       	
 
    }
    
    public function updateBidAction(){
        $request = $this->getRequest();
        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }
        
        //validate ajax call
        if (!App_User::isValidAjaxCall($request, 'advertiser-update-bid')) {
            echo json_encode(array());
            exit(0);
        }
        
        
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        
        $id = $request->getParam('id');
        $bid = $request->getParam('bid');
        
        $campaign = new Model_Campaign();
        $campaign->updateBid($id, $bid);
        
        $this->view->result = true;
        $this->asJSON(true);
    }
    
    public function deprecatedMediaPlannerAction() 
    {
        $tableReport = new Model_PublisherReport_List(false);

        $request = $this->getRequest();
        $params = $request->getParams();        

        $defaults = array('country_name', 'platform_name', 'carrier_name', 'channel_name', 'site_name', 'requests'); 
        $defaultsSelected = array('country_name', 'platform_name', 'carrier_name', 'requests'); 
        $defaultsOrdered = array('country_name' => 'Country', 'platform_name' => 'Platform', 'carrier_name' => 'Carrier', 'channel_name' => 'Channel', 'requests' => 'Impressions', 'site_name' => 'Site'); 
                
        if(isset($params['columns'])){
            $columns = $params['columns'];
        }
        
        $params['columns'] = empty($columns) ? $defaultsSelected : $columns;        
        
        if (isset($params['site_ids']) && $params['site_ids']) {
            $params['site_id'] = explode(',', $params['site_ids']);
        }
        
        $tableData = array();
        $reqColumns = array();        
        $customColumns = array();
        
        foreach ($defaults as $name => $values) {
            if(in_array($values, $params['columns'])){
                $customColumns[] = $values;
                $reqColumns[$values] = $defaultsOrdered[$values];
            }                
        }
        
        $useColumns = $tableReport->getAdPlannerColumnDef($customColumns, false);    
        
        $partner = App_Partner::singleton();
        $this->view->partnerName = $partner->partner['name'];
        $this->view->partnerId = $partner->partner['id'];
        $partnerSites = null;
        
        if($request->isPost()) { 
            $filters = array();
            if($request->getParam('filters')){
                $filters = $request->getParam('filters');
            }
            
            $filterCols = array('site_ids' => 'site_name', 'platform_id' => 'platform_name', 'carrier_id' => 'carrier_name', 'channel_id' => 'channel_name');
            foreach($filterCols as $k => $v){
                if (!in_array($v, $filters)) {
                    unset($params[$k]);
                }
            }
            $params['filters'] = $filters;
            $tableData = $tableReport->generateAdPlannerReport($params);
            //echo '<pre>';print_r($tableData);exit;
        }
       
        //request should be last col.
        $gridCols = array();
        $requestCol = null;
        foreach ($params['columns'] as $col) {
            if ($col == 'requests') {
                $requestCol = $col;
                continue;
            }
            $gridCols[] = $col;
        }
        if ($requestCol) {
            $gridCols[] = $requestCol;
        }
        
        $this->view->selectedColumns = $gridCols;          
        $this->view->columns = $defaultsOrdered;
        $this->view->tableColumns = $useColumns;
        $this->view->tableData = $tableData;
       
        //build the filter form.
        $reportForm = new Default_Form_Report_AdPlanner();
        $reportForm->populate($params);        
        $this->view->reportForm = $reportForm;
    }
    
    public function mediaPlannerAction() 
    {
        $tableReport = new Model_PublisherReport_List(false);

        $defaults = array('country_name', 'platform_name', 'carrier_name', 'channel_name', 'site_name', 'requests'); 
        $defaultsSelected = array('country_name', 'platform_name', 'carrier_name', 'requests'); 
        $defaultsOrdered = array('country_name' => 'Country', 'state_name' => 'State', 'dma_name' => 'DMA', 'platform_name' => 'Platform', 'carrier_name' => 'Carrier', 'channel_name' => 'Channel', 'requests' => 'Impressions', 'site_name' => 'Site');
                
        $reqColumns = array();        
        $customColumns = array();
        
        foreach ($defaults as $name => $values) {
            if(in_array($values, $defaultsSelected)){
                $customColumns[] = $values;
                $reqColumns[$values] = $defaultsOrdered[$values];
            }                
        }
        
        $useColumns = $tableReport->getAdPlannerColumnDef($customColumns, false);    
        
        $partner = App_Partner::singleton();
        $this->view->partnerName = $partner->partner['name'];
        $this->view->partnerId = $partner->partner['id'];
       
        //request should be last col.
        $gridCols = array();
        $requestCol = null;
        foreach ($defaultsSelected as $col) {
            if ($col == 'requests') {
                $requestCol = $col;
                continue;
            }
            $gridCols[] = $col;
        }
        if ($requestCol) {
            $gridCols[] = $requestCol;
        }
        
        $this->view->selectedColumns = $gridCols;          
        $this->view->columns = $defaultsOrdered;
        $this->view->tableColumns = $useColumns;
       
        //build the filter form.
        $reportForm = new Default_Form_Report_AdPlanner();
        $this->view->reportForm = $reportForm;
        
        $mediaPlannerPageInfo = array('partner_id' => $partner->partner['id']);
        $this->view->mediaPlannerPageInfo = $mediaPlannerPageInfo;
    }

    /**
     * Ad Optimizer page
     */
    public function adOptimizerAction() {        
        $adOptForm = new Default_Form_AdOptimizer();
        
        $isAdmin       =  App_User::isAdmin();
        
        $user = App_User::getLoggedUser();      
        
        //access optimizer with campaign id selected
        $campaignId = $this->getRequest()->getParam('campaign_id');
        if ($campaignId) {
            //fetch user id of the campaign
            $userId = Model_Campaign::getUserIdforCampaign($campaignId);

            //checks if non-admin user is accessing other campaign_id
            if (($userId != $user->id) && !$isAdmin) {
                $this->_redirect('/');
            }
        } else {
            $campaignId = null;
            $userId = null;
        }
        
        $partnerArr = Model_Partner::getUserPartner($user->id);        
        $this->view->partnerLabel = $partnerArr['label'];
        $allAdvertiser = Tapit_AdvertiserStats::getAdOptimizerUsers( $partnerArr['partner_id'], $user->id );
        $this->view->advertisers = $allAdvertiser;   
        $this->view->adOptForm = $adOptForm;
        $this->view->isAdmin = $isAdmin;
        
        $adOptimizerPageInfo = array('user_id' => $userId, 'campaign_id' => $campaignId);
        $this->view->adOptimizerPageInfo = $adOptimizerPageInfo;

        $this->_helper->layout->setLayout('default/layout_optimizer');
    }
    
    //Ad Optimizer page - Get all campaigns of ad optimizer as per seleted advertiser
    public function getOptimizerCampaignsAction(){
        $request = $this->getRequest();
        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }        
        $getMethod  = $request->getParam('method');        
        $advertiser = '';
        $advertiser = $request->getParam('advertiser');                                        
        $isAdmin = App_User::isAdmin();        
        if( !$isAdmin ){
             $advertiser = App_User::getUserId();   
        }                      
       $params = array();
       $campaigns = array();
       $params = array('user_id' => $advertiser );                
       $list = new Model_Campaign_List(false);               
       $campaigns = $list->loadOptimizersCampaigns($params);                       
       $this->view->campaigns = $campaigns ;            
       $this->asJSON(true);                     
    } 
    
    //Ad Optimizer page - Get all platforms as per chosen campaings    
    public function getOptimizerPlatformsAction(){
        //we need more memory.
        //drop when we move to server side paginations
        ini_set('memory_limit','1G');
        set_time_limit(0);   
        
        
        $request = $this->getRequest();
        //make sure to validate AJAX request.
        if (!$request->isXmlHttpRequest()) {
            echo json_encode(array());
            exit(0);
        }  
        $advertiser = $request->getParam('advertiser'); 
        $campagins  = $request->getParam('campaign'); 
        $country    = $request->getParam('country'); 
        $period     = $request->getParam('period'); 
        $startDate  = $request->getParam('start_date'); 
        $endDate    = $request->getParam('end_date'); 
        $timeSpan   = array();
        if( $startDate && $endDate ){
            $timeSpan = array('start_date'=> $startDate ,'end_date'=> $endDate );
        }
            
        $isAdmin = App_User::isAdmin();        
        if( !$isAdmin ){
             $advertiser = App_User::getUserId();   
        }   
        $params = array();
        $params =  array('user_id' => $advertiser,'campaign_id'=>$campagins ,'period'=> $period,'time_span'=> $timeSpan );  
        $list = new Model_Campaign_List(false);                               
        $creativeStats = $list->loadOptimizersStats($params);          
        $this->view->stats =  $creativeStats;
        $this->asJSON(true);           
    }
    
    //Ad Optimizer page - Download records in csv format
    public function optimizerDownlaodDataAction(){
         $request = $this->getRequest();
         $csv = '';
         $headers = array();
         $fields  = array();
         $rows    = array();
         $downloadData      = $request->getParam('data');
         $downloadfileName  = $request->getParam('name');
         
         $data = json_decode( $downloadData );
         foreach ($data->headers as $header) {
            $headers[] = sprintf('"%s"', str_replace('"', '\\"', html_entity_decode(ucfirst($header))));
        };
        $rows[] = implode(",", $headers);
        foreach ($data->rows as $row) {
            $fields = array();
            reset($data->headers);
            foreach ($data->headers as $col) {
                $fields[] = sprintf('"%s"', str_replace('"', '\\"', $row->{$col}));
            }
            $rows[] = implode(',', $fields);
        }
        $content = implode("\n", array_merge($rows));
        header('Content-Type: application/vnd.ms-excel');
        header("Content-type: application/x-msexcel");
        header("Content-Disposition: attachment; filename=\"".preg_replace("/\s+/","-",$downloadfileName ).".csv\"");
        header("Pragma: no-cache");
        header("Content-Length: ".strlen($content));
        header("Expires: 0");
        echo $content;
        exit(0);              
    }
    
    public function allowScheduling() {
        $allowScheduling = false;
        if (App_User::isAdmin()) {
            $allowScheduling = true;
        }

        return $allowScheduling;
    }
   
    public function exportMediaPlannerAction() {
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        
        //collect the parameters
        $request = $this->getRequest();

        $defaultsOrdered = array('country_name' => 'Country', 'state_name' => 'State', 'dma_name' => 'DMA', 'platform_name' => 'Platform', 'carrier_name' => 'Carrier', 'channel_name' => 'Channel', 'site_name' => 'Site', 'requests' => 'Impressions');

        //collect the parameters
        $params = array();
        $criteria = $request->getParam('export_csv_criteria');
        parse_str($criteria, $params);

        $columns = $params['columns'];

        //get the grid pagination
        $sortMapping =
                array('country_name' => array('table' => 'countries', 'name' => 'name'),
                    'state_name' => array('table' => 'states', 'name' => 'name'),
                    'dma_name' => array('table' => 'dmas', 'name' => 'name'),
                    'platform_name' => array('table' => 'platforms', 'name' => 'name'),
                    'carrier_name' => array('table' => 'carriers', 'name' => 'name'),
                    'channel_name' => array('table' => 'channels', 'name' => 'name'),
                    'requests' => array('table' => null, 'name' => 'requests'),
                    'site_name' => array('table' => null, 'name' => 'site_name'));
        
        $gridLimit = Tapit_Stats::getGridPaging($sortMapping);
        
        //get the object order by
        $gridOrderBy = Tapit_Stats::getGridOrderBy($gridLimit['order']);
        $objectsOrderBy = $gridOrderBy['objects_order_by'];

        //get actual page data
        $reports = Tapit_AdvertiserStats::getAdPlannerReport($params, $objectsOrderBy);

        $reportCols = array();
        foreach($columns as $column) {
            if(array_key_exists($column, $defaultsOrdered)) {
                $reportCols[$column] = array('label' => $defaultsOrdered[$column]);
            }
        }

        $exportReport = array();
        foreach ($reports as $report) {
            $exportReport[] = $report;
        }
        
        Tapit_Utils::exportToCSV($reportCols, $exportReport, 'Media_Planner_Report');
    }
    
}
