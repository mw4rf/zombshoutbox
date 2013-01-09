<?php
session_start();
include_once('../config.inc.php');

// Commandes ville
echo "<div class=\"help\"><b><u>Liste des commandes d'information sur la ville</u></b>
<br /><i>Pour utiliser ces commandes, vous devez avoir fourni <a href=\"apikey.jpg\">votre cl&eacute; API</a> lors de la connexion.</i>
<br /><br /><span class=\"comm\">/whois <i>&lt;pseudo&gt;</i></span> : affiche de nombreuses informations sur un citoyen.
<br /><span class=\"comm\">/pos <i>&lt;pseudo&gt;</i></span> : affiche la position actuelle d'un citoyen.
<br /><span class=\"comm\">/hero <i>&lt;pseudo&gt;</i></span> : affiche le statut h&eacute;ro d'un citoyen et son m&eacute;tier.
<br /><span class=\"comm\">/who [vivant|mort|banni|hero|gardien|fouineur|eclaireur|enville|dehors]</span> : informations sur les citoyens.
<br /><span class=\"comm\">/ville [eau|def|attaque|evo|chantiers]</span> : informations sur la ville.
<br /><span class=\"comm\">/map [bat|ex|exall]</span> : informations sur le d&eacute;sert.
<br /><span class=\"comm\">/expe</span> : affiche la liste des exp&eacute;ditions du jour.
<br /><span class=\"comm\">/survie &lt;nombre de points de d&amp;eacute;fense personnelle&gt;</span> : affiche les chances de survie en fonction de l'attaque du jour.
<br /><span class=\"comm\">/objet <i>&lt;objet&gt;</i></span> : affiche des informations sur un objet
<br /><span class=\"comm\">/chantier <i>&lt;chantier&gt;</i></span> : affiche des informations sur un chantier
</div>";

// Autres commandes
echo "<div class=\"help\"><b><u>Commandes utilitaires</u></b>
<br /><span class=\"comm\">/me</span> : affiche un emote.
<br /><span class=\"comm\">/note &lt;texte&gt;</span> : enregistre une note.
<br /><span class=\"comm\">/w <i>&lt;pseudo&gt;[,pseudo,...]</i> &lt;texte&gt;</span> : envoie un message priv&eacute; &agrave; la personne d&eacute;sign&eacute;e. Pour envoyer le m&ecirc;me message priv&eacute; &agrave; plusieurs personnes, s&eacute;parez leurs noms par des virgules, <u>sans laisser aucun espace</u>. Exemple : <i>/w Pierre,Paul,Jacques Salut !</i>.
<br /><span class=\"comm\">/r &lt;texte&gt;</span> : permet de r&eacute;pondre au dernier message priv&eacute; re&ccedil;u.
<br /><span class=\"comm\">/afk</span> : change l'&eacute;tat de l'utilisateur de pr&eacute;sent � absent, et vice-versa.
<br /><br /><span class=\"comm\">/quote</span> : affiche une citation al&eacute;atoire
<br /><span class=\"comm\">/quote new &lt;texte&gt;</span> : ajoute une nouvelle citation. <u>SVP pr�cisez la source, entre parenth�ses, � la fin de la citation.</u>
</div>";

// Etat des utilisateurs
echo "<div class=\"help\"><b><u>�tat des utilisateurs</u></b>
<br />L'�tat des utilisateurs connect�s � la ShoutBox est indiqu�, dans une liste dans la partie <i>gauche</i> de la page, gr�ce � un code de couleurs : 
<br /><span class=\"user-online\">Utilisateur connect� et disponible</span>
<br /><span class=\"user-background\">Utilisateur connect� avec la fen�tre de la ShoutBox en arri�re-plan</span>
<br /><span class=\"user-afk\">Utilisateur indisponible ou � ne pas d�ranger</span>.
<br />Vous pouvez indiquer votre indisponibilit� � l'aide de la commande <span class=\"comm\">/afk</span>. Utilisez � nouveau cette commande, ou �crivez un message dans la ShoutBox, pour �tre � nouveau consid�r� comme �tant disponible.
<br /><br />A c�t� du nom de chaque utilisateur :
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>+i</b> indique que le pseudonyme est enregistr� et que l'utilisateur est identifi� ; <b>-i</b> indique que le pseudonyme n'est pas enregistr� ou que l'utilisateur n'est pas identifi�.
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>+k</b> indique que l'utilisateur a fourni sa cl� API et qu'il peut utiliser les commandes ; <b>-k</b> indique qu'il n'a pas fourni sa cl� API.
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>+v</b> indique que l'utilisateur peur parler sur la ShoutBox ; <b>-v</b> indique que l'utilisateur a �t� priv� de parole.
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>@</b> indique que l'utilisateur est un administrateur ou un mod�rateur.
</div>";

