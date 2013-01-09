<?php
session_start();
header( 'Content-Type: text/xml; charset=ISO-8859-1' );
include_once("../config.inc.php");
include_once("../fonctions.inc.php");

include_once($rootpath."/classes/Note.php");

if(isset($_POST['action']))
{
	$output = "";
	
	switch($_POST['action'])
	{
		case 'delete':
			if(!isset($_POST['delid'])) die("Erreur.");
			$id = $_POST['delid'];
			$sql = "DELETE FROM notes WHERE id='$id'";
			query($sql);			
		break;
		
		case 'new':
			$note = addslashes(iconv("UTF-8","ISO-8859-1",$_POST['note']));
			$user = $_COOKIE['user'];
			$cdate = $cdate = date("Y-m-d H:i:s");
			$sql = "INSERT INTO notes VALUES ('','$note','$user','$cdate')";
			query($sql);
		break;
		
		case 'update':
			$note = addslashes(iconv("UTF-8","ISO-8859-1",$_POST['note']));
			$user = $_COOKIE['user'];
			$cdate = $cdate = date("Y-m-d H:i:s");
			$id = $_POST['note_id'];
			$sql = "UPDATE notes SET note='$note', cdate='$cdate' WHERE id='$id'";
			query($sql);
		break;
		
		case 'sharenotewith':
			$reader = addslashes(iconv("UTF-8","ISO-8859-1",$_POST['reader']));
			$user = $_COOKIE['user'];
			$id = $_POST['note_id'];
			$note = new Note();
			$note->loadNote($id);
			$note->shareNoteWith($reader);
		break;
		
		case 'unsharenotewith':
			$reader = addslashes(iconv("UTF-8","ISO-8859-1",$_POST['reader']));
			$id = $_POST['note_id'];
			$note = new Note();
			$note->loadNote($id);
			$note->unShareNoteWith($reader);
		break;
		
		default: $output = "Aucune commande spécifiée"; break;
	}
}
else
{
	echo "Vous ne pouvez pas appeler cette page directement";
}

?>