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
		$user_nick = $person['twitter'];
		$id_person = $person['id'];
		$qry_count = "SELECT COUNT(T.id_twitter) AS total FROM twitter_tweets AS T
			LEFT JOIN twitter_related AS TR ON TR.id_twitter = T.id_twitter
			LEFT JOIN twitter_search AS TS ON TR.id_twitter_search = TS.id
			WHERE TS.search_string = '$user_nick' AND TS.search_type = ".DbaFrtTwitter::SEARCH_BY_SENDER."
			AND T.date BETWEEN '$begin_date' AND '$end_date'";
		$arr_result = $oMySQL->mysqlQuery($qry_count);
		$count = $arr_result[0]['total'];

		$str = "$user_nick ($begin_date - $end_date) = $count<BR>";
		echo $str;

		$qry_update = "UPDATE statistics SET tweets = $count, modified = NOW()
			WHERE id_person = $id_person AND date = '$begin_date' ";
		$res = $oMySQL->mysqlNonQuery($qry_update);

		if (!$res) {
			$qry_stats_insert = "INSERT INTO statistics (id_person, date, tweets, created, modified) VALUES
			($id_person, '$begin_date', $count, NOW(), NOW())";
			$oMySQL->mysqlNonQuery($qry_stats_insert);
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
		if ($search['search_string'] == $twitter_user && $search['search_type'] == DbaFrtTwitter::SEARCH_BY_SENDER) {
			return $search['id'];
		}
	}
}