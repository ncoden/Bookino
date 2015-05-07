<?php
	//TEST DES PARAMETRES D'INSCRIPTION POUR JAVASCRIPT

//TEST DU PSEUDO
function script_inscription_pseudo($donnees){
	global $db;
	$pseudo = $donnees[crypter('register_pseudo')];
	$echo='false';
	$sql_rep = SELECT('utilisateurs', array('pseudo'=>$pseudo));
	if(!empty($sql_rep)){
		return false;
	}else{
		return true;
	};
}
//TEST DE L'EMAIL
function script_inscription_email($donnees){
	global $db;
	$email = $donnees[crypter('register_email')];
	
	$sql_rep = SELECT('utilisateurs', array('email'=>$email));
	if(!empty($sql_rep)){
		return false;
	}else{
		return true;
	};
}

//TEST POUR CHANGER EMAIL
function script_inscription_testchangeremail($donnees){
	global $db;
	$email = $donnees[crypter('changeremail_nemail')];
	$autreuser['cle'] = decrypter($donnees[crypter('changeremail_cleuser')]);
	$echo = true;
	
	$req = $db->prepare('SELECT * FROM utilisateurs WHERE email=? AND cleUtilisateur<>?');
	$req->execute(array($email, $autreuser['cle']));  
	while($row = $req->fetch()){$echo = false;}
	
	return $echo;
}

//RENVOYER EMAIL
function script_inscription_renvoyeremail($donnees){
	global $db;
	global $l;
	global $langue;
	$autreuser['cle'] = decrypter($donnees[crypter('renvoyeremail_cleuser')]);
	$echo = false;
	
	$sql_rep = SELECT('utilisateurs', array('cleUtilisateur'=>$autreuser['cle'],'etat'=>'valide_email'), array('email','pseudo'));
	$autreuser_existe = !empty($sql_rep);

	//si l'utilisateur est bon, renvoi le mail
	if($autreuser_existe){
		$email = $sql_rep[0]['email'];
		$pseudo = $sql_rep[0]['pseudo'];
		
		$subject = $pseudo.' - comfirmation de votre inscription';
		$headers = 'From: bookino.org <noreply@bookino.org>'."\r\n";
		$headers .= 'Mime-Version: 1.0'."\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8'."\r\n";
		$headers .= "\r\n";
		$msg = $l['M:BIENVENUEBOO'].'<BR>
		<A href="http://bookino.org/'.$langue.'/'.$l['P_NOM:INSCRIPTION'].'?'.
		crypter('action').'='.crypter('valider_email').'&'.crypter('valideremail_cleuser').'='.crypter($autreuser['cle']).'
		">'.$l['M:VALIDER'].'</A>';

		if(mail($email, $subject, $msg, $headers)){$echo = true;};
	}else{
		$echo = 'erreur - user doesn\'t exist';
	};
	
	return $echo;
}

//CHANGER EMAIL
function script_inscription_changeremail($donnees){
	global $db;
	$autreuser['cle'] = decrypter($donnees[crypter('changeremail_cleuser')]);
	$nouvel_email = $donnees[crypter('changeremail_nemail')];
	$echo = false;
	
	//test si l'email est bon
	if($nouvel_email!='' && strlen($nouvel_email)<256 && filter_var($nouvel_email, FILTER_VALIDATE_EMAIL)){
		
		//test si l'email est deja pris
		$sql_rep = SELECT('utilisateurs', array('email'=>$nouvel_email));
		if(empty($sql_rep)){
		
			$sql_rep = SELECT('utilisateurs', array('cleUtilisateur'=>$autreuser['cle'], 'etat'=>'valide_email'));
			if(!empty($sql_rep)){
				$req = $db->prepare('UPDATE utilisateurs SET email=? WHERE cleUtilisateur=? AND etat=?');
				$req->execute(array($nouvel_email, $autreuser['cle'], 'valide_email'));
				$echo = true;
			}else{
				$echo = 'erreur - user doesn\'t exist';
			};
		}elseif($sql_rep[0]['cleUtilisateur'] == $autreuser['cle']){
			$echo = true;
		};
	};
	
	return $echo;
}

?>