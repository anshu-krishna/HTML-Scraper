# Class HTML_Scraper
### Static Functions:
-	`new_from($source)`

	Create a new HTML_Scraper object from the passed source.  
	`$source` can be of type `DOMNodeList`, `DOMNode` or `string`.

	**Returns:**  
	
	| Type | Description |
	|---|---|
	| `array` | When `$source` is an instance of `DOMNodeList` then returns an `array` of `HTML_Scraper` objects. |
	| `HTML_Scraper` | When `$source` is an instance of `DOMNode` or a `string` |


-	`CSS_to_Xpath(string $path) : string`

	Translates CSS selector to XPath expression.

### Functions:
-	`__toString() : string`

	Magic function to convert `HTML_Scraper` into a `string` containing the HTML code of the loaded document.


-	`textContent() : string`

	Get the *textContent* of the loaded HTML document.


-	`load_HTML_str(string $source, int $options = NULL) : bool`

	Load HTML from a string.

	-	`$options`  
		It is for passing LIBXML constant flags. `LIBXML_NOERROR | LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED` is always applied (even when `$options` is `NULL`).

	Returns `TRUE` on success and `FALSE` on failure.


-	`load_HTML_file(string $filename, int $options = NULL, array $context = NULL) : bool`

	Load HTML from a file.

	-	`$options`  
		*see `$options` in `HTML_Scraper->load_HTML_str()`*

	-	`$context`  
		*see `$context` in `stream_context_create()`*

	Returns `TRUE` on success and `FALSE` on failure.


-	`xpath(string $expr, int ...$items)`

	Get `DOMNode` that match the passed *XPath* path expression.

	-	`$items`  
		Index of the `DOMNode` to be returned in the `DOMNodeList` matching the *XPath* path expression.  
		It is 0-indexed. (*i.e.* to get first node use `0`, for second node use `1` and so on).  
		Negative values can be used for referencing the list item from the end. (*i.e.* use `-1` for last node, `-2` for second last node and so on).  
		If invalid index is used `NULL` is returned. (*i.e.* if only two nodes match the *XPath* path expression then using 3 will return `NULL`).

	**Returns:**  
	| Type | Description |
	|---|---|
	| `NULL` | When no nodes matches the XPath path expression |
	| `DOMNodeList` | When no `...$items` are passed |
	| `DOMNode` | When only one `...$items` is passed |
	| `array` | When more than one `...$items` are passed. Array contains `DOMNode` or `NULL` |

	Returns `DOMNodeList` (or `DOMNode` when `$item` index is specified) that matches the specified *XPath* path expression.


-	`querySelector(string $selector, int ...$items)`
	
	Same as `HTML_Scraper->xpath()` except that it uses CSS selector instead of *XPath* path expression.

-	`xpath_extract($mapper, string $expr, int ...$items)`

	Find `DOMNode`(s) in the same way as in `HTML_Scraper->xpath()` then extract data from the `DOMNode`(s) as specified by the `$mapper`.

	-	`$mapper`  
		It can be any one of the `string` specified below or a `function` that takes a `DOMNode` and returns any extracted value.  
		| Mapper Value | Description |
		|---|---|
		| `'innerHTML'` | Maps `DOMNode` to its *innerHTML* |
		| `'outerHTML'` | Maps `DOMNode` to its *outerHTML* |
		| `'textContent'` | Maps `DOMNode` to its *textContent* |
		| `'textContentTrim'` | Maps `DOMNode` to its *textContent* without any whitespaces at the beginning or at the end of the *textContent* |

-	`querySelector_extract($mapper, string $selector, int ...$items)`

	Same as `HTML_Scraper->xpath_extract()` except that it uses CSS selector instead of *XPath* path expression.

---

# Class DOMNodeHelper

### Static Functions:

-	`innerHTML(DOMNode &$node) : string`

	Returns *innerHTML* of the passed `DOMNode`.


-	`outerHTML(DOMNode &$node) : string`

	Returns *outerHTML* of the passed `DOMNode`.


-	`xpath(DOMNode &$node, string $expr, int ...$items)`

	Similar to `HTML_Scraper->xpath()` except that it works on a `DOMNode` instead of the `HTML_Scraper`'s `DOMDocument`.

-	`querySelector(DOMNode &$node, string $selector, int ...$items)`

	Similar to `DOMNodeHelper::xpath()` except it uses CSS selector instead of a *XPath* path expression.

-	`getChildNode(DOMNode &$node, int ...$indexes)`

	Get one or more child nodes of the `DOMNode`.

	-	`$indexes`  
		*See `$items` in `HTML_Scraper->expath()`.*

	**Returns:**

	| Type | Description |
	|---|---|
	| `DOMNodeList` | When no `...$indexes` is passed |
	| `DOMNode` | When only one `...$indexes` is passed |
	| `array` | When more that one `...$indexes` is passed. Array contains `DOMNode` or `NULL` |


-	`getChildElements(DOMNode &$node, int ...$indexes) : array`

	Same as `DOMNode::getChildNode()` except that it works on child **elements** instead of child **nodes**.

-	`remove_self(DOMNode &$node)`

	Removes the `DOMNode` from its parent `DOMDocument`.

-	`filter_child_elements_xpath(DOMNode &$node, string ...$exprs)`

	Removes the child elements of the passed `DOMNode` that match the passed *XPath* path expression(s).

-	`filter_child_elements_querySelector(DOMNode &$node, string ...$selectors)`

	Removes the child elements of the passed `DOMNode` that match the passed CSS selector(s).

-	`filter_child_elements_index(DOMNode &$node, int ...$indexes)`

	Removes the child elements of the passed `DOMNode` specified by the `...$indexes`.

	-	`$indexes`  
		*See `$items` in `HTML_Scraper->expath()`.*