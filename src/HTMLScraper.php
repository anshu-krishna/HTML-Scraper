<?php
namespace Krishna;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;

class HTMLScraper {
	const Extract_innerHTML = 'innerHTML';
	const Extract_outerHTML = 'outerHTML';
	const Extract_textContent = 'textContent';
	const Extract_textContentTrim = 'textContentTrim';
	
	protected static function map_node($type, &$node) {
		if(!($node instanceof DOMNode)) {
			return null;
		}
		switch(true) {
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
				return null;
				break;
		}
	}

    protected $doc = null;
	
	public function __construct(?string $source_html = null, ?int $options = null) {
		$this->doc = new DOMDocument('1.0', 'utf-8');
		if($source_html !== null) {
			$this->load_HTML_str($source_html, $options);
		}
	}

	public function __toString() : string {
		return $this->doc->saveHTML();
	}

	public function textContent() : string {
		return $this->doc->textContent;
	}
	
    public function load_HTML_str(?string $source, ?int $options = null) : bool {
		$opt = LIBXML_NOERROR | LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED;
		if($options !== null) {
			$opt = $opt | $options;
		}
        // return $this->doc->loadHTML(mb_convert_encoding($source, 'HTML-ENTITIES', 'UTF-8'), $opt);
		return $this->doc->loadHTML($source, $opt);
	}
	
	public function load_HTML_file(string $filename, ?int $options = null, ?array $context = null) : bool {
		$source = ($context === null) ? file_get_contents($filename, false) : file_get_contents($filename, false, stream_context_create($context));
		if($source === false) {
			return false;
		}
		return $this->load_HTML_str($source, $options);
	}

	public function xpath(string $expr, int ...$items) : DOMNode | DOMNodeList | array | null {
		$xpath = new DOMXPath($this->doc);
		$nodes = $xpath->query($expr);
		if($nodes === false) {
			return null;
		}
		$icount = count($items);
		$item = null;
		switch(true) {
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

		if($item !== null) {
			if($item >=0 ) {
				return $nodes->item($item);
			} else {
				return $nodes->item(count($nodes) + $item);
			}
		}
		return $nodes;
	}

	public function querySelector(string $selector, int ...$items) : DOMNode | DOMNodeList | array | null {
		return $this->xpath(static::CSS_to_Xpath($selector), ...$items);
	}

	public function xpath_extract(string | callable $mapper, string $expr, int ...$items) {
		$nodes = $this->xpath($expr, ...$items);
		if($nodes === null) {
			return null;
		}
		switch(true) {
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
					return null;
				}
				break;
			default:
				trigger_error('Invalid mapper value in xpath_extract', E_USER_WARNING);
				return null;
				break;
		}
	}

	public function querySelector_extract(string | callable $mapper, string $selector, int ...$items) {
		$nodes = $this->xpath(static::CSS_to_Xpath($selector), ...$items);
		if($nodes === null) {
			return null;
		}
		switch(true) {
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
					return null;
				}
				break;
			default:
				trigger_error('Invalid mapper value in querySelector_extract', E_USER_WARNING);
				return null;
				break;
		}
	}

	public static function new_from(string | DOMNodeList | DOMNode $source) : static | array {
		switch(true) {
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