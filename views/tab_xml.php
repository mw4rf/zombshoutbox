<?php
include("../config.inc.php");
include("../fonctions.inc.php");
include_once("../classes/HordesXML.php");
header( 'Content-Type: text/xml; charset=UTF-8' );

$h = new HordesXML();

// Rencontres
include_once('../rencontres.php');
update();

// Statistiques de l'attaque
include_once('../graphiques.php');

if(!isset($_COOKIE['key'])) die("<div class=\"tabmaj\">Vous devez fournir votre cl&eacute; API pour acc&eacute;der aux informations de votre ville. Veuillez consulter l'aide.</div>");

// TABS
?>
	<ul id="innertabs_xml" class="tabs">
		<li class="tab" id="tabxml1"><a href="#innertab_xml_1"><img style="vertical-align: middle;" src="smilies/toolbar/ma_ville/attaque.gif" />&nbsp;L'Attaque</a></li>
		<li class="tab" id="tabxml2"><a href="#innertab_xml_2"><img style="vertical-align: middle;" src="smilies/toolbar/ma_ville/banque.gif" />&nbsp;La Banque</a></li>
		<li class="tab" id="tabxml3"><a href="#innertab_xml_3"><img style="vertical-align: middle;" src="smilies/toolbar/ma_ville/chantiers.gif" />&nbsp;Les Chantiers</a></li>
		<li class="tab" id="tabxml4"><a href="#innertab_xml_4"><img style="vertical-align: middle;" src="smilies/toolbar/ma_ville/desert.gif" />&nbsp;Le D&eacute;sert</a></li>
		<li class="tab" id="tabxml5"><a href="#innertab_xml_5"><img style="vertical-align: middle;" src="smilies/toolbar/ma_ville/citoyens.gif" />&nbsp;Les Citoyens</a></li>
	</ul>
<?php
// Initialisation
$key = $_COOKIE['key'];
$doc = getxml($key);
$output_misc = "";
$output_banque = "";
$output_banque_graph = "";
$output_attaque = "";
$output_desert = "";
$output_citoyens = "";
$output_chantiers = "";

$now = date("H:i:s");
$output_misc .= "<div class=\"tabmaj\">Derni&egrave;re mise &agrave; jour &agrave; $now&nbsp;&nbsp;<input id=\"tab_xml_rb\" type=\"submit\" value=\"&#x238B; Actualiser\" onclick=\"ajaxrefresh('tab_xml')\"></div>";

// Nom de la ville
$ville = $h->getVille();
$ville = iconv("UTF-8", "ISO-8859-1", $ville);

// Portes
$portes = $h->getDoor();
if($portes)
	$portes = "<span class=\"d1\">Portes ouvertes</span>";
else
	$portes = "<span class=\"d3\">Portes ferm&eacute;es</span>";

$output_misc .= "<div class=\"command_tab big center\">Ma Ville : <i>$ville</i><br />$portes</div>";

$ville = addslashes($ville);
$ma_ville = $ville;
 
##########################################################################################
// Banque
$msg = " ";
$objets = array();
$items = $h->getBanque();
$compteur = 0;
$archives = "";
foreach($items as $item)
{
	$objets[$compteur]['name'] = $item->getAttribute('name');
	$objets[$compteur]['icon'] = $item->getAttribute('img');
	$objets[$compteur]['count'] = $item->getAttribute('count');
	$objets[$compteur]['cat'] = $item->getAttribute('cat');
	$objets[$compteur]['broken'] = $item->getAttribute('broken');
	$compteur++;
	
	$archives .= $item->getAttribute('count')
				.":".$item->getAttribute('name')
				.":".$item->getAttribute('img')
				.":".$item->getAttribute('cat')
				.":".$item->getAttribute('broken')
				.",";
}
$archives = trim($archives,",");
$archives = $h->todb($archives);

