<?php
	//FONCTIONS CONCERNANT LES PARAGRAPHES

//VOTER
function script_voter($donnees){
	global $datetime;
	global $user;
	
	$cle = decrypter($donnees['cle']);
	$sens = $donnees['sens'];
	
	if($sens == 'plus'){$sens = 1;};
	if($sens == 'moins'){$sens = 0;};
	
	if(substr($cle,0,3) == 'PAR'){
		//si paragraphe
		
		//verifi que le paragraphe existe
		$paragraphe = INFOS('paragraphe', ['cle'=>$cle]);
		if($paragraphe){
			//verifi que la vote n'existe pas déjà
				$sql_rep = SELECT('votes_p', ['idParagraphe'=>$paragraphe['id'], 'idUtilisateur'=>$user['id']]);
				if(empty($sql_rep)){
					//si n'existe pas insert la vote
					INSERT('votes_p', ['idParagraphe'=>$paragraphe['id'], 'idUtilisateur'=>$user['id'], 'vote'=>$sens, 'date'=>$datetime]);
					return true;
				}else{
					//si existe, met à jour
					UPDATE('votes_p', ['idParagraphe'=>$paragraphe['id'], 'idUtilisateur'=>$user['id']], ['vote'=>$sens]);
				};
		};
	};
}

