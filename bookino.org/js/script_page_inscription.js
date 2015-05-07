
//CODE JS PROPRE A LA PAGE INSCRIPTION

(function($,W,D){
var JQUERY4U = {};
JQUERY4U.UTIL = {
setupFormValidation: function(){


	$("#form_register").validate({
		rules:{
			IVeaiEgXyw031zfl9VMmr:{
				required:true,
				defaultValue:"true",
				minlength:4,
				maxlength:30,
				regex:/^[A-Za-z0-9\d]+$/i,
				remote:{datas:{action:'inscription_pseudo', 'IVeaiEgXyw031zfl9VMmr':'_value'},
						type:"post",
						async:true}
			},
			IVeaiEgXyw0jW1Xh0hT:{
				required:true,
				defaultValue:"true",
				email:true,
				maxlength:256,
				remote:{datas:{action:'inscription_email', 'IVeaiEgXyw0jW1Xh0hT':'_value'},
						type:"post",
						async:true}
			},
			IVeaiEgXyw0jWqtSv80:{
				required:true,
				defaultValue:"true",
				minlength:6,
				maxlength:30,
				regex:/^[A-Za-z0-9\d]+$/i
			},
			IVeaiEgXyw0jWqtSv81:{
				required:true,
				defaultValue:"true",
				equalTo:'#IVeaiEgXyw0jWqtSv80'
			},
			IVeaiEgXyw0CVxnlGoOacp:{
				required:true,
				equalTo:'#IVeaiEgXyw0CWIRq7A8c0I3D'
			}
		},
		messages:{
			IVeaiEgXyw031zfl9VMmr:{
				required:"Pseudo nécéssaire",
				defaultValue:"Pseudo nécéssaire",
				minlength:"Le pseudo doit faire entre 4 et 30 carractères",
				maxlength:"Le pseudo doit faire entre 4 et 30 carractères",
				regex:"Pseudo invalide : chiffres et lettres uniquement",
				remote:"Le pseudo est déjà utilisé"
			},
			IVeaiEgXyw0jW1Xh0hT:{
				required:"Email nécéssaire",
				defaultValue:"Email nécéssaire",
				email:"Email incorrect",
				maxlength:"L'email doit faire moins de 256 carractères",
				remote:"L'email est déjà utilisé"
			},
			IVeaiEgXyw0jWqtSv80:{
				required:"Mot de passe nécéssaire",
				defaultValue:"Mot de passe nécéssaire",
				minlength:"Le mot de passe doit faire entre 6 et 30 carractères",
				maxlength:"Le mot de passe doit faire entre 6 et 30 carractères",
				regex:"Mot de passe invalide : chiffres et lettres uniquement"
			},
			IVeaiEgXyw0jWqtSv81:{
				required:"Comfirmation de mot de passe nécéssaire",
				defaultValue:"Comfirmation de mot de passe nécéssaire",
				equalTo:"Les mots de passe sont différents"
			},
			IVeaiEgXyw0CVxnlGoOacp:{
				required:"Veuillez répondre a la question de sécurité",
				equalTo:"Faux"
			}

		},
		submitHandler: function(form){
			form.submit();
		}
	});

	$("#form_changeremail").validate({
		rules:{
			HDs3nlVOyz1DiRbhh91e6drmpg:{
				required:true,
				defaultValue:"true",
				email:true,
				maxlength:256,
				remote:{datas:{action:'inscription_testchangeremail', 'HDs3nlVOyz1DiRbhh91e6drmpg':'_value', 'HDs3nlVOyz1DiRbhh8yd233wqHrP':'_value'},
						type:"post",
						async:false}
			}},
		messages:{
			HDs3nlVOyz1DiRbhh91e6drmpg:{
				required:"Email nécéssaire",
				defaultValue:"true",
				email:"Email incorrect",
				maxlength:"L'email doit faire moins de 256 carractères",
				remote:"L'email est déjà utilisé"
			}},
		submitHandler:function(form){
			ajax_form(form, {action:'inscription_changeremail', 'HDs3nlVOyz1DiRbhh91e6drmpg':'_value', 'HDs3nlVOyz1DiRbhh8yd233wqHrP':'_value'}, 
					function(){$('#changeremail_affichemail').html($('*[name=HDs3nlVOyz1DiRbhh91e6drmpg]').val()); 
								$('#form_renvoyeremail').submit(); 
								zone_montrer('changeremail', '0');
					});
			return false;
		}
	});
	
	$("#form_renvoyeremail").validate({
		rules:{},
		messages:{},
		submitHandler:function(form){
			ajax_form(form, {'action':'inscription_renvoyeremail', 'IVecuiin8KaD26qA62PUTCF5zaSIx':'_value'}, null);
			return false;
		}
	});

}
}
$(D).ready(function($){
JQUERY4U.UTIL.setupFormValidation();
});
})(jQuery, window, document);
