<?php
session_start();
include_once("classes/HordesXML.php");
include_once("classes/Objets.php");

$cmd = $commande = $message;
$cmd = explode(" ",$message);     
$cmd = substr($cmd[0], 1);

$pm = array();

// Traitement de la commande
switch($cmd)
{
	// Version de la shoutbox
	case 'version':
		include_once('version.php');
		$output = "Version de la ShoutBox : $SBVERSION ($SBDATE)";
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
	
	// objets
	case 'objet':
		// récupération du nom de l'objet
		$commande = explode(" ", $commande);
		if(empty($commande[1]) or trim($commande[1]) == "") break;
		$obj = "";
		for($i = 1 ; $i < count($commande) ; $i++)
			$obj .= stripslashes($commande[$i])." ";
		$input_objet = trim($obj);
		// recherche des données de l'objet
		include("views/recherche/view_search_objets.php");
		$output = $output_objet;
		// Envoi du message
		$user = $_COOKIE['user'];
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
	
	// chantiers
	case 'chantier':
		// récupération du nom du chantier
		$commande = explode(" ", $commande);
		if(empty($commande[1]) or trim($commande[1]) == "") break;
		$ch = "";
		for($i = 1 ; $i < count($commande) ; $i++)
			$ch .= stripslashes($commande[$i])." ";
		$input_chantier = trim($ch);
		// recherche des données de l'objet
		include("views/recherche/view_search_chantiers.php");
		$output = $output_chantier;
		// Envoi du message
		$user = $_COOKIE['user'];
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
	
	// Utilisateurs
	case 'register':
		$user = $_COOKIE['user'];
		$commande = explode(" ", $commande);
		if(empty($commande[1]) or trim($commande[1]) == "") break;
		$pwd = trim($commande[1]);
		$encpwd = sha1($pwd);
		
		$sql = "SELECT * FROM users_registered WHERE user ='$user'";
		$count = num_rows($sql);
		if($count > 0)
		{
			$output = "L'utilisateur existe d&eacute;j&agrave;. Veuillez vous identifier avec le mot de passe &agrave; l'aide de la commande /auth. Si vous n'&ecirc;tes pas <i>$user</i>, veuillez vous d&eacute;connecter et vous reconnecter avec un autre pseudonyme.";
			$pm['to'] = $user;
			$user = "SYSTEM";
		}
		else
		{
			// IP de l'utilisateur
			if (isset($_SERVER['HTTP_X_FORWARD_FOR']) and !empty($_SERVER['HTTP_X_FORWARD_FOR']))
				$ip = $_SERVER['HTTP_X_FORWARD_FOR'];
			else
				$ip = $_SERVER['REMOTE_ADDR'];
			// Insertion
			$sql = "INSERT INTO users_registered (id,user,pwd,ip) VALUES ('','$user','$encpwd','$ip')";
			query($sql);
			
			// Envoi du message
			$output = "Utilisateur <i>$user</i> enregistr&eacute;.";
			$pm['to'] = $user;
			$user = "SYSTEM";
		}
	break;
	
	case 'auth':
		$user = $_COOKIE['user'];
		$commande = explode(" ", $commande);
		if(empty($commande[1]) or trim($commande[1]) == "") break;
		$pwd = $commande[1];
		$encpwd = sha1($pwd);
				
		$sql = "SELECT * FROM users_registered WHERE user ='$user'";
		$count = num_rows($sql);
		if($count > 0)
		{
			$req = query($sql);
			$data = mysql_fetch_assoc($req);
			/*
			if($_SESSION['auth'] == 1)
			{
				$output = "Vous &ecirc;tes d&eacute;j&agrave; identifi&eacute;(e), <i>$user</i>.";
				$pm['to'] = $user;
				$user = "SYSTEM";
			}
			
			elseif($data['pwd'] == $encpwd)
			*/
			if($data['pwd'] == $encpwd)
			{
				// Authentifier
				$_SESSION['auth'] = 1;
				// Insérer l'IP
				if (isset($_SERVER['HTTP_X_FORWARD_FOR']) and !empty($_SERVER['HTTP_X_FORWARD_FOR']))
					$ip = $_SERVER['HTTP_X_FORWARD_FOR'];
				else
					$ip = $_SERVER['REMOTE_ADDR'];
				$sql = "UPDATE users_registered SET ip='$ip' WHERE user='$user'";
				query($sql);
				// MP non lus
				$sql = "SELECT * FROM messages WHERE pm='$user' AND pm_unread = 'yes' AND user != 'SYSTEM'";
				$mpcount = num_rows($sql);
				if($mpcount == 1) $newmp = "<br />Vous avez <b>$mpcount</b> nouveau message priv&eacute;.";
				elseif($mpcount > 1) $newmp = "<br />Vous avez <b>$mpcount</b> nouveaux messages priv&eacute;s.";
				else $newmp = "";
				// Avertir
				$output = "Vous &ecirc;tes &agrave; pr&eacute;sent identifi&eacute;, <i>$user</i>.$newmp";
				$pm['to'] = $user;
				$user = "SYSTEM";				
			}
			else
			{
				$output = "Mot de passe incorrect pour l'utilisateur <i>$user</i>.";
				$pm['to'] = $user;
				$user = "SYSTEM";
			}
		}
		else
		{
			$output = "L'utilisateur <i>$user</i> n'existe pas. Vous pouvez l'enregistrez &agrave; l'aide de la commande <b>/register mot_de_passe</b>.";
			$pm['to'] = $user;
			$user = "SYSTEM";
		}
	break;
	
	// Citations
	case 'quote':
		// Ajouter
		$commande = explode(" ", $commande);
		if($commande[1] == 'new')
		{
			$quote = "";
			for($i = 2 ; $i < count($commande) ; $i++)
				$quote .= $commande[$i]." ";

			include_once("classes/Quote.php");
			$qu = new Quote(); 
			$qu->addQuote($quote);
			
			$output = "Citation ajout&eacute;e";
			$pm['to'] = $user;
			$user = "SYSTEM";
		}
		else
		{				
			include_once("classes/Quote.php");
			$quote = new Quote(); 
			$quote->getRandom();
			
			$output = "<i>&laquo; ".$quote->getQuote()." &raquo;</i>&nbsp;<br /><span class=\"small\">[Citation ajout&eacute;e par ".$quote->getUser()." le ".$quote->getDate()."]</span>";
		}
	break;
	
	// Twitter
	case 'tweet':		
		$commande = explode(" ", $commande);
		$tweet = "";
		for($i = 1 ; $i < count($commande) ; $i++)
			$tweet .= " ".$commande[$i];
		$tweet = trim($tweet);
		
		if(isset($_COOKIE['user']))
			$user = $_COOKIE['user'];
		else
			$user = "?";

		$sendtweet = $user.">".$tweet;
			
		include_once('twitter.php');
		twitter_write(stripslashes($sendtweet));
		
		$output = "<b>[Twitter] $user gazouille</b> <i>&laquo; $tweet &raquo;</i>";
	break;
	
	// Sondages
	case 'poll':
		$user = $_COOKIE['user'];
		$commande = explode(" ", $commande);
		// Mauvaise syntaxe de la commande : afficher l'aide
		if(empty($commande[1]))
		{
			$output = "<b>Aide &agrave; la cr&eacute;ation de sondages</b><br />1) Cr&eacute;ez un nouveau sondage avec la commande <span class=\"comm\">/poll new &lt;expiration&gt; &lt;intitul&eacute; du sondage&gt;</span><br />2) Ajoutez autant d'options que n&eacute;cessaire avec la commande <span class=\"comm\">/poll option &lt;texte de l'option&gt;</span><br /><br /><u>Exemple</u> : Pour poser la question <i>&laquo; Comment allez-vous ? &raquo;</i> pendant 5 jours et permettre aux utilisateurs de r&eacute;pondre <i>&laquo; Bien &raquo;</i> ou <i>&laquo; Mal &raquo;</i>, tapez les 3 commandes suivantes :<br /><i>/poll new 5 Comment allez-vous ?<br />/poll option Bien<br />/poll option Mal</span>";
			$pm['to'] = $user;
			$user = "SYSTEM";
		}
		else
		{
			$pollcmd = $commande[1];
			switch($pollcmd)
			{
				// Nouveau sondage
				case 'new':
					// Mauvaise syntaxe de la commande : afficher l'aide
					if(empty($commande[3]))
					{
						$output = "Pour cr&eacute;er un nouveau sondage, utilisez la commande <span class=\"comm\">/poll new &lt;expiration&gt; &lt;intitul&eacute; du sondage&gt;</span>.<br />L'argument <i>expiration</i> correspond &agrave; la dur&eacute;e du sondage en nombre de jours.<br /><u>Exemple</u> : Pour poser la question <i>&laquo; Comment allez-vous ? &raquo;</i> pendant 5 jours, tapez : <i>/poll new 5 Comment allez-vous ?</i>";
						$pm['to'] = $user;
						$user = "SYSTEM";
					}
					else
					{
						// Expiration du sondage
						$expiration = $commande[2];
						if(!is_numeric($expiration)) { $expiration = 5; $start = 2; } else $start = 3;
						$expiration = date("Y-m-d", mktime(0,0,0,date("m"), date("d")+$expiration, date("Y")));
						// Titre du sondage
						$titre = "";
						for($i = $start ; $i < count($commande) ; $i++)
							$titre .= $commande[$i]." ";
						$titre = addslashes(trim($titre));
						$titre = iconv("UTF-8", "ISO-8859-1", $titre);
						
						// Insertion
						$sql = "INSERT INTO poll_titres VALUES ('','$titre','$expiration')";
						query($sql);
						
						// Affichage
						$titre = iconv("ISO-8859-1", "UTF-8", $titre);
						$output = "Nouveau sondage : <i>&laquo; ".stripslashes($titre)." &raquo;</i> (expiration le ".fdate($expiration).")<br />Ajoutez maintenant des options avec la commande <span class=\"comm\">/poll option &lt;nom de l'option&gt;</span>";
					}
				break;
				// Nouvelle option
				case 'option':
					// Mauvaise syntaxe de la commande : afficher l'aide
					if(empty($commande[2]))
					{
						$output = "Pour ajouter une nouvelle option au dernier sondage cr&eacute;&eacute;, utilisez la commande <span class=\"comm\">/poll option &lt;texte de l'option&gt;</span>.";
						$pm['to'] = $user;
						$user = "SYSTEM";
					}
					else
					{
						// Contenu de l'option
						$option = "";
						for($i = 2 ; $i < count($commande) ; $i++)
							$option .= $commande[$i]." ";
						$option = addslashes(trim($option));
						$option = iconv("UTF-8", "ISO-8859-1", $option);
						
						// Obtenir l'id du dernier sondage ajouté
						$sql = "SELECT * FROM poll_titres ORDER BY id DESC LIMIT 0,1";
						$req = query($sql);
						$data = mysql_fetch_assoc($req);
						$sondage_id = $data['id'];
						$sondage_titre = stripslashes($data['question']);
						
						// Ajouter l'option
						$sql = "INSERT INTO poll_options VALUES ('','$sondage_id','$option')";
						query($sql);
						
						// Affichage
						$option = iconv("ISO-8859-1", "UTF-8", $option);
						$sondage_titre = iconv("ISO-8859-1", "UTF-8", $sondage_titre);
						$output = "Option <b><i>&laquo; ".stripslashes($option)." &raquo;</i></b> ajout&eacute;e au sondage <i>&laquo; $sondage_titre &raquo;</i>";
					}
				break;
			}
		}
	break;
	
	// Dés
	case 'roll':
		$user = $_COOKIE['user'];
		$commande = explode(" ", $commande);
		// Par défaut ou nombre de face sélectionné ?
		if(!empty($commande[1]) and is_numeric($commande[1]))
			$de = $commande[1];
		else
			$de = 1;
		// Contrôle du nombre de faces
		if($de < 1) $de = 1;
		elseif($de > 100) $de = 100;
		// Nombre aléatoire
		$alea = 0;
		// Calcul
		for($i = 0 ; $i < $de ; $i++)
			$alea = $alea + ($de * round(rand(1,6)) );
		// Affichage
		$output = "<b>$user</b> a lanc&eacute; <b>$de</b> <img src=\"smilies/item_dice.gif\" /> obtenant un score de <b>$alea</b>";
	break;
	
	// Pierre-feuille-ciseau
	case 'pfc':
		$user = $_COOKIE['user'];
		$alea = round(rand(1,3));
		switch($alea)
		{
			case 1: $alea = "Pierre"; break;
			case 2: $alea = "Feuille"; break;
			case 3: $alea = "Ciseaux"; break;
		}
		// Affichage
		$output = "<b>$user</b> <img src=\"smilies/small_wrestle.gif\" /> | <i><b>$alea</b></i>";
	break;
	
	// Pile-face
	case 'pf':
		$user = $_COOKIE['user'];
		$alea = round(rand(1,2));
		switch($alea)
		{
			case 1: $alea = "Pile"; break;
			case 2: $alea = "Face"; break;
		}
		// Affichage
		$output = "<b>$user</b> lance une pi&egrave;ce <img src=\"smilies/small_coin.gif\" /> qui retombe c&ocirc;t&eacute; <i><b>$alea</b></i>";
	break;
	
	// Butin
	case 'butin':
		$cmd = explode(" ", $commande);
		$user = $_COOKIE['user'];
		if(isset($cmd[1]) and is_numeric($cmd[1]) and isset($cmd[2]))
		{
			// Récupérer l'objet
			$objet = "";
			for($i = 2 ; $i < count($cmd) ; $i++)
				$objet .= $cmd[$i]." ";
			$objet = addslashes(trim($objet));
			$objet = iconv("UTF-8", "ISO-8859-1",$objet);
			
			// Doit-on l'attribuer à un autre utilisateur ?
			$obj = explode("@",$objet);
			if(isset($obj[1]))
			{
				$objet = trim(ltrim($obj[0]));
				$user = trim(ltrim($obj[1]));
			}
			
			// Récupérer le nombre d'objets
			$nombre = $cmd[1];
			
			// Ajout et mise à jour d'objets
			if($nombre > 0 or $nombre < 0)
			{				
				// Voir si l'objet existe déjà dans la bdd
				$sql = "SELECT * FROM butin_found WHERE objet='$objet'";
				$count = num_rows($sql);
			
				if($count > 0) // l'objet existe : on le met à jour avec la nouvelle quantité
				{
					// Utilisateurs possédant l'objet
					$req = query($sql);
					$data = mysql_fetch_assoc($req);
					$userslist = $data['users'];
					$oldnbr = $data['nombre'];
					
					$users = explode(",",$userslist);
					
					
					// Récupérer le nombre d'objets
					$nbr = $nombre;
						
					// On ajoute un butin : on ajoute l'utilisateur
					if(substr($nbr,0,1) == "+")
					{
						for($i = 0 ; $i < substr($nbr,1) ; $i++)
							$users[] = $user;
					}
					// On supprime un butin : on supprime l'utilisateur
					elseif(substr($nbr,0,1) == "-")
					{
						for($i = 0 ; $i < substr($nbr,1) ; $i++)
							for($j = 0 ; $j < count($users) ; $j++)
								if($users[$j] == $user)
								{
									$users[$j] = "";
									break;
								}
					}
					// On n'a utilisé ni + ni -
					elseif(is_numeric($nbr))
					{
						
						for($i = $oldnbr ; $i < $nbr ; $i++)
							$users[] = $user;
					}
					// Réécrire la liste des utilisateurs
					$userslist = "";
					foreach($users as $u)
						$userslist .= $u.",";
					$userslist = trim($userslist,",");
					
					// Calcul du nombre d'objets (positif/négatif)
					if(substr($nbr,0,1) == "+" or substr($nbr,0,1) == "-")
						$nbr = "nombre".substr($nbr,0,1)."'".substr($nbr,1)."'";
					
					// Requête
					$sql = "UPDATE butin_found SET nombre=$nbr, users='$userslist' WHERE objet='$objet'";
					query($sql);
					$output = "Butin mis &agrave; jour : <i>$nombre $objet @ $user</i>";
				}
				else // l'objet n'existe pas, on le crée
				{
					// Utilisateur
					$userslist = "";
					for($i = 0 ; $i < $nombre ; $i++)
						$userslist .= $user.",";
					$userslist = trim($userslist,",");
						
					// Insertion
					$sql = "INSERT INTO butin_found VALUES('$objet','$nombre','$userslist')";
					query($sql);
					$output = "Butin ajout&eacute; : <i>$nombre $objet @ $user</i>";
				}
			}
			// Suppression d'un objet existant
			else
			{
				$sql = "DELETE FROM butin_found WHERE objet='$objet'";
				query($sql);
				$output = "Butin supprim&eacute; <i>($objet)</i>";
			}
		}
		elseif(isset($cmd[1]) and $cmd[1] == "reset")
		{
			$sql = "DELETE FROM butin_found"; query($sql);
			$output = "Butin remis &agrave; z&eacute;ro";
		}
		else
		{
			$output = "<u>Syntaxe de la commande <i>butin</i></u> : <span class=\"comm\">/butin &lt;[+|-]nombre&gt; &lt;objet&gt;</span><br />1) Si l'objet existe d&eacute;j&agrave; dans la liste, le nombre est mis &agrave; jour. Dans le cas contraire, l'objet est cr&eacute;&eacute;.<br />2) Si vous utilisez le symbole + (plus), le nombre est <b>ajout&eacute;</b> au nombre d'objets existant. Si vous utilisez le symbole - (moins), il lui est <b>retranch&eacute;</b>. Si vous n'utilisez aucun symbole, il lui est <b>substitu&eacute;</b>.<br />3) Pour supprimer un objet de la liste, d&eacute;finissez son nombre &agrave; 0 (z&eacute;ro).<br />4) Pour supprimer en une seule fois tous les objets du butin, utilisez la commande <i>/butin reset</i>.";
			// Avertir
			$pm['to'] = $user;
			$user = "SYSTEM";	
		}
		$output = iconv("ISO-8859-1", "UTF-8",$output); // ce sera par la suite reconverti pour insertion dans la bdd
	break;
	
	// Butin - besoin
	case 'besoin':
		$cmd = explode(" ", $commande);
		if(isset($cmd[1]) and is_numeric($cmd[1]) and isset($cmd[2]))
		{
			// Récupérer l'objet
			$objet = "";
			for($i = 2 ; $i < count($cmd) ; $i++)
				$objet .= $cmd[$i]." ";
			$objet = addslashes(trim($objet));
			//$objet = iconv("UTF-8", "ISO-8859-1",$objet);
			
			// Récupérer le nombre d'objets
			$nombre = $cmd[1];
			
			// Ajout et mise à jour d'objets
			if($nombre > 0 or $nombre < 0)
			{				
				// Voir si l'objet existe déjà dans la bdd
				$sql = "SELECT * FROM butin_wanted WHERE objet='$objet'";
				$count = num_rows($sql);
			
				if($count > 0) // l'objet existe : on le met à jour avec la nouvelle quantité
				{
					// Récupérer le nombre d'objets
					$nbr = $nombre;
					if(substr($nbr,0,1) == "+" or substr($nbr,0,1) == "-")
						$nbr = "nombre".substr($nbr,0,1)."'".substr($nbr,1)."'";
					// Requête
					$sql = "UPDATE butin_wanted SET nombre=$nbr WHERE objet='$objet'";
					query($sql);
					$output = "Besoin mis &agrave; jour <i>($nombre $objet)</i>";
				}
				else // l'objet n'existe pas, on le crée
				{
					$sql = "INSERT INTO butin_wanted VALUES('$objet','$nombre')";
					query($sql);
					$output = "Besoin ajout&eacute; <i>($nombre $objet)</i>";
				}
			}
			// Suppression d'un objet existant
			else
			{
				$sql = "DELETE FROM butin_wanted WHERE objet='$objet'";
				query($sql);
				$output = "Besoin supprim&eacute; <i>($objet)</i>";
			}
		}
		elseif(isset($cmd[1]) and $cmd[1] == "reset")
		{
			$sql = "DELETE FROM butin_wanted"; query($sql);
			$output = "Besoin remis &agrave; z&eacute;ro";
		}
		else
		{
			$output = "<u>Syntaxe de la commande <i>besoin</i></u> : <span class=\"comm\">/besoin &lt;[+|-]nombre&gt; &lt;objet&gt;</span><br />1) Si l'objet existe d&eacute;j&agrave; dans la liste, le nombre est mis &agrave; jour. Dans le cas contraire, l'objet est cr&eacute;&eacute;.<br />2) Si vous utilisez le symbole + (plus), le nombre est <b>ajout&eacute;</b> au nombre d'objets existant. Si vous utilisez le symbole - (moins), il lui est <b>retranch&eacute;</b>. Si vous n'utilisez aucun symbole, il lui est <b>substitu&eacute;</b>.<br />3) Pour supprimer un objet de la liste, d&eacute;finissez son nombre &agrave; 0 (z&eacute;ro).<br />4) Pour supprimer en une seule fois tous les objets du butin, utilisez la commande <i>/besoin reset</i>.";
			// Avertir
			$pm['to'] = $user;
			$user = "SYSTEM";	
		}
		$output = iconv("ISO-8859-1", "UTF-8",$output); // ce sera par la suite reconverti pour insertion dans la bdd
	break;
	
	// Carte
	case 'carte':
		$user = $_COOKIE['user'];
		// Aléa
		$couleurs = array("Coeur", "Carreau", "Pique", "Tr&egrave;fle");
		$couleur = round(rand(0,3));
		$cartes = array("l'As", "le 2", "le 3", "le 4", "le 5", "le 6", "le 7", "le 8", "le 9", "le 10", "le Valet", "la Reine", "le Roi");
		$carte = round(rand(0,12));
		$joker = round(rand(0,100));
		// Analyse
		if($joker > 95)
			$alea = "le JOKER!";
		else
			$alea = $cartes[$carte]." de ".$couleurs[$couleur];
		// Affichage
		$output = "<b>$user</b> tire <img src=\"smilies/item_cards.gif\" /> <i><b>$alea</b></i>";
	break;
	
	// AFK
	case 'afk':
		$user = $_COOKIE['user'];
		$sql = "SELECT * FROM users WHERE user='$user'";
		$req = query($sql);
		$data = mysql_fetch_assoc($req);
		$afk = $data['afk'];
		if(!$afk or empty($afk))
		{
			$sql = "UPDATE users SET afk='1' WHERE user='$user'";
			$output = "Vous &ecirc;tes AFK";
		}
		elseif(!isset($_COOKIE['prefs_afk']) or $_COOKIE['prefs_afk'] != "Oui")
		{
			$sql = "UPDATE users SET afk='0' WHERE user='$user'";
			$output = "Vous n'&ecirc;tes plus AFK";
		}
		query($sql);
		// Avertir
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
	
	// Notes
	case 'note':
		// Récupérer le contenu
		$commande = explode(" ", $commande);
		$note = ""; $user = $_COOKIE['user'];
		for($i = 1; $i < count($commande) ; $i++)
			$note .= $commande[$i]." ";
		// Charset et autres
		$note = addslashes(trim(iconv("UTF-8","ISO-8859-1",$note)));
		// Date
		$cdate = date("Y-m-d H:i:s");
		// Insérer
		$sql = "INSERT INTO notes VALUES ('','$note','$user','$cdate')";
		query($sql);
		// Avertir
		$output = "Note ajout&eacute;e";
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
	
	// Messagerie privée
	case 'w':
		$commande = explode(" ", $commande);
		// Destinataire(s)
		$pm['to'] = explode(",",$commande[1]);
		// Message
		$pm['msg'] = "";
		$pm['unread'] = 'yes';
		for($i = 2; $i < count($commande) ; $i++)
			$pm['msg'] .= $commande[$i]." ";
		// Envoi
		$output = $pm['msg'];
		$command = 0; // traiter ceci comme un message normal et non comme une commande
	break;
	
	// Répondre automatiquement au dernier MP
	case 'r':
		$user = $_COOKIE['user'];
		$sql = "SELECT * FROM messages WHERE pm = '$user' AND user != 'SYSTEM' ORDER BY id DESC LIMIT 0,1";
		$req = query($sql);
		$data = mysql_fetch_assoc($req);
		
		$pm['to'][0] = $data['user'];
		$pm['unread'] = 'yes';
		
		$commande = explode(" ", $commande);
		$pm['msg'] = "";
		for($i = 1; $i < count($commande) ; $i++)
			$pm['msg'] .= $commande[$i]." ";
			
		$output = $pm['msg'];
		$command = 0;
	break;
	
	// Commandes relatives au timer
	case 'timer':
		$commande = explode(" ",$commande);
		$subcommande = $commande[1];
		switch($subcommande)
		{
			// Créer un nouveau timer
			case 'new':
				if(!empty($commande[2])) $tname = addslashes($commande[2]); else $tname = "default";
				if(!empty($commande[3])) 
					for($i = 3; $i < count($commande) ; $i++)
						$label .= addslashes($commande[$i])." "; 
				else $label = "";
				$label = trim($label);
				$sql = "INSERT INTO timer VALUES ('0','$tname','$label','0')";
				query($sql);
				if(!empty($label)) $label = "<i>($label)</i>";
				$output = "Compteur <b>$tname</b> $label cr&eacute;&eacute;.";
			break;
			// Définir pour une heure précise
			case 'to':
				// A-t-on spécifié le nom d'un timer ?
				if(count($commande) == 4)
					{ $tname = addslashes($commande[2]); $myh = explode(":",$commande[3]); }
				else
					{ $tname = "default"; $myh = explode(":",$commande[2]); }
				// Récupérer le timestamp
				if(!empty($myh[0])) $h = $myh[0]; else $h = date("H");
				if(!empty($myh[1])) $m = $myh[1]; else $m = date("i");
				if(!empty($myh[2])) $s = $myh[2]; else $s = date("s");
				$ts = mktime($h,$m,$s)-3600; // mktime() a 1h d'avance...
				//$ts = mktime($h,$m,$s); // mktime() est à la bonne heure locale
				// Insérer dans la bdd
				$sql = "UPDATE timer SET timer='$ts', actif='1' WHERE tname='$tname'";
				query($sql);
				$output = "Compteur <b>$tname</b> d&eacute;fini pour durer jusqu'&agrave; <b>$h heures $m minutes</b>";
			break;
			// Définir pour une durée (p. ex. dans 20 minutes)
			case 'set':
				// A-t-on spécifié le nom d'un timer ?
				if(count($commande) == 4)
				{ $tname = addslashes($commande[2]); $myh = explode(":",$commande[3]); }
				else
				{ $tname = "default"; $myh = explode(":",$commande[2]); }
				// Récupérer le timestamp
				if(!empty($myh[0])) $h = $myh[0]; else $h = 0;
				if(!empty($myh[1])) $m = $myh[1]; else $m = 0;
				if(!empty($myh[2])) $s = $myh[2]; else $s = 0;
				$to = time()+$h*3600+$m*60+$s-3600; // time() a 1h d'avance...
				//$to = time()+$h*3600+$m*60+$s; // time() est à la bonne heure locale
				// Insérer dans la bdd
				$sql = "UPDATE timer SET timer='$to', actif='1' WHERE tname='$tname'";
				query($sql);
				$output = "Compteur <b>$tname</b> d&eacute;fini pour une dur&eacute;e de <b>$h heures $m minutes</b>"."&nbsp;<i>(jusqu'&agrave;".date("H\hi",$to+3600).")</i>";
			break;
			// Définit le label d'un timer
			case 'label':
				if(!empty($commande[2])) $tname = addslashes($commande[2]); else $tname = "default";
				if(!empty($commande[3])) 
					for($i = 3; $i < count($commande) ; $i++)
						$label .= addslashes($commande[$i])." "; 
				else $label = "";
				$label = trim($label);
				$sql = "UPDATE timer SET label='$label' WHERE tname='$tname'";
				query($sql);
				if(!empty($label)) $label = "<i>&laquo; $label &raquo;</i>";
				$output = "Compteur <b>$tname</b> d&eacute;fini avec l'&eacute;tiquette $label";
			break;
			// Supprimer le timer
			case 'del':
				// A-t-on spécifié le nom d'un timer ?
				if(!empty($commande[2]))
					$tname = addslashes($commande[2]);
				else
					$tname = "default";
				// Supprimer les timers
				$sql = "DELETE FROM timer WHERE tname='$tname'";
				query($sql);
				$output = "Compteur(s) <b>$tname</b> supprim&eacute;(s)";
			break;
			// Aide (on va en avoir besoin !)
			case 'help':
				$output = 	 "<b>Aide sur les compteurs</b>"
							."<br /><span class=\"comm\">/timer new [nom] [label]</span> : cr&eacute;e un nouveau compteur avec le nom (facultatif) et l'&eacute;tiquette (facultatif) sp&eacute;cifi&eacute;s."
							."<br /><span class=\"comm\">/timer set [nom] h:m:s</span> : d&eacute;finit une dur&eacute;e, p. ex. 0:30 pour 30 minutes"
							."<br /><span class=\"comm\">/timer to [nom] h:m:s</span> : d&eacute;finit une heure, p. ex. 14:30 pour 14h30"
							."<br /><span class=\"comm\">/timer label [nom] [label]</span> : d&eacute;finit l'&eacute;tiquette d'un compteur"
							."<br /><span class=\"comm\">/timer del [nom]</span> : supprime les compteurs portant le nom indiqu&eacute; ou tous les compteurs sans nom";
				// Avertir
				$pm['to'] = $user;
				$user = "SYSTEM";
			break;
		}
	break;
	
	// Affiche la position du joueur sur la carte
	// Syntaxe : /pos <pseudo>
	case 'pos':			
		// Nom du joueur
		$commande = explode(" ",$commande);
		$nom = $commande[1];
		// Récupération de la position
		$doc = getxml($key);
		$citizens = $doc->getElementsByTagName('citizen');
		foreach($citizens as $citizen)
			if($citizen->getAttribute('name') == $nom)
			{
				$x = $citizen->getAttribute('x');
				$y = $citizen->getAttribute('y');
				$out = $citizen->getAttribute('out');
				$ok = true;
				break;
			}
			else $ok = false;
		if($ok)
		{
			if(!$out)
				$msg = "Le citoyen <b>$nom</b> est en ville.";
			else
				$msg = "Le citoyen <b>$nom</b> est sur la case <b>[$x,$y]</b>";
		}
		else
				$msg = "Aucun citoyen de ce nom : <b>$nom</b>.";
		
		// Avertir
		$pm['to'] = $user;
		$user = "SYSTEM";
		$output = $msg;
	break;
	
	// Survie 
	case 'survie':
		// Def perso
		$commande = explode(" ",$commande);
		$dp = $commande[1];
		if(!is_numeric($dp)) 
		{
			$output = "Vous devez indiquer votre défense personnelle : ";
			break;
		}
		
		// Calcul
		$h = new HordesXML();
		$sr = $h->getSurvie($dp);
		$min = $sr['min'];
		$max = $sr['max'];
		
		$atmin = $h->getAttaque('min');
		$atmax = $h->getAttaque('max');
		
		$output = "Avec <b>$dp</b> points de défense personnelle, vous avez entre <b>$min%</b> et <b>$max%</b> de chances de survie ce soir, l'attaque étant estimée entre <b>$atmin</b> et <b>$atmax</b> zombies.";
		$output = iconv("ISO-8859-1","UTF-8",$output);
		// Avertir
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
	
	// Affiche le statut héro d'un joueur
	// Syntaxe : /hero <pseudo>
	case 'hero':
		// Nom du joueur
		$commande = explode(" ",$commande);
		$nom = $commande[1];
		// Récupération du statut
		$doc = getxml($key);
		$citizens = $doc->getElementsByTagName('citizen');
		foreach($citizens as $citizen)
			if($citizen->getAttribute('name') == $nom)
			{
				$hero = $citizen->getAttribute('hero');
				$type = $citizen->getAttribute('job');
					switch($type)
					{
						case 'guardian': $metier = 'Gardien'; break;
						case 'collec': $metier = 'Fouineur'; break;
						case 'eclair': $metier = '&Eacute;claireur'; break;
						default: $metier = false; break;
					}
				$ok = true;
				break;
			}
			else $ok = false;
		if($ok)
		{
			if(!$hero)
				$msg = "Le citoyen <b>$nom</b> a une &acirc;me faible.";
			else
				$msg = "Le citoyen <b>$nom</b> est <b>$metier</b>";
		}
		else
				$msg = "Aucun citoyen de ce nom : <b>$nom</b>.";
		$output = $msg;
		// Avertir
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
	
	// Affiche toutes les infos disponibles sur un joueur
	// Syntaxe : /whois <pseudo>
	case 'whois':
		// Nom du joueur
		$commande = explode(" ",$commande);
		$nom = $commande[1];
		// Récupération du statut
		$doc = getxml($key);
		// Ville
		$cities = $doc->getElementsByTagName('city');
		foreach($cities as $city)
			$ville = $city->getAttribute('city');
		// Citoyens
		$citizens = $doc->getElementsByTagName('citizen');
		foreach($citizens as $citizen)
			if($citizen->getAttribute('name') == $nom)
			{
				$hero = $citizen->getAttribute('hero');
				$type = $citizen->getAttribute('job');
					switch($type)
					{
						case 'guardian': $metier = "<img src=\"smilies/h_guard.gif\" />&nbsp;Gardien"; break;
						case 'collec': $metier = "<img src=\"smilies/h_collec.gif\" />&nbsp;Fouineur"; break;
						case 'eclair': $metier = "<img src=\"smilies/h_ranger.gif\" />&nbsp;Éclaireur"; break;
						case '': $metier = '<img src=\"smilies/r_heroac.gif\" />&nbsp;H&eacute;ro en devenir'; break;
						case 'basic': $metier = '<img src=\"smilies/h_human.gif\" />&nbsp;Citoyen'; break;
						default: $metier = "(?)"; break;
					}
				$x = $citizen->getAttribute('x');
				$y = $citizen->getAttribute('y');
				$out = $citizen->getAttribute('out');
					if(!$out) $pos = "<img src=\"smilies/h_home.gif\" />&nbsp;En Ville"; else $pos = "<img src=\"smilies/r_explor.gif\"/>&nbsp;Dehors en [$x,$y]";
				$dead = $citizen->getAttribute('dead');
					if(!$dead) $dead = "<img src=\"smilies/h_human.gif\" />&nbsp;Vivant"; else $dead = "<img src=\"smilies/h_death.gif\"/>&nbsp;Mort";
				$banni = $citizen->getAttribute('ban');
					if($banni) $banni = "<img src=\"smilies/h_ban.gif\"/>&nbsp;<span style=\"color:#F00;\">Banni</span>"; else $banni = "&nbsp;";
				$ok = true;
				break;
			}
			else $ok = false;
		if($ok)
		{
				$ville_conv = iconv("UTF-8", "ISO-8859-1", $ville);
				$msg = "<b><u><span style=\"font-size:1.2em;\">$nom</span></u></b>";
				$msg .= "<br /><b>Localisation</b> : $pos <i>($ville_conv)</i> | <b>Métier</b> : $metier | <b>État</b> : $dead $banni";
				
			//	$msg = "Le citoyen <b>$nom</b> est un <b>$metier $dead et $banni</b>, actuellement situ&eacute; en <b>$pos</b>.";
		}
		else
		{
				$ville_conv = iconv("UTF-8", "ISO-8859-1", $ville);
				$msg = "<b><u><span style=\"font-size:1.2em;\">$nom</span></u></b>";
				$msg .= "&nbsp;|&nbsp;Aucun citoyen de ce nom dans la ville <i>$ville_conv</i>.";
		}
		
		// Dernière connexion sur la shoutbox
		$db = mysql_connect($dbhost, $dblogin, $dbpassword);
		$sql = "SELECT * FROM users WHERE user='$nom'";
		$req = mysql_query($sql);
		while($data = mysql_fetch_assoc($req))
		{
			$timestamp = $data['lastaction'];
			$msg .= "<br />Dernière visite sur la ShoutBox le <b>".date("d/m/Y",$timestamp)."</b> à <b>".date("H\hi",$timestamp)."</b>";

			$sql2 = "SELECT * FROM users_registered WHERE user='$nom'";
			$req2 = mysql_query($sql2);
			while($data2 = mysql_fetch_assoc($req2))
				$msg .= "&nbsp|&nbsp;<u><b>Utilisateur enregistré</b></u>";
		}
		
		// Vilains
		$sql = "SELECT * FROM parias WHERE nom='$nom'";
		$req = mysql_query($sql);
		$count = mysql_num_rows($req);
		if($count > 0)
		{
			while($data = mysql_fetch_assoc($req))
			{
				switch($data['priorite'])
				{
					case 1: $priorite = "<span class=\"prio1\">P&eacute;nible</span>"; break;
					case 2: $priorite = "<span class=\"prio2\">D&eacute;sagr&eacute;able</span>"; break;
					case 3: $priorite = "<span class=\"prio3\">Vilain</span>"; break;
					case 4: $priorite = "<span class=\"prio4\">Mis&eacute;rable</span>"; break;
					case 5: $priorite = "<span class=\"prio5\">Immonde</span>"; break;
					default: $priorite = "<span class=\"prio3\">Vilain</span>"; break;
				}

				$msg .= "<br />Citoyen <b>$priorite</b> [";
				$msg .= "Signalé par <i>".$data['user']."</i>, le ".fdatetime($data['adate'],"d/m/Y").", dans la ville <i>".$data['ville']."</i>. Il a excédé ".$data['note']." citoyens, pour la raison suivante : <i>&laquo;&nbsp;".$data['raison']."&nbsp;&raquo;</i>]";
			}
		}
				
		// Rencontres
		$db = mysql_connect($dbhost, $dblogin, $dbpassword);
		$sql = "SELECT * FROM xml_rencontres WHERE nom='$nom'";
		$req = mysql_query($sql);
		$count = mysql_num_rows($req);
		
		if($count < 1)
			$msg .= "&nbsp;";
		else
		{
			if($count == 1) $vvs = "ville"; else $vvs = "villes";
			$msg .= "<br />Rencontré dans <b>$count $vvs</b> : ";
			while($data = mysql_fetch_assoc($req))
			{
				if(empty($data['note'])) $note = ""; else $note = "|<i>".$data['note']."</i>";
				$msg .= "&nbsp;<u>".$data['ville']."</u> [".fdate($data['adate'],false)."$note]";
			}
			$msg = trim($msg,",");
		}
		
		// Charset
		$msg = iconv("ISO-8859-1","UTF-8",$msg);
		// Output
		$output = $msg;
		// Avertir
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
	
	// Affiche tous les joueurs correspondant à l'information demandée
	// Syntaxe : /who [mort|vivant|banni|hero|gardien|fouineur|eclaireur|enville|dehors]
	case 'who':
		// Critère de recherche
		$commande = explode(" ",$commande);
		$critere = $commande[1];
		$compteur = 0;
		
		// Récupération du statut
		$msg = "";
		$doc = getxml($key);
		if($critere == "mort")
		{
			$deads = $doc->getElementsByTagName('cadaver');
			foreach($deads as $dead)
			{
				switch($dead->getAttribute('dtype'))
				{
					case 1: $cause = "d&eacute;shydratation"; break;
					case 2: $cause = "?"; break;
					case 3: $cause = "?"; break;
					case 4: $cause = "?"; break;
					case 5: $cause = "attaque dehors"; break;
					case 6: $cause = "attaque en ville"; break;
					default : $cause = "cause inconnue";
				}
				$nom = $dead->getAttribute('name');
				$jour = $dead->getAttribute('day');
				
				$compteur++;
				$msg .= "<u>$nom</u> <i>(jour $jour, $cause)</i>, ";
			}
			$txt .= " ($compteur au total) : ";
		}
		else
		{
			$citizens = $doc->getElementsByTagName('citizen');
			foreach($citizens as $citizen)
			{
				if($critere == "vivant")
					if($citizen->getAttribute('dead') == 0) 
						{ $msg .= $citizen->getAttribute('name').", "; $compteur++; }
				if($critere == "banni")
					if($citizen->getAttribute('ban') == 1) 
						{ $msg .= $citizen->getAttribute('name').", "; $compteur++; }
				if($critere == "hero")
					if($citizen->getAttribute('hero') == 1) 
						{ $msg .= $citizen->getAttribute('name').", "; $compteur++; }
				if($critere == "gardien")
					if($citizen->getAttribute('job') == "guardian") 
						{ $msg .= $citizen->getAttribute('name').", "; $compteur++; }
				if($critere == "fouineur")
					if($citizen->getAttribute('job') == "collec") 
						{ $msg .= $citizen->getAttribute('name').", "; $compteur++; }
				if($critere == "eclaireur")
					if($citizen->getAttribute('job') == "eclair") 
						{ $msg .= $citizen->getAttribute('name').", "; $compteur++; }
				if($critere == "enville")
					if($citizen->getAttribute('out') == 0) 
						{ $msg .= $citizen->getAttribute('name').", "; $compteur++; }
				if($critere == "dehors")
					if($citizen->getAttribute('out') == 1) 
						{ $msg .= $citizen->getAttribute('name').", "; $compteur++; }
			}
		}
		
		switch($critere)
		{
			case 'mort': $txt = "Les joueurs suivants sont <b>morts</b> : "; break;
			case 'vivant': $txt = "Les joueurs suivants sont <b>vivants</b> <i>($compteur)</i> : "; break;
			case 'banni': $txt = "Les joueurs suivants sont <b>bannis</b> <i>($compteur)</i> : "; break;
			case 'hero': $txt = "Les joueurs suivants sont des <b>h&eacute;ros</b> <i>($compteur)</i> : "; break;
			case 'gardien': $txt = "Les joueurs suivants sont des <b>gardiens</b> <i>($compteur)</i> : "; break;
			case 'fouineur': $txt = "Les joueurs suivants sont des <b>fouineurs</b> <i>($compteur)</i> : "; break;
			case 'eclaireur': $txt = "Les joueurs suivants sont des <b>&eacute;claireurs</b> <i>($compteur)</i> : "; break;
			case 'enville': $txt = "Les joueurs suivants sont <b>en ville</b> <i>($compteur)</i> : "; break;
			case 'dehors': $txt = "Les joueurs suivants sont <b>dans le d&eacute;sert</b> <i>($compteur)</i> : "; break;
			default: $txt = "<u>Syntaxe</u> : <i>/who [crit&egrave;re]</i>. Les crit&egrave;res sont les suivants : mort, vivant, banni, hero, gardien, fouineur, eclaireur, enville, dehors.<br />Pour obtenir des informations d&eacute;taill&eacute;es sur un citoyen, utilisez la commande <u>/whois</u>.";
		}
		$output = $txt.trim($msg,", ");
		// Avertir
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
	
	// Affiche les expéditions
	// Syntaxe : /expe
	case 'expe':
		$doc = getxml($key);
		$msg = "";
		$expeditions = $doc->getElementsByTagName('expedition');
		foreach($expeditions as $expedition)
		{
			$msg .= "- &laquo;<i> ".$expedition->getAttribute('name')."</i> &raquo;, par <b>"
					.$expedition->getAttribute('author')."</b><br />";
			$points = $expedition->getElementsByTagName('point');
			foreach($points as $point)
			{
				$x = $point->getAttribute('x');
				$y = $point->getAttribute('y');
				$msg .= "[$x,$y]&nbsp;&#x2192;&nbsp;";
			}
			$msg = trim($msg,"&nbsp;&#x2192;&nbsp;");
			$msg .= "<br />";
		}
		$output = "Liste des exp&eacute;ditions : <br />".trim($msg, "<br />");
		// Avertir
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
	
	// Emotes
	// Syntaxe : /me blabla
	case 'me':
		$txt = substr($commande, 4);
		$output = "<i>$user $txt</i>";
	break;
	
	// Ville
	// Syntaxe : /ville [eau|def|attaque|evo|chantiers]
	case 'ville':
		// Critère de recherche
		$commande = explode(" ",$commande);
		$critere = $commande[1];
		$msg = ""; $txt = "";
		$doc = getxml($key);
		switch($critere)
		{
			case 'eau':
				$cities = $doc->getElementsByTagName('city');
				foreach($cities as $city)
					$puits = $city->getAttribute('water');
				$banque = 0;
				$items = $doc->getElementsByTagName('item');
				foreach($items as $item)
					if($item->getAttribute('id') == 1)
						$banque = $item->getAttribute('count');
				$msg = "Il y a <b>$puits rations d'eau</b> dans le puits et <b>$banque</b> en banque."; 
			break;
			
			case 'porte':
				$cities = $doc->getElementsByTagName('city');
				foreach($cities as $city)
					$porte = $city->getAttribute('door');
				if($porte) $porte = "ouverte"; else $porte = "ferm&eacute;e";
				$msg = "La porte de la ville est <b>$porte</p>";
			break;
			
			case 'def':
				$defs = $doc->getElementsByTagName('defense');
				foreach($defs as $def)
				{
					$base = $def->getAttribute('base');
					$gardiens = $def->getAttribute('citizen_guardians');
					$maisons = $def->getAttribute('citizen_homes');
					$batiments = $def->getAttribute('buildings');
					$total = $def->getAttribute('total');
					$objets = $def->getAttribute('items');
					$fixations = $def->getAttribute('itemsMul');
					$objfix = $objets * $fixations;
				}
				$attqs = $doc->getElementsByTagName('e');
				foreach($attqs as $attq)
				{
					$max = $attq->getAttribute('max');
					$min = $attq->getAttribute('min');
					$prec = $attq->getAttribute('maxed');
						if($prec == 1) $prece = "Estimation <b>finale</b>"; 
						else $prece = "Estimation <b>provisoire</b>";
				}
				if(empty($max))
					$atq = "Estimation indisponible";
				else
					$atq = "$min &lt; $max</b> ($prece)";
				$msg =	"<u>D&eacute;fenses de la ville</u> : ".
						"<br />- <b>$base</b> points de base".
						"<br />- <b>$gardiens</b> points gr&acirc;ce aux h&eacute;ros Gardiens".
						"<br />- <b>$maisons</b> points gr&acirc;ce aux habitations".
						"<br />- <b>$batiments</b> points gr&acirc;ce aux chantiers".
						"<br />- <b>$objfix</b> points gr&acirc;ce aux objets de d&eacute;fense ".
						"<i>($objets objets x $fixations fixations)</i>".
						"<br /><b>TOTAL : $total points</b>".
						"<br /><b>ATTAQUE : $atq";
					// Avertir
					$pm['to'] = $user;
					$user = "SYSTEM";
			break;
			
			// Estimation de l'attaque
			case 'attaque':
				$attqs = $doc->getElementsByTagName('e');
				foreach($attqs as $attq)
				{
					$max = $attq->getAttribute('max');
					$min = $attq->getAttribute('min');
					$prec = $attq->getAttribute('maxed');
						if($prec == 1) $prec = "Il s'agit de l'estimation <b>finale</b>"; 
						else $prec = "Il s'agit d'une estimation <b>provisoire</b>";
				}
				if(empty($max))
					$msg = "Estimation de l'attaque indisponible";
				else
					$msg = "Entre <b>$min</b> et <b>$max</b> zombies d&eacute;ferleront sur la ville ce soir. $prec.";
					// Avertir
					$pm['to'] = $user;
					$user = "SYSTEM";
			break;
			
			// Liste des évolutions
			case 'evo':
				$txt = "<u>Liste des &eacute;volutions</u> :<br />";
				$evos = $doc->getElementsByTagName('upgrades');
				foreach($evos as $evo)
				{
					$total = $evo->getAttribute('total');
					$ups = $evo->getElementsByTagName('up');
					foreach($ups as $up)
					{
						$nom = $up->getAttribute('name');
						$lvl = $up->getAttribute('level');
						$msg .= "- <i>&laquo; $nom &raquo;</i> niveau $lvl/5<br />";
					}
					$msg = trim($msg,"<br />");
				}
				// Avertir
				$pm['to'] = $user;
				$user = "SYSTEM";
			break;
			
			// Liste des bâtiments
			case 'chantiers':
			$txt = "<u>Liste des chantiers construits</u> : <br />";
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

				// Affichage
				/*
				$msg .=  "<br /><img src=\"http://data.hordes.fr/gfx/icons/$img.gif\" />&nbsp;"
						."<i>&laquo; $nom &raquo;</i>$tmp";
				*/		
			}
			break;
				
			default: $txt = "";
		}
		$output = $txt.trim($msg,", ");
		// Avertir
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
	
	// Affiche des informations sur le désert
	// Syntaxe : /map [bat|ex|exall]
	case 'map':
		// Critère de recherche
		$commande = explode(" ",$commande);
		$critere = $commande[1];
		$msg = ""; $txt = "";
		$doc = getxml($key);
		switch($critere)
		{
			// Liste des bâtiments
			case 'bat':
				$txt = "<u>Liste des b&acirc;timents d&eacute;couverts</u> : ";
				$zones = $doc->getElementsByTagName('zone');
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
			break;
			
			// Liste des zones explorées aujorud'hui
			case 'ex':
				$txt = "<u>Liste des zones explor&eacute;es <i>aujourd'hui</i></u> : ";
				$zones = $doc->getElementsByTagName('zone');
				foreach($zones as $zone)
				{
					$x = $zone->getAttribute('x');
					$y = $zone->getAttribute('y');
					$nvt = $zone->getAttribute('nvt');
					$danger = $zone->getAttribute('danger');
					if(!$nvt)
						if(is_numeric($danger))
							$msg .= "<span class=\"d$danger\">[$x,$y]</span> ";
						else
							$msg .= "[$x,$y] ";
				}
				$msg .= "<br />Danger : <span class=\"d1\">faible</span> - <span class=\"d2\">moyen</span> - <span class=\"d3\">fort</span>";
			break;
			
			// Liste des zones explorées depuis le début de la partie
			case 'exall':
				$txt = "<u>Liste des zones explor&eacute;es <i>depuis le d&eacute;but</i></u> : ";
				$zones = $doc->getElementsByTagName('zone');
				foreach($zones as $zone)
				{
					$x = $zone->getAttribute('x');
					$y = $zone->getAttribute('y');
					$danger = $zone->getAttribute('danger');
					if(is_numeric($danger))
						$msg .= "<span class=\"d$danger\">[$x,$y]</span> ";
					else
						$msg .= "[$x,$y] ";
				}
				$msg .= "<br />Danger : <span class=\"d1\">faible</span> - <span class=\"d2\">moyen</span> - <span class=\"d3\">fort</span>";
			break;
		}
		$output = $txt.trim($msg);
		// Avertir
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
	
	// Créer une salle ou rejoindre une salle existance
	case 'join':
		// Utilisateur enregistré ?
		if(isset($_SESSION['auth']) and $_SESSION['auth'] == 1)
		{
			$output = "";
			$commande = explode(" ",$commande);
			$room = iconv("UTF-8", "ISO-8859-1",addslashes($commande[1]));
			
			if(strtolower($room) == 'public')
			{
				$output .= "Le salon Public est toujours actif. Lisez l'aide en ligne.";
				$pm['to'] = $user;
				$user = "SYSTEM";
				break;
			}
			
			if(isset($commande[2])) 
				{ $pass = iconv("UTF-8", "ISO-8859-1",addslashes($commande[2])); $sp = "priv&eacute;"; }
			else
				{ $pass = false; $sp = "public"; }
		
			include_once("classes/Rooms.php");
			$rooms = new Rooms();
			
			// Création de la salle si elle n'existe pas
			if(!$rooms->isRoom($room))
			{
				$rooms->newRoom($room,$pass);
				$output .= "Salon $sp <b>$room</b> cr&eacute;&eacute;e.<br />";
			}
		
			// Rejoindre la salle
			$response = $rooms->joinRoom($rooms->getRoomId($room),$pass);
		
			// Avertir
			if(!$response)
			{
				$output .= "Le salon <b>$room</b> est priv&eacute;, vous devez entrer son mot de passe pour le rejoindre.";
				$pm['to'] = $user;
				$user = "SYSTEM";
			}
			else
			{
				$output .= "Vous venez de rejoindre le salon $sp <b>$room</b>";
				$pm['to'] = $user;
				$user = "SYSTEM";
			}
		}
		else
		{
			$output .= "Les utilisateurs non-identifi&eacute;s ne peuvent acc&eacute;der qu'au salon Public.";
			$pm['to'] = $user;
			$user = "SYSTEM";
		}
	break;
	
	// Quitter une salle
	case 'leave':
		$output = "";
		$commande = explode(" ",$commande);
		
		include_once("classes/Rooms.php");
		$rooms = new Rooms();
		
		// Nom du salon spécifié
		if(isset($commande[1]))
		{
			$room = iconv("UTF-8", "ISO-8859-1",addslashes($commande[1]));
			// Id de la salle
			$rid = $rooms->getRoomId($room);
		}
		// Nom du salon non spécifié
		else
		{
			$arid = $rooms->getActiveRoom();
			if(!$arid)
			{
				$output .= "Vous ne pouvez pas quitter le salon Public";
				$pm['to'] = $user;
				$user = "SYSTEM";
				break;
			}
			else
				$rid = $arid;
			$room = $rooms->getRoomName($rid);
		}
		
		// Quitter la salle
		$rooms->leaveRoom($rid);
		
		// Avertir
		$output .= "Vous venez de quitter le salon <b>$room</b>";
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
	
	// Inviter dans un salon
	case 'invite':
		$commande = explode(" ",$commande);
		$invite = iconv("UTF-8", "ISO-8859-1",addslashes($commande[1]));
		
		// L'utilisateur invité existe-t-il ?
		$sql = "SELECT * FROM users WHERE user = (SELECT user FROM users_registered WHERE user = '$invite') AND online = '1'";
		$count = num_rows($sql);
		if($count < 1)
		{
			$output = "L\'utilisateur $invite n\'est pas connect&eacute; ou n\'existe pas";
			$pm['to'] = $user;
			$user = "SYSTEM";
			break;
		}
		
		// Salons
		include_once("classes/Rooms.php");
		$rooms = new Rooms();
		
		// Id de la salle active
		$room_id = $rooms->getActiveRoom();
		
		// Nom du salon
		$room = $rooms->getRoomName($room_id);
		
		// Si l'utilisateur invité est déjà dans le salon, ne pas poursuivre
		if($rooms->isUserInRoom($room_id,$invite))
		{
			$output = "$invite est d&eacute;j&agrave; dans le salon <b>$room</b>";
			$pm['to'] = $user;
			$user = "SYSTEM";
			break;
		}
		
		$output = "";
		
		// Mot de passe de la salle
		$pass = $rooms->getRoomPassword($room_id);
		
		// Join
		$rooms->joinRoom($room_id,$pass,$invite);
		
		// Avertir
		$output .= "Vous venez d'&ecirc;tre invit&eacute; dans le salon <b>$room</b> par ".addslashes($_COOKIE['user']);
		$pm['to'] = $invite;
		$user = "SYSTEM";
	break;
	
	default:
		$output = "Commande invalide.";
		$pm['to'] = $user;
		$user = "SYSTEM";
	break;
}


?>