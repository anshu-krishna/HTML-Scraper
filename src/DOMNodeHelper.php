<?php
namespace Krishna;

use DOMNode;
use DOMNodeList;
use DOMXPath;

class DOMNodeHelper {
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

	public static function xpath(DOMNode &$node, string $expr, int ...$items) : DOMNode | DOMNodeList | array | null {
		$xpath = new DOMXPath($node->ownerDocument);
		$path = $node->getNodePath();
		$nodes = $xpath->query($path . $expr);
		if($nodes === false) {
			return null;
		}
		$icount = count($items);
		switch(true) {
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

	public static function querySelector(DOMNode &$node, string $selector, int ...$items) : DOMNode | DOMNodeList | array | null  {
		return static::xpath($node, HTMLScraper::CSS_to_Xpath($selector), ...$items);
	}

	public static function getChildNode(DOMNode &$node, int ...$indexes) : DOMNode | DOMNodeList | array | null  {
		$children = $node->childNodes;
		$icount = count($indexes);
		switch(true) {
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
				$Return[] = null;
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
			if($nodes !== null) {
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
			$exprs[] = HTMLScraper::CSS_to_Xpath($selector);
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