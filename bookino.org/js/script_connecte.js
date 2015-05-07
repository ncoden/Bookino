
//CODE JS QUAND USER CONNECTE

//DECLARATION
var l_discussions = {};
var l_users = {};
var l_votes = {};
var l_notifs = {};

var menu_precedent = '';
var menu_ouvert = false;
var l_menus = {};

var listes_temps = {};

//FONCTIONS

function menu_afficher(menu_demande){
	
	info_toutcacher();
	
	if((menu_demande != menu_precedent) || !menu_ouvert){
		if(typeof(timeout['menu_ouvert']) != 'undefined'){
			clearTimeout(timeout['menu_ouvert']);
		};
		
		//affiche le menu
		$('.POS_chat').addClass('chatactive').removeClass(menu_precedent).addClass(menu_demande);
		$('.POS_chat_top').addClass('chatactive').removeClass(menu_precedent).addClass(menu_demande);
		$('.POS_Bookino').addClass('chatactive').removeClass(menu_precedent).addClass(menu_demande);
		if(menu_precedent){$('.POS_chat .'+menu_precedent).css('display','none');};
		$('.POS_chat .'+menu_demande).css('display','block');
		
		//retien et ajax
		ajax_ajouter('menu_afficher', {action:'menu_afficher', menu:menu_demande}, 'un', true, function(reponse, ajax){
			$('#menu_'+menu_demande).removeClass('init');
		});
		
		ajax_exec();
		
		menu_precedent = menu_demande;
		menu_ouvert = true;
		
	}else{
		//cache le menu
		$('.POS_chat').removeClass('chatactive');
		$('.POS_chat_top').removeClass('chatactive');
		$('.POS_Bookino').removeClass('chatactive');
		timeout['menu_ouvert'] = setTimeout(function(){$('.POS_chat .'+menu_demande).css('display', 'none');}, 400);
		
		//retiens
		ajax_ajouter('menu_afficher', {action:'menu_cacher'}, 'un', true, null);
		menu_ouvert = false;
	};
}
function menu_notifs(menu, pour, nombre){
	//defini tableau si vide
	if(typeof(l_menus[menu]) == 'undefined'){
		l_menus[menu] = {};
	};	
	if(typeof(l_menus[menu][pour]) == 'undefined'){
		l_menus[menu][pour] = 0;
	};
		
	//si actualiser
	if(nombre == '='){
		nombre = l_menus[menu][pour];
	};
	
	//change nombre
	switch(nombre){
		//met à 0
		case 0:		l_menus[menu][pour] = 0; break;
		case '+': 	l_menus[menu][pour]++; break;
		case '-': 	l_menus[menu][pour]--; break;
		default:	l_menus[menu][pour] = nombre; break;
	};
	
	//change notif du sous menu
	if(l_menus[menu][pour] > 0){
		$('[pour="afficher_notif_nombre"][visee="'+menu+' '+pour+'"]').addClass('visible').html(l_menus[menu][pour]);
	}else{
		l_menus[menu][pour] = 0;
		$('[pour="afficher_notif_nombre"][visee="'+menu+' '+pour+'"]').removeClass('visible').html('');
	};
	
	//change notif du menu
	var taille = 0;
	for(i in l_menus[menu]){
		if(l_menus[menu][i] != 0){
			taille++;
		};
	}
	
	if(taille == 0){
		$('#bouton_menu_'+menu).removeClass('active');
		$('#bouton_menu_'+menu+' .texte').html('');
	}else{
		$('#bouton_menu_'+menu).addClass('active');
		$('#bouton_menu_'+menu+' .texte').html(taille);
	};
}
function notif_cacher(notif_cle){
	
	if(typeof(l_notifs[notif_cle]) != 'undefined' 
	&& l_notifs[notif_cle].etat != 'vu'){
	
		element = $('#notif[cle="'+notif_cle+'"]');
		e_contenu = element.find('.contenu');
		
		//envoi requete
		ajax_ajouter('notifs_vues', {action:'notifs_vues'}, 'un', false, null);
		ajax_priorite('moyenne', 3);
		
		//ajoute nouvelle notif dans menu
		$('#menu_infos').prepend('	<DIV class="C_notif dansmenu active init" id="notif" cle="nouveau_'+notif_cle+'" style="visibility:hidden;"><DIV class="contenu">\
										' + l_notifs[notif_cle].html + '<DIV class="fermer" pour="notif_supprimer"></DIV>\
									</DIV></DIV>');
		element_nouveau = $('#notif[cle="nouveau_'+notif_cle+'"]');
		e_contenu_nouveau = element_nouveau.find('.contenu');
		
		//e_contenu.css({top:'5px', bottom:'auto'});
		
		setTimeout(function(){
			if(menu_ouvert && menu_precedent=='infos'){
				//calcul largeur du menu
				var div_temp = $('<DIV/>');
				$('#menu_infos').append(div_temp);
				var largeur_interne = (div_temp.width() - 30);
				div_temp.remove();
				
				e_contenu.css({width:largeur_interne});
			};
			
			var top = (5 - element.position().top);
			
			//déplace notif nouvelle notif
			e_contenu.css({top:top, bottom:'auto'});
			element.css({left:'100%', right:'-100%', height:0}).removeClass('horsmenu').addClass('dansmenu');
			element_nouveau.removeClass('init');
			
			//remplace notif par nouvelle
			setTimeout(function(){
				element.remove();
				element_nouveau.css({visibility:'visible'});
			}, 400);
		}, 1);
		
		l_notifs[notif_cle].etat = 'vu'
	};
}
function notif_supprimer(notif_cle){
	element = $('#notif[cle="'+notif_cle+'"]');
	e_contenu = element.find('.contenu');
	
	//envoi requete
	ajax_ajouter('notif_supprimer'+notif_cle, {action:'notif_supprimer', notif_cle:notif_cle}, 'un', false, null);
	ajax_priorite('moyenne', 3);
	
	element.css({overflow: 'hidden'});
	
	setTimeout(function(){
		e_contenu.css({left:'100%', right:'-100%', opacity:0});
		
		setTimeout(function(){
			element.css({height:0});
			setTimeout(function(){element.remove();}, 300);
		}, 100);
	}, 1);
}

