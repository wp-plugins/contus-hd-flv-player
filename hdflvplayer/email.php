<?php

/*
 * version : 1.3
 * Edited by : John THomas
 * Email : johnthomas@contus.in
 * Purpose : Used to send email from player(share)
 * Path:/wp-content/plugins/contus-hd-flv-player/hdflvplayer/email.php
 * Date:13/1/11
 *
 */


$to = $_POST["to"];
$from = $_POST["from"];
$url = $_POST["url"];
$subject = "You have received a video!";

// header information not including sendTo and Subject
$headers = "From: " . "<" . $_POST["from"] . ">\r\n";
$headers .= "Reply-To: " . $_POST["from"] . "\r\n";
$headers .= "Return-path: " . $_POST["from"];
$message = $_POST["note"] . "\n\n";
$message .= "Video URL: " . $url;
 //Mail function
if(mail($to, $subject, $message, $headers))
{
	echo "output=sent";
} else {
	echo "output=error";
}
?>