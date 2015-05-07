	
	//WOME BETA 0.1 client (WOME_B0.1_client)
	//Website Oriented Multilanguage Engine - client file for BETA 0.1
	//Copyright WOME © 2013-2014 Nicolas Coden. All rights reserved

	
	
	
//===== VARIABLES ET INIT =====

//variables partagées principales
var e_wome = {};	//informations générales sur wome et le site
var e_page = {};	//informations sur le rendu du site et de la page
var e_user = {};	//toutes les infos sur l'utilisateur, connecte ou non

//variables necessaires à wome
var zones = [];
var ajax = {};
var l_messages = {};
var l_chronos = {};
var l_forms = {};
var l_infos = {};

var timeout_info = null;
var si_focus = false;
var si_init = true;

var init_pages = {};
var timeout = {};

//===== FONCTIONS =====
//FONCTIONS NECESSAIRES
function nl(mot){
	var mot = mot.toUpperCase();
	return si_defini(l, mot, mot);
}
function getXMLHttpRequest(){
	var xhr = null;
	if(window.XMLHttpRequest || window.ActiveXObject){
		if(window.ActiveXObject){
			try{
				xhr = new ActiveXObject("Msxml2.XMLHTTP");
			}catch(e){
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			}
		}else{
			xhr = new XMLHttpRequest(); 
		};
	}else{
		alert("Votre navigateur est trop ancien. Veuillez intaller Google Chrome pour utiliser correctement Bookino");
		return null;
	};
	return xhr;
}
function url_relative(url_donnee){
	//fourni l'url relative de la page

	var url = url_donnee.split('/');
	var taille = Object.keys(url).length;
	var urlrelative = '';
	for(i=3; i<taille; i++){
		if(url[i] != ''){
			urlrelative +=  '/'+url[i];
		};
	};
	
	return urlrelative;
}
function url_delapage(nom){
	return ('/' + langue + '/' + l['P_NOM:'+nom]);
}
function trouver_cle(e){
	//parcour tous les elements et cherche "cle"
	while(e.parent().length && e.parent().attr('cle') == undefined){
		e = e.parent();
	}
	
	//si trouve, retourne cle
	if(e.parent().length){
		return e.parent().attr('cle');
	}else{
		return false;
	};
}
function lire_attr(element, attr){
	//defini chaine
	var tableau = {};
	var chaine = $(element).attr(attr);
	
	if(chaine){
		return lire_chaine(chaine);
	}else{
		return false;
	};
}
function lire_chaine(chaine){
	//declare regex
	var regex_chainesimple = /^[a-zA-Z0-9]+$/;
	var regex_chaine = /^\'(.*)\'$/;
	var regex_tableau = /^\{(.*)\}$/;
	var regex_morceaux = /([^\;]+)\;?/;
	var regex_indexs = /^([^\:]+)\:(.*)$/;
	
	var chaine = chaine.trim();
	
	if(chaine.match(regex_chainesimple)){
		//si chaine simple
		return chaine.match(regex_chainesimple)[0];
	}else if(chaine.match(regex_chaine)){
		//si chaine
		return chaine.match(regex_chaine)[1];
	}else if(chaine.match(regex_tableau)){
		//si tableau
		var tab_chaine = chaine.match(regex_tableau)[1];
		var morceaux = tab_chaine.split(regex_morceaux);
		var tableau = {};
		var i = 1;
		
		//parcour morceaux
		while(morceaux[i]){
			console.log(morceaux[i]);
			//si avec index
			if(morceaux[i].match(regex_indexs) && !lire_chaine(morceaux[i].match(regex_indexs)[0])){
				var match = morceaux[i].match(regex_indexs);
				var index = match[1];
				var valeur = lire_chaine(match[2]);
				tableau[index] = valeur;
			}else{
			//sinon
				var index = 0;
				while(tableau[index]){
					index++;
				}
				var valeur = lire_chaine(morceaux[i]);
				tableau[index] = valeur;
			};
			i = i + 2;
		}
		return tableau;
	}else{
		//sinon
		return false;
	};
}

// console.log(lire_chaine("chaine"));
// console.log(lire_chaine("{0:tableau}"));
// console.log(lire_chaine("{0:'tableau';1:'tableau';}"));
// console.log(lire_chaine("{'tableau';'tableau';}"));
// console.log(lire_chaine("{A:1;B:2;C:3;D:4}"));
//console.log(lire_chaine("{A:{'a';1;'index':'c'};B:{'index1':a;'index2':'b';'c'};'chaine1';index0:'chaine2'}"));

function si_defini(tableau, index, defaut){
	if(typeof(tableau[index]) != 'undefined'){
		return tableau[index];
	}else{
		return defaut;
	};
}
function element_visible(element){
	//trouve le parent visuel autour d'un champ
	//de formulaire
	
	var nodename = $(element).get(0).nodeName;
	var parent = $(element).parent();
	
	if(nodename != 'LABEL'){
		if(parent.hasClass('C_champ') 
		|| parent.hasClass('C_choix')){
			var e_visible = parent.get(0);
		}else{
			var e_visible = element;
		};
	}else{
		var e_visible = element;
	};
	
	return e_visible;
}

