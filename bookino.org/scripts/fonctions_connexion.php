<?php
	//TEST DES PARAMETRES DE CONNEXION ET RECUPMDP
	
	if(isset($_POST['action'])){$action = $_POST['action'];}
	elseif(isset($_GET['action'])){$action = $_GET['action'];}
	else{echo('erreur'); exit();};

	$echo = 'false';
	
	//SI RECUP MOT DE PASSE
	if($action=='recupmdp'){
		$pseudo = $_POST[crypter('recupmdp_pseudo')];
		$email = $_POST[crypter('recupmdp_email')];
		
		$sql_rep = SELECT('utilisateurs', array('pseudo'=>$pseudo, 'email'=>$email), array('pseudo', 'cleUtilisateur', 'cleUtilisateur_privee', 'email'));

		if(!empty($sql_rep)){
			$pseudo = $sql_rep[0]['pseudo'];
			$email = $sql_rep[0]['email'];
			$user['cle'] = $sql_rep[0]['cleUtilisateur'];
			$user['cle']privee = $sql_rep[0]['cleUtilisateur_privee'];
			$date = date('Y-m-d H:i:s');
			
			//envoi du mail
			$subject = $pseudo.' - recuperation de votre mot de passe';
			$headers = 'From: bookino.org <noreply@bookino.org>'."\r\n";
			$headers .= 'Mime-Version: 1.0'."\r\n";
			$headers .= 'Content-type: text/html; charset=utf-8'."\r\n";
			$headers .= "\r\n";
			$msg = 'Votre nouveau mot de passe : <BR/>
			<A href="http://bookino.org/'.$langue.'/'.$l['P_NOM:CONNEXION'].'?'.
			crypter('action').'='.crypter('nouveaumdp').'&'.crypter('nouveaumdp_cleuser').'='.crypter($user['cle']privee).'
			">Générer un nouveau mot de passe</A>';

			if(mail($email, $subject, $msg, $headers)){$echo = true;};
			UPDATE('utilisateurs', array('cleUtilisateur'=>$user['cle']), array('motdepasse_nouveau'=>$date));
		};
	};
	
	//SI NOUVEAU MOT DE PASSE
	if($action=='nouveaumdp'){
		
		$user['cle']privee = decrypter($_POST[crypter('nouveaumdp_cleuser')]);
		$sql_rep = SELECT('utilisateurs', array('cleUtilisateur_privee'=>$user['cle']privee), array('cleUtilisateur', 'pseudo', 'email', 'motdepasse_nouveau'));
		
		if(!empty($sql_rep)){
			$user['cle'] = $sql_rep[0]['cleUtilisateur'];
			
			//test de la date
			$pseudo = $sql_rep[0]['pseudo'];
			$email = $sql_rep[0]['email'];
			$date_mdp = strtotime($sql_rep[0]['motdepasse_nouveau']);
			$date = time();

			if($date-$date_mdp < 2*3600){
				//generation du mot de passe
				$motdepasse='';
				$chaine1 = 'azertyuiopqsdfghjklmwxcvbn';
				$chaine2 = '0123456789';
				for($i=0; $i<=4; $i++){$motdepasse .= $chaine1[mt_rand(0, (strlen($chaine1)-1))];}
				for($i=0; $i<=3; $i++){$motdepasse .= $chaine2[mt_rand(0, (strlen($chaine2)-1))];}
				
				//envoi du mail
				$subject = $pseudo.' - rappel de vos identifiants';
				$headers = 'From: bookino.org <noreply@bookino.org>'."\r\n";
				$headers .= 'Mime-Version: 1.0'."\r\n";
				$headers .= 'Content-type: text/html; charset=utf-8'."\r\n";
				$headers .= "\r\n";
				$msg = 'Nom d\'utilisateur : '.$pseudo.'<BR/>
						Mot de passe : '.$motdepasse.'<BR/>';
						
				mail($email, $subject, $msg, $headers);
				
				//change mot de passe
				UPDATE('utilisateurs', array('cleUtilisateur'=>$user['cle']), array('motdepasse'=>md5($motdepasse), 'motdepasse_nouveau'=>'1970-01-01 00:00:00'));
				$echo = $motdepasse;
			};
		};
	};
	
	echo(json_encode($echo));
?>