// Salles de discussion
echo "<div class=\"help\"><b><u>Salons de discussion</u></b>
<br />La ShoutBox permet � ses utilisateurs de discuter dans des salons de discussion. Le salon <b>Public</b> regroupe tous les utilisateurs et il n'est pas possible de le quitter. Tous les autres salons peuvent �tre cr��s, rejoints et quitt�s par les utilisateurs <u>enregistr�s</u>.

<br /><br />Les salons peuvent �tre <i>publics</i> ou <i>priv�s</i>. Cela est d�termin� lors de leur cr�ation : les salons priv�s sont cr��s avec un mot de passe, qu'il sera n�cessaire d'indiquer pour les rejoindre ; les salons publics sont cr��s sans mot de passe et peuvent �tre rejoints sans mot de passe. 

<br /><br />L'onglet <i>Recherche</i> permet de rechercher les messages dans les salons publics et priv�s. Cependant, pour rechercher dans un salon priv�, il faut avoir rejoint ce salon (et donc conna�tre son mot de passe).

<br /><br /><u><b>Commande de salon</b></u>
<br /><span class=\"comm\">/join &lt;nom_du_salon&gt; [mot_de_passe]</span> : permet de rejoindre le salon public dont le nom est indiqu�, ou le salon priv� dont le nom et le mot de passe sont indiqu�s. Si le salon n'existe pas, il sera cr��.
<br /><span class=\"comm\">/leave [nom_du_salon]</span> : permet de quitter un salon dont le nom est sp�cifi�. Si aucun nom n'est sp�cifi�, le salon actif sera quitt�. Le salon Public ne peut pas �tre quitt�.
<br /><span class=\"comm\">/invite &lt;utilisateur&gt;</span> : permet d'inviter un utilisateur enregistr� et connect� dans le salon public ou priv� actif.

<br /><br /><u><b>Liste des salons</b></u>
<br />La liste des salons s'affiche en dessous de la liste des utilisateurs connect�s. Un code de couleurs permet d'identifier rapidement les diff�rents salons :
<br /><span class=\"user-online\">&#x2192; Salon actif</span> : les messages �crits seront envoy�s dans ce salon
<br /><span class=\"user-background\">&bull; Salon ouvert</span> : vous �tes dans ce salon et vous verrez les messages qui y sont envoy�s
<br /><span class=\"user-afk\">Salon ferm�</span> : vous n'�tes <u>pas</u> dans ce salon et vous ne verrez <u>pas</u> les messages qui y sont envoy�s.
<br />Vous ne pouvez d�finir le salon actif que parmi les salons que vous avez pr�alablement rejoints avec la commande /join. <u>Pour activer un salon, cliquez sur son nom dans la liste.</u>
<br />Le symbole <b>#</b> � droite du nom d'un salon indique qu'il s'agit d'un salon priv�, accessible uniquement en connaissant son mot de passe.

<br /><br /><u><b>Liste des utilisateurs pr�sents dans le salon</b></u>
<br />Dans la liste des salons, le nombre de personnes pr�sentes dans chaque salon est indiqu�e � droite du nom du salon.
<br />Dans la liste des utilisateurs connect�s, les utilisateurs pr�sents dans votre salon <u>actif</u> sont identifi�s par un point (&bull;) � gauche de leur nom.
</div>";

// Syntaxe de style
echo "<div class=\"help\"><b><u>Syntaxe de style</u></b>
<br /><i>Italique</i> : <span class=\"comm\">*texte*</span> (une �toile)
<br /><b>Gras</b> : <span class=\"comm\">**texte**</span> (deux �toiles)
<br /><i><b>Gras-Italique</b></i> : <span class=\"comm\">***texte***</span> (trois �toiles)
<br /><u>Soulign�</u> : <span class=\"comm\">__texte__</span> (deux traits de soulignement)
<br /><s>Barr�</s> : <span class=\"comm\">--texte--</span> (deux tirets)
<br />Couleur du texte : <span class=\"comm\">couleur=red(texte)</span> (remplacer <span style=\"color:red;\">red</span> par la couleur d�sir�e)
<br />Couleur de fond : <span class=\"comm\">fond=blue(texte)</span> (remplacer <span style=\"background-color:blue;\">blue</span> par la couleur d�sir�e)
<br />Couleurs de texte et de fond : <span class=\"comm\">couleur=red(fond=blue(texte))</span>
</div>";

