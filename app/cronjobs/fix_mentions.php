<?php

require('../../include/include.php');

set_time_limit(0);

// TIME BEFORE
list($usec, $sec) = explode(' ', microtime());
$time_exec_ini = ((float) $usec + (float) $sec);

$oPerson = new DbaFrtPerson();
$arr_persons = $oPerson->getPersons();

$oMySQL = new ThefMysql();
$oMySQL->skipInsertExcepetionLog = true;

foreach ($arr_persons as $person) {
	for ($i = 0; $i <= 24; $i++) {
		$diff = ($i * 3600) + 1;
		$begin_date = Date('Y-m-d H:00:00', time() - $diff);
		$end_date = Date('Y-m-d H:i:s', strtotime($begin_date) + 3599);

		// Calculate mentions
		$mention = '@'.$person['twitter'];
		$id_person = $person['id'];
		$qry_count = "SELECT COUNT(id_twitter) AS total FROM twitter_tweets
			WHERE text LIKE '%$mention%'
			AND date BETWEEN '$begin_date' AND '$end_date'";
		$arr_result = $oMySQL->mysqlQuery($qry_count);
		$count = $arr_result[0]['total'];

		$str = "$mention ($begin_date - $end_date) = $count<BR>";
		echo $str;

		$qry_update = "UPDATE statistics SET mentions = $count, modified = NOW()
			WHERE id_person = $id_person AND date = '$begin_date' ";
		$res = $oMySQL->mysqlNonQuery($qry_update);

		if (!$res) {
			$qry_stats_insert = "INSERT INTO statistics (id_person, date, mentions, created, modified) VALUES
			($id_person, '$begin_date', $count, NOW(), NOW())";
			$oMySQL->mysqlNonQuery($qry_stats_insert);
		}


		// Update relationship
		$id_search = getSearchMentionId($person['twitter']);
		$qry_select = "SELECT id_twitter FROM twitter_tweets
			WHERE text LIKE '%$mention%'
			AND date BETWEEN '$begin_date' AND '$end_date'";
		$arr_tweets = $oMySQL->mysqlQuery($qry_select);
		if (count($arr_tweets)) {
			foreach ($arr_tweets as $t) {
                $id_twitter = $t['id_twitter'];
				$qry_insert = "INSERT INTO twitter_related (id_twitter_search, id_twitter) VALUES ($id_search, '$id_twitter')";
				try {
					$oMySQL->mysqlNonQuery($qry_insert);
				} catch (Exception $e) {}
			}
		}
		echo "<hr><br>";
	}
}

// TIME AFTER
list($usec, $sec) = explode(' ', microtime());
$time_exec_end = ((float) $usec + (float) $sec);

// EXECUTE TIME
$time_exec = $time_exec_end - $time_exec_ini;
echo  "<strong>execution time: $time_exec seconds </strong><BR>";



function getSearchMentionId($twitter_user)
{
	$oTwitter = new DbaFrtTwitter();
	$arr_searches = $oTwitter->getTwitterSearches();
	foreach ($arr_searches as $search) {
		if ($search['search_string'] == $twitter_user && $search['search_type'] == DbaFrtTwitter::SEARCH_BY_MENTION) {
			return $search['id'];
		}
	}
}