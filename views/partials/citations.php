<?php
if(file_exists('../../config.inc.php')) include_once('../../config.inc.php');
@include_once($rootpath."/classes/Quote.php");

// Affichage des citations
$quote = new Quote(); 
$quote->getRandom();
echo "<div class=\"sondage\"><div class=\"tname\">Citation al&eacute;atoire</div><div style=\"font-weight:normal;font-size:0.9em;\">&laquo;&nbsp;".$quote->getQuote()."&nbsp;&raquo;</div></div>";
?>