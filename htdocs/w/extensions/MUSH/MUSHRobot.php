<?
class MUSHRobot {
	function __construct( $host, $port ) {
		$this->host = $host;
		$this->port = $port;
	}
	
	public function connect() {
		$this->socket = new Net_Socket();
		$this->socket->connect( $this->host, $this->port );		
	}
	
	public function login( $username, $password ) {
		return $this->send( sprintf( 'connect %s %s', $username, $password ) );
	}
	
	public function send( $text ) {
		$terminator = 'xXxXxXx';
		
		$output = '';
		$this->socket->writeLine( "$text\n" );
		$this->socket->writeLine( "think $terminator" );
		
		$s = $this->socket->readLine();
		while ( $s != $terminator ) {
			$output .= "$s\n";
			$s = $this->socket->readLine();
		}
		
		return $output;
	}

}

?>