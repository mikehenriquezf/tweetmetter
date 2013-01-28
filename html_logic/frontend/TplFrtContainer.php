<?php

class TplFrtContainer extends ThefTemplate
{

    public function __construct()
    {
	parent::__construct('container.html');

	$this->assign('TWITTER_CONSUMER_KEY', TWITTER_CONSUMER_KEY);
	$this->assign('FACEBOOK_APP_ID', FACEBOOK_APP_ID);
	
	if (!DEBUG_ON) {
	    $this->newBlock('DAX');
	    if (GOOGLE_ANALYTICS_ID) {
		$this->newBlock('GOOGLE_ANALYTICS');
		$this->assign('GOOGLE_ANALYTICS_ID', GOOGLE_ANALYTICS_ID);
	    }
	}
	
	$this->gotoBlock('_ROOT');
    }



    public function setMainContent($html)
    {
	$this->gotoBlock('_ROOT');
	$this->assign('MAIN_CONTENT', $html);
    }


}