//PRENDRE LA MAIN
function script_paragraphe_main($donnees){
	global $datetime;
	global $user;
	
	$livre_cle = decrypter($donnees['livre_cle']);
	//verifi si livre existe
	$livre = INFOS('livre', ['cle'=>$livre_cle]);
	if($livre && $livre['idUtilisateur_ecrit']==null){
		//met a jour utilisateur qui peux ecrire
		UPDATE('livres', ['cleLivre'=>$livre_cle], ['idUtilisateur_ecrit'=>$user['id'], 'date_ecrit'=>$datetime]);
		
		return script_paragraphe_actualiser($donnees);
	};
}
//ALERTER AUTREUSER
function script_paragraphe_alerte($donnees){
	global $user;
	global $datetime;
	
	$temps_alerte = 10;
	
	$return = false;
	
	$livre_cle = decrypter($donnees['livre_cle']);
	//verifi si livre existe
	$livre = INFOS('livre', ['cle'=>$livre_cle]);
	$decalage_ecrire = langue_date($livre['date_ecrit'])['decalage'];
	$autreuser_id = $livre['idUtilisateur_ecrit'];
	
	//si livre existe et que l'autre user ecrit depuis plus de 4mn
	if($livre 
	&& $decalage_ecrire>240 
	&& $autreuser_id!=null
	){
		//liste les autres alertes
		$alerte_recente = false;
		
		$sql_rep = SELECT('notifications', [
			'idUtilisateur'=>$autreuser_id, 
			'type'=>'paragraphe_alerte', 
			'id'=>$livre['id'],	
			'id2'=>$user['id']
		]);
		//regarde si une recente
		foreach($sql_rep as $valeur){
			$decalage_alerte = langue_date($valeur['date'])['decalage'];
			if($decalage_alerte!=false && $decalage_alerte<$temps_alerte){
				$alerte_recente = true;
			};
		}
		
		//si pas d'alerte recente, ajoute notification "alerte"
		if(!$alerte_recente){
			INSERT('notifications', [
				'idUtilisateur'=>$autreuser_id, 
				'type'=>'paragraphe_alerte',
				'id'=>$livre['id'],
				'id2'=>$user['id'],
				'date'=>$datetime
			]);
			$return = true;
		};
	};
	
	return $return;
}
//LIBERER SALLE
function script_paragraphe_liberer($donnees){
	global $datetime;
	global $user;
	
	$livre_cle = decrypter($donnees['livre_cle']);
	
	//verifi si livre existe
	$livre = INFOS('livre', ['cle'=>$livre_cle]);
	$decalage_ecrire = langue_date($livre['date_ecrit'])['decalage'];
	$decalage_liberer = langue_date($livre['date_liberer'])['decalage'];
	
	//si le livre existe et que l'autre user ecrit depuis plus de 4mn
	//et que personne d'autre n'a demandé à liberer.
	if($livre 
	&& $livre['idUtilisateur_ecrit']!=null 
	&& $decalage_ecrire>240 
	&& ($decalage_liberer>60 || !$decalage_liberer)){
		//met a jour date de libération de la salle
		UPDATE('livres', ['cleLivre'=>$livre_cle], ['date_liberer'=>$datetime]);		
		
		return script_paragraphe_actualiser($donnees);
	};
}
//ACTUALISER ZONE D'ECRITURE
function script_paragraphe_actualiser($donnees){
	global $datetime;
	global $user;
	global $l;
	global $langue;
	
	$return = null;
	$evenement = null;
	$contenu = null;
	$temps = null;
	$livre_cle = decrypter($donnees['livre_cle']);
	
	//verifi si livre existe
	$livre = INFOS('livre', ['cle'=>$livre_cle]);
	if($livre){
		if($livre['idUtilisateur_ecrit']!=null){
			
			//regarde si autre utilisateur en train d'écrire
			$autreuser = INFOS('user', ['id'=>$livre['idUtilisateur_ecrit']]);
			
			$date_ecrit = langue_date($livre['date_ecrit']);
			$decalage_ecrit = $date_ecrit['decalage'];
			$depuis = $date_ecrit['date_langue'];
			$date_liberer = langue_date($livre['date_liberer']);
			$decalage_liberer = $date_liberer['decalage'];

			if($decalage_ecrit>=240 && $decalage_liberer>=60){
				//si 4 minutes dépassés et demande de liberer depuis plus de 1 minute, libere
				if($livre['paragraphe_encours'] != null){
					$cle = cle('PAR', 32);
					INSERT('paragraphes', ['cleParagraphe' => $cle, 
										   'idUtilisateur' => $autreuser['id'], 
										   'idLivre' => $livre['id'], 
										   'contenu' => $livre['paragraphe_encours'],
										   'date_cree' => $datetime]);
				};
				UPDATE('livres', ['idLivre'=>$livre['id']], ['idUtilisateur_ecrit'=>null, 'date_liberer'=>null, 'paragraphe_encours'=>null]);
				$livre = INFOS('livre', ['cle'=>$livre_cle]);
			}elseif(!$user['connecte'] || $livre['idUtilisateur_ecrit']!=$user['id']){
				
				//si 4 minutes dépassés, et personne demande à liberer, propose actions
				if($decalage_ecrit>=240 && $decalage_liberer===false){
					$temps = (300 - $decalage_ecrit);
					$html_decalage = ' 		depuis '.$depuis.'.<BR/>';
					
					if($user['connecte']){
						//si connete, propose actions
						$evenement = 'lecteur_actions';
						
						//liste les autres alertes
						$alerte_active = '';
						$sql_rep = SELECT('notifications', [
							'idUtilisateur'=>$autreuser['id'], 
							'type'=>'paragraphe_alerte',
							'id'=>$livre['id'],	
							'id2'=>$user['id']
						]);
						//regarde si une recente
						foreach($sql_rep as $valeur){
							$decalage_alerte = langue_date($valeur['date'])['decalage'];
							if($decalage_alerte!=false && $decalage_alerte<60){
								$alerte_active = ' disabled';
							};
						}
						
						$html_decalage_apres = '
											<DIV class="C_txt_description2">
												Nous vous conseillions de patienter, mais vous pouvez
											</DIV>
											<BUTTON pour="discussion_ouvrir" 
													class="C_bouton blanc trespetit"
											>Contacter '.$autreuser['pseudo'].'</BUTTON>
											
											<BUTTON pour="paragraphe_alerte" 
													class="C_bouton blanc trespetit" 
													info="Signaler à '.$autreuser['pseudo'].' que vous patientez par une alerte sonore;noir;haut;nonfixe"
											'.$alerte_active.'>Envoyer une alerte</BUTTON>
											
											<BUTTON pour="paragraphe_liberer" 
													class="C_bouton blanc trespetit" 
													info="Laisser une minute à '.$autreuser['pseudo'].' pour terminer son paragraphe;noir;haut;nonfixe"
											>Faire libérer la salle</BUTTON>
						';	
					}else{
						//sinon, demande de se connecter
						$evenement = 'lecteur_deconnecte';
						$html_decalage_apres = '';
					};
						
				}elseif($decalage_ecrit>=240 && $decalage_liberer<60){
					//si 4 minutes dépassés, et demande de liberer depuis moins de 1 minute, informe
					$evenement = 'lecteur_liberer';
					$temps = (60 - $decalage_liberer);
					
					$html_decalage = ' 		. Il liberera la salle dans <SPAN chrono="paragraphe_ecrit;'.(60 - $decalage_liberer).';0;-;true;{0:paragraphe_actualiser()}">'.(60 - $decalage_liberer).'</SPAN> secondes.';
					$html_decalage_apres = '';
				}else{
					//sinon, donne chrono
					$evenement = 'lecteur_simple';
					$temps = (300 - $decalage_ecrit);
					
					$html_decalage = ' 		(<SPAN chrono="paragraphe_ecrit;'.(300 - $decalage_ecrit).';0;-;true;{60:chrono_pause(\'paragraphe_ecrit\')}">'.(300 - $decalage_ecrit).'</SPAN> secondes restantes).';
					$html_decalage_apres = '';
				};
			};
			
			if(isset($html_decalage) || isset($html_decalage_apres)){
				$return .= '<DIV class="I_marges_10">';
				if($user['connecte']){
					$return .= 'Vous ne pouvez pas ajouter de paragraphe pour l\'instant,<BR/>
								<A href="/'.$langue.'/'.$l['P_NOM:UTILISATEUR'].'/'.$autreuser['pseudo'].'" class="C_nomuser">'.$autreuser['pseudo'].'</A> 
								est déjà en train d\'écrire'.$html_decalage;
				}else{
					$return .= 'Voici ce que 
								<A href="/'.$langue.'/'.$l['P_NOM:UTILISATEUR'].'/'.$autreuser['pseudo'].'" class="C_nomuser">'.$autreuser['pseudo'].'</A> 
								est en train d\'écrire '.$html_decalage;
				};
				
				$contenu = $livre['paragraphe_encours'];
				$return .= '</DIV>
							<TEXTAREA class="U_livre_textarea C_champ plein" disabled>'.$livre['paragraphe_encours'].'</TEXTAREA><BR/>
							'.$html_decalage_apres;
			};
		};
		
		if(!$user['connecte']){
			$return = '	<DIV class="I_marges_30">
							Vous devez être connecté pour <BR/>
							ajouter votre paragraphe à ce livre.<BR/>
							<BR/>
							<A href="/'.l('P_NOM:CONNEXION').'" class="C_bouton moyen bleu">'.l('M:SECONNECTER').'</A>
							<A href="/'.l('P_NOM:INSCRIPTION').'" class="C_bouton moyen blanc">'.l('M:SINSCRIRE').'</A>
						</DIV>';
		}else{
			if($livre['idUtilisateur_ecrit'] == $user['id']){
				//si moi utilisateur , affiche textearea
				
				$date_ecrit = langue_date($livre['date_ecrit']);
				$decalage_ecrit = $date_ecrit['decalage'];
				$date_liberer = langue_date($livre['date_liberer']);
				$decalage_liberer = $date_liberer['decalage'];
				
				$return .= '<DIV class="I_marges_10" id="U_livre_zoneajouter_haut">';
				
				if($decalage_liberer===false){
					if($decalage_ecrit>=240 && $decalage_liberer===false){
						$evenement = 'ecrivain';
						$decalage_ecrit = 240;
						$temps = 60;
						$html_decalage = ' tant que <BR/> personne ne demande à écrire un paragraphe';
					}elseif($decalage_ecrit<240 && $decalage_liberer===false){
						$evenement = 'ecrivain';
						$temps = (300 - $decalage_ecrit);
						$html_decalage = '';
					};
					
					$return .= 'Il vous reste au moins 
								<SPAN chrono="paragraphe_ecrit;'.(300 - $decalage_ecrit).';0;-;true;{60:paragraphe_e_ecrivain(),0:paragraphe_actualiser(\'paragraphe_ecrit\')}">'.(300 - $decalage_ecrit).'</SPAN> 
								secondes'.$html_decalage;
					$html_chrono = '';
				}else{
					$evenement = 'ecrivain_liberer';
					$html_decalage = '';
					$temps = (60 - $decalage_liberer);
					
					$return .= 'Quelqu\'un a demandé à ce que vous liberiez la salle, <BR/>
								<B>vous avez <SPAN chrono="paragraphe_ecrit;'.(60 - $decalage_liberer).';0;-;true;{0:paragraphe_actualiser()}">'.(60 - $decalage_liberer).'</SPAN>
								secondes pour finir votre paragraphe</B>';
					$html_chrono = ' chrono';
				};
				
				$contenu = $livre['paragraphe_encours'];
				$return .= '</DIV>
							<TEXTAREA class="U_livre_textarea C_champ plein '.$html_chrono.'" pour="paragraphe_ecrire">'.$livre['paragraphe_encours'].'</TEXTAREA>
							<DIV class="I_marges_10 I_droite">
								<BUTTON pour="paragraphe_annuler" class="C_bouton blanc moyen">'.$l['M:ANNULER'].'</BUTTON>
								<BUTTON pour="paragraphe_valider" class="C_bouton bleu moyen">'.$l['M:VALIDER'].'</BUTTON>
							</DIV>';
				inclure_js('livre.ecrivain = true;');
				
			}elseif($livre['idUtilisateur_ecrit'] == null){
				$evenement = 'propose_ecrire';
				
				//sinon propose de prendre la main
				$return = '<DIV class="I_marges_30">
								Personne n\'écrit pour l\'instant, <BR/>
								vous pouvez prendre la main 5 minutes <BR/>
								pour ajouter votre paragraphe <BR/>
								<BR/>
								<BUTTON class="C_bouton bleu moyen I_marges_V" pour="paragraphe_main">'.$l['M:AJOUTERUNPARAGRAPHE'].'</BUTTON>
							</DIV>';
			};
		};
	}else{
		$return = 'Le livre n\'existe plus, veuillez actualiser la page';
	};
	
	return ['html' => $return,
			'evenement' => $evenement,
			'contenu' => $contenu,
			'temps' => $temps,
			];
}
//SAUVEGARDER ECRITURE
function script_paragraphe_envoyer($donnees){
	global $user;
	
	$return = false;
	$contenu = $donnees['contenu'];
	
	if($contenu != ''){
		$livre_cle = decrypter($donnees['livre_cle']);
		
		//verifi si livre existe
		$livre = INFOS('livre', ['cle'=>$livre_cle]);
		if($livre){
			//verifi qu'on est bien ecrivain
			if($livre['idUtilisateur_ecrit'] == $user['id']){
				//met à a jour
				UPDATE('livres', ['idLivre'=>$livre['id']], ['paragraphe_encours'=>$contenu]);
				$return = true;
			};		
		};
	};
	
	return $return;
}
//AJOUTER PARAGRAPHE
function script_paragraphe_valider($donnees){
	global $datetime;
	global $user;
	
	$livre_cle = decrypter($donnees['livre_cle']);
	$contenu = si_defini($donnees, 'contenu', '');
	
	//verifi si livre existe et on est auteur
	$livre = INFOS('livre', ['cle'=>$livre_cle]);
	if($livre && $livre['idUtilisateur_ecrit']==$user['id']){
		
		//ajoute paragraphe
		if($contenu != ''){
			$cle = cle('PAR', 32);
			INSERT('paragraphes', ['cleParagraphe' => $cle, 
								   'idUtilisateur' => $user['id'], 
								   'idLivre' => $livre['id'], 
								   'contenu' => $contenu,
								   'date_cree' => $datetime]);
		};
		
		//rend livre libre
		UPDATE('livres', ['idLivre'=>$livre['id']], ['idUtilisateur_ecrit'=>null, 'date_liberer'=>null, 'paragraphe_encours'=>null]);
		return script_paragraphe_actualiser($donnees);
	};
	return false;
}

