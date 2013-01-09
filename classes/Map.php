<?php
include_once("HordesXML.php");

class Map
{
	var $CELLS; // initialisée par le constructeur
	var $CELL; // initialisée par setCell()
	var $VILLE; // initialisée par le constructeur
	var $HEIGHT; // initialisée par le constructeur
	var $WIDTH; // initialisée par le constructeur
	
	// Constructeur
	function __construct()
	{
		$h = new HordesXML();
		$hzones = $h->getMap();
		
		// Remplir le tableau de la carte
		$this->CELLS = array();
		foreach($hzones as $hzone)
		{
			$x = $hzone->getAttribute('x'); // abs
			$y = $hzone->getAttribute('y'); // ord
			$z = $hzone->getAttribute('z'); // nombre de zomb, si connu

			if(!empty($z))
				$this->CELLS["$x.$y"]['z'] = $hzone->getAttribute('z');
			else
				$this->CELLS["$x.$y"]['z'] = 0;

			$this->CELLS["$x.$y"]['nvt'] = $hzone->getAttribute('nvt'); 
			$this->CELLS["$x.$y"]['danger'] = $hzone->getAttribute('danger');
			$this->CELLS["$x.$y"]['tag'] = $hzone->getAttribute('tag');

			$bats = $hzone->getElementsByTagName('building'); // bâtiment
			foreach($bats as $bat)
				if(!empty($bat))
					$this->CELLS["$x.$y"]['bat'] = 1;					
		}
		
		// Définir la case de la ville
		$this->VILLE = $h->getVillePos();
		
		// Taille de la map
		$this->HEIGHT = $h->getMapHeight();
		$this->WIDTH = $h->getMapWidth();
	}
	
	// Définit une bonne fois pour toutes la case sur laquelle on travaille, pour avoir à la définir
	// à chaque appel des fonctions publiques de cette classe
	public function setCell($x,$y)
	{
		$this->CELL = "$x.$y";
	}
	
	public function getCell()
	{
		if(isset($this->CELL) and !empty($this->CELL))
			return $this->CELL;
		else
			return false;
	}
	
	public function getHeight()
	{
		return $this->HEIGHT;
	}
	
	public function getWidth()
	{
		return $this->WIDTH;
	}
	
	public function getDistance($x=false,$y=false)
	{
		if(is_numeric($x) and is_numeric($y))
			$this->setCell($x,$y);
			
		return $this->DistanceBetweenCells($this->VILLE , $this->getCell());
	}
	
	public function getVille()
	{
		$ville = explode("." , $this->VILLE);
		$v['x'] = $ville[0];
		$v['y'] = $ville[1];
		return $v;
	}
	
	public function isVille($x=false,$y=false)
	{
		if(is_numeric($x) and is_numeric($y))
			$this->setCell($x,$y);
		
		if($this->getCell() == $this->VILLE)
			return true;
		else
			return false;
	}
	
	public function getZomb($x=false,$y=false)
	{
		if(is_numeric($x) and is_numeric($y))
			$this->setCell($x,$y);
				
		if(isset($this->CELLS[$this->getCell()]['z']))
			return $this->CELLS[$this->getCell()]['z'];
		else
			return "";
	}
	
	public function getDanger($x=false,$y=false)
	{
		if(is_numeric($x) and is_numeric($y))
			$this->setCell($x,$y);
			
		if(isset($this->CELLS[$this->getCell()]['danger']))
			return $this->CELLS[$this->getCell()]['danger'];
		else
			return 0;
	}
	
	public function hasBat($x=false,$y=false)
	{
		if(is_numeric($x) and is_numeric($y))
			$this->setCell($x,$y);
			
		if(isset( $this->CELLS[$this->getCell()]['bat'] ))
			return true;
		else
			return false;
	}
	
	public function hasBeenVisited($x=false,$y=false)
	{
		if(is_numeric($x) and is_numeric($y))
			$this->setCell($x,$y);
			
		if(isset( $this->CELLS[$this->getCell()]['nvt'] ))
			return true;
		else
			return false;
	}
	
