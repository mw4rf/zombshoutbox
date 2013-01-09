<?php

class HordesXML
{
	const XMLURL = "http://www.hordes.fr/xml/?k=";
	var $DOC; // initialisée par le constructeur
	
	// Constructeur
	function __construct()
	{
		// Chargement du fichier XML
		$doc = new DOMDocument();
		@$doc->load('http://www.hordes.fr/xml/?k='.$this->getKey());
		$this->DOC = $doc;
	}
	
	// Récupère la clé API de l'utilisateur courant
	private function getKey()
	{
		if(isset($_COOKIE['key']))
			return $_COOKIE['key'];
		else
			return false;
	}
	
	// Renvoie le document
	private function getXML()
	{
		return $this->DOC;
	}
	
	// Le jour de jeu
	public function getDay()
	{
		$gm = $this->getXML()->getElementsByTagName('game');
		foreach($gm as $g)
			$day = $g->getAttribute('days');
		return $day;
	}
	
	// La ville
	public function getVille()
	{
		$cities = $this->getXML()->getElementsByTagName('city');
		foreach($cities as $city)
			$ville = $city->getAttribute('city');
		return $ville;
	}
	
	public function getVillePos()
	{
		$cities = $this->getXML()->getElementsByTagName('city');
		foreach($cities as $city)
			$ville = $city->getAttribute('x').".".$city->getAttribute('y');
		return $ville;
	}
	
	// Les portes
	public function getDoor()
	{
		$cities = $this->getXML()->getElementsByTagName('city');
		foreach($cities as $city)
			$door = $city->getAttribute('door');
		return $door;
	}
	
	// La banque
	public function getBanque()
	{
		return $this->getXML()->getElementsByTagName('item');
	}
	
	// La map
	public function getMap()
	{
		return $this->getXML()->getElementsByTagName('zone');
	}
	
	public function getMapHeight()
	{
		$maps = $this->getXML()->getElementsByTagName('map');
		foreach($maps as $map)
			$theight = $map->getAttribute('hei');
		return $theight;
	}
	
	public function getMapWidth()
	{
		$maps = $this->getXML()->getElementsByTagName('map');
		foreach($maps as $map)
			$twidth = $map->getAttribute('wid');
		return $twidth;
	}
	
	// Les expéditions
	public function getExpeditions()
	{
		$res = ""; $count = 0;
		$expes = $this->getXML()->getElementsByTagName('expedition');
		foreach($expes as $expe)
		{
			$res[$count]['name'] = $expe->getAttribute("name");
			$res[$count]['author'] = $expe->getAttribute("author");
			$res[$count]['length'] = $expe->getAttribute("length");
			$count++;
		}
		if(!empty($res)) return $res; else return false;
	}
	
	// Défense
	public function getDefense($p='total')
	{
		$defs = $this->getXML()->getElementsByTagName('defense');
		
		foreach($defs as $def)
		{
			if($p == 'base') return $base = $def->getAttribute('base');
			if($p == 'gardiens') return $gardiens = $def->getAttribute('citizen_guardians');
			if($p == 'maisons') return $maisons = $def->getAttribute('citizen_homes');
			if($p == 'chantiers') return $batiments = $def->getAttribute('buildings');
			if($p == 'total') return $total = $def->getAttribute('total');
			if($p == 'objets') return $total = $def->getAttribute('items');
			if($p == 'fixations') return $total = $def->getAttribute('itemsMul');
			if($p == 'od') 
			{
				$objets = $def->getAttribute('items');
				$fixations = $def->getAttribute('itemsMul');
				return $objfix = $objets * $fixations;
			}
		}
		return false;
	}
	
	// Zomb attaque
	public function getAttaque($p = 'max')
	{
		$attqs = $this->getXML()->getElementsByTagName('e');
		$attaque = array();
		foreach($attqs as $attq)
		{
			if($p == 'jour') return $attq->getAttribute('day');
			if($p == 'max') return $attq->getAttribute('max');
			if($p == 'min') return $attq->getAttribute('min');
			if($p == 'maxed') return $attq->getAttribute('maxed');
		}
		return false;
	}
	
	// Nombre de citoyens vivants
	public function numCitoyens()
	{
		$citizens = $this->getXML()->getElementsByTagName('citizen');
		$cit = 0;
		foreach($citizens as $citizen)
			$cit++;
		return $cit;
	}
	
	// Les chances de survie
	public function getSurvie($def_perso)
	{	
		$survie = array();
		
		$defense = $this->getDefense();
		
		$nbz_min = $this->getAttaque('min') - $defense;
		$nbz_max = $this->getAttaque('max') - $defense;
		
		$nb_cit = $this->numCitoyens();
		
		if($def_perso >= $nbz_min or $nbz_min == 0 or $nbz_min < 0)
		{
			$survie['min'] = 100;
			$survie['max'] = 100;
		}
		else
		{
			$survie['max'] = round($this->somme_binomiale($def_perso, $nbz_min, 1/$nb_cit) * 100 , 2);
			$survie['min'] = round($this->somme_binomiale($def_perso, $nbz_max, 1/$nb_cit) * 100 , 2);
		}
			
		// Retourne un tableau contenant le % de chances de survie en fonction du nombre de tps de 
		// défenses personnelles. P. ex. pour un taudis à 3 pts de défense : $survie = ... %
		return $survie;
	}
	
	#################################################################
	// Fonctions utilitaires
	public function todb($string)
	{
		return addslashes(iconv("UTF-8","ISO-8859-1",$string));
	}
	
	#################################################################
	// Fonctions privées
	
	private function factorielle($n)
	{
		if($n === 0)
		{ 
			return 1;
		}
		else
		{
			return $n*$this->factorielle($n-1);
		}
	}
	
	private function combinatoire($n,$k)
	{
		$x = 1 ; $y = 1;
	    
		for($i = $n-$k+1 ; $i < $n+1 ; $i++)
			$x = $x * $i;
		
		for($i = 2 ; $i < $k+1 ; $i++)
			$y = $y * $i;
			
		return $x / $y;
	}
	
	private function binomiale($k, $n, $p)
	{
		return $this->combinatoire($n, $k) * pow( $p , $k ) * pow( 1-$p , $n-$k );		
	}
	
	private function somme_binomiale($k, $n, $p)
	{
		$res = 0;
		for($i = 0 ; $i < $k + 1 ; $i++)
		{
			$b = $this->binomiale($i, $n, $p);
			if($b == 0) break;
			$res = $res + $b;
		}
		return $res;
	}
}



?>