//RECUPERER PARAGRAPHES OU VARIANTES
function script_paragraphes_recuperer($donnees){
	global $datetime;
	global $_SESSION;
	global $db;
	global $user;
	$return = false;
	
	//recupere donnees
	$si_nouveau = si_defini($donnees, 'nouveau', false);
	$type = si_defini($donnees, 'type', 'paragraphe');
	
	if($si_nouveau){
		$date_charge = si_defini($_SESSION['dates'], 'paragraphes', false);
	};
	session_date('paragraphes');
	
	if(($type == 'paragraphe' || $type == 'variante')
	&& !$si_nouveau || ($si_nouveau && $date_charge)){
		//verifi livre existe
		if($type=='paragraphe'){$cle = decrypter(si_defini($donnees, 'livre_cle', false)); $livre = INFOS('livre', ['cle'=>$cle]);};
		if($type=='variante'){$cle = decrypter(si_defini($donnees, 'paragraphe_cle', false)); $livre = INFOS('paragraphe', ['cle'=>$cle]);};
		
		if($livre){
			$return = [];
			
			//construit requete suivant parametres
			if($type == 'paragraphe'){$table='paragraphes'; $id='idParagraphe'; $cle='cleParagraphe'; $filtre='idLivre'; $filtre_id=$livre['id']; $table_vote='vote_p';};
			if($type == 'variante'){$table='variantes'; $id='idVariante'; $cle='cleVariante'; $filtre='idParagraphe'; $filtre_id=$livre['id']; $table_vote='vote_v';};
			
			$requete = 'SELECT * FROM '.$table.' WHERE '.$filtre.'=?';
			$tableau = [$filtre_id];
			
			if($si_nouveau){
				$requete .= ' AND date_cree>?';
				$tableau[] = $date_charge;
			};
			
			//requete
			$req = $db->prepare($requete);
			if(!$req->execute($tableau)){echo('erreur_bdd');exit();};
			while($sql_rep = $req->fetch()){
				$paragraphe = $sql_rep;
				$paragraphe['id'] = $paragraphe[$id];
				$paragraphe['cle'] = $paragraphe[$cle];
				
				//verifi ecrivain existe
				$autreuser = INFOS('user', ['id'=>$paragraphe['idUtilisateur']]);
				if($autreuser){
					//compte les votes des paragraphes
					$note = 0;
					$monvote = false;
					$votes = LISTER($table_vote, [$id=>$paragraphe['id']]);
					foreach($votes as $vote){
						if($vote['vote'] == 0){$note--;};
						if($vote['vote'] == 1){$note++;};
						
						//retiens mon vote
						if($user['connecte']){
							if($vote['vote'] == 0){$monvote = 'moins';};
							if($vote['vote'] == 1){$monvote = 'plus';};
						};
					}
					
					$return[crypter($paragraphe['cle'])] = [
						'user_cle' => crypter($autreuser['cle']),
						'user_pseudo' => $autreuser['pseudo'],
						'contenu' => $paragraphe['contenu'],
						'date_cree' => $paragraphe['date_cree'],
						'note' => $note,
						'nombre_votes' => count($votes),
						'monvote' => $monvote,
					];
					
					if($type == 'paragraphe'){
						$return[crypter($paragraphe['cle'])]['variantes'] = script_paragraphes_recuperer(['paragraphe_cle'=>crypter($paragraphe['cle']), 'type'=>'variante']);
					};
				};
			}
		};
		return $return;
	};
}
//ECRIRE (html) PARAGRAPHES OU VARIANTES
function script_paragraphes_ecrire($donnees){
	global $user;
	global $langue;
	global $date;
	$return = '';
	
	$paragraphes = script_paragraphes_recuperer($donnees);
	if(!$paragraphes){return false;};
	
	foreach($paragraphes as $cle=>$paragraphe){
		$html_votemoins_class = '';
		$html_voteplus_class = '';
		$html_votemoins_attr = '';
		$html_voteplus_attr = '';
		$html_init_class = ' ';
		
		//paragraphe init
		if(isset($donnees['init']) && $donnees['init']==true){
			$html_init_class = ' init';
		};
		
		//mes votes
		if($paragraphe['monvote'] == 'moins'){
			inclure_js('l_votes["'.$cle.'"] = "moins";');
			$html_votemoins_class = ' rouge';
			$html_votemoins_attr = ' disabled';
		};
		if($paragraphe['monvote'] == 'plus'){
			inclure_js('l_votes["'.$cle.'"] = "plus";');
			$html_voteplus_class = ' vert';
			$html_voteplus_attr = ' disabled';
		};
		if($paragraphe['note'] > 0){
			$html_note = '+'.$paragraphe['note'];
		}else{
			$html_note = $paragraphe['note'];
		};
		
		if($paragraphe['nombre_votes'] == 1){
			$html_nbvotes = '1 '.strtolower(l('M:VOTE'));
		}else{
			$html_nbvotes = $paragraphe['nombre_votes'].' '.strtolower(l('M:VOTES'));
		};
		$contenu = str_replace("\n", '<BR/>', $paragraphe['contenu']);
		
		//ecrit le paragraphe
		$return .= '<DIV class="C_paragraphe'.$html_init_class.'" id="paragraphe" cle="'.$cle.'">
						<TABLE class="C_grandezone tresclair"><TBODY><TR>
							<TD class="U_livre_zonegauche contenu migauche clair">
								<DIV class="C_grandezone_texte I_marges_V">
									'.l('M:AUTEUR').' : 
									<A class="C_nomuser" href="/'.url_vers_lien(l('P_NOM:UTILISATEUR').'/'.$paragraphe['user_pseudo']).'">
										'.$paragraphe['user_pseudo'].'
									</A><BR/>
									'.l('M:DATE').' : <B>'.$date.'</B><BR/>
								</DIV>
							</TD>
							
							<TD class="U_livre_zonecentre contenu">
								<DIV class="C_grandezone_texte I_marges_V">
									'.$contenu.'
								</DIV>
							</TD>
							
							<TD class="U_livre_zonedroite contenu midroite clair">
								<DIV class="C_grandezone_texte"><CENTER>
									<DIV class="C_vote">';
		
		if($user['connecte']){$return .= '<BUTTON class="voter C_bouton blanc trespetit '.$html_votemoins_class.'" pour="voter" sens="moins" '.$html_votemoins_attr.'>-</BUTTON>';};
		$return .= '						<DIV class="vote">'.$html_note.'</DIV>';
		if($user['connecte']){$return .= '<BUTTON class="voter C_bouton blanc trespetit '.$html_voteplus_class.'" pour="voter" sens="plus" '.$html_voteplus_attr.'>+</BUTTON>';};
		$return .= '				</DIV>';
		if($user['connecte']){$return .= '
									<DIV class="C_txt_description2">('.$html_nbvotes.') <DIV class="C_lien">signaler</DIV></DIV>
		';};
		
		//ecrit les variantes
		foreach($paragraphe['variantes'] as $variante){
			$return .= '			<TABLE class="C_variante I_marges_V"><TBODY><TR>
										<TD class="vote">0</TD>
										<TD class="contenu">Par 
											<A class="C_nomuser" href="/'.$langue.'/'.l('P_NOM:UTILISATEUR').'/'.$variante['user_pseudo'].'">
												'.$variante['user_pseudo'].'
											</A>
										</TD>
									</TR></TBODY></TABLE>';
		}
	
		if($user['connecte']){$return .= '
									<A href="#" class="C_bouton blanc trespetit I_marges_V">'.l('M:PROPOSERUNEVARIANTE').'</A>
		';};
		$return .= '			</CENTER></DIV>
							</TD>
						</TR></TBODY></TABLE>
					</DIV>';
	}
	
	return $return;
}
	

?>