function discussion_afficher(discussion_cle){
	discussion_id = '#discussion_'+discussion_cle;
	
	if(typeof(l_discussions[discussion_cle])=='undefined'){
		l_discussions[discussion_cle] = {};
	};
	if(typeof(l_discussions[discussion_cle].ouvert)=='undefined'){
		l_discussions[discussion_cle].ouvert = 'visible';
	};

	if(l_discussions[discussion_cle].ouvert=='visible'){
		//cache la discussion
		$(discussion_id).addClass('cache');
		l_discussions[discussion_cle].ouvert = 'cache';
		ajax_ajouter('discussion_afficher'+discussion_cle, {action:'discussion_cacher', discussion_cle:discussion_cle}, 'un', true, null);
	}else if(l_discussions[discussion_cle].ouvert=='cache'){
		//affiche la discussion
		$(discussion_id).removeClass('cache');
		l_discussions[discussion_cle].ouvert = 'visible';
		ajax_ajouter('discussion_afficher'+discussion_cle, {action:'discussion_afficher', discussion_cle:discussion_cle}, 'un', true, null);
		
		//evenement de "lu"
		discussion_lu(discussion_cle);
	};
}
function discussion_ouvrir(discussion_cle, discussion_nom, autreuser_cle, ouvert){
	var discussion_id = '#discussion_'+discussion_cle;
	var discussion_prechargee = $(discussion_id).is('.prechargee');
	
	if(!($(discussion_id).is('.chargement')) || discussion_prechargee){
		if((typeof(l_discussions[discussion_cle])=='undefined') || (typeof(l_discussions[discussion_cle].ouvert)=='undefined')){
			l_discussions[discussion_cle] = {};
			l_discussions[discussion_cle].etat = 'chargement';
			if(ouvert=='cache'){
				l_discussions[discussion_cle].ouvert = 'cache';
			}else{
				l_discussions[discussion_cle].ouvert = 'visible';
			};
			
			if(!discussion_prechargee){
				//prepare le statut de l'utilisateur
				var autreuser_etat = l_users[autreuser_cle].etat;
				var autreuser_pseudo = l_users[autreuser_cle].pseudo;
				var date = langue_date(l_users[autreuser_cle].date_activite);

				if(autreuser_etat=='connecte'){active=' connecte'; info='connecté';}
				else if(autreuser_etat=='absent'){active=' absent'; info='inactif depuis '+date.date_langue;}
				else{active=''; info = '';};
			
				//ajoute la discussion
				$('#discussions').append('\
					<DIV class="C_discussion init chargement" id="discussion_'+discussion_cle+'" pour="discussion_active" cle="'+discussion_cle+'" user_cle="'+autreuser_cle+'">\
						<DIV class="icon'+active+'" pour="afficher_user_etat" cle="'+autreuser_cle+'" info="'+info+';noir;gauche;fixe"></DIV>\
						<DIV class="C_grandezone_titre" pour="discussion_afficher">'+autreuser_pseudo+'</DIV>\
						<DIV class="icon fermer" pour="discussion_fermer"></DIV>\
						<DIV class="texte" pour="discussion_lu" scrollcontrole>   \
							<DIV class="I_marges" id="discussion_texte_'+discussion_cle+'"></DIV>\
							<DIV class="info" id="discussion_info_'+discussion_cle+'"></DIV>\
						</DIV>\
						<INPUT type="text" class="champ" pour="discussion_envoyer"/>\
					</DIV>\
				');
			};
			
			//lance la detection
			$('.C_discussion[cle='+discussion_cle+'] > [pour=discussion_lu]').scroll(function(){discussion_lu( $(this.parentNode).attr('cle') );});

			//envoi la requete
			ajax_ajouter('discussion_ouvrir'+discussion_cle, {action:'discussion_ouvrir', discussion_cle:discussion_cle}, 'un', true, function(reponse, ajax){
				//met a jour la nouvelle cle dans les variables
				var messages = reponse.messages;
				var discussion_cle = reponse.discussion_cle;
				var newdiscussion_cle = reponse.discussion_cle;
				var discussiontexte_id = '#discussion_texte_'+discussion_cle;
				var newdiscussiontexte_id = '#discussion_texte_'+newdiscussion_cle;
				
				//dans les "cle=" et les tableaux
				if(typeof(l_discussions[discussion_cle])!='undefined'){
					if(newdiscussion_cle != discussion_cle){
						//change les cles html
						$(discussion_id).attr('id', 'discussion_'+newdiscussion_cle);
						$(discussiontexte_id).attr('id', newdiscussiontexte_id);
						$('.C_discussion[cle='+discussion_cle+'], [pour=discussion_ouvrir][cle='+discussion_cle+']').attr('cle', newdiscussion_cle);
						
						//change les cles variables
						l_discussions[newdiscussion_cle] = {};
						l_discussions[newdiscussion_cle].ouvert = l_discussions[discussion_cle].ouvert;
						delete l_discussions[discussion_cle];
						
						discussiontexte_id = newdiscussiontexte_id;
						discussion_cle = newdiscussion_cle;
						discussion_id = '#discussion_'+discussion_cle;
					};
					
					//ajoute les messages a la discussion
					taille = Object.keys(messages).length;
					for(var messages_id in messages){
						var pseudo = messages[messages_id].pseudo;
						var contenu = messages[messages_id].contenu;
						var cle = messages[messages_id].cleUtilisateur;
						discussion_messageajouter(discussion_cle, cle, pseudo, contenu, messages_id);
					}
					$(discussion_id+' > .texte').scrollTop($(discussiontexte_id).outerHeight(true));
					
					l_discussions[discussion_cle].etat = 'ok';
					discussion_lu(discussion_cle);
					$('#discussion_'+discussion_cle).removeClass('chargement');
					if(!discussion_prechargee){$('#discussion_'+discussion_cle+' .champ').focus();};
				};
			});
			
			//lance la verification d'evenements (lus, ecriture...)
			ajax_ajouter('discussion_activites'+discussion_cle, {action:'discussion_activites', discussion_cle:discussion_cle}, 'infini', false, function(reponse, ajax){
				var discussion_cle = ajax.donnees.discussion_cle;
				var date_lu = reponse.date_lu;
				var ecrire = reponse.ecrire;
				
				if(typeof(l_discussions[discussion_cle])!='undefined'){
					date = langue_date(date_lu);
					date_lu = date.date_langue;
					decalage = date.decalage;
					
					//si message lu est dernier message est mien
					if(typeof(l_discussions[discussion_cle].dernier_index)!='undefined'){
						dernier_index = l_discussions[discussion_cle].dernier_index;
					}else{
						dernier_index = '';
					};
					if(typeof(date_lu)!='undefined' && l_discussions[discussion_cle].info!='cache'){
						if(ecrire=='oui'){
							l_discussions[discussion_cle].info = 'ecrire';
							$('#discussion_info_'+discussion_cle).html('En train d\'écrire');
							ajax_priorite('haute', 5);
							ajax_priorite('moyenne', 9);
						}else if(l_discussions[discussion_cle].info!='ecrire' 
						&& date_lu != '' 
						&& dernier_index != '' 
						&& l_discussions[discussion_cle].messages[dernier_index].user_cle == user_cle
						){
							l_discussions[discussion_cle].info = 'date';
							
							if(decalage>60){
								$('#discussion_info_'+discussion_cle).html('Vu il y a '+date_lu);
							}else{
								$('#discussion_info_'+discussion_cle).html('Vu');
							};
						}else{
							l_discussions[discussion_cle].info = '';
							$('#discussion_info_'+discussion_cle).html('');
						};
					};
					
					//descend le scroll
					var scroll = $('#discussion_'+discussion_cle+' > .texte').scrollTop();
					var hauteur = $('#discussion_texte_'+discussion_cle).outerHeight(true);
					if((scroll+200) > hauteur){
						$('#discussion_'+discussion_cle+' > .texte').scrollTop($('#discussion_texte_'+discussion_cle).outerHeight(true));
					};
				};
			});
			
			//retire les notifications
			menu_notifs('amis', autreuser_cle, 0);
			
			ajax_priorite('haute', 1);
			
			setTimeout(function(){$(discussion_id).removeClass('init');}, 1);
		}else{
			discussion_afficher(discussion_cle);
		};
	};
}
function discussion_fermer(discussion_cle){
	discussion_id = '#discussion_'+discussion_cle;
	
	clearInterval(l_discussions[discussion_cle].ecrire_boucle);
	delete l_discussions[discussion_cle];
	ajax_supprimer('discussion_activites'+discussion_cle);
	ajax_ajouter('discussion_ouvrir'+discussion_cle, {action:'discussion_fermer', discussion_cle:discussion_cle}, 'un', false, null);
	ajax_priorite('haute', 1);
	
	$(discussion_id).removeClass('active');
	
	setTimeout(function(){
		$(discussion_id).css('margin-right', '-203px');
		$(discussion_id).fadeOut(200);
	}, 1);
	setTimeout(function(){
		$(discussion_id).css('display', 'none');
		$(discussion_id).remove();
	}, 200);
}
function discussion_active(discussion_cle){
	discussion_id = '#discussion_'+discussion_cle;
	
	$('.C_discussion.active').removeClass('active');
	$(discussion_id).addClass('active');
}
function discussion_envoyer(e, discussion_cle, message){
	var discussion_id = '#discussion_'+discussion_cle;
	var discussiontexte_id = '#discussion_texte_'+discussion_cle;
	var nom = 'discussion_envoyer'+discussion_cle;
	
	//si entrer, envoi le message
	if(message.length>0 && e.keyCode==13){
		discussion_messageajouter(discussion_cle, user_cle, user_pseudo, message);
		$(discussion_id+' .champ').val('');
		$(discussion_id+' > .texte').scrollTop($(discussiontexte_id).outerHeight(true));
		
		clearInterval(l_discussions[discussion_cle].ecrire_boucle);
		delete l_discussions[discussion_cle].ecrire_boucle;
		ajax_supprimer('discussion_ecrire'+discussion_cle);
		
		if((typeof(ajax_actions[nom])=='undefined') || (ajax_actions[nom].etat=='envoi')){
			
			//envoi en ajax
			ajax_ajouter('discussion_ecrire'+discussion_cle, {action:'discussion_ecrire', discussion_cle:discussion_cle, ecrire:'non'}, 'un', false, null);
			ajax_ajouter(nom, {action:'message_envoyer', discussion_cle:discussion_cle, contenu:message},'un', false, function(reponse, ajax){
				var discussion_cle = ajax.donnees.discussion_cle;
				l_discussions[discussion_cle].info = '';
			});
			
		}else{
			ajax_actions[nom].donnees.contenu += ('\n'+message);
		};
		
		ajax_priorite('haute', 5);
		ajax_priorite('moyenne', 12);
	};
	
	//sinon, boucle en train d'écrire
	if(message.length>0 && e.keyCode!=13){
		
		//boucle pour vérifier que l'on écrit toujouts
		l_discussions[discussion_cle].ecrire_temps = 5;
		if(typeof(l_discussions[discussion_cle].ecrire_boucle)=='undefined'){
			l_discussions[discussion_cle].ecrire_boucle = setInterval(
				function(){
					if(l_discussions[discussion_cle].ecrire_temps>0){
						l_discussions[discussion_cle].ecrire_temps--;
					}else{
						ajax_ajouter('discussion_ecrire'+discussion_cle, {action:'discussion_ecrire', discussion_cle:discussion_cle, ecrire:'non'}, 'un', false, null);
						ajax_priorite('moyenne', 3);
						clearInterval(l_discussions[discussion_cle].ecrire_boucle);
					};
				}
			, 500);
		};
		
		//envoi en ajax
		ajax_ajouter('discussion_ecrire'+discussion_cle, {action:'discussion_ecrire', discussion_cle:discussion_cle, ecrire:'oui'}, 'un', false, null);
		ajax_priorite('moyenne', 3);
	};
}
function discussion_messageajouter(discussion_cle, autreuser_cle, pseudo, message, message_id){
	var discussion_id = '#discussion_'+discussion_cle;
	var discussiontexte_id = '#discussion_texte_'+discussion_cle;
	
	if(typeof(l_discussions[discussion_cle])!='undefined'){
		if(typeof(l_discussions[discussion_cle].messages)=='undefined'){
			l_discussions[discussion_cle].messages = Array();
		};
		
		//si le message n'existe pas
		var taille = Object.keys(l_discussions[discussion_cle].messages).length;
		if(typeof(message_id) == 'undefined'){var message_id = 'MES' + taille;};
		if(!($('#discussion_message_'+discussion_cle+'_'+message_id).length)){
			//recuperation des indexs
			var dernier_index = l_discussions[discussion_cle].dernier_index;
			var dernier_groupe = l_discussions[discussion_cle].dernier_groupe;
			if(dernier_index == 'undefined'){dernier_index = message_id;};
			if(dernier_groupe == 'undefined'){dernier_groupe = message_id;};
			
			//recuperation des donnees du message
			var date = new Date();
			var datetime = (date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate() + ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds());
			message = message.replace(/\n/g, '<br />');
			
			//ajout au tableau
			l_discussions[discussion_cle].messages[message_id] = {user_cle:autreuser_cle, contenu:message, date:datetime};
			l_discussions[discussion_cle].dernier_index = message_id;
			
			//retire message "vu"
			if(l_discussions[discussion_cle].info=='date'){
				$('#discussion_info_'+discussion_cle).html('');
				l_discussions[discussion_cle].info = 'cache';
			};
			
			//si second message a la suite, ajoute message
			if((taille>0) && (l_discussions[discussion_cle].messages[dernier_index].user_cle == autreuser_cle)){
				$('#discussion_groupe_'+discussion_cle+'_'+dernier_groupe).append('\
					<DIV class="message" id="discussion_message_'+discussion_cle+'_'+message_id+'">'+message+'</DIV>\
				');
			}else{
				//sinon, nouveau bloc avec message dedans
				$(discussiontexte_id).append('\
					<A href="/'+user_langue+'/'+l['P_NOM:UTILISATEUR']+'/'+pseudo+'">\
						<IMG class="image" \
							 info="'+pseudo+';noir;gauche;fixe"\
							 src="http://f.bookino.org/datas/users/'+autreuser_cle+'/profil_45.png"\
						/>\
					</A>\
					<DIV class="ensemble_messages" id="discussion_groupe_'+discussion_cle+'_'+message_id+'">\
						<DIV class="message" id="discussion_message_'+discussion_cle+'_'+message_id+'">'+message+'</DIV>\
					</DIV><BR/>\
				');
				l_discussions[discussion_cle].dernier_groupe = message_id;
			};
			discussion_lu(discussion_cle);
		};
	};
}
function discussion_lu(discussion_cle){
	var discussion_id = '#discussion_'+discussion_cle;
	var discussiontexte_id = '#discussion_texte_'+discussion_cle;
	nouveau_message = false;
	
	//si la discussion est bien affiche
	if(typeof(l_discussions[discussion_cle])!='undefined' && l_discussions[discussion_cle].etat=='ok' && l_discussions[discussion_cle].ouvert=='visible'){
		var scroll = $(discussion_id+' > .texte').scrollTop();
		var taille = $(discussiontexte_id).outerHeight(true);

		//si le message est lu
		if(typeof(l_discussions[discussion_cle].dernier_scroll)=='undefined'){l_discussions[discussion_cle].dernier_scroll=0;};
		if(typeof(l_discussions[discussion_cle].dernier_nbmessage)=='undefined'){l_discussions[discussion_cle].dernier_nbmessage=0;};
		
		dernier_index = l_discussions[discussion_cle].dernier_index;
		
		//s'il y a eu un nouveau message de moi
		if((typeof(l_discussions[discussion_cle].messages) == 'object') 
		&& (Object.keys(l_discussions[discussion_cle].messages).length > l_discussions[discussion_cle].dernier_nbmessage) 
		&& (l_discussions[discussion_cle].messages[dernier_index].user_cle == user_cle)
		){
			nouveau_message = true;
			l_discussions[discussion_cle].dernier_nbmessage = Object.keys(l_discussions[discussion_cle].messages).length;
		};

		//verifi au final si lu ou non
		if((scroll+200>taille && (l_discussions[discussion_cle].dernier_scroll<scroll || nouveau_message)) || taille<170){
			l_discussions[discussion_cle].dernier_scroll = scroll;		

			ajax_ajouter('discussion_lu'+discussion_cle, {action:'discussion_lu', discussion_cle:discussion_cle}, 'un', false, null);
			ajax_priorite('haute', 1);
			ajax_priorite('moyenne', 9);
		};
	};
}

