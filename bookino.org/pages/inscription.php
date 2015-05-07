<?php 
	//GENERATEUR DE PAGE 'INSCRIPTION'
		
	if(isset($_GET[crypter('action')])){$action = decrypter($_GET[crypter('action')]);}else{$action='inscription';};
	if(isset($_POST[crypter('form_id')])){$form_id = decrypter($_POST[crypter('form_id')]);}else{$form_id='';};
	
	//===== FORMULAIRES =====
	//si inscription
	if($form_id=='inscription'){
		$pseudo = trim($_POST[crypter('register_pseudo')]);
		$email = trim($_POST[crypter('register_email')]);
		$motdepasse1 = trim($_POST[crypter('register_pass1')]);
		$motdepasse2 = trim($_POST[crypter('register_pass2')]);
		$securite = intval(trim($_POST[crypter('register_securite')]));
		$securite1 = intval(trim($_POST[crypter('register_securite1')]));
		$securite2 = intval(trim($_POST[crypter('register_securite2')]));
		
		$erreur_pseudo=null; 
		$erreur_email=null; 
		$erreur_motdepasse1=null; 
		$erreur_motdepasse2=null; 
		$erreur_securite=null;
			
		//test du pseudo
		if($pseudo==''){$erreur_pseudo='vide';}else{
		if((strlen($pseudo)<4) || (strlen($pseudo)>30)){$erreur_pseudo='taille';}else{
		if(!preg_match('/^[A-Za-z0-9\d]+$/i', $pseudo)){$erreur_pseudo='regex';}else{
		
		$sql_rep = SELECT('utilisateurs', array('pseudo'=>$pseudo));
		if(!empty($sql_rep)){$erreur_pseudo='existe';};
		};};};
		
		//test de l'email
		if($email==''){$erreur_email='vide';}else{
		if(strlen($email)>256){$erreur_email='taille';}else{
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){$erreur_email='regex';}else{

		$sql_rep = SELECT('utilisateurs', array('email'=>$email));
		if(!empty($sql_rep)){$erreur_email='existe';};
		};};};
		
		//test du mot de passe 1
		if($motdepasse1==''){$erreur_motdepasse1='vide';}else{
		if((strlen($motdepasse1)<6) || (strlen($motdepasse1)>30)){$erreur_motdepasse1='taille';}else{
		if(!preg_match('/^[A-Za-z0-9\d]+$/i', $motdepasse1)){$erreur_motdepasse1='regex';};
		};};
		
		//test du mot de passe 2
		if($motdepasse2==''){$erreur_motdepasse2='vide';}else{
		if($motdepasse2 != $motdepasse1){$erreur_motdepasse2='different';};
		};
		
		//test de la securite
		if($securite==''){$erreur_securite='vide';}else{
		if($securite!=($securite1+$securite2)){$erreur_securite='different';};
		};

		//SI PAS D'ERREURS
		if(($erreur_pseudo==null) && ($erreur_email==null) && ($erreur_motdepasse1==null) && ($erreur_motdepasse2==null) && ($erreur_securite==null)){
			
			//calcul des clés
			$autreuser_clelibre = false;
			$autreuser_cleprivee_libre = false;
			$datetime = (date('Y-m-d H:i:s'));
			
			//clé publique : l'utilisateur l'utilise pour se reconnaitre dans la communauté
			while(!$autreuser_clelibre){
				$autreuser_cle = 'USE'.md5('g!E4-8.B'.$datetime.'OA/jèçN*q'.$pseudo.'x9('.rand(0,100)).md5('r,h;O'.$email.'h"iàN6{Q'.rand(0,100));
				$sqlc_rep = SELECT('utilisateurs', array('cleUtilisateur'=>$autreuser_cle));
				$autreuser_clelibre = empty($sql_rep);
			}
			
			//clé privé : clé utilisé quand non connecté, non récupérable par les autres utilisateurs
			while(!$autreuser_cleprivee_libre){
				$autreuser_cleprivee = 'PRI'.md5('#\dV5'.$datetime.'6*-g(T'.$pseudo.'nè7O:'.rand(0,100)).md5('+wAé"3;'.$email.',V2xà}'.rand(0,100));
				$sql_rep = SELECT('utilisateurs', array('cleUtilisateur_privee'=>$autreuser_cleprivee));
				$autreuser_cleprivee_libre = empty($sql_rep);
			}
			
			//crypte le mot de passe
			$motdepasse = md5($motdepasse1);
			
			//ajoute utilisateur
			INSERT('utilisateurs', 
					array('cleUtilisateur'=>$autreuser_cle,
						  'cleUtilisateur_privee'=>$autreuser_cleprivee,
						  'pseudo'=>$pseudo,
						  'email'=>$email,
						  'motdepasse'=>$motdepasse,
						  'date_inscription'=>$datetime,
						  'points'=>'0',
						  'enligne'=>'0',
						  'etat'=>'valide_email'
						  ));

			$action = 'verifier_email';
		}else{
			$action='inscription_corrige';
		};
		
	};
	
	
	
	//===== ECRITURE DE LA PAGE =====
	echo('
	<DIV class="POS_body">
	<DIV class="POS_titre"><DIV class="I_marges">
		'.l('M:INSCRIPTION').'
	</DIV></DIV>
	<BR/>
	<BR/>
	');
	
	//si inscription ou correction d'inscription
	if($action=='inscription' || $action=='inscription_corrige'){
		
		if(isset($pseudo)){$champ_pseudo=$pseudo;}else{$champ_pseudo=l('M:PSEUDO');}; 
		if(isset($email)){$champ_email=$email;}else{$champ_email=l('M:ADDREMAIL');}; 
		if(isset($motdepasse1)){$champ_motdepasse1=$motdepasse1;}else{$champ_motdepasse1=l('M:MOTDEPASSE');}; 
		if(isset($motdepasse2)){$champ_motdepasse2=$motdepasse2;}else{$champ_motdepasse2=l('M:COMFIRMATION');}; 
		
		$securite1 = mt_rand(1,9);
		$securite2 = mt_rand(1,9);
		$securite = $securite1 + $securite2;
		
		echo('
		<DIV class="C_zone I_centre U_zoneinscription" id="zone_inscription"><DIV class="I_marges">
			<DIV class="contenu premier visible" id="zone_inscription_0">');
				echo(html_formulaire('inscription'));
				echo('
				<FORM method="POST" action="/'.$langue.'/'.l('P_NOM:INSCRIPTION').'" id="form_register">
				<CENTER><DIV class="C_zone_texte">
					<DIV class="C_txt_titre1">'.l('M:INSCRIPTION').'</DIV>
					<DIV class="C_zone_texte">
						<INPUT type="text" name="'.crypter('register_pseudo').'" class="C_champ plein I_marges_V requiere accept_icon" value="'.$champ_pseudo.'" defaut="'.l('M:PSEUDO').'"/><BR/>
						<INPUT type="text" name="'.crypter('register_email').'" class="C_champ plein I_marges_V requiere accept_icon" value="'.$champ_email.'" defaut="'.l('M:ADDREMAIL').'"/><BR/>
						<INPUT name="'.crypter('register_pass1').'" id="'.crypter('register_pass1').'" class="C_champ moyen I_marges_V requiere accept_icon" value="'.$champ_motdepasse1.'" defaut="'.l('M:MOTDEPASSE').'" motdepasse="true"/>
						<INPUT name="'.crypter('register_pass2').'" class="C_champ moyen I_marges_V requiere accept_icon" value="'.$champ_motdepasse2.'" defaut="'.l('M:COMFIRMATION').'" motdepasse="true"/><BR/>
					</DIV>
				</DIV></CENTER>
				<DIV class="C_zone_valide">
					<CENTER><BUTTON type="submit" class="C_bouton bleu grand I_marges_V">'.l('M:VALIDER').'</BUTTON></CENTER>
					<DIV class="U_inscription_securite">
					<DIV class="C_securite">
						<DIV class="cadena"></DIV><DIV class="operation">'.$securite1.'+'.$securite2.' =
						</DIV><INPUT type="text" name="'.crypter('register_captcha').'" class="C_champ securite accept_border"/>
						<INPUT type="hidden" name="'.crypter('register_securite').'" id="'.crypter('register_securite').'" value="'.$securite.'"/>
						<INPUT type="hidden" name="'.crypter('register_securite1').'" value="'.$securite1.'"/>
						<INPUT type="hidden" name="'.crypter('register_securite2').'" value="'.$securite2.'"/>
						<INPUT type="hidden" name="'.crypter('form_id').'" value="'.crypter('inscription').'"/>
					</DIV>
					</DIV>
				</DIV>
				</FORM>
			</DIV>
		</DIV></DIV>
		');
	};
	
	//si demande de validation mail
	if($action=='verifier_email'){
	
		//verification de la cle et recupération pseudo/mail
		$sql_rep = SELECT('utilisateurs', array('cleUtilisateur'=>$autreuser_cle,'etat'=>'valide_email'), array('pseudo','email'));
		$_user_existe = !empty($sql_rep);
		
		//si l'utilisateur est bon, affiche infos et formulaire
		if($_user_existe){
			$pseudo = $sql_rep[0]['pseudo'];
			$email = $sql_rep[0]['email'];
			
			echo('
			<DIV class="C_zone I_centre U_zoneinscription" id="zone_changeremail"><DIV class="I_marges">
				<CENTER>
				<DIV class="contenu premier visible" id="zone_changeremail_0">
					<DIV class="C_zone_texte">
						<DIV class="C_txt_titre1">'.l('M:BONJOUR').' '.$pseudo.'</DIV>
						<DIV class="C_txt_description1">
							'.l('T_VALIDEREMAIL:MESSAGEENVOYE').'
							<DIV class="C_txt_evidence1" id="changeremail_affichemail">'.$email.'.</DIV>
							'.l('T_VALIDEREMAIL:VEUILLEZCLIQUER').'
						</DIV>
					</DIV>
					<DIV class="C_zone_valide">
						<TABLE><TR>
						<TD><BUTTON type="button" pour="zone_montrer" zone="changeremail" niveau="1" class="C_bouton blanc moyen I_marges_H I_marges_V">'.l('M:MODIF_ADDREMAIL').'</BUTTON></TD>
						<TD>
							<FORM action="/'.$langue.'/'.l('P_NOM:INSCRIPTION').'" method="POST" id="form_renvoyeremail">
							<BUTTON type="submit" class="C_bouton blanc moyen I_marges_H I_marges_V">'.l('M:RENVOYEREMAIL').'</BUTTON>
							<INPUT type="hidden" name="'.crypter('renvoyeremail_cleuser').'" value="'.crypter($autreuser_cle).'"/>
							</FORM>
						</TD>
						</TR></TABLE>
					</DIV>
				</DIV>
				<DIV class="contenu second" id="zone_changeremail_1">
					<FORM method="POST" action="/'.$langue.'/'.l('P_NOM:INSCRIPTION').'" id="form_changeremail">
					<DIV class="C_zone_texte">
						<DIV class="C_txt_titre1">'.l('M:MODIF_ADDREMAIL').'</DIV>
						<DIV class="C_txt_description1"><BR/>
							<INPUT type="text" name="'.crypter('changeremail_nemail').'" class="C_champ moyen I_marges_V requiere accept_icon" value="'.$email.'"/>
							<INPUT type="hidden" name="'.crypter('changeremail_cleuser').'" value="'.crypter($autreuser_cle).'"/>
							<BR/><BR/>
						</DIV>
					</DIV>
					<DIV class="C_zone_valide" id="zone_changeremail_valide_1">
						<TABLE><TR>
						<TD><BUTTON type="submit" class="C_bouton bleu moyen I_marges_H I_marges_V">'.l('M:VALIDER').'</BUTTON></TD>
						<TD><BUTTON type="button" pour="zone_montrer" zone="changeremail" niveau="0" class="C_bouton blanc moyen I_marges_H I_marges_V">'.l('M:ANNULER').'</BUTTON></TD>
						</TR></TABLE>
					</DIV>
					</FORM>
				</DIV>
				</CENTER>
			</DIV></DIV>
			');
		}else{
			echo('
			<DIV class="C_zone I_centre U_zoneinscription" id="zone_inscriptionfini"><DIV class="I_marges">
				<CENTER>
				<DIV class="contenu premier visible" id="zone_inscriptionerreur_0">
					<DIV class="C_zone_texte">
						<DIV class="C_txt_titre1">'.l('M:VOUSCHERCHEZ?').'</DIV>
						<DIV class="C_txt_description1">
							'.l('T_VALIDEREMAIL:ERREUR').'
						</DIV>
					</DIV>
					<DIV class="C_zone_valide"><TABLE><TR>
					<TD><A href="Login.php" class="C_bouton bleu moyen I_marges_V">'.l('M:SECONNECTER').'</A></TD>
					<TD><A href="Register.php" class="C_bouton bleu moyen I_marges_V">'.l('M:SINSCRIRE').'</A></TD>
					</TR></TABLE></DIV>
				</DIV>
				</CENTER>
			</DIV></DIV>
			');
		};
	};
	
	//si  validation mail
	if($action=='valider_email'){
		$autreuser_cle=decrypter($_GET[crypter('valideremail_cleuser')]);
		
		//verification de la cle et recupération pseudo
		$sql_rep = SELECT('utilisateurs', array('cleUtilisateur'=>$autreuser_cle,'etat'=>'valide_email'), array('pseudo'));
		$_user_existe = !empty($sql_rep);
		
		//si l'utilisateur est bon, ouvre le compte et informe
		if($_user_existe){
			$pseudo = $sql_rep[0]['pseudo'];
			$req = $db->prepare('UPDATE utilisateurs SET etat=?');
			if(!$req->execute(array('nouveau'))){echo('erreur_bdd');exit();};
			
			echo('
			<DIV class="C_zone I_centre U_zoneinscription" id="zone_inscriptionfini"><DIV class="I_marges">
				<CENTER>
				<DIV class="contenu premier visible" id="zone_inscriptionfini_0">
					<DIV class="C_zone_texte">
						<DIV class="C_txt_titre1">'.l('M:BIENVENUE').' '.$pseudo.'</DIV>
						<DIV class="C_txt_description1">
							'.l('T_INSCRIPTION:BIENVENUE').'
						</DIV>
					</DIV>	
					<DIV class="C_zone_valide">
						<A href="/'.$langue.'/'.l('P_NOM:CONNEXION').'" class="C_bouton bleu grand I_marges_V">'.l('M:SECONNECTER').'</A>
					</DIV>
				</DIV>
				</CENTER>
			</DIV></DIV>
			');
		}else{
			echo('
			<DIV class="C_zone I_centre U_zoneinscription" id="zone_inscriptionfini"><DIV class="I_marges">
				<CENTER>
				<DIV class="contenu premier visible" id="zone_inscriptionerreur_0">
					<DIV class="C_zone_texte">
						<DIV class="C_txt_titre1">'.l('M:VOUSCHERCHEZ?').'</DIV>
						<DIV class="C_txt_description1">
							'.l('T_VALIDEREMAIL:ERREUR').'
						</DIV>
					</DIV>
					<DIV class="C_zone_valide"><TABLE><TR>
					<TD><A href="/'.$langue.'/'.l('P_NOM:CONNEXION').'" class="C_bouton bleu moyen I_marges_V">'.l('M:SECONNECTER').'</A></TD>
					<TD><A href="/'.$langue.'/'.l('P_NOM:INSCRIPTION').'" class="C_bouton bleu moyen I_marges_V">'.l('M:SINSCRIRE').'</A></TD>
					</TR></TABLE></DIV>
				</DIV>
				</CENTER>
			</DIV></DIV>
			');
		};
	};
	
	echo('</DIV>
		<SCRIPT type="text/javascript">$(document).ready(function(){');
		if($action=='inscription_corrige'){echo('$(document).ready(function(){$("#form_register").validate().form();');};
		if($action=='verifier_email'){echo('$(\'#form_renvoyeremail\').submit();');};
	echo('});</SCRIPT>');
	
?>