<?php
class Autoresponder
{
	
	private $id;
	private $name;
	
	function __construct($id) {
		global $wpdb;
		
		if ("integer" !== gettype($id))
			throw new InvalidArgumentException("Autoresponder has invalid argument type of '".gettype($id). "' where 'integer' expected");
		
		$getAutoresponderInformationQuery = sprintf("SELECT AR.* FROM {$wpdb->prefix}wpr_autoresponders AR, {$wpdb->prefix}wpr_newsletters NS WHERE NS.id=AR.nid AND AR.id=%d", $id);
		$results = $wpdb->get_results($getAutoresponderInformationQuery);
		if (0 == count($results))
			throw new NonExistentAutoresponderException();		
		$autoresponder = $results[0];
		$this->id = $autoresponder->id;
		$this->nid = $autoresponder->nid;
		$this->name = $autoresponder->name;
	}
	
	
	public function getNewsletterId() {
		return $this->nid;
	}
	
	public function getName() {
		return $this->name;
	}
	
	
	public static function getAutorespondersOfNewsletter($nid) {
		
		if (!Newsletter::whetherNewsletterIDExists($nid))
			throw new NonExistentNewsletterException();
		
		$autoresponders = Autoresponder::getAllAutoresponders();
		$resultList = array();
		
		foreach ($autoresponders as $responder) {
			if ($nid == $responder->getNewsletterId())
				$resultList[] = $responder;
		}
		
		return $resultList;
	}
	
	/*
	 * 1. Get all autoresponders
	 * 2. Get only autoresponders that have a newsletter associated with them
	 */
	public static function getAllAutoresponders() {
		global $wpdb;
		$getAllAutorespondersQuery = sprintf("SELECT autores.id FROM {$wpdb->prefix}wpr_autoresponders autores, {$wpdb->prefix}wpr_newsletters newsletters WHERE autores.nid=newsletters.id;");
		$respondersRes = $wpdb->get_results($getAllAutorespondersQuery);
		
		$autoresponders = array();
		foreach ($respondersRes as $responder) {
			$autoresponders[] = new Autoresponder(intval($responder->id));
		}
		return $autoresponders;
	}
	
	public static function getAutoresponderById($autoresponder_id) {
		$resultObj = new Autoresponder($autoresponder_id);
		return $resultObj;
	}
	
	
	public static function whetherValidAutoresponder($autoresponderInfo) {
		
		if ("array" != gettype($autoresponderInfo)) {
			throw new InvalidArgumentException("Invalid type sent as argument for autoresponder validation");
		}
		
		if (!isset($autoresponderInfo['name'])) {
			throw new InvalidArgumentException("Expected autoresponder to have a name");
		}
		
		$name = trim($autoresponderInfo['name']);
		
		if (preg_match("@[\"']@",$name)) {
			return false;
		}
		
		if (0 == strlen($name)) {
			return false;
		}
		
		return true;
	}
	
	public static function addAutoresponder($nid, $name) 
	{
		global $wpdb;
		
		if (!Newsletter::whetherNewsletterIDExists($nid))
			throw new NonExistentNewsletterAutoresponderAdditionException();
		
		if (!Autoresponder::whetherValidAutoresponder(array("nid"=>$nid, "name"=>$name))) {
			throw new InvalidArgumentException("Invalid autoresponder arguments");
		}
		
		$createAutoresponderQuery = sprintf("INSERT INTO `{$wpdb->prefix}wpr_autoresponders` (`nid`, `name`) VALUES (%d, '%s');",$nid, $name);
		$wpdb->query($createAutoresponderQuery);
		$autoresponder_id = $wpdb->insert_id;
		return new Autoresponder($autoresponder_id);
	}
	
	
	
	
}

class NonExistentNewsletterAutoresponderAdditionException extends Exception {
	/*
	 * When the user tries to create a autoresponder in a non existent newsletter
	 */
}

class NonExistentNewsletterException extends Exception {
	
}


class NonExistentAutoresponderException extends Exception {
	
}