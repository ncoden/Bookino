var semblerecrire = {}

//CODE JS PROPRE A LA PAGE DE LIVRE
function paragraphe_main(){
	//icon de chargement
	info_cacher();
	$('#U_livre_zoneajouter').html('<DIV class="C_icon t16 chargement centre_v"></DIV>');
	
	//requete ajax
	ajax_ajouter('paragraphe_main', {action:'paragraphe_main', livre_cle:livre['cle']}, 'un', false, function(reponse, ajax){
		if(reponse){
			html = reponse.html;
			evenement = reponse.evenement;
			
			$('#U_livre_zoneajouter').html(html);
			chrono_ajouter('[chrono^="paragraphe_ecrit"]');
			
			if(evenement == 'ecrivain'){
				//si j'écris
				ajax_ajouter('paragraphe_envoyer', {
					action:'paragraphe_envoyer', 
					livre_cle:livre.cle, 
					contenu:function(){return $('.U_livre_textarea').val();}
				}, 'page', false, null);
			};
		};
	});
	ajax_priorite('haute', 1);
}

function paragraphe_liberer(element){
	//icon de chargement
	info_cacher();
	element.addClass('verif').attr('disabled', 'disabled');
	
	//requete ajax
	ajax_ajouter('paragraphe_liberer', {action:'paragraphe_liberer', livre_cle:livre['cle']}, 'un', false, function(reponse, ajax){
		if(reponse){
			html = reponse.html;
			evenement = reponse.evenement;
		
			$('#U_livre_zoneajouter').html(html);
			chrono_ajouter('[chrono^="paragraphe_ecrit"]');
			element.removeClass('verif');
		};
	});
	ajax_priorite('haute', 1);
}

function paragraphe_alerte(element){
	//icon de chargement
	info_cacher();
	element.addClass('verif').attr('disabled', 'disabled');
	
	//requete ajax
	ajax_ajouter('paragraphe_alerte', {action:'paragraphe_alerte', livre_cle:livre['cle']}, 'un', false, function(reponse, ajax){
		element.removeClass('verif').addClass('valide');
		setTimeout(function(){element.removeClass('valide')}, 1000);
	});
	ajax_priorite('haute', 1);
}

function paragraphe_actualiser(){
	
	//requete ajax
	ajax_priorite('haute', 1);
}

function paragraphe_e_ecrivain(){
	chrono_pause('paragraphe_ecrit');
	$('#U_livre_zoneajouter_haut').html('\
		Il vous reste au moins\
		<SPAN chrono="paragraphe_ecrit;60;0;-;true;{60:chrono_pause(\'paragraphe_ecrit\'),0:paragraphe_actualiser()}">60</SPAN>\
		secondes tant que <BR/> personne ne demande à écrire un paragraphe\
	');
}

function paragraphe_valider(si_annuler){
	//icon de chargement
	info_cacher();
	
	//si contenu vide, annule juste sans creer de paragraphe
	if(!si_annuler){
		contenu = $('.U_livre_textarea').val();
	}else{
		contenu = null;
	};
	
	$('#U_livre_zoneajouter').html('<DIV class="C_icon t16 chargement centre_v"></DIV>');
	
	//requete ajax
	ajax_ajouter('paragraphe_valider', {action:'paragraphe_valider', livre_cle:livre['cle'], contenu:contenu}, 'un', false, function(reponse, ajax){
		if(reponse){
			html = reponse.html;
			evenement = reponse.evenement;
			
			//modifi zone
			$('#U_livre_zoneajouter').html(html);
			
			//retire actions chrono & ajax
			if(evenement != 'ecrivain' && evenement != 'ecrivain_liberer'){
				chrono_supprimer('[chrono^="paragraphe_ecrit"]');
				ajax_supprimer('paragraphe_envoyer');
			};
		};
	});
	ajax_priorite('haute', 1);
}

function sembler_ecrire(element, chaine, numero, taille, depuisfonction){
	if(chaine != ''){

		if(depuisfonction || !semblerecrire.etat || typeof(semblerecrire.etat)=='undefined'){
			//recupere valeur
			var valeur = element.val();
			if(!numero){var numero = 0;};
			if(!taille){var taille = chaine.length;};
			var lettre = chaine.substring(numero, numero+1);
			
			if(typeof(semblerecrire.a_ecrire)=='undefined' || !semblerecrire.a_ecrire){semblerecrire.a_ecrire = chaine;};
			semblerecrire.a_ecrire = semblerecrire.a_ecrire.substring(0, taille-1);
			semblerecrire.etat = true;
			
			//calcul temps entre les lettres
			if((semblerecrire.a_ecrire).length != 0){
				var temps = 150*Math.random()*(40/semblerecrire.a_ecrire.length);
				if(temps > 2000){temps = 2000;};
				if(temps < 5){temps = 5;};
			}else{
				var temps = 50 + 100*Math.random();
			};
			
			//ecrit lettre
			element.val(valeur + lettre);
			numero++;
			
			//lance fonction pour lettre suivante
			if(numero < taille){
				setTimeout(function(){sembler_ecrire(element, chaine, numero, taille, true)}, temps);
			}else{
				semblerecrire.etat = false;
				semblerecrire.a_ecrire = false;
			};
		}else{
			//si existe boucle en cours, 
			//ajoute a la boucle la chaine
			semblerecrire.a_ecrire = semblerecrire.a_ecrire + chaine
		};
	};
}

