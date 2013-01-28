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
	$person_total_1 = $person_total_2 = 0;
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
		$person_total_1 += $count;

		$str = "$user_nick ($begin_date - $end_date) = $count vs ";
		echo $str;

		$qry_stats = "SELECT SUM(tweets) AS tweets FROM statistics
			WHERE id_person = $id_person AND date = '$begin_date'";
		$arr_stats = $oMySQL->mysqlQuery($qry_stats);

		echo $arr_stats[0]["tweets"] . "<br><hr>";

		$person_total_2 += $arr_stats[0]["tweets"];

	}

	echo "<strong>$user_nick = $person_total_1 - $person_total_2</strong><hr><br>";
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