// Insertion
echo "<div class=\"help\"><b><u>Syntaxe d'insertion</u></b>
<br />Pour ins�rer une image : <span class=\"comm\">image=http://www.serveur.com/mon_image.jpg</span>
<br />Pour ins�rer un lien hypertexte : <span class=\"comm\">lien=http://hordes.valhalla.fr/shoutbox/</span>
<br />Pour ins�rer une vid�o YouTube : <span class=\"comm\">youtube=http://www.youtube.com/watch?v=XYZ</span>
<br />Pour ins�rer un saut de ligne : <span class=\"comm\">//</span>
<br />Pour ins�rer le r�sultat d'une �quation : <span class=\"comm\">calcul=1+1</span> (affiche : <i>1+1 = 2</i>)
<br />Pour ins�rer au hasard le nom d'un utilisateur de la Shoutbox : <span class=\"comm\">[membre]</span>
<br />Pour ins�rer au hasard le nom d'un citoyen de votre ville : <span class=\"comm\">[habitant]</span>
</div>";

// Smilies
$msg = "";
for($i = 0 ; $i < count($smiley_in) ; $i++)
	$msg .= "<img alt=\"$smiley_out[$i]\" src=\"smilies/h_$smiley_out[$i].gif\" />".ltrim($smiley_in[$i])."&nbsp;&nbsp;";

echo "<div class=\"help\"><b><u>Liste des smilies</u></b>
<br />$msg
<br /><b>NB</b> : vous devez laisser un espace avant les deux points.</div>";

// Autres commandes
echo "<div class=\"help\"><b><u>Jeux de hasard</u></b>
<br /><span class=\"comm\">/roll</span> : lance un d&eacute; � 6 faces.
<br /><span class=\"comm\">/roll [1-100]</span> : lance le nombre de d&eacute;s demand&eacute; (p. ex. /roll 20 pour tirer 20 d�s).
<br /><span class=\"comm\">/pfc</span> : Pierre-Feuille-Ciseaux (la pierre est battue par la feuille qui est battue par les ciseaux qui sont battus par la pierre).
<br /><span class=\"comm\">/pf</span> : Pile ou Face ?
<br /><span class=\"comm\">/carte</span> : Tire une carte d'un jeu de 52 cartes avec 1 joker.
</div>";

// Twitter
echo "<div class=\"help\"><b><u>Twitter</u></b>
<br /><span class=\"comm\">/tweet &lt;texte&gt;</span> : envoie un nouveau message vers Twitter
<br /><a href=\"http://twitter.com/$twitter_user\" target=\"_blank\">Cliquez ici</a> pour suivre les messages sur Twitter (compte � <i>follow</i> : <b>$twitter_user</b>).
</div>";

// Sondages
echo "<div class=\"help\"><b><u>Sondages</u></b>
<br />1) Pour cr&eacute;er un nouveau sondage, utilisez la commande <span class=\"comm\">/poll new &lt;expiration&gt; &lt;intitul&eacute; du sondage&gt;</span>.<br />L'argument <i>expiration</i> correspond &agrave; la dur&eacute;e du sondage en nombre de jours.<br />L'intitul� du sondage est la question que vous posez aux utilisateurs.<br /><br />2) Ajoutez autant d'options que n&eacute;cessaire avec la commande <span class=\"comm\">/poll option &lt;texte de l'option&gt;</span><br /><br /><u>Exemple</u> : Pour poser la question <i>&laquo; Comment allez-vous ? &raquo;</i> pendant 5 jours et permettre aux utilisateurs de r&eacute;pondre <i>&laquo; Bien &raquo;</i> ou <i>&laquo; Mal &raquo;</i>, tapez les 3 commandes suivantes :<br /><i>/poll new 5 Comment allez-vous ?<br />/poll option Bien<br />/poll option Mal</i></div>";

// Butin
echo "<div class=\"help\"><b><u>Butin</u></b> : 
<br /><span class=\"comm\">/butin &lt;[+|-]nombre&gt; &lt;objet&gt; [@&lt;pseudo&gt;]</span> : d&eacute;finit le butin trouv&eacute; en exp&eacute;dition
<br /><span class=\"comm\">/besoin &lt;[+|-]nombre&gt; &lt;objet&gt; [@&lt;pseudo&gt;]</span> : d&eacute;finit le butin &agrave; ramener en priorit&eacute;, ou les objets dont la ville a besoin
<br /><br /><i>Fonctionnement des commandes</i> : 
<br />1) Si l'objet existe d&eacute;j&agrave; dans la liste, le nombre est mis &agrave; jour. Dans le cas contraire, l'objet est cr&eacute;&eacute;.
<br />2) Si vous utilisez le symbole + (plus), le nombre est <b>ajout&eacute;</b> au nombre d'objets existant. Si vous utilisez le symbole - (moins), il lui est <b>retranch&eacute;</b>. Si vous n'utilisez aucun symbole, il lui est <b>substitu&eacute;</b>.
<br />3) Pour supprimer un objet de la liste, d&eacute;finissez son nombre &agrave; 0 (z&eacute;ro).
<br />4) Pour supprimer en une seule fois tous les objets du butin, utilisez les commandes <i>/butin reset</i> ou <i>/besoin reset</i>.
<br /><br />Le butin est en principe attribu� � la personne qui a utilis� la commande /butin. Cependant, il est possible que la personne qui tape la commande ne soit pas la personne qui poss�de l'objet : par exemple, en cas d'escorte. Pour attribuer un objet � une autre personne, faites suivre la commande butin par le symbole @ et le pseudo de la personne � qui attribuer l'objet. Exemple : <u>/butin +1 OD @mw4rf</u> attribue un objet de d�fense � mw4rf. NB : vous pouvez laisser un ou plusieurs espaces de part et d'autre du symbole @, cela n'a pas d'incidence sur le fonctionnement de la commande.
</div>";

