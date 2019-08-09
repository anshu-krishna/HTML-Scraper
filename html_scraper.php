<?php
/*
Author: Anshu Krishna
Contact: anshu.krishna5@gmail.com
Date: 22-Nov-2018
Description: Set of PHP classes for simplifing data extraction from HTML.
 */
final class HTML_Scraper {
	const Extract_innerHTML = 'innerHTML';
	const Extract_outerHTML = 'outerHTML';
	const Extract_textContent = 'textContent';
	const Extract_textContentTrim = 'textContentTrim';
	
	private static function map_node($type, &$node) {
		if(!($node instanceof DOMNode)) {
			return NULL;
		}
		switch(TRUE) {
			case ($type === 'innerHTML'):
				return DOMNodeHelper::innerHTML($node);
				break;
			case ($type === 'outerHTML'):
				return DOMNodeHelper::outerHTML($node);
				break;
			case ($type === 'textContent'):
				return $node->textContent;
				break;
			case ($type === 'textContentTrim'):
				return trim($node->textContent);
				break;
			case (is_callable($type)):
				return $type($node);
				break;
			default:
				return NULL;
				break;
		}
	}

    protected $doc = null;
	
	public function __construct(string $source_html = NULL, int $options = NULL) {
		$this->doc = new DOMDocument('1.0', 'utf-8');
		if($source_html !== NULL) {
			$this->load_HTML_str($source_html, $options);
		}
	}

	public function __toString() : string {
		return $this->doc->saveHTML();
	}

	public function textContent() : string {
		return $this->doc->textContent;
	}
	
    public function load_HTML_str(string $source, int $options = NULL) : bool {
		$opt = LIBXML_NOERROR | LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED;
		if($options !== NULL) {
			$opt = $opt | $options;
		}
        return $this->doc->loadHTML(mb_convert_encoding($source, 'HTML-ENTITIES', 'UTF-8'), $opt);
	}
	
	public function load_HTML_file(string $filename, int $options = NULL, array $context = NULL) : bool {
		$source = ($context === NULL) ? file_get_contents($filename, FALSE) : file_get_contents($filename, FALSE, stream_context_create($context));
		if($source === FALSE) {
			return FALSE;
		}
		return $this->load_HTML_str($source, $options);
	}

	public function xpath(string $expr, int ...$items) {
		$xpath = new DOMXPath($this->doc);
		$nodes = $xpath->query($expr);
		if($nodes === FALSE) {
			return NULL;
		}
		$icount = count($items);
		switch(TRUE) {
			case ($icount === 0):
				return $nodes;
				break;
			case ($icount === 1):
				if($items[0] < 0) {
					return $nodes->item($nodes->length + $items[0]);
				}
				return $nodes->item($items[0]);
				break;
			default:
				$ret = [];
				$ncount = $nodes->length;
				foreach($items as $item) {
					if($item >= 0) {
						$ret[] = $nodes->item($item);
					} else {
						$ret[] = $nodes->item($ncount + $item);
					}
				}
				return $ret;
				break;
		}

		if($item !== NULL) {
			if($item >=0 ) {
				return $nodes->item($item);
			} else {
				return $nodes->item(count($nodes) + $item);
			}
		}
		return $nodes;
	}

	public function querySelector(string $selector, int ...$items) {
		return $this->xpath(static::CSS_to_Xpath($selector), ...$items);
	}

	public function xpath_extract($mapper, string $expr, int ...$items) {
		$nodes = $this->xpath($expr, ...$items);
		if($nodes === NULL) {
			return NULL;
		}
		switch(TRUE) {
			case ($mapper === 'innerHTML'):
			case ($mapper === 'outerHTML'):
			case ($mapper === 'textContent'):
			case ($mapper === 'textContentTrim'):
			case (is_callable($mapper)):
				if($nodes instanceof DOMNode) {
					return static::map_node($mapper, $nodes);
				} elseif($nodes instanceof DOMNodeList || is_array($nodes)) {
					$ret = [];
					foreach($nodes as $node) {
						$ret[] = static::map_node($mapper, $node);
					}
					return $ret;
				} else {
					return NULL;
				}
				break;
			default:
				trigger_error('Invalid mapper value in xpath_extract', E_USER_WARNING);
				return NULL;
				break;
		}
	}

