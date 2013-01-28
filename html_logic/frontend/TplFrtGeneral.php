<?php

class TplFrtGeneral
{

	public function __construct()
	{
		
	}



	public function getHomeHTML($twitter_user = null)
	{
		$oTpl = new ThefTemplate('home.html');

		$oPerson = new DbaFrtPerson();
		$arr_persons = $oPerson->getPersons();

		// Stats
		$oStats = new DbaFrtStatistics();
		$arr_stats = $oStats->getStats();
		$arr_stats24 = $arr_stats['stats24'];

		// Tweets
		$oTwitter = new DbaFrtTwitter();

		// Harcoded
		$arr_persons[0]['css'] = 'cotto';
		$arr_persons[1]['css'] = 'trout';

		for ($i = 0; $i < count($arr_persons); $i++) {
			$person = $arr_persons[$i];
			$oTpl->newBlock('PERSON');
			$oTpl->assign('css', $person['css']);
			$oTpl->assign('id_person', $person['id']);

			$oTpl->assign('name', ThefText::optimize($person['name']));
			$oTpl->assign('name_short', ThefText::optimize($person['name_short']));
			$arr_name_short = explode(' ', $person['name_short']);
			$oTpl->assign('first_name', ThefText::optimize($arr_name_short[0]));
			$oTpl->assign('last_name', ThefText::optimize($arr_name_short[1]));

			$oTpl->assign('twitter', $person['twitter']);
			$oTpl->assign('followers', $person['followers']);
			$oTpl->assign('tweets_24', $arr_stats24[$person['id']]['tweets']);
			$oTpl->assign('tweets_24_%', $arr_stats24[$person['id']]['tweets_%']);
			$oTpl->assign('retweets_24', $arr_stats24[$person['id']]['retweets']);
			$oTpl->assign('retweets_24_%', $arr_stats24[$person['id']]['retweets_%']);
			$oTpl->assign('mentions_24', $arr_stats24[$person['id']]['mentions']);
			$oTpl->assign('mentions_24_%', $arr_stats24[$person['id']]['mentions_%']);
			$oTpl->assign('followers_24', $arr_stats24[$person['id']]['followers']);
			$oTpl->assign('followers_24_%', $arr_stats24[$person['id']]['followers_%']);
			$oTpl->assign('url', WEB_PATH . 'candidatos/' . ThefText::urlOptimize($person['name']) . '_(' . $person['twitter'] . ')/');

			// Technical card
			$tech = ThefConfig::get('Personas');
			$tech = $tech[$i]['Persona'];
			$oTpl->assign('nacionalidad', ThefText::optimize($tech['Nacionalidad']));
			$oTpl->assign('edad', $tech['Edad']);
			$oTpl->assign('altura_mts', $tech['Altura_mts']);
			$oTpl->assign('altura_inch', $tech['Altura_inch']);
			$oTpl->assign('alcance_mts', $tech['Alcance_mts']);
			$oTpl->assign('alcance_inch', $tech['Alcance_inch']);
			$oTpl->assign('peleas_total', $tech['Peleas_Total']);
			$oTpl->assign('peleas_ganadas', $tech['Peleas_Ganadas']);
			$oTpl->assign('peleas_perdidas', $tech['Peleas_Perdidas']);
			$oTpl->assign('peleas_ko', $tech['Peleas_KO']);

			// Mentions
			$arr_tweets = $oTwitter->getMentionsFromUser24($person['twitter']);
			if (count($arr_tweets) > 0) {
				$oTpl->newBlock('SLIDER');
				$oTpl->assign('css', $person['css']);
				$oTpl->assign('twitter', $person['twitter']);
				foreach ($arr_tweets as $tweet) {
					$oTpl->newBlock('SLIDER_ITEM');
					$oTpl->assign('text', ThefText::optimize($tweet['text']));
				}
			}

			$oTpl->gotoBlock('_ROOT');
		}
		
		
		// Event day (will enable one option or another) DEBUG_ON = show both!
		$arr_event_day = explode(' ', ThefConfig::get('ContadorFecha'));
		list($day, $month, $year) = explode('/', $arr_event_day[0]);
		list($hour, $minute) = explode(':', $arr_event_day[1]);
		$mktime_event = mktime($hour, $minute, 0, $month, $day, $year);

		// Counter		
		$is_counter_active = DEBUG_ON || (mktime() < $mktime_event);
		if ($is_counter_active) {
			$oTpl->newBlock('COUNTDOWN');
			$oTpl->assign('counter_minute', $minute);
			$oTpl->assign('counter_hour', $hour);
			$oTpl->assign('counter_day', $day);
			$oTpl->assign('counter_month', $month);
			$oTpl->assign('counter_year', $year);			
			$oTpl->gotoBlock('_ROOT');
		}		
		
		// Round by round
		$is_round_active = DEBUG_ON || (mktime() >= $mktime_event);
		if (!$is_round_active) {
			$oTpl->assign('display_rounds', 'none');
		}

		return $oTpl->getHTML();
	}



	public function getKeywordsPage($word)
	{
		$oTpl = new ThefTemplate('twitter_list.html');
		$oTpl->assign('word', ThefText::optimize(strtoupper($word)));

		$oTwitter = new DbaFrtTwitter();
		$arr_tweets = $oTwitter->getTweetsUsingWord($word, 36);
		for ($i = 0; $i < count($arr_tweets); $i++) {
			$t = $arr_tweets[$i];
			if ($i % 9 == 0)
				$oTpl->newBlock('SLIDE');
			if ($i % 3 == 0)
				$oTpl->newBlock('SUBSLIDE');
			$oTpl->newBlock('TWEET');
			$oTpl->assign('id_twitter', $t['id_twitter']);
			$oTpl->assign('twitter', $t['user_nick']);
			$oTpl->assign('fecha', ThefDate::getFechaEsp($t['date'], true));
			$oTpl->assign('img_src', $t['user_img']);
			$oTpl->assign('text', ThefText::optimize($t['text']));
		}
		return $oTpl->getHTML();
	}



	public function getTweetsPage($hashtag)
	{
		// HARDCODED HASHTAG VALUE
		$hashtag = TWITTER_HASHTAG;

		$oTpl = new ThefTemplate('twitter_list.html');
		$oTpl->assign('word', '#' . $hashtag);

		$oTwitter = new DbaFrtTwitter();
		$arr_tweets = $oTwitter->getTweetsForHashtag($hashtag);
		for ($i = 0; $i < count($arr_tweets); $i++) {
			$t = $arr_tweets[$i];
			if ($i % 9 == 0)
				$oTpl->newBlock('SLIDE');
			if ($i % 3 == 0)
				$oTpl->newBlock('SUBSLIDE');
			$oTpl->newBlock('TWEET');
			$oTpl->assign('id_twitter', $t['id_twitter']);
			$oTpl->assign('twitter', $t['user_nick']);
			$oTpl->assign('fecha', ThefDate::getFechaEsp($t['date'], true));
			$oTpl->assign('img_src', $t['user_img']);
			$oTpl->assign('text', ThefText::optimize($t['text']));
		}
		return $oTpl->getHTML();
	}



}
