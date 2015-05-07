<?php
	//GENERATEUR DE PAGE 'LIVRE'

	//recupere le livre
	if(isset($pages[1])){
		$nom = $pages[1];
	};
	
	//verifi que le livre existe
	$livre = INFOS('livre', ['titresimple'=>$nom]);
	if(!$livre || !isset($pages[1])){
		return rediriger(url('LIVRES'));
	};
	
	inclure_js('var livre = {};
				livre["cle"] = "'.crypter($livre['cle']).'";');
	
	//ecrit le titre
	echo('	
	<DIV class="POS_titre U_livre_titre"><DIV class="I_marges">
		'.$livre['titre'].'
	</DIV></DIV>
	
	<DIV class="I_marges">
		<TABLE class="C_grandezone tresclair"><TBODY><TR>
	');
	
	//INFORMATIONS
	echo('
			<TD class="U_livre_zonegauche contenu migauche clair">
				<DIV class="U_livre_image"><IMG src="http://f.bookino.org/datas/livres/'.crypter($livre['titre']).'/couverture_150.png"/></DIV>
	');
	
	//recupere infos
	$auteur = INFOS('user', ['id'=>$livre['idUtilisateur']]);
	$date = new DateTime($livre['date_cree']);
	$date = $date->format('d/m/Y');
	$etat = $livre['etat'];
	
	//$paragraphes = LISTER('paragraphe', ['idLivre'=>$livre['id']]);
	echo('		<DIV class="C_grandezone_titre">'.l('M:APROPOS').'</DIV>
				<DIV class="C_grandezone_texte">
					'.l('M:CREEPAR').' <A class="C_nomuser" href="/'.$langue.'/'.l('P_NOM:UTILISATEUR').'/'.$auteur['pseudo'].'">'.$auteur['pseudo'].'</A><BR/>
					'.l('M:DATE').' : <B>'.$date.'</B><BR/>
					'.l('M:ETAT').' : <B>'.$etat.'</B>
				</DIV>
			</TD>
			
			<TD class="U_livre_zonecentre contenu">
				<DIV class="I_marges_10">
	');
	
	//DESCRIPTION
	
	echo('			<DIV class="C_txt_titre1">'.l('M:PRESENTATION').'</DIV>
					<DIV class="C_txt_description1">'.$livre['description'].'</DIV>
					<BR/>
					<DIV class="C_txt_titre1">'.l('M:SCENARIO').'</DIV>
				</DIV>
			</TD>
			
			<TD class="U_livre_zonedroite contenu midroite clair">
	');
	
	//PARTICIPANTS
	echo('		<DIV class="C_grandezone_titre">'.l('M:PARTICIPANTS').'</DIV>
				<DIV class="I_marges_10"><CENTER>
					<A href="/'.$langue.'/" class="C_bouton blanc trespetit">Participer</A>
				</CENTER></DIV>
				
			</TD>
		</TR></TBODY></TABLE>
	');
	
	//PARAGRAPHES
	
	$html = script_paragraphes_ecrire(['livre_cle' => crypter($livre['cle'])]);
	echo($html);
	
	//AJOUTER PARAGRAPHE		
	$date = new DateTime();
	$date = $date->format('d/m/Y');
		
	echo('
		<DIV class="I_marges_V20" id="paragraphe_ecrit">
			<TABLE class="C_grandezone tresclair"><TBODY><TR>
				<TD class="U_livre_zonegauche contenu migauche clair">
					<DIV class="C_grandezone_texte I_marges_V">
						'.l('M:AUTEUR').' : ');
	if($user['connecte']){
		echo('			<A class="C_nomuser" href="/'.$langue.'/'.l('P_NOM:UTILISATEUR').'/'.$user['pseudo'].'">'.$user['pseudo'].'</A><BR/>');
	}else{
		echo('			'.l('M:NONCONNECTE').'<BR/>');
	};
	
	$html_paragraphe = script_paragraphe_actualiser(['livre_cle' => crypter($livre['cle'])]);
	inclure_js('var paragraphe_evenement = "'.$html_paragraphe['evenement'].'";');
	
	echo('				'.l('M:DATE').' : <B>'.$date.'</B><BR/>
					</DIV>
				</TD>
			
				<TD class="U_livre_zonecentre contenu">
					<DIV class="C_grandezone_texte I_marges_V">
						<CENTER id="U_livre_zoneajouter">'.$html_paragraphe['html'].'</CENTER>
					</DIV>
				</TD>
				
				<TD class="U_livre_zonedroite contenu midroite clair">
					<DIV class="C_grandezone_texte I_marges_V">
						<DIV class="C_txt_titre2">Attention</DIV>
						<DIV class="C_txt_description2">
							<B/>Les paragraphes écrits doivent être de qualité.</B><BR/>
							<BR/>
							Tout spam, publicité ou language SMS est immédiatement sanctionné.<BR/>
							<BR/>
							Plusieurs fautes au reglement répétées conduiront à un ban de votre compte
							TrollBlock.
						</DIV>
					</DIV>
				</TD>
			</TR></TBODY></TABLE>
		</DIV>');
	
	echo('
	</DIV>');
?>