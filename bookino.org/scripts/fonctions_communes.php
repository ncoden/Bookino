<?php
	//FONCTIONS POUR TOUS DE BOOKINO
	
//RECHERCHE
function script_rechercher($donnees){
	global $user;

	$recherche = $donnees['recherche'];
	
	if($recherche != ''){
		$connaissances = array();
		$autresusers = array();
		
		if($user['connecte']){
			$l_connaissances = lister_connaissances($user['id']);
		}else{
			$l_connaissances = array(
				'lecteurs'=>[],
				'suivis'=>[],
				'amis'=>[]
			);
		};
		
		//selectionne les membres
		$requete = SQL('SELECT * FROM utilisateurs WHERE pseudo LIKE ? ORDER BY pseudo LIMIT 0,30', [$recherche.'%']);
		
		foreach($requete as $autreuser){
			$autreuser_id = $autreuser['id'];
			$autreuser_pseudo = $autreuser['pseudo'];
			$autreuser_cle = $autreuser['cle'];
			
			if(!$user['connecte'] || ($user['connecte'] && $autreuser_cle!=$user['cle'])){
				if(in_array($autreuser_id, $l_connaissances['lecteurs'])
				|| in_array($autreuser_id, $l_connaissances['suivis'])
				|| in_array($autreuser_id, $l_connaissances['amis'])){
					$connaissances[] = array($autreuser_pseudo, crypter($autreuser_cle));
				}else{
					$autresusers[] = array($autreuser_pseudo, crypter($autreuser_cle));
				};
			};
		}
		
		
		$echo = array('connaissances'=>$connaissances,
					  'autresusers'=>$autresusers);
		return $echo;
	};
}

?>