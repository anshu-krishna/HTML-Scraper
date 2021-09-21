# HTML Scraper
A set of PHP classes to simplify data extraction from HTML.

---

>Base code for the *CSS_to_Xpath* method in *HTML_Scraper* was cloned from [https://github.com/zendframework/zend-dom](https://github.com/zendframework/zend-dom).  
>Zend Framework
>: [http://framework.zend.com/](http://framework.zend.com/)  
>Repository
>: [http://github.com/zendframework/zf2](http://github.com/zendframework/zf2)  
>Copyright (c) 2005-2015 Zend Technologies USA Inc. [http://www.zend.com](http://www.zend.com)  
>License
>: [https://framework.zend.com/license](https://framework.zend.com/license) New BSD License
---

For *basic* documentation see the DOC file.

### Example
```php
<?php
require_once 'vendor/autoload.php';

use Krishna\DOMNodeHelper;
use Krishna\HTMLScraper;

const TrimmedText = HTMLScraper::Extract_textContentTrim;

$doc = new HTMLScraper();

if(!$doc->load_HTML_file('https://www.royalroad.com/fiction/10073/the-wandering-inn')) {
	echo 'Unable to load data';
	exit(1);
}

$data = [];

$data['title'] = $doc->querySelector_extract(TrimmedText, 'div.fic-title h1[property="name"]', 0);

$data['url'] = $doc->xpath_extract(function($meta) {
	return $meta->getAttribute('content');
}, '//meta[@property="og:url"]', 0);

$data['description'] = htmlspecialchars($doc->querySelector_extract(function(&$div) {
	return trim(DOMNodeHelper::innerHTML($div));
}, 'div.description div[property="description"]', 0));

$data['tags'] = $doc->querySelector_extract(TrimmedText, 'span.tags span[property="genre"]');

var_dump($data);
```