//FONCTIONS BOOKINO
function champ_actions(element, sens, e){
	if(sens=='focus'){si_focus = true;};
	if(sens=='blur'){si_focus = false;};
	
	//si DIV, vise l'element enfant INPUT
	if(element.nodeName == 'DIV'){
		parent = $(element);
		element = $(element).find('INPUT');
	}else{
		parent = $(element);
		element = $(element);
	};
	
	if(typeof(e)!='undefined' && (e.type=='keydown')){
		touche = e.keyCode;
	}else{
		touche = 0;
	};
	
	//recupere propriétés
	defaut = element.attr('defaut');
	motdepasse = element.attr('motdepasse');
	if(typeof(motdepasse)=='undefined'){motdepasse = false;};
	valeur = element.val();
	
	if(parent.attr('fermer') != 'undefined'){
		fermer = true;
	}else{
		fermer = false;
	};
	
	if(typeof(defaut)!='undefined' && defaut!=''){
		//si focus
		if(sens=='focus'){
			if(valeur == defaut){
				element.val('');
				element.css('color', 'rgb(46,46,46)');
				if(motdepasse){element.attr('type', 'password');};
			};
		};
		
		//si blur
		if(sens=='blur'){
			if(valeur == ''){
				element.val(defaut);
				element.css('color', 'rgb(143,143,143)');
				if(motdepasse){element.attr('type', 'text');};
			};
		};
		
		//si keydown
		if(sens=='keydown' && touche>32 && touche<127){
			if(valeur == defaut){
				element.val(String.fromCharCode(touche));
				element.css('color', 'rgb(46,46,46)');
				if(motdepasse){element.attr('type', 'password');};
			};
			
			if(fermer){
				parent.addClass('fermer');
			};
		};
	};
}
function champ_fermer(element, verifier){
	//selectionne l'element INPUT
	if(element.nodeName == 'DIV'){
		parent = $(element);
		champ = $(element).find('INPUT');
	}else{
		parent = $(element);
		champ =	$(element);
	};
	if(typeof(verifier)=='undefined'){
		verifier = false;
	};
	
	if(champ.val()=='' || !verifier){
		//retire le 'X'
		parent.removeClass('fermer');
		
		//vide le champ
		champ.focus();
		champ.val('');
		
		if(parent.is('.recherche_champ')){
			recherche_fermer();
		};
	};
}

function raccourcis(e){
	touche = e.keyCode;
	
	//si touche alphanumérique et aucun element n'a le focus
	if(touche>32 && touche<127 && !si_focus){
		s_touche = String.fromCharCode(touche);
		
		if(s_touche=='s' || s_touche=='S'){$('#recherche_champ').select();};
		
		if(e_user.connecte){
			if(s_touche=='a' || s_touche=='A'){menu_afficher('amis');};
			if(s_touche=='n' || s_touche=='N'){menu_afficher('infos');};
			if(s_touche=='m' || s_touche=='M'){menu_afficher('general');};
		};
		
		return false;
	};
}

function zone_montrer(id_zone, niveau){
	//defini zone precedente
	if(typeof(zones[id_zone])=='undefined'){
		zones[id_zone] = 0;
		
		$('.C_zone').css({'-webkit-transition':'none', 'transition':'none'});
		
		zhauteur = $('#zone_'+id_zone+'_0').height();
		$('#zone_'+id_zone).css('height', zhauteur);

		setTimeout(function(){$('.C_zone').css({'-webkit-transition':'height 500ms', 'transition':'height 500ms'});},1);
	};
	
	setTimeout(function(){
		niveau_precedent = zones[id_zone];

		//si la zone est sur un niveau différent
		if(zones[id_zone]!=niveau){
			zones[id_zone]=niveau;
			
			hauteur = $('#zone_'+id_zone+'_'+niveau).height();
			
			//applique les propriétés
			$('#zone_'+id_zone).css('overflow', 'hidden');
			$('#zone_'+id_zone+'_'+niveau).css('display', 'block');
			
			$('#zone_'+id_zone).css('height', hauteur);
			$('#zone_'+id_zone+'_'+niveau_precedent).removeClass('visible');
			$('#zone_'+id_zone+'_'+niveau).addClass('visible');
			
			setTimeout(function(){
				$('#zone_'+id_zone).css('overflow', 'visible');
				$('#zone_'+id_zone+'_'+niveau_precedent).css('display', 'none');
			},500);
		};
	},2);
	
	return false;
}

