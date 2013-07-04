<?

class MUSHRobot {

	private $socket = false;

	function __construct($host, $port) {
		$this->host = $host;
		$this->port = $port;
	}

	public function connect() {
		$this->socket = fsockopen($this->host, $this->port);
		//stream_set_blocking($this->socket, 0);
		return $this->socket;
	}

	public function login($username, $password) {
		return $this->send(sprintf('connect %s %s', $username, $password));
	}

	public function send($text) {
		$terminator = 'xXxXxXx';

		$output = '';
		$this->writeLine("$text\n");
		$this->writeLine("think $terminator");

		$s = $this->readLine("\n");
		while ($s != $terminator) {
			$output .= "$s\n";
			$s = $this->readLine("\n");
		}

		return $output;
	}

	private function writeLine($text) {
		fwrite($this->socket, $text . "\n");
		fflush($this->socket);
	}

	private function readLine($terminator) {
		return trim(stream_get_line($this->socket, 10000, $terminator));
	}

}

?>