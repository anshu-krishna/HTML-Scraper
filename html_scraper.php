<?php
/*
Author: Anshu Krishna
Contact: anshu.krishna5@gmail.com
Date: 16-Nov-2018
Description: A PHP class to simplify data extraction from HTML.
*/
class HTML_Scraper {
	protected $doc;
	
	public function __construct(string $source = NULL, bool $utf = TRUE) {
		$this->doc = new DOMDocument;
		if($source !== NULL) {
			$this->load_HTML_str($source, $utf);
		}
	}
	
	public function __toString() : string {
		return $this->doc->saveHTML();
	}
	
	public function getBody() : string {
		return static::innerHTML($this->xpath('//body', 0));
	}
	
	public function getHead() : string {
		return static::innerHTML($this->xpath('//head', 0));
	}
	
	public function load_HTML_str(string $source, bool $utf = TRUE) : bool {
		if($utf) {
			$source = '<?xml encoding="utf-8" ?>' . $source;
		}
		// return $this->doc->loadHTML($source, LIBXML_NOERROR | LIBXML_NOBLANKS);
		return $this->doc->loadHTML($source, LIBXML_NOERROR);
	}
	
	public function load_HTML_file(string $filename, bool $utf = TRUE, resource $context = NULL) : bool {
		$source = file_get_contents($filename, $utf, $context);
		if($source === FALSE) {
			return FALSE;
		}
		return $this->load_HTML_str($source);
	}

	public function xpath(string $expr, int $item = NULL) {
		$xpath = new DOMXPath($this->doc);
		$nodes = @$xpath->query($expr);
		if($nodes === FALSE) {
			return NULL;
		}
		if($item !== NULL) {
			return $nodes->item($item);
		}
		return $nodes;
	}

	public function querySelector(string $selector, int $item = NULL) {
		return $this->xpath(static::CSS_to_Xpath($selector), $item);
	}

	public function from_xpath(string $expr, int $item = NULL, bool $utf = TRUE) {
		$nodes = $this->xpath($expr, $item);
		if($nodes === NULL) {
			return NULL;
		}
		return static::from($nodes, $utf);
	}

	public function from_querySelector(string $selector, int $item = NULL, bool $utf = TRUE) {
		return $this->from_xpath(static::CSS_to_Xpath($selector), $item, $utf);
	}

	public function xpath_innerHTML(string $expr, int $item = 0) {
		$node = $this->xpath($expr, $item);
		if($node === NULL) {
			return NULL;
		} else {
			return static::innerHTML($node);
		}
	}

	public function xpath_outerHTML(string $expr, int $item = 0) {
		$node = $this->xpath($expr, $item);
		if($node === NULL) {
			return NULL;
		} else {
			return static::outerHTML($node);
		}
	}

	public function xpath_textContent(string $expr, int $item = 0) {
		$node = $this->xpath($expr, $item);
		if($node === NULL) {
			return NULL;
		} else {
			return $node->textContent;
		}
	}

	public function querySelector_innerHTML(string $selector, int $item = 0) {
		$node = $this->xpath(static::CSS_to_Xpath($selector), $item);
		if($node === NULL) {
			return NULL;
		} else {
			return static::innerHTML($node);
		}
	}

	public function querySelector_outerHTML(string $selector, int $item = 0) {
		$node = $this->xpath(static::CSS_to_Xpath($selector), $item);
		if($node === NULL) {
			return NULL;
		} else {
			return static::outerHTML($node);
		}
	}

	public function querySelector_textContent(string $selector, int $item = 0) {
		$node = $this->xpath(static::CSS_to_Xpath($selector), $item);
		if($node === NULL) {
			return NULL;
		} else {
			return $node->textContent;
		}
	}

	public static function outerHTML(DOMNode $node) : string {
		return $node->ownerDocument->saveHTML($node);
	}

	public static function innerHTML(DOMNode $node) : string {
		$owner = $node->ownerDocument;
		$Return = [];
		foreach($node->childNodes as $n) {
			$Return[] = $owner->saveHTML($n);
		}
		return implode('', $Return);
	}

	public static function from($source, bool $utf = TRUE) {
		if($source instanceof DOMNodeList) {
			$Return = [];
			foreach($source as $n) {
				$Return[] = new static($n->ownerDocument->saveHTML($n), $utf);
			}
			return $Return;
		} elseif ($source instanceof DOMNode) {
			return new static($source->ownerDocument->saveHTML($source), $utf);
		} elseif (is_string($source)) {
			return new static($source, $utf);
		}
		return new static();
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
?>