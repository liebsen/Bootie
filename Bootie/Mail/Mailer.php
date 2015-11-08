<?php namespace Bootie\Mail;

class Mailer {

	public function send($recipient,$subject,$tpl,$data=array(),$from="")
	{

		//if( in_array($_SERVER['REMOTE_ADDR'],['127.0.0.1'])) return false;

		$message = \template($tpl,$data);

		//Create a new PHPMailer instance
		$mail = new \Bootie\Mail\PHPMailerLite;
		// Set PHPMailer to use the sendmail transport
		$mail->isSendmail();
		//Set who the message is to be sent from
		$mail->setFrom('noreply@devmeta.net', 'Devmeta Blogs');
		//Set an alternative reply-to address
		$mail->addReplyTo('noreply@devmeta.net', 'Devmeta Blogs');
		//Set who the message is to be sent to
		$mail->addAddress($recipient, 'John Doe');
		//Set the subject line
		$mail->Subject = utf8_decode($subject);

		$mail->isHTML(true);   
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$mail->msgHTML($message);
		//Replace the plain text body with one created manually
		$mail->AltBody = strip_tags($message);
		//Attach an image file
		//$mail->addAttachment('images/phpmailer_mini.png');
		//send the message, check for errors
		if (!$mail->send()) {
		    //echo "Mailer Error: " . $mail->ErrorInfo;
		    log_message("Mailer Error: " . $mail->ErrorInfo);
		    return false;
		} else {
		    return true;
		}
	}

	function template($tpl,$data=array())
	{

		@extract($data);
		
		$str = "";
		$template = PATH_VIEWS . ( str_replace(".","/",$tpl) ) . '.php';

		if(file_exists($template)){
			$str= file_get_contents($template);
		}

		return $str;
	}    
}
