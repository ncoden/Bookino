<?php
	//PLUGIN 'FORMULAIRES'
	
	//FONCTIONS
	function si_form_envoye($nom){
		global $forms;
		return si_defini($forms, $nom, false);
	}
	function form_importer($nom){
		global $d;
		global $_wome;
		global $_config;
		
		$repertoire = repertoire('plugins', 'formulaires');
		
		//recupere les donnees du formulaire et stock dans $d
		if(!isset($d['forms'][$nom])){
			//si form non defini, inclu fichier
			if(file_exists($repertoire.'/'.$nom.'.php')){
				include($repertoire.'/'.$nom.'.php');
				
				if(isset($form)){
					$form = e_form($form);
					
					if($form){
						$d['forms'][$nom] = $form;
						return $form;
					}else{
						return false;
					};
				}else{
					return false;
				};
			}else{
				return false;
			};
		}else{
			//sinon, recupere depuis variable
			return $d['forms'][$nom];
		};
	}	
	function e_form($donnees){
		global $_wome;
		
		if(is_array($donnees)){
			//defini valeurs
			$nform = [];
			$nform['champs'] = si_defini($donnees, 1, []);
			$nform['boutons'] = si_defini($donnees, 2, []);
			
			//defini valeurs par defaut
			if(isset($donnees[0]) && is_array($donnees[0])){
				$l_proprietes = [
					'url' => $_wome['url_ajax'],
					'titre' => null, 
					'actualiser' => false, 
					'design' => false, 
					'action_js' => null, 
					'action_php' => null, 
					'css' => null,
				];
				foreach($l_proprietes as $nom=>$defaut){
					$nform[$nom] = si_defini($donnees[0], $nom, $defaut);
				}
			};
			return $nform;
		}else{
			return false;
		};
	}
	function form_verifier($nom, $donnees){
		global $_wome;
		global $l_forms;
		
		//importe le formulaire
		$form = form_importer($nom);
		if($form){
			$form_valide = true;
			$regles = [
				'obligatoire'=>	function($e_champ, $valeur, $value){return ($valeur && ($value=='' || $value=='undefined' || (isset($e_champ['proprietes']['defaut']) && $e_champ['proprietes']['defaut']==$value)));},
				'taille_min'=>	function($e_champ, $valeur, $value){return (strlen($value) < $valeur);},
				'taille_max'=>	function($e_champ, $valeur, $value){return (strlen($value) > $valeur);},
			];
			
			//pour chaque champ
			foreach($form['champs'] as $champ_nom=>$champ){
				
				$type = $champ[1];
				if($type != 'titre' && $type != 'html'){
					$erreur = false;
					$erreur_valeur = false;
					
					$proprietes = si_defini($champ, 2, []);
					$necessaire = si_defini($champ, 3, []);
					$necessaire['obligatoire'] = si_defini($champ, 4, false);
					$value = si_defini($donnees, $champ_nom, null);
					$e_champ = ['proprietes'=>$proprietes, 'necessaire'=>$necessaire];
										
					//verifi toutes les regles
					foreach($regles as $regle_nom=>$fonction){
						//si elles sont definies
						if(isset($necessaire[$regle_nom])){
							$valeur = $necessaire[$regle_nom];
							
							if($fonction($e_champ, $valeur, $value)){
								$form_valide = false;
								$erreur = $regle_nom;
								break;
							};
						};
					}
					
					//si captcha
					if($type=='captcha' && !$erreur){
						$value = md5(strtoupper(trim($value)));
						
						if($value == null
						|| !isset($_SESSION['captcha'])
						|| $value != $_SESSION['captcha']){
							$form_valide = false;
							$erreur = 'captcha';
						};
					};
					
					//si erreur, retiens
					$l_forms[$nom]['champs'][$champ_nom]['erreur'] = $erreur;
				};
			}
			
			return $form_valide;
		}else{
			return false;
		};
	}
	function form_valide($nom){
		//retourne seulement si le formulaire a été validé
		global $l_forms;
		
		if(isset($l_forms[$nom]) 
		&& $l_forms[$nom]['valide']){
			return true;
		}else{
			return false;
		};
	}
	function form_sidefini($form_nom, $champ_nom, $defaut){
		global $l_forms;
		
		if(isset($l_forms[$form_nom])
		&& isset($l_forms[$form_nom]['champs'])
		&& isset($l_forms[$form_nom]['champs'][$champ_nom])
		&& isset($l_forms[$form_nom]['champs'][$champ_nom]['valeur'])){
			return $l_forms[$form_nom]['champs'][$champ_nom]['valeur'];
		}else{
			return $defaut;
		};
	}
	function html_formulaire($nom, $form=null){
		global $l_forms;
		
		//recupere donnees du formulaire
		if(!is_array($form) && $form!=null){
			$form = form_importer($form);		
		}elseif(!is_array($form) && $form==null){
			$form = form_importer($nom);
		}else{
			$form = e_form($form);
		};
		
		//ecrit form
		$champs = [];
		$return = '<FORM id="form_'.$nom.'" name="form_'.$nom.'" method="POST" action="'.$form['url'].'" onsubmit="return form_envoyer(\''.$nom.'\')">';
		if($form['design']){$return .= '<DIV class="C_zone_texte '.$form['css'].'">';};
		
		//ecrit le titre
		if($form['titre'] != null){$return .= '<CENTER><DIV class="C_txt_titre1">'.$form['titre'].'</DIV></CENTER>';};
		$return .= '<TABLE class="C_formulaire"><TBODY>';
		
		//parcour les champs
		foreach($form['champs'] as $champ_nom=>$tableau){
			$attr = null;
			$contenu = null;
			$attr_visible = null;
			$label = si_defini($tableau, 0, null);
			$type = si_defini($tableau, 1, false);
			$proprietes = si_defini($tableau, 2, []);
			$necessaire = si_defini($tableau, 3, []);
			$obligatoire = si_defini($tableau, 4, false);
			$attr_name = 'form_'.$nom.'['.$champ_nom.']';
			$class = si_defini($proprietes, 'css', null);
			
			if($obligatoire){$necessaire['obligatoire'] = true;};
			
			if($type != 'titre' && $type != 'html'){
				//calcul les phrases d'erreur
				foreach($necessaire as $regle=>$valeur){
					$champs[$champ_nom]['necessaire'][$regle] = $valeur;
				}
				foreach($proprietes as $propriete=>$valeur){
					if(is_numeric($propriete)){
						$proprietes[$valeur] = true;
						unset($proprietes[$propriete]);
						$champs[$champ_nom]['proprietes'][$valeur] = true;
					}else{
						$champs[$champ_nom]['proprietes'][$propriete] = $valeur;
					};
				}
			};
			
			$return .= '<TR>';
			
			//si info pour ou chrono
			if(isset($proprietes['info'])){$attr .= ' info="'.$proprietes['info'].'"';};
			if(isset($proprietes['pour'])){$attr .= ' pour="'.$proprietes['pour'].'"';};
			if(isset($proprietes['chrono'])){$attr .= ' chrono="'.$proprietes['chrono'].'"';};
			if(isset($proprietes['attr']) && is_array($proprietes['attr'])){
				foreach($proprietes['attr'] as $attr_nom=>$attr_valeur){
					$attr .= ' '.$attr_nom.'="'.$attr_valeur.'"';
				};
			};
			
			//si titre
			if($type == 'titre'){$return .= '<TD '.$attr.'><DIV class="titre_label">'.$label.'</DIV></TD>
											 <TD><DIV class="titre_champ"></DIV></TD>';}; 
			
			//si avec texte devant, ecrit texte
			$class_label = null;
			if(isset($proprietes['label_position'])){$class_label .= ' '.$proprietes['label_position'];};
			if(isset($proprietes['label_css'])){$class_label .= ' '.$proprietes['label_css'];};
			
			if($label !== false){
				$return .= '<TD class="label'.$class_label.'">'.$label;
				if($proprietes && isset($proprietes['description'])){
					$return .= '<DIV class="description">'.$proprietes['description'].'</DIV>';
				};
				$return .= '</TD>';
			};
			$return .= '<TD>';
			
			if($label === false){
				$return .= '<CENTER>';
			};
			
			//prepare pour champ, textarea ou simple texte
			if($type=='champ' || $type=='textarea'){
				//verification de validité
				$attr .= ' onchange="form_verifier(\''.$nom.'\', \''.$champ_nom.'\', \'change\');"
						   onkeyup="form_verifier(\''.$nom.'\', \''.$champ_nom.'\', \'keyup\');"';
								
				//contenu et defaut
				$value = null;
				if($obligatoire){$class .= ' requiere';};
				if(isset($proprietes['contenu'])){
					$value = $proprietes['contenu'];
				};
				if(isset($proprietes['defaut'])){
					$attr .= ' defaut="'.$proprietes['defaut'].'" placeholder="'.$proprietes['defaut'].'"';
					if(!isset($proprietes['contenu'])){
						$value = $proprietes['defaut'];
					};
				};
				$value = form_sidefini($nom, $champ_nom, $value);
				if($value != null){
					if($type=='champ'){$attr .= ' value="'.$value.'"';};
					if($type=='textarea'){$contenu .= $value;};
				};
			};
			
			//si champ
			if($type == 'champ'){
				//type texte ou motdepasse
				if(isset($proprietes['motdepasse']) && $proprietes['motdepasse']){
					$attr .= ' motdepasse="motdepasse"';
				};
				$return .= '<DIV class="C_champ moyen'.$class.'"'.$attr_visible.'><INPUT name="'.$attr_name.'" type="text"'.$attr.'/></DIV>';
			};
			//si textarea
			if($type == 'textarea'){$return .= '<TEXTAREA name="'.$attr_name.'" class="C_champ textarea'.$class.'"'.$attr.'>'.$contenu.'</TEXTAREA>';};
			//si texte
			if($type == 'texte'){$return .= $proprietes['contenu'];};
			
			//si bouton
			if($type == 'bouton'){$return .= '<BUTTON name="'.$attr_name.'" type="button" class="'.$class.'"'.$attr.'>'.$proprietes['contenu'].'</BUTTON>';};
			
			//si choix
			if($type == 'choix' && $proprietes && isset($proprietes['contenu'])){
				$i = 0;
				$taille = count($proprietes['contenu']);
				$return .= '<DIV class="C_choix" '.$attr.'>';
				
				foreach($proprietes['contenu'] as $nom_e=>$sous_tableau){
					$sous_label = si_defini($sous_tableau, 0, false);
					$sous_proprietes = si_defini($sous_tableau, 1, false);
					
					$i++;
					$sous_attr = null;
					$label_class = null;
					$label_attr = null;
					if($i == 1){$label_class .= ' gauche';};
					if($i == $taille){$label_class .= ' droite';};
					if(isset($proprietes['defaut']) && $proprietes['defaut']==$nom_e){$sous_attr .= ' checked';};
					if(isset($sous_proprietes['info'])){$label_attr .= ' info="'.$sous_proprietes['info'].'"';};
					if(isset($sous_proprietes['icon'])){
						$prop = $sous_proprietes['icon'];
						$sous_label = html_icon($prop[0], $prop[1], $prop[2], $prop[3]).' '.$sous_label;
					};
					
					$return .= '<INPUT type="radio" class="radio" id="'.$attr_name.'_'.$nom_e.'" name="'.$attr_name.'" value="'.$nom_e.'" '.$sous_attr.'/>'.
							   '<LABEL class="choix'.$label_class.'" for="'.$attr_name.'_'.$nom_e.'" '.$label_attr.'>'.$sous_label.'</LABEL>';
				}
				$return .= '</DIV>';
			};
			
			//si captcha
			if($type == 'captcha'){$return .= '
				<DIV class="C_captcha">
					<IMG src="/captcha"/ id="captcha_'.$nom.'">
					<BUTTON class="C_bouton blanc" type="button" onclick="captcha_actualiser(\''.$nom.'\');">'.html_icon('actualiser', 16, 112, 32, true).'</BUTTON>
					<INPUT name="'.$attr_name.'" type="text" class="C_champ moyen"/>
				</DIV>';
			};
			
			//si HTML
			if($type == 'html'){$return .= $proprietes['contenu'];};
			
			//ferme quand centré (sans label)
			if($type!='titre'){
				if($label === false){
					$return .= '</CENTER>';
				};
				$return .= '</TD>';
			};
			$return .= '</TR>';
		}
		
		$return .= '</TBODY></TABLE>';
		if($form['design']){$return .= '</DIV><DIV class="C_zone_valide">';};
		$return .= '<CENTER class="I_marges_10">';
		
		//boutons
		foreach($form['boutons'] as $nom_champ=>$tableau){
			$label = si_defini($tableau, 0, false);
			$proprietes = si_defini($tableau, 1, false);
			
			$return .= '<BUTTON class="C_bouton '.si_defini($proprietes, 'css', null).'"';
			if(isset($proprietes['action']) && $proprietes['action']!=null){
				if($proprietes['action'] == 'envoyer'){
					$return .= ' type="submit" ';
				}else{
					$return .= ' pour="'.$proprietes['action'].'" ';
				};
			};
			$return .= '>'.$label.'</BUTTON>';
		}
		$return .= '</CENTER>';
		
		if($form['design']){$return .= '</DIV>';};
		$return .= '</FORM>';
		
		//Javascript
		inclure_js('form_ajouter("'.$nom.'", "'.$form['actualiser'].'", '.json_encode($champs).', function(reponse, ajax){'.$form['action_js'].'});', true);
		if(isset($l_forms[$nom]) && !$l_forms[$nom]['valide']){
			inclure_js('setTimeout(function(){form_verifier("'.$nom.'", false, "*");}, 1);', true);
		};
		
		return $return;
	}
	
	
	//CONFIGURATION
	$conf = [
		
	];
	
	
	//CODE EXECUTE
	//verification des formulaires
	
	//variables crées
	global $forms;
	
	//variables utilisées
	global $_wome;
	global $_POST;
	
	$forms = [];
	
	//recupere formulaires envoyes
	if($_wome['depuis_ajax']){
		if(isset($_POST['forms'])){
			foreach($_POST['forms'] as $form_nom=>$form){
				$forms[$form_nom] = $form;
			}
			$_POST['forms'] = null;
		};
	}else{
		foreach($_POST as $form_nom=>$form){
			if(is_array($form)){
				$forms[$form_nom] = $form;
			};
		}
	};
	
	//verifi validite des formulaires
	foreach($forms as $form_nom=>$donnees){
		if($donnees){
			//determine nom
			if(strlen($form_nom) > 5 && substr($form_nom,0,5) == 'form_'){
				$form_nom = substr($form_nom, 5);
			};
			
			//recupere parametres et verifi
			$form = form_importer($form_nom);
			
			//si le formulaire existe
			if($form){
				//supprime le formulaire de $_POST
				if(!$_wome['depuis_ajax']){
					unset($_POST[$form_nom]);
				};
				
				//verifi sa validite
				$valide = form_verifier($form_nom, $donnees);
				
				foreach($donnees as $champ_nom=>$donnee){
					$l_forms[$form_nom]['champs'][$champ_nom]['valeur'] = $donnee;
				}
				foreach($form['champs'] as $champ_nom=>$donnee){
					$type = $donnee[1];
					if($type != 'titre'){
						$l_forms[$form_nom]['champs'][$champ_nom]['necessaire'] = si_defini($donnee, 3, []);
						$l_forms[$form_nom]['champs'][$champ_nom]['necessaire']['obligatoire'] = si_defini($donnee, 4, false);
					};
				}
				$l_forms[$form_nom]['valide'] = $valide;
				if($valide){
					//si valide fait action
					if($form['action_php'] != null){$valide = $form['action_php']($donnees);};
				};
				
				if(!$valide){
					//sinon, renvoi a la page de generation
					if($_wome['depuis_site']){
						$url = $_wome['url_envoi'];
						$adresse = url_vers_pages($url);
						$adresse = pages_retirerlangue($adresse);
						$url = adresse_vers_url($adresse);
					}else{
						header('Location: '.$_wome['url_envoi']);
					};
					break;
				};
			};
		};
	}
?>