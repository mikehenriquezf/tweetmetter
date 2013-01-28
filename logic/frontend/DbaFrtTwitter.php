<?php

class DbaFrtTwitter
{

    const SEARCH_BY_HASHTAG = 1;
    const SEARCH_BY_MENTION = 2;
    const SEARCH_BY_SENDER = 3;
    const SEARCH_BY_RETWEETS = 4;
    const SEARCH_BY_RECEIVER = 5;
    const SEARCH_BY_STRING = 6;

    private $db = null;
    private $cache_queries;
    private $oTwitterAPI = null;
    private $current_search_id;
    private $current_search_type;
    private $current_id_person;

    public function __construct()
    {
	$this->db = new ThefMysql('twitter_tweets');
    }



    private function getTwitterAPI()
    {
	if (is_null($this->oTwitterAPI)) {
	    require_once(ROOT_PATH . 'include/lib/TwitterAsync/include.php');
	    $this->oTwitterAPI = new EpiTwitter(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, TWITTER_ACCESS_TOKEN, TWITTER_ACCESS_TOKEN_SECRET);
	}
	return $this->oTwitterAPI;
    }



    public function getTwitterSearches()
    {
	$qry = "SELECT * FROM twitter_search
			WHERE active = 1
			ORDER BY id ASC";
	$qry_md5 = md5($qry);
	$this->cache_queries[$qry_md5] = $qry;
	return $this->db->cachedQuery($qry_md5, $qry, MEMCACHE_DEFAULT_EXPIRES);
    }



    public function synchronizeTweets()
    {
	$current_date = Date('Y-m-d H:00:00');
	$oPerson = new DbaFrtPerson();
	$oWordcloud = new DbaFrtWordcloud;
	$searches = $this->getTwitterSearches();

	for ($i = 0; $i < count($searches); $i++) {

	    $this->current_search_id = $searches[$i]['id'];
	    $this->current_search_type = $searches[$i]['search_type'];

	    switch ($searches[$i]['search_type']) {

		case self::SEARCH_BY_HASHTAG:
		    $result = $this->twitterGetTweetsByHashtag($searches[$i]['search_string'], $searches[$i]['max_twitter_id']);
		    break;

		case self::SEARCH_BY_SENDER:
		    $person = $oPerson->getPersonByTwitterUser($searches[$i]['search_string']);
		    $this->current_id_person = $person['id'];
		    $result = $this->twitterGetTweetsFromUser($searches[$i]['search_string'], $searches[$i]['max_twitter_id']);

		    //ThefFile::log(print_r($result, true), '_log/sync/{Y}{m}{d}/{H}/{i}/' . $searches[$i]['search_string'] . '.txt');

		    // Update statistics
		    $options = array(
			'tweets' => $result['tweets_count'],
			'retweets' => $result['retweets_count']
		    );
		    $oPerson->addDiailyStatistics($this->current_id_person, $current_date, $options);
		    break;

		case self::SEARCH_BY_MENTION:
		    $person = $oPerson->getPersonByTwitterUser($searches[$i]['search_string']);
		    $this->current_id_person = $person['id'];
		    $result = $this->twitterGetMentionsFromUser($searches[$i]['search_string'], $searches[$i]['max_twitter_id']);

		    // Update statistics
		    $options = array(
			'mentions' => $result['tweets_count']
		    );
		    $oPerson->addDiailyStatistics($this->current_id_person, $current_date, $options);
		    break;

		case self::SEARCH_BY_RETWEETS:
		    $arr_retweets = $this->getRetweetsFromUser24($searches[$i]['search_string']);
		    if (count($arr_retweets)) {
			foreach ($arr_retweets as $t) {
			    $retweets_count = $this->twitterGetRetweetsForATweet($t['id_twitter']);
			    $this->updateTweetRetweets($t['id_twitter'], $retweets_count);
			}
		    }
		    break;
	    }

	    // Update max id_twitter search
	    if ($result['tweets_count'] > 0) {
		$this->db->mysqlNonQuery("UPDATE twitter_search SET max_twitter_id = '" . $result['max_twitter_id'] . "' WHERE id = " . $searches[$i]['id']);
	    }
	}
    }



    public function synchronizeFollowers()
    {
	$oPerson = new DbaFrtPerson();
	$arr_persons = $oPerson->getPersons();
	for ($i = 0; $i < count($arr_persons); $i++) {
	    $followers_count = $this->twitterGetFollowersCount($arr_persons[$i]['twitter']);
	    $oPerson->updateFollowers($arr_persons[$i]['id'], $followers_count);
	}
    }



