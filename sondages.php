<?php
session_start();
header( 'Content-Type: text/xml; charset=UTF-8' );
include_once("config.inc.php");
include_once("fonctions.inc.php");

if(isset($_COOKIE['user']) and isset($_SESSION['auth']) and $_SESSION['auth'] == 1)
{
	
	$user = $_COOKIE['user'];

	// Affichage des sondages
	$sql_sondages = "SELECT * FROM poll_titres WHERE TO_DAYS(expiration) - TO_DAYS(NOW()) > -1 ORDER BY id DESC";
	$req_sondages = query($sql_sondages);
	while($data_sondages = mysql_fetch_assoc($req_sondages))
	{
		$sondage_id = $data_sondages['id'];
		$question = stripslashes(iconv("ISO-8859-1", "UTF-8", $data_sondages['question']));
	
		// Protection contre les images
		if(isset($_COOKIE['prefs_images']) and $_COOKIE['prefs_images'] == "Non")
			$question = strip_selected_tags($question, array("img"));
	
		echo "<div class=\"sondage\">";
		echo 	"<div class=\"tname\">$question</div>";
	
		// L'utilisateur a-t-il déjà voté ?
		$sql_user = "SELECT * FROM poll_votes WHERE user='$user' AND sondage_id = '$sondage_id'";
		if(num_rows($sql_user) > 0) $hasvoted = true; else $hasvoted = false;
	
		// L'utilisateur n'a pas voté : on affiche les options du sondage
		if(!$hasvoted)
		{
			$sql_options = "SELECT * FROM poll_options WHERE sondage_id = '$sondage_id'";
			$req_options = query($sql_options);
			while($data_options = mysql_fetch_assoc($req_options))
			{
				$opt = stripslashes(iconv("ISO-8859-1", "UTF-8", $data_options['option']));
				$opt_id = $data_options['id'];
			
				// Protection contre les images
				if(isset($_COOKIE['prefs_images']) and $_COOKIE['prefs_images'] == "Non")
					$opt = strip_selected_tags($opt, array("img"));
			
				echo "<label><input type=\"radio\" name=\"sondage_$sondage_id\""
						."onclick=\"addvote('$sondage_id','$opt_id')\" />$opt</label><br />";
			}
		}
		// L'utilisateur a voté : on affiche les résultats du sondage
		else
		{
				$sql_options = "SELECT * FROM poll_options WHERE sondage_id = '$sondage_id'";
				$req_options = query($sql_options);
				while($data_options = mysql_fetch_assoc($req_options))
				{
					$opt = stripslashes(iconv("ISO-8859-1", "UTF-8", $data_options['option']));
					$opt_id = $data_options['id'];
					$vcount = 0;
					$vuser = "";
				
					$sql_votes = "SELECT * FROM poll_votes WHERE sondage_id='$sondage_id' AND option_id='$opt_id'";
					$req_votes = query($sql_votes);
					while($data_votes = mysql_fetch_assoc($req_votes))
					{
						$vcount++;
						$vuser .= $data_votes['user']." ";
					}
					$vuser = trim($vuser);
				
					// Protection contre les images
					if(isset($_COOKIE['prefs_images']) and $_COOKIE['prefs_images'] == "Non")
						$opt = strip_selected_tags($opt, array("img"));
				
					echo "<div class=\"sondage_vote\">$opt : $vcount <br /><span class=\"sondage_users\">$vuser</span></div>";
				}
				// Changer mon vote
				echo "<span style=\"font-size:0.8em;text-align:center;\"><a href=\"#\" onclick=\"delvote('$sondage_id')\">Changer mon vote</a></span>";
		}
	
		echo "</div>";
	}
}
?>