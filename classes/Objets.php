<?php
/* SYNTAXE XML
<objets>
	<objet>
		<nom>Ration d'eau</nom> #nom de l'objet
		<image>water</image> #icone de l'objet dans Hordes => item_X.gif
		<categorie>Food</categorie> #Catégorie de l'objet dans Hordes
		<valeur>5</valeur> #Valeur de l'objet : de 1 à 5 selon son utilité
		<chantiers>1</chantiers> #Utilisée dans la construction des chantiers ? 1/0
		<pa>6</pa> #Nombre de PA donnés lors de la consommation
		<defense>0</defense> #Donne de la défense à la ville ? 1/0
		<transformable>0</transformable> #Transformable en un autre objet à l'atelier ? 1/0
		<assemblable>1</assemblable> #Assemblable avec d'autres objets ? 1/0
		<consommable>1</consommable> #Consommable par un citoyen ? 1/0
		<arme>0</arme> #Utilisable pour tuer les zombies dans le désert ? 1/0
		<cassable>0</cassable> #Peut-être cassé (et mis en banque avec l'état "cassé") ? 1/0
		<maison>0</maison> #Utilisé dans l'amélioration de la maison ou dans le coffre ? 1/0
		<encombrant>0</encombrant> #Objet encombrant (1 par personne en expé) ? 1/0
	</objet>
	...
</objets>

Format du tableau T : T[nom de l'objet][propriété] = valeur
Exemple: T[Ration d'eau][image] = water
*/

if(file_exists('config.inc.php')) include_once('config.inc.php');

class Objets
{	
	// Variables de classe
	private $DOC;
	private $T;
	
	// Constructeur
	function __construct()
	{
		global $rootpath; // défini dans config.inc.php qui doit être inclu en arrivant ici
		
		// Chargement du fichier XML
		$doc = new DOMDocument();
		@$doc->load($rootpath.'data/objets.xml');
		$this->DOC = $doc;
				
		// Remplissage du tableau principal
		$objets = $this->getDoc()->getElementsByTagName('objet');
		foreach($objets as $objet)
		{
			$nom = $this->getNodeValue($objet,'nom');
			$utilisations = 0;
			foreach($objet->childNodes as $cn)
			{
				if(isset($cn->localName) and isset($cn->nodeValue))
				{
					if($cn->localName == "utilisation")
					{
						foreach($cn->childNodes as $c)
							if(isset($c->localName) and isset ($c->nodeValue))
								$this->T[$nom]['utilisation'][$utilisations][$c->localName][] = $c->nodeValue;
						$utilisations++;
					}
					else
						$this->T[$nom][$cn->localName] = $cn->nodeValue;
				}
			}
		}
	}
	
	// Fonctions publiques
	
	public function getImage($objet,$enc="UTF-8") { return $this->getValue($objet,"image",$enc); }
	public function getCategorie($objet,$enc="UTF-8") { return $this->getValue($objet,"categorie",$enc); }
	public function getValeur($objet,$enc="UTF-8") { return $this->getValue($objet,"valeur",$enc); }
	public function isForChantiers($objet,$enc="UTF-8") { return $this->getValue($objet,"chantiers",$enc); }
	public function getPA($objet,$enc="UTF-8") { return $this->getValue($objet,"pa",$enc); }
	public function isForDefense($objet,$enc="UTF-8") { return $this->getValue($objet,"defense",$enc); }
	public function isTransformable($objet,$enc="UTF-8") { return $this->getValue($objet,"transformable",$enc); }
	public function isAssemblable($objet,$enc="UTF-8") { return $this->getValue($objet,"assemblable",$enc); }
	public function isConsommable($objet,$enc="UTF-8") { return $this->getValue($objet,"consommable",$enc); }
	public function isArme($objet,$enc="UTF-8") { return $this->getValue($objet,"arme",$enc); }
	public function isCassable($objet,$enc="UTF-8") { return $this->getValue($objet,"cassable",$enc); }
	public function isForMaison($objet,$enc="UTF-8") { return $this->getValue($objet,"maison",$enc); }
	public function isEncombrant($objet,$enc="UTF-8") { return $this->getValue($objet,"encombrant",$enc); }
	public function isUtilisable($objet,$enc="UTF-8") { return $this->getValue($objet,"utilisable",$enc); }
	public function isDangereux($objet,$enc="UTF-8") { return $this->getValue($objet,"danger",$enc); }
	public function getNote($objet,$enc="UTF-8") { return $this->getValue($objet,"note",$enc); }
	public function getUtilisations($objet,$enc="UTF-8") { return $this->getValue($objet,"utilisation",$enc); }
	
