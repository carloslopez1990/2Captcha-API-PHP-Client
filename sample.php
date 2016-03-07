<?php
# 2Captcha API PHP Client
# By Carlos López Software
# facebook.com/c.ernest.1990
# 2016-03-07

include '2Captcha.php';

$o2c = new _2Captcha;

if( $o2c->setKey('YOUR_KEY') && 
		$o2c->setImage( './mycaptcha.jpg' ) && 
		$o2c->run() )

	print "Captcha Text: ".$o2c->getText();

else print_r( $o2c->getErrors() );
	
?>