function se_deconnecter(){
	message('deconnexion', l['M:DECONNEXION'], l['M:VOUSDECONNECTER?'], true,
		{'M:OUI': 		[function(){rediriger(url_delapage('DECONNEXION'));}, 'bleu moyen'],
		 'M:ANNULER': 	['_annuler', 'blanc moyen']
		}
	);
}

function voter(element){
	//recupere les objects
	var cle = trouver_cle(element);
	var parent = $('.C_paragraphe[cle='+cle+']');
	var e_vote = parent.find('.C_vote').find('.vote');
	var bouton_moins = parent.find('BUTTON[pour=voter][sens=moins]');
	var bouton_plus = parent.find('BUTTON[pour=voter][sens=plus]');
	
	//recupere les valeurs
	var sens = element.attr('sens');
	
	//desactive les boutons
	bouton_moins.removeClass('rouge').attr('disabled', 'disabled');
	bouton_plus.removeClass('vert').attr('disabled', 'disabled');
			
	//calcul le vote
	var contenu = e_vote.html();
	if(!isNaN(contenu)){
		var vote = parseInt(contenu);
		var dejavote = l_votes[cle];
		if(sens=='plus' && typeof(dejavote)=='undefined'){vote++;};
		if(sens=='plus' && dejavote=='moins'){vote = vote+2;};
		if(sens == 'moins' && typeof(dejavote)=='undefined'){vote--;};
		if(sens == 'moins' && dejavote=='plus'){vote = vote-2;};
		if(vote > 0){vote = '+' + vote;};
		e_vote.html('<DIV class="C_icon chargement"></DIV>');
		
		//envoi la requete
		ajax_ajouter('voter'+cle, {action:'voter', cle:cle, sens:sens}, 'un', false, function(reponse, ajax){
			e_vote.html(vote);
			l_votes[cle] = sens;
			
			if(sens=='plus'){bouton_plus.addClass('vert'); bouton_moins.removeAttr('disabled');};
			if(sens=='moins'){bouton_moins.addClass('rouge'); bouton_plus.removeAttr('disabled');};
		});
		ajax_priorite('haute', 1);
	};
}