    /**
     * Inserts new tweets in the database an associates with the search
     * @param array	$arr_tweets	Array with tweets
     * @return array	Information about new tweets, retweets, max_twitter_id
     */
    private function addTweets(array $arr_tweets)
    {
	$result = array(
	    'tweets_count' => 0,
	    'tweets_new' => 0,
	    'retweets_count' => 0,
	    'max_twitter_id' => 0,
	);

	if (count($arr_tweets)) {

	    $oKeywords = new DbaFrtKeywords();
	    $oWordcloud = new DbaFrtWordcloud();

	    $result['max_twitter_id'] = $arr_tweets[0]['id_str'];

	    for ($i = 0; $i < count($arr_tweets); $i++) {
		$t = $arr_tweets[$i];

		// Retweets
		$retweets = ($t['retweet_count'] > 0 && count($t['retweeted_status']) == 0) ?
			$t['retweet_count'] : 0;

		// Synchronize only after specific date
		$tweet_date = ThefDate::fromTwitterDate($t['created_at']);
		$date_invtal = date_diff(date_create(TWITTER_SYNCHRONIZE_FROM), date_create($tweet_date));
		$diff_days = $date_invtal->format('%R%d');

		if ($diff_days > 0) {

		    $fields = array(
			'id_twitter' => $t['id_str'],
			'date' => $tweet_date,
			'user_id' => $t['user']['id_str'],
			'user_nick' => $t['user']['name'],
			'user_img' => $t['user']['profile_image_url'],
			'user_receiver_id' => $t[''],
			'text' => $t['text'],
			'geolocation' => $t['geo'],
			'url' => $t[''],
			'retweets' => $retweets,
			'is_retweet' => '',
			'published' => (TWEETS_IMPORTED_PUBLISHED) ? '1' : ''
		    );

		    if (count($t['retweeted_status'])) {
			$fields['is_retweet'] = 1;
		    }

		    if (!count($t['user'])) {
			$fields['user_id'] = $t['from_user_id_str'];
			$fields['user_nick'] = $t['from_user'];
			$fields['user_img'] = $t['profile_image_url'];
		    }

		    $result['tweets_new']++;
		    try {
			$this->db->skipInsertExcepetionLog = true;
			$is_new = $this->db->mysqlInsert($fields);
		    } catch (Exception $e) {
			$result['tweets_new']--;
		    }

		    // INSERT RELATION
		    if ($this->current_search_id > 0) {
			$id_twitter = $t['id_str'];
			$id_twitter_search = $this->current_search_id;
			$qry = "INSERT INTO twitter_related
							(id_twitter, id_twitter_search) VALUES
							($id_twitter, $id_twitter_search)";
			try {
			    $this->db->mysqlNonQuery($qry);

			    // GET KEYWORDS FOR USER
			    if ($this->current_search_type == self::SEARCH_BY_SENDER) {
				$arr_keywords = $oKeywords->analyzeTweet($t['text'], $this->current_id_person);
			    }

			    // UPDATE WORDCLOUD
			    $oWordcloud->analyzeTweet($t['text'], $id_twitter);
			} catch (Exception $e) {
			    
			}
		    }

		    // RETWEETS
		    $result['tweets_count']++;
		    $result['retweets_count'] += $retweets;

		    // LOG TWEET
		    $log_tweet_str = print_r($t, true);
		    $log_tweet_str .= print_r($fields, true);
		    $log_tweet_str .= print_r($result, true);
		    //ThefFile::log($log_tweet_str, '_log/tweets/{Y}{m}{d}/{H}/' . $t['id_str'] . '.txt');
		}
	    }
	}
	return $result;
    }



    private function twitterGetFollowersIds($from_user, $next_cursor = -1)
    {
	$oTwitter = $this->getTwitterAPI();
	$result = $oTwitter->get_followersIds(array(
	    'screen_name' => $from_user,
	    'stringyfy_ids' => true
		));
	var_dump($result);
    }



    private function twitterGetFollowersCount($from_user)
    {
	//ThefFile::log("twitterGetFollowersCount ($from_user)", '_log/sync/{Y}{m}{d}/{H}/{i}/twitter_sync.txt');
	$oTwitter = $this->getTwitterAPI();
	$result = $oTwitter->get_usersLookup(array(
	    'screen_name' => $from_user,
	    'stringyfy_ids' => true
		));

	if ($result->code == '200') {
	    //ThefFile::log("result " . $result->response[0]['followers_count'], '_log/sync/{Y}{m}{d}/{H}/{i}/twitter_sync.txt');
	    return $result->response[0]['followers_count'];
	}
	return null;
    }



