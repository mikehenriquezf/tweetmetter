<?php

class DbaFrtStatistics
{

    const TIME_DAYS = 'd';
    const TIME_HOURS = 'h';
    const DEFAULT_PERCENT = '100';
    const CACHE_STATS_KEY = 'CurrentStats';

    private $db = null;
    private $cache_queries;

    public function __construct()
    {
	$this->db = new ThefMysql('statistics');
    }



    private function verifyExistingEntry($id_person, $date)
    {
	$result = $this->getStatistics($id_person, $date);
	if (is_null($result)) {
	    $fields = array(
		'id_person' => $id_person,
		'date' => $date
	    );
	    $this->db->mysqlInsert($fields);
	}
    }



    public function getStatistics($id_person, $date)
    {
	$qry = "SELECT * FROM statistics
			WHERE id_person = $id_person AND date = '$date'";
	$qry_md5 = md5($qry);
	$this->cache_queries[$qry_md5] = $qry;
	$result = $this->db->cachedQuery($qry_md5, $qry, MEMCACHE_DEFAULT_EXPIRES);
	if (count($result[0])) {
	    return $result[0];
	} else {
	    ThefCache::delete($qry_md5);
	    return null;
	}
    }



    public function getStatisticsByPeriod($days = 1)
    {
	$cache_key = "statistics-$days";

	// Try getting stats from memcache first
	$return = ThefCache::get($cache_key);
	//$ruturn = false;

	if (!$return) {
	    $return = array();

	    $tw_update_min = floor(ThefConfig::get('TwitterApiRefresh') / 60);

	    $begin_date = Date("Y-m-d H:00:00", strtotime("-$days day"));
	    $end_date = Date("Y-m-d H:i:s");


	    $qry = "SELECT S.id_person, SUM(S.tweets) AS tweets, SUM(S.retweets) AS retweets,
				SUM(S.followers) AS followers, SUM(S.mentions) AS mentions
				FROM statistics AS S
				WHERE S.date BETWEEN '$begin_date' AND '$end_date'
				GROUP BY S.id_person
				ORDER BY S.id_person ASC";

	    $qry_md5 = md5($qry);
	    $this->cache_queries[$qry_md5] = $qry;
	    $result = $this->db->cachedQuery($qry_md5, $qry, 0);

	    // Calculation about max and totals using id person as index
	    $arr_max_totals = array(
		'max_tweets' => 0,
		'max_retweets' => 0,
		'max_followers' => 0,
		'max_mentions' => 0,
		'total_tweets' => 0,
		'total_retweets' => 0,
		'total_followers' => 0,
		'total_mentions' => 0
	    );
	    foreach ($result as $key => $person) {
		$arr_max_totals['max_tweets'] = max($arr_max_totals['max_tweets'], $person['tweets']);
		$arr_max_totals['max_retweets'] = max($arr_max_totals['max_retweets'], $person['retweets']);
		$arr_max_totals['max_followers'] = max($arr_max_totals['max_followers'], $person['followers']);
		$arr_max_totals['max_mentions'] = max($arr_max_totals['max_mentions'], $person['mentions']);
		$arr_max_totals['total_tweets'] += $person['tweets'];
		$arr_max_totals['total_retweets'] += $person['retweets'];
		$arr_max_totals['total_followers'] += $person['followers'];
		$arr_max_totals['total_mentions'] += $person['mentions'];
	    }

	    // Calculate percents
	    foreach ($result as $key => $person) {

		$return[$person['id_person']]['tweets'] = $person['tweets'];
		if ($arr_max_totals['max_tweets'] > 0) {
		    $return[$person['id_person']]['tweets_%'] = round(($person['tweets'] * 100) / $arr_max_totals['max_tweets']);
		    $return[$person['id_person']]['tweets_%%'] = round(($person['tweets'] * 100) / $arr_max_totals['total_tweets']);
		} else {
		    $return[$person['id_person']]['tweets_%'] = self::DEFAULT_PERCENT;
		    $return[$person['id_person']]['tweets_%%'] = self::DEFAULT_PERCENT;
		}

		$return[$person['id_person']]['retweets'] = $person['retweets'];
		if ($arr_max_totals['max_retweets'] > 0) {
		    $return[$person['id_person']]['retweets_%'] = round(($person['retweets'] * 100) / $arr_max_totals['max_retweets']);
		    $return[$person['id_person']]['retweets_%%'] = round(($person['retweets'] * 100) / $arr_max_totals['total_retweets']);
		} else {
		    $return[$person['id_person']]['retweets_%'] = self::DEFAULT_PERCENT;
		    $return[$person['id_person']]['retweets_%%'] = self::DEFAULT_PERCENT;
		}

		$return[$person['id_person']]['followers'] = $person['followers'];
		if ($arr_max_totals['max_followers'] > 0) {
		    $return[$person['id_person']]['followers_%'] = round(($person['followers'] * 100) / $arr_max_totals['max_followers']);
		    $return[$person['id_person']]['followers_%%'] = round(($person['followers'] * 100) / $arr_max_totals['total_followers']);
		} else {
		    $return[$person['id_person']]['followers_%'] = self::DEFAULT_PERCENT;
		    $return[$person['id_person']]['followers_%%'] = self::DEFAULT_PERCENT;
		}

		$return[$person['id_person']]['mentions'] = $person['mentions'];
		if ($arr_max_totals['max_mentions'] > 0) {
		    $return[$person['id_person']]['mentions_%'] = round(($person['mentions'] * 100) / $arr_max_totals['max_mentions']);
		    $return[$person['id_person']]['mentions_%%'] = round(($person['mentions'] * 100) / $arr_max_totals['total_mentions']);
		} else {
		    $return[$person['id_person']]['mentions_%'] = self::DEFAULT_PERCENT;
		    $return[$person['id_person']]['mentions_%%'] = self::DEFAULT_PERCENT;
		}
		
		// Change number format
		$return[$person['id_person']]['tweets'] = number_format($return[$person['id_person']]['tweets'], 0, '.', ',');
		$return[$person['id_person']]['retweets'] = number_format($return[$person['id_person']]['retweets'], 0, '.', ',');
		$return[$person['id_person']]['followers'] = number_format($return[$person['id_person']]['followers'], 0, '.', ',');
		$return[$person['id_person']]['mentions'] = number_format($return[$person['id_person']]['mentions'], 0, '.', ',');
	    }

	    unset($result);
	    unset($arr_max_totals);

	    $this->cache_queries[$cache_key] = $qry;
	    ThefCache::set($cache_key, $return, ThefConfig::get('TwitterApiRefresh'));
	}

	return $return;
    }



