<?php

class DbaFrtWordcloud
{

    private $db = null;
    private $memcache_queries;
    private $arr_stopwords;

    public function __construct()
    {
	$this->db = new ThefMysql('wordcloud');
	$this->memcache_queries = array();
	$this->arr_stopwords = $this->getStopWords();
    }



    public function getWords()
    {
	$qry = "SELECT * FROM wordcloud
			WHERE active = 1
			ORDER BY count DESC
			LIMIT 0,20";
	$qry_md5 = md5($qry);
	$this->memcache_queries[$qry_md5] = $qry;
	return $this->db->cachedQuery($qry_md5, $qry, MEMCACHE_DEFAULT_EXPIRES);
    }



    public function analyzeTweet($text, $id_twitter)
    {
	$this->db->skipInsertExcepetionLog = true;
	$arr_text = explode(" ", strtolower($text));

	// ADD "Puerto Rico" and "San Juan" as valid words
	if (substr_count($text, 'puerto rico'))
	    $arr_text [] = 'puerto rico';
	if (substr_count($text, 'san juan'))
	    $arr_text [] = 'san juan';

	foreach ($arr_text as $word) {
	    $word = $this->cleanWord($word);
	    if ($this->isValidWord($word)) {
		$qry = "INSERT INTO wordcloud (word, count, active, created) VALUES ('$word', 1, 1, NOW())";
		try {
		    $this->db->skipInsertExcepetionLog = true;
		    $this->db->mysqlNonQuery($qry);
		} catch (Exception $e) {
		    $qry = "UPDATE wordcloud SET count = count + 1 WHERE word = '$word'";
		    $this->db->mysqlNonQuery($qry);
		}
		$qry_related = "INSERT INTO tweets_words (word, id_twitter) VALUES ('$word', '$id_twitter')";
		$result = $this->db->mysqlNonQuery($qry_related);
	    }
	}
    }



    private function getStopWords()
    {
	$arr_words = ThefCache::get('stopwords');
	if (!$arr_words) {
	    $arr_words = explode(' ', file_get_contents(TXT_STOPWORDS));
	    ThefCache::set('stopwords', $arr_words, MEMCACHE_STOPWORDS);
	}
	return $arr_words;
    }



    private function cleanWord($word)
    {
	$word = str_replace('"', '', $word);
	$word = str_replace("'", '', $word);
	$word = str_replace('«', '', $word);
	$word = str_replace('»', '', $word);
	$word = str_replace('`', '', $word);
	$word = str_replace('´', '', $word);
	$word = str_replace('“', '', $word);
	$word = str_replace('”', '', $word);
	$word = str_replace('‘', '', $word);
	$word = str_replace('’', '', $word);
	$word = str_replace('.', '', $word);
	$word = str_replace('·', '', $word);
	$word = str_replace(',', '', $word);
	$word = str_replace(';', '', $word);
	$word = str_replace(':', '', $word);
	$word = str_replace('¿', '', $word);
	$word = str_replace('?', '', $word);
	$word = str_replace('¡', '', $word);
	$word = str_replace('!', '', $word);
	$word = str_replace('(', '', $word);
	$word = str_replace(')', '', $word);
	$word = str_replace('$', '', $word);
	$word = trim($word, "()¿?¡!.,:;'");
	return $word;
    }



    private function isValidWord($word)
    {
	if (strlen($word) <= 2) {
	    return false;
	} else
	if (substr($word, 0, 1) == '@') {
	    return false;
	} else
	if (substr($word, 0, 1) == '#') {
	    return false;
	} else
	if (substr($word, 0, 4) == 'http') {
	    return false;
	} else
	if (is_numeric($word)) {
	    return false;
	} else
	if (in_array($word, $this->arr_stopwords)) {
	    return false;
	} else
	if (in_array($word, array('puerto', 'rico', 'san', 'juan'))) {
	    return false;
	}
	return true;
    }



}