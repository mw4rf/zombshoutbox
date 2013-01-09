<?php
if(file_exists('config.inc.php')) include_once('config.inc.php'); else include_once('../config.inc.php');
if(file_exists('fonctions.inc.php')) include_once('fonctions.inc.php'); else include_once('../fonctions.inc.php');

class Censure
{	
	// D�claration des variables
	private $user;
	private $mots;
	private $frequence;
	private $actif;
		
	// Constructeur
	function __construct()
	{
		// Initialisation du nom de l'utilisateur
		if(!isset($_COOKIE['user']))
			return false;
		else
			$this->user = $_COOKIE['user'];
			
		// Initialisation de la fr�quence et du caract�re actif
		$sql = "SELECT frequence,actif FROM censure_users WHERE user='".$this->user."'";
		$req = query($sql);
		$data = mysql_fetch_assoc($req);
		$this->frequence = $data['frequence'];
		$this->actif = $data['actif'];
				
		if($this->frequence > 100) $this->frequence = 100;
		
		$this->init_mots();
	}
	
	// Intialisation des mots
	private function init_mots()
	{
		$this->mots = "";
		$sql = "SELECT * FROM censure_mots";
		$req = query($sql);
		while($data = mysql_fetch_assoc($req))
			$this->mots[] = stripslashes(iconv("ISO-8859-1","UTF-8",$data['mot']));
	}
	
	// R�cup�rer les mots
	function get_mots()
	{
		return $this->mots;
	}
	
	// R�cup�ration compl�te de toutes les infos sur les mots
	function get_mots_list()
	{
		$mots = ""; $ids = ""; $liste = "";
		$sql = "SELECT * FROM censure_mots ORDER BY id DESC";
		$req = query($sql);
		$count = num_rows($sql); if($count < 1) return false;
		while($data = mysql_fetch_assoc($req))
		{
			$mots[] = stripslashes($data['mot']);
			$ids[] = $data['id'];
		}
		for($i = 0 ; $i < count($ids) ; $i++)
		{
			$liste[$i]["mot"] = $mots[$i];
			$liste[$i]["id"] = $ids[$i];
		}
		return $liste;
	}
	
	// R�cup�ration compl�te de toutes les infos sur les utilisateurs
	function get_users_list()
	{
		$us = ""; $fr = ""; $ac = ""; $users = "";
		$sql = "SELECT * FROM censure_users";
		$req = query($sql);
		$count = num_rows($sql); if($count < 1) return false;
		while($data = mysql_fetch_assoc($req))
		{
			$us[] = $data['user'];
			$fr[] = $data['frequence'];
			$ac[] = $data['actif'];
		}
		for($i = 0 ; $i < count($us) ; $i++)
		{
			$users[$i]['user'] = $us[$i];
			$users[$i]['frequence'] = $fr[$i];
			$users[$i]['actif'] = $ac[$i];
		}
		return $users;
	}
	
	// Fonction principale
	function censurer($phrase)
	{
		// Si la censure est d�sactiver, retourner le mot normal
		if($this->actif != 1) return $phrase;
		// Sinon censurer
		$in = explode(" ", $phrase);
		$out = "";
		// Parcourir tous les mots de la phrase
		foreach($in as $t)
		{
			// D�terminer si l'on doit censurer ou non, en fonction de la fr�quence attribu�e � l'utilisateur
			$censurer = ceil(rand(0,100));
			if($this->frequence > $censurer)
				$t = $this->mots[ceil(rand(0,count($this->mots)))];
			$out .= $t." ";
		}
		$out = trim($out);
		return $out;
	}
	
	// Ajouter un mot
	function ajouter_mot($mot)
	{
		$mot = trim(addslashes($mot));
		if(empty($mot)) return false;
		$sql = "INSERT INTO censure_mots VALUES ('','$mot')";
		query($sql);
		$this->init_mots(); // apr�s modification, on met � jour la liste des mots
	}
	
	// Supprimer un mot
	function supprimer_mot($id)
	{
		$sql = "DELETE FROM censure_mots WHERE id='$id'";
		query($sql);
		$this->init_mots(); // apr�s modification, on met � jour la liste des mots
	}
	
	// Modifier un mot
	function modifier_mot($id,$mot)
	{
		$mot = addslashes($mot);
		$sql = "UPDATE censure_mots SET mot='$mot' WHERE id='$id'";
		query($sql);
		$this->init_mots(); // apr�s modification, on met � jour la liste des mots
	}
	
	// Ajouter un utilisateur
	function ajouter_user($user,$frequence=50,$actif=1)
	{
		$sql = "INSERT INTO censure_users VALUES ('$user','$frequence','$actif')";
		query($sql);
	}
	
	// Supprimer un utilisateur
	function supprimer_user($user)
	{
		$sql = "DELETE FROM censure_users WHERE user='$user'";
		query($sql);
	}
	
	// Modifier la fr�quence
	function modifier_frequence($user,$frequence)
	{
		$sql = "UPDATE censure_users SET frequence='$frequence' WHERE user='$user'";
		query($sql);
	}
	
	// Activer
	function activer($user)
	{
		$sql = "UPDATE censure_users SET actif='1' WHERE user='$user'";
		query($sql);
	}
	
	// D�sactiver
	function desactiver($user)
	{
		$sql = "UPDATE censure_users SET actif='0' WHERE user='$user'";
		query($sql);
	}
	

}



?>