    public function addStatistics($id_person, $date, $options = array())
    {
	$tweets = ($options['tweets'] > 0) ? $options['tweets'] : 0;
	$retweets = ($options['retweets'] > 0) ? $options['retweets'] : 0;
	$followers = ($options['followers'] > 0) ? $options['followers'] : 0;
	$mentions = ($options['mentions'] > 0) ? $options['mentions'] : 0;

	$qry = "UPDATE statistics SET
			tweets = tweets + $tweets,
			retweets = retweets + $retweets,
			followers = followers + $followers,
			mentions = mentions + $mentions,
			modified = NOW()
			WHERE id_person = $id_person AND date = '$date'";

	$this->verifyExistingEntry($id_person, $date);
	$result = $this->db->mysqlNonQuery($qry);
	ThefCache::deleteArray($this->cache_queries);
	return $result;
    }



    public function updateFollowers($id_person, $new_followers_count)
    {
	$curr_date = Date('Y-m-d H:00:00');

	// Calculate the followers from the previous days
	$qry = "SELECT SUM(followers) AS followers FROM statistics
			WHERE id_person = $id_person AND date < '$curr_date'";
	$result = $this->db->mysqlQuery($qry);
	$current_followers = ($result[0]['followers']) ? $result[0]['followers'] : 0;

	$fields = array(
	    'followers' => ($new_followers_count - $current_followers)
	);
	$this->verifyExistingEntry($id_person, $curr_date);
	$result = $this->db->mysqlUpdate($fields, "id_person = $id_person AND date = '$curr_date'");
	ThefCache::deleteArray($this->cache_queries);
	return $result;
    }



    public function getStats()
    {
	$arr_stats = ThefCache::get(self::CACHE_STATS_KEY);
	//$arr_stats = false;

	if (!$arr_stats) {
	    $arr_stats = array();

	    $oPerson = new DbaFrtPerson();
	    $arr_persons = $oPerson->getPersons();

	    // CALCULATE MAX FOLLOWERS %
	    $max_followers = 0;
	    $total_followers = 0;
	    foreach ($arr_persons as $person) {
		$max_followers = max($max_followers, $person['followers']);
		$total_followers += $person['followers'];
	    }
	    foreach ($arr_persons as $index => $person) {
		$arr_persons[$index]['followers_%'] = round(($person['followers'] * 100) / $max_followers);
		$arr_persons[$index]['followers_%%'] = round(($person['followers'] * 100) / $total_followers);
		
		// Change number format
		$arr_persons[$index]['followers'] = number_format($arr_persons[$index]['followers'], 0, '.', ',');

		// Remove unnecesary fields
		unset($arr_persons[$index]['name']);
		unset($arr_persons[$index]['name_short']);
		unset($arr_persons[$index]['image']);
		unset($arr_persons[$index]['twitter']);
		unset($arr_persons[$index]['active']);
		unset($arr_persons[$index]['created']);
		unset($arr_persons[$index]['modified']);
	    }

	    $arr_stats['persons'] = $arr_persons;
	    $arr_stats['stats7'] = $this->getStatisticsByPeriod(7);
	    $arr_stats['stats24'] = $this->getStatisticsByPeriod(1);
	    $arr_stats['modified'] = Date('Y-m-d H:i:s');
	    $arr_stats['expires'] = Date('Y-m-d H:i:s', time() + ThefConfig::get('StatisticsTTL'));
	    $arr_stats['interval'] = ThefConfig::get('AjaxTTL');

	    $this->cache_queries[self::CACHE_STATS_KEY] = true;
	    ThefCache::set(self::CACHE_STATS_KEY, $arr_stats, ThefConfig::get('StatisticsTTL'));
	}

	return $arr_stats;
    }



}