<?php
$c = new Chantiers();
$chantiers = $c->getAllNames(true);
?>
<select id="s_chantiers">
	<?php
	foreach($chantiers as $chantier)
		echo "<option id=\"$chantier\">$chantier</option>";
	?>
</select>