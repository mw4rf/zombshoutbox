<?php

class Rooms
{
	var $user;
		
	// Constructeur
	function __construct()
	{
		// Initialisation de l'utilisateur
		if(!isset( $_COOKIE['user']) or empty($_COOKIE['user']))
			die("Vous devez vous connecter.");
		else
			$this->user =  addslashes($_COOKIE['user']);
	}
	
	// Création d'une nouvelle salle
	function newRoom($name,$pwd=false)
	{
		$name = addslashes($name);
		if(!$pwd) { $private = 0; $pwd = ""; } else { $private = 1; $pwd = addslashes($pwd); }
		$sql = "INSERT INTO rooms VALUES('','$name','$private','$pwd', '1')";
		query($sql);
	}
	
	// Récupérer les salles de l'utilisateur
	function getRooms()
	{
		$user = $this->user;
		$sql = "SELECT * FROM users_rooms WHERE user='$user' AND joined = '1'";
		
		// Si l'utilisateur n'existe pas dans la table, l'assigner à la salle 0
		$count = num_rows($sql);
		if($count < 1) return 0;
		
		// Créer un tableau avec les salles de l'utilisateur
		$req = query($sql);
		$rooms = array();
		while($data = mysql_fetch_assoc($req))
			$rooms[] = $data['room_id'];
		return $rooms;
	}
	
	// Faire rentrer l'utilisateur dans une salle
	function joinRoom($room_id,$pwd=false,$user=false)
	{		
		if(!$user) $user = $this->user;
		
		// Vérifier que le bon mot de passe a été donné
		if($this->isRoomPrivate($room_id))
		{
			if(!$pwd) return false;
			$pass = $this->getRoomPassword($room_id);
			if($pass != $pwd) return false;
		}

		// Rendre les autres salles inactives
		$sql = "UPDATE users_rooms SET active='0' WHERE user='$user'";
		query($sql);
		
		// Si cette salle a été désactivée, la réactiver
		if(!$this->isRoomActive($room_id))
			$this->openRoom($room_id);
			
		// Utilisateur déjà dans la salle : la rendre active
		if($this->isUserInRoom($room_id,$user))
		{
			$sql = "UPDATE users_rooms SET active='1' WHERE user='$user' AND room_id='$room_id'";
			query($sql);
		}
		// Utilisateur absent de la salle : la rejoindre
		else
		{	
			// L'utilisateur a déjà été dans cette salle : mettre à jour la bdd
			if($this->hasUserBeenInRoom($room_id,$user))
			{
				$sql = "UPDATE users_rooms SET active='1', joined='1' WHERE user='$user' AND room_id='$room_id'";
				query($sql);
			}
			// L'utilisateur n'a jamais été dans cette salle : créer une nouvelle ligne
			else
			{
				$sql = "INSERT INTO users_rooms VALUES('','$user','$room_id','1','1')";
				query($sql);
			}
			// Avertir que l'utilisateur vient de rejoindre la salle
			$msg = "$user vient de rejoindre le salon ".$this->getRoomName($room_id);
			$sql = "INSERT INTO messages VALUES ('','$msg','".time()."','SYSTEM', '1', '0', 'no','$room_id')";
			query($sql);
		}
		return true;
	}
	
	// Définit la salle active
	function setActiveRoom($room_id,$user=false)
	{
		if(!$user) $user = $this->user;
		
		// Vérifier que l'utilisateur a bien rejoint le sallon
		if(!$this->isUserInRoom($room_id,$user))
		return false;
		
		// Rendre les autres salles inactives
		$sql = "UPDATE users_rooms SET active='0' WHERE user='$user'";
		query($sql);
		
		// Activer la salle
		$sql = "UPDATE users_rooms SET active='1' WHERE user='$user' AND room_id='$room_id'";
		query($sql);
		return true;
	}
	
	// Récupère le mot de passe du salon
	function getRoomPassword($room_id)
	{
		$sql = "SELECT * FROM rooms WHERE id='$room_id'";
		$req = query($sql);
		while($data = mysql_fetch_assoc($req))
		{
			$private = $data['private'];
			if(!$private) return false;
			
			$password = $data['password'];
			if(empty($password)) return false;
			return stripslashes($password);
		}
		return false;
	}
	
	// Salon privé ?
	function isRoomPrivate($room_id)
	{
		$sql = "SELECT * FROM rooms WHERE id='$room_id'";
		$req = query($sql);
		while($data = mysql_fetch_assoc($req))
		{
			$private = $data['private'];
			if($private == '1') return true;
			else return false;
		}
		return false;
	}
	
	// Récupère la salle active de l'utilisateur
	function getActiveRoom($user=false)
	{
		if(!$user) $user = $this->user;
		$sql = "SELECT * FROM users_rooms WHERE user='$user' AND ( active='1' AND joined = '1' )";
		
		// Si aucune salle active : salle publique
		$count = num_rows($sql);
		if($count < 1) return 0;
		
		// Sinon, rechercher la salle active
		$req = query($sql);
		$data = mysql_fetch_assoc($req);
		return $data['room_id'];
	}
	
	// Récupère les salles dans lesquelles l'utilisateur se trouve
	function getJoinedRooms($user=false)
	{
		if(!$user) $user = $this->user;
		$sql = "SELECT * FROM users_rooms WHERE user='$user' AND joined = '1'";
		
		// Si aucune : seulement la salle publique
		$count = num_rows($sql);
		if($count < 1) return 0;
		
		// Sinon, créer un tableau
		$rooms = array();
		$req = query($sql);
		while($data = mysql_fetch_assoc($req))
			$rooms[] = $data['room_id'];
		return $rooms;
	}
	
