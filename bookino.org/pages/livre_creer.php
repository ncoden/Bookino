<?php
	//GENERATEUR DE PAGE 'CREER LIVRE'
	
	//ecrit le titre
	echo('	
	<DIV class="POS_titre"><DIV class="I_marges">
		'.l('P_TITRE:LIVRE_CREER').'
	</DIV></DIV>
	
	<BR/>
	<BR/>
	<DIV class="C_zone I_centre U_zone_creerlivre"><DIV class="I_marges">
		<DIV class="contenu premier visible" id="zone_creerlivre_0">');
	
	echo(html_formulaire('livre_creer'));
	
	echo('
		</DIV>
	</DIV></DIV>');
?>