<?php
	//GENERATEUR DE PAGE 'CONNEXION'

	if(isset($_GET[crypter('action')])){$action = decrypter($_GET[crypter('action')]);}else{$action='connexion';};
	if(isset($_POST[crypter('form_id')])){$form_id = decrypter($_POST[crypter('form_id')]);}else{$form_id='';};
	
	//===== FORMULAIRES =====
	//si connexion
	if($form_id=='connexion'){
		$datetime = (date('Y-m-d H:i:s'));
		$pseudo = trim($_POST[crypter('connexion_pseudo')]);
		$motdepasse = trim($_POST[crypter('connexion_motdepasse')]);
		$securite = intval(trim($_POST[crypter('connexion_securite')]));
		$securite1 = intval(trim($_POST[crypter('connexion_securite1')]));
		$securite2 = intval(trim($_POST[crypter('connexion_securite2')]));
		
		$erreur_connexion=null; 
		$erreur_securite=null;
		
		//test du compte
		$sql_rep = SELECT('utilisateurs', array('pseudo'=>$pseudo, 'motdepasse'=>md5($motdepasse)), array('cleUtilisateur','idUtilisateur','pseudo'));
		if(empty($sql_rep)){$erreur_connexion='mauvais';};
			
		//test de la securite
		if($securite==''){$erreur_securite='vide';}else{
		if($securite!=($securite1+$securite2)){$erreur_securite='different';};
		};

		//SI PAS D'ERREURS
		if(($erreur_connexion==null) && ($erreur_securite==null)){
			
			//créer clé de connexion
			$date = date('Y-m-d H:i:s');
			$autreuser['cle'] = $sql_rep[0]['cleUtilisateur'];
			$autreuser['id'] = $sql_rep[0]['idUtilisateur'];
			$autreuser['pseudo'] = $sql_rep[0]['pseudo'];
			$connexion_cle = cle('CON', 96);
			
			//ajoute utilisateur comme connecté
			INSERT('connexions', array('idUtilisateur'=>$autreuser['id'], 'cleConnexion'=>$connexion_cle, 'date'=>$date));
			setcookie(crypter('cookie_bookinoConnexion'), crypter($connexion_cle), time()+2*24*3600);
			$autreuser['connecte'] = true;
			UPDATE('utilisateurs', array('idUtilisateur'=>$autreuser['id']), array('enligne'=>'1', 'date_activite'=>$datetime));
			
			//redirige
			header('Location: /'.$langue.'/'.l('P_NOM:UTILISATEUR').'/'.$autreuser['pseudo']);
		}else{
			$autreuser['connecte'] = false;
			$action = 'connexion_corrige';
		};
		
	};

	
	
	//===== ECRITURE DE LA PAGE =====
	
	echo('
	<DIV class="POS_titre"><DIV class="I_marges">
		'.l('M:CONNEXION').'
	</DIV></DIV>
	<BR/>
	<BR/>
	');
	
	//si formulaire de connexion
	if($action=='connexion' || $action=='connexion_corrige'){
		
		if(isset($pseudo)){$champ_pseudo=$pseudo;}else{$champ_pseudo=l('M:PSEUDO');}; 
		if(isset($motdepasse)){$champ_motdepasse=$motdepasse;}else{$champ_motdepasse=l('M:MOTDEPASSE');}; 
		$securite1 = mt_rand(1,9);
		$securite2 = mt_rand(1,9);
		$securite = $securite1 + $securite2;
		
		echo('
		<DIV class="C_zone I_centre U_zone_connexion" id="zone_connexion"><DIV class="I_marges">
			<DIV class="contenu premier visible" id="zone_connexion_0">');
			echo(html_formulaire('connexion'));
			echo('
				<FORMS method="POST" action="/'.$langue.'/'.l('P_NOM:CONNEXION').'" id="form_connexion2" autocomplete="off">
				<CENTER><DIV class="C_zone_texte U_connexion_zonetexte">
					<DIV class="C_txt_titre1">'.l('M:CONNEXION').'</DIV>
					<DIV class="C_txt_erreur"></DIV>
					<DIV class="C_txt_description1">
						<DIV class="C_champ moyen I_marges_V requiere accept_icon">
							<INPUT type="text" name="'.crypter('connexion_pseudo').'" value="'.$champ_pseudo.'" defaut="'.l('M:PSEUDO').'"/><BR/>
						</DIV>
						<INPUT name="'.crypter('connexion_motdepasse').'" class="C_champ moyen I_marges_V requiere accept_icon" value="'.$champ_motdepasse.'" defaut="'.l('M:MOTDEPASSE').'" motdepasse="true"/><BR/>
						<DIV class="I_txt_droite"><BUTTON type="button" pour="zone_montrer" zone="connexion" niveau="1" class="C_lien petit">'.l('M:MDPPERDU?').'</BUTTON></DIV>
					</DIV>
				</DIV></CENTER>
				<DIV class="C_zone_valide">
					<CENTER><BUTTON type="submit" class="C_bouton bleu grand I_marges_V">'.l('M:VALIDER').'</BUTTON></CENTER>
					<DIV class="U_connexion_securite">
					<DIV class="C_securite">
						<DIV class="cadena"></DIV><DIV class="operation">'.$securite1.'+'.$securite2.' =
						</DIV><INPUT type="text" name="'.crypter('connexion_captcha').'" class="C_champ securite accept_border"/>
						<INPUT type="hidden" name="'.crypter('connexion_securite').'" id="'.crypter('connexion_securite').'" value="'.$securite.'"/>
						<INPUT type="hidden" name="'.crypter('connexion_securite1').'" value="'.$securite1.'"/>
						<INPUT type="hidden" name="'.crypter('connexion_securite2').'" value="'.$securite2.'"/>
						<INPUT type="hidden" name="'.crypter('form_id').'" value="'.crypter('connexion').'"/>
					</DIV>
					</DIV>
				</DIV>
				</FORM>
			</DIV>
			
			<DIV class="contenu second" id="zone_connexion_1">
				<FORM method="POST" action="/'.$langue.'/'.l('P_NOM:CONNEXION').'" id="form_recupmdp">
				<CENTER><DIV class="C_zone_texte">
					<DIV class="C_txt_titre1">'.l('M:RECUPERATIONMDP').'</DIV>
					<INPUT type="text" name="'.crypter('recupmdp_pseudo').'" class="C_champ moyen I_marges_V requiere accept_icon" value="'.$champ_pseudo.'" defaut="'.l('M:PSEUDO').'"/><BR/>
					<INPUT type="text" name="'.crypter('recupmdp_email').'" class="C_champ moyen I_marges_V requiere accept_icon" value="'.l('M:ADDREMAIL').'" defaut="'.l('M:ADDREMAIL').'"/><BR/>
					<DIV class="C_txt_description2 I_txt_centre">'.l('T_RECUPMDP:EMAILENVOYE').'</DIV>
				</DIV></CENTER>
				<DIV class="C_zone_valide">
					<CENTER>
						<TABLE><TR>
						<TD><BUTTON type="submit" class="C_bouton bleu moyen I_marges_V">'.l('M:VALIDER').'</BUTTON></TD>
						<TD><BUTTON type="button" class="C_bouton blanc moyen I_marges_V" pour="zone_montrer" zone="connexion" niveau="0">'.l('M:ANNULER').'</BUTTON></TD>
						</TR></TABLE>
					</CENTER>
				</DIV>
				</FORM>
			</DIV>
			
			<DIV class="contenu second" id="zone_connexion_2">
				<CENTER><DIV class="C_zone_texte">
					<DIV class="C_txt_titre1">'.l('M:RECUPERATIONMDP').'</DIV>
					<DIV class="C_txt_description1 I_txt_centre">
						'.l('T_RECUPMDP:OUVRIRAVANT').'
					</DIV>
				</DIV></CENTER>
			</DIV>
		</DIV></DIV>
		');
	};
	
	//si nouveau mot de passe
	if($action=='nouveaumdp'){
		
		$autreuser['cle'] = decrypter($_GET[crypter('nouveaumdp_cleuser')]);
		$sql_rep = SELECT('utilisateurs', array('cleUtilisateur_privee'=>$autreuser['cle']), array('pseudo', 'motdepasse_nouveau'));

		if(!empty($sql_rep)){
			$pseudo = $sql_rep[0]['pseudo'];
			$date_mdp = strtotime($sql_rep[0]['motdepasse_nouveau']);
			$date = time();

			if($date-$date_mdp < 2*3600){
				echo('
				<DIV class="C_zone I_centre U_zoneconnexion" id="zone_nouveaumdp"><DIV class="I_marges">			
					<DIV class="contenu premier visible" id="zone_nouveaumdp_0">					
						<CENTER><DIV class="C_zone_texte">
							<DIV class="C_txt_titre1">'.l('M:RECUPERATIONMDP').'</DIV>
							<DIV class="C_txt_description1 I_txt_centre">
								'.l('T_RECUPMDP:COMFIRMATION').'
							</DIV>
						</DIV></CENTER>
						<DIV class="C_zone_valide">
							<CENTER>
								<TABLE><TR>
								<TD>
									<FORM method="POST" action="/'.$langue.'/'.l('P_NOM:CONNEXION').'" id="form_nouveaumdp">
									<BUTTON type="submit" class="C_bouton bleu moyen I_marges_V">'.l('M:OUI').'</BUTTON>
									<INPUT type="hidden" name="'.crypter('nouveaumdp_cleuser').'" value="'.crypter($autreuser['cle']).'"/>
									</FORM>
								</TD>
								<TD>
									<FORM method="POST" action="/'.$langue.'/'.l('P_NOM:CONNEXION').'" id="form_annulermdp">
									<BUTTON type="submit" class="C_bouton blanc moyen I_marges_V">'.l('M:ANNULER').'</BUTTON>
									<INPUT type="hidden" name="'.crypter('annulermdp_cleuser').'" value="'.crypter($autreuser['cle']).'"/>
									</FORM>
								</TD>
								</TR></TABLE>
							</CENTER>
						</DIV>
					</DIV>
					
					<DIV class="contenu second" id="zone_nouveaumdp_1">
						<CENTER><DIV class="C_zone_texte">
							<DIV class="C_txt_titre1">'.l('M:VOTRENOUVEAUMDP').'</DIV>
							<DIV class="C_txt_description1 I_txt_centre">
								'.l('T_RECUPMDP:VOTRENOUVEAUMDP').'<BR/>
								<DIV class="C_txt_evidence1" id="U_nouveaumotdepasse"></DIV>
								'.l('T_RECUPMDP:POUVEZVOUSCONNECTER').'
							</DIV>
						</DIV></CENTER>
						<DIV class="C_zone_valide">
							<CENTER><A href="/'.$langue.'/'.l('P_NOM:CONNEXION').'" class="C_bouton bleu grand I_marges_V">'.l('M:SECONNECTER').'</A></CENTER>
						</DIV>
					</DIV>
				</DIV></DIV>
				');
			};
		};
	};
	
	echo('
		<SCRIPT type="text/javascript">$(document).ready(function(){');
		if($action=='connexion_corrige'){echo('$("#form_connexion").validate().form();');};
		if(isset($erreur_connexion) && $erreur_connexion=='mauvais'){echo('$("#form_connexion").validate().showErrors({"HE19M5OlCwjDnj5Z1xZBa9": "Nom d\'utilisateur ou mot de passe incorrect"});');};
	echo('});</SCRIPT>');
?>