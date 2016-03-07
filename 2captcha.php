<?php
# 2Captcha API PHP Client
# By Carlos López Software
# facebook.com/c.ernest.1990
# 2016-03-07

class _2Captcha {
	private $image64bits,
		$id,
		$text,
		$key,
		$errors,
		$sendingErrors,
		$readingErrors;

	public function __construct() {
		$this->errors = array();

		$this->sendingErrors = array(
		  'ERROR_WRONG_USER_KEY' 	   => 'Wrong "key" parameter format, it should contain 32 symbols',
		  'ERROR_KEY_DOES_NOT_EXIST' 	   => 'The "key" doesn’t exist',
		  'ERROR_ZERO_BALANCE' 		   => 'You don’t have money on your account',
		  'ERROR_NO_SLOT_AVAILABLE'  	   => 'The current bid is higher than the maximum bid set for your account.',
		  'ERROR_ZERO_CAPTCHA_FILESIZE'    => 'CAPTCHA size is less than 100 bites',
		  'ERROR_TOO_BIG_CAPTCHA_FILESIZE' => 'CAPTCHA size is more than 100 Kbites',
		  'ERROR_WRONG_FILE_EXTENSION' 	   => 'The CAPTCHA has a wrong extension. Possible extensions are: jpg,jpeg,gif,png',
		  'ERROR_IMAGE_TYPE_NOT_SUPPORTED' => 'The server cannot recognize the CAPTCHA file type.',
		  'ERROR_IP_NOT_ALLOWED' 	   => 'The request has sent from the IP that is not on the list of your IPs. Check the list of your IPs in the system.',
		  'IP_BANNED' 			   => 'The IP address you\'re trying to access our server with is banned due to many frequent attempts to access the 
		  																		 server using wrong authorization keys. To lift the ban, please, contact our support team via email: support@2captcha.com'
		);

		$this->readingErrors = array(
			'CAPCHA_NOT_READY' 	   => 'CAPTCHA is being solved, repeat the request several seconds later',
			'ERROR_KEY_DOES_NOT_EXIST' => 'You used the wrong key in the query',
			'ERROR_WRONG_ID_FORMAT'    => 'Wrong format ID CAPTCHA. ID must contain only numbers',
			'ERROR_CAPTCHA_UNSOLVABLE' => 'Captcha could not solve three different employee. Funds for this captcha not'
		);
	}

	public function setKey( $key ) {
		 if( !preg_match('|^[a-z0-9]{32}$|', $key) ) {
			$this->errors[] = 'Invalid API Key';
			return false;
		}

		$this->key = $key; 

		return true;
	}

	private function curl( $url, $fields = array() ) {
		$ch = curl_init( $url );

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if(  count($fields  ) > 0  ) {	
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		}

		return @curl_exec($ch);
	}

	public function setImage( $image ) {
		$image = str_replace( array("\r", "\n"), "",  trim( $image ));

		if( $image == '' ) {
			$this->errors[] = 'Invalid Image Name';
			return false;
		}

		$image = explode( '.', $image );
		$ext = $image[ count( $image ) -  1];

		if( !in_array( strtolower( $ext ), array( 'jpg', 'jpeg', 'bmp', 'gif', 'png' ) ) ) {
			$this->errors[] = 'Invalid Image Format';
			return false;
		}

		$img = @file_get_contents( implode('.', $image) );

		if( $img == '' ) {
			$this->errors[] = 'Invalid Image File';
			return false;
		}

		$this->image = base64_encode( $img );

		return true;
	}

	private function send() {
		$info = $this->curl('http://2captcha.com/in.php', array( 'method' => 'base64', 
						 			 'key'    => $this->key,
									 'body'   => $this->image ));

		if( !preg_match('|OK|', $info) ) {
			$this->errors[] = $this->sendingErrors[ $info ];
			return false;
		}
		else {
			$info = explode('|', $info);
			$this->id = (int)$info[1];
			return true;
		}
	}

	private function read() {
		do {
			$info = @file_get_contents( 'http://2captcha.com/res.php?key=' . $this->key . '&action=get&id=' . $this->id );
			if( $info == 'CAPCHA_NOT_READY' )
				usleep(300);
		} while( $info == 'CAPCHA_NOT_READY' );

		if( !preg_match('|OK|', $info) ) {
			$this->errors[] = $this->readingErrors[ $info ];
			return false;
		}
		else {
			$info = explode('|', $info);
			$this->text = $info[1];
			return true;
		}
	}

	public function run() {
		if($this->send())
			return $this->read();
		return false;
	}

	public function getErrors() {
		return $this->errors;
	}

	public function getText() {
		return $this->text;
	}
}

?>
