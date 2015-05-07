<?php
	//GENERATEUR DE PAGE 'ACCEUIL'
	
	global $_user;
	
	page_titre('Titre de la page');
	
	echo('<BR/>
	<BR/>
	<BR/>
	<BR/>
	<BR/>
	Acceuil<BR/>');
	
if(cache('bonjour', 'user')){
	echo('Bonjour '.$_user['pseudo'].'<BR/>');
}elseif(cache('bonjour', 'nonconnectes')){
	echo('Bonjour cher inconnu<BR/>');
};

if(cache('zone_membre', 'connectes')){
	echo('Zone membre<BR/>');
	
	if(cache('admins', 'administrateurs')){
		echo('Admins uniquement<BR/>');
		
		if(cache('admins_perso', 'user')){
			echo('Ma zone admin, si je le suis<BR/>');
		};
	};
	
	if(cache('modos', 'moderateurs')){
		echo('Zone modo<BR/>');
	};
	
	if(cache('membres')){
		echo('Zone membre, encore<BR/>');
	};
};

if(cache('zone_nonmembres', 'nonconnectes')){
	echo('Zone non-membres<BR/>');
}; 

cache('tous');

	echo('Zone pour tous');
?>