    public function twitterGetTweetsFromUser($from_user, $from_id = '', $max_id = '')
    {
	//ThefFile::log("twitterGetTweetsFromUser ($from_user - $from_id)", '_log/sync/{Y}{m}{d}/{H}/{i}/twitter_sync.txt');
	$oTwitter = $this->getTwitterAPI();
	$api_params = array(
	    'screen_name' => $from_user,
	    'stringyfy_ids' => true,
	    'count' => 200);

	if ($from_id != '' && $from_id != '0')
	    $api_params['since_id'] = $from_id;
	if ($max_id != '' && $max_id != '0')
	    $api_params['max_id'] = $max_id;

	$result = $oTwitter->get_statusesUser_timeline($api_params);
	if ($result->code == '200') {
	    $add_result = $this->addTweets($result->response);
	    //ThefFile::log("result " . print_r($add_result, true), '_log/sync/{Y}{m}{d}/{H}/{i}/twitter_sync.txt');
	}
	return $add_result;
    }



    public function twitterGetRetweetsForATweet($twitter_id)
    {
	//ThefFile::log("twitterGetRetweetsForATweet ($twitter_id)", '_log/sync/{Y}{m}{d}/{H}/{i}/twitter_sync.txt');
	$oTwitter = $this->getTwitterAPI();
	$result = $oTwitter->get("/statuses/retweets/$twitter_id.json");
	if ($result->code == '200') {
	    return $result->response[0]['retweeted_status']['retweet_count'];
	} else {
	    return null;
	}
    }



    public function twitterGetMentionsFromUser($from_user, $from_id = '', $max_id = '')
    {
	//ThefFile::log("twitterGetMentionsFromUser ($from_user - $from_id)", '_log/sync/{Y}{m}{d}/{H}/{i}/twitter_sync.txt');
	$oTwitter = $this->getTwitterAPI();
	$api_params = array(
	    'q' => '@' . $from_user,
	    'stringyfy_ids' => true,
	    'result_type' => 'recent',
	    'rpp' => 100,
	    'count' => 100
	);

	if ($from_id != '' && $from_id != '0')
	    $api_params['since_id'] = $from_id;
	if ($max_id != '' && $max_id != '0')
	    $api_params['max_id'] = $max_id;

	$result = $oTwitter->get('/search.json', $api_params);
	if ($result->code == '200') {
	    $add_result = $this->addTweets($result->response['results']);
	    //ThefFile::log("tweets_count " . $add_result['tweets_count'], '_log/sync/{Y}{m}{d}/{H}/{i}/twitter_sync.txt');
	}
	return $add_result;
    }



    public function twitterGetTweetsByHashtag($hash_tag, $from_id = '', $max_id = '')
    {
	//ThefFile::log("twitterGetTweetsByHashtag ($hash_tag - $from_id)", '_log/sync/{Y}{m}{d}/{H}/{i}/twitter_sync.txt');
	$oTwitter = $this->getTwitterAPI();
	$api_params = array(
	    'q' => '#' . $hash_tag,
	    'stringyfy_ids' => true,
	    'result_type' => 'recent',
	    'rpp' => 100,
	    'count' => 100
	);

	if ($from_id != '' && $from_id != '0')
	    $api_params['since_id'] = $from_id;
	if ($max_id != '' && $max_id != '0')
	    $api_params['max_id'] = $max_id;

	try {
	    $result = $oTwitter->get('/search.json', $api_params);
	} catch (Exception $e) {
	    ThefFile::log(print_r($e, true), '_log/twitter_exception.txt');
	    echo "<table bgcolor='#f0f0f0' width='100%'><tr><td><PRE>" . print_r($e, true) . "</PRE></td></tr></table>" . "\n";
	}
	if ($result->code == '200') {
	    $add_result = $this->addTweets($result->response['results']);
	    //ThefFile::log("tweets_found " . $add_result['tweets_count'], '_log/sync/{Y}{m}{d}/{H}/{i}/twitter_sync.txt');
	}
	return $add_result;
    }



    public function updateTweetRetweets($id_twitter, $count)
    {
	$qry = "UPDATE twitter_tweets SET retweets = $count WHERE id_twitter = '$id_twitter'";
	$this->db->mysqlNonQuery($qry);
    }



    public function getTweetsFromUser24($twitter_user, $limit = 100)
    {
	$begin_date = Date("Y-m-d H:00:00", strtotime("-1 day"));
	$end_date = Date("Y-m-d H:i:s");

	$qry = "SELECT DISTINCT(T.id_twitter), T.* FROM twitter_tweets AS T
			LEFT JOIN twitter_related AS TR ON TR.id_twitter = T.id_twitter
			LEFT JOIN twitter_search AS TS ON TR.id_twitter_search = TS.id
			WHERE TS.search_type = " . self::SEARCH_BY_SENDER . " AND TS.search_string = '$twitter_user'
			AND T.date BETWEEN '$begin_date' AND '$end_date'
			ORDER BY T.date DESC
			LIMIT 0, $limit";
	$qry_md5 = md5($qry);
	$this->cache_queries[$qry_md5] = $qry;
	return $this->db->cachedQuery($qry_md5, $qry, ThefConfig::get('TwitterApiRefresh'));
    }



