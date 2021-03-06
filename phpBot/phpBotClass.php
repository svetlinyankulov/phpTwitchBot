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
	
	public function botConfig($config){
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
	
	public function socket(){
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

	public function run(){
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
                
                if(strpos($msg, "!whatis") !== false){
                    $search = str_replace("!whatis ", '', $msg);
                    	    
					$url = "http://wikipedia.org/w/api.php?action=opensearch&search=".urlencode($search)."&format=xml&limit=1";
				    $ch = curl_init($url);
				    curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
				    curl_setopt($ch, CURLOPT_POST, FALSE);
				    curl_setopt($ch, CURLOPT_HEADER, false);
				    curl_setopt($ch, CURLOPT_NOBODY, FALSE);
				    curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
				    curl_setopt($ch, CURLOPT_REFERER, "");
				    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
				    curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
				    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; he; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8");
				    $page = curl_exec($ch);
				    $xml = simplexml_load_string($page);
				    $answer = $xml->Section->Item->Description;

					$response = fputs($socket, "PRIVMSG ".$this->chan." :".$nick." ".$answer."\n");
					
				}
			}
			flush();
		}
	}
}