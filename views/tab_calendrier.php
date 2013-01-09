<?php
session_start();
include_once("../config.inc.php");
include_once("../fonctions.inc.php");
include_once("../classes/HordesXML.php");
######################################################################################################################

?>

<ul id="innertabs_cal" class="tabs">
	<li class="tab" id="tabcal1"><a href="#innertab_cal_1"><img style="vertical-align: middle;" src="smilies/toolbar/calendrier/evenements.png" />&nbsp;&Eacute;v&eacute;nements</a></li>
	<li class="tab" id="tabcal1"><a href="#innertab_cal_2"><img style="vertical-align: middle;" src="smilies/toolbar/calendrier/disponibilites.png" />&nbsp;Mes disponibilit&eacute;s</a></li>
	<li class="tab" id="tabcal2"><a href="#innertab_cal_3"><img style="vertical-align: middle;" src="smilies/toolbar/calendrier/frise.png" />&nbsp;Frise des disponibilit&eacute;s</a></li>
	<li class="tab" id="tabcal3"><a href="#innertab_cal_5"><img style="vertical-align: middle;" src="smilies/toolbar/calendrier/calendrier.png" />&nbsp;Calendrier des disponibilit&eacute;s</a></li>
	<li class="tab" id="tabcal3"><a href="#innertab_cal_4"><img style="vertical-align: middle;" src="smilies/toolbar/calendrier/expeditions.png" />&nbsp;Exp&eacute;ditions</a></li>
</ul>

<div id="innertab_cal_1" class="intab" style="width:100%">
<div class="prefs" style="text-align:center;"><input id="tab_calendrier_rb" type="submit" value="&#x238B; Actualiser" onclick="ajaxrefreshspecial('tab9','tab_calendrier')"></div>
<?php include_once('calendrier/view_cal_evtscoa.php'); ?>
</div><!--// intab -->

<div id="innertab_cal_2" class="intab" style="width:100%">
<?php include_once('calendrier/view_cal_owndispo.php'); ?>
</div> <!--// intab -->

<div id="innertab_cal_3" class="intab" style="width:100%">
<?php include_once('calendrier/view_cal_frise.php'); ?>
</div> <!--// intab -->

<div id="innertab_cal_4" class="intab" style="width:100%">
<?php include_once('calendrier/view_cal_expeditions.php'); ?>
</div> <!--// intab -->

<div id="innertab_cal_5" class="intab" style="width:100%">
<?php include_once('calendrier/view_cal_calendrier.php'); ?>
</div><!--// intab -->

<?php
// Charger le javascript permettant de mettre en place les TABS
// Il faut le faire ici, car si on le fait dans le fichier fonctions.js, la méthode dom:loaded
// de prototype est appelée après le chargement de index.php mais AVANT le chargement de tab_calendrier.php
// Conséquence : le javascript ne trouve pas le div sur lequel il est censé agir (qui est défini sur cette page)
echo "<script>innertabs_cal = new Control.Tabs('innertabs_cal');</script>";
?>