function recherche_lancer(recherche, defaut){
	taille = recherche.length;
	
	$('#recherche_personnes > .contenu > .nombre').html('').addClass('verif');
	$('#recherche_autrepersonnes > .contenu > .nombre').html('').addClass('verif');
	
	if(taille>0 && recherche!=defaut){
		ajax_ajouter('rechercher', {action:'rechercher', recherche:recherche}, 'un', false, function(reponse, ajax){
			connaissances = reponse.connaissances;
			autresusers = reponse.autresusers;
			connaissances_html = '';
			autresusers_html = '';
			
			for(var i=0; i<connaissances.length; i++){
				connaissances_html += '\
				<DIV class="C_liste_flottant">\
					<A href="/'+l['P_NOM:UTILISATEUR']+'/'+connaissances[i][0]+'" class="C_user">\
						<IMG class="image" src="http://f.bookino.org/datas/users/'+connaissances[i][1]+'/profil_45.png"/>\
						<DIV class="nom">'+connaissances[i][0]+'</DIV>\
					</A>\
				</DIV>\
				';
			}
			for(var i=0; i<autresusers.length; i++){
				autresusers_html += '\
				<DIV class="C_liste_flottant">\
					<A href="/'+l['P_NOM:UTILISATEUR']+'/'+autresusers[i][0]+'" class="C_user">\
						<IMG class="image" src="http://f.bookino.org/datas/users/'+autresusers[i][1]+'/profil_45.png"/>\
						<DIV class="nom">'+autresusers[i][0]+'</DIV>\
					</A>\
				</DIV>\
				';
			}
			
			if(connaissances_html!=''){
				$('#recherche_personnes').addClass('trouve');
			}else{
				$('#recherche_personnes').removeClass('trouve');
			};
			
			if(autresusers_html!=''){
				$('#recherche_autrepersonnes').addClass('trouve');
			}else{
				$('#recherche_autrepersonnes').removeClass('trouve');
			};
			
			$('#recherche_personnes > .contenu > .nombre').html('('+connaissances.length+')').removeClass('verif');
			$('#recherche_autrepersonnes > .contenu > .nombre').html('('+autresusers.length+')').removeClass('verif');
			$('#recherche_personnes > .contenu > .texte').html(connaissances_html);
			$('#recherche_autrepersonnes > .contenu > .texte').html(autresusers_html);
		});
			
		ajax_priorite('haute', 1);
		
		$('.POS_Bookino > .menu').removeClass('liens').addClass('recherche');
	}else{
		recherche_fermer();
	};
}
function recherche_fermer(){
	$('.POS_Bookino > .menu').removeClass('recherche').addClass('liens');
	$('#recherche_personnes').removeClass('trouve');
	$('#recherche_autrepersonnes').removeClass('trouve');
	$('#recherche_personnes > .contenu > .texte').html('');
	$('#recherche_autrepersonnes > .contenu > .texte').html('');
}

function ajax_priorite(niveau, temps){
	if(niveau=='haute'){
		if(temps > ajax.priorites.haute){
			ajax.priorites.haute=temps;
		};
	};
	if(niveau=='moyenne'){
		if(temps > ajax.priorites.moyenne){
			ajax.priorites.moyenne=temps;
		};
	};
}
function ajax_init(){
	ajax = {etat:'attend', actions:{}, temps:0, priorites:{haute:0, moyenne:0}};
	
	setInterval(function(){
		//decremente priorités
		priorite = 'basse';
		if(ajax.priorites.haute > 0){
			ajax.priorites.haute--; 
			priorite='haute';
		}else if(ajax.priorites.moyenne > 0){
			ajax.priorites.moyenne--; 
			priorite='moyenne';
		};
		
		//lance ajax
		ajax.temps++;
		if(priorite=='haute' && ajax.temps>=1){ajax_exec(); ajax.temps=0;};
		if(priorite=='moyenne' && ajax.temps>=3){ajax_exec(); ajax.temps=0;};
		if(priorite=='basse' && ajax.temps>=10){ajax_exec(); ajax.temps=0;};
	}, 1000);
	
	setTimeout(function(){
		ajax_exec();
	}, 1);
}
function ajax_ajouter(nom, donnees, nbfois, binaire, action_fin, type){
	if(binaire && typeof(ajax.actions[nom])=='object'){
		//si action inverse, annule
		console.log('AJAX annulé');
		ajax_supprimer(nom);
	}else{
		//sinon ajoute à la liste
		if(type == undefined){type = 'script';};
		if(donnees != null){
			donnees['_init'] = si_init;
			ajax.actions[nom] = {type:type, donnees:donnees, nbfois:nbfois, action_fin:action_fin, etat:'attente'};
		};
	};
}
function ajax_supprimer(nom){
	if(typeof(ajax.actions[nom]) == 'object'){
		delete ajax.actions[nom];
	};
}
function ajax_exec(){
	taille = Object.keys(ajax.actions).length;
	//si quelque chose à envoyer
	
	if(taille>0 && ajax.etat=='attend'){
		ajax.etat = 'envoi';
		var envoye = {scripts:{}, forms:{}, donnees:{
			'url_envoi': e_page.url,
		}};
		
		//recupere les donnees à envoyer
		for(var nom in ajax.actions){
			if(ajax.actions[nom].type == 'form'){
				groupe = 'forms';
			}else{
				groupe = 'scripts';
			};
			
			ajax.actions[nom].etat = 'envoi';
			envoye[groupe][nom] = ajax.actions[nom].donnees;
		}
		
		url = '/'+e_page.langue;
		
		//envoi en ajax		
		$.ajax($.extend(true,{
			url: url,
			type: 'POST',
			port: 'ajax',
			dataType: 'json',
			data: envoye,
			beforeSend:function(jqXHR, settings){
				this.actions = ajax.actions;
				this.donnees = envoye.donnees;
			},
			success:function(reponses, textStatus, jqXHR){
				ajax.etat = 'analyse';
				var actions = this.actions;
				var donnees = this.donnees;
				console.log('AJAX reponse: ' + Object.keys(reponses).length + ' données');
				
				for(var nom in actions){
					//prepare donnes pour la reponse
					var type = actions[nom].type;
					var envoye = actions[nom].donnees;
					var najax = {
						init: envoye._init,
						envoye: envoye,
						donnees: donnees,
						type: type,
					};
					var action_fin = actions[nom].action_fin;
					var nombre_fois = actions[nom].nbfois;
					var reponse = reponses[nom];
										
					if(type == 'form'){
						//action pour les formulaires
						form_id = nom;
						//affiche "valide" sur le bouton
						$('#'+form_id+' BUTTON[type=submit]').removeClass('verif').removeClass('serveur').addClass('valide');
						setTimeout(function(form_id){
							var nom = form_id;
						
							//fait action de fin et supprime si une fois
							if(action_fin != null){action_fin(reponse, najax);};
							if(typeof(ajax.actions[nom]) != 'undefined'){
								ajax.actions[nom].donnees._init = false;
							};
							if(nombre_fois == 'un'){ajax_supprimer(nom);};
							
							//retabli le formulaire
							setTimeout(function(form_id){
								$('#'+form_id+' BUTTON, #'+form_id+' INPUT, #'+form_id+' SELECT, #'+form_id+' TEXTAREA').attr('disabled', false);
								$('#'+form_id+' BUTTON[type=submit]').removeClass('valide');
							}, 500, form_id);
						}, 500, form_id);
					}else{
						//action pour les scripts
						if(typeof(actions[nom])!='undefined'){
							if(action_fin != null){action_fin(reponse, najax);};
							if(typeof(ajax.actions[nom] != 'undefined')){
								ajax.actions[nom].donnees._init = false;
							};
							if(nombre_fois == 'un'){ajax_supprimer(nom);};
						};
					};
				}
				
				//execution du js transmis
				if(typeof(reponses.js) != 'undefined'){
					action = new Function(reponses.js);
					action();
				};
				
				ajax.etat = 'attend';
			},
			error: function(jqXHR, textStatus, errorThrown){
				ajax.etat = 'analyse';
				var actions = this.actions;
				var donnees = this.donnees;
				
				console.log('AJAX erreur');
				console.log(textStatus, errorThrown);
				
				for(var nom in actions){
					var type = actions[nom].type;
					
					//si formulaire, action d'erreur
					if(type == 'form'){
						form_id = nom;
						setTimeout(function(){
							$('#'+form_id+' BUTTON[type=submit]').removeClass('verif').removeClass('valide').addClass('serveur').attr('disabled', false)
						}, 4000);
					};
					if(typeof(ajax.actions[nom] != 'undefined')){
						ajax.actions[nom].donnees._init = false;
					};
				}
				ajax.etat = 'attend';
			},
			xhrFields: {
				onprogress: function(e){
					if(e.lengthComputable){
						$('.POS_Bookino .chargement').css('width', (50 + e.loaded/e.total*50 + '%'));
					};
				}
			}
		}, url));
	};
}
function ajax_form(form, datas, action_fin){
	//calcul des propriétés
	var form_id = form.id;
	donnees={};

	if(typeof(datas)=='object'){
		for(var nom in datas){
			nom=nom.trim();
			if(datas[nom] == '_value'){
				donnees[nom] = $('*[name="'+nom+'"]').val();
			}else{
				donnees[nom] = datas[nom];
			};
		}
	};
	donnees['ajax_type'] = 'formulaire';
	
	//affichage chargement
	$('#'+form_id+' BUTTON[type=submit]').removeClass('serveur').removeClass('valide').addClass('verif');
	$('#'+form_id+' BUTTON, #'+form_id+' INPUT, #'+form_id+' SELECT, #'+form_id+' TEXTAREA').attr('disabled', true);
	//envoi ajax
	ajax_ajouter(form_id, donnees, action_fin, 'un', false);
	ajax_priorite('haute', 1);
}

