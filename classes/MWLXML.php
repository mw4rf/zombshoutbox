<?php

class MWLXML
{
	var $XMLURL = "http://hordes.my-css-lab.com/xml.php?name=";
	var $DOC; // initialisée par le constructeur
	
	// Constructeur
	function __construct()
	{
	}
	
	// Charge la classe avec le nom d'un seul joueur
	function load_withName($nom)
	{
		$doc = new DOMDocument();
		@$doc->load($this->XMLURL.$nom);
		$this->DOC = $doc;
	}
	
	// Charge la classe avec les noms de plusieurs joueurs
	function load_withNames($noms)
	{
		$nload = "";
		foreach($noms as $nom)
			$nload .= $nom.",";
		$nload = trim($nload,",");
		$this->load_withName($nload);
	}
	
	// Récupère les infos sur le joueur donné
	function getInfos($name)
	{
		$infos = false;
		$cits = $this->getXML()->getElementsByTagName('citizen');
		foreach($cits as $cit)
			if( $cit->getAttribute('name') == strtolower($name) )
			{
				$infos['recomms'] = $cit->getAttribute('recomms');
				$infos['pos_chieur'] = $cit->getAttribute('pos_chieur');
				$infos['plaintes_donnees'] = $cit->getAttribute('plaintes_donnees');
				$infos['plaintes'] = $cit->getAttribute('plaintes');
				
				// Ratio recomms/paintes
				$r = $infos['recomms']; $p = $infos['plaintes'];
				if($p == 0 or !is_numeric($p)) $p = 1;
				if(!is_numeric($r)) $r = 0;
				
				$infos['ratio'] = $r / $p;
				
				if($infos['ratio'] > 1) $infos['ratio'] = round($infos['ratio']);
			}
		return $infos;
	}
	
	// Renvoie le document
	private function getXML()
	{
		return $this->DOC;
	}
	
	
}



?>