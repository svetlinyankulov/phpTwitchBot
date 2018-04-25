<?php

Class phpBot{

	// Configuration
	public $chan = null;
	public $serv = null;
	public $port = null;
	public $nick = null;
	public $token = null;
	private $socket = null;
	
	public function __construct(){
		global $argv;
		if($argv[1]){
			echo "Applying configuration file\n";
			$this->botConfig($argv[1]);
		}
		if($this->socket()){
			echo "Listening..\n";
		}
	}
	
	function botConfig($config){
		$xml = simplexml_load_file($config)or die("Error: Could not read object");
		$json = json_encode($xml);
		$config = json_decode($json, true);
		foreach($config as $user){
			$this->chan = $user['chan'];
			$this->serv = $user['serv'];
			$this->port = $user['port'];
			$this->nick = $user['nick'];
			$this->token = $user['token'];
		}
	}
	
	function socket(){
		$socket = fsockopen($this->serv, $this->port);
		fputs($socket,"CAP REQ :twitch.tv/tags twitch.tv/commands twitch.tv/membership\n");
		fputs($socket,"PASS ".$this->token."\n");
		fputs($socket,"NICK ".$this->nick."\n");
		fputs($socket,"JOIN ".$this->chan."\n");

		if($socket){
			$this->socket = $socket;
			return true;
		}
	}

	function run(){
		$socket = $this->socket;
		while(true){
			$data = fgets($socket);
			$data = trim($data);

			// Respond to PING check			
			if ($data == 'PING :tmi.twitch.tv') {
				fputs($socket, "PONG :tmi.twitch.tv\n");
			}		
			
			// Check if a user message was sent			
			if(strpos($data, "PRIVMSG") !== FALSE){
				
				//# Format data stream				
				$msgData = explode(";", $data);
				
				// Format Nick				
				$nick = explode("=", $msgData[2])[1];
				
				// Format Message
				$msg = explode($this->chan." :", $msgData[11])[1];
				
				// Output Message				
				echo $nick.": ".$msg."\n";
				
				// Commands section and other functions				
				if($msg == "!about"){
					$response = fputs($socket, "PRIVMSG ".$this->chan." :". $nick ." About me\n");
					var_dump($response);
					//echo $response."\n";
				}
				
				if($msg == "!hello"){
					$response = fputs($socket, "PRIVMSG ".$this->chan." : Hello ".$nick." \n");
					var_dump($response);
					//echo $response."\n";
				}

				if($msg == "!bye"){
					$response = fputs($socket, "PRIVMSG ".$this->chan." : Bye ".$nick." come again soon!\n");
					var_dump($response);
					//echo $response."\n";
				}
			}
			flush();
		}
	}
}