function langue_date(date_donnee){

	if(date_donnee!='' && date_donnee!='undefined' && typeof(date_donnee)!='undefined'){
		
		//defini les variables dates
		var date = new Date(date_donnee['year'],
							date_donnee['month']-1,
							date_donnee['day'],
							date_donnee['hour'],
							date_donnee['minute'],
							date_donnee['second']);

		var date_now = new Date();
		var decalage = (date_now.getTime() - date.getTime());
		
		//calcul le decalage et la date finale
		decalage = decalage/1000;
		var annees = Math.floor(decalage/(	3600*24*365));
		var reste = decalage - annees*		3600*24*365;
		var mois = Math.floor((reste)/(		3600*24*30.41));
		reste = reste - mois*				3600*24*30.41;
		var jours = Math.floor((reste)/(	3600*24));
		reste = reste - jours*				3600*24;
		var heures = Math.floor((reste)/(	3600));
		reste = reste - heures*				3600;
		var minutes = Math.floor((reste)/(	60));
		reste = reste - minutes*			60;
		var secondes = Math.floor(reste);
				
		//construit la phrase
		if(decalage < 60){date = ''+secondes+' secondes';}
		else if(decalage < 60*2){date = '1 minute';}
		else if(decalage < 3600){date = minutes+' minutes';}
		else if(decalage < 3600*2){date = '1 heure';}
		else if(decalage < 3600*24){date = heures+' heures';}
		else if(decalage < 3600*24*2){date = '1 jour';}
		else if(decalage < 3600*24*7){date = jours+' jours';}
		else if(decalage < 3600*24*7*2){date = '1 semaine';}
		else if(decalage < 3600*24*31){date = jours/4+' semaines';}
		else if(decalage < 3600*24*31*2){date = '1 mois';}
		else if(decalage < 3600*24*365){date = mois+' mois';}
		else if(decalage < 3600*24*365*2){date = '1 an';}
		else{date = annees+' ans';};
	}else{
		date = '';
		decalage = 0;
	};
		
	return {date_langue:date, decalage:decalage};
}

