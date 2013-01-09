<?php
include("config.inc.php");
include("fonctions.inc.php");

// MAINTENANCE : Suppression des utilisateurs qui ne sont pas à jour

/*
Petite explication nécessaire. Chaque message a un id et un timestamp. Chaque utilisateur a l'id du dernier
message vu et le timestamp du dernier rafraichissement de la page (si elle contient de nouveau messages).
Donc si l'utilisateur n'a pas vu le dernier message et qu'il n'a pas refresh depuis plus de 15 minutes,
alors qu'il y a eu des nouveaux messages, c'est qu'il n'est plus là !
*/

$sql = "SELECT id,timestamp FROM messages ORDER BY id DESC LIMIT 0,1";
$data = mysql_fetch_assoc(query($sql));
$lastmsg = $data['id'];
//$lasttime = $data['timestamp']-60*15; // en secondes
$now = time();
$decay = 60*15; // en secondes : 15 minutes
$sql = "UPDATE users SET online='0' WHERE refresh != '$lastmsg' AND $now-lastaction > $decay";
query($sql);
//echo $sql;
?>