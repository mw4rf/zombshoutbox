<?php
/* SYNTAXE XML
<chantiers>
	<chantier>		
		<nom>Atelier</nom>
		<parent></parent>
		<pa>25</pa>
		<defense>0</defense>
		<temporaire>0</temporaire>
		<ressource nombre = "10" nom = "Planche tordue"></ressource>
		<ressource nombre = "8" nom = "Ferraille"></ressource>
		<ressource nombre = "1" nom = "Pavés de béton informes"></ressource>
	</chantier>
	...
</chantiers>

Format du tableau T : T[nom de l'objet][propriété] = valeur
Exemple: T[Ration d'eau][image] = water
*/
if(file_exists('config.inc.php')) include_once('config.inc.php');

class Chantiers
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
		@$doc->load($rootpath.'data/chantiers.xml');
		$this->DOC = $doc;
				
		// Remplissage du tableau principal
		$objets = $this->getDoc()->getElementsByTagName('chantier');
		foreach($objets as $objet)
		{
			$nom = $this->getNodeValue($objet,'nom');
			foreach($objet->childNodes as $cn)
				if(isset($cn->localName) and isset($cn->nodeValue))
				{
					if($cn->localName == "ressource")
						$this->T[$nom]["Ressources"][$cn->getAttribute('nom')] = $cn->getAttribute('nombre');
					else	
						$this->T[$nom][$cn->localName] = $cn->nodeValue;
				}
		}		
	}
	
	// Fonctions publiques
	
	public function getRessources($chantier,$enc="UTF-8") { return $this->getValue($chantier,"Ressources",$enc); }
	public function getParent($chantier,$enc="UTF-8") { return $this->getValue($chantier,"parent",$enc); }
	public function getPA($chantier,$enc="UTF-8") { return $this->getValue($chantier,"pa",$enc); }
	public function getDefense($chantier,$enc="UTF-8") { return $this->getValue($chantier,"defense",$enc); }
	public function isTemporaire($chantier,$enc="UTF-8") { return $this->getValue($chantier,"temporaire",$enc); }
	
	/*
	Cette fonction permet de rechercher des objets selon un critère.
	Par exemple, pour chercher tous les objets qui donnent plus de 4 PA : 
		$c = new Chantiers();
		$c->query("pa", ">", 4);
	*/
	public function query($propriete,$critere="",$valeur="",$enc="UTF-8")
	{
		$res = array();
		
		// Fonctions is... renvoyer true ou false
		if(empty($critere) or empty($valeur))
		{
			foreach($this->T as $chantier=>$c)
				if($this->getValue($chantier,$propriete,$enc))
					$res[] = $chantier;
		}
		// Fonctions get... effectuer une comparaison
		else
		{
			foreach($this->T as $chantier=>$c)
				switch($critere)
				{
					case '==': if($this->getValue($chantier,$propriete,$enc) == $valeur) $res[] = $chantier; break;
					case '!=': if($this->getValue($chantier,$propriete,$enc) != $valeur) $res[] = $chantier; break;
					case '<=': if($this->getValue($chantier,$propriete,$enc) <= $valeur) $res[] = $chantier; break;
					case '>=': if($this->getValue($chantier,$propriete,$enc) >= $valeur) $res[] = $chantier; break;
					case '<': if($this->getValue($chantier,$propriete,$enc) < $valeur) $res[] = $chantier; break;
					case '>': if($this->getValue($chantier,$propriete,$enc) > $valeur) $res[] = $chantier; break;
					default: return "Les critères autorisés sont : ==, !=, <=, >=, <, >"; break;
				}
		}
		
		return $res;
	}
	
	public function existe($chantier)
	{
		$cc = $this->getPA($chantier);
		if($cc)
			return true;
		else
			return false;
	}
	
	// --
	
	public function printList()
	{
		foreach($this->T as $item=>$itemvalue)
		{
			echo "<p><b>$item</b>";
			foreach($itemvalue as $prop=>$val)
				if(!is_array($val))
					echo "<br /><u>$prop</u> => <i>$val</i>";
				else
				{
					echo "<br /><u>$prop</u> => {";
					foreach($val as $p=>$v)
						echo "<br />$p x $v";
					echo "<br />}";
				}
			echo "</p>";
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
	
	public function getValue($nom,$prop,$enc="UTF-8")
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
		$chantiers = array();
		
		// insérer les éléments dans le tableau
		foreach($this->T as $chantier=>$tab)
			if($enc == "UTF-8")
				$chantiers[] = $chantier;
			else
				$chantiers[] = iconv("UTF-8", $enc, $chantier);
		
		// trier les éléments, si nécessaire
		if($sort)
			sort($chantiers);
		
		// retour du tableau
		return $chantiers;
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