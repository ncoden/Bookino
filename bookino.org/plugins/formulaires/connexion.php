<?php
	//FORMULAIRE DE CONNEXION
	
	$form = [
		['url' => lien('ACCEUIL'), 
		 'actualiser' => true, 
		 'design' => true, 
		 'action_php' => function($donnees){
			//connecte l'utilisateur
			global $datetime;
			global $options;
			global $user;
			
			$sql = SELECT('utilisateurs', ['pseudo'=>$donnees['pseudo'], 'motdepasse'=>md5($donnees['motdepasse'])], ['id']);
			
			if(!empty($sql)){
				$autreuser_id = $sql[0]['id'];
				$cle = 'CON'.cle(32);
				
				//ajoute utilisateur comme connecté
				INSERT('connexions', ['idUtilisateur'=>$autreuser_id, 'cle'=>$cle]);
				setcookie($options['cookie_account'], crypter($cle), time()+7*24*3600, '/');
				UPDATE('utilisateurs', ['id'=>$autreuser_id], ['enligne'=>'1', 'date_activite'=>$datetime]);
				
				//actualiser variables utilisateur
				$user = e_user($cle, $autreuser_id);
				$_SESSION['user'] = $user;
				
				//ajouter : fonction actualiser langue
				//pour connexion & deconnexion
				
				return true;
			}else{
				return false;
			};
		 }], 
		['image'		=> [false,				'html',		['contenu'=>'<IMG class="I_marges_20" src="http://f.bookino.org/img/img/user.png"/>'], ], 
		 'pseudo' 		=> [false, 				'champ', 	['defaut'=>l('M:PSEUDO')], 						['taille_min'=>5, 'taille_max'=>30], true],
		 'motdepasse' 	=> [false,				'champ', 	['defaut'=>l('M:MOTDEPASSE'), 'motdepasse'], 	['taille_min'=>5, 'taille_max'=>30], true],
		 'mdpoublie' 	=> [false,				'bouton', 	['contenu'=>l('M:MDPOUBLIE?'), 'css'=>'C_lien petit', 'attr'=>['onclick'=>'zone_montrer(\'connexion\', 1)']]],
		 'captcha' 		=> [false,				'captcha', 	[], [], true],
		], ['submit'	=> [l('M:SECONNECTER'),	['action'=>'envoyer', 'css'=>'moyen bleu']],], 
	];
?>