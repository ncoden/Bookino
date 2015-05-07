<?php $conf = [
// --------------------------------------
// 	WOME CONFIGURATION FILE FOR WEBSITE
//				bookino
// --------------------------------------


'PAGES' => [
//	|NOM DE LA PAGE						|ADRESSE DE GENERATION	|ACCESS				|CACHE	|HTML	|CSS	|JS		|SOUSPAGES
					/*SIGNIGICATION :*/	['adress',				'access',			'cache','html',	'css',	'js',	'underpages',],
	
	'ACCEUIL' 						=> ['acceuil.php', 			'*', 				5*60,	true,	false,	false,	null],
	'ACTUALITES' 					=> ['actualites.php', 		'connectes', 		5*60,	true,	false,	false,	null],
	'CAPTCHA'						=> ['captcha.php', 			'*', 				false,	false,	false,	false,	null],
	'CONNEXION'						=> ['connexion.php', 		'nonconnectes', 	5*60,	true,	false,	false,	null],
	'DECONNEXION'					=> ['deconnexion.php', 		'connectes', 		5*60,	true,	false,	false,	null],
	'ERREUR404'						=> ['erreur.php',			'*', 				3600,	true,	false,	false,	null],
	'INSCRIPTION'					=> ['inscription.php', 		'nonconnectes', 	5*60,	true,	false,	false,	null],
	'LIVRE'							=> ['livre.php', 			'*', 				5*60,	true,	false,	true,	'*'],
	'LIVRES'						=> ['livres.php', 			'*', 				5*60,	true,	false,	false,	[
		'LIVRES_MOI'					=> ['livres.php',			'connectes',		5*60,	true,	false,	false,	null],
		'LIVRES_FINIS'					=> ['livres.php',			'connectes',		5*60,	true,	false,	false,	null],
		'LIVRES_ENCOURS'				=> ['livres.php',			'connectes',		5*60,	true,	false,	false,	null],
		'LIVRES_PROPOSITIONS'			=> ['livres.php',			'connectes',		5*60,	true,	false,	false,	null],
		'LIVRE_CREER'	 				=> ['livre_creer.php', 		'connectes',		5*60,	true,	false,	false,	null],]],
	'PARAMETRES'					=> ['parametres.php', 		'connectes', 		5*60,	true,	false,	false,	null],
	'UTILISATEUR'					=> ['utilisateur.php', 		'*',				5*60,	true,	false,	false,	'*'],
		
	'TEST'							=> ['test.php', 			'*',				false,	false,	false,	false,	null],
],


'SCRIPTS' => [
//	|NOM DU SCRIPT						|ADRESSE DE GENERATION			|ACCESS	
					/*SIGNIGICATION :*/	['adress',						'access',],

	'rechercher' 					=> ['fonctions_communes.php',		'*'],
	
	'discussion_ouvrir'				=> ['fonctions_utilisateur.php',	'connectes'],
	'discussion_fermer'				=> ['fonctions_utilisateur.php',	'connectes'],
	'discussion_afficher'			=> ['fonctions_utilisateur.php',	'connectes'],
	'discussion_cacher'				=> ['fonctions_utilisateur.php',	'connectes'],
	'discussion_lu'					=> ['fonctions_utilisateur.php',	'connectes'],
	'discussion_ecrire'				=> ['fonctions_utilisateur.php',	'connectes'],
	'discussion_activites'			=> ['fonctions_utilisateur.php',	'connectes'],
	'menu_afficher'					=> ['fonctions_utilisateur.php',	'connectes'],
	'menu_cacher'					=> ['fonctions_utilisateur.php',	'connectes'],
	'autreuser_suivre'				=> ['fonctions_utilisateur.php',	'connectes'],
	'autreuser_plussuivre'			=> ['fonctions_utilisateur.php',	'connectes'],
	'message_envoyer'				=> ['fonctions_utilisateur.php',	'connectes'],
	
	'notifs_recuperer'				=> ['fonctions_utilisateur.php',	'connectes'],
	'notifs_vues'					=> ['fonctions_utilisateur.php',	'connectes'],
	'notif_supprimer'				=> ['fonctions_utilisateur.php',	'connectes'],
	'membres_recuperer'				=> ['fonctions_utilisateur.php',	'connectes'],
	'messages_recuperer'			=> ['fonctions_utilisateur.php',	'connectes'],
	
	'voter'							=> ['fonctions_paragraphe.php',		'connectes'],
	'paragraphe_main'				=> ['fonctions_paragraphe.php',		'connectes'],
	'paragraphe_alerte'				=> ['fonctions_paragraphe.php',		'connectes'],
	'paragraphe_liberer'			=> ['fonctions_paragraphe.php',		'connectes'],
	'paragraphe_actualiser'			=> ['fonctions_paragraphe.php',		'*'],
	'paragraphe_envoyer'			=> ['fonctions_paragraphe.php',		'connectes'],
	'paragraphe_valider'			=> ['fonctions_paragraphe.php',		'connectes'],
	'paragraphes_recuperer'			=> ['fonctions_paragraphe.php',		'*'],
	'paragraphes_ecrire'			=> ['fonctions_paragraphe.php',		'*'],
	
	'inscription_pseudo'			=> ['fonctions_inscription.php',	'nonconnectes'],
	'inscription_email'				=> ['fonctions_inscription.php',	'nonconnectes'],
	'inscription_testchangeremail'	=> ['fonctions_inscription.php',	'nonconnectes'],
	'inscription_renvoyeremail'		=> ['fonctions_inscription.php',	'nonconnectes'],
	'inscription_changeremail'		=> ['fonctions_inscription.php',	'nonconnectes'],
],


'USER' => function(){
	//FONCTION D'IDENTIFICATION DE L'UTILISATEUR
	
	global $_config;
	global $_options;
	$_user = [];
	
	//si le cookie de connexion est présent, verifie session
	if(isset($_COOKIE[$_options['cookie_account']])){
		$connexion_cle = decrypter($_COOKIE[$_options['cookie_account']]);
		
		//si la session existe déjà, reprend l'utilisateur en mémoire
		if(isset($_SESSION['user']['connecte']) 
		&& $_SESSION['user']['connecte']==true 
		&& $_options['optimize_accounts']){
			$_user = $_SESSION['user'];
			$_user['connecte'] = true;
		}else{
			//sinon, verifi la clé de connexion
			$sql_rep = SELECT('connexions', ['cle'=>$connexion_cle], ['idUtilisateur']);
			if(!empty($sql_rep)){
				$_user['id'] = $sql_rep[0]['idUtilisateur'];
				
				//verifi que l'utilisateur existe
				$sql_rep = SELECT($options['users_table'], ['id'=>$user_id]);
				if(!empty($sql_rep)){
					//recupere les donnees de l'utilisateur
					$_user = $sql_rep[0];
					$_user['connecte'] = true;
					
					//recupere les données de l'interface
					$sql_rep = SELECT('interfaces', ['idUtilisateur'=>$_user['id']]);
					$_user['interfaces'] = [];
					$_user['interfaces']['discussions'] = [];
					
					foreach($sql_rep as $valeur){
						if($valeur['type'] == 'menu'){
							$_user['interfaces']['menu'] = $valeur['valeur'];
						};
						if($valeur['type'] == 'privee'){
							$_user['interfaces']['discussions'][$valeur['id']] = [$valeur['valeur'], 'privee'];
						};
					}
					
					//cree repertoire si inexistant
					if(!file_exists($_wome['site'].'/'.$_config['DIRECTORIES']['fichiers'].'/users/'.crypter($_user['cle']))){
						creer_dossier($_wome['site'].'/'.$_config['DIRECTORIES']['fichiers'].'/users/'.crypter($_user['cle']), 0700);
					};
				};
			};
		};
	};

	//si utilisateur non authentifié, créé un profil par defaut
	if(empty($_user)){
		$_user['connecte'] = false;
	};
	
	//défini les groupes
	list($_user['groupes'], $_user['groupe']) = lister_groupes($_user);
	
	//sauvegarde cookie et enregistre
	if($_user['connecte'] && $_options['optimize_accounts']){
		setcookie(crypter($_options['cookie_account']), crypter($connexion_cle), time()+7*24*3600, '/');
		$_SESSION['user'] = $_user;
	};
	
	//met a jour utilisateur
	if($_user['connecte']){
		UPDATE('utilisateurs', ['id'=>$_user['id']], ['date_enligne'=>$datetime, 'enligne'=>'1']);
	};
	
	return $_user;
},


'GROUPS' => [
	//personnes connectées
	'connectes' => [
		function($_user){
			return $_user['connecte'];
		},[
			//administrateurs
			'administrateurs' => [
				function($_user){
					return $_user['poste']=='administrateur';
				}, null
			],
			//moderateurs
			'moderateurs' => [
				function($_user){
					return $_user['poste']=='moderateur';
				}, null
			],
		]
	],
	//personnes non connectées
	'nonconnectes' => [
		function($_user){
			return !$_user['connecte'];
		}, null
	],
],


'REDIRECTIONS' => [
	'visibles' => [
		function($_wome,$_config,$_user,$_page){
			global $_user;
			//quand l'utilisateur n'est pas connecté
			if($_page['proprietes'][1]=='connectes' && !$_user['connecte']){
				return page_nomlocal('connexion');
			};
			//quand l'utilisateur est connecté
			if($_page['proprietes'][1]=='nonconnectes' && $_user['connecte']){
				return page_nomlocal('utilisateur').'/'.$_user['pseudo'];
			};
		},
	],
	'invisibles' => [
		
	],
],


'DIRECTORIES' => [
	//use defaut configuration
],


'LANGUAGES' => [
	//use defaut configuration
],


'PLUGINS' => [
	//use defaut configuration
],


'OPTIONS' => [
	//use defaut configuration
],


'HTML' => function($_wome, $_page, $_user){
	global $_page;
	global $_config;
	global $l;
	
	$langue = $_page['langue'];
	$l_langues = $_config['LANGUAGES'];
	
	header('Content-Type: text/html; charset=utf-8');
	
	$js_discussions = '';
	$html_discussions = '';
	$html_menuouvert = '';
	
	//si connecte
	if($_user['connecte']){
		//prepare code html du menu
		if(isset($_user['interfaces']['menu'])){
			$html_menuouvert = ' chatactive '.$_user['interfaces']['menu'];
		};
		
		//prepare code js et html des discussions
		foreach($_user['interfaces']['discussions'] as $id=>$valeur){
			$etat = $valeur[0];
			$type = $valeur[1];
			
			$discussion = INFOS('discussion_privee', ['id'=>$id]);
			$cle = crypter($discussion['cle']);
			if($discussion['idUtilisateur_1'] == $_user['id']){
				$autreuser_id = $discussion['idUtilisateur_2'];
			}else{
				$autreuser_id = $discussion['idUtilisateur_1'];
			};
			
			$autreuser = INFOS('user', ['id'=>$autreuser_id]);
			$autreuser_cle = crypter($autreuser['cle']);
			
			$html_discussions .= '	<DIV class="C_discussion chargement prechargee '.$etat.'" id="discussion_'.$cle.'" pour="discussion_active" cle="'.$cle.'" user_cle="'.$autreuser_cle.'">
										<DIV class="icon" pour="afficher_user_etat" cle="'.$autreuser_cle.'"></DIV>
										<DIV class="C_grandezone_titre" pour="discussion_afficher">'.$autreuser['pseudo'].'</DIV>
										<DIV class="icon fermer" pour="discussion_fermer"></DIV>
										<DIV class="texte" pour="discussion_lu" scrollcontrole>
											<DIV class="I_marges" id="discussion_texte_'.$cle.'"></DIV>
											<DIV class="info" id="discussion_info_'.$cle.'"></DIV>
										</DIV>
										<INPUT type="text" class="champ" pour="discussion_envoyer"/>
									</DIV>';
			inclure_js('discussion_ouvrir("'.$cle.'", "'.$autreuser['pseudo'].'", "'.$autreuser_cle.'", "'.$etat.'");');
		}
	};
	
	inclure_fichier('css', 'css/style.css');
	
	echo('	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<HTML xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$langue.'" lang="'.$langue.'">
			<HEAD>
				<META http-equiv="content-type" content="text/html; charset=UTF-8"/>
				<META http-equiv="content-language" content="'.$langue.'"/>
				<META name="language" content="'.$langue.'"/>
				<TITLE>'.$_page['titre'].'</TITLE>
				<LINK rel="icon" href="http://f.bookino.org/img/logo/favicon.ico"/>
				<SCRIPT type="text/javascript" src="http://f.bookino.org/langues/'.$langue.'.js"></SCRIPT>');
	
	//js du connecte
	if($_user['connecte']){
		inclure_fichier('js', 'js/script_connecte.js');
		inclure_js('e_user.cle = "'.crypter($_user['cle']).'";
					e_user.pseudo = "'.$_user['pseudo'].'";');
	};
	
	//js du menu
	if(isset($_user['interfaces']['menu'])){
		inclure_js('var menu_ouvert = true;
					var menu_precedent = "'.$_user['interfaces']['menu'].'";');
	};
	
	echo(		  $_page['entete_js'].'
				'.$_page['entete_css'].'
			</HEAD>
			
			<BODY>
			'.$_page['logs'].'
			<DIV class="POS_head"><TABLE><TBODY><TR>
			
			<TD class="POS_Bookino'.$html_menuouvert.'">
				<DIV class="titre">
					<DIV class="I_marges">
						<DIV class="bookino">
							<A href="/'.$langue.'"><DIV class="U_logo_bookino"></DIV></A>
							<DIV class="langues '.$langue.'">
								<DIV class="C_info blanc bas sansmarges">
									<DIV class="contenu"><DIV class="texte">
										<DIV class="titre">');
											if($langue != null){
												echo($l_langues[$langue][1]);
											}; echo('
										</DIV>');
											foreach($l_langues as $lan => $valeur){echo('
											<A href="http://bookino.org'.$_page['url_locale'].'?language='.$lan.'" class="U_langue" langue="'.$lan.'">
												<DIV class="C_grandezone_liste">
													<DIV class="icon I_gauche '.$lan.'"></DIV>
													'.$l_langues[$lan][0].'
												</DIV>
											</A>');
	}
										echo('
									</DIV></DIV>
								</DIV>
							</DIV>
						</DIV>
						
						<DIV class="C_champ moyen recherche_champ" fermer>
							<INPUT type="text" id="recherche_champ" value="'.$l['M']['RECHERCHER'].'" defaut="'.$l['M']['RECHERCHER'].'"/>
							<DIV class="fermer" pour="champ_fermer"><DIV class="icon"></DIV></DIV>
						</DIV>
	');
	
	if($_user['connecte']){echo('
						<DIV class="connecte">
							<A href="/'.$langue.'/'.$l['PAGE:NOM']['UTILISATEUR'].'/'.$_user['pseudo'].'" class="C_user">
								<IMG class="image" 
									 src="http://f.bookino.org/datas/users/'.crypter($_user['cle']).'/profil_45.png"
									 info="'.$_user['pseudo'].';noir;gauche;fixe"/>
							</A>
							
							<DIV class="notif amis" id="bouton_menu_amis" pour="menu_afficher" visee="amis">
								<DIV class="icon"></DIV>
								<DIV class="texte"></DIV>
							</DIV>
							
							<DIV class="notif infos" id="bouton_menu_infos" pour="menu_afficher" visee="infos">
								<DIV class="icon"></DIV>
								<DIV class="texte"></DIV>
							</DIV>
							
							<DIV class="notif general" id="bouton_menu_general" pour="menu_afficher" visee="general">
								<DIV class="icon"></DIV>
								<DIV class="texte"></DIV>
							</DIV>
						</DIV>
	');}else{echo('
						<DIV class="connexion">
							<A href="/'.$langue.'/'.$l['PAGE:NOM']['INSCRIPTION'].'" class="C_bouton blanc grand">'.$l['M']['SINSCRIRE'].'</A>
							<A href="/'.$langue.'/'.$l['PAGE:NOM']['CONNEXION'].'" class="C_bouton bleu grand">'.$l['M']['SECONNECTER'].'</A>
						</DIV>
	');};
	
	echo('
					</DIV>
					<DIV class="chargement"><DIV class="lumiere"></DIV></DIV>
				</DIV>
				
				<DIV class="menu liens"><DIV class="contenu"><DIV class="ombres">
					<DIV class="I_marges">
							<DIV class="POS_recherche">
							<TABLE><TBODY><TR>
	');
	
	if($_user['connecte']){echo('
							<TD class="resultats personnes" id="recherche_personnes">
								<DIV class="contenu">
									<DIV class="titre">'.$l['HEAD']['AMIS_ET_SUIVIS'].'</DIV>
									<DIV class="nombre"></DIV>
									<DIV class="texte">
									</DIV>
								</DIV>
							</TD>
	');};
	
	echo('
							<TD class="resultats autrepersonnes" id="recherche_autrepersonnes">
								<DIV class="contenu">
	');
	
	if($_user['connecte']){echo('<DIV class="titre">'.$l['HEAD']['AUTRE_MEMBRES'].'</DIV>');};
	if(!$_user['connecte']){echo('<DIV class="titre">'.$l['M']['MEMBRES'].'</DIV>');};
	
	echo('
									<DIV class="nombre"></DIV>
									<DIV class="texte">
									</DIV>
								</DIV>
							</TD>
							<TD class="resultats projets" id="recherche_projets">
								<DIV class="contenu">
									<DIV class="titre">Projets</DIV>
									<DIV class="nombre"></DIV>
									<DIV class="texte"></DIV>
								</DIV>
							</TD>
							<TD class="resultats livres" id="recherche_livres">
								<DIV class="contenu">
									<DIV class="titre">'.m('LIVRES').'</DIV>
									<DIV class="nombre"></DIV>
									<DIV class="texte"></DIV>
								</DIV>
							</TD>
							</TR></TBODY></TABLE>
						</DIV>
						
						<DIV class="liens">
							<A href="#" class="I_lien">'.l('HEAD','ACCEUIL').'</A>
							<A href="#" class="I_lien">'.l('HEAD','CONCEPT').'</A>
							<A href="#" class="I_lien">'.l('HEAD','livres_ENCOURS').'</A>
							<A href="#" class="I_lien">'.l('HEAD','livres_TERMINES').'</A>
							<A href="#" class="I_lien I_droite U_liencontact">'.l('HEAD','CONTACT_PRESSE').'</A>
						</DIV>
					</DIV>
				</DIV></DIV></DIV>

			</TD>
	');
	
	if($_user['connecte']){echo('
			<TD class="POS_chat_top '.$html_menuouvert.'">

				<DIV class="titre">
					<DIV class="I_marges">
						<DIV class="onglet amis">
							<DIV class="icon"></DIV>
							<DIV class="titre">'.$l['M']['MEMBRES'].'</DIV>
						</DIV>
						
						<DIV class="onglet infos">
							<DIV class="icon"></DIV>
							<DIV class="titre">'.$l['M']['NOTIFICATIONS'].'</DIV>
						</DIV>
						
						<DIV class="onglet general">
							<DIV class="icon"></DIV>
							<DIV class="titre">'.$_user['pseudo'].'</DIV>
						</DIV>
					</DIV>
				</DIV>
				
				<DIV class="menu">
					<DIV class="I_marges">
						<DIV class="onglet amis">
							<DIV class="C_txt_titre2">Membres</DIV>
						</DIV>
														
						<DIV class="onglet infos">
							<DIV class="C_txt_titre2">Notifications</DIV>
						</DIV>
					</DIV>
				</DIV>
			</TD>
	');};
	
	echo('
			</TR></TBODY></TABLE></DIV>
				
			<TABLE class="POS_page"><TBODY><TR>
			<TD class="POS_contenu">
				<DIV class="POS_body">
	');

	//ecrit le contenu
	echo($_page['html']);

	//ecrit le bas
	echo('		</DIV>
				<DIV class="POS_prebottom"><DIV class="I_marges">
					<CENTER>
						Faites-nous connaitre !<BR/>
						<A class="social" href="http://facebook.com"><DIV class="C_icon t32 x0 y0 centre_h centre_v"></DIV></A>
						<A class="social" href="http://twitter.com"><DIV class="C_icon t32 x32 y0 centre_h centre_v"></DIV></A>
						<A class="social" href="http://plus.google.com"><DIV class="C_icon t32 x64 y0 centre_h centre_v"></DIV></A>
					</CENTER>
				</DIV></DIV>
				
				<DIV class="POS_bottom"><DIV class="I_marges">
					<TABLE><TBODY><TR>
						<TD>
							<DIV class="C_txt_titre2">Bookino</DIV>
							<DIV class="C_lien noir">Acceuil</DIV><BR/>
							<DIV class="C_lien noir">Le concept</DIV><BR/>
							<DIV class="C_lien noir">Livres en cours</DIV><BR/>
							<DIV class="C_lien noir">Livres terminés</DIV><BR/>
						</TD>');
	if($_user['connecte']){echo('
						<TD>
							<DIV class="C_txt_titre2">Mon espace</DIV>
							<DIV class="C_lien noir">Actualités</DIV><BR/>
							<DIV class="C_lien noir">Mon profil</DIV><BR/>
							<DIV class="C_lien noir">Mes participations</DIV><BR/>
							<DIV class="C_lien noir">Administration</DIV><BR/>
						</TD>');};
	echo('				<TD>
							<DIV class="C_txt_titre2">Communauté</DIV>
							<DIV class="C_lien noir">Proposer son aide</DIV><BR/>
							<DIV class="C_lien noir">Devenir rédacteur</DIV><BR/>
							<DIV class="C_lien noir">Devenir modérateur</DIV><BR/>
							<DIV class="C_lien noir">Faire un don</DIV><BR/>
						</TD>
						<TD>
							<DIV class="C_txt_titre2">Réseaux sociaux</DIV>
							<DIV class="C_lien noir">Facebook</DIV><BR/>
							<DIV class="C_lien noir">Twitter</DIV><BR/>
							<DIV class="C_lien noir">Google+</DIV><BR/>
						</TD>
						<TD>
							<DIV class="C_txt_titre2">Partenaires</DIV>
							<DIV class="C_lien noir">Devenir partenaire</DIV><BR/>
						</TD>
						<TD>
							<DIV class="C_txt_titre2">Contact</DIV>
							<DIV class="C_lien noir">Contacter Bookino</DIV><BR/>
							<DIV class="C_lien noir">Presse</DIV><BR/>
							<DIV class="C_lien noir">Publicitaires</DIV><BR/>
							<DIV class="C_lien noir">Copyright</DIV><BR/>
						</TD>
					</TR><TBODY></TABLE>
					<BR/>
					<DIV class="C_txt_description2">
						Copyright © 2013-2014. Tous droits réservés
					</DIV>
				</DIV></DIV>
			</TD>
	');
			
	if($_user['connecte']){echo('
			<TD class="POS_chat '.$html_menuouvert.'">
				<DIV class="I_marges">
					<DIV class="notifications"><DIV class="I_marges" id="notifications"></DIV></DIV>
					<DIV class="onglet amis init" id="menu_amis" scrollcontrole></DIV>
					<DIV class="onglet infos init" id="menu_infos" scrollcontrole></DIV>
					
					<DIV class="onglet general" id="menu_general" scrollcontrole>
						<DIV class="C_grandezone_titre">'.$l['M']['MOI'].'</DIV>
						<A href="/'.$langue.'/'.$l['PAGE:NOM']['ACTUALITES'].'"><DIV class="C_grandezone_liste">'.$l['M']['ACTUALITES'].'</DIV></A>
						<A href="/'.$langue.'/'.$l['PAGE:NOM']['UTILISATEUR'].'/'.$_user['pseudo'].'"><DIV class="C_grandezone_liste">'.$l['M']['MONPROFIL'].'</DIV></A>
						<A href="/'.$langue.'/'.$l['PAGE:NOM']['PARAMETRES'].'"><DIV class="C_grandezone_liste">'.$l['M']['PARAMETRES'].'</DIV></A>
						<DIV class="C_grandezone_liste" onclick="se_deconnecter();">'.$l['M']['SEDECONNECTER'].'</DIV>
						
						<DIV class="C_grandezone_titre">'.$l['M']['MESLIVRES'].'</DIV>
						<A href="/'.$langue.'/'.$l['PAGE:NOM']['LIVRES'].'"><DIV class="C_grandezone_liste">'.$l['M']['TOUSLESLIVRES'].'</DIV></A>
						<A href="/'.$langue.'/'.$l['PAGE:NOM']['LIVRES'].'/'.$l['PAGE:NOM']['LIVRES_MOI'].'"><DIV class="C_grandezone_liste">'.$l['M']['MESLIVRES'].'</DIV></A>
	');
	
	//mes livres
	$livres = LISTER('livre', ['idUtilisateur'=>$_user['id']]);
	foreach($livres as $id=>$livre){
		$titre = $livre['titre'];
		$nom = crypter(strval($id)).'-'.str_replace(' ', '-', $titre);
		
		echo('			<A href="/'.$langue.'/'.$l['PAGE:NOM']['LIVRE'].'/'.$livre['titre_simple'].'"><DIV class="C_grandezone_liste">&nbsp'.$titre.'</DIV></A>');
	}
	
	echo('				<DIV class="I_marges_10"><CENTER>
							<A href="/'.$langue.'/'.$l['PAGE:NOM']['LIVRE'].'/'.$l['PAGE:NOM']['LIVRE_CREER'].'" class="C_bouton blanc trespetit">'.$l['M']['CREERUNLIVRE'].'</A>
						</CENTER></DIV>
						
						<DIV class="C_grandezone_titre">'.$l['M']['MESPARTICIPATIONS'].'</DIV>
						
						<DIV class="C_grandezone_titre">'.$l['M']['RACCOURCIS'].'</DIV>
					</DIV>
					
					<DIV class="discussion">
						<DIV class="I_marges" id="discussions">
							<DIV class="reference"></DIV>
							'.$html_discussions.'
						</DIV>
					</DIV>
				</DIV>
			</TD>
	');};
	
	echo('
			</TR></TBODY></TABLE>
			
			<DIV class="POS_message">
				<DIV class="contenu">
					<DIV class="titre"></DIV>
					<DIV class="texte"></DIV>
					<DIV class="boutons"></DIV>
				</DIV>
			</DIV>
			<DIV class="POS_cache" scrollcontrole></DIV>
		</BODY>
		</HTML>
	');

},

];?>