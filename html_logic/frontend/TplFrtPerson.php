<?php

class TplFrtPerson
{

    public function __construct()
    {
	
    }


    public function getLightboxMentions24($twitter_user)
    {
	$oTpl = new ThefTemplate('lightbox_tweets.html');
	$oTpl->assign('title', 'Menciones');
	$oTpl->assign('twitter', $twitter_user);
		
	$oPerson = new DbaFrtPerson();
	$arr_person = $oPerson->getPersonByTwitterUser($twitter_user);
	$arr_person_tmp = explode(' ', $arr_person['name_short']);
	$oTpl->assign('first_name', ThefText::optimize($arr_person_tmp[0]));
	$oTpl->assign('last_name', ThefText::optimize($arr_person_tmp[1]));
	
	$oStats = new DbaFrtStatistics();
	$arr_stats_24 = $oStats->getStatisticsByPeriod(1);
	$count = $arr_stats_24[$arr_person['id']]['mentions'];
	$oTpl->assign('count', $count); 
	
	$oTwitter = new DbaFrtTwitter();
	$arr_tweets = $oTwitter->getMentionsFromUser24($twitter_user);
	if (count($arr_tweets) > 0) {
	    foreach ($arr_tweets as $t) {
		$oTpl->newBlock('TWEET');
		$oTpl->assign('id_twitter', $t['id_twitter']);
		$oTpl->assign('twitter', $t['user_nick']);
		$oTpl->assign('date', ThefDate::getDateInSpanish($t['date'], true));
		$oTpl->assign('img_src', $t['user_img']);
		$oTpl->assign('text', ThefText::optimize($t['text']));
	    }
	}
	
	// TRACKING
	if (!DEBUG_ON) {
	    $oTpl->newBlock('DAX');
	    $oTpl->assign('DAX_tipo', 'menciones');
	    $oTpl->assign('DAX_person', strtolower($arr_person_tmp[1]));
	}
	
	return $oTpl->getHTML();
    }



    public function getLightboxTweets24($twitter_user)
    {
	$oTpl = new ThefTemplate('lightbox_tweets.html');
	$oTpl->assign('title', 'Tuits');
	$oTpl->assign('twitter', $twitter_user);
	
	$oPerson = new DbaFrtPerson();
	$arr_person = $oPerson->getPersonByTwitterUser($twitter_user);
	$arr_person_tmp = explode(' ', $arr_person['name_short']);
	$oTpl->assign('first_name', ThefText::optimize($arr_person_tmp[0]));
	$oTpl->assign('last_name', ThefText::optimize($arr_person_tmp[1]));
	
	$oStats = new DbaFrtStatistics();
	$arr_stats_24 = $oStats->getStatisticsByPeriod(1);
	$count = $arr_stats_24[$arr_person['id']]['tweets'];
	$oTpl->assign('count', $count); 
	
	
	$oTwitter = new DbaFrtTwitter();
	$arr_tweets = $oTwitter->getTweetsFromUser24($twitter_user);
	if (count($arr_tweets) > 0) {
	    foreach ($arr_tweets as $t) {
		$oTpl->newBlock('TWEET');
		$oTpl->assign('id_twitter', $t['id_twitter']);
		$oTpl->assign('twitter', $t['user_nick']);
		$oTpl->assign('date', ThefDate::getDateInSpanish($t['date'], true));
		$oTpl->assign('img_src', $t['user_img']);
		$oTpl->assign('text', ThefText::optimize($t['text']));
	    }
	}
	
	// TRACKING
	if (!DEBUG_ON) {
	    $oTpl->newBlock('DAX');
	    $oTpl->assign('DAX_tipo', 'tweets');
	    $oTpl->assign('DAX_person', strtolower($arr_person_tmp[1]));
	}
	
	return $oTpl->getHTML();
    }



}
