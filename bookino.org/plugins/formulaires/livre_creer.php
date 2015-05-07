<?php
	//FORMULAIRE DE CREATION DE LIVRE
	
	$form = [
		['titre' => 'Créer mon livre', 
		 'design' => true, 
		 'action_php' => function($donnees){
			
		}], 
		[	0 				=> ['En général', 		'titre'],
			'acces' 		=> ['Accès', 			'choix', 	['contenu' => ['public'=>['Public', ['info'=>'Tout le monde peut participer', 'icon'=>['public',16,0,96]]], 
																			'restreint' => ['Restreint', ['info'=>'Seuls les membres actifs peuvent participer', 'icon'=>['restreint',16,32,96]]], 
																			'prive' => ['Privé', ['info'=>'Seuls vous et les membres autorisés peuvent participer', 'icon'=>['prive',16,64,96]]]
																			],
																'defaut' => 'public'], [], true],
			'titre' 		=> ['Titre', 			'champ', 	['description' => 'Le titre serra décidé par la communautée', 'defaut' => 'Titre du livre', 'label_position'=>'haut'], ['taille_min'=>2, 'taille_max'=>30], true],
			'description'	=> ['Description', 		'textarea', ['description' => 'du contexte et de l\'histoire dans sa globalité (année, lieu, personnages...)', 'label_position'=>'haut'], [], true],
			'histoire'		=> ['Histoire', 		'textarea', ['description' => 'Points scénaristiques très importants', 'label_position'=>'haut'], [], true],
			
			1				=> ['Narrateur', 		'titre'],
			'pointdevue' 	=> ['Point de vue', 	'choix', 	['contenu' => ['omnicient'=>['Omnicient'], 'interne'=>['Interne'], 'externe'=>['Externe']],
																'defaut' => 'interne'], [], true],
			'niveaudelangue'=> ['Niveau de langue', 'choix', ['contenu' => ['familier'=>['Familier'], 'courant'=>['Courant'], 'soutenu'=>['Soutenu']],
															'defaut' => 'courant'], [], true],
			'precisions'	=> ['Autres précisions','textarea', ['description' => 'Informations supplémentaire sur la façon d\'écrire, le narrateur', 'label_position'=>'haut'], [], true],
			
			2				=> ['Livre', 'titre'],
			'nombre_pages' 	=> ['Nombre de page', 	'choix', 	['contenu' => ['20moins'=>['Moins de 20'], '2050'=>['20 - 50'], '50100'=>['50 - 100'], '100200'=>['100 - 200'], '200plus'=>['Plus de 200']],
																'defaut' => '100200'], [], true],
		], ['submit'		=> ['Envoyer',			['action' => 'envoyer', 'css' => 'moyen bleu']],], 
	];
?>