	public function querySelector_extract($mapper, string $selector, int ...$items) {
		$nodes = $this->xpath(static::CSS_to_Xpath($selector), ...$items);
		if($nodes === NULL) {
			return NULL;
		}
		switch(TRUE) {
			case ($mapper === 'innerHTML'):
			case ($mapper === 'outerHTML'):
			case ($mapper === 'textContent'):
			case ($mapper === 'textContentTrim'):
			case (is_callable($mapper)):
				if($nodes instanceof DOMNode) {
					return static::map_node($mapper, $nodes);
				} elseif($nodes instanceof DOMNodeList || is_array($nodes)) {
					$ret = [];
					foreach($nodes as $node) {
						$ret[] = static::map_node($mapper, $node);
					}
					return $ret;
				} else {
					return NULL;
				}
				break;
			default:
				trigger_error('Invalid mapper value in querySelector_extract', E_USER_WARNING);
				return NULL;
				break;
		}
	}

	public static function new_from($source) {
		switch(TRUE) {
			case ($source instanceof DOMNodeList):
				$Return = [];
				foreach($source as $n) {
					$Return[] = new static($n->ownerDocument->saveHTML($n));
				}
				return $Return;
				break;
			case ($source instanceof DOMNode):
				return new static($source->ownerDocument->saveHTML($source));
				break;
			case (is_string($source)):
				return new static($source);
				break;
			default:
				return new static();
				break;
		}
	}

	public static function CSS_to_Xpath(string $path) {
		/**
		 * BASE CODE FOR THIS FUNCTION WAS CLONED FROM
		 * https://github.com/zendframework/zend-dom
		 * AND THEN MODIFIED
		 * 
		 * Zend Framework (http://framework.zend.com/)
		 *
		 * @link      http://github.com/zendframework/zf2 for the canonical source repository
		 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
		 * @license   http://framework.zend.com/license/new-bsd New BSD License
		 */
		$_tokenize = function($expression) {
			$expression = str_replace('>', '/', $expression);

			$expression = preg_replace('|#([a-z][a-z0-9_-]*)|i', '[@id=\'$1\']', $expression);
			$expression = preg_replace('|(?<![a-z0-9_-])(\[@id=)|i', '*$1', $expression);
			
			$expression = preg_replace_callback(
				'/\[@?([a-z0-9_-]+)=([\'"])((?!\2|\\\2).*?)\2\]/i',
				function ($matches) {
					return sprintf("[@%s='%s']", strtolower($matches[1]), str_replace("'", "\\'", $matches[3]));
				},
				$expression
			);
			
			$expression = preg_replace_callback(
				'/\[([a-z0-9_-]+)~=([\'"])((?!\2|\\\2).*?)\2\]/i',
				function ($matches) {
					return "[contains(concat(' ', normalize-space(@" . strtolower($matches[1]) . "), ' '), ' " . $matches[3] . " ')]";
				},
				$expression
			);
			
			$expression = preg_replace_callback(
				'/\[([a-z0-9_-]+)\*=([\'"])((?!\2|\\\2).*?)\2\]/i',
				function ($matches) {
					return "[contains(@" . strtolower($matches[1]) . ", '" . $matches[3] . "')]";
				},
				$expression
			);
			
			if (false === strpos($expression, "[@")) {
				$expression = preg_replace(
					'|\.([a-z][a-z0-9_-]*)|i',
					"[contains(concat(' ', normalize-space(@class), ' '), ' \$1 ')]",
					$expression
				);
			}
			
			$expression = str_replace('**', '*', $expression);
			return $expression;
		};
		if (strstr($path, ',')) {
			$paths	   = explode(',', $path);
			$expressions = [];
			foreach ($paths as $path) {
				$xpath = static::CSS_to_Xpath(trim($path));
				if (is_string($xpath)) {
					$expressions[] = $xpath;
				} elseif (is_array($xpath)) {
					$expressions = array_merge($expressions, $xpath);
				}
			}
			return implode('|', $expressions);
		}

		do {
			$placeholder = '{' . uniqid(mt_rand(), true) . '}';
		} while (strpos($path, $placeholder) !== false);
		$path = preg_replace_callback(
			'/\[\S+?([\'"])((?!\1|\\\1).*?)\1\]/',
			function ($matches) use ($placeholder) {
				return str_replace($matches[2], preg_replace('/\s+/', $placeholder, $matches[2]), $matches[0]);
			},
			$path
		);

		$paths	= ['//'];
		$path	 = preg_replace('|\s*>\s*|', '>', $path);
		$segments = preg_split('/\s+/', $path);
		$segments = str_replace($placeholder, ' ', $segments);

		foreach ($segments as $key => $segment) {
			$pathSegment = $_tokenize($segment);
			if (0 == $key) {
				if (0 === strpos($pathSegment, '[contains(')) {
					$paths[0] .= '*' . ltrim($pathSegment, '*');
				} else {
					$paths[0] .= $pathSegment;
				}
				continue;
			}
			if (0 === strpos($pathSegment, '[contains(')) {
				foreach ($paths as $pathKey => $xpath) {
					$paths[$pathKey] .= '//*' . ltrim($pathSegment, '*');
					$paths[]	  = $xpath . $pathSegment;
				}
			} else {
				foreach ($paths as $pathKey => $xpath) {
					$paths[$pathKey] .= '//' . $pathSegment;
				}
			}
		}

		if (1 == count($paths)) {
			return $paths[0];
		}
		return implode('|', $paths);
	}
}