// Enregistrement dans les archives
if(!empty($archives))
{
	$sql = "SELECT * FROM xml_banque WHERE TIMESTAMPDIFF(MINUTE,adate,NOW()) < 10 ORDER BY id DESC LIMIT 0,1";
	$count = num_rows($sql);
	if($count < 1)
	{	
		$sql = "SELECT * FROM xml_banque ORDER BY id DESC LIMIT 0,1";
		$req = query($sql);
		$data = mysql_fetch_assoc($req); 
		$oldarchives = $data['banque'];
		
		if($oldarchives != stripslashes($archives))
		{
			$adate = date("Y-m-d H:i:s");
			$sql = "INSERT INTO xml_banque VALUES ('','$ville','$adate','$archives')";
			query($sql);
		}	
	}
}
// Préparation de l'affichage
$cats = '';
// Tri des catégories
foreach($objets as $key=>$objet)	
	$cats[$key] = $objet['cat'];

// Supprimer les doublons
$cats = array_unique($cats);

// Parcourir les catégories
foreach($cats as $cat)
{
	// Traduction des catégories
	switch($cat)
	{
		case 'Rsc': $cat_trad = "Ressources"; break;
		case 'Weapon': $cat_trad = "Armes"; break;
		case 'Armor': $cat_trad = "D&eacute;fense"; break;
		case 'Misc': $cat_trad = "Divers"; break;
		case 'Drug': $cat_trad = "Drogues"; break;
		case 'Furniture': $cat_trad = "Meubles"; break;
		case 'Food': $cat_trad = "Nourriture"; break;
		case 'Box': $cat_trad = "Conteneurs"; break;
		default: $cat_trad = $cat;
	}

	// Affichage
	$msg .= "<tr class=\"bank_cat\"><td class=\"bank_cat_header\">$cat_trad</td>";
	$msg .= "<td class=\"bank_cat_content\">";
	$objcount = 0; // compte le nombre d'objets dans chaque catégorie
	// Parcourir les objets
	foreach($objets as $key=>$objet)
	{
		// Si l'objet appartient à la catégorie courante
		if($objet['cat'] == $cat)
		{	
			if($objet['broken'])
				$spanstyle = " style=\"border: 1px dotted red; padding-top: 5px;\"";
			else
				$spanstyle = "";
			
			$msg .= "&nbsp;<span$spanstyle><img src=\"http://data.hordes.fr/gfx/icons/item_".$objet['icon'].".gif\" />"
			     ."&nbsp;".$objet['count']."</span>&nbsp;&nbsp;";

			$objcount += $objet['count'];	
		}
	}
	$msg .= "</td>";
	$msg .= "<td class=\"bank_cat_total\">$objcount</td>"; // Total d'objets par catégorie
	$msg .= "</tr>";
}


//$msg .= "&nbsp;<img src=\"http://data.hordes.fr/gfx/icons/item_$icon.gif\" /> x$count&nbsp;&nbsp;";
include_once("../archives_banque.php");
$output_banque .= "<div class=\"command_tab\">"
		."<div class=\"bank_archives\">".archives_banque()."</div>"
		."<div class=\"bank_table\"></div><table class=\"bank\"><tr><td colspan=\"2\" class=\"bank_cat_header\">"
		."Liste des objets en banque</td><td class=\"bank_cat_total bank_cat_header\">Total</td></tr>"
		.trim($msg)."</table></div>";
##########################################################################################
// GRAPHIQUES
$output_banque_graph .= "<div class=\"command_tab\" style=\"text-align:center;\"><img src=\"graphiques/gobjdef.php\" alt=\"Graphique OD\"/></div>";
$output_banque_graph .= "<div class=\"command_tab\" style=\"text-align:center;\"><img src=\"graphiques/geau.php\" alt=\"Graphique Eau\"/></div>";
$output_banque_graph .= "<div class=\"command_tab\" style=\"text-align:center;\"><img src=\"graphiques/gobjets.php\" alt=\"Graphique Objets\"/></div>";
##########################################################################################
// GRAPHIQUES
$output_attaque .= "<div class=\"command_tab\" style=\"text-align:center;\"><img src=\"graphiques/gattaque.php\" alt=\"Graphique Attaque\"/></div>";
##########################################################################################
// Défense