function page(url){
	if(url.length>4 && url.substr(0,4)=='http'){
		//si lien externe, enregistre
		
		return true;
	}else{
		//si lien interne, change la page
		if(typeof(ajax.actions['page']) == 'undefined'){
			//si nom de page, construit url
			if(url.charAt(0)!='/'){
				url = url_delapage(url);
			};
			
			//sauvegarde la page actuelle
			page_actualiser();
			
			//barre de chargement
			$('.POS_Bookino .chargement').addClass('visible').css('width', '50%');
			$('BODY').addClass('verif');
			
			//actions de fin des pages
			for(init_nom in init_pages){
				if(init_pages[init_nom].active
				&& init_pages[init_nom].fin != null){
					init_pages[init_nom].fin();
				};
			}
			
			//supprime les actions ajax non constantes
			for(var nom in ajax.actions){
				if(ajax.actions[nom].nbfois == 'page'){
					ajax_supprimer(nom);
				};
			}
			
			//requete ajax
			ajax_ajouter('page', {action:'page', url:url}, 'un', false, function(reponse, ajax){
				if(reponse && reponse.page){
					var titre = reponse.titre;
					var entete = reponse.entete;
					var contenu = reponse.contenu;
					var adresse = reponse.url;
					var relative = reponse.relative;
					var logs = reponse.logs;
											
					recherche_fermer();
					info_toutcacher();
					
					//retire les balises inexistantes
					$('HEAD>SCRIPT, HEAD>LINK').each(function(){
						if((this.id).length>5 
						&& (this.id).substring(0,5)=='head_'){
							balise_nom = (this.id).substring(5);
							if(typeof(entete[balise_nom]) == 'undefined'){
								$(this).remove();
								if(typeof(init_pages[balise_nom]) != 'undefined'){
									init_pages[balise_nom].active = false;
								};
							};
						};
					});
					
					//rajoute les balises manquantes et actualise les autres
					for(balise_nom in entete){
						if($('[id="head_'+balise_nom+'"]').length == 0){
							$('#head_js').before(entete[balise_nom]);
						};
					}
					$('#head_js').replaceWith(entete['js']);
					
					//actions de debut des pages
					for(init_nom in init_pages){
						if(init_pages[init_nom].active 
						&& init_pages[init_nom].debut != null){
							init_pages[init_nom].debut();
						};
					}
					
					document.title = titre;
					$('.POS_body').html(contenu);
					$('.logs').html(logs);
					
					//ajoute à l'historique					
					if(window.history.state==null || window.history.state.adresse!=adresse){
						window.history.pushState({'titre':titre, 'entete':entete, 'contenu':contenu, 'adresse':adresse}, titre, adresse);
					}else if(window.history.state!=null && window.history.state.adresse==adresse){
						window.history.state.titre = titre;
						window.history.state.entete = entete;
						window.history.state.contenu = contenu;
						window.history.state.adresse = adresse;
					};
					
					//modifi les liens de la langue
					$('.U_langue').each(function(){
						$(this).attr('href', 'http://bookino.org/' + $(this).attr('langue') + '/' + relative + '?language=' + langue);
					});
					
					//barre de chargement
					$('BODY').removeClass('verif');
					window.scrollTo(0, 0);
					$('.POS_Bookino .chargement').css('width', '100%');
					setTimeout(function(){
						$('.POS_Bookino .chargement').removeClass('visible');
						$('.POS_Bookino .chargement').css('width', '0');
					}, 100);
				};
			});
			ajax_exec();
		};
		
		return false;
	};
}
function rediriger(url){
	window.location = url;
}
function page_actualiser(){
	var adresse = e_page.url;
	
	window.history.replaceState({
		'titre': document.title, 
		'entete': $('HEAD').html(), 
		'contenu': $('.POS_body').html(), 
		'adresse': adresse, 
	}, document.title, adresse);
}