	/*
	Cette fonction permet de rechercher des objets selon un critère.
	Par exemple, pour chercher tous les objets qui donnent plus de 4 PA : 
		$c = new Objets();
		$c->query("pa", ">", 4);
	*/
	public function query($propriete,$critere="",$valeur="",$enc="UTF-8")
	{
		$res = array();
		
		// Fonctions is... renvoyer true ou false
		if(empty($critere) or empty($valeur))
		{
			foreach($this->T as $objet=>$o)
				if($this->getValue($objet,$propriete,$enc))
					$res[] = $objet;
		}
		// Fonctions get... effectuer une comparaison
		else
		{
			foreach($this->T as $objet=>$o)
				switch($critere)
				{
					case '==': if($this->getValue($objet,$propriete,$enc) == $valeur) $res[] = $objet; break;
					case '!=': if($this->getValue($objet,$propriete,$enc) != $valeur) $res[] = $objet; break;
					case '<=': if($this->getValue($objet,$propriete,$enc) <= $valeur) $res[] = $objet; break;
					case '>=': if($this->getValue($objet,$propriete,$enc) >= $valeur) $res[] = $objet; break;
					case '<': if($this->getValue($objet,$propriete,$enc) < $valeur) $res[] = $objet; break;
					case '>': if($this->getValue($objet,$propriete,$enc) > $valeur) $res[] = $objet; break;
					default: return "Les critères autorisés sont : ==, !=, <=, >=, <, >"; break;
				}
		}
		
		return $res;
	}
	
	public function existe($objet)
	{
		$oo = $this->getValeur($objet);
		if($oo)
			return true;
		else
			return false;
	}
	
	// --
	
	public function printList()
	{
		//$this->printArray($this->T);
		print_nice($this->T);
	}
	
	private function printArray($tab)
	{
		foreach($tab as $item=>$itemvalue)
		{
			echo "<ul>";
			foreach($itemvalue as $prop=>$val)
				if(is_array($val))
					$this->printList($val);
				else
					echo "<li><u>$prop</u> => <i>$val</i></li>";
			echo "</ul>";
		}
	}
	
	// Accesseurs génériques
	
	private function getDoc()
	{
		return $this->DOC;
	}
	
	private function getNodeValue($parent,$node)
	{
		//$this->T[$nom_de_l'objet]['propriété'] = $this->getNodeValue($objet,'propriété');
		$nodes = $parent->getElementsByTagName($node);
		foreach($nodes as $node)
			return $node->firstChild->nodeValue;
	}
	
	// Accesseurs spécifiques (propriétés)
	
	private function getValue($nom,$prop,$enc="UTF-8")
	{
		if(!isset($this->T[$nom][$prop]) or empty($this->T[$nom][$prop]))
			return false;
		
		if($enc == "UTF-8")
			return $this->T[$nom][$prop];
		else
			return iconv("UTF-8", $enc, $this->T[$nom][$prop]);
	}
	
	public function getAllNames($sort=false,$enc="UTF-8")
	{	
		// tableau à retourner
		$objets = array();
		
		// insérer les éléments dans le tableau
		foreach($this->T as $objet=>$tab)
			if($enc == "UTF-8")
				$objets[] = $objet;
			else
				$objets[] = iconv("UTF-8", $enc, $objet);
		
		// trier les éléments, si nécessaire
		if($sort)
			sort($objets);
		
		// retour du tableau
		return $objets;
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