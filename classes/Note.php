<?php
if(file_exists('../config.inc.php')) include_once('../config.inc.php');
include_once($rootpath.'/fonctions.inc.php');

class Note
{	
	// Déclaration des variables
	private $id;
	private $note;
	private $user;
	private $cdate;
		
	// Constructeur
	function __construct()
	{
		// Initialisation du nom de l'utilisateur
		if(!isset($_COOKIE['user']))
			return false;
		else
			$this->user = $_COOKIE['user'];
		
		$this->cdate = date("Y-m-d H:m:i");
	}
	
	// Obtenir une citation
	function loadNote($id)
	{
		if(!is_numeric($id)) return false;
		$sql = "SELECT * FROM notes WHERE id = '$id'";
		$req = query($sql);
		$data = mysql_fetch_assoc($req);
		
		$this->note = $data['note'];
		$this->cdate = $data['cdate'];
		$this->id = $data['id'];
		$this->user = $data['user'];		
	}
	
	function getNote()
	{
		return $this->utf8(stripslashes($this->note));
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
		return fdate($this->cdate);
	}
		
	// Ajouter une note
	function addNote($note)
	{
		$quote = $this->latin1(addslashes(trim($note)));
		$sql = "INSERT INTO notes VALUES ('','$note','".$this->user."','".$this->cdate."')";
		query($sql);
	}
	
	// Partager une note
	function shareNoteWith($reader)
	{
		$reader = addslashes($reader);
		$sql = "INSERT INTO notes_users VALUES ('','".$this->id."','".$this->user."','".$reader."')";
		query($sql);
	}
	
	// Supprimer le partage
	function unShareNoteWith($reader)
	{
		$reader = addslashes($reader);
		$sql = "DELETE FROM notes_users WHERE reader = '$reader' AND note_id = '".$this->id."'";
		query($sql);
	}
	
	function getSharedWith()
	{
		$sql = "SELECT * FROM notes_users WHERE note_id = '".$this->id."'";
		
		$count = num_rows($sql);
		if($count < 1) return false;
		
		$req = query($sql);
		$result = array();
		while($data = mysql_fetch_assoc($req))
		{
			$result[] = $data['reader'];
		}
		
		return $result;
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