function form_ajouter(form_nom, actualiser, champs, action_fin){
	l_forms[form_nom] = {champs:champs, actualiser:actualiser, action_fin:action_fin};
}
function form_verifier(form_nom, champ_nom, evenement){
	//si le formulaire existe
	if(typeof(l_forms[form_nom]) != 'undefined'){
		var form_valide = true;
		var regles = {
			'obligatoire':	[function(e_champ, valeur, value){return (value=='' || value==undefined 
																	|| (typeof(e_champ.proprietes)!='undefined' 
																	 && typeof(e_champ.proprietes.defaut)!='undefined' 
																	 && e_champ.proprietes.defaut==value));},		['change'], []],
			'taille_min':	[function(e_champ, valeur, value){return (value.length < valeur);}, 			['change'], ['keyup']],
			'taille_max':	[function(e_champ, valeur, value){return (value.length > valeur);}, 			['change'], ['keyup']],
		};
		
		//declare si non donné
		if(typeof(champ_nom)!='undefined' && champ_nom && typeof(l_forms[form_nom].champs[champ_nom])!='undefined'){
			var champs = {};
			champs[champ_nom] = l_forms[form_nom].champs[champ_nom];
		}else{
			var champs = l_forms[form_nom].champs;
		};
		if(typeof(evenement) == 'undefined'){var evenement = false;};
		
		//verifi champs suivant evenement
		for(var nom_champ in champs){
			var nom = '[name="form_'+form_nom+'['+nom_champ+']"]';
			var necessaire = si_defini(champs[nom_champ], 'necessaire', []);
			var jq_champ = $(nom);
			var e_champ = l_forms[form_nom].champs[nom_champ];
			
			//verifi si bouton radio
			if(jq_champ.get(0).nodeName=='INPUT'
			&& jq_champ.attr('type')=='radio'){
				var value = $(nom+':checked').val();
			}else{
				var value = jq_champ.val();
			};
			var erreur = false;
			var erreur_valeur = false;
			
			//parcour toutes les regles
			for(var regle in regles){
				if(typeof(necessaire[regle]) != 'undefined'){
					var valeur = necessaire[regle];
					var fonction = regles[regle][0];
					var evenements1 = regles[regle][1];
					var evenements2 = regles[regle][2];
					
					//s'il y a une erreur, et que l'evenement principale ou secondaire l'autorise
					if(fonction(e_champ, valeur, value)																					//si erreur
					&& ((evenement=='*' || evenements1=='*' || evenements1.indexOf(evenement)>=0)								//evenement principal
					 || ((evenements2=='*' || evenements2.indexOf(evenement)>=0) && (typeof(e_champ.erreur)!='undefined')))){	//evenement secondaire
							form_valide = false;
							erreur = regle;
							erreur_valeur = valeur;
							break;
					};
				};
			}
			form_erreur(form_nom, nom_champ, erreur, erreur_valeur);		
		}
		
		return form_valide;
	}else{
		return false;
	};
}
function form_erreur(form_nom, champ_nom, raison, valeur){
	//determine champ et element HTML de l'info
	var nom = '[name="form_'+form_nom+'['+champ_nom+']"]';
	var champ = l_forms[form_nom].champs[champ_nom];
	var e_visible = element_visible(nom);
	
	//si erreur
	if(raison){
		if(typeof(champ.erreur) != 'undefined'
		&& champ.erreur != raison
		&& champ.erreur != false){
			info_cacher(e_visible, true);
		};
		
		//construit phrase
		var phrase = nl('\'T_'+form_nom+'_'+champ_nom+':'+raison+'\'');
		phrase = phrase.replace('&'+raison+'&', valeur);

		//si erreur, affiche
		$(e_visible).attr('info', 'texte:'+phrase+';couleur:blanc;direction:droite;visible:visible')
				.addClass('accept_icon accept_border erreur');
		info_afficher(e_visible);
		l_forms[form_nom].champs[champ_nom].erreur = raison;
	}else{
		//si pas d'erreur, retire
		info_cacher(e_visible, true);
		$(e_visible).attr('info', '').removeClass('erreur');
		l_forms[form_nom].champs[champ_nom].erreur = false;
	};
}
function form_envoyer(form_nom){
	//fonction form : ne fait que changer de page (ou actualiser) en
	//				  recuperant les données du formulaire & fait la
	//				  mise en forme.
	
	//verifie validité
	
	var form_id = '#form_'+form_nom;
	var valide = form_verifier(form_nom, false, '*');
	
	if(valide){
		var url = $(form_id).attr('action');
		var externe = (url.length>4 && url.substring(0,4)=='http');
		
		$(form_id+' BUTTON[type=submit]').addClass('verif');
		
		var form = l_forms[form_nom];
		if(typeof(form) == 'undefined' 
		|| (url && externe) 
		|| form.actualiser){
			//si lien externe
			return true;
		}else{
			//si lien interne ou sans redirection
			$(form_id+' INPUT, '+form_id+' TEXTAREA, '+form_id+' SELECT, '+form_id+' BUTTON').attr('disabled', 'disabled');
			
			//recupere les valeurs
			var donnees = {};
			$(form_id+' INPUT, '+form_id+' TEXTAREA, '+form_id+' SELECT').each(function(){
				var champ_nom = this.name.match(/\[(\w+)]/);
				if(champ_nom){
					//verifi si bouton radio
					if(this.nodeName=='INPUT'
					&& $(this).attr('type')=='radio'){
						var value = $('[name="'+this.name+'"]:checked').val();
					}else{
						var value = $(this).val();
					};
					
					donnees[champ_nom[1]] = value;
				};
			});
			
			//ajoute ajax
			if(!externe){
				ajax_ajouter('form_'+form_nom, donnees, 'un', false, form.action_fin, 'form')
				if(url){
					page(url);
				};
			};
			
			return false;
		};
	}else{
		return false;
	};
}
function captcha_actualiser(nom){
	$('#captcha_'+nom).attr('src', '/captcha');
}

function message(nom, titre, texte, cache, boutons){
	
	//prepare le contenu
	$('.POS_message .titre').html(titre);
	$('.POS_message .texte').html(texte);
	l_messages[nom] = {};
	l_messages[nom]['_cache'] = cache;
	
	//prepare les boutons
	html_boutons = '';
	for(bnom in boutons){
		html_action = boutons[bnom][0];
		html_class = boutons[bnom][1];
		if(typeof(l[bnom]) != 'undefined'){
			bnom = l[bnom];
		};
		
		l_messages[nom][bnom] = html_action;
		html_boutons += '<DIV class="C_bouton ' + html_class + '" onclick="message_action(\'' + nom + '\', \'' + bnom + '\')">' + bnom + '</DIV>';
	}
	$('.POS_message .boutons').html(html_boutons);
	
	//affiche le message
	$('.POS_message').css('display', 'block');
	if(cache){$('.POS_cache').css('display', 'block');};
	setTimeout(function(){
		$('.POS_message').css('opacity', '1');
		if(cache){$('.POS_cache').css('opacity', '1');};
	}, 1);
}
function message_fermer(cache){
	$('.POS_message').css('opacity', '0');
	if(cache){$('.POS_cache').css('opacity', '0');};
	
	setTimeout(function(){
		$('.POS_message').css('display', 'none');
		if(cache){$('.POS_cache').css('display', 'none');};
	}, 100);
}
function message_action(nom, bnom){
	//si message existe
	if(typeof(l_messages[nom]) != 'undefined' 
	&& typeof(l_messages[nom][bnom]) != 'undefined'){
		var action = l_messages[nom][bnom];
		var cache = l_messages[nom]['_cache'];
		
		//si fonction, execute
		if(typeof(action) == 'function'){
			action();
			message_fermer(cache);
		};

		//sinon, gere les cas particuliers
		if(typeof(action) == 'string'){
			if(action == '_annuler'){
				message_fermer(cache);
			};
		};
		
		delete l_messages[nom];
	};
}

