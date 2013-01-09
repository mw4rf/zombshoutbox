<?php
$dbhost = "localhost:/Applications/MAMP/tmp/mysql/mysql.sock"; /* L'adresse du serveur */
$dblogin = "root"; /* Votre nom d'utilisateur */
$dbpassword = "mdp"; /* Votre mot de passe */
$dbbase = "gflorimond_hordes_shoutbox"; /* Le nom de la base */

// Domaine d'installation de la shoutbox, pour les cookies
$domain = "";

// Emplacement de la shoutbox sur le serveur
$rootpath = "/Volumes/Documents/Sites/VALHALLA/hordes/shoutbox/trunk/";

// Nom de l'appli
$sitename = "&#x2192;&nbsp;Zomb'ShoutBox&nbsp;&#x2190;";

// Twitter
$twitter_user = 'hordesmeta';
$twitter_pass = 'faf8ik7wurv6es2za';
$num_tweets = 5; // nombre de tweets à afficher

// Combien de messages afficher en même temps ?
$msg_limit = 15;

// Log des requêtes ?
$logqueries = false;

// Smileys
$smiley_in = 	array(" :)", " :(", " ;)", " :o", " :D", " :hmm", " :|", " :grr", " :zzz", " :wink", " :horreur", " :zhead", " :erk", " :home", " :porte", " :puits", " :eau", " :zombie", " :humain", " :soin", " :drogue", " :dig", " :mort", " :gard", " :eclair", " :collec", " :hab", " :os", " :fleche", " :sac", " :ame", " :atelier", " :!!", " :pa", " :ban", " :chat", " :calim", " :annu", " :evo", " :arma");
$smiley_out = 	array(	"smile", "sad", "blink", "surprise", "lol", "exas", "neutral", "rage", "sleep", "wink", "horror", "zhead", "sick", "home", "door", "well", "water", "zombie", "human", "heal", "drug", "dig", "death", "guard", "ranger", "collec", "basic", "bone", "arrow", "bag", "ghost", "refine", "warning", "pa", "ban", "chat", "calim", "sites", "city_up", "arma");
?>