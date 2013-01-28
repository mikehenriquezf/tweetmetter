<?php

class DbaFrtKeywords
{

	private $db = null;
	private $memcache_queries;

	public function __construct()
	{
		$this->db = new ThefMysql('persons_keywords');
		$this->memcache_queries = array();
	}


	public function getKeywords()
	{
		$qry = "SELECT DISTINCT(word) AS word FROM persons_keywords ORDER BY word ASC";
		$qry_md5 = md5($qry);
		$this->memcache_queries[$qry_md5] = $qry;
		return $this->db->cachedQuery($qry_md5, $qry, MEMCACHE_DEFAULT_EXPIRES);
	}


	public function synchronizeXML()
	{
		// Read XML
		$oXML = new SimpleXMLElement(file_get_contents(XML_KEYWORDS));

		$palabras_count = count($oXML->Palabra);
		if ($palabras_count > 0) {

			$oPersons = new DbaFrtPerson();
			$arr_persons = $oPersons->getPersons();

			for ($i = 0; $i < $palabras_count; $i++) {
				$word = (String)$oXML->Palabra[$i];
				for ($j = 0; $j < count($arr_persons); $j++) {
					$fields = array(
						'id_person'	=> $arr_persons[$j]['id'],
						'word'		=> $word
					);
					try {
						$this->db->skipInsertExcepetionLog = true;
						$this->db->mysqlInsert($fields);
					} catch (Exception $e) {}
				}
			}

			ThefMemcache::borrarArray($this->memcache_queries);
		}
	}


	public function analyzeTweet($text, $id_person)
	{
		$text = strtolower($text);
		$arr_keywords = $this->getKeywords();
		for ($i = 0; $i < count($arr_keywords); $i++) {
			$arr_keywords[$i]['count'] = 0;
			$word = $arr_keywords[$i]['word'];
			$result = substr_count($text, strtolower($arr_keywords[$i]['word']));
			if ($result > 0) {
				$qry = "UPDATE persons_keywords SET count = count + 1 WHERE word = '$word' AND id_person = $id_person";
				$this->db->mysqlNonQuery($qry);
				$arr_keywords[$i]['count']++;
			}
		}
		return $arr_keywords;
	}


	public function getTopKeywordsForUser($id_person, $limit = 3)
	{
		$qry = "SELECT * FROM persons_keywords
			WHERE id_person = $id_person AND count > 0
			ORDER BY count DESC
			LIMIT 0, $limit";
		$qry_md5 = md5($qry);
		$this->memcache_queries[$qry_md5] = $qry;
		return $this->db->cachedQuery($qry_md5, $qry, MEMCACHE_DEFAULT_EXPIRES);
	}


}