function liste_temps(nom, liste, fonction, temps){
	var iliste = [];
	var limite = 0;
	
	for(i in liste){
		iliste[limite] = liste[i];
		iliste[limite]['id'] = i;
		limite++;
	}
	
	if(typeof(listes_temps[nom])=='undefined' || Object.keys(listes_temps[nom]).length==0){
		listes_temps[nom] = iliste;
		liste_temps_boucle(nom, fonction, temps, 0, limite);
	}else{
		listes_temps[nom].concat(iliste);
	};
}
function liste_temps_boucle(nom, fonction, temps, numero, limite){
	//si dans la boucle
	if(numero<limite && typeof(listes_temps[nom])!='undefined'){
		liste = listes_temps[nom];
		
		//fait action
		var donnees = liste[numero];
		if(typeof(fonction) == 'function'){
			var retour = fonction(donnees);
		};
		if(retour == false){temps = 0};
		numero++;
		
		setTimeout(function(){liste_temps_boucle(nom, fonction, temps, numero, limite)}, temps);
	}else{
		delete listes_temps[nom];
	};
}

function init_connecte(){

	//LANCEMENT DES AJAXS
	
	//recuperation des messages
	ajax_ajouter('messages_recuperer', {action:'messages_recuperer'}, 'infini', false, function(reponse, ajax){
		if(reponse){
			messages = reponse.messages;
		
			//parcour messages
			for(var message_id in messages){
				var type = messages[message_id].type;
				var discussion_cle = messages[message_id].cleDiscussion;
				var cle = messages[message_id].cleUtilisateur;
				var pseudo = messages[message_id].pseudo;
				var contenu = messages[message_id].contenu;
				
				//si message dans discussion privee
				if(type=='privee'){
					if((typeof(l_discussions[discussion_cle])!='undefined') && (l_discussions[discussion_cle].etat=='ok')){
						var scroll = $('#discussion_'+discussion_cle+' > .texte').scrollTop();
						var hauteur = $('#discussion_texte_'+discussion_cle).outerHeight(true);
						
						//si discussion ouverte, affiche message
						discussion_messageajouter(discussion_cle, cle, pseudo, contenu, message_id);
						
						//descend le scroll de la discussion
						if((scroll+200) > hauteur){
							$('#discussion_'+discussion_cle+' > .texte').scrollTop($('#discussion_texte_'+discussion_cle).outerHeight(true));
						};
						
						ajax_priorite('haute', 3);
						ajax_priorite('moyenne', 9);
					}else{
						//sinon, notifi
						menu_notifs('amis', cle, '+');
					};
				};
			}
		};
	});
	
	//actualisation des personnes connectés
	ajax_ajouter('membres_recuperer', {action:'membres_recuperer'}, 'infini', false, function(reponse, ajax){
		if(reponse){
			var si_init = ajax.init;
			var amis = reponse.amis;
			var suivis = reponse.suivis;
			var lecteurs = reponse.lecteurs;
			
			//defini variables
			var titre = Array();
			var aide = Array();
			titre[0] = 'Amis';
			titre[1] = 'Membres suivis';
			titre[2] = 'Lecteurs';
			aide[0] = 'Lecteurs respectifs. Vous pouvez vous contacter dans le chat.';
			aide[1] = 'Les membres dont vous êtes lecteur. Ils peuvent vous contacter dans le chat, mais pas l\'inverse.';
			aide[2] = 'Vos lecteurs. Vous pouvez les contacter dans le chat, mais pas l\'inverse.';
			var html = '';
			
			//parcour les 3 catégories
			for(var i=0; i<3; i++){
				if(i==0){tableau = amis;};
				if(i==1){tableau = suivis;};
				if(i==2){tableau = lecteurs;};
				
				if(si_init){
					//prepare titre
					html += '\
						<DIV class="C_grandezone_titre">\
							<DIV class="C_aide I_gauche" info="'+aide[i]+';noir;gauche;fixe">?</DIV>\
							'+titre[i]+'\
						</DIV>\
					';
				};
				
				//parcour les autre utilisateurs
				for(var user_cle in tableau){
					var discussion_cle = tableau[user_cle]['cleDiscussion'];
					var user_pseudo = tableau[user_cle]['pseudo'];
					var user_enligne = tableau[user_cle]['enligne'];
					var user_date = tableau[user_cle]['date_activite'];
					
					var date = langue_date(user_date);
					if(user_enligne=='1' && date.decalage<(5*60)){active='active connecte'; info='connecté'; etat='connecte';}
					else if(user_enligne=='1'){active='absent'; info='inactif depuis '+date.date_langue; etat='absent';}
					else{active = 'desactive'; info = ''; etat='deconnecte';};
					
					l_users[user_cle] = {pseudo:user_pseudo, etat:etat, date_activite:user_date};
					
					if(si_init){
						if(typeof(l_menus['amis']) != 'undefined' 
						&& typeof(l_menus['amis'][user_cle]) != 'undefined' 
						&& l_menus['amis'][user_cle] != 0){
							notif = l_menus['amis'][user_cle];
							notif_active = ' visible';
						}else{
							notif = '';
							notif_active = '';
						};
						
						//ajoute l'user dans la barre
						html += '\
							<DIV class="C_grandezone_liste '+active+'" pour="discussion_ouvrir" cle="'+discussion_cle+'" user_cle="'+user_cle+'" nom="'+user_pseudo+'">\
								<A href="/'+l['P_NOM:UTILISATEUR']+'/'+user_pseudo+'" class="C_user ouvririmage">\
									<IMG class="image" src="http://f.bookino.org/datas/users/'+user_cle+'/profil_45.png"/>\
								</A>\
								<DIV class="texte">'+user_pseudo+'</DIV>\
								<DIV class="icons">\
									<DIV class="icon notif'+notif_active+'" pour="afficher_notif_nombre" visee="amis '+user_cle+'">'+notif+'</DIV>\
									<DIV class="icon etat" pour="afficher_user_etat" info="'+info+';noir;gauche;fixe"></DIV>\
								</DIV>\
							</DIV>\
						';
					};
					
					//met a jour les icons d'etat
					icon = '[pour="afficher_user_etat"][cle="'+user_cle+'"]';
					switch(etat){
						case 'connecte': $(icon).removeClass('absent').addClass('connecte'); break;
						case 'absent': $(icon).removeClass('connecte').addClass('absent'); break;
						case 'deconnecte': $(icon).removeClass('connecte').removeClass('absent'); break;
					};
					$(icon).attr('info', info+';noir;gauche;fixe');
					
					//met a jour les icons de notification
					menu_notifs('amis', user_cle, '=');
				}
			}
			
			if(si_init){$('#menu_amis').html(html).removeClass('init');};
		};
	});
	
	//recuperation des notifications
	ajax_ajouter('notifs_recuperer', {action:'notifs_recuperer'}, 'infini', false, function(reponse, ajax){
		if(reponse){
			var si_init = ajax.init;
			var notifs = reponse.notifs;
			
			liste_temps('notifs_afficher', notifs, function(donnees){
				//recupere valeurs
				var id = donnees.id;
				var type = donnees.type;
				var html = donnees.html;
				var etat = donnees.etat;
				var important = donnees.important;
				var visible = donnees.visible;
				
				l_notifs[id] = donnees;
				
				classes = '';
				if(!si_init){classes += ' init';};
				if(etat != 'vu'){classes += ' active';};
				
				//si menu ouvert sur notifications
				if((menu_ouvert && menu_precedent=='infos') || si_init){
					if(etat!='vu' && si_init){menu_notifs('infos', '+');};
					if(etat!='vu'){ajax_ajouter('notifs_vues', {action:'notifs_vues'}, 'un', false, null);};
					
					$('#menu_infos').prepend('	<DIV class="C_notif dansmenu'+classes+'" id="notif" cle="'+id+'"><DIV class="contenu">\
														' + html + '<DIV class="fermer" pour="notif_supprimer"></DIV>\
												</DIV></DIV>');
				}else{
					$('#notifications').prepend('<DIV class="C_notif horsmenu'+classes+'" id="notif" cle="'+id+'"><DIV class="contenu">\
													' + html + '<DIV class="cacher" pour="notif_cacher"></DIV>\
												</DIV></DIV>');
				};
				
				if(!si_init){
					//enleve "init" pour declencher apparition
					setTimeout(function(){$('.C_notif.init').removeClass('init');}, 1);
					//setTimeout(function(){notif_cacher(id);}, 5000);
				};
				
				//si notif deja vu, n'attend pas
				if(etat=='vu'){return false};
			}, 500);
			
			$('#menu_infos').removeClass('init');
		};
	});
}