$base = $h->getDefense('base');
$gardiens = $h->getDefense('gardiens');
$maisons = $h->getDefense('maisons');
$batiments = $h->getDefense('chantiers');
$objets = $h->getDefense('objets');
$fixations = $h->getDefense('fixations');
$objfix = $h->getDefense('od');
$total = $h->getDefense();

$dayatq = $h->getAttaque('jour');
$max = $h->getAttaque('max');
$min = $h->getAttaque('min');
$prec = $h->getAttaque('maxed');
	if($prec == 1) $prec = "Estimation <b>finale</b>"; 
	else $prec = "Estimation <b>provisoire</b>";
	
if(empty($max))
{
	$estimatq = "<br /><b>ATTAQUE :</b> Estimation indisponible.";
	$acptper = 0;
}
else
{
	$atq = "<b>$min &lt; $max</b> ($prec)";
	$acptper = ($total*100)/$max;
	
	$matq = "";
	if($max < $total)
		$matq = "| <span class=\"d1\">D&eacute;fenses suffisantes !</span>";
	elseif($max > $total and $min < $total and $acptper > 80)
		$matq = "| <span class=\"d2\">D&eacute;fenses insuffisantes !</span>";
	elseif($max > $total and $min > $total)
		$matq = "| <span class=\"d3\">D&eacute;fenses largement insuffisantes !</span>";
		

	$estimatq = "<br /><b>ATTAQUE <i>jour $dayatq</i> :</b> $atq $matq";
}


$output_attaque .=	"<div class=\"command_tab\"><u>D&eacute;fenses de la ville</u> : ".
		"<br /><img src=\"smilies/h_door.gif\" /> <b>$base</b> points de base".
		"<br /><img src=\"smilies/h_guard.gif\" /> <b>$gardiens</b> points gr&acirc;ce aux h&eacute;ros Gardiens".
		"<br /><img src=\"smilies/h_home.gif\" /> <b>$maisons</b> points gr&acirc;ce aux habitations".
		"<br /><img src=\"smilies/h_city_up.gif\" /> <b>$batiments</b> points gr&acirc;ce aux chantiers".
		"<br /><img src=\"smilies/item_plate.gif\" /> <b>$objfix</b> points gr&acirc;ce aux objets de d&eacute;fense ".
		"<i>($objets objets x $fixations fixations)</i>".
		"<br /><br /><b>TOTAL D&Eacute;FENSE : $total points</b>".
		"$estimatq".
		"</div>";

// Estimation survie
$defp = array(0,1,2,3,4,5,6,7,8,9,10,15,20);
$output_attaque .= "<div class=\"command_tab small\"><u>Chances de survie</u><table width=\"100%\"><tr><td>Pts de d&eacute;f. perso</td>";
foreach($defp as $dp)
	$output_attaque .= "<td style=\"text-align:center;\">$dp</td>";
$output_attaque .= "</tr><tr><td>Chances de survie<br />(minima/maxima)</td>";
foreach($defp as $dp)
{
	$sr = $h->getSurvie($dp);
	$min = $sr['min'];
	$max = $sr['max'];
	$output_attaque .= "<td style=\"text-align:center;\">$min%<br />$max%</td>";
}