$(document).ready(function(){
	
	//ACTUALISATION DU PARAGRAPHE
	ajax_ajouter('paragraphe_actualiser', {action:'paragraphe_actualiser', livre_cle:livre['cle']}, 'page', false, function(reponse, ajax){	
		if(reponse){
			//recupere valeurs
			html = reponse.html;
			evenement = reponse.evenement;
			contenu = reponse.contenu;
			temps = reponse.temps;
			
			if(paragraphe_evenement!=evenement && evenement=='ecrivain_liberer'){
				//si j'écris et dois liberer, informe
				$('#U_livre_zoneajouter_haut').html('\
					Quelqu\'un a demandé à ce que vous liberiez la salle, <BR/>\
					<B>vous avez <SPAN chrono="paragraphe_ecrit;' + temps + ';0;-;true;{0:paragraphe_actualiser()}">' + temps + '</SPAN>\
					secondes pour finir votre paragraphe</B>\
				');
				chrono_ajouter('[chrono^="paragraphe_ecrit"]');
				$('.U_livre_textarea').addClass('chrono');
			}else if(evenement != 'ecrivain'){
				//si evenement differente, maj zone
				if(paragraphe_evenement != evenement){
					$('#U_livre_zoneajouter').html(html);
					paragraphe_evenement = evenement;
					chrono_ajouter('[chrono^="paragraphe_ecrit"]');
					$('.U_livre_textarea').removeClass('chrono');
					
					ajax_supprimer('paragraphe_envoyer');
				};
				
				//si je n'écrit pas, maj champ
				if(contenu!=null && $('.U_livre_textarea').length){
					var valeur = $('.U_livre_textarea').val();
					
					//si champ vide, ajoute texte
					if(valeur == ''){
						sembler_ecrire( $('.U_livre_textarea'), contenu);
						ajax_priorite('moyenne', 6);
					}else{
						//sinon, ajoute necessaire
						var emplacement = contenu.indexOf(valeur);
						
						if(emplacement==0){
							chaine = contenu.substring(valeur.length, contenu.length);
							if(chaine!=''){
								sembler_ecrire( $('.U_livre_textarea'), chaine);
								ajax_priorite('moyenne', 6);
							};
						}else{
							$('.U_livre_textarea').val(contenu);
							semblerecrire.a_ecrire = '';
						};
					};
				};
			};
		};
	});
	
	//ENREGISTREMENT SI JE SUIS AUTEUR
	if(typeof(livre.ecrivain) != 'undefined' 
	&& livre.ecrivain == true
	&& $('.U_livre_textarea').length){
			ajax_ajouter('paragraphe_envoyer', {
				action:'paragraphe_envoyer', 
				livre_cle:livre.cle, 
				contenu:function(){return $('.U_livre_textarea').val();}
			}, 'page', false, null);
	};
	
	//VERIFICATION DES NOUVEAUX PARAGRAPHES
	ajax_ajouter('paragraphes_ecrire', {action:'paragraphes_ecrire', livre_cle:livre['cle'], nouveau:true, init:true}, 'page', false, function(reponse, ajax){
		if(reponse){
			//ajoute html nouveaux paragraphes
			$('#paragraphe_ecrit').before(reponse);
			
			//retire init
			setTimeout(function(){
				$('.C_paragraphe.init').each(function(){
					e = $(this);
					e.css('height', e.children().height());
					e.removeClass('init');
				});
			}, 1);
		};
	});


	//INIT ET EVENEMENTS
	$(document).on('click', '[pour=paragraphe_liberer]', function(e){paragraphe_liberer( $(this) );});
	$(document).on('click', '[pour=paragraphe_alerte]', function(e){paragraphe_alerte( $(this) );});
	$(document).on('click', '[pour=paragraphe_main]', function(){paragraphe_main();});
	$(document).on('keyup', '[pour=paragraphe_ecrire]', function(){ajax_priorite('moyenne', 3);});
	$(document).on('click', '[pour=paragraphe_valider]', function(){paragraphe_valider(false);});
	$(document).on('click', '[pour=paragraphe_annuler]', function(){paragraphe_valider(true);});
});