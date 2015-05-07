<?php
	//GENERATEUR CAPTCHA
	
	//génere code
	//$caracteres = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
	$caracteres = '0';
	$captcha1 = aleatoire($caracteres);
	$captcha2 = aleatoire($caracteres);
	$captcha3 = aleatoire($caracteres);
	$captcha4 = aleatoire($caracteres);
	$captcha5 = aleatoire($caracteres);
	
	//sauvegarde captcha
	$code = $captcha1.$captcha2.$captcha3.$captcha4.$captcha5;
	$_SESSION['captcha'] = md5($code);
	
	//genere fond de l'image
	$image = imagecreatefrompng(repertoire('images', '/img/captcha.png'));
	
	//defini polices et couleurs
	$polices = glob(repertoire('polices', '/*.ttf'));
	$couleurs = [
		imagecolorallocate($image,  255, 255, 255),
		imagecolorallocate($image,  255, 255, 255),
		imagecolorallocate($image,  255, 200, 200),
		imagecolorallocate($image,  200, 255, 200),
		imagecolorallocate($image,  200, 200, 255),
	];
	
	//place les caracteres sur l'image
	imagettftext($image, 12, rand(-12, 12), 5, 20, aleatoire($couleurs), aleatoire($polices), $captcha1);
	imagettftext($image, 12, rand(-12, 12), 22, 20, aleatoire($couleurs), aleatoire($polices), $captcha2);
	imagettftext($image, 12, rand(-12, 12), 39, 20, aleatoire($couleurs), aleatoire($polices), $captcha3);
	imagettftext($image, 12, rand(-12, 12), 58, 20, aleatoire($couleurs), aleatoire($polices), $captcha4);
	imagettftext($image, 12, rand(-12, 12), 75, 20, aleatoire($couleurs), aleatoire($polices), $captcha5);
	
	//ecrit l'image
	header('Content-Type: image/png');
	imagepng($image);
	
	//libère la mémoire
	imagedestroy($image);
?>