	// Faire sortir l'utilisateur d'une salle
	function leaveRoom($room_id,$user=false)
	{
		if(!$user) $user = $this->user;
		$sql = "UPDATE users_rooms SET joined = '0', active = '0' WHERE user='$user' AND room_id='$room_id'";
		query($sql);
		// Supprimer la salle si elle est désormais vide
		if($this->countRoomMembers($room_id) < 1)
			$this->deleteRoom($room_id);
	}
	
	// Supprimer la salle
	function deleteRoom($room_id)
	{
		$sql = "UPDATE rooms SET active = '0' WHERE id='$room_id'";
		query($sql);
	}
	
	// Retourne le nom de la salle
	function getRoomName($room_id)
	{
		if($room_id == 0) return "Public";
		
		$sql = "SELECT * FROM rooms WHERE id='$room_id'";
		
		$count = num_rows($sql);
		if($count < 1) return 0;
		
		$req = query($sql);
		$data = mysql_fetch_assoc($req);
		$room = $data['room'];
		return $room;
	}
	
	// Afficher la liste des salles
	function getRoomsName($active=true,$id=false)
	{
		if($active) $sql = "SELECT * FROM rooms WHERE active = '1'";
		else $sql = "SELECT * FROM rooms";
		$req = query($sql);
		$rooms = array();
		$index = 0;
		while($data = mysql_fetch_assoc($req))
		{
			if(!$id)
				$rooms[] = $data['room'];
			else
			{
				$rooms[$index][0] = $data['id'];
				$rooms[$index][1] = $data['room'];
				$index++;
			}
		}
		return $rooms;
	}
	
	// Compte le nombre d'utilsateurs d'une salle
	function countRoomMembers($room_id)
	{
		$sql = "SELECT * FROM users_rooms WHERE room_id = '$room_id' AND joined = '1'";
		return num_rows($sql);
	}
	
	// Obtient les membres de la salle
	function getRoomMembers($room_id,$joined=false,$active=false)
	{
		if($joined) $joined = "AND joined='1'"; else $joined = '';
		if($active) $active = "AND active='1'"; else $active = '';
		$sql = "SELECT * FROM users_rooms WHERE room_id = '$room_id' $joined $active";
		$req = query($sql);
		$members = array();
		while($data = mysql_fetch_assoc($req))
			$members[] = $data['user'];
		return $members;
	}
	
	// L'utilisateur est-il dans cette salle ?
	function isUserInRoom($room_id,$user=false)
	{
		if($room_id == 0) return true;
		if(!$user) $user = $this->user;
		$sql = "SELECT * FROM users_rooms WHERE room_id='$room_id' AND user='$user' AND joined = '1'";
		$count = num_rows($sql);		
		if($count < 1) return false;
		else return true;
	}
	
	// L'utilisateur a-t-il été dans cette salle ?
	function hasUserBeenInRoom($room_id,$user=false)
	{
		if(!$user) $user = $this->user;
		$sql = "SELECT * FROM users_rooms WHERE room_id='$room_id' AND user='$user'";
		$count = num_rows($sql);		
		if($count < 1) return false;
		else return true;
	}
	
	// Les deux utilisateurs sont-ils dans la même salle ?
	function sameActiveRoom($user1,$user2=false)
	{
		if(!$user2) $user2 = $this->user;
		$a1 = $this->getActiveRoom($user1);
		$a2 = $this->getActiveRoom($user2);
		
		if($a1 == $a2)
			return true;
		else
			return false;
	}
	
	// Déterminer si la salle avec le nom donné existe ou non
	function isRoom($name)
	{
		$sql = "SELECT * FROM rooms WHERE room='$name'";
		$count = num_rows($sql);
		if($count < 1) return false; 
		else return true;
	}
	
	// Détermine si une salle est active
	function isRoomActive($room_id)
	{
		$sql = "SELECT * FROM rooms WHERE id='$room_id' AND active='1'";
		$count = num_rows($sql);
		if($count < 1) return false; 
		else return true;
	}
	
	// Récupère l'id d'une salle
	function getRoomId($name)
	{
		$sql = "SELECT * FROM rooms WHERE room='$name'";
		$req = query($sql);
		$data = mysql_fetch_assoc($req);
		return $data['id'];
	}
	
	// Définir le nom du salon
	function renameRoom($room_id,$name="room")
	{
		if(!is_numeric($room_id)) return false;
		$name = addslashes($name);
		$sql = "UPDATE rooms SET room='$name' WHERE id='$room_id'";
		query($sql);
		return true;
	}
	
	// Fermer un salon
	function closeRoom($room_id)
	{
		// Virer les utilisateurs
		$sql = "UPDATE users_rooms SET joined = '0', active = '0' WHERE room_id='$room_id'";
		query($sql);
		
		// Fermer le salon
		$sql = "UPDATE rooms SET active = '0' WHERE id='$room_id'";
		query($sql);
		
		return true;
	}
	
	// Ouvrir le salon
	function openRoom($room_id)
	{
		$sql = "UPDATE rooms SET active = '1' WHERE id='$room_id'";
		query($sql);
		
		return true;
	}
	
	// Publiciser un salon
	function publicizeRoom($room_id)
	{
		$sql = "UPDATE rooms SET private = '0', password='' WHERE id='$room_id'";
		query($sql);
		return true;
	}
	
	// Privatiser un salon
	function privatizeRoom($room_id,$password='password')
	{
		$password = addslashes($password);
		$sql = "UPDATE rooms SET private = '1', password='$password' WHERE id='$room_id'";
		query($sql);
		return true;
	}
	
}



?>