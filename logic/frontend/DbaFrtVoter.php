<?php

class DbaFrtVoter
{

	const SEARCH_BY_HASHTAG = 1;
	const SEARCH_BY_MENTION = 2;
	const SEARCH_BY_SENDER = 3;
	const SEARCH_BY_RETWEETS = 4;
	const SEARCH_BY_RECEIVER = 5;
	const SEARCH_BY_STRING = 6;

	private $db = null;
	private $memcache_queries;

	public function __construct()
	{
		$this->db = new ThefMysql('voters');
	}


	private function getFacebookIdBySignature($signature)
	{
		list($encoded_sig, $payload) = explode('.', $signature, 2);

		// decode the data
		$sig = base64_decode(strtr($encoded_sig, '-_', '+/'));
		$data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

		if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
			$error_str = "Unknown algorithm. Expected HMAC-SHA256: <BR>\r\n".print_r($_GET, true)."<BR>\r\n ".print_r($_SERVER,true)."<BR>\r\n".print_r($data, true);
			ThefFile::log($error_str, '_log/facebook/{Y}{m}{d}/' . time() . '.txt');
			header('HTTP/1.1 400: BAD REQUEST');
			die();
		}

		// Adding the verification of the signed_request below
		$expected_sig = hash_hmac('sha256', $payload, FACEBOOK_SECRET, $raw = true);
		if ($sig !== $expected_sig) {
			$error_str = "Bad Signed JSON signature: <BR>\r\n".print_r($_GET, true)."<BR>\r\n ".print_r($_SERVER,true)."<BR>\r\n".print_r($data, true);
			ThefFile::log($error_str, '_log/facebook/{Y}{m}{d}/' . time() . '.txt');
			header('HTTP/1.1 400: BAD REQUEST');
			die();
		} else {
			return $data['user_id'];
		}
	}



	private function createToken()
	{
		return md5(rand(0,10000).'_'.time().'_PR');
	}



	public function voteInicialize($signature, $user_data)
	{
		$new_token = $this->createToken();
		$IP = $_SERVER['REMOTE_ADDR'];
		$fbk_id = $user_data['id'];
		$return_data = array(
			'fbk_id'	=> $fbk_id,
			'new_token'	=> $new_token
		);

		ThefFile::log(print_r($user_data, true));

		if (is_numeric($fbk_id)) {

			// Validate user id versus signature
			$signature_id = $this->getFacebookIdBySignature($signature);
			if ($signature_id == $fbk_id) {

				$updated = $this->db->mysqlUpdate(array('token'	=> $new_token, 'IP' => $IP), "fbk_id = '$fbk_id'");
				if (!$updated) {
					$fields = array(
						'fbk_id'	=> $fbk_id,
						'first_name'	=> filter_var($user_data['first_name'], FILTER_SANITIZE_STRING),
						'last_name'		=> filter_var($user_data['last_name'], FILTER_SANITIZE_STRING),
						'name'			=> filter_var($user_data['name'], FILTER_SANITIZE_STRING),
						'username'		=> filter_var($user_data['username'], FILTER_SANITIZE_STRING),
						'email'			=> filter_var($user_data['email'], FILTER_SANITIZE_EMAIL),
						'birthday'		=> filter_var($user_data['birthday'], FILTER_SANITIZE_STRING),
						'gender'		=> filter_var($user_data['gender'], FILTER_SANITIZE_STRING),
						'link'			=> filter_var($user_data['link'], FILTER_SANITIZE_STRING),
						'locale'		=> filter_var($user_data['locale'], FILTER_SANITIZE_STRING),
						'timezone'		=> filter_var($user_data['timezone'], FILTER_SANITIZE_STRING),
						'revote_count'	=> 0,
						'token'			=> $new_token,
						'IP'			=> $IP
					);
					$success = $this->db->mysqlInsert($fields);
					if ($success) {
						$return_data['id_person'] = 0;
						$return_data['success'] = true;
					}
				} else {
					$qry = "SELECT id_person FROM voters WHERE fbk_id = '$fbk_id'";
					$arr_query = $this->db->mysqlQuery($qry);
					$oPerson = new DbaFrtPerson();
					$arr_person = $oPerson->getPerson($arr_query[0]['id_person']);
					$return_data['id_person'] = $arr_query[0]['id_person'];
					$return_data['name'] = $arr_person['name'];
					$return_data['success'] = true;
				}
			}
		}

		if ($return_data['success']) {
			return $return_data;
		} else {
			header('HTTP/1.1 400: BAD REQUEST');
			return null;
		}
	}


	public function vote($fbk_id, $token, $id_person, $vote)
	{
		$return_data = array(
			'fbk_id'	=> $fbk_id,
			'new_token'	=> $this->createToken(),
		);
		$qry = "SELECT * FROM voters WHERE fbk_id = '$fbk_id'";
		$arr_voter = $this->db->mysqlQuery($qry);
		$arr_voter = $arr_voter[0];
		if (count($arr_voter)) {
			if ($arr_voter['token'] == $token) {
				$oPerson = new DbaFrtPerson();
				if ($vote) {
					$success = true;
					// If there was a vote, remove it
					if ($arr_voter['id_person'] > 0) {
						$success = $oPerson->removeVote($arr_voter['id_person']);
					}
					// Insert new vote
					if ($success) {
						$success = $oPerson->addVote($id_person);
					}
				} else {
					// Remove existing vote
					// NOT ALLOWED
					$success = false;
					/*
					if ($arr_voter['id_person'] == $id_person) {
						$success = $oPerson->removeVote($id_person);
						if ($success) {
							$id_person = '0';
						}
					}
					*/

				}
				if ($success) {
					$fields = array(
						'id_person'	=> $id_person,
						'token'		=> $return_data['new_token'],
						'IP'		=> $_SERVER['REMOTE_ADDR']
					);
					$this->db->mysqlUpdate($fields, "fbk_id = '$fbk_id'");
					$return_data['id_person'] = $id_person;
					$return_data['success'] = true;
				} else {
					$return_data['error'] = 'Error';
				}
			}
		}

		if ($return_data['success']) {
			return $return_data;
		} else
		if ($return_data['error']) {
			return $return_data;
		} else {
			header('HTTP/1.1 400: BAD REQUEST');
			return null;
		}
	}


	public function getVoters($id_person, $limit = 45)
	{
		$qry = "SELECT * FROM voters
			WHERE id_person = $id_person
			ORDER BY RAND()
			LIMIT 0, $limit";
		$qry_md5 = md5($qry);
		$this->memcache_queries[$qry_md5] = $qry;
		return $this->db->cachedQuery($qry_md5, $qry, ThefConfig::get('StatisticsTTL'));
	}

}
