<?php
	//GENERATEUR DE PAGE 'UTILISATEUR'
	
	//si page admin, user est moi
	if($pages[0]=='admin'){
		$autreuser = $user;
		$monprofil = true;
	}else{
		$monprofil = false;
		
		//sinon selectionne utilisateur
		if(isset($pages[1])){
			$autreuser = INFOS('user', ['pseudo'=>$pages[1]]);
		};
	};
	
	//si innexistant, regirige vers 404
	if(!isset($autreuser) || !$autreuser){
		return rediriger(url('ERREUR404'));
	};
	
	
	//===== ECRITURE DE LA PAGE =====
	echo('
	<DIV class="POS_titre U_profil_titre"><DIV class="I_marges">
		'.$autreuser['pseudo'].'
	</DIV></DIV>
	
	<DIV class="I_marges">
		<TABLE class="C_grandezone" style="height:500px;"><TBODY><TR>
		
			<TD class="U_profil_zonegauche contenu clair">
				<DIV class="U_profil_image"><IMG src="http://f.bookino.org/datas/users/'.crypter($autreuser['cle']).'/profil_150.png"/></DIV>
				<DIV class="C_grandezone_titre">'.l('M:APROPOS').'</DIV>
				<DIV class="C_grandezone_texte">
					'.l('M:GENRE').' : <B>Homme</B><BR/>
					'.l('M:AGE').' : <B>16 ans</B><BR/>
					'.l('M:PAYS').' : <B>France</B><BR/>
					'.l('M:INSCRIPTION').' : <B>16 juillet 2013</B><BR/>
					<BR/>
					'.l('M:PLACE').' : <B>789</B>ème<BR/>
					'.l('M:POINTS').' : <B></B><BR/>
					'.l('M:RANG').' : <B>Petit écrivain</B><BR/>
				</DIV>
				
				<DIV class="C_grandezone_titre">'.l('M:DESCRIPTION').'</DIV>
				<DIV class="C_grandezone_texte">
					'.$autreuser['description'].'
				</DIV>
				
				<DIV class="C_grandezone_titre">Citation favorite</DIV>
				<DIV class="C_grandezone_texte C_txt_citation1">
					Rien ne sert de courrir, il faut partir à point.
				</DIV>
				
				<DIV class="I_marges"></DIV>
			</TD>
			
			<TD class="U_profil_zonecentre contenu centre"><DIV class="I_marges">
	');
				
	//PUBLICATIONS
	//liste les publication sur ce mur
	$publications = $livres = LISTER('publication', ['idUtilisateur'=>$autreuser['id']]);
	foreach($publications as $publication){
		//recupere les infos sur le publicateur
		$id = $publication['idUtilisateur_publi'];
		$autreuser_publi = INFOS('user', ['id'=>$id]);
				
		switch($publication['type']){
			case 'message': $txt_action = l('A:MESSAGE');
							$txt_message = $publication['contenu'];
		}
		
		echo('
				<DIV class="C_bulle">
					<A class="image" href="/'.l('P_NOM:UTILISATEUR').'/'.$autreuser_publi['pseudo'].'">
						<IMG src="http://f.bookino.org/datas/users/'.crypter($autreuser_publi['cle']).'/profil_45.png"/>
					</A>
					<DIV class="contenu">
						<DIV class="titre">
							<A class="C_nomuser" href="/'.l('P_NOM:UTILISATEUR').'/'.$autreuser_publi['pseudo'].'">
								'.$autreuser_publi['pseudo'].'
							</A>
							'.$txt_action.'
						</DIV>
						<DIV class="texte">'.$txt_message.'</DIV>
					</DIV>
				</DIV>
		');
	}
	
	echo('
			</DIV></TD>
			
			<TD class="U_profil_zonedroite contenu clair">
				<DIV class="C_grandezone_titre">
					<DIV class="C_icon t32 x0 y64 gauche centre_v"></DIV>'.l('M:RESULTATS').'
				</DIV>
				<DIV class="C_grandezone_images">
					<CENTER>
	');
	

	//RESULTATS & MEDAILLES
	$liste_resultats = array(
		'EXTRAITS' => array('ABANNIR', 'BORDDUBAN', 'AVERTISSEMENT', 'BRAVO', 'TRIPLEBRAVO', 'JEVOUSAIME'),
		'POINTS' => array('POUBELLE', 'ORDURE', 'MAUVAIS', 'BONDEBUT', 'FELICITATION', 'PALME'),
	);	
	$liste_medailles = array('FELICITATION');
	$resultats_eu = array();
	$medailles_eu = array();
	
	//recupere tout les resultats
	$medailles = LISTER('resultat', ['idUtilisateur'=>$autreuser['id']]);
	
	foreach($medailles as $medaille){
		$groupe = $medaille['groupe'];
		$type = $medaille['type'];
		
		//si resultat
		if(isset($liste_resultats[$groupe]) && in_array($type, $liste_resultats[$groupe])){
			if(!isset($resultats_eu[$groupe])){
				$resultats_eu[$groupe] = array();
			};
			
			//si resultat deja present, incrémente
			//sinon, nb résultat à 1
			if(!isset($resultats_eu[$groupe][$type])){
				$place = array_search($type, $liste_resultats[$groupe]);
				$resultats_eu[$groupe][$type] = array(1, $place);
			}else{
				$resultats_eu[$groupe][$type][0]++;
			};
		};
		
		//si médaille déjà présente, incrémente
		//sinon nb médaille à 1
		if($groupe=='MEDAILLE' && in_array($type, $liste_medailles)){
			if(!isset($medailles_eu[$type])){
				$medailles_eu[$type] = 1;
			}else{
				$medailles_eu[$type]++;
			};
		};
	}
	
	//ecri les medailles
	foreach($medailles_eu as $type=>$valeur){
		$html_nombre = $valeur;
		echo('				<DIV class="C_medaille" info="'.l('R_MTITRE:'.$type).';'.l('R_MTEXTE:'.$type).';noir;haut;nonfixe">
								<DIV class="contenu">'.$html_nombre.'</DIV>
							</DIV>
			');
	}
	
	//parcour les resultats et ecri en formant les groupes
	foreach($liste_resultats as $groupe=>$valeur){
		echo('				<DIV class="C_resultat_ensemble">');
		foreach($valeur as $type){
			if(isset($resultats_eu[$groupe]) && isset($resultats_eu[$groupe][$type])){
				$html_nombre = $resultats_eu[$groupe][$type][0];
				$place = $resultats_eu[$groupe][$type][1];
				if($place < 3){
					$html_active = 'rouge';
				}else{
					$html_active = 'vert';
				};
			}else{
				$html_nombre = '0';
				$html_active = 'disabled';
			};
			echo('				<DIV class="C_resultat '.$html_active.'" info="'.l('R_NOM:'.$groupe.'_'.$type).';'.l('R_TEXTE:'.$groupe.'_'.$type).';noir;haut;nonfixe">
									<DIV class="texte">x'.$html_nombre.'</DIV>
								</DIV>
			');
		}
		echo('					<DIV class="C_aide" info="'.l('R_INFO:'.$groupe).';noir;gauche;nonfixe">?</DIV>
							</DIV><BR/>');
	}
	
	echo('
					</CENTER>
				</DIV>
	');

	
	//PARTICIPATIONS
	$livres = array();
	$nb_participations = 0;
	
	//compte les paragraphes
	$paragraphes = LISTER('paragraphe', ['idUtilisateur'=>$autreuser['id']]);
	foreach($paragraphes as $paragraphe){
		$id = $paragraphe['idLivre'];
		$livre = INFOS('livre', ['id'=>$id]);
		
		$nb_participations++;
		if(isset($livres[$id])){
			$livres[$id][0]++;
		}else{
			$livres[$id] = array(1, 0);
		};
	}
	
	//compte les idées
	$idees = LISTER('idee', ['idUtilisateur'=>$autreuser['id']]);
	foreach($idees as $idee){
		$id = $idee['idIdee'];
		
		$nb_participations++;
		if(isset($livres[$id])){
			$livres[$id][1]++;
		}else{
			$livres[$id] = array(0, 1);
		};
	}
	
	//ecrit le titre
	echo('		<DIV class="C_grandezone_titre">
					<DIV class="C_icon t32 x32 y32 gauche centre_v"></DIV>
					'.l('M:PARTICIPATIONS').' ('.$nb_participations.')
				</DIV>
				<DIV class="C_grandezone_images">
					<CENTER>
	');
	
	//ecrit les livres
	foreach($livres as $id=>$valeur){
		//recupere infos sur le livre
		$livre = INFOS('livre', ['id'=>$id]);
		
		$titre = $livre['titre'];
	
		echo('			<A href="/'.$langue.'/'.l('P_NOM:LIVRE').'/'.$livre['titre_simple'].'">
							<DIV class="C_livre petit I_marges_H" info="'.$titre.';noir;haut;nonfixe">
								<DIV class="contenu">
									<DIV class="extraits"><DIV class="icon"></DIV>'.$valeur[0].'</DIV>
									<DIV class="idees">'.$valeur[1].'<DIV class="icon"></DIV></DIV>
								</DIV>
							</DIV>
						</A>
		');
	}
	
	echo('			</CENTER>
				</DIV>
	');
	
	//LIVRES CREES
	
	//parcour les livres
	$livres = LISTER('livre', ['idUtilisateur'=>$autreuser['id']]);
	
	//ecrit le titre
	echo('		<DIV class="C_grandezone_titre">
					<DIV class="C_icon t32 x64 y32 gauche centre_v"></DIV>
					'.l('M:LIVRES').' ('.count($livres).')
				</DIV>
				<DIV class="C_grandezone_images">
					<CENTER>
	');
	//ecrit les livres
	foreach($livres as $id=>$livre){
		$titre = $livre['titre'];
		
		echo('			<A href="/'.$langue.'/'.l('P_NOM:LIVRE').'/'.$livre['titre_simple'].'">
							<DIV class="C_livre petit I_marges_H">
								<DIV class="contenu">
									<DIV class="titre">'.$titre.'</DIV>
								</DIV>
							</DIV>
						</A>
		');
	}
	
	echo('			</CENTER>
				</DIV>
	');
	
	
	//AMIS
	$connaissances = lister_connaissances($autreuser['id']);
	
	//ecrit le titre
	echo('		<DIV class="C_grandezone_titre">
					<DIV class="C_icon t32 x0 y32 gauche centre_v"></DIV>
					'.l('M:AMIS').' ('.count($connaissances['amis']).')
				</DIV>
				<DIV class="C_grandezone_images"><CENTER>
	');
	//ecrit les personnes suivies
	foreach($connaissances['amis'] as $id){
		$autreuser = INFOS('user', ['id'=>$id]);
		echo('		<A href="/'.$langue.'/'.l('P_NOM:UTILISATEUR').'/'.$autreuser['pseudo'].'" class="C_user">
						<IMG class="image" 
							 src="http://f.bookino.org/datas/users/'.crypter($autreuser['cle']).'/profil_45.png" 
							 info="'.$autreuser['pseudo'].';noir;haut;nonfixe"
						/>
					</A>
		');
	}
	echo('		</CENTER></DIV>');
	
	
	//PERSONNES SUIVIES	
	//ecrit le titre
	echo('		<DIV class="C_grandezone_titre">
					<DIV class="C_icon t32 x0 y32 gauche centre_v"></DIV>
					'.l('M:PERSONNESSUIVIES').' ('.count($connaissances['suivis']).')
				</DIV>
				<DIV class="C_grandezone_images"><CENTER>
	');
	//ecrit les personnes suivies
	foreach($connaissances['suivis'] as $id){
		$autreuser = INFOS('user', ['id'=>$id]);
		echo('		<A href="/'.$langue.'/'.l('P_NOM:UTILISATEUR').'/'.$autreuser['pseudo'].'" class="C_user">
						<IMG class="image" 
							 src="http://f.bookino.org/datas/users/'.crypter($autreuser['cle']).'/profil_45.png" 
							 info="'.$autreuser['pseudo'].';noir;haut;nonfixe"
						/>
					</A>
		');
	}
	echo('		</CENTER></DIV>');
	
	
	//LECTEURS
	//ecrit le titre
	echo('		<DIV class="C_grandezone_titre">
					<DIV class="C_icon t32 x0 y32 gauche centre_v"></DIV>
					'.l('M:LECTEURS').' ('.count($connaissances['lecteurs']).')
				</DIV>
				<DIV class="C_grandezone_images"><CENTER>
	');
	//ecrit les lecteurs
	foreach($connaissances['lecteurs'] as $id){
		$autreuser = INFOS('user', ['id'=>$id]);
		echo('		<A href="/'.$langue.'/'.l('P_NOM:UTILISATEUR').'/'.$autreuser['pseudo'].'" class="C_user">
						<IMG class="image" 
							 src="http://f.bookino.org/datas/users/'.crypter($autreuser['cle']).'/profil_45.png" 
							 info="'.$autreuser['pseudo'].';noir;haut;nonfixe"
						/>
					</A>
		');
	}
	echo('		</CENTER></DIV>
			</TD>
			
		</TR></TBODY></TABLE>
	</DIV>	
	');
?>