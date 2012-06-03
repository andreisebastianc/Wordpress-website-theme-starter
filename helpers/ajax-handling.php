<?php
/**
 *
 */
function handle_message_me(){
	$nonce = $_POST['nonce'];
	if(wp_verify_nonce($nonce,'message_me')){
		//send email
		$email_address = get_bloginfo('admin_email');

		// php validation of fields
		if(empty($_POST['name']) || empty($_POST['email']) ||
			empty($_POST['message'])){
				echo false;exit();die();
			}

		// email sending
		$message = "Un mesaj nou de la ".$_POST['name']." (".$_POST['email'].")
			:\n";
		$message .= $_POST['message'];
		echo mail($email_address,'New message from contact form on my website',$message);
		exit();die();
	}
}

add_action('wp_ajax_message_me','handle_message_me');
add_action('wp_ajax_nopriv_message_me','handle_message_me');

?>