	public function getTag($x=false,$y=false)
	{
		if(is_numeric($x) and is_numeric($y))
			$this->setCell($x,$y);
			
		$tag = $this->CELLS[$this->getCell()]['tag'];		
		switch($tag)
		{
			case 0: $res = ""; break;
			case 1: $res = ""; break;
			case 2: $res = ""; break;
			case 3: $res = ""; break;
			case 4: $res = ""; break;
			case 5: $res = ""; break;
			case 6: $res = ""; break;
			case 7: $res = ""; break;
			case 8: $res = ""; break;
			default: $res = ""; break;
		}
		return $res;
	}
	
	public function getArea($x,$y)
	{			
		$area = array();

		$area['t'] = false; 
		$area['b'] = false; 
		$area['l'] = false; 
		$area['r'] = false;
				
		// Coordonnées de la ville
		$ville = $this->getVille();
		$vx = $ville['x'];
		$vy = $ville['y'];
		
		// 
		$fi = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14);
		$si = array(0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8);
		foreach($fi as $i)
		{
			// Branches numérotées dans le sens des aiguilles d'une montre à partir de midi
			$r[$vx+$si[$i]][$vy-$i] = 1; // 1
			$t[$vx+$i][$vy-$si[$i]] = 1; // 2
			$b[$vx+$i][$vy+$si[$i]] = 1; // 3
			$r[$vx+$si[$i]][$vy+$i] = 1; // 4
			$l[$vx-$si[$i]][$vy+$i] = 1; // 5
			$b[$vx-$i][$vy+$si[$i]] = 1; // 6
			$t[$vx-$i][$vy-$si[$i]] = 1; // 7
			$l[$vx-$si[$i]][$vy-$i] = 1; // 8
			
			// Traits complémentaires (de jonction) -- non nécessaire, mais plus esthétique
			if($y % 2 != 0)	$b[$vx+$si[$i]][$vy-$i] = 1; // 1
			if($x % 2 == 0) $l[$vx+$i][$vy-$si[$i]] = 1; // 2
			if($x % 2 == 0) $l[$vx+$i][$vy+$si[$i]] = 1; // 3
			if($y % 2 != 0)	$t[$vx+$si[$i]][$vy+$i] = 1; // 4
			if($y % 2 != 0)	$t[$vx-$si[$i]][$vy+$i] = 1; // 5
			if($x % 2 == 0) $r[$vx-$i][$vy+$si[$i]] = 1; // 6
			if($x % 2 == 0) $r[$vx-$i][$vy-$si[$i]] = 1; // 7
			if($y % 2 != 0)	$b[$vx-$si[$i]][$vy-$i] = 1; // 8	
		}
		
		if(isset($t[$x][$y]) and $t[$x][$y] == 1)
			$area['t'] = true;
		if(isset($b[$x][$y]) and $b[$x][$y] == 1)
			$area['b'] = true;
		if(isset($r[$x][$y]) and $r[$x][$y] == 1)
			$area['r'] = true;
		if(isset($l[$x][$y]) and $l[$x][$y] == 1)
			$area['l'] = true;
		
		// Retourne un tableau avec les clés t,r,b,l (top,right,bottom,left), et pour chaque clé
		// la valeur true s'il doit y avoir un trait et false s'il ne doit pas y en avoir.
		return $area;
	}
	
	//----------------------
	
	private function DistanceBetweenCells($cell1,$cell2)
	{
		$c1 = explode(".",$cell1);
		$x1 = $c1[0];
		$y1 = $c1[1];
		
		$c2 = explode(".",$cell2);
		$x2 = $c2[0];
		$y2 = $c2[1];
				
		// Distance horizontale à parcourir
		$xd = abs ( $x2 - $x1 ); // abs() retourne la valeur absolue : |-1| = 1
		// Distance verticale
		$yd = abs ( $y2 - $y1 );
		// Distance totale
		$td = $xd + $yd;
		// Aller-retour
		$td = $td * 2;
		//
		return $td;
	}
}
?>