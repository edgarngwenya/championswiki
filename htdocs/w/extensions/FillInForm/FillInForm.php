<?

class FillInForm {

	function fill( $html, $params, $errors ) {
		$parser = xml_parser_create();
		xml_parse_into_struct( $parser, "<root>$html</root>", $data ); 
		xml_parser_free( $parser );

		$selectName = '';
		$output = '';
		foreach ( $data as $tag ) {
			if ( $tag['tag'] == 'DIV' ) {
				if ( array_key_exists( $tag['attributes']['ID'], $errors ) ) {
					$tag['value'] = $errors[$tag['attributes']['ID']];
				}
			}

			if ( $tag['tag'] == 'TEXTAREA' ) {
				if ( array_key_exists( $tag['attributes']['NAME'], $params ) ) {
					$tag['value'] = $params[$tag['attributes']['NAME']];
				}
			}

			if ( $tag['tag'] == 'INPUT' ) {
				if ( strtolower( $tag['attributes']['TYPE'] ) == 'radio' && 
					array_key_exists( $tag['attributes']['NAME'], $params ) ) {
					if ( $tag['attributes']['VALUE'] == $params[$tag['attributes']['NAME']] ) {
						$tag['attributes']['checked'] = '1';
					}
				}
				else if ( array_key_exists( $tag['attributes']['NAME'], $params ) )  {
					$tag['attributes']['VALUE'] = $params[$tag['attributes']['NAME']];
				}

			}

			if ( $tag['tag'] == 'DIV' ) {
				if ( array_key_exists( $tag['attributes']['ID'], $params ) ) {
					//$tag['tag']['attributes']['value'] = $params[$tag['attributes']['ID']];
				}

			}
			
			if ( $tag['tag'] == 'OPTION' && $selectName ) {
				if ( $tag['attributes']['VALUE'] == $params[$selectName] ) {
					$tag['attributes']['selected'] = '1';
				}
			}

			if ( $tag['type'] == 'open' ) {
				$attributes = '';
				if ( $tag['attributes'] ) {
					foreach ( $tag['attributes'] as $k => $v ) {
						$attributes .= sprintf( ' %s="%s"', $k, $v );
					}
				}

				$output .= sprintf( "<%s%s>\n", 
					$tag['tag'],
					$attributes
			  	);
			  	
			  	if ( $tag['tag'] == 'SELECT' ) {
			  		$selectName = $tag['attributes']['NAME'];
			  	}
			}	
			elseif ( $tag['type'] == 'complete' ) {
				$attributes = '';
				if ( $tag['attributes'] ) {
					foreach ( $tag['attributes'] as $k => $v ) {
						$attributes .= sprintf( ' %s="%s"', $k, $v );
					}
				}

				$output .= sprintf( "<%s%s/>%s</%s>\n", 
					$tag['tag'],
					$attributes,
					$tag['value'],
					$tag['tag']
			  	);
			}	
                        else if ( $tag['type'] == 'close' ) {
				$output .= sprintf( "</%s>\n", $tag['tag'] );
			}
                        else if ( $tag['type'] == 'cdata' ) {
				$output .= $tag['value']; //print_r( $tag, 1 );
			}
		}
		return $output;
	}

}


?>