function info_afficher(element){
	//recupere les infos
	e_visible = element_visible(element);
	donnees = lire_attr(e_visible, 'info');
	
	if(typeof(donnees) == 'string'){
		var nom = donnees.replace(/ /g,"");
		var titre = '';
		var texte = donnees;
		var couleur = 'noir';
		var direction = 'haut';
		var fixe = 'nonfixe';
		var visible = 'auto';
	}else{
		var titre = si_defini(donnees, 'titre', '');
		var texte = si_defini(donnees, 'texte', '');
		var couleur = si_defini(donnees, 'couleur', 'noir');
		var direction = si_defini(donnees, 'direction', 'haut');
		var fixe = si_defini(donnees, 'fixe', 'nonfixe');
		var visible = si_defini(donnees, 'visible', 'auto');
		var nom = si_defini(donnees, 'nom', texte).replace(/ /g,"");
	};
	
	if(nom!='' && texte!=''){
		//recupere les tailles et positions
		var position = $(e_visible).offset();
		var largeur = $(e_visible).outerWidth();
		var hauteur = $(e_visible).outerHeight();	
		var x_fenetre = $(document).scrollLeft();
		var y_fenetre = $(document).scrollTop();
		
		//prepare la nouvelle bulle
		var nom_e = '#info_'+nom;
		
		if(typeof(l_infos[nom]) == 'undefined'){
			if($(nom_e).length == 0){
				$('BODY').append('<DIV class="POS_info" id="info_'+nom+'"><DIV class="contenu"></DIV></DIV>');
				l_infos[nom] = {titre:titre, texte:texte, couleur:couleur, direction:direction, fixe:fixe, visible:visible}
			};
		};
		
		//calcul le contenu et ecrit
		var html = '';
		if(titre != ''){html += '<DIV class="titre">'+titre+'</DIV>';};
		html += texte;
		$(nom_e+' > .contenu').html(html);
		
		//place l'info
		var largeur_info = $(nom_e).outerWidth();
		var hauteur_info = $(nom_e).outerHeight();
		var decalage = 10;
		
		//emplacements de base (centre)
		var x = position.left + largeur/2 - largeur_info/2;
		var y = position.top + hauteur/2 - hauteur_info/2;
		
		//puis suivant haut/bas/gauche/droite
		if(direction == 'haut'){y = position.top - hauteur_info - decalage;};
		if(direction == 'bas'){y = position.top + hauteur + decalage;};
		if(direction == 'gauche'){x = position.left - largeur_info - decalage;};
		if(direction == 'droite'){x = position.left + largeur + decalage;};
		
		if(fixe == 'fixe'){
			var position_css = 'fixed';
			x -= x_fenetre;
			y -= y_fenetre;
		}else{
			var position_css = 'absolute';
		};
		
		//ajoute les class
		$(nom_e+' > .contenu').removeClass('blanc noir haut bas gauche droite')
								 .addClass(couleur).addClass(direction);
		//empeche l'info de disparaitre
		clearTimeout(timeout['info_'+nom]);
		
		//affiche
		$(nom_e).css({'position':position_css, 'top':y, 'left':x});
		
		if(visible=='visible' || visible=='auto'){
			$(nom_e).css({'display':'block'});
			setTimeout(function(){
				$(nom_e).css('opacity', '1');
			}, 1);
		};
	};
}
function info_cacher(element, forcer){
	//recupere nom
	e_visible = element_visible(element);
	donnees = lire_attr(element, 'info');
	if(typeof(forcer) == 'undefined'){var forcer = false;};
	
	if(typeof(donnees) == 'string'){
		var nom = donnees.replace(/ /g,"");
	}else{
		var texte = si_defini(donnees, 'texte', '');
		var nom = si_defini(donnees, 'nom', texte).replace(/ /g,"");
	};
	
	//si info existe et non bloqué
	if(typeof(l_infos[nom]) != 'undefined'
	&& (l_infos[nom].visible=='auto'
	 || l_infos[nom].visible=='cache'
	 || forcer)){
		var info_nom = '#info_'+nom;
		
		//cache
		$(info_nom).css('opacity', '0');
		
		//supprime
		timeout['info_'+nom] = setTimeout(function(){
			$(info_nom).remove();
			delete l_infos[nom];
		}, 100, info_nom, nom);
	};
}
function info_toutcacher(){
	for(var nom in l_infos){
		var info_nom = '#info_'+nom;
		
		//cache
		$(info_nom).css('opacity', '0');
		
		//supprime
		timeout['info_'+nom] = setTimeout(function(){
			$(info_nom).remove();
			delete l_infos[nom];
		}, 100, info_nom, nom);
	}
}

