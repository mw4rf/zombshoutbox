<?php
$o = new Objets();
$objets = $o->getAllNames(true);
?>
<select id="s_objets">
	<?php
	foreach($objets as $objet)
		echo "<option id=\"$objet\">$objet</option>";
	?>
</select>