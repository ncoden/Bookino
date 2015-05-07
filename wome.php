<?php
	//WOME BETA 0.1 server (WOME_B0.1_server)
	//Website Oriented Multilanguage Engine - server file for BETA 0.1
	//Copyright WOME © 2013-2014 Nicolas Coden. All rights reserved

ini_set('xdebug.var_display_max_depth', '10');


//===== VARIABLES ET INIT =====

//variables partagées principales
$_wome = [];	//informations générales sur wome et le site
$_page = [];	//informations sur le rendu du site et de la page
$_user = [];	//toutes les infos sur l'utilisateur, connecte ou non
$_config = [];	//configuration de wome et du site

//variables necessaires à wome
$url = null;
$adresse = [];
$l_scripts = [];
$plugins = [];

$logs = [
	'execution' => '',
	'request_number' => 0,
];
$temps_debut = microtime(true);

$l = [];
$d = [];

//variables pour les fonctions de generation
$l_icons = [];


	//===== FONCTIONS =====
	
	//fonctions réutilisés
	//elles n'appartiennent pas à Wome
	function creer_dossier($path, $mode=0777, $recursive=true){
		if(empty($path)){
			return false;
		};
		
		if($recursive){
			$toDo = substr($path, 0, strrpos($path, '/'));
			if($toDo !== '.' && $toDo !== '..'){
				creer_dossier($toDo, $mode);
			};
		};
		
		if(!is_dir($path)){
			mkdir($path, $mode);
		};
		
		return true;
	}
	
	//fonctions nécéssaires basiques
	function base62_encode($data){
		$outstring = '';
		$l = strlen($data);
		for ($i = 0; $i < $l; $i += 8) {
			$chunk = substr($data, $i, 8);
			$outlen = ceil((strlen($chunk) * 8)/6); //8bit/char in, 6bits/char out, round up
			$x = bin2hex($chunk);  //gmp won't convert from binary, so go via hex
			$w = gmp_strval(gmp_init(ltrim($x, '0'), 16), 62); //gmp doesn't like leading 0s
			$pad = str_pad($w, $outlen, '0', STR_PAD_LEFT);
			$outstring .= $pad;
		}
		return $outstring;
	}
	function base62_decode($data){
		$outstring = '';
		$l = strlen($data);
		for ($i = 0; $i < $l; $i += 11) {
			$chunk = substr($data, $i, 11);
			$outlen = floor((strlen($chunk) * 6)/8); //6bit/char in, 8bits/char out, round down
			$y = gmp_strval(gmp_init(ltrim($chunk, '0'), 62), 16); //gmp doesn't like leading 0s
			$pad = str_pad($y, $outlen * 2, '0', STR_PAD_LEFT); //double output length as as we're going via hex (4bits/char)
			$outstring .= pack('H*', $pad); //same as hex2bin
		}
		return $outstring;
	}
	function crypter($chaine){
		$cle='BOOKINOCRYPTKEYo1997';
		$cle = md5($cle);
		$lettre = -1;
		$nstr = '';
		
		$str_taille = strlen($chaine);
		for($i=0; $i<$str_taille; $i++){
			$lettre++;
			if($lettre > 31 ){
				$lettre = 0;
			};
			$nmot = ord($chaine{$i}) + ord($cle{$lettre});
			if($nmot>255){$nmot -= 256;}
			$nstr .= chr($nmot);
		}

		return base62_encode($nstr);
	}
	function decrypter($chaine){	
		$cle='BOOKINOCRYPTKEYo1997';
		$cle = md5($cle);
		$lettre = -1;
		$nstr = '';

		$chaine = base62_decode($chaine);
		$str_taille = strlen($chaine);
		for($i=0; $i<$str_taille; $i++){
			$lettre++;
			if($lettre > 31 ){
				$lettre = 0;
			};
			$nmot = ord($chaine{$i}) - ord($cle{$lettre});
			if($nmot<1){$nmot += 256;}
			$nstr .= chr($nmot);
		}
		return $nstr;
	}
	function simplifier($chaine){
		$alphabet = array(
			'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
			'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
			'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
			'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
			'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
			'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
			'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
		);
		
		$chaine = strtr($chaine, $alphabet);
		$chaine = strtolower(preg_replace('/\W+/', '-', $chaine));
		
		return $chaine;
	}
	function erreur($numero){
		
		$message = '<H1>An error has occurred.</H1>
					<H3>Please try to access the site later, the error may be temporary.</H3>
					<I>Note: Your computer works, you are connected to the Internet.<BR/>
					The error is caused by a configuration error of WOME, the engine of your website.<BR/>
					If you are the administrator, please check your configuration. </I><BR/>
					<BR/>
					<B>Error Number: WOME'.$numero.' </B>(';
		
		//ajoute message suivant l'erreur
		switch($numero){
			//100 : erreurs de fichiers, de lecture
			case 100 : $message .= 'can\'t read wome_conf.php'; break;
			case 101 : $message .= 'can\'t read conf.php in the website repertory'; break;
			
			//200 :  erreurs de configuration
			case 200 : $message .= 'domain no found in wome_conf.php'; break;
			
			//500 : erreur de connexion & base de données
			case 500 : $message .= 'unable to connect to database'; break;
		}
		
		$message .= ')';
		
		//quitte
		exit($message);
	}
	function logs($type, $nom=null){
		global $_options;
		global $temps_debut;
		if(!isset($_options['use_logs']) || $_options['use_logs']){
			global $logs;
			$temps = microtime(true)-$temps_debut;
			$logs['execution'][] = [number_format((float)$temps*1000, 2, '.', ''), $type, $nom];
		};
	}	
	logs('STARTING WOME...');
	
	//fonctions SQL
	function connexion_sql(){
		global $_options;
		global $db;
		
		if(!isset($db)){
			try{
				$db = new PDO('mysql:
					host='.$_options['db_host'].';
					dbname='.$_options['db_name'].'', 
					$_options['db_username'], 
					$_options['db_password'], 
					[PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
				);
			}catch(Exception $e){
				erreur(500);
			}
		};
		
		return $db;
	}
	function SQL($requete, $donnees=[]){
		global $logs;
		
		//connexion à la base de données
		$db = connexion_sql();
		
		//envoi la requete
		$req = $db->prepare($requete);
		if(!$req->execute($donnees)){echo('erreur_bdd on "'.$requete.'"');exit();};
		
		$reponse = [];
		while($donnee = $req->fetch()){
			//ajoute les résultats dans un tableau
			$reponse[] = $donnee;
		}
		$logs['request_number']++;
		
		return $reponse;
	}
	function SELECT($table, $necessaire=[], $asavoir=[]){
		$valeurs = [];
		
		//construit la requete
		$requete = 'SELECT ';
		if(!empty($asavoir)){
			foreach($asavoir as $nom){
				$requete.= $nom.',';
			}
			$requete = substr($requete, 0, -1);
		}else{
			$requete.= '*';
		};
		$requete .= ' FROM '.$table;
		
		if(!empty($necessaire)){
			$requete.= ' WHERE ';
			foreach($necessaire as $nom=>$valeur){
				$requete.= $nom.'=? AND ';
				$valeurs[] = $valeur;
			}
			$requete = substr($requete, 0, -5);
		};
		
		//envoi la requete
		$reponse = SQL($requete, $valeurs);

		return $reponse;
	}
	function INSERT($table, $donnees=[]){
		$valeurs = [];
		
		//construit la requete
		$requete = 'INSERT INTO '.$table;
		if(!empty($donnees)){
			$requete .= '(';
			foreach($donnees as $nom=>$valeur){
				$requete.= $nom.',';
			}
			$requete = substr($requete, 0, -1).') VALUES(';
			
			foreach($donnees as $nom=>$valeur){
				$requete.= '?,';
				$valeurs[] = $valeur;
			}
			$requete = substr($requete, 0, -1).')';
		};
		
		//envoi la requete
		SQL($requete, $valeurs);
	}
	function DELETE($table, $donnees=[]){
		$valeurs = [];
		
		//construit la requete
		$requete = 'DELETE FROM '.$table;
		if(!empty($donnees)){
			$requete .= ' WHERE ';
			foreach($donnees as $nom=>$valeur){
				$requete.= $nom.'=? AND ';
				$valeurs[] = $valeur;
			}
			$requete = substr($requete, 0, -5);
		};
		
		//envoi de la requête
		sql($requete, $valeurs);
	}
	function UPDATE($table, $necessaire=[], $donnees=[]){
		$valeurs = array();
		
		//construit la requete
		$requete = 'UPDATE '.$table.' SET ';
		if(!empty($donnees)){
			foreach($donnees as $nom=>$valeur){
				$requete.= $nom.'=?, ';
				$valeurs[] = $valeur;
			}
			$requete = substr($requete, 0, -2);
		
			if(!empty($necessaire)){
				$requete .= ' WHERE ';
				foreach($necessaire as $nom=>$valeur){
					$requete.= $nom.'=? AND ';
					$valeurs[] = $valeur;
				}
				$requete = substr($requete, 0, -5);
			};
			
			//envoi la requete
			SQL($requete, $valeurs);
		};
	}
	
	//fonctions de retour
	function cle($taille=0, $caracteres='ABCDEFGHIJKLMNOPQRSTUVWXYZabCdefghijklmnopqrstuvwxyz123457890'){
		//genere une clé aléatoire de X carractères
		$cle = '';
		$nb = strlen($caracteres);
		
		for($i=0; $i<$taille; $i++){
			$position = rand(0,$nb-1);
			$lettre = $caracteres{$position};
			$cle .= $lettre;
		}
		
		return $cle;
	}
	function si_defini($tableau, $index, $defaut=null){
		if(is_array($tableau) && isset($tableau[$index])){
			return $tableau[$index];
		}else{
			return $defaut;
		};
	}
	function si_danstableau($tableau, $index, $defaut){
		if(isset($tableau[$index])){
			return $index;
		}else{
			return $defaut;
		};
	}
	function session_date($nom){
		global $_SESSION;
		$datetime = date('Y-m-d H:i:s');
		
		$_SESSION['dates'][$nom] = $datetime;
	}
	function aleatoire($caracteres='ABCDEFGHIJKLMNOPQRSTUVWXYZabCdefghijklmnopqrstuvwxyz123457890'){
		if(is_array($caracteres)){
			return $caracteres[array_rand($caracteres)];
		}else{
			return $caracteres{rand(0, strlen($caracteres)-1)};
		};
	}
	
	function valeur($variable){
		global $_wome;
		global $_page;
		global $_user;
		
		//si fonction
		if(is_callable($variable)){
			return $variable($_wome, $_page, $_user);
		}else{
			//si chaine
			return $variable;
		};
	}
	function repertoire($dossier, $supplement=null){
		global $_wome;
		global $_config;
		
		$repertoire = $_config['DIRECTORIES'][$dossier];
		
		if($supplement != null){
			$repertoire .= '/'.$supplement;
		};
		
		return $repertoire;
	}
	
	//fonctions de gestion des taches et pages
	function traiter_url($url){
		global $_GET;
		global $_wome;
		global $_config;
		global $_options;
		global $_user;
		global $l_langues;
		global $l_pages;
		global $l_redirections;
		
		$page = [];
		$adresse = url_vers_adresse($url);
		
		//----- MODIFICATIONS DE L'URL -----
		
		
		
		
		//----- LANGUE -----
		
		//déduit langue et retire de l'url
		if(count($adresse)>0 && isset($l_langues[$adresse[0]])){
			$langue = $adresse[0];
			array_shift($adresse);
		}else{
			if(count($l_langues) > 0){
				$langue = array_keys($l_langues)[0];
			}else{
				$langue = null;
			};
		};
		$url = adresse_vers_url($adresse);
		$url_relative = $url;
		$url_locale = '/'.$langue.$url;
		
		logs('PAGE PROCESSING', $url_locale);
		
		
		//si la langue est nulle, aucune langue
		//n'est inscite dans la configuration
		if($langue != null){
			$langue_origine = $langue;
			
			//-- langue du formulaire --
			if(isset($_GET['language']) 
			&& isset($l_langues[$_GET['language']]) 
			&& $_wome['depuis_site']){
				//change la langue
				$langue = $_GET['language'];
				
				//retiens en base de données et cookie
				if($_user[$_options['user_login_index']]){
					UPDATE('utilisateurs', ['id'=>$_user['id']], ['langue'=>$langue]);
				};
				if(!$_user[$_options['user_login_index']] || !isset($_COOKIE[$_options['cookie_language']])){
					setcookie($_options['cookie_language'], $langue, time()+365*24*3600, '/');
				};
			}else{
			
				//OU -- langue d'un précédent formulaire --
				if($_user[$_options['user_login_index']] && isset($_user[$_options['user_language_index']])
				&& isset($l_langues[$_user[$_options['user_language_index']]]) 
				&& $_options['user_save_language']){
					//si l'utilisateur est connecté
					$langue = $_user[$_options['user_language_index']];
				}elseif(isset($_COOKIE[$_options['cookie_language']])
				&& isset($l_langues[$_COOKIE[$_options['cookie_language']]])){
					//sinon si la langue est dans un cookie
					$langue = $_COOKIE[$_options['cookie_language']];
				}else{
				
					//OU -- langue du navigateur --
					if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
						$langues_nav = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
						
						//parcourt chaque langue et choisi la première langue
						//présente dans la configuration
						foreach($langues_nav as $langue_nav){
							$langue_nav = substr($langue_nav, 0, 2);
							if(isset($l_langues[$langue_nav])){
								$langue = $langue_nav;
								break;
							};
						}
					};
				};
			};
			$langue;
			
			//inclu la langue
			$l = importer_langue($langue);
			
			//si la langue doit changer (différente)
			if($langue != $langue_origine){
				//inclu l'ancienne langue pour comparer les mots de l'url
				$l_precedente = parse_ini_file($_config['DIRECTORIES']['langues'].'/'.$langue_origine.'.ini', true);
				
				//redirige vers nouvelle page
				foreach($adresse as $i=>$morceau){
					if(in_array($morceau, si_defini($l_precedente,'PAGE:NOM',[]))){
						$adresse[$i] = $l['PAGE:NOM'][array_search($morceau, $l_precedente['PAGE:NOM'])];
					};
				}
				$url = adresse_vers_url($adresse);
			};
		};
		
		//redirige page vide vers la page par defaut ou la premiere page déclarée
		if(empty($adresse) || $adresse==['']){
			if(isset($_options['defaut_page_url'])){
				$nouvelle_url = traiter_url($_options['defaut_page_url'])[0];
			}else{
				$nouvelle_url = traiter_url('/'.page_nomlocal(array_keys($l_pages)[0]))[0];
			};
			
			if($nouvelle_url != $url){
				$url = $nouvelle_url;
				$adresse = url_vers_adresse($url);
			};
		};
		
		//vérifi qu'il y a une page demandée
		if($url != $_options['script_page_url'] 
		&& (!$_wome['depuis_ajax'] || ($_wome['depuis_ajax'] && isset($_POST['page'])))){
		
			//----- DETECTION PAGE 404 -----
			$l_pages_ref = $l_pages;
			$page['proprietes'] = false;
			
			foreach($adresse as $index=>$nom_local){
				//page 404 si le morceau n'existe pas 
				//ET il n'a pas été autorisé par le parent (si parent)
				$nom_general = page_nomgeneral($nom_local);
				
				//test si page 404
				if((!$nom_general || !isset($l_pages_ref[$nom_general]))
				&& ($index==0 || $l_pages_ref != '*')){
					$donnees = traiter_url(page_nomlocal($_options['404_page']));
					$url = $donnees[0];
					$adresse = [$url];			
					
					$page['nom'] = $donnees[4]['nom'];
					$page['proprietes'] = $donnees[4]['proprietes'];
					break;
				};
				
				//enregistre les proprietes de la derniere page connue
				if(isset($l_pages_ref[$nom_general])){
					$page['nom'] = $nom_general;
					$page['proprietes'] = $l_pages_ref[$nom_general];
				};
				
				//Utilise la liste des pages de l'enfant.
				//Si elle n'existe pas, la page serra 404 à la boucle suivante.
				if(isset($l_pages_ref[$nom_general])){
					$l_pages_ref = $l_pages_ref[$nom_general][6];
				}else{
					$l_pages_ref = null;
				};
			}
			
			//----- REDIRECTIONS -----
			
			//redirections visibles
			$taille = count($l_redirections['visibles']);
			for($i=0; $i<$taille; $i++){
				$fonction = $l_redirections['visibles'][$i];
				$url_redirection = $fonction($_wome,$_config,$_user,$page);
				
				if($url_redirection != null){
					$url = traiter_url($url_redirection)[0];
				};
			}
			
			//calcul adresse visible de la page
			
			//si l'url par defaut doit être cachée
			if($url==$_options['defaut_page_url'] && $_options['hide_defaut_url']){
				//si elle PEUT être cachée
				if(!$_options['force_hide_defaut_url']){
					//utilise l'url non changée
					$page['url_relative'] = $url_relative;
					
					//si la langue peut être cachée
					if($_options['hide_language_in_defaut_url']){
						$page['url_locale'] = $_wome['url_origine'];
					}else{
						$page['url_locale'] = '/'.$langue.$page['url_relative'];
					};
				}else{
					//si elle DOIT être cachée
					$page['url_relative'] = '/';
					$page['url_locale'] = '/';
				};
			}else{
				//sinon, utilise url actuelle
				$page['url_relative'] = $url;
				$page['url_locale'] = '/'.$langue.$page['url_relative'];
			};
			
			//redirections invisibles
			$taille = count($l_redirections['invisibles']);
			for($i=0; $i<$taille; $i++){
				$fonction = $l_redirections['invisibles'][$i];
				$url_redirection = $fonction();
				
				if($url_redirection != null){
					$url = traiter_url($url_redirection)[0];
				};
			}
			
			return [
				$url,
				$url_relative,
				$url_locale,
				$langue,
				$page,
			];
			
		}else{
			return [
				null,
				null,
				null,
				$langue,
				false,
			];
		};
	}
	function rediriger($nouvelle_url){
		global $rediriger;
		global $url;
		$rediriger = true;
		$url = $nouvelle_url;
	}
	
	function url_vers_lien($url){
		global $langue;
		return '/'.$langue.$url;
	}
	function url_vers_adresse($url){
		//decoupe
		$adresse = explode('/', $url);
		
		//retire les trous
		$nouvelle_adresse = [];
		foreach($adresse as $i=>$morceau){
			if($morceau != ''){
				$nouvelle_adresse[] = $morceau;
			};
		}
				
		return $nouvelle_adresse;
	}
	function adresse_vers_url($adresse){
		$url = '';
		$taille = count($adresse);
		
		for($i=0; $i<$taille; $i++){
			$url .= $adresse[$i].'/';
		}
		
		if($url != ''){
			$url = substr($url, 0, -1);
		};
		$url = '/'.$url;
		
		return $url;
	}
	function url($adresse){
		$url = '';
		//si simple page, transforme en page
		if(!is_array($adresse)){$adresse = [$adresse];};
		$taille = count($adresse);
		
		//construit url
		for($i=0; $i<$taille; $i++){
			$nom_local = page_nomlocal($adresse[$i]);
			if($nom_local){
				$url .= '/'.$nom_local;
			}else{
				$url .= '/'.$adresse[$i];
			};
		}
		
		return $url;
	}
	function lien($adresse){
		global $langue;
		
		$url = url($adresse);
		if($url){
			if($langue != null){
				return '/'.$langue.$url;
			}else{
				return $url;
			};
		}else{
			return false;
		};
	}
	function nomgeneral_vers_pages($nom){
		$nom_langue = page_nomlocal($nom);
		
		if($nom_langue){
			return [$nom_langue];
		}else{
			return false;
		};
	};
	function page_nomgeneral($nom){
		//retourne le nom general de la page
		//(international)
		global $l;
		
		$nom_langue = array_search($nom, si_defini($l, 'PAGE:NOM', []));
		if($nom_langue){
			return $nom_langue;
		}else{
			//return strtoupper($nom);
			return false;
		};
	}
	function page_nomlocal($nom){
		//retourne le nom local de la page
		//(suivant la langue)
		global $l;
		
		if(isset($l['PAGE:NOM'][$nom])){
			return $l['PAGE:NOM'][$nom];
		}else{
			return strtolower($nom);
		};
	}
	
	function inclure_js($js, $action=false){
		global $meta;
		global $meta_morceau;
		
		//ajoute le code a la variable du cache
		if(!$action){
			$meta['js_init'] .= $js;
			$meta_morceau['js_init'] .= $js;
		}else{
			$meta['js'] .= $js;
			$meta_morceau['js'] .= $js;
		};
		
		//ajoute le code a la page
		actualiser_js();
	}
	function inclure_css($css){
		global $meta;
		global $meta_morceau;
		
		//ajoute le code a la variable du cache
		$meta['css'] .= $css;
		$meta_morceau['css'] .= $css;
		
		//ajoute le code a la page
		actualiser_css();
	}
	function inclure_fichier($type, $url){
		global $meta;
		global $meta_morceau;
		
		if($type == 'js'){
			$meta['fichiers_js'][] = $url;
			$meta_morceau['fichiers_js'][] = $url;
			actualiser_js();
		}elseif($type == 'css'){
			$meta['fichiers_css'][] = $url;
			$meta_morceau['fichiers_css'][] = $url;
			actualiser_css();
		};
	}
	function actualiser_js(){
		global $_page;
		global $meta;
		global $_config;
		
		$_page['entete_js'] = '';
		
		//fichiers importés
		foreach($meta['fichiers_js'] as $url){
			$_page['entete_js'] .= '<SCRIPT type="text/javascript" src="http://f.bookino.org/'.$url.'"  id="head_'.$url.'"></SCRIPT>';
		}
		
		//code
		$meta_js = '';
		if($meta['js'] != ''){
			$meta_js = '$(document).ready(function(){
							'.$meta['js'].'
						});';
		};
		
		if($meta_js != '' || $meta['js_init'] != ''){
			$_page['entete_js'] .= '
				<SCRIPT type="text/javascript" id="head_js">
					'.$meta['js_init'].'
					'.$meta_js.'
				</SCRIPT>';
		};
		
		$_page['js'] = $meta['js_init'].$meta_js;
		$_page['fichiers_js'] = $meta['fichiers_js'];
	}
	function actualiser_css(){
		global $_page;
		global $meta;
		
		$_page['entete_css'] = '';
		
		//fichiers importés
		foreach($meta['fichiers_css'] as $url){
			$_page['entete_css'] .= '<LINK type="text/css" rel="stylesheet" href="http://f.bookino.org/'.$url.'"/  id="head_'.$url.'">';
		}
		
		//code
		if($meta['css'] != null){
			$_page['entete_css'] .= '
				<STYLE type="text/css" id="head_css">
					'.$meta['css'].'
				</STYLE>';
		};
		
		$_page['css'] = $meta['css'];
		$_page['fichiers_css'] = $meta['fichiers_css'];
	}
	function page_titre($titre){
		global $meta_morceau;
		$meta_morceau['titre'] = $titre;
	}
	
	//fonctions de gestion des groupes et du cache
	function cache($nom, $groupe = null){
		global $_user;
		global $_options;
		global $_page;
		global $adresse;
		global $langue;
		global $cache_groupeprecedent;
		global $cache_nomprecedent;
		global $cache_precedent_lu;
		global $cache;
		global $meta_morceau;
		
		global $logs;
		
		//si dans le groupe actuel
		if(si_dansgroupe($cache_groupeprecedent)
		&& $nom != $_options['start_cache_name']){
			$html = ob_get_contents();
			
			//si le cache a changé
			if(!$cache_precedent_lu && (
			$html != '' 
			|| $meta_morceau['js'] != '' 
			|| $meta_morceau['js_init'] != ''
			|| $meta_morceau['css'] != ''
			|| !empty($meta_morceau['fichiers_js'])
			|| !empty($meta_morceau['fichiers_css'])
			|| isset($meta_morceau['titre']))){
				
				if($_options['use_cache']){
					//determine nom unique du morceau
					$fichier = $_page['proprietes'][0];
					$nom_fichier = base62_encode($fichier).'_'.base62_encode($cache_nomprecedent).'_'.base62_encode($cache_groupeprecedent);
					
					//ajoute le contenu au cache
					$cache[$nom_fichier] = $meta_morceau;
					$cache[$nom_fichier]['html'] = $html;
					$cache[$nom_fichier]['cache_lu'] = false;
				}else{
					$cache = array_replace_recursive($cache, $meta_morceau);
					$cache['html'] .= $html;
				};
			};
		};
		
		//prépare les valeurs pour le morceau suivant
		ob_clean();
		$meta_morceau = [
			'js' => '',
			'js_init' => '',
			'css' => '',
			'fichiers_js' => [],
			'fichiers_css' => [],
		];
		
		//si dans le nouveau groupe, prepare pour fonction suivante
		if(si_dansgroupe($groupe)
		&& $nom != $_options['end_cache_name']){
			$cache_nomprecedent = $nom;
			$cache_groupeprecedent = $groupe;
			
			$fichier = $_page['proprietes'][0];
			$nom_fichier = base62_encode($fichier).'_'.base62_encode($nom).'_'.base62_encode($groupe);
			$repertoire_fichier = repertoire('cache', $langue.'/'.implode('_', $adresse).'/'.$nom_fichier);
			
			if($_user[$_options['user_login_index']]){
				$nom_groupe = '_'.base62_encode($_options['groupe_user']);
				$nom_fichier = str_replace($nom_groupe, $nom_groupe.$_user[$_options['user_unique_index']], $cache_nomprecedent);
			};
			
			//SI le cache est activé 
			//ET on doit optimiser au niveau des morceaux
			//ET le fichier cache existe et est à jour
			//ET le cache est autorisé sur cette page
			if($_options['use_cache'] 
			&& $_options['cache_each_parts'] 
			&& file_exists($repertoire_fichier) 
			&& $_page['proprietes'][2] != false 
			&& filemtime($repertoire_fichier) > (time() - $_page['proprietes'][2])){
				
				//lit le morceau
				$donnees = unserialize(file_get_contents($repertoire_fichier));
				if(is_array($donnees) && (
				$donnees['html'] != '' 
				|| $donnees['js'] != '' 
				|| $donnees['js_init'] != ''
				|| $donnees['css'] != ''
				|| !empty($donnees['fichiers_js'])
				|| !empty($donnees['fichiers_css'])
				|| isset($donnees['titre']))){
					//enregistre données dans le cache
					$cache[$nom_fichier] = $donnees;
					$cache[$nom_fichier]['cache_lu'] = true;
				};
				$cache_precedent_lu = true;
				return false;
			}else{
				$cache_precedent_lu = false;
				return true;
			};
		}else{
			$cache_precedent_lu = false;
			return false;
		};
	}
	function completer_cache($page, $meta, $donnees){
		$page['html'] .= $donnees['html'];
		$meta['js_init'] .=$donnees['js_init'];
		$meta['js'] .= $donnees['js'];
		$meta['css'] .= $donnees['css'];
		$meta['fichiers_js'] = array_merge($meta['fichiers_js'], $donnees['fichiers_js']);
		$meta['fichiers_css'] = array_merge($meta['fichiers_css'], $donnees['fichiers_css']);
		
		//change le titre s'il a été défini dans ce morceau
		if(isset($donnees['titre'])){$page['titre'] = $donnees['titre'];};
		
		return [$page, $meta];
	}
	function lister_groupes($user=null, $groupes=null){
		//defini variables par defaut
		if($groupes == null){
			global $l_groupes;
			$groupes = $l_groupes;
		};
		if($user == null){
			global $_user;
			$user = $_user;
		};
		
		$user_groupes = [];
		$user_groupe = null;
		
		//parcour les groupes
		foreach($groupes as $nom=>$donnees){
			list($fonction, $sousgroupes) = $donnees;
			
			//si utilisateur dans le groupe
			if($fonction($user)){
				//ajoute
				$user_groupes[] = $nom;
				$user_groupe = $nom;
				
				//ajoute les sous-groupes
				if(is_array($sousgroupes)){
					list($user_sousgroupes, $user_sousgroupe) = lister_groupes($user, $sousgroupes);
					$user_groupes = array_merge($user_groupes, $user_sousgroupes);
					if($user_sousgroupe != null){$user_groupe = $user_sousgroupe;};
				};
				break;
			};
		}
		
		return [$user_groupes, $user_groupe];
	}
	function si_dansgroupe($nom){
		global $_user;
		global $_options;
		
		$dansgroupe = false;
		
		//si chaine, transforme en tableau
		if(!is_array($nom)){
			$groupes = [$nom];
		}else{
			$groupes = $nom;
		};
		
		//parcour groupes et retiens si dans groupe
		foreach($groupes as $groupe){
			if(in_array($groupe, $_user['groupes']) 
			|| ($groupe==$_options['groupe_user'] && $_user[$_options['user_login_index']]) 
			|| $groupe==null 
			|| $groupe=='*'){
				$dansgroupe = true;
			};
		}
		
		return $dansgroupe;
	}
	
	//fonctions divers
	function lister_connaissances($autreuser_id){
		global $db;
		global $_user;
		global $d;
		
		if(!isset($d['connaissances'])){
			$d['connaissances'] = array();
		};
		$d['connaissances'][$autreuser_id] = array(	'lecteurs'=>array(),
													'suivis'=>array(),
													'amis'=>array()
													);
		
		//recupere tout
		$req = $db->prepare('SELECT * FROM lecteurs WHERE idUtilisateur_1=? OR idUtilisateur_2=?');
		if(!$req->execute(array($autreuser_id, $autreuser_id))){echo('erreur_bdd');exit();};
		
		while($donnee = $req->fetch()){
			//si lecteur
			if($donnee['type']=='suis' && $donnee['idUtilisateur_2']==$autreuser_id){
				$d['connaissances'][$autreuser_id]['lecteurs'][] = $donnee['idUtilisateur_1'];
			};
			//si personnes suivie
			if($donnee['type']=='suis' && $donnee['idUtilisateur_1']==$autreuser_id){
				$d['connaissances'][$autreuser_id]['suivis'][] = $donnee['idUtilisateur_2'];
			};
			//si ami
			if($donnee['type']=='amis'){
				if($donnee['idUtilisateur_1']==$autreuser_id){
					$d['connaissances'][$autreuser_id]['amis'][] = $donnee['idUtilisateur_2'];
				};
				if($donnee['idUtilisateur_2']==$autreuser_id){
					$d['connaissances'][$autreuser_id]['amis'][] = $donnee['idUtilisateur_1'];
				};
			};
		}
		
		return $d['connaissances'][$autreuser_id];
	}
	function langue_date($date_donnee){

		if($date_donnee!='' && $date_donnee!='undefined'){
			
			//defini les variables dates
			$date_donnee = strtotime($date_donnee);
			$date_now = time();
			$decalage = abs($date_now - $date_donnee);
			
			//calcul le decalage et la date finale
			$annees = floor($decalage/(	 3600*24*365));
			$reste = $decalage - $annees*3600*24*365;
			$mois = floor($reste/(		 3600*24*30.41));
			$reste -= $mois*			 3600*24*30.41;
			$jours = floor($reste/(		 3600*24));
			$reste -= $jours*			 3600*24;
			$heures = floor($reste/(	 3600));
			$reste -= $heures*			 3600;
			$minutes = floor($reste/(	 60));
			$reste -= $minutes*			 60;
			$secondes = floor($reste);
					
			//construit la phrase
			if($decalage < 60){$date = ''.$secondes.' secondes';}
			else if($decalage < 60*2){$date = '1 minute';}
			else if($decalage < 3600){$date = $minutes.' minutes';}
			else if($decalage < 3600*2){$date = '1 heure';}
			else if($decalage < 3600*24){$date = $heures.' heures';}
			else if($decalage < 3600*24*2){$date = '1 jour';}
			else if($decalage < 3600*24*7){$date = $jours.' jours';}
			else if($decalage < 3600*24*7*2){$date = '1 semaine';}
			else if($decalage < 3600*24*31){$date = ($jours/4).' semaines';}
			else if($decalage < 3600*24*31*2){$date = '1 mois';}
			else if($decalage < 3600*24*365){$date = $mois.' mois';}
			else if($decalage < 3600*24*365*2){$date = '1 an';}
			else{$date = $annees.' ans';};
		}else{
			$date = '';
			$decalage = false;
		};
			
		return ['date_langue' => $date, 
				'decalage' => $decalage
				];
	}
	
	//fonctions de langue
	function l($categorie, $mot=null){
		global $l;
		if(isset($l[$categorie]) 
		&& isset($l[$categorie][$mot])){
			return $l[$categorie][$mot];
		}else{
			return $categorie.':'.$mot;
		};
	}
	function m($mot){
		global $l;
		if(isset($l['M']) 
		&& isset($l['M'][$mot])){
			return $l['M'][$mot];
		}else{
			return 'M:'.$mot;
		};
	}
	function importer_langue($langue, $repertoire=false){
		global $l;
		
		//defini le repertoire par defaut
		if($repertoire === false){
			global $_wome;
			global $_config;
			$repertoire = $_wome['site'].'/'.$_config['DIRECTORIES']['langues'];
		};
		
		//inclu la langue
		$l_second = parse_ini_file($repertoire.'/'.$langue.'.ini', true);
		$l = array_replace_recursive($l, $l_second);
		
		return $l;
	}
	function age($naiss){
		list($annee, $mois, $jour) = explode('-', $naiss);
		$today['mois'] = date('n');
		$today['jour'] = date('j');
		$today['annee'] = date('Y');
		$annees = $today['annee'] - $annee;
		if($today['mois'] <= $mois){
			if($mois == $today['mois']){
				if($jour > $today['jour']){
					$annees--;
				};
			}else{
				$annees--;
			};
		};
		return $annees;
	}
	
	//fonctions de génération HTML
	function html_icon($nom_icon, $taille, $x, $y, $hover=false){
		global $l_icons;
		
		$nom = 'icon_'.$taille.'_'.$x.'_'.$y;
		if(!isset($l_icons[$nom])){
			inclure_css('.C_icon.'.$nom.'{background-position:-'.$x.'px -'.$y.'px;}');
			$l_icons[$nom] = true;
		};
		if($hover){
			inclure_css('*:hover > .C_icon.'.$nom.'.hover, .C_icon.'.$nom.'.hover:hover{background-position:-'.($x+16).'px -'.$y.'px;}');
			return '<DIV class="C_icon t'.$taille.' '.$nom.' hover"></DIV>';
		}else{
			return '<DIV class="C_icon t'.$taille.' '.$nom.'"></DIV>';
		};
	}
	
	
	//===== LOGS SERVEUR ET TESTS =====
	
	
	
	
	//===== RECUPERATION DES DONNEES =====
	logs(null, 'loading site datas');
	
	//données wome de base
	$_wome['version'] = 'v/BETA 0.1';
	
	//déduit url actuelle & precedente, sousdomaine
	$_wome['url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$_wome['url_origine'] = $_SERVER['REQUEST_URI'];
	$_wome['adresse'] = parse_url($_wome['url']);
	$_wome['adresse']['domaine'] = si_defini($_GET, 'domaine', null);
	$_wome['adresse']['sousdomaine'] = si_defini($_GET, 'sousdomaine', null);
	if(isset($_SERVER['HTTP_REFERER'])){
		$_wome['url_precedente'] = $_SERVER['HTTP_REFERER'];
		$_wome['adresse_precedente'] = parse_url($_wome['url_precedente']);
		$_wome['depuis_site'] = ($_wome['adresse_precedente']['host'] == $_wome['adresse']['domaine']);
		if($_wome['depuis_site']){
			$_wome['url_precedente'] = $_wome['adresse_precedente']['path'];
		};
	}else{
		$_wome['url_precedente'] = null;
		$_wome['adresse_precedente'] = null;
		$_wome['depuis_site'] = false;
	};
	
	//donnees ajax
	$_wome['donnees'] = si_defini($_POST, 'donnees', []);
	$_wome['depuis_ajax'] = isset($_SERVER['HTTP_X_REQUESTED_WITH']);
	if($_wome['depuis_ajax']){
		$_wome['url_ajax'] = si_defini($_wome['donnees'], 'url_envoi', $_wome['url_precedente'] );
	}else{
		$_wome['url_ajax'] = $_wome['url_precedente'];
	};

	
	
	//===== LECTURE DE LA CONFIGURATION =====
	logs(null, 'reading configuration');
	
	//- lit configuration generale -
	require('wome_conf.php');

	//selection site suivant nom de domaine
	if(isset($conf) 
	&& isset($conf['DOMAINS'])){
		$_config = array_replace_recursive($_config, $conf);
		unset($conf);
		
		//parcour tous les noms de domaine
		foreach($_config['DOMAINS'] as $acces => $domaine){
			//si acces pour tous, ou ce nom de domaine
			if($acces === '*' 
			|| $acces === $_wome['adresse']['domaine'] 
			|| (is_numeric($acces) && is_integer($acces) 
			 && ($_wome['adresse']['domaine']==$domaine || $_config['OPTIONS']['allow_domains_access']))
			){
				if(is_callable($domaine)){
					//si fonction, le ndd est le retour
					$ndd = $domaine($_wome);
					if($ndd){
						$_wome['site'] = $ndd;
						chdir($_wome['site'].'/');
						break;
					};
				}else{
					//sinon, $domaine est le ndd
					$_wome['site'] = $domaine;
					chdir($_wome['site'].'/');
					break;
				};
			};
		}
		
		//si le ndd n'a pas été trouvé, erreur 200
		if(!isset($_wome['site'])){
			erreur(200);
		};
	}else{
		//si $conf est absent, erreur 100
		erreur(100);
	};
	
	//- lit configuration du site -
	require('conf.php');
	if(isset($conf)){
		$_config = array_replace_recursive($_config, $conf);
		unset($conf);
		
		$l_pages = si_defini($_config, 'PAGES', []);
		$l_scripts = si_defini($_config, 'SCRIPTS', []);
		$l_langues = si_defini($_config, 'LANGUAGES', []);
		$l_groupes = si_defini($_config, 'GROUPS', []);
		$l_redirections = si_defini($_config, 'REDIRECTIONS', []);
		$_options = si_defini($_config, 'OPTIONS', []);
	}else{
		erreur(101);
	};
	
	
	
	//===== RECUPERATION DE L'ADRESSE =====
	
	//detection pages (par ajax ou de l'url)
	$url = null;
	
	if($_wome['depuis_ajax'] && isset($_POST['page'])){
		$url = $_POST['page'];
	}elseif(isset($_GET['page']) && $_GET['page']!=''){
		$url = $_GET['page'];
	}elseif(isset($_options['defaut_page_url'])
		 && $_options['defaut_page_url'] != null){
		$url = $_options['defaut_page_url'];
	}else{
		$url = $_wome['url_origine'];
	};
	
	
	
	//===== AUTHENTIFICATION DE L'UTILISATEUR =====
	logs(null, 'authentificate user');
	
	session_start();
	//session_destroy();
	$_user[$_options['user_login_index']] = false;
	
	if(isset($_config['USER']) 
	&& is_callable($_config['USER'])){
		$_user = $_config['USER']();
	};
	
	//variables de session
	if(!isset($_SESSION['dates'])){$_SESSION['dates'] = [];};
	
	
	
	//===== LISTE LES SCRIPTS =====
	
	//recupere les scripts
	$scripts = si_defini($_POST, 'scripts', []);
	
	//tri les scripts suivant l'ordre de définition
	$scripts_triees = [];
	foreach($l_scripts as $nom_liste=>$script_liste){
		$script_trouve = false;
		foreach($scripts as $numero=>$script){
			if($nom_liste == $script[0]){$script_trouve = $script[0];};
		}
		if($script_trouve != false){
			$scripts_triees[] = [$numero, $script_trouve];
		};
	}
	$scripts = $scripts_triees;
	
	
	//boucle tant que la page est redirigée
	do{
		
		
		//===== CHARGEMENT DES PLUGINS =====
		logs('LOADING PLUGINS');
		
		//pour chaque plugin déclaré
		foreach(si_defini($_config, 'PLUGINS', []) as $index=>$valeur){		
			$plugin_nom = null;
			
			//si simple nom de plugin, verifi qu'il existe et qu'il n'est pas chargé
			if(is_integer($index)
			&& !in_array($valeur, $plugins) 
			&& file_exists($_config['DIRECTORIES']['plugins'].'/'.$valeur.'.php')){
				$plugin_nom = $valeur;
			};
			
			//si fonction, execute la fonction et verifi que le plugin existe et qu'il n'est pas chargé
			if(is_callable($valeur) 
			&& !in_array($index, $plugins) 
			&& file_exists($_config['DIRECTORIES']['plugins'].'/'.$index.'.php') 
			&& $valeur()){
				$plugin_nom = $index;
			};
			
			//vérifi son existence
			if($plugin_nom != null){
				//inclu le plugin
				$wome_plugin_url = $_config['DIRECTORIES']['plugins'].'/'.$plugin_nom.'.php';
				$fonction = function($wome_plugin_url){
					include($wome_plugin_url);
				
					if(isset($conf)){
						return $conf;
					};
				};
				
				//execute son code
				$conf = $fonction($wome_plugin_url);
				
				//ajoute la configuration
				if(is_array($conf)){
					$_config = array_replace_recursive($_config, $conf);
					$plugins[] = $plugin_nom;
					logs(null, '(<I>'.$plugin_nom.'</I> plugin loaded)');
				};
			};
		}
		
		
		
		//===== TRAITEMENT DES SCRIPTS =====		
		logs('SCRIPTS PROCESSING', count($scripts).' scripts');
		
		$repertoire_scripts = $_config['DIRECTORIES']['scripts'];
		
		//parcour les scripts
		foreach($scripts as $numero=>$temp){			
			list($script_numero, $script_nom) = $temp;
			$donnees = $_POST['scripts'][$numero][1];
			$proprietes = $l_scripts[$script_nom];
			$fonction_nom = 'script_'.$script_nom;
			$wome_url_fichier = $repertoire_scripts.'/'.$proprietes[0];
			
			logs(null, 'executing script (<I>'.$script_nom.'</I>)');
			
			//execute le script dans une fonction
			$fonction = function($wome_url_fichier, $wome_fonction_nom, $wome_donnees){
				include($wome_url_fichier);
				return $wome_fonction_nom($wome_donnees);
			};
			
			$retour['scripts'][$script_numero] = $fonction($wome_url_fichier, $fonction_nom, $donnees);
			unset($scripts[$numero]);
		}
		
		
		
		//===== TRAITEMENT DE LA PAGE =====
		
		//SI c'est le premier traitement
		//OU que la page a été redirigée
		if(!isset($rediriger) || $rediriger==true){
		
			$rediriger = false;
			$cache = [];
			$cache_nomprecedent = null;
			$cache_groupeprecedent = null;
			$cache_precedent_lu = false;
			$retour = [];
			$meta = $meta_morceau = [
				'html' => '',
				'js' => '',
				'js_init' => '',
				'css' => '',
				'fichiers_js' => [],
				'fichiers_css' => [],
			];
			
			$donnees = traiter_url($url);
			$nouvelle_url = $donnees[0];
			$_wome['url_relative'] = $donnees[1];
			$_wome['url_locale'] = $donnees[2];
			$langue = $donnees[3];
			$_page = $donnees[4];
			
			
			//s'il y a une page à traiter
			if($_page){
				//met à jour l'adresse (si elle est différente)
				if($nouvelle_url != $url){
					$url = $nouvelle_url;
					$adresse = url_vers_adresse($url);
				};
				
				//charge les proprietes de la derniere page connue
				$l_pages_ref = $l_pages;
				
				foreach($adresse as $nom_local){
					$nom_general = page_nomgeneral($nom_local);
					
					//si la page est connue, enregistre proprietes
					if(isset($l_pages_ref[$nom_general])){
						$page['proprietes'] = $l_pages_ref[$nom_general];
					}else{
						break;
					};
				}
				
				
				//----- CACHE ET EXECUTION DE LA PAGE -----
				
				//détermine les proprietes de la page
				$_page['html'] = '';
				$_page['entete_css'] = '';
				$_page['entete_js'] = '';
				$_page['titre'] = valeur($_options['defaut_title']);
				$_page['langue'] = $langue;
				
				//si l'acces est permis
				if(si_dansgroupe($_page['proprietes'][1])){
					$repertoire_cache = $_config['DIRECTORIES']['cache'].'/'.$langue.'/'.implode('_', $adresse);
					$repertoire_index = $repertoire_cache.'/index_'.$_user[$_options['user_groupe_index']];
					
					$si_cache = false;
					
					//si les fichiers cache existent et sont à jour
					if($_options['use_cache'] 
					&& file_exists($repertoire_index) 
					&& $_page['proprietes'][2] != false 
					&& filemtime($repertoire_index) > (time() - $_page['proprietes'][2])){
						
						$si_cache = true;
						$morceaux = file_get_contents($repertoire_index);
						$morceaux = explode("\r\n", $morceaux);
						
						//verifie que les morceaux existent
						foreach($morceaux as $morceau_nom){
							if($morceau_nom != ''){
								//change le nom du morceau si morceau "unique"
								if($_user[$_options['user_login_index']]){
									$groupe_nom = '_'.base62_encode($_options['groupe_user']);
									$morceau_nom = str_replace($groupe_nom, $groupe_nom.$_user[$_options['user_unique_index']], $morceau_nom);
								};
								if(!file_exists($repertoire_cache.'/'.$morceau_nom)){
									$si_cache = false;
									break;
								};
							};
						}
					};
					
					//si on peut lire le cache
					if($si_cache){
						//LECTURE DU CACHE
						logs(null, 'reading cache');
						
						//lit tous les morceaux
						foreach($morceaux as $morceau_nom){
							if($morceau_nom != ''){
								//change le nom du morceau si morceau "unique"
								if($_user[$_options['user_login_index']]){
									$groupe_nom = '_'.base62_encode($_options['groupe_user']);
									$morceau_nom = str_replace($groupe_nom, $groupe_nom.$_user[$_options['user_unique_index']], $morceau_nom);
								};
								
								//lit le morceau
								$donnees = unserialize(file_get_contents($repertoire_cache.'/'.$morceau_nom));
								if(is_array($donnees)){
									list($_page, $meta) = completer_cache($_page, $meta, $donnees);
									
									actualiser_js();
									actualiser_css();
								};
							};
						};
					}else{
						//EXECUTION DE LA PAGE
						logs(null, 'executing page');
						
						//noms par defaut
						$cache_nomprecedent = $_options['start_cache_name'];
						$cache_groupeprecedent = $_options['defaut_cache_groupe'];
						
						//execute la page dans une fonction				
						$wome_url_fichier = $_config['DIRECTORIES']['pages'].'/'.$_page['proprietes'][0];
						$fonction = function($wome_url_fichier){
							include($wome_url_fichier);
						};
						
						ob_start();
						cache($_options['start_cache_name']);
						$fonction($wome_url_fichier);
						cache($_options['end_cache_name']);
						ob_end_clean();					
						
						//si la page ne doit pas être redirigée, continu les operations
						if(!$rediriger){
							if($_options['use_cache']){
								if($_page['proprietes'][2] && !empty($cache)){
									
									//ECRITURE DU CACHE
									logs(null, 'writing cache');
									$liste_morceaux = '';
									
									//créé le répertoire du cache s'il n'existe pas
									if(!file_exists($repertoire_cache)){
										creer_dossier($repertoire_cache,0700);
									};
									
									//parcour chaque morceau
									foreach($cache as $morceau_nom=>$morceau){
										//ajoute le morceau a $_page
										list($_page, $meta) = completer_cache($_page, $meta, $morceau);
										$liste_morceaux .= $morceau_nom."\r\n";
										
										//si le morceau n'a pas été lu
										if($morceau['cache_lu'] == false){
											//change le nom du fichier si le morceau est personnel
											if($_user[$_options['user_login_index']]){
												$morceau_nom = str_replace($_options['groupe_user'], $_options['groupe_user'].$_user[$options['user_unique_index']], $morceau_nom);
											};
											unset($morceau['cache_lu']);
											file_put_contents($repertoire_cache.'/'.$morceau_nom, serialize($morceau));
										};
									}
									actualiser_js();
									actualiser_css();
									
									file_put_contents($repertoire_index, $liste_morceaux);
								};
							}else{
								//si le cache est desactive
								$_page = array_replace($_page, $cache);
							};
						};
					};
					
					//calcul des logs (après le cache : ils ne sont pas enregistrés)
					if($_options['use_logs']){
						$_page['logs'] = '	<DIV id="WOME_logs_window" style="position:fixed;z-index:999;bottom:30px;left:30px;padding:10px;background:white;border:1px solid rgb(175,175,175);font-size:11px;box-shadow:0 0 5px rgba(0,0,0,0.2);border-radius:5px;">
											<DIV style="position:relative;">
												<CENTER><img height="25" src="data:image/jpeg;base64,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" /></CENTER>
												<DIV style="position:absolute;top:0px;right:0px;">
													<BUTTON style="border:none;background:none;cursor:pointer;-webkit-appearance:none;appearance:none;" onclick="document.getElementById(\'WOME_logs_window\').style.visibility=\'hidden\';">x</BUTTON>
												</DIV>
											</DIV>
											<DIV id="WOME_logs" style="margin-top:10px;padding:7px;max-height:300px;max-width:300px;white-space:nowrap;overflow:scroll;background:rgb(250,250,250);border:1px solid rgb(230,230,230);">';
											
						foreach($logs['execution'] as $numero=>$donnees){
							if($donnees[1] != null && $numero != 0){$_page['logs'] .= '</BR>';};
							$_page['logs'] .= $donnees[0].' ms - ';
							if($donnees[1] != null){$_page['logs'] .= '<B>'.$donnees[1].'</B>';};
							if($donnees[1] != null && $donnees[2] != null){$_page['logs'] .= ' : ';};
							if($donnees[2] != null){$_page['logs'] .= $donnees[2];};
							$_page['logs'] .= '<BR/>';
						};
						$temps = number_format((float)(microtime(true)-$temps_debut)*1000, 2, '.', '');
						$_page['logs'].= '<BR/>==============================<BR/>
											Page generated in <B>'.$temps.' ms</B><BR/>
											<B>'.$logs['request_number'].'</B> DB requests<BR/>
											</DIV>
											</DIV>';
					};
					
					
					//si la page ne doit pas être redirigée, continu les operations
					if(!$rediriger){
					
						//AJOUT DE LA PARTIE STATIQUE
						
						//ajout des fichiers et scripts JS nécéssaires à WOME client
						if($_options['use_WOME_client']){
							inclure_fichier('js', $_config['DIRECTORIES']['javascript'].'/jquery.js');
							inclure_fichier('js', 'wome.js');
							inclure_js('
								e_wome = {
									version: "'.$_wome['version'].'",
									site: "'.$_wome['site'].'",
									logs: "",
								};
								
								e_page = {
									url_relative: "'.$_page['url_relative'].'",
									url_locale: "'.$_page['url_locale'].'",
									titre: "'.$_page['titre'].'",
									langue: "'.$_page['langue'].'",
								};
								
								e_user = {
									connecte: "'.$_user['connecte'].'",
								};
							');
						};
						
						//si la page n'est pas appellée depuis ajax
						//et qu'on doit ajouter le HTML statique
						if($_page['proprietes'][3] && !$_wome['depuis_ajax']){
							logs(null, 'executing static page');
							
							//execute la fonction de la page statique
							if(is_callable($_config['HTML'])){
								ob_start();
								$_config['HTML']($_wome, $_page, $_user);
								$_page['html'] = ob_get_contents();
								ob_end_clean();
							};
						};
					};
				}else{
					logs(null, 'access denied');
				};
				$retour['page'] = $_page;
			};
		};
		
	}while($rediriger || !empty($scripts));
	
	
	
	//===== ENVOI DE LA REPONSE =====
	
	if(!$_wome['depuis_ajax'] && isset($retour['page'])){
		echo($retour['page']['html']);
	}else{
		echo(json_encode($retour));
	};
	
	
	//construit html final
	// $html = [
		// 'titre' => $_page['titre'],
		// 'entete' => $entete,
		// 'contenu' => $reponses['page'],
		// 'url' => $_page['url_locale'],
		// 'relative' => $_page['url_relative'],
		// 'logs' => $html_logs
	// ];
?>