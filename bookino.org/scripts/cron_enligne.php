<?php
	//TACHE POUR METTRE HORS LIGNE LES UTILISATEURS NE REPONDANT PAS
	
	//les utilisateurs en ligne envoient une tache au moins toutes les 10 secondes
	//si pas d'activité détecté pendant plus de 30 secondes > hors ligne
	
try{
	$enligne = false;
	if(!$enligne){$db = new PDO('mysql:host=localhost;dbname=byfr_bookino', 'root', '', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));};
	if($enligne){$db = new PDO('mysql:host=localhost;dbname=byfr_bookino', 'byfr_admin', 'b6c775219');};

	
	//test s'il 'y a pas de discussion privee
	$req = $db->prepare('UPDATE utilisateurs SET enligne=0 WHERE TIMESTAMPDIFF(SECOND, date_enligne, NOW())>30');
	if(!$req->execute(array())){echo('erreur_bdd on cron_enligne'); exit();};
	
}catch(Exception $e){$echo='erreur_bdd';exit();}

?>