//INIT ET EVENEMENTS
$(document).ready(function(){
	si_init = true;
	
	init_connecte();
	
	si_init = false;
	
	
	$(document).on('click', '[pour=discussion_afficher]', function(){discussion_afficher( $(this.parentNode).attr('cle') );});
	$(document).on('click', '[pour=discussion_fermer]', function(){discussion_fermer( $(this.parentNode).attr('cle') );});
	$(document).on('click', '[pour=discussion_ouvrir]', function(){discussion_ouvrir( $(this).attr('cle'), $(this).attr('nom'), $(this).attr('user_cle') );});
	$(document).on('focus', '[pour=discussion_active]', function(){discussion_active( $(this).attr('cle') );});
	$(document).on('mousedown', '[pour=discussion_active]', function(){discussion_active( $(this).attr('cle') );});
	$(document).on('keydown', '[pour=discussion_envoyer]', function(e){discussion_envoyer(e, $(this.parentNode).attr('cle'), $(this).val() );});
	
	$(document).on('click', '[pour=notif_cacher]', function(){notif_cacher( $(this).parents('#notif').attr('cle') );});
	$(document).on('click', '[pour=notif_supprimer]', function(){notif_supprimer( $(this).parents('#notif').attr('cle') );});
	
	$(document).on('click', '[pour=menu_afficher]', function(){menu_afficher( $(this).attr('visee') );});
	
	$(document).on('click', '[pour=voter]', function(e){voter( $(this) );});
});