<?
class LogScrubber {

	function scrub( $filename, $params ) {
		$data = array();

		$text = '';
		$cast = array();
		$debug = false;

		$patterns = array(
			'/^You say/' => $params['logger'] . ' says',
			'/^You (paged|slowly fade|take|drop|leave|have)/' => false,
			'/^\[OOC/' => false,
			'/^\[Game/' => false,
			'/^GAME/' => false,
			'/has (partially disconnected|disconnected|arrived|connected|reconnected)\.$/' => false,
			'/takes a (full ){0,1}[rR]{1}ecovery\.$/' => false,
			'/^tick$/' => false,
			'/^\|/' => false,
			'/[-=_\*]{4}/' => false,
			'/[^\s]{1}[\s]{2}/' => false,
			'/^<(Public|Staff|Newbie|RP)>/' => false,
			'/^([^<]{1,30})>/' => false,
			'/(.+(<\w+>)){1,2}/' => false,
			'/^(.{1,15})(?:\([^\)]+\)?) pages:/' => false,
			'/^(Damage|Characters|Objects|Created|Obvious [Ee]xits):/' => false,
			'/rolls (\d+ x ){0,1}\d{0,2}(\.\d){0,1}\s*D6/' => false,
			'/^Huh\?/' => false,
			'/^MAIL:/' => false,
			'/^From afar/' => false,
			'/^Long distance to/' => false,
			'/^Parent changed\.$/' => false,
			'/- Set\.$/' => false,
			'/^Name set\.$/' => false,
			'/^Logfile from/' => false,
			'/>\*EFFECT\*</' => false,
			'/^.{1,20}(slowly fades from the IC world\.\.|has left)\.$/' => false,
			'/\d+ AP/' => false,
			'/\d+ pts/' => false,
			'/\([+-]\d(\s*\d*\/\d*)?\)/' => false,
			'/Telepathic message to ([^:]+):/' => $params['logger'] . ' sends a telepathic message to $1:',
			"/You hear ([^']+)'s voice inside your head:/" => $params['logger'] . ' hears $1\'s telepathic voice:',
		);


		$file = fopen( $filename, 'r' );
		while ( $s = fgets( $file ) ) {
			$s = preg_replace( '/[\r\n]+$/', '', $s );
			foreach ( $patterns as $pattern => $replace ) {
					if ( $debug ) {
						//print $s . ' ' . $pattern . "\n";
					}					
				if ( preg_match( $pattern, $s ) ) {
					if ( $replace ) {
						$s = preg_replace( $pattern, $replace, $s );
					}
					else {
						$s = '';
					}
				}
			}

			if ( preg_match( '/^([A-Za-z\s]{1,19}) says/', $s, $match ) ) {
				$cast[$match[1]] = 1;
			}

			$s = preg_replace( '/[\s\n]+$/', '', $s );
			$s = preg_replace( '/^[\s\n]+/', '', $s );			

			$text .= ($s && !preg_match( '/^[\s\n]+$/', $s ) ) ? $this->justify( $s, 79 ) . "\n\n" : '';		
		}

		fclose( $file );

		$cast = array_keys( $cast );
		sort( $cast );

		$data['text'] = $text;
		$data['cast'] = $cast;
		return $data;
	}

	function justify( $s, $size ) {
		$text = '';
		$current = 0;

		foreach ( explode( ' ', $s ) as $w ) {
			if ( $current + strlen( $w ) + 1 > $size ) {
				$text .= "\n";
				$current = 0;
			}
			$text .= "$w ";
			$current += strlen( $w ) + 1;
		}
		return $text;
	}


}

#$log = new LogScrubber();
#$data = $log->scrub( '/home/engwenya/IF9.txt', array( 'logger' => 'Yolanda' ) );
#print "{$data['text']}\n";
?>