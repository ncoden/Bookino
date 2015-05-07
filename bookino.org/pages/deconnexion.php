<?php
	//GENERATEUR DE PAGE 'DECONNEXION'
	global $_wome;
	global $_options;
	global $_user;
	global $datetime;
	
	//deconnexion par default si page précédente bookino
	
	if($_wome['depuis_site']){
		
		//supprime et met a jour
		DELETE('connexions', ['idUtilisateur'=>$_user['id']]);
		setcookie($_options['cookie_account'], '', time()+0, '/');
		UPDATE('utilisateurs', ['id'=>$_user['id']], ['enligne'=>'0', 'date_activite'=>$datetime]);
		session_destroy();
		
		$_user = ['connecte'=>false];
		list($_user['groupes'], $_user['groupe']) = lister_groupes($_user);
		
		//redirige
		rediriger(url('CONNEXION'));
	}else{
		rediriger(url('ADMIN'));
	};
?>