function chrono_ajouter(element){
	
	if($(element).length){
		//tri infos
		var infos = $(element).attr('chrono');
		var infos = infos.split(';');
		var actions = [];
		
		if(infos[5] == 'null'){
			var action = 'null';
		}else{
			var temp = infos[5].match(/^\{(.*)\}$/);
			temp = temp[1].split(',');
			
			var taille = temp.length;
			
			for(var i=0; i<taille; i++){
				var split = temp[i].split(':');
				var numero = split[0];
				var action = split[1];
				actions[numero] = new Function(action);
			}
		};
		
		var taille = Object.keys(l_chronos).length;
		//ajoute dans tableau
		l_chronos[infos[0]] = {
			element: element,
			temps: infos[1],
			limite: infos[2],
			sens: infos[3],
			afficher: infos[4],
			actions: actions,
			etat: 'play',
		};
	};
}
function chrono_supprimer(nom){
	delete l_chronos[nom];
}
function chrono_pause(nom){
	//cherche l'index
	if(typeof(l_chronos[nom]) == 'object'){
		l_chronos[nom].etat = 'pause';
	};
}
function chrono_play(nom){
	//cherche l'index
	if(typeof(l_chronos[nom]) == 'object'){
		l_chronos[nom].etat = 'play';
	};
}
function chrono_temps(nom, temps){
	//cherche l'index
	if(typeof(l_chronos[nom]) == 'object'){
		l_chronos[nom].temps = temps;
		$(l_chronos[nom].element).html(temps);
	};
}

function init_ajouter(adresse, action_debut, action_fin){
	init_pages[adresse] = {
		active: true,
		debut: action_debut, 
		fin: action_fin
	};
}
function init(){
	
	//SAUVEGARDE LA PAGE
	page_actualiser();
	
	//RECUPERE LES CHRONOS
	$("[chrono]").each(function(){
		//recupere infos
		chrono_ajouter(this);
	});
	
	//LANCE BOUCLE DE CHRONOS
	setInterval(function(){
		for(i in l_chronos){
			var element = l_chronos[i].element;
			var temps = l_chronos[i].temps;
			var limite = l_chronos[i].limite;
			var sens = l_chronos[i].sens;
			var afficher = l_chronos[i].afficher;
			var actions = l_chronos[i].actions;

			//fait action
			if(typeof(actions[temps])=='function'){actions[temps]();};
			
			var etat = l_chronos[i].etat;
			if(etat == 'play'){
			
				//augmente ou diminu chrono
				if(sens=='+'){l_chronos[i].temps++; temps++;};
				if(sens=='-' && temps>0){l_chronos[i].temps--; temps--;};
				
				//affiche si necessaire
				if(afficher){
					$(element).html(temps);
				};
				
				//si fin, oubli chrono
				if(temps == limite){
					if(typeof(actions[temps])=='function'){actions[temps]();};
					delete(l_chronos[i]);
				};
			}
		}
	}, 1000);
	
	//LANCE ACTIONS DES PAGES
	for(nom in init_pages){
		init_pages[nom].debut();
	}
}


//INIT ET EVENEMENTS
$(document).ready(function(){
	si_init = true;
	
	init();
	ajax_init();
	
	si_init = false;
	
	//gere les evenements
	$(document).on('keyup', '#recherche_champ', function(){recherche_lancer( $(this).val(), $(this).attr('defaut') );});
	$(document).on('focus', '#recherche_champ', function(){recherche_lancer( $(this).val(), $(this).attr('defaut') );});
	
	$(document).on('focus', '.C_champ, .C_champ.champ', function(e){champ_actions(this, 'focus', e);});
	$(document).on('blur', '.C_champ, .C_champ.champ', function(e){champ_actions(this, 'blur', e);});
	$(document).on('keydown', '.C_champ, .C_champ.champ', function(e){champ_actions(this, 'keydown', e);});
	$(document).on('keyup', '.C_champ, .C_champ.champ', function(e){champ_fermer(this, true);});
	$(document).on('change', '.C_champ, .C_champ.champ', function(e){champ_fermer(this, true);});
		
	$(document).on('click', '[pour=zone_montrer]', function(){zone_montrer( $(this).attr('zone'), $(this).attr('niveau') );});
	$(document).on('click', '[pour=champ_fermer]', function(){champ_fermer( this.parentNode );});
	$(document).on('click', 'A', function(e){return page( $(this).attr('href') );});
	
	$(document).on('mouseenter', '[info]', function(){info_afficher(this);});
	$(document).on('mouseleave', '[info]', function(){info_cacher(this);});
		
	//recupere toutes les touches pour les raccourcis
	$(document).keypress(function(e){return raccourcis(e);});
		
	//empecher le scroll [scrollcontrole]
	$(document).on('mousewheel DOMMouseScroll', '[scrollcontrole]', function(e){
		var scrollTo = null;
		if(e.type == 'mousewheel'){
			scrollTo = (e.originalEvent.wheelDelta * -1);
		}else if(e.type == 'DOMMouseScroll'){
			scrollTo = 20 * e.originalEvent.detail;
		};
		if(scrollTo){
			e.preventDefault();
			$(this).scrollTop(scrollTo + $(this).scrollTop());
		};
	});
	
	//gerer l'historique de la page
	window.onpopstate = function(e){
		if(e.state){
			
			//retire les balises inexistantes
			$('HEAD').each(function(){
				if((this.id).length>5 
				&& (this.id).substring(0,5)=='head_'){
					balise_nom = (this.id).substring(5);
					if(typeof(e.state.entete[balise_nom]) == 'undefined'){
						$(this).remove();
					};
				};
			});
			//rajoute les balises manquantes
			for(balise_nom in e.state.entete){
				if($('[id="head_'+balise_nom+'"]').length == 0){
					$('HEAD').append(e.state.entete[balise_nom]);
				};
			}
			document.title = e.state.titre;
			$('.POS_body').html(e.state.contenu);
			$('.logs').html(e.state.logs);	
		};
	};
});