// Compteurs
echo "<div class=\"help\"><b><u>Compteurs</u></b>
<br />Les compteurs permettent d'afficher un compte-&agrave;-rebours jusqu'&agrave; un certain moment.
<br />Pour obtenir de l'aide, utilisez la commande <span class=\"comm\">/timer help</span>
<br />
<br />1) <u>Cr&eacute;ation d'un compteur</u>
<br /><span class=\"comm\">/timer new &lt;nom&gt;</span>
<br />Exemple : <i>/timer new FA</i>
<br />
<br />2) <u>Initialisation d'un compteur</u>
<br />Le moment d'expiration du compte &agrave; rebours peut &ecirc;tre d&eacute;fini de deux mani&egrave;res :
<ul>
<li /><span class=\"comm\">/timer to &lt;nom&gt; &lt;heure&gt;</span><br /><i>compter jusqu'&agrave; une heure d�finie</i> (p. ex., <i>/timer to FA 12:30</i> ordonne au compteur FA pr�c�demment cr&eacute;&eacute; de compter jusqu'&agrave; 12h30)
<li /><span class=\"comm\">/timer set &lt;nom&gt; &lt;dur&eacute;e&gt;</span><br /><i>compter pendant un certain nombre d'heure et de minutes</i> (p. ex., <i>/timer set FA 1:30</i> ordonne au compteur FA pr�c�demment cr&eacute;&eacute; de compter pendant 1 heure et 30 minutes)
</ul>
3) <i>(falcultatif)</i> <u>D�finir une �tiquette pour un compteur</u>
<br /><span class=\"comm\">/timer label &lt;nom&gt;</span>
<br />Exemple : <i>/timer label FA Ceci est l'�tiquette du compteur FA</i>
<br />
<br />4) <u>Supprimer un compteur</u>
<br /><span class=\"comm\">/timer del &lt;nom&gt;</span>
<br />Exemple : <i>/timer del FA</i>
<br /><br /><b>Nota Bene</b>
<br />- Lors de l'utilisation des commandes <i>timer to</i> et <i>timer set</i>, la dur�e doit �tre d�finie au format <u>H:M</u> ou <u>HH:MM</u>, avec deux points (:) pour s�parer les heures et les minutes.
<br />- Les compteurs ne sont pas affich�s imm�diatement apr�s leur cr�ation. Ils ne le sont qu'apr�s leur initialisation, � l'aide des commandes <i>timer to</i> ou <i>timer set</i>.
</div>";

// Authentification
echo "<div class=\"help\"><b><u>Enregistrement du pseudo</u></b>
<br />L'acc�s � la <i>ShoutBox</i> n'est pas restreint et ne n&eacute;cessite pas de mot de passe. Vous pouvez cependant enregistrer votre pseudonyme � l'aide d'un mot de passe pour emp&ecirc;cher d'autres utilisateurs d'usurper votre identit&eacute;. Apr&egrave;s avoir enregistr&eacute; votre pseudonyme, vous devrez vous identifier � l'aide du mot de passe choisi avant de pouvoir &eacute;crire un message dans la ShoutBox ou utiliser les commandes d'information sur la ville.
<br /><br />1) <u>Enregistrer un pseudonyme</u>
<br /><span class=\"comm\">/register <i>&lt;mot de passe&gt;</i></span>
<br />Le pseudonyme enregistr&eacute; est celui utilis&eacute; par l'utilisateur qui tape la commande, avec prise en compte de la casse (majuscules et minuscules).
<br /><br />2) <u>S'identifier pour utiliser un pseudonyme enregistr&eacute;</u>
<br /><span class=\"comm\">/auth <i>&lt;mot de passe&gt;</i></span></div>";

?>