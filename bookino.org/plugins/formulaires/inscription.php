<?php
	//FORMULAIRE D'INSCRIPTION
	
	$form = [
		['url' => lien('ACCEUIL'), 
		 'actualiser' => true, 
		 'design' => true, 
		 'action_php' => function($donnees){
			//inscrit l'utilisateur
			global $user;
			
			//calcul des clés
			$clelibre = false;
			$cleprivee_libre = false;
			
			//clé publique : clé utilisée pour reconnaitre l'utilisateur dans la communauté
			while(!$clelibre){
				$cle = 'USE'.cle(32);
				$sql_rep = SELECT('utilisateurs', ['cle'=>$cle]);
				$clelibre = empty($sql_rep);
			}
			
			//clé privé : clé auquel seul l'utilisateur a accès
			while(!$cleprivee_libre){
				$cleprivee = 'PRI'.cle(32);
				$sql_rep = SELECT('utilisateurs', ['cle_privee'=>$cleprivee]);
				$cleprivee_libre = empty($sql_rep);
			}
						
			//ajoute utilisateur
			INSERT('utilisateurs', [
				'cle' => $cle,
				'cle_privee' => $cleprivee,
				'pseudo' => $donnees['pseudo'],
				'email' => $donnees['email'],
				'motdepasse' => md5($donnees['motdepasse1']),
				'statut' => 'valide_email'
			]);
			
			return true;
		 }], 
		
		['image'		=> [false,				'html',		['contenu'=>'<IMG class="I_marges_20" src="http://f.bookino.org/img/img/user.png"/>'], ], 
		 'pseudo' 		=> [false, 				'champ', 	['defaut'=>l('M:PSEUDO')], 						['taille_min'=>5, 'taille_max'=>30], true],
		 'email' 		=> [false,				'champ', 	['defaut'=>l('M:ADRESSEEMAIL')], 				['type'=>'adresseemail'],			 true],
		 'motdepasse1' 	=> [false,				'champ', 	['defaut'=>l('M:MOTDEPASSE'), 'motdepasse'], 	['taille_min'=>5, 'taille_max'=>30], true],
		 'motdepasse2' 	=> [false,				'champ', 	['defaut'=>l('M:COMFIRMATION'), 'motdepasse'], 	['taille_min'=>5, 'taille_max'=>30], true],
		 'captcha' 		=> [false,				'captcha', 	[], [], true],
		 'securite'		=> [false,				'html',		['contenu'=>'<DIV info="Inscription sécurisée">'.html_icon('securite',16,80,96).' Inscription sécurisée'],],
		], ['submit'	=> [l('M:SINSCRIRE'),	['action'=>'envoyer', 'css'=>'moyen bleu']],], 
	];
?>