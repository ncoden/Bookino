<?php
	//FONCTIONS QUAND L'UTILISATEUR EST CONNECTE

//AJOUTER DISCUSSION
function script_discussion_ouvrir($donnees){
	global $db;
	global $user;
	global $datetime;
	
	$discussion_cle = decrypter($donnees['discussion_cle']);
	$securite_ok = false;
	
	if(substr($discussion_cle, 0, 3) == 'USE'){	
		//===== si utilisateur =====
		// créé discussion privee
		
		$discussion_type = 'use';
		$autreuser_cle = $discussion_cle;
		
		//verifier que l'utilisateur existe
		$autreuser = INFOS('user', ['cle'=>$autreuser_cle]);
		
		if($autreuser){
			$discussion_privee = false;
			
			//test s'il 'y a pas de discussion privee
			$req = $db->prepare('SELECT * FROM discussions_privees WHERE (idUtilisateur_1=? AND idUtilisateur_2=?) OR (idUtilisateur_1=? AND idUtilisateur_2=?)');
			$req->execute(array($user['id'], $autreuser['id'], $autreuser['id'], $user['id']));
			while($valeur = $req->fetch()){
				$discussion_privee = true;
				$discussion_privee_id = $valeur['idDiscussion_privee'];
				$discussion_privee_cle = $valeur['cleDiscussion'];
			}
			
			//si pas de discussion privee, cree
			if(!$discussion_privee){
				$discussion_cle = 'PRI'.md5('t-9*\ù'.$datetime.'3.xcç@'.$user['id'].'/5xR(&').md5('36*/é"'.$autreuser['id'].'7pM%^=');
				
				//cree la discussion
				INSERT('discussions_privees', 
					   array('cleDiscussion'=>$discussion_cle, 
							 'idUtilisateur_1'=>$user['id'], 
							 'idUtilisateur_2'=>$autreuser['id'], 
							 'date_lu_1'=>$datetime, 
							 'date_lu_2'=>$datetime
						)
				);
					  
				//recupere l'id de la discussion
				$sql_rep = SELECT('discussions_privees', array('cleDiscussion'=>$discussion_cle), array('idDiscussion_privee'));
				$discussion_id = $sql_rep[0]['idDiscussion_privee'];							
				
				//importe tous les messages libres
				UPDATE('messages', 
						array('idUtilisateur_envoi'=>$user['id'], 'idUtilisateur_recoi'=>$autreuser['id'], 'idDiscussion'=>''), 
						array('idUtilisateur_envoi'=>0, 'idUtilisateur_recoi'=>0, 'idDiscussion'=>$discussion_id)
					  );
				UPDATE('messages', 
						array('idUtilisateur_recoi'=>$user['id'], 'idUtilisateur_envoi'=>$autreuser['id'], 'idDiscussion'=>''), 
						array('idUtilisateur_recoi'=>0, 'idUtilisateur_envoi'=>0, 'idDiscussion'=>$discussion_id)
					  );
						  
			}else{
				$discussion_id = $discussion_privee_id;
				$discussion_cle = $discussion_privee_cle;
			};
			
			$securite_ok = true;
		};
	
	}elseif(substr($discussion_cle, 0, 3) == 'PRI'){
		//===== si discussion privee ===== 
		// afficher dans interface
		
		$discussion_type = 'pri';
		
		//verifi que la discussion existe et que l'on est membre
		$req = $db->prepare('SELECT idDiscussion_privee FROM discussions_privees WHERE cleDiscussion=? AND (idUtilisateur_1=? OR idUtilisateur_2=?)');
		$req->execute(array($discussion_cle, $user['id'], $user['id']));
		while($valeur = $req->fetch()){
			$discussion_id = $valeur['idDiscussion_privee'];
			$securite_ok = true;
		}
		
	}elseif(substr($discussion_cle, 0, 3) == 'DIS'){
		//===== si discussion de groupe ===== 
		// afficher dans interface
		
		$discussion_type = 'dis';
	
		//verifier que la discussion existe
		$sql_rep = SELECT('discussions', array('cleDiscussion'=>$discussion_cle), array('idDiscussion'));
		if(!empty($sql_rep)){
		
			//verifi si on est membre, sinon ajoute
			$discussion_id = $sql_rep[0]['idDiscussion'];
			$sql_rep = SELECT('discussions_membres', array('idDiscussion'=>$discussion_id, 'idUtilisateur'=>$user['id']), array('date'));
			if(empty($sql_rep)){
				INSERT('discussions_membres', array('idDiscussion'=>$discussion_id, 'idUtilisateur'=>$user['id'], 'date'=>$datetime));
			}else{
				$datetime = $sql_rep[0]['date'];
			};
			
			$securite_ok = true;
		};
	};
	
	//===== apres vérifications, fait actions communes =====
	if($securite_ok){

		if($discussion_type == 'dis'){
			$type = 'discussion';
		}else{
			$type = 'privee';
		};
		
		//recupere messages
		$sql_rep = SELECT('messages', array('idDiscussion'=>$discussion_id, 'type'=>$type));
		$messages = array();
		
		foreach($sql_rep as $index=>$valeur){
			$message_id = $valeur['idMessage'];
			//verifi la date pour discussion de groupe seulement
			if(($valeur['date_envoi']>$datetime) || ($discussion_type!='dis')){
				
				//recupere la cle de l'envoyeur
				$userenvoi_id = $valeur['idUtilisateur_envoi'];
				if(!isset($userenvoi_cle[$userenvoi_id])){
					$sql_rep = SELECT('utilisateurs', array('idUtilisateur'=>$userenvoi_id), array('cleUtilisateur, pseudo'));
					$userenvoi_cle[$userenvoi_id] = $sql_rep[0]['cleUtilisateur'];
					$userenvoi_pseudo[$userenvoi_id] = $sql_rep[0]['pseudo'];
				};
				
				//liste messages
				$messages[crypter($message_id)] = array('type'=>$type,
									'cleUtilisateur'=>crypter($userenvoi_cle[$userenvoi_id]),
									'pseudo'=>$userenvoi_pseudo[$userenvoi_id],
									'titre'=>$valeur['titre'],
									'contenu'=>$valeur['contenu'],
									'date_envoi'=>$valeur['date_envoi'],
									'lu'=>$valeur['lu']
									);
			};
		}
		
		//verifi si absent de l'interface et ajoute
		$sql_rep = SELECT('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>$type, 'id'=>$discussion_id));
		if(empty($sql_rep)){				
			INSERT('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>$type, 'id'=>$discussion_id, 'etat'=>'visible'));
		};
		
		return array('messages'=>$messages, 'discussion_cle'=>crypter($discussion_cle));
	};
}
//FERMER DISCUSSION
function script_discussion_fermer($donnees){
	global $db;
	global $user;
	$discussion_cle = decrypter($donnees['discussion_cle']);
	
	if(substr($discussion_cle, 0, 3) == 'PRI'){
		//verifi que la discussion existe
		$sql_rep = SELECT('discussions_privees', array('cleDiscussion'=>$discussion_cle), array('idDiscussion_privee'));
		if(!empty($sql_rep)){
			$discussion_id = $sql_rep[0]['idDiscussion_privee'];
			//supprime de l'interface
			DELETE('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'privee', 'id'=>$discussion_id));
			return 'true';
		};
	}else if(substr($discussion_cle, 0, 3) == 'DIS'){
		//verifi que la discussion existe
		$sql_rep = SELECT('discussions', array('cleDiscussion'=>$discussion_cle), array('idDiscussion'));
		if(!empty($sql_rep)){
			$discussion_id = $sql_rep[0]['idDiscussion'];
			//supprime de l'interface
			DELETE('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'discussion', 'id'=>$discussion_id));
			return 'true';
		};
	};
}
//AFFICHER DISCUSSION
function script_discussion_afficher($donnees){
	global $db;
	global $user;
	$discussion_cle = decrypter($donnees['discussion_cle']);
	
	if(substr($discussion_cle, 0, 3) == 'PRI'){
		//recupere l'id
		$sql_rep = SELECT('discussions_privees', array('cleDiscussion'=>$discussion_cle), array('idDiscussion_privee'));
		if(!empty($sql_rep)){
			$discussion_id = $sql_rep[0]['idDiscussion_privee'];
			//verifi si discussion existe et cree ou maj
			$sql_rep = SELECT('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'privee', 'id'=>$discussion_id));
			if(empty($sql_rep)){
				INSERT('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'privee', 'id'=>$discussion_id, 'etat'=>'affiche'));
			}else{
				UPDATE('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'privee', 'id'=>$discussion_id), array('etat'=>'affiche'));
			};
			return 'true';
		};
	}else if(substr($discussion_cle, 0, 3) == 'DIS'){
		//recupere l'id
		$sql_rep = SELECT('discussions', array('cleDiscussion'=>$discussion_cle), array('idDiscussion'));
		if(!empty($sql_rep)){
			$discussion_id = $sql_rep[0]['idDiscussion'];
			//verifi si discussion existe et cree ou maj
			$sql_rep = SELECT('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'discussion', 'id'=>$discussion_id));
			if(empty($sql_rep)){
				INSERT('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'discussion', 'id'=>$discussion_id, 'etat'=>'affiche'));
			}else{
				UPDATE('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'discussion', 'id'=>$discussion_id), array('etat'=>'affiche'));
			};
			return 'true';
		};
	};
}
//CACHER DISCUSSION
function script_discussion_cacher($donnees){
	global $db;
	global $user;
	$discussion_cle = decrypter($donnees['discussion_cle']);
	
	if(substr($discussion_cle, 0, 3) == 'PRI'){
		//recupere l'id
		$sql_rep = SELECT('discussions_privees', array('cleDiscussion'=>$discussion_cle), array('idDiscussion_privee'));
		if(!empty($sql_rep)){
			$discussion_id = $sql_rep[0]['idDiscussion_privee'];
			//verifi si discussion existe et cree ou maj
			$sql_rep = SELECT('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'privee', 'id'=>$discussion_id));
			if(empty($sql_rep)){
				INSERT('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'privee', 'id'=>$discussion_id, 'etat'=>'cache'));
			}else{
				UPDATE('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'privee', 'id'=>$discussion_id), array('etat'=>'cache'));
			};
			return 'true';
		};
	}else if(substr($discussion_cle, 0, 3) == 'DIS'){
		//recupere l'id
		$sql_rep = SELECT('discussions', array('cleDiscussion'=>$discussion_cle), array('idDiscussion'));
		if(!empty($sql_rep)){
			$discussion_id = $sql_rep[0]['idDiscussion'];
			//verifi si discussion existe et cree ou maj
			$sql_rep = SELECT('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'discussion', 'id'=>$discussion_id));
			if(empty($sql_rep)){
				INSERT('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'discussion', 'id'=>$discussion_id, 'etat'=>'cache'));
			}else{
				UPDATE('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'discussion', 'id'=>$discussion_id), array('etat'=>'cache'));
			};
			return 'true';
		};
	};
}
//DISCUSSION LUE
function script_discussion_lu($donnees){
	global $db;
	global $user;
	global $datetime;
	$discussion_cle = decrypter($donnees['discussion_cle']);
	
	if(substr($discussion_cle, 0, 3) == 'PRI'){
		//si discussion privee
		
		
		//regarde la date de vue
		$sql_rep = SELECT('discussions_privees', array('cleDiscussion'=>$discussion_cle));
		if(!empty($sql_rep)){
			if($sql_rep[0]['idUtilisateur_1'] == $user['id']){$date_lu = $sql_rep[0]['date_lu_1'];};
			if($sql_rep[0]['idUtilisateur_2'] == $user['id']){$date_lu = $sql_rep[0]['date_lu_2'];};
			$discussion_id = $sql_rep[0]['idDiscussion_privee'];
			
			//regarde le dernier message
			$sql_rep = SELECT('messages', array('idDiscussion'=>$discussion_id, 'type'=>'privee'));
			$taille = count($sql_rep);
			if($taille>0){
				$date_envoi = $sql_rep[$taille-1]['date_envoi'];
			}else{
				$date_envoi = '';
			};

			if(($date_envoi > $date_lu) || $date_envoi == ''){
			UPDATE('discussions_privees', 
				   array('idUtilisateur_1'=>$user['id'], 'cleDiscussion'=>$discussion_cle), 
				   array('date_lu_1'=>$datetime)
				   );
			UPDATE('discussions_privees', 
				   array('idUtilisateur_2'=>$user['id'], 'cleDiscussion'=>$discussion_cle), 
				   array('date_lu_2'=>$datetime)
				  );
			};
		};
		
		return 'true';
	}else if(substr($discussion_cle, 0, 3) == 'DIS'){
		//si duscussion publique
		
		//recupere l'id de la discussion
		$sql_rep = SELECT('discussions', array('cleDiscussion'=>$discussion_cle), array('idDiscussion'));
		if(!empty($sql_rep)){
			$discussion_id = $sql_rep[0]['idDiscussion'];
			UPDATE('discussions_membres', 
				   array('idUtilisateur'=>$user['id'], 'idDiscussion'=>$discussion_id), 
				   array('date_lu'=>$datetime)
				  );
			return 'true';
		};
	};	
}
//DISCUSSION EN TRAIN ECRIRE
function script_discussion_ecrire($donnees){
	global $db;
	global $user;
	$discussion_cle = decrypter($donnees['discussion_cle']);
	$user_ecrire = $donnees['ecrire'];
	
	if($user_ecrire=='oui'){
		$user_ecrire = '1';
	}else{
		$user_ecrire = '0';
	};
	
	if(substr($discussion_cle, 0, 3) == 'PRI'){
		//si discussion privee
		
		UPDATE('discussions_privees', 
			   array('idUtilisateur_1'=>$user['id'], 'cleDiscussion'=>$discussion_cle), 
			   array('ecrire_1'=>$user_ecrire)
			   );
		UPDATE('discussions_privees', 
			   array('idUtilisateur_2'=>$user['id'], 'cleDiscussion'=>$discussion_cle), 
			   array('ecrire_2'=>$user_ecrire)
			  );
		return 'true';
	};
}
//TEST DISCUSSION LUE & EN TRAIN ECRIRE
function script_discussion_activites($donnees){
	global $db;
	global $user;
	$discussion_cle = decrypter($donnees['discussion_cle']);
	$discussion = array();
	
	if(substr($discussion_cle, 0, 3) == 'PRI'){
		//si discussion privee
		$sql_rep = SELECT('discussions_privees', array('cleDiscussion'=>$discussion_cle));
		if(!empty($sql_rep)){
			//recupere date derniere lecture
			$discussion_id = $sql_rep[0]['idDiscussion_privee'];
			if($sql_rep[0]['idUtilisateur_1'] == $user['id']){
				$autreuser_datelu = $sql_rep[0]['date_lu_2']; 
				$autre_user_ecrire = $sql_rep[0]['ecrire_2'];
			};
			if($sql_rep[0]['idUtilisateur_2'] == $user['id']){
				$autreuser_datelu = $sql_rep[0]['date_lu_1'];
				$autre_user_ecrire = $sql_rep[0]['ecrire_1'];
			};
			
			if($autreuser_datelu != '0000-00-00 00:00:00'){
				//selectionne dernier message envoyé qui est lu
				$messages_lus = true;
				$req = $db->prepare('SELECT * FROM messages WHERE idDiscussion=? AND idUtilisateur_envoi=? AND date_envoi>=?');
				if(!$req->execute(array($discussion_id, $user['id'], $autreuser_datelu))){echo('erreur_bdd');exit();};
				while($valeur = $req->fetch()){
					$messages_lus = false;
				}
				if($messages_lus){
					$discussion['date_lu'] = date_parse($autreuser_datelu);
				}else{
					$discussion['date_lu'] = '';
				};
			}else{
				$discussion['date_lu'] = '';
			};
			
			if($autre_user_ecrire=='0'){$discussion['ecrire'] = 'non';};
			if($autre_user_ecrire=='1'){$discussion['ecrire'] = 'oui';};
		};
	};
	
	return $discussion;
}

//AFFICHER MENU
function script_menu_afficher($donnees){
	global $db;
	global $user;
	$menu = $donnees['menu'];
	
	if($menu=='amis' || $menu=='messages' || $menu=='infos' || $menu=='general'){
		//verifi si menu existe, et créé ou maj
		$sql_rep = SELECT('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'menu'));
		if(empty($sql_rep)){
			INSERT('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'menu', 'etat'=>$menu));
		}else{
			UPDATE('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'menu'), array('etat'=>$menu));
		};
		
		$user['interfaces']['menu'] = $menu;
		$_SESSION['user']['interfaces']['menu'] = $menu;
		return 'true';
	};
}
//CACHER MENU
function script_menu_cacher($donnees){
	global $db;
	global $user;
	
	DELETE('interfaces', array('idUtilisateur'=>$user['id'], 'type'=>'menu'));
	unset($GLOBALS['user']['interfaces']['menu']);
	unset($_SESSION['user']['interfaces']['menu']);
	return 'true';
}

//SUIVRE USER
function script_autreuser_suivre($donnees){
	global $db;
	global $user;
	$autreuser_cle = decrypter($donnees['autreuser_cle']);

	//verifier que l'utilisateur existe
	$sql_rep = SELECT('utilisateurs', array('cleUtilisateur'=>$autreuser_cle), array('idUtilisateur'));
	if(!empty($sql_rep)){
		$autreuser_id = $sql_rep('idUtilisateur');
		
		//regarde si la personne me suis
		$sql_rep = SELECT('lecteurs', array('idUtilisateur_1'=>$autreuser_id, 'idUtilisateur_2'=>$user['id'], 'type'=>'suis'));
		if(!empty($sql_rep)){
			//si oui > deviens amis
			UPDATE('lecteurs', array('idUtilisateur_1'=>$autreuser_id, 'idUtilisateur_2'=>$user['id'], 'type'=>'suis'), array('type'=>'amis'));
		}else{
			//sinon, ajoute
			INSERT('lecteurs', array('idUtilisateur_1'=>$user['id'], 'idUtilisateur_2'=>$autreuser_id, 'type'=>'suis', 'date'=>$datetime));
		};

		return 'true';
	};
}
//NE PLUS SUIVRE USER
function script_autreuser_plussuivre($donnees){
	global $db;
	global $user;
	$autreuser_cle = decrypter($donnees['autreuser_cle']);
	
	//verifier que l'utilisateur existe
	$sql_rep = SELECT('utilisateurs', array('cleUtilisateur'=>$autreuser_cle), array('idUtilisateur'));
	if(!empty($sql_rep)){
		$autreuser_id = $sql_rep[0]['idUtilisateur'];
		
		//regarde les liaisons
		$req = $db->prepare('SELECT * FROM lecteurs WHERE (idUtilisateur_1=? AND idUtilisateur_2=?) OR (idUtilisateur_1=? AND idUtilisateur_2=?)');
		$req->execute(array($user['id'], $autreuser_id, $autreuser_id, $user['id']));  
		while($valeur = $req->fetch()){
			$liaison_id = $valeur['idLecteur'];
			$liaison_type = $valeur['type'];
			
			//si on est amis > l'autre personne nous suis
			if($liaison_type == 'amis'){
				UPDATE('lecteurs', 
					   array('idLecteur'=>$liaison_id), 
					   array('idUtilisateur_1'=>$autreuser_id, 'idUtilisateur_2'=>$user['id'], 'type'=>'suis')
					  );
				return 'true';
			};
			
			//si je ne suis que lecteur > supprime
			if($liaison_type == 'suis'){
				DELETE('lecteurs', array('idLecteur'=>$liaison_id));
				return 'true';
			};
		}
	};
}

//ENVOYER MESSAGE
function script_message_envoyer($donnees){
	global $db;
	global $user;
	global $datetime;
	$contenu = $donnees['contenu'];
	$discussion_cle = decrypter($donnees['discussion_cle']);
	
	//si discussion
	if(substr($discussion_cle, 0, 3) == 'DIS'){
		
		//verifier que la discussion existe
		$sql_rep = SELECT('discussions', array('cleDiscussion'=>$discussion_cle), array('idDiscussion'));
		if(!empty($sql_rep)){
			$discussion_id = $sql_rep[0]['idDiscussion'];
			
			//verifi si on est membre et ajoute message
			$discussion_id = $sql_rep('idDiscussion');
			$sql_rep = SELECT('discussions_membres', array('idDiscussion'=>$discussion_id, 'idUtilisateur'=>$user['id']), array('date'));
			if(!empty($sql_rep)){
				INSERT('messages', 
					   array('idDiscussion'=>$discussion_id, 
							 'idUtilisateur_envoi'=>$user['id'], 
							 'type'=>'discussion', 
							 'contenu'=>$contenu, 
							 'date_envoi'=>$datetime, 
							));
				return 'true';
			};
		};
	}else if(substr($discussion_cle, 0, 3) == 'PRI'){
		//si disussion privee
		
		//verifier que la discussion privee existe
		$sql_rep = SELECT('discussions_privees', array('cleDiscussion'=>$discussion_cle));
		if(!empty($sql_rep)){
			$discussion_id = $sql_rep[0]['idDiscussion_privee'];
			
			//verifi si on est membre et ajoute message
			if(($user['id']==$sql_rep[0]['idUtilisateur_1']) || ($user['id']==$sql_rep[0]['idUtilisateur_2'])){
				INSERT('messages', 
					   array('idDiscussion'=>$discussion_id, 
							 'idUtilisateur_envoi'=>$user['id'], 
							 'type'=>'privee', 
							 'contenu'=>$contenu, 
							 'date_envoi'=>$datetime, 
							));
				return 'true';
			};
		};
	};
}

//RECUPERER NOTIFS
function script_notifs_recuperer($donnees){
	global $user;
	global $l;
	global $langue;
	$notifs = array();
	
	$init = $donnees['_init'];
	
	if($init == 'true'){
		//parcoure toutes les notifications
		$sql_rep = SELECT('notifications', ['idUtilisateur'=>$user['id']]);
	}else{
		//parcoure les nouvelles notifications
		$sql_rep = SELECT('notifications', ['idUtilisateur'=>$user['id'], 'etat'=>'nonvu']);
	};
	
	UPDATE('notifications', ['idUtilisateur'=>$user['id'], 'etat'=>'nonvu'], ['etat'=>'chargé']);
	
	foreach($sql_rep as $valeur){
		//defini valeurs
		$notif_id = $valeur['idNotification'];
		$type = $valeur['type'];
		$html = '';
		$etat = $valeur['etat'];
		
		//met en forme
		if($type == 'paragraphe_alerte'){
			$autreuser = INFOS('user', ['id'=>$valeur['id2']]);
			$livre = INFOS('livre', ['id'=>$valeur['id']]);
			if($autreuser && $livre){
				$html = '	<DIV class="C_user"><IMG class="image" src="http://f.bookino.org/datas/users/'.crypter($autreuser['cle']).'/profil_45.png"/></DIV>
							<DIV class="texte">
								<A href="/'.$langue.'/'.$l['P_NOM:UTILISATEUR'].'/'.$autreuser['pseudo'].'" class="C_nomuser">
								<DIV class="nom">'.$autreuser['pseudo'].'</DIV>
								</A> attend pour écrire dans 
								<A href="/'.$langue.'/'.$l['P_NOM:LIVRE'].'/'.$livre['titre_simple'].'" class="C_nomuser">'.$livre['titre'].'</A>
							</DIV>';
			};
		};
		
		if($valeur['important'] == 1){$important = true;}else{$important = false;};
		if($valeur['visible'] == 1){$visible = true;}else{$visible = false;};
		
		//ecrit les notifs
		if($html != ''){
			$notifs[crypter($notif_id)] = [
				'type' => $type,
				'html' => $html,
				'etat' => $etat,
				'important' => $important,
				'visible' => $visible,
			];
		};
	}
	
	return ['notifs'=>$notifs];
}
//RECUPERER MESSAGE
function script_messages_recuperer($donnees){
	global $db;
	global $user;
	global $datetime;
	
	$init = $donnees['_init'];
	if($init == 'true'){
		$nom_date = 'date_lu';
	}else{
		$nom_date = 'date_charge';
	};
	
	$requete = 'SELECT * FROM messages WHERE ((';
	$discussionenvoi_cle = array();
	$userenvoi_cle = array();
	
	//construit la requete
	//(liste les differentes discussions et la date du dernier message lu)
	$sql_rep = SELECT('discussions_membres', array('idUtilisateur'=>$user['id']), array('idDiscussion', $nom_date));
	foreach($sql_rep as $valeur){
		$requete = $requete.'(idDiscussion="'.$valeur['idDiscussion'].'" AND date_envoi>"'.$valeur[$nom_date].'") OR ';
	}
	
	//construit requete et 'lus'
	$req = $db->prepare('SELECT * FROM discussions_privees WHERE idUtilisateur_1=? OR idUtilisateur_2=?');
	if(!$req->execute(array($user['id'], $user['id']))){echo('erreur_bdd');exit();};
	while($valeur = $req->fetch()){
		$discussion_id = $valeur['idDiscussion_privee'];
		if($valeur['idUtilisateur_1']==$user['id']){$user_datecharge=$valeur[$nom_date.'_1']; };
		if($valeur['idUtilisateur_2']==$user['id']){$user_datecharge=$valeur[$nom_date.'_2']; };
		$requete = $requete.'(idDiscussion="'.$discussion_id.'" AND date_envoi>"'.$user_datecharge.'") OR ';
	}
	
	if($requete != 'SELECT * FROM messages WHERE ('){
		$requete = substr($requete, 0, -4);
		$requete = $requete.') OR (idUtilisateur_recoi=? AND type=? AND lu=?)) AND idUtilisateur_envoi<>"'.$user['id'].'"';
	}else{
		$requete = 'SELECT * FROM messages WHERE idUtilisateur_recoi=? AND type=? AND lu=?';
	};
	
	//selectionne les messages libres non lus, 
	//ou dont la date d'envoi est passé à la date de derniere lecture (pour groupes et privees)
	$messages = array();
	$messages_lus = array();
	
	$req = $db->prepare($requete);
	$req->execute(array($user['id'], 'libre', 0));
	while($valeur = $req->fetch()){
		$type = $valeur['type'];
		$message_id = $valeur['idMessage'];
		$discussion_id = $valeur['idDiscussion'];
		
		//recupere la cle de l'envoyeur
		$userenvoi_id = $valeur['idUtilisateur_envoi'];
		if(!isset($userenvoi_cle[$userenvoi_id])){
			$sql_rep = SELECT('utilisateurs', array('idUtilisateur'=>$userenvoi_id), array('cleUtilisateur', 'pseudo'));
			$userenvoi_cle[$userenvoi_id] = $sql_rep[0]['cleUtilisateur'];
			$userenvoi_pseudo[$userenvoi_id] = $sql_rep[0]['pseudo'];
		};
		
		//recupere la cle de la discussion privee
		if(!isset($discussionenvoi_cle[$discussion_id]) && $type=='privee' && $discussion_id!=0){
			$discussion = INFOS('discussion_privee', ['id'=>$discussion_id]);
			if($discussion){
				$discussionenvoi_cle[$discussion_id] = $discussion['cle'];
				
				//maj date des messages affichés
				if($discussion['idUtilisateur_1'] == $user['id']){$nom = 'date_charge_1';};
				if($discussion['idUtilisateur_2'] == $user['id']){$nom = 'date_charge_2';};
				if(isset($nom)){UPDATE('discussions_privees', ['idDiscussion_privee'=>$discussion_id], [$nom=>$datetime]);};
			};
		};
		
		//recupere la cle de la discussion publique
		if(!isset($discussionenvoi_cle[$discussion_id]) && $type=='publique' && $discussion_id!=0){
			$sql_rep = SELECT('discussions', array('idDiscussion'=>$discussion_id), array('cleDiscussion'));
			if(!empty($sql_rep)){
				$discussionenvoi_cle[$discussion_id] = $sql_rep[0]['cleDiscussion'];
				
				//maj date des messages affichés
				UPDATE('discussion_membres', ['idDiscussion'=>$discussion_id, 'idUtilisateur'=>$user['id']], ['date_charge'=>$datetime]);
			};
		};
		
		if($type=='libre'){
			$envoi_discussioncle = 0;
			//maj date des messages affichés
			UPDATE('messages', ['idMessage'=>$message_id], ['etat'=>'charge']);
		}else{
			$envoi_discussioncle = crypter($discussionenvoi_cle[$discussion_id]);
		};
		
		//liste les messages
		$messages[crypter($message_id)] = array('type'=>$type,
							'cleDiscussion'=>$envoi_discussioncle,
							'cleUtilisateur'=>crypter($userenvoi_cle[$userenvoi_id]),
							'pseudo'=>$userenvoi_pseudo[$userenvoi_id],
							'titre'=>$valeur['titre'],
							'contenu'=>$valeur['contenu'],
							'date_envoi'=>$valeur['date_envoi'],
							);
	}
	
	return array('messages'=>$messages);			
}
//RECUPERER CONNECTES
function script_membres_recuperer($donnees){
	global $db;
	global $user;

	$discussions_privees = [];	
	$liste_connaissances = [];

	$connaissances = lister_connaissances($user['id']);
	
	//tri des lecteurs connectés
	foreach($connaissances as $nom=>$liste){
		$connectes = [];
		$absents = [];
		$deconnectes = [];
	
		foreach($liste as $id){
			$autreuser = INFOS('user', ['id'=>$id]);
			$cle = crypter($autreuser['cle']);
			$enligne = $autreuser['enligne'];
			$decalage = langue_date($autreuser['date_activite'])['decalage'];
			
			if($enligne && $decalage<30){
				//si connecte
				$connectes[$cle] = ['cleDiscussion' => $cle, 
									'pseudo' => $autreuser['pseudo'], 
									'enligne' => $autreuser['enligne'], 
									'date_activite' => date_parse($autreuser['date_activite'])
				];
			}elseif($enligne && $decalage>=30){
				//si absent
				$absents[$cle] = ['cleDiscussion' => $cle, 
									'pseudo' => $autreuser['pseudo'], 
									'enligne' => $autreuser['enligne'], 
									'date_activite' => date_parse($autreuser['date_activite'])
				];
			}else{
				//si deconnecté
				$deconnectes[$cle] = ['cleDiscussion' => $cle, 
										'pseudo' => $autreuser['pseudo'], 
										'enligne' => $autreuser['enligne'], 
										'date_activite' => date_parse($autreuser['date_activite'])
				];
			};
		}
		
		$liste_connaissances[$nom] = array_merge($connectes, $absents, $deconnectes);
	}
		
	//liste les discussions privées
	$req = $db->prepare('SELECT * FROM discussions_privees WHERE (idUtilisateur_1=? OR idUtilisateur_2=?)');
	$req->execute(array($user['id'], $user['id']));
	while($valeur = $req->fetch()){
		$discussion_user1 = $valeur['idUtilisateur_1'];
		$discussion_user2 = $valeur['idUtilisateur_2'];
		$discussion_cle = $valeur['cleDiscussion'];
		if($user['id'] == $discussion_user1){$discussions_privees[$discussion_user2] = $discussion_cle;};
		if($user['id'] == $discussion_user2){$discussions_privees[$discussion_user1] = $discussion_cle;};
	}
	
	//rassemble les groupes et discussions
	//pour les lecteurs
	foreach($liste_connaissances as $nom=>$liste){
		foreach($liste as $cle=>$valeur){
			//recupere l'id de l'user
			$autreuser = INFOS('user', ['cle' => decrypter($cle)]);
			$id = $autreuser['id'];
			
			if(isset($discussions_privees[$id])){
				$liste_connaissances[$nom][$cle]['cleDiscussion'] = crypter($discussions_privees[$id]);
			};
		}
	}
	
	
	//envoi la liste
	return $liste_connaissances;
}

//NOTIFS VUES
function script_notifs_vues($donnees){
	global $user;
	
	UPDATE('notifications', ['idUtilisateur'=>$user['id'], 'etat'=>'chargé'], ['etat'=>'vu']);
	
	return true;
}
//SUPPRIMER NOTIF
function script_notif_supprimer($donnees){
	global $user;
	
	$return = false;
	$notif_id = decrypter($donnees['notif_cle']);
	
	//verifi que la notification existe et appartient
	$sql_rep = SELECT('notifications', ['idNotification'=>$notif_id], ['idUtilisateur']);
	if(count($sql_rep)>0 && $sql_rep[0]['idUtilisateur']==$user['id']){
		DELETE('notifications', ['idNotification'=>$notif_id]);
		$return = true;
	};
	
	return $return;
}

?>