    public function getMentionsFromUser24($twitter_user, $limit = 100)
    {
	$begin_date = Date("Y-m-d H:00:00", strtotime("-1 day"));
	$end_date = Date("Y-m-d H:i:s");

	$qry = "SELECT T.* FROM twitter_tweets AS T
			LEFT JOIN twitter_related AS TR ON TR.id_twitter = T.id_twitter
			LEFT JOIN twitter_search AS TS ON TR.id_twitter_search = TS.id
			WHERE TS.search_type = " . self::SEARCH_BY_MENTION . " AND TS.search_string = '$twitter_user'
			AND T.date BETWEEN '$begin_date' AND '$end_date'
			ORDER BY T.date DESC
			LIMIT 0, $limit";

	$qry_md5 = md5($qry);
	$this->cache_queries[$qry_md5] = $qry;
	return $this->db->cachedQuery($qry_md5, $qry, ThefConfig::get('TwitterApiRefresh'));
    }



    public function getRetweetsFromUser24($twitter_user, $limit = 100)
    {
	$begin_date = Date("Y-m-d H:00:00", strtotime("-1 day"));
	$end_date = Date("Y-m-d H:i:s");


	$qry = "SELECT T.* FROM twitter_tweets AS T
			LEFT JOIN twitter_related AS TR ON TR.id_twitter = T.id_twitter
			LEFT JOIN twitter_search AS TS ON TR.id_twitter_search = TS.id
			WHERE TS.search_type = " . self::SEARCH_BY_SENDER . " AND TS.search_string = '$twitter_user'
			AND T.retweets > 0 AND T.date BETWEEN '$begin_date' AND '$end_date'
			ORDER BY T.retweets DESC, T.date DESC
			LIMIT 0, $limit";

	$qry_md5 = md5($qry);
	$this->cache_queries[$qry_md5] = $qry;
	return $this->db->cachedQuery($qry_md5, $qry, ThefConfig::get('TwitterApiRefresh'));
    }



    public function getTweetsUsingWord($word, $limit = 100)
    {
	$qry = "SELECT T.* FROM twitter_tweets AS T
			LEFT JOIN tweets_words AS TW ON TW.id_twitter = T.id_twitter
			WHERE TW.word = '$word' AND T.published = 1
			ORDER BY T.date DESC
			LIMIT 0, $limit";
	$qry_md5 = md5($qry);
	$this->cache_queries[$qry_md5] = $qry;
	return $this->db->cachedQuery($qry_md5, $qry, ThefConfig::get('TwitterApiRefresh'));
    }



    public function getTweetsForHashtag($hashtag, $limit = 100)
    {
	$qry = "SELECT T.* FROM twitter_tweets AS T
			LEFT JOIN twitter_related AS TR ON TR.id_twitter = T.id_twitter
			LEFT JOIN twitter_search AS TS ON TS.id = TR.id_twitter_search
			WHERE TS.search_type = '" . self::SEARCH_BY_HASHTAG . "'  AND TS.search_string = '$hashtag'
			AND T.published = 1
			ORDER BY T.date DESC
			LIMIT 0, $limit";
	$qry_md5 = md5($qry);
	$this->cache_queries[$qry_md5] = $qry;
	return $this->db->cachedQuery($qry_md5, $qry, ThefConfig::get('TwitterApiRefresh'));
    }



    /* ----------------------- TWITTER SEARCH ----------------------- */

    public function saveSearchForUser($twitter_user, $active)
    {
	$db_search = new ThefMysql('twitter_search');

	$fields = array(
	    'search_string' => $twitter_user,
	    'active' => ($active) ? '1' : ''
	);

	// MENTIONS
	$fields['search_type'] = self::SEARCH_BY_MENTION;
	$updated = $db_search->mysqlUpdate($fields, "search_string = '$twitter_user' AND search_type = " . self::SEARCH_BY_MENTION);
	if (!$updated) {
	    $db_search->mysqlInsert($fields);
	}

	// SENDER
	$fields['search_type'] = self::SEARCH_BY_SENDER;
	$updated = $db_search->mysqlUpdate($fields, "search_string = '$twitter_user' AND search_type = " . self::SEARCH_BY_SENDER);
	if (!$updated) {
	    $db_search->mysqlInsert($fields);
	}

	// RETWEETS
//		$fields['search_type'] = self::SEARCH_BY_RETWEETS;
//		$updated = $db_search->mysqlUpdate($fields, "search_string = '$twitter_user' AND search_type = " . self::SEARCH_BY_RETWEETS);
//		if (!$updated) {
//			$db_search->mysqlInsert($fields);
//		}

	ThefCache::deleteArray($this->cache_queries);
    }



}
