<?php

$method = strtoupper($_SERVER['REQUEST_METHOD']);
if('POST' != $method) {
	exit('Invalid request');
}

// CONFIGURE RECAPTCHA
define('GR_SECRET', '6LfL2q4UAAAAABTqw2pd8W6d_kCCIKyXgh-xvwM4');
define('GR_URL', 'https://www.google.com/recaptcha/api/siteverify');

// Configuration option.
// Enter the email address that you want to emails to be sent to.
// Example $address = "john.doe@yourdomain.com";
$address = "info@igelephant.com";
$Cc = "fportabella@igelephant.com; aferre@igelephant.com"

function validateRecaptcha( $secret, $response, $url = GR_URL ){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, 1);
	$params = array(
		'secret' => urlencode($secret),
		'response' => urlencode($response),
	);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	$result = json_decode($result);
	curl_close($ch);
	return (isset($result->success) && $result->success);
}


if (!defined("PHP_EOL")) define("PHP_EOL", "\r\n");

// Sanitize request
$name = (isset($_POST['name']) ? strip_tags($_POST['name']) : '');
$lastname = (isset($_POST['lastname']) ? strip_tags($_POST['lastname']) : '');
$email = (isset($_POST['email']) ? strip_tags($_POST['email']) : '');
$subject = (isset($_POST['subject']) ? strip_tags($_POST['subject']) : '');
$message = (isset($_POST['message']) ? strip_tags($_POST['message']) : '');
$g_response = (isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '');

// Validate inputs
if(empty($name)){
	echo '<div class="alert alert-warning error"><p><strong>¡AVISO!</strong> El nombre es requerido.</p></div>';
	exit();
}
if(empty($lastname)){
	echo '<div class="alert alert-warning error"><p><strong>¡AVISO!</strong> Last name is required.</p></div>';
	exit();
}
if(empty($email)){
	echo '<div class="alert alert-warning error"><p><strong>¡AVISO!</strong> El email es requerido.</p></div>';
	exit();
}
if(filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
	echo '<div class="alert alert-warning error"><p><strong>¡AVISO!</strong> Has introducido un email no válido. Prueba de nuevo.</p></div>';
	exit();
}
if(empty($subject)){
	$subject = 'You have been contacted from your website by ' . $name . ' ' . $lastname;
}
if(empty($message)){
	echo '<div class="alert alert-warning error"><p><strong>¡AVISO!</strong> Debes escribir un mensaje.</p></div>';
	exit();
}
if(get_magic_quotes_gpc()) {
	$message = stripslashes($message);
}
if(empty($g_response)){
	echo '<div class="alert alert-warning error"><p><strong>Attention!</strong> Confirma que no eres un robot aceptando el recaptcha.</p></div>';
	exit();
}
if(!validateRecaptcha(GR_SECRET, $g_response, GR_URL)){
	echo '<div class="alert alert-warning error"><p><strong>Attention!</strong> Captcha no es correcto.</p></div>';
	exit();
}


// Configuration option.
// You can change this if you feel that you need to.
// Developers, you may wish to add more fields to the form, in which case you must be sure to add them here.

$e_body = "Has sido contactado por $name $lastname , y este es el mensaje que ha enviado." . PHP_EOL . PHP_EOL;
$e_content = "\"$message\"" . PHP_EOL . PHP_EOL;
$e_reply = "Puedes contactar $name via email, $email";

$msg = wordwrap( $e_body . $e_content . $e_reply, 70 );

$headers = "From: \"$name $lastname\" < \"$email\" >" . PHP_EOL;
$headers .= "Cc: $Cc" . PHP_EOL;
$headers .= "Reply-To: $email" . PHP_EOL;
$headers .= "MIME-Version: 1.0" . PHP_EOL;
$headers .= "Content-type: text/plain; charset=utf-8" . PHP_EOL;
$headers .= "Content-Transfer-Encoding: quoted-printable" . PHP_EOL;

if(mail($address, $subject, $msg, $headers)) {

	// Email has sent successfully, echo an error message.
	echo '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button><p>Gracias <strong>'.$name.'</strong>, hemos recibido tu mensaje. En breve te responderemos.</p></div>';

} 
else {
	// Email has NOT been sent successfully, echo an error message.
	echo '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button><div class="alert alert-danger"><strong>¡ERROR!</strong> El mensaje no se ha enviado correctamente. Prueba de nuevo.</div>';
}
?>