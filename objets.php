<?php

$objets = array(
	// G�n�riques
	"OD" => "plate",
	"Nourriture" => "food_biscuit",
	"Bouffe" => "food_biscuit",
	"Animal" => "pet_pig",
	"Drogue" => "drug",
	"Alcool" => "vodka",
	
	// Utilitaires
	"Appareil �lectronique en panne" => "electro_box",
	"Appareil �lectronique" => "electro_box",
	"Appareil electronique" => "electro_box",
	"Appareil" => "electro_box",
	"Pile" => "pile",
	"Tournevis" => "screw",
	"Canif d�risoire" => "small_knife",
	"Canif" => "small_knife",
	"Ciment" => "concrete",
	"Sac de ciment" => "concrete",
	"Chat" => "pet_cat",
	"Gros chat mignon" => "pet_cat",
	"cadenas" => "lock",
	"Cha�ne de Porte + cadenas" => "lock",
	"Cha�ne" => "chain",
	"Chaine" => "chain",
	"Cha�ne rouill�e" => "chain",
	"Chaine rouill��" => "chain",
	"Cochon" => "pet_pig",
	"Cochon malodorant" => "pet_pig",
	"Courroie" => "courroie",
	"D�vastator incomplet" => "big_pgun_part",
	"D�vastator" => "big_pgun_part",
	"Devastator incomplet" => "big_pgun_part",
	"Devastator" => "big_pgun_part",
	"Grosse cha�ne rouill��" => "chain",
	"Grosse chaine rouill��" => "chain",
	"Ouvre-bo�te" => "can_opener",
	"Ouvre-boite" => "can_opener",
	"Ouvre boite" => "can_opener",
	"Ouvre bo�te" => "can_opener",
	"Bo�te en m�tal" => "chest",
	"Boite en m�tal" => "chest",
	"Bo�te" => "chest",
	"Boite" => "chest",
	"Poign�e de vis et �crous" => "meca_parts",
	"Jerrycan" => "jerrycan",
	"Jeu de cartes" => "cards",
	"Jeu de cartes incomplet" => "cards",
	"Cartes" => "cards",
	"Kit de bricolage" => "repair_kit",
	"PVE" => "meca_parts",
	"Vis" => "meca_parts",
	"Poign�e de vis" => "meca_parts",
	"Planche tordue" => "wood2",
	"Planche" => "wood2",
	"Ferraille" => "metal",
	"Explosif" => "explo",
	"Dynamite" => "explo",
	"Explosif brut" => "explo",
	"P�tard" => "explo",
	"Explosifs bruts" => "explo",
	"Bandage rudimentaire" => "bandage",
	"Bandage" => "bandage",
	"Bo�te d'allumettes" => "lights",
	"Boite d'allumettes" => "lights",
	"Viande Humaine" => "hmeat",
	"Sac plastique" => "grenade_empty",
	"Sac" => "grenade_empty",
	"Rustine" => "rustine",
	"Poule" => "pet_chick",
	"Rat g�ant" => "pet_rat",
	"Rat" => "pet_rat",
	"Tube de cuivre" => "tube",
	"Tube" => "tube",
	"Produits pharmaceutiques" => "pharma",
	"PP" => "pharma",
	"Composant �lectronique" => "electro",
	"Composant" => "electro",
	"Compo" => "electro",
	"Moteur" => "engine",
	"M�canisme" => "mecanism",
	"Radio K7 �teint" => "radio_off",
	"Radio" => "radio_off",
	"Radius mark II" => "radius_mk2",
	"Radius mark 2" => "radius_mk2",
	"Mark II" => "radius_mk2",
	"Pistolet � Eau" => "watergun_empty",
	"Pistolet" => "watergun_empty",
	"Lance-Pile 1-PDTG" => "pilegun_empty",
	"Lance-pile" => "pilegun_empty",
	"Vieille machine � laver" => "machine_1",
	"Vieille machine a laver" => "machine_1",
	"Machine � laver" => "machine_1",
	"Machine a laver" => "machine_1",
	"Meuble en kit" => "deco_box",
	"Meubles en kit" => "deco_box",
	"Meuble" => "deco_box",
	"Meubles" => "deco_box",
	"Nesquick" => "digger",
	"Nessquick" => "digger",
	"Ness-quick" => "digger",
	"D�sherbant" => "digger",
	"D�sherbant Nesquick" => "digger",
	"D�sherbant Ness-Quick" => "digger",
	"Balise Radius" => "tagger",
	"Balise" => "tagger",
	"D�tonateur compact" => "deto",
	"D�tonateur" => "deto",
	"Parac�to�de 7g" => "disinfect",
	"Parac�to�de" => "disinfect",
	"Paracet" => "disinfect",
	"Os charnu" => "bone_meat",
	"Os" => "bone_meat",
	"Poutre rafistol�e" => "wood_beam",
	"Poutre" => "wood_beam",
	"PMV" => "vibr_empty",
	"Petit manche vibrant" => "vibr_empty",
	"Serpent" => "pet_snake",
	"Serpent de 2 m�tres" => "pet_snake",
	"Four" => "machine_2",
	"Four canc�rig�ne" => "machine_2",
	"Sport-elec" => "sport_elec_empty",
	"Sport-�lec" => "sport_elec_empty",
	"Sportelec" => "sport_elec_empty",
	"Structures m�talliques" => "metal_beam",
	"Structure" => "metal_beam",
	"Souche de bois pourrie" => "wood_bad",
	"Souche de bois" => "wood_bad",
	"Souche" => "wood_bad",
	"Twino�de" => "drug_hero",
	"Twinoide" => "drug_hero",
	"Twin" => "drug_hero",
	"Twino�de 500mg" => "drug_hero",
	"Twinoide 500mg" => "drug_hero",
	"Vodka" => "vodka",
	"Vodka Marinostov" => "vodka",
	"Brico'Facile" => "repair_one",
	"Brico" => "repair_one" // end
	);

// Pr�voir d'autres orhtographes
foreach($objets as $objet => $img)
{
	$objets[strtolower($objet)] = $img; // sans majuscule
	$objets[strtolower($objet)."s"] = $img; // au pluriel
}

function listobjets()
{
	global $objets;
	
	$out = "<table>";
	$out .= "<tr><td colspan=\"2\">Butin</td></tr>";
	foreach($objets as $objet => $image)
		$out .= "";
	$out .= "</table>";
}


?>