$output_attaque .= "</tr></table></div>";
##########################################################################################
// Chantiers
$txt = "<u>Liste des chantiers construits</u> : <br />";
$msg = " ";
$cities = $doc->getElementsByTagName('city');
foreach($cities as $city)
{	
	$chantiers = array();
	$bats = $city->getElementsByTagName('building');
	// Parcourir les bâtiments
	foreach($bats as $bat)
	{
		$nom = $bat->getAttribute('name');
		$chid = $bat->getAttribute('id');
		// Chantier parent
		if($bat->getAttribute('parent'))
			$par = $bat->getAttribute('parent');
		else
			$par = 0;
		// Chantier temporaire ?
		$tmp = $bat->getAttribute('temporary');
			if($tmp)
				$tmp = "&nbsp;<span class=\"d3\">chantier temporaire</span>";
			else
				$tmp = "&nbsp;";
		// Icone du chantier
		$img = $bat->getAttribute('img');
		// Remplissage du tableau
		$chantiers[$chid]['nom'] = $nom;
		$chantiers[$chid]['tmp'] = $tmp;
		$chantiers[$chid]['img'] = $img;
		$chantiers[$chid]['par'] = $par;
	}
	
	function chrecur($chs, $cid)
	{
		$out = "<ul class=\"chantiers_ul\">";
		foreach($chs as $id=>$ch)
		{
			if($ch['par'] == $cid)
			{
				$out .= "<li class=\"chantiers_li\">";
				$out .= "<img src=\"http://data.hordes.fr/gfx/icons/".$ch['img'].".gif\" />&nbsp;";
				$out .= "<i>&laquo;&nbsp;".$ch['nom']."&nbsp;&raquo;</i> ".$ch['tmp'];
				$out .= chrecur($chs,$id);
				$out .= "</li>";
			}
		}
		$out .= "</ul>";
		return $out;
	}
	
	$msg .= chrecur($chantiers,0);
}
$msg = entitiescharset($msg);
$output_chantiers .= "<div class=\"command_tab\">$txt $msg</div>";
##########################################################################################
// Evolutions		
$txt = "<u>Liste des &eacute;volutions</u> :<br />";
$msg = " ";
$evos = $doc->getElementsByTagName('upgrades');
foreach($evos as $evo)
{
	$total = $evo->getAttribute('total');
	$ups = $evo->getElementsByTagName('up');
	$msg .= "<table width=\"90%\" cellspacing=0>";
	foreach($ups as $up)
	{
		$nom = $up->getAttribute('name');
		$lvl = $up->getAttribute('level');
		
		$msg .= "<tr><td width=\"40%\" style=\"text-align:right;\">$nom</td>";
		
		for($i = 1 ; $i < 6 ; $i++)
		{
			if($lvl > $i and $i == 1)
				$msg .= "<td class=\"frise_disponible frise_start\" width=\"12%\">&nbsp;</td>";
			elseif($lvl == $i)
				$msg .= "<td class=\"frise_disponible frise_stop\" width=\"12%\">$lvl/5</td>";
			elseif($lvl > $i)
				$msg .= "<td class=\"frise_disponible\" width=\"12%\">&nbsp;</td>";
			else
				$msg .= "<td width=\"12%\">&nbsp;</td>";
		}
		
		$msg .= "</tr><tr><td colspan=\"6\"></td></tr>";
		
		
		//$msg .= "- <i>&laquo; $nom &raquo;</i> niveau $lvl/5<br />";
	}
	$msg .= "</table>";
}
$output_chantiers .= "<div class=\"command_tab\">$txt $msg</div>";
// Bug : les évolutions sont affichées en double (???)
##########################################################################################
// Cases explorées aujourd'hui
$txt = "<u>Carte du d&eacute;sert</u> : <br />";
$msg = " ";
$msg .= "<br />L&eacute;gende : <span class=\"villemap\">Ville</span> | <span class=\"nvt\">Case visit&eacute;e <u>aujourd'hui</u></span> | <span class=\"bat\">B&acirc;timent</span> | <span class=\"unknown\">Case inexplor&eacute;e</span><br /> Danger : <span class=\"d1\">faible</span> - <span class=\"d2\">moyen</span> - <span class=\"d3\">fort</span> - inconnu | <span class=\"epuisee\">&eacute;puis&eacute;e</span> - <span class=\"mhelp\">citoyen bloqu&eacute;</span>";
$msg = cleancharset($msg);
$output_desert .= "<div class=\"command_tab\">$txt";

