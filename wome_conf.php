<?php $conf = [
// -------------------------------
// 	  WOME CONFIGURATION FILE
// -------------------------------


'DOMAINS' => [
	//'127.0.0.1' => 'manager'
	'bookino.org',
],


//  DEFAUT PAGE CONFIG
// Don't delete defaut configuration.


'DIRECTORIES' => [
	'cache' => 'cache',				
	'css' => 'css',
	'fichiers' => 'datas',
	'images' => 'img',
	'javascript' => 'js',
	'langues' => 'langues',		
	'pages' => 'pages',
	'plugins' => 'plugins',
	'polices' => 'fonts',
	'scripts' => 'scripts',
],


'LANGUAGES' => [
	'en' => ['English', 'Language'],
	'fr' => ['Français', 'Langue'],
],


'PLUGINS' => [
	'formulaires',
],


'OPTIONS' => [
	'allow_domains_access' => false,
	
	'use_logs' => true,
	'use_WOME_client' => true,
	
	'use_cache' => false,
		'cache_each_parts' => false,
		
		'defaut_cache_groupe' => '',
		'start_cache_name' => 'wome_start',
		'end_cache_name' => 'wome_end',
	
	'use_database' => true,
		'db_host' => 'localhost',
		'db_name' => 'bookino',
		'db_username' => 'root',
		'db_password' => '',
	
	'defaut_page_url' => '/home',
	'hide_defaut_url' => true,
		'hide_language_in_defaut_url' => true,
		'force_hide_defaut_url' => true,
	'script_page_url' => '/script',
	
	'defaut_title' => function($_wome, $_page, $_user){
		global $l;
		
		if(isset($l['PAGE:TITRE'][$_page['nom']])){
			return $l['PAGE:TITRE'][$_page['nom']].' - '.$_wome['site'];
		}else{
			return $_page['titre'] = $_wome['site'];
		};
	},
	'404_page' => 'ERREUR404',
	
	'optimize_accounts' => true,
	'groupe_user' => 'user',
	'user_unique_index' => 'id',
	'user_login_index' => 'connecte',
	'user_groupe_index' => 'groupe',
	
	'user_save_language' => true,
		'user_language_index' => 'langue',
		
	'cookie_language' => crypter('cookie_BookinoLangue'),
	'cookie_account' => crypter('cookie_BookinoConnexion'),
],

];?>