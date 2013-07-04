<?

class FillInForm {

	function fill($html, $params, $errors) {
		$parser = xml_parser_create();
		
		xml_parse_into_struct($parser, "<root>$html</root>", $data);
		xml_parser_free($parser);

		$selectName = '';
		$output = '';
		foreach ($data as $tag) {
			$tagId = array_key_exists('attributes', $tag) 
					&& array_key_exists('ID', $tag['attributes']) 
					? $tag['attributes']['ID']
					: '';
			$tagName = array_key_exists('attributes', $tag) 
					&& array_key_exists('NAME', $tag['attributes']) 
					? $tag['attributes']['NAME']
					: '';
			$tagValue = array_key_exists('attributes', $tag) 
					&& array_key_exists('VALUE', $tag['attributes']) 
					? $tag['attributes']['VALUE']
					: '';
			$tagType = array_key_exists('attributes', $tag) 
					&& array_key_exists('TYPE', $tag['attributes']) 
					? $tag['attributes']['TYPE']
					: '';
			
			if ($tag['tag'] == 'DIV') {
				if (array_key_exists($tagId, $errors)) {
					$tag['value'] = $errors[$tagId];
				}
			}

			if ($tag['tag'] == 'TEXTAREA') {
				if (array_key_exists($tagName, $params)) {
					$tag['value'] = $params[$tagName];
				}
			}

			if ($tag['tag'] == 'INPUT') {
				if (strtolower($tagType) == 'radio' &&
						array_key_exists($tagName, $params)) {
					if ($tag['attributes']['VALUE'] == $params[$tagName]) {
						$tag['attributes']['checked'] = '1';
					}
				} else if (array_key_exists($tagName, $params)) {
					$tag['attributes']['VALUE'] = $params[$tagName];
				}
			}

			if ($tag['tag'] == 'DIV') {
				if (array_key_exists($tagId, $params)) {
					//$tag['tag']['attributes']['value'] = $params[$tag['attributes']['ID']];
				}
			}

			if ($tag['tag'] == 'OPTION' && $selectName) {
				if ($tag['attributes']['VALUE'] == $params[$selectName]) {
					$tag['attributes']['selected'] = '1';
				}
			}

			if ($tag['type'] == 'open') {
				$attributes = '';
				if (array_key_exists('attributes', $tag) && $tag['attributes']) {
					foreach ($tag['attributes'] as $k => $v) {
						$attributes .= sprintf(' %s="%s"', $k, $v);
					}
				}

				$output .= sprintf("<%s%s>\n", $tag['tag'], $attributes
				);

				if ($tag['tag'] == 'SELECT') {
					$selectName = $tagName;
				}
			} elseif ($tag['type'] == 'complete') {
				$attributes = '';
				if (array_key_exists('attributes', $tag) && $tag['attributes']) {
					foreach ($tag['attributes'] as $k => $v) {
						$attributes .= sprintf(' %s="%s"', $k, $v);
					}
				}

				$output .= sprintf("<%s%s/>%s</%s>\n", $tag['tag'], $attributes, array_key_exists('value', $tag) ? $tag['value'] : '', $tag['tag']
				);
			} else if ($tag['type'] == 'close') {
				$output .= sprintf("</%s>\n", $tag['tag']);
			} else if ($tag['type'] == 'cdata') {
				$output .= $tag['value']; //print_r( $tag, 1 );
			}
		}
		return $output;
	}

}

?>