final class DOMNodeHelper {
	public static function innerHTML(DOMNode &$node) : string {
		$owner = $node->ownerDocument;
		$Return = [];
		foreach($node->childNodes as $n) {
			$Return[] = $owner->saveHTML($n);
		}
		return implode('', $Return);
	}

	public static function outerHTML(DOMNode &$node) : string {
		return $node->ownerDocument->saveHTML($node);
	}

	public static function xpath(DOMNode &$node, string $expr, int ...$items) {
		$xpath = new DOMXPath($node->ownerDocument);
		$path = $node->getNodePath();
		$nodes = $xpath->query($path . $expr);
		if($nodes === FALSE) {
			return NULL;
		}
		$icount = count($items);
		switch(TRUE) {
			case ($icount === 1):
				return $nodes->item($items[0]);
				break;
			case ($icount > 1):
				$ret = [];
				$ncount = $nodes->length;
				foreach($items as $item) {
					if($item >= 0) {
						$ret[] = $nodes->item($item);
					} else {
						$ret[] = $nodes->item($ncount + $item);
					}
				}
				return $ret;
				break;
			default:
				return $nodes;
				break;
		}
	}

	public static function querySelector(DOMNode &$node, string $selector, int ...$items) {
		return static::xpath($node, HTML_Scraper::CSS_to_Xpath($selector), ...$items);
	}

	public static function getChildNode(DOMNode &$node, int ...$indexes) {
		$children = $node->childNodes;
		$icount = count($indexes);
		switch(TRUE) {
			case ($icount === 0):
				return $children;
				break;
			case ($icount === 1):
				return $children->item($indexes[0]);
				break;
			default:
				$ret = [];
				$ncount = $children->length;
				foreach($indexes as $index) {
					if($index >= 0) {
						$ret[] = $children->item($index);
					} else {
						$ret[] = $children->item($ncount + $index);
					}
				}
				return $ret;
				break;
		}
	}

	public static function getChildElements(DOMNode &$node, int ...$indexes) : array {
		$childNodes = $node->childNodes;
		$len = $childNodes->length;
		$childElements = [];
		for($i = 0; $i < $len; $i++) {
			if($childNodes[$i]->nodeType === XML_ELEMENT_NODE) {
				$childElements[] = &$childNodes[$i];
			}
		}
		if(empty($indexes)) {
			return $childElements;
		}
		$length = count($childElements);
		$Return = [];
		foreach ($indexes as $index) {
			if(0 <= $index && $index < $length) {
				$Return[] = &$childElements[$index];
			} elseif ($index < 0 && -$length <= $index) {
				$Return[] = &$childElements[$length + $index];
			} else {
				$Return[] = NULL;
			}
		}
		return $Return;
	}

	public static function remove_self(DOMNode &$node) {
		$node->parentNode->removeChild($node);
	}

	public static function filter_child_elements_xpath(DOMNode &$node, string ...$exprs) {
		$xpath = new DOMXPath($node->ownerDocument);
		$path = $node->getNodePath();
		foreach($exprs as $expr) {
			$nodes = $xpath->query($path . $expr);
			if($nodes !== NULL) {
				$nodes = iterator_to_array($nodes);
				foreach($nodes as &$node) {
					$node->parentNode->removeChild($node);
				}
			}
		}
	}

	public static function filter_child_elements_querySelector(DOMNode &$node, string ...$selectors) {
		$exprs = [];
		foreach($selectors as $selector) {
			$exprs[] = HTML_Scraper::CSS_to_Xpath($selector);
		}
		static::filter_child_elements_xpath($node, ...$exprs);
	}

	public static function filter_child_elements_index(DOMNode &$node, int ...$indexes) {
		$elements = static::getChildElements($node, ...$indexes);
		foreach($elements as &$element) {
			$element->parentNode->removeChild($element);
		}
	}
}
?>