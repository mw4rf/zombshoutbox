<?php
$sql = "SELECT DISTINCT ville FROM xml_rencontres ORDER BY id DESC";
$req = query($sql);

echo "<select id=\"s_citoyens_ville\">";
while($data = mysql_fetch_assoc($req))
{
	if(!empty($data['ville']))
	{
		$ville = iconv("ISO-8859-1", "UTF-8", $data['ville']); // nécessaire depuis le passage par ajax / rev 30
		echo "<option value=\"$ville\">".$ville."</option>";
	}
}
echo "</select>";
?>