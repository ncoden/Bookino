<?php
	//GENERATEUR DE PAGE 'ACTUALITES'
		
	
	//===== ECRITURE DE LA PAGE =====
	
	echo('
	<DIV class="POS_body">
	<DIV class="POS_titre"><DIV class="I_marges">
		'.l('P_TITRE:ACTUALITES').'
	</DIV></DIV>
	
	<DIV class="I_marges">
		<TABLE class="C_grandezone" style="height:500px;"><TBODY><TR>
		
			<TD class="U_actualites_zonegauche contenu clair">
	');
	
	//PARTICIPATIONS
	$livres = array();
	$nb_participations = 0;
	
	//compte les paragraphes
	$paragraphes = LISTER('paragraphe', ['idUtilisateur'=>$user['id']]);
	foreach($paragraphes as $paragraphe){
		$id = $paragraphe['idLivre'];
		
		$nb_participations++;
		if(isset($livres[$id])){
			$livres[$id][0]++;
		}else{
			$livres[$id] = array(1, 0);
		};
	}
	//compte les idées
	$idees = LISTER('idee', ['idUtilisateur'=>$user['id']]);
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
		$livre = INFOS('livre', ['id'=>$id]);
		$nom = crypter(strval($id)).'-'.str_replace(' ', '-', $livre['titre']);
		
		echo('			<A href="/'.$langue.'/'.l('P_NOM:LIVRE').'/'.$livre['titre_simple'].'">
							<DIV class="C_livre petit I_marges_H" info="'.$livre['titre'].';noir;haut;nonfixe">
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
	$livres = array();
	
	//parcour les livres
	$livres = LISTER('livre', ['idUtilisateur'=>$user['id']]);
	
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
		$livre = INFOS('livre', ['id'=>$id]);
		$nom = crypter(strval($id)).'-'.str_replace(' ', '-', $livre['titre']);
		
		echo('			<A href="/'.$langue.'/'.l('P_NOM:LIVRE').'/'.$nom.'">
							<DIV class="C_livre petit I_marges_H">
								<DIV class="contenu">
									<DIV class="titre">'.$livre['titre'].'</DIV>
								</DIV>
							</DIV>
						</A>
		');
	}
	echo('			</CENTER>
				</DIV>
	');
	
	
	//AMIS
	$connaissances = lister_connaissances($user['id']);
	
	//ecrit le titre
	echo('		<DIV class="C_grandezone_titre">
					<DIV class="C_icon t32 x0 y32 gauche centre_v"></DIV>
					'.l('M:AMIS').' ('.count($connaissances['amis']).')</DIV>
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
			
			<TD class="U_actualites_zonecentre contenu centre"><DIV class="I_marges">
	');
	
				
	//PUBLICATIONS
	$publications = LISTER('publication', ['idUtilisateur'=>$user['id']]);
	foreach($publications as $publication){
		$id = $publication['idUtilisateur_publi'];
		$autreuser = INFOS('user', ['id'=>$id]);
		
		switch($publication['type']){
			case 'message': $txt_action = l('A:MESSAGE');
							$txt_message = $publication['contenu'];
		}
		
		echo('
				<DIV class="C_bulle">
					<A class="image" href="/'.l('P_NOM:UTILISATEUR').'/'.$autreuser['pseudo'].'">
						<IMG src="http://f.bookino.org/datas/users/'.crypter($autreuser['cle']).'/profil_45.png"/>
					</A>
					<DIV class="contenu">
						<DIV class="titre">
							<DIV class="C_nomuser" pour="apercu_user" cle="'.$autreuser['cle'].'">'.$autreuser['pseudo'].'</DIV>
							'.$txt_action.'
						</DIV>
						<DIV class="texte">'.$txt_message.'</DIV>
					</DIV>
				</DIV>
		');
	}
	
	echo('	</DIV></TD>
			
			<TD class="U_actualites_zonedroite contenu clair">
				<DIV class="C_grandezone_titre">'.l('M:PUBLICITE').'</DIV>
				<DIV class="C_grandezone_titre">'.l('M:BLOGDESDEVELLOPEURS').'</DIV>
			</TD>
			
		</TR></TBODY></TABLE>
	</DIV>	
	</DIV>
	');
?>