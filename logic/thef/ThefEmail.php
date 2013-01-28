<?php

/**
 * 	Interface for sending mails
 *  Please check for all needed constants to be defined in config_site or config_global
 */
class ThefEmail
{

    /**
     * Send mail using PHPMailer class
     * @param String $from_mail Mail address to send from
     * @param String $from_name Name to send from
     * @param Array $to Array of mailboxes to send to
     * @param String $subject Subject of the mail
     * @param String $body HTML content of the body
     * @param Array $images Array of images with relative path
     * @param Array $attachs Array of files attached with relative path
     * @param Array $options Array of extras options [EMAIL_CC, EMAIL_BCC]
     * @return Boolean True if successfully sended
     */
    public static function send($from_mail, $from_name, array $to, $subject, $body, array $images = array(), array $attachs = array(), array $options = array())
    {
	require_once(ROOT_PATH . '/include/lib/phpmailer/class.phpmailer.php');
	$oMail = new PHPMailer(true);
	$oMail->IsSMTP();
	try {
	    $oMail->SMTPDebug = 1;
	    $oMail->SMTPAuth = EMAIL_SMTP_AUTH;
	    $oMail->Host = EMAIL_SMTP_SERVER;
	    $oMail->Port = EMAIL_SMTP_PORT;
	    $oMail->Username = EMAIL_SMTP_USER;
	    $oMail->Password = EMAIL_SMTP_PASS;
	    $oMail->SetFrom($from_mail, $from_name);
	    $oMail->Subject = $subject;
	    $oMail->AddReplyTo($from_mail, $from_name);
	    foreach ($to as $address)
		$oMail->AddAddress($address);
	    if ($options['EMAIL_CC'])
		$oMail->AddCC($options['EMAIL_CC']);
	    if ($options['EMAIL_BCC'])
		$oMail->AddBCC($options['EMAIL_BCC']);
	    $oMail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
	    $oMail->MsgHTML($body);

	    if ($images) {
		foreach ($images as $img) {
		    $oMail->AddEmbeddedImage($img['path'], $img['cid']);
		}
	    }

	    if ($attachs) {
		foreach ($attachs as $attach) {
		    $oMail->AddAttachment($attach);
		}
	    }
	    //$oMail->AddAttachment('images/phpmailer.gif');      // attachment
	    //$oMail->AddAttachment('images/phpmailer_mini.gif'); // attachment

	    $success = $oMail->Send();
	    if (!$success) {
		self::sendWarningMail($from_mail, $from_name, $to, $subject, $body, $images, $attachs, $options);
	    }
	    return $success;
	} catch (phpmailerException $e) {
	    $options['error'] = '<PRE>' . print_r($e, true) . '</PRE>';
	    self::sendWarningMail($from_mail, $from_name, $to, $subject, $body, $images, $attachs, $options);
	    return false;
	} catch (Exception $e) {
	    $options['error'] = '<PRE>' . print_r($e, true) . '</PRE>';
	    self::sendWarningMail($from_mail, $from_name, $to, $subject, $body, $images, $attachs, $options);
	    return false;
	}
    }



    /**
     * Validate email address
     * @param String $email Email address
     * @return Boolean True if email address is valid
     */
    public static function isValidEmail($email)
    {
	$email = filter_var($email, FILTER_SANITIZE_EMAIL);
	$regex = "/^([a-z0-9+_]|\-|\.)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$/i";
	if (strpos($email, '@') !== false && strpos($email, '.') !== false) {
	    if (preg_match($regex, $email)) {
		return true;
	    } else {
		return false;
	    }
	} else {
	    return false;
	}
    }



    /**
     * Send warning mail if original mail couldnt be sended
     * @param String $from_mail Mail address to send from
     * @param String $from_name Name to send from
     * @param Array $to Array of mailboxes to send to
     * @param String $subject Subject of the mail
     * @param String $body HTML content of the body
     * @param Array $images Array of images with relative path
     * @param Array $attachs Array of files attached with relative path
     * @param Array $options Array of extras options [EMAIL_CC, EMAIL_BCC]
     * @return Boolean True if successfully sended
     */
    private static function sendWarningMail($from_mail, $from_name, array $to, $subject, $body, array $images = array(), array $attachs = array(), array $options = array())
    {
	require_once(ROOT_PATH . '/include/lib/phpmailer/class.phpmailer.php');
	$oMail = new PHPMailer(false);
	$oMail->IsSMTP();
	try {
	    $oMail->SMTPDebug = 0;
	    $oMail->SMTPAuth = true;
	    $oMail->Host = EMAIL_WARNING_HOST;
	    $oMail->Port = EMAIL_WARNING_PORT;
	    $oMail->Username = EMAIL_WARNING_USER;
	    $oMail->Password = EMAIL_WARNING_PASS;
	    $oMail->Subject = EMAIL_WARNING_SUBJECT;
	    $oMail->SetFrom(EMAIL_WARNING_FROM_EMAIL, EMAIL_WARNING_FROM_NAME);
	    for ($i = 1; $i <= 10; $i++) {
		$warning_to = 'EMAIL_WARNING_TO_' . $i;
		if (defined($warning_to))
		    $oMail->AddAddress(constant($warning_to));
	    }
	    $html = "
		<table bgcolor='FFFFFF' border='0' cellpading='0' cellspacing='5' width='800'>
			<tr>
				<td colspan='2' valign='top'><strong>Error al enviar el mail</td>
			</tr>
			<tr>
				<td valign='top'><strong>Fecha:</strong></td>
				<td valign='top'>" . Date('d/m/Y H:i:s') . "</td>
			<tr>
			<tr>
				<td valign='top'><strong>De:</strong></td>
				<td valign='top'>$from_mail ($from_name) </td>
			<tr>
			<tr>
				<td valign='top'><strong>Para:</strong></td>
				<td valign='top'>" . print_r($to, true) . "</td>
			<tr>
			<tr>
				<td valign='top'><strong>Asunto:</strong></td>
				<td valign='top'>$subject</td>
			<tr>
			<tr>
				<td valign='top'><strong>Cuerpo:</strong></td>
				<td valign='top'>$body</td>
			<tr>
			<tr>
				<td valign='top'><strong>Error:</strong></td>
				<td valign='top'>" . $options['error'] . "</td>
			<tr>
		</table>";
	    $oMail->MsgHTML($html);
	    if ($attachs) {
		foreach ($attachs as $attach) {
		    $oMail->AddAttachment($attach);
		}
	    }
	    $oMail->Send();
	} catch (phpmailerException $e) {
	    //echo $e->errorMessage();
	} catch (Exception $e) {
	    //echo $e->getMessage();
	}
    }



}