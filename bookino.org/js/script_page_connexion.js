
//CODE JS PROPRE A LA PAGE DE CONNEXION

(function($,W,D){
var JQUERY4U = {};
JQUERY4U.UTIL = {
setupFormValidation: function(){

	$("#form_connexion").validate({
		rules:{
			HE19M5OlCwjDnj5Z1xZBa9:{
				required:true,
				defaultValue:"true",
				minlength:4,
				maxlength:30,
				regex:/^[A-Za-z0-9\d]+$/i
			},
			HE19M5OlCwjDnj4ceyxFOo3a8ONq:{
				required:true,
				defaultValue:"true",
				minlength:6,
				maxlength:30,
				regex:/^[A-Za-z0-9\d]+$/i
			},
			HE19M5OlCwjDnj1U1c9F5q39:{
				required:true,
				equalTo:'#HE19M5OlCwjDnj6U2mtCBzEI2'
			}
		},
		messages:{
			HE19M5OlCwjDnj5Z1xZBa9:{
				required:"Pseudo nécéssaire",
				defaultValue:"Pseudo nécéssaire",
				minlength:"Le pseudo doit faire entre 4 et 30 carractères",
				maxlength:"Le pseudo doit faire entre 4 et 30 carractères",
				regex:"Pseudo invalide : chiffres et lettres uniquement"
			},
			HE19M5OlCwjDnj4ceyxFOo3a8ONq:{
				required:"Mot de passe nécéssaire",
				defaultValue:"Mot de passe nécéssaire",
				minlength:"Le mot de passe doit faire entre 6 et 30 carractères",
				maxlength:"Le mot de passe doit faire entre 6 et 30 carractères",
				regex:"Mot de passe invalide : chiffres et lettres uniquement"
			},
			HE19M5OlCwjDnj1U1c9F5q39:{
				required:"Veuillez répondre a la question de sécurité",
				equalTo:"Faux"
			}
		},
		submitHandler:function(form){
			form.submit();
		}
	});
	
	$("#form_recupmdp").validate({
		rules:{
			IVeZTiDsr2Y31zfl9VMmr:{
				required:true,
				defaultValue:"true",
				minlength:4,
				maxlength:30,
				regex:/^[A-Za-z0-9\d]+$/i
			},
			IVeZTiDsr2YjW1Xh0hT:{
				required:true,
				defaultValue:"true",
				email:true,
				maxlength:256,
			}
		},
		messages:{
			IVeZTiDsr2Y31zfl9VMmr:{
				required:"Pseudo nécéssaire",
				defaultValue:"Pseudo nécéssaire",
				minlength:"Le pseudo doit faire entre 4 et 30 carractères",
				maxlength:"Le pseudo doit faire entre 4 et 30 carractères",
				regex:"Pseudo invalide : chiffres et lettres uniquement"
			},
			IVeZTiDsr2YjW1Xh0hT:{
				required:"Email nécéssaire",
				defaultValue:"Email nécéssaire",
				email:"Email incorrect",
				maxlength:"L'email doit faire moins de 256 carractères",
			}
		},
		submitHandler:function(form){
			ajax_form(form, {'action':'recupmdp', 'IVeZTiDsr2Y31zfl9VMmr':'_value','IVeZTiDsr2YjW1Xh0hT':'_value'}, 
				 ('/'+user_langue+'/scripts/fonctions_connexion'), 
				 (function(){zone_montrer('connexion', '2');}));
			return false;
		}
	});

	$("#form_nouveaumdp").validate({
		rules:{},
		messages:{},
		submitHandler:function(form){
			ajax_form(form, {'action':'nouveaumdp', 'IAZSI3sUgpzCwqfn9OI1jlDIJ':'_value'}, 
				 ('/'+user_langue+'/scripts/fonctions_connexion'), 
				 (function(){$('#U_nouveaumotdepasse').html(ajax_rep); zone_montrer('nouveaumdp', '1');}));
			return false;
		}
	});

}
}
$(D).ready(function($){
JQUERY4U.UTIL.setupFormValidation();
});
})(jQuery, window, document);
