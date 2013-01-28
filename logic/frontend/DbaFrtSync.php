<?php

class DbaFrtSync
{

    const STATE_RUNNING = 0;
    const STATE_FINISHED_OK = 1;
    const STATE_FINISHED_ERROR = -1;

    private $db = null;
    private $memcache_queries;

    public function __construct()
    {
	$this->db = new ThefMysql('twitter_sync_history');
	$this->db->hasCreatedField = false;
	$this->db->hasModifiedField = false;
	$this->memcache_queries = array();
    }

    private function isSyncRunning()
    {
	$qry = "SELECT * FROM twitter_sync_history WHERE state = " . self::STATE_RUNNING;
	$result = $this->db->mysqlQuery($qry);
	return ($result[0]['id'] > 0);
    }

    public function syncAll()
    {
	if ($this->isSyncRunning()) {
	    return self::STATE_RUNNING;
	} else {

	    $insert_data = array(
		'date_ini' => Date('Y-m-d H:i:s'),
		'state' => self::STATE_RUNNING
	    );
	    $id_sync = $this->db->mysqlInsert($insert_data);

	    set_time_limit(0);

	    // TIME BEFORE
	    list($usec, $sec) = explode(' ', microtime());
	    $time_exec_ini = ((float) $usec + (float) $sec);

	    try {
		$oTwitter = new DbaFrtTwitter();
		$oTwitter->synchronizeTweets();
		$oTwitter->synchronizeFollowers();
		$content = '';
		$result = self::STATE_FINISHED_OK;
		ThefCache::flush();
	    } catch (Exception $e) {
		$content = print_r($e, true);
		$result = self::STATE_FINISHED_ERROR;
	    }

	    // TIME AFTER
	    list($usec, $sec) = explode(' ', microtime());
	    $time_exec_end = ((float) $usec + (float) $sec);

	    // EXECUTE TIME
	    $time_exec = $time_exec_end - $time_exec_ini;
	    $output = "execution time: $time_exec seconds <BR>\r\n output = $result <BR>\r\n $content";

	    $update_data = array(
		'date_end' => Date('Y-m-d H:i:s'),
		'state' => $result,
		'result' => $output
	    );
	    $this->db->mysqlUpdate($update_data, "id = $id_sync");

	    //ThefFile::log($output, "_log/sync/{Y}{m}{d}/{H}/{i}/$result.txt");

	    return $result;
	}
    }

}