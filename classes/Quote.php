<?php
if(file_exists('../config.inc.php')) include_once('../config.inc.php');
include_once($rootpath.'/fonctions.inc.php');

class Quote
{	
	// Déclaration des variables
	private $id;
	private $quote;
	private $user;
	private $adate;
		
	// Constructeur
	function __construct()
	{
		// Initialisation du nom de l'utilisateur
		if(!isset($_COOKIE['user']))
			return false;
		else
			$this->user = $_COOKIE['user'];
		
		$this->adate = date("Y-m-d");
	}
	
	// Obtenir une citation
	function getRandom()
	{
		$sql = "SELECT * FROM quotes ORDER BY RAND() LIMIT 1";
		$req = query($sql);
		$data = mysql_fetch_assoc($req);
		
		$this->quote = $data['quote'];
		$this->adate = $data['adate'];
		$this->id = $data['id'];
		$this->user = $data['user'];		
	}
	
	function getQuote()
	{
		return $this->utf8(stripslashes($this->quote));
	}
	
	function getId()
	{
		return $this->id;
	}
	
	function getUser()
	{
		return $this->user;
	}
	
	function getDate()
	{
		return fdate($this->adate);
	}
		
	// Ajouter une citation
	function addQuote($quote)
	{
		$quote = $this->latin1(addslashes(trim($quote)));
		$sql = "INSERT INTO quotes VALUES ('','$quote','".$this->user."','".$this->adate."')";
		query($sql);
	}
	
	
	// Encodage
	private function utf8($in)
	{
		return iconv("ISO-8859-1", "UTF-8", $in);
	}
	
	private function latin1($in)
	{
		return iconv("UTF-8", "ISO-8859-1", $in);
	}

}



?>