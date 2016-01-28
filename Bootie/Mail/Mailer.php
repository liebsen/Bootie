<?php namespace Bootie\Mail;

class Mailer {

	public function send($recipient,$subject,$data=array(),$tpl=null,$from="")
	{

		if($tpl)	$message = \template($tpl,$data);
		else $message = $data['message'];

		$mail = new \Bootie\Mail\PHPMailerLite;
		$mail->isSendmail();
		$mail->setFrom(config()->mailer['from'],config()->mailer['title']);
		$mail->addReplyTo(config()->mailer['from'],config()->mailer['title']);
		$mail->addAddress($recipient);
		$mail->Subject = utf8_decode($subject);

		$mail->isHTML(true);   
		$mail->msgHTML($message);
		$mail->AltBody = strip_tags($message);

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
			$str = file_get_contents($template);
		}

		return $str;
	}    
}
