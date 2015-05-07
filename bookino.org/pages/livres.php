<?php
	//GENERATEUR DE PAGE 'LIVRES'
	
	//recupere les filtres
	$filtres = array();
	$categories = array(
		'L_ARTS',
		'L_ESSAIS',
		'L_JEUNESSE',
		'L_POLICIER',
		'L_livreCE',
		'L_SIENCEFICTION',
		'L_THEATRE',
	);
	
	$menu = '';
	$menu_tous = '';
	$menu_moi = '';
	$menu_finis = '';
	$menu_encours = '';
	$menu_propositions = '';
	
	switch($page['nom']){
		case 'livres_moi': 			$filtres['idUtilisateur']=$user['id']; $menu_moi=' menu'; break;
		case 'livres_finis': 		$filtres['etat']='fini'; $menu_finis=' menu'; break;
		case 'livres_encours': 		$filtres['etat']='encours'; $menu_encours=' menu'; break;
		case 'livres_propositions': $filtres['etat']='proposition'; $menu_propositions=' menu'; break;
		default: 					$menu_tous = ' menu'; break;
	}
	
	//ecrit la page	
	echo('	
	<DIV class="POS_titre"><DIV class="I_marges">
		'.l('P_TITRE:LIVRES').'
	</DIV></DIV>
	
	<DIV class="I_marges">
		<TABLE class="C_grandezone clair"><TBODY><TR>
			<TD class="U_actualites_zonegauche contenu clair">			
				<DIV class="C_grandezone_titre">'.l('M:LIVRES').'</DIV>
				<A href="/'.$langue.'/'.l('P_NOM:LIVRES').'"><DIV class="C_grandezone_liste'.$menu_tous.'">'.l('M:TOUSLESLIVRES').'</DIV></A>
				<A href="/'.$langue.'/'.l('P_NOM:LIVRES').'/'.l('P_NOM:LIVRES_MOI').'"><DIV class="C_grandezone_liste'.$menu_moi.'">'.l('M:MESLIVRES').'</DIV></A>
				<A href="/'.$langue.'/'.l('P_NOM:LIVRES').'/'.l('P_NOM:LIVRES_FINIS').'"><DIV class="C_grandezone_liste'.$menu_finis.'">'.l('M:LIVRESTERMINES').'</DIV></A>
				<A href="/'.$langue.'/'.l('P_NOM:LIVRES').'/'.l('P_NOM:LIVRES_ENCOURS').'"><DIV class="C_grandezone_liste'.$menu_encours.'">'.l('M:LIVRESENCOURS').'</DIV></A>
				<A href="/'.$langue.'/'.l('P_NOM:LIVRES').'/'.l('P_NOM:LIVRES_PROPOSITIONS').'"><DIV class="C_grandezone_liste'.$menu_propositions.'">'.l('M:PROPOSITIONS').'</DIV></A>
				<CENTER>
					<DIV class="C_grandezone_images I_marges_V">
						<A href="/'.$langue.'/'.l('P_NOM:CREERLIVRE').'" class="C_bouton blanc trespetit">'.l('M:CREERUNLIVRE').'</A>
					</DIV>
				</CENTER>
				
				<DIV class="C_grandezone_titre">'.l('M:CATEGORIES').'</DIV>
	');
	
	//catégories
	foreach($categories as $nom){
		if('livres_'.$nom == $page['nom']){
			$menu = ' menu';
		}else{
			$menu = '';
		};
		
		echo('	<A href="/'.$langue.'/'.l('P_NOM:LIVRES').'/'.l('M:'.$nom).'">
					<DIV class="C_grandezone_liste'.$menu.'">'.l('M:'.$nom).'</DIV>
				</A>
		');
	}
	
	echo('
			</TD>
			
			<TD class="contenu midroite">
				<DIV class="I_marges_10"><CENTER>
	');
	
	//livres
	$livres = LISTER('livre', $filtres);
	
	if(!empty($livres)){
		foreach($livres as $id=>$livre){
			$titre = $livre['titre'];
			$description = $livre['description'];
			
			if(strlen($description)>170){
				$description = substr($description, 0, 170).'...';
			};
			$description = str_replace('\n', '<BR/>', $description);
			
			echo('	<A href="/'.$langue.'/'.l('P_NOM:LIVRE').'/'.$livre['titre_simple'].'">
						<DIV class="C_grandlivre moyen I_marges_10">
							<IMG class="image" src="http://f.bookino.org/datas/livres/'.crypter($id).'/couverture_150.png" />
							<DIV class="contenu">
								<DIV class="titre">'.$titre.'</DIV>
								
								<DIV class="description">'.$description.'</DIV>
								<DIV class="infos">
									<DIV class="C_icon t16 x0 y80"></DIV>17 paragraphes<BR/>
									<DIV class="C_icon t16 x16 y80"></DIV>11 idées<BR/>
								</DIV>
							</DIV>
						</DIV>
					</A>
			');
		}
	}else{
		echo('		<DIV class="C_txt_description2">
						Aucun livre dans cette catégorie
					</DIV>
		');
	};
	
	echo('
				</CENTER></DIV>
			</TD>
		</TR></TBODY></TABLE>
	</DIV>');
?>