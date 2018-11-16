# HTML Scraper
A PHP class to simplify data extraction from HTML.

---

>Base code for the *CSS_to_Xpath* method in *HTML_Scraper* was cloned from [https://github.com/zendframework/zend-dom](https://github.com/zendframework/zend-dom).
>
>Zend Framework
>: [http://framework.zend.com/](http://framework.zend.com/)
>
>Repository
>: [http://github.com/zendframework/zf2](http://github.com/zendframework/zf2)
>
>Copyright (c) 2005-2015 Zend Technologies USA Inc. [http://www.zend.com](http://www.zend.com)
>
>License
>: [https://framework.zend.com/license](https://framework.zend.com/license) New BSD License
---
## Static methods:
---
-	`CSS_to_Xpath(string $selector) : string`
	
	Translate *CSS* selector to *XPath* path query.

	*Returns:*
	-	`string` containing the equivalent *XPath* path query.
---
-	`from($source [, bool $utf = TRUE])`

	Create new `HTML_Scraper` object from various sources.

	`$source` can be of type
	-	`DOMNodeList`
	-	`DOMNode`
	-	`string` containing HTML

	*Returns:*
	-	`array` of `HTML_Scraper` objects when `$source instanceof DOMNodeList`
	-	`HTML_Scraper` object when `$source instanceof DOMNode`
	-	`HTML_Scraper` object when `$source` is `string`
---
 -	`outerHTML(DOMNode $node) : string`
	
	Extract *outerHTML* from a `DOMNode`

	*Returns:*
	-	`string` containing *outerHTML* of the `DOMNode`
---
-	`innerHTML(DOMNode $node) : string`

	Extract *innerHTML* from a `DOMNode`

	*Returns:*
	-	`string` containing *innerHTML* of the `DOMNode`
---
## Methods:
---
-	`__toString() : string`

	***Magic*** method to convert `HTML_Scraper` object to HTML `string`.
---
-	`from_querySelector(string $selector, int $item = NULL, bool $utf = TRUE)`

	Create `HTML_Scraper` object (or `array` of objects) from `DOMNode` (or `DOMNodeList`) that matches the specified *CSS* selector.

	Returns `NULL` when no match is found.
---
-	`from_xpath(string $expr, int $item = NULL, bool $utf = TRUE)`

	Create `HTML_Scraper` object (or `array` of objects) from `DOMNode` (or `DOMNodeList`) that matches the specified *XPath* path expression.

	Returns `NULL` when no match is found.
---
-	`getBody() : string`

	Get *innerHTML* of `document.body`
---
-	`getHead() : string`

	Get *innerHTML* of `document.head`
---
-	`load_HTML_file(string $filename, bool $utf = TRUE, resource $context = NULL) : bool`

	Load *HTML* text from local or remote file.
	
	Returns `TRUE` on success and `FALSE` on failure.
---
-	`load_HTML_str(string $source, bool $utf = TRUE) : bool`

	Load *HTML* text from `string`.
	
	Returns `TRUE` on success and `FALSE` on failure.
---
-	`querySelector(string $selector, int $item = NULL)`

	Returns `DOMNodeList` (or `DOMNode` when `$item` index is specified) that matches the specified *CSS* selector.
	
	`$item` is *0-indexed*.

	Returns `NULL` when no match is found.
---
-	`querySelector_innerHTML(string $expr, int $item = 0)`

	Returns *innerHTML* of the `DOMNode` that matches the specified *CSS* selector.

	Returns `NULL` when no match is found.
---
-	`querySelector_outerHTML(string $expr, int $item = 0)`

	Returns *outerHTML* of the `DOMNode` that matches the specified *CSS* selector.

	Returns `NULL` when no match is found.
---
-	`querySelector_textContent(string $expr, int $item = 0)`

	Returns *textContent* of the `DOMNode` that matches the specified *CSS* selector.

	Returns `NULL` when no match is found.
---
-	`xpath(string $expr, int $item = NULL)`

	Returns `DOMNodeList` (or `DOMNode` when `$item` index is specified) that matches the specified *XPath* path expression.
	
	`$item` is *0-indexed*.

	Returns `NULL` when no match is found.
---
-	`xpath_innerHTML(string $expr, int $item = 0)`

	Returns *innerHTML* of the `DOMNode` that matches the specified *XPath* path expression.

	Returns `NULL` when no match is found.
---
-	`xpath_outerHTML(string $expr, int $item = 0)`

	Returns *outerHTML* of the `DOMNode` that matches the specified *XPath* path expression.

	Returns `NULL` when no match is found.
---
-	`xpath_textContent(string $expr, int $item = 0)`

	Returns *textContent* of the `DOMNode` that matches the specified *XPath* path expression.

	Returns `NULL` when no match is found.
---
## Example:
```php
<?php
$doc = new HTML_Scraper;
if($doc->load_HTML_file('sample_data_file.html') === TRUE) {
	$title = $doc->querySelector_textContent('.fic-title [property="name"]', 0);
	echo "Fiction name is {$title}.<br />",

	$rows = $doc->querySelector('#chapters tbody tr');
	echo "There are ", count($rows), "chapters. <br />";

	echo "First chapter is called", $doc->querySelector_textContent('#chapters tbody tr a', 0), "<br />";
}
?>
```