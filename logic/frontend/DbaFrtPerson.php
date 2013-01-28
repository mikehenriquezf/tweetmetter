<?php

class DbaFrtPerson
{

    private $db;
    private $memcache_queries;
    private $memcache_prefix_key = 'Person_';

    public function __construct()
    {
	$this->db = new ThefMysql('persons');
	$this->memcache_queries = array();
    }



    public function getPersons()
    {
	$qry = "SELECT * FROM persons WHERE active = 1 ORDER BY sort ASC";
	$qry_md5 = md5($qry);
	$this->memcache_queries[$qry_md5] = $qry;
	return $this->db->cachedQuery($qry_md5, $qry, ThefConfig::get('StatisticsTTL'));
    }



    public function getPositionByVotes($id_person)
    {
	$arr_persons = $this->getPersons();
	usort($arr_persons, 'DbaFrtPerson_cmpVotes');
	for ($i = 0; $i < count($arr_persons); $i++) {
	    if ($arr_persons[$i]['id'] == $id_person)
		return ($i + 1);
	}
	return null;
    }



    public function addVote($id_person)
    {
	$qry = "UPDATE persons SET votes = votes + 1 WHERE id = $id_person";
	$return = $this->db->mysqlNonQuery($qry);
	if ($return)
	    ThefCache::borrarArray($this->memcache_queries);
	return $return;
    }



    public function removeVote($id_person)
    {
	$qry = "UPDATE persons SET votes = votes - 1 WHERE id = $id_person";
	$return = $this->db->mysqlNonQuery($qry);
	if ($return)
	    ThefCache::borrarArray($this->memcache_queries);
	return $return;
    }



    public function getPerson($id)
    {
	$qry = "SELECT * FROM persons WHERE id = $id";
	$qry_md5 = md5($qry);
	$this->memcache_queries[$qry_md5] = $qry;
	$result = $this->db->cachedQuery($qry_md5, $qry, ThefConfig::get('StatisticsTTL'));
	if (count($result)) {
	    return $result[0];
	} else {
	    return null;
	}
    }



    public function getPersonByTwitterUser($str_user)
    {
	$qry = "SELECT * FROM persons WHERE twitter = '$str_user'";
	$qry_md5 = md5($qry);
	$this->memcache_queries[$qry_md5] = $qry;
	$result = $this->db->cachedQuery($qry_md5, $qry, ThefConfig::get('StatisticsTTL'));
	if (count($result)) {
	    return $result[0];
	} else {
	    return null;
	}
    }



    public function getPersonData($str_user)
    {
	$result = ThefCache::get($this->memcache_prefix_key . $str_user);
	if (!$result) {
	    $arr_person = ThefConfig::get('Personas');
	    for ($i = 0; $i < count($arr_person); $i++) {
		if ($arr_person[$i]['Persona']['Twitter'] == $str_user) {
		    $result = $arr_person[$i]['Persona'];
		    ThefCache::set($this->memcache_prefix_key . $str_user, $result, MEMCACHE_DEFAULT_EXPIRES);	    
		}
	    }	    
	}
	return $result;
    }



    public function updateFollowers($id_person, $followers)
    {
	// Update person table
	$this->db->mysqlUpdate(array('followers' => $followers), "id = $id_person");

	// Update daily followers
	$oStatistics = new DbaFrtStatistics();
	$oStatistics->updateFollowers($id_person, $followers);
    }



    public function addDiailyStatistics($id_person, $date, $options = array())
    {
	$oStatistics = new DbaFrtStatistics();
	$oStatistics->addStatistics($id_person, $date, $options);
    }



}

function DbaFrtPerson_cmpVotes($a, $b)
{
    return ($a['votes'] > $b['votes']) ? -1 : 1;
}