include_once('view_map.php');

$output_desert .= "$msg</div>";
// GRAPHIQUES
$output_desert .= "<div class=\"command_tab\" style=\"text-align:center;\"><img src=\"graphiques/gmap.php\" alt=\"Graphique Map\"/></div>";
##########################################################################################
##########################################################################################
// Liste des bâtiments de la carte
$txt = "<u>Liste des b&acirc;timents d&eacute;couverts</u> : ";
$zones = $doc->getElementsByTagName('zone');
$msg = " ";
foreach($zones as $zone)
{	
	$x = $zone->getAttribute('x');
	$y = $zone->getAttribute('y');
	$bats = $zone->getElementsByTagName('building');
	foreach($bats as $bat)
	{
		$nom = $bat->getAttribute('name');
		$dig = $bat->getAttribute('dig');
			if(!$dig) $dig = "b&acirc;timent d&eacute;blay&eacute;";
			else $dig = "$dig PA &agrave; d&eacute;blayer";
		$msg .= "<br />[$x,$y] <i>&laquo; $nom &raquo;</i> ($dig)";
	}
}
$output_desert .= "<div class=\"command_tab\">$txt $msg</div>";
##########################################################################################
// GRAPHIQUES
$output_citoyens .= "<div class=\"command_tab\" style=\"text-align:center;\"><img src=\"graphiques/gcitoyens.php\" alt=\"Graphique Citoyens\"/></div>";
$output_citoyens .= "<div class=\"command_tab\" style=\"text-align:center;\"><img src=\"graphiques/gprofessions.php\" alt=\"Graphique Citoyens\"/></div>";
##########################################################################################

// AFFICHAGE
$error = "";
$errs = $doc->getElementsByTagName('error');
foreach($errs as $err)
	$error = $err->getAttribute('code');
		
if(!empty($error) and $error == "horde_attacking")
	echo "Les donn&eacute;es de la ville ne sont pas disponibles pendant l'attaque de la Horde";
elseif(!empty($error) and $error == "not_in_game")
	echo "Vous n'&ecirc;tes citoyen d'aucune ville. Aucune donn&eacute;e &agrave; afficher.";
else
	{
		// TAB 1 : misc + attaque
		echo "<div id=\"innertab_xml_1\" class=\"intab\" style=\"width:100%\">";
		echo $output_misc;
		echo $output_attaque;
		echo "</div>";
		// TAB 2 : banque
		echo "<div id=\"innertab_xml_2\" class=\"intab\" style=\"width:100%\">";
		echo $output_banque;
		echo $output_banque_graph;
		echo "</div>";
		// TAB 3 : chantiers
		echo "<div id=\"innertab_xml_3\" class=\"intab\" style=\"width:100%\">";
		echo $output_chantiers;
		echo "</div>";
		// TAB 3 : désert
		echo "<div id=\"innertab_xml_4\" class=\"intab\" style=\"width:100%\">";
		echo $output_desert;
		echo "</div>";
		// TAB 5 : citoyens
		echo "<div id=\"innertab_xml_5\" class=\"intab\" style=\"width:100%\">";
		include_once('form_parias.php');
		include_once('view_parias.php');
		include_once('view_rencontres.php');
		echo $output_citoyens;
		echo "</div>";
		echo "</div>";
	}

// Charger le javascript permettant de mettre en place les TABS
// Il faut le faire ici, car si on le fait dans le fichier fonctions.js, la méthode dom:loaded
// de prototype est appelée après le chargement de index.php mais AVANT le chargement de tab_xml.php
// Conséquence : le javascript ne trouve pas le div sur lequel il est censé agir (qui est défini sur cette page)
echo "<script>innertabs_xml = new Control.Tabs('innertabs_xml');</script>";
?>