<?php
// function echo_html(string $html_source) {
// 	echo '<pre>', htmlentities($html_source), '</pre>';
// }

// function echo_DOMNode(DOMNode $node) {
// 	echo_html(DOMNodeHelper::outerHTML($node));
// }

require_once 'HTML_Scraper.php';

$doc = new HTML_Scraper;

if(!$doc->load_HTML_file('sample_data_file.html')) {
	echo 'Unable to load data';
	exit(1);
}

// $context = [
// 	"ssl" => ["verify_peer" => FALSE, "verify_peer_name" => FALSE]
// ];
// if(!$doc->load_HTML_file('https://www.royalroad.com/fiction/10073/the-wandering-inn', NULL, $context)) {
// 	echo 'Unable to load data';
// 	exit(1);
// }

$data = [];

$data['title'] = $doc->querySelector_extract('textContentTrim', 'div.fic-title h1[property="name"]', 0);

$data['url'] = $doc->xpath_extract(function($meta) {
	return $meta->getAttribute('content');
}, '//meta[@property="og:url"]', 0);

$data['auth'] = $doc->querySelector_extract('textContentTrim', 'div.fic-title h4[property="author"] span[property="name"]', 0);

$data['auth_link'] = $doc->querySelector_extract(function(&$a) {
	return 'https://www.royalroad.com' . $a->getAttribute('href');
}, 'div.fic-title h4[property="author"] span[property="name"] a', 0);

$data['cover'] = $doc->xpath_extract(function($meta) {
	return $meta->getAttribute('content');
}, '//meta[@property="og:image"]', 0);

if($data['cover'] !== NULL && strpos($data['cover'], 'nocover.png') !== FALSE) {
	$data['cover'] = NULL;
}

$data['chaps'] = NULL;

$data['words'] = $doc->querySelector_extract(function(&$li) {
	$pages = filter_var(str_replace(',', '', trim(DOMNodeHelper::innerHTML($li))), FILTER_VALIDATE_INT);
	if($pages === FALSE) {
		return NULL;
	}
	return 275 * $pages;
}, 'li[property="numberOfPages"]', 0);

$data['desc'] = $doc->querySelector_extract(function(&$div) {
	return trim(DOMNodeHelper::innerHTML($div));
}, 'div.description div[property="description"]', 0);

$data['tags'] = $doc->querySelector_extract('textContentTrim', 'span.tags span[property="genre"]');

$replace = NULL;
if($data['url'] !== NULL && preg_match("/http[s]?:\/\/www\.royalroad\.com\/(.+)\/?/", $data['url'], $mtc)) {
	$replace = "/{$mtc[1]}/chapter/";
	$data['ch_link_base'] = "https://www.royalroad.com{$replace}[ch_index]";
}

$data['ch_links'] = $doc->querySelector_extract(function(&$row) use ($replace) {
	list($a1, $a2) = DOMNodeHelper::xpath($row, '//a', 0, 1);
	$link = $a1->getAttribute('href');
	if($replace !== NULL) {
		$link = str_replace($replace, '', $link);
	}
	// return [
	// 	'link' => $link,
	// 	'title' => trim($a1->textContent),
	// 	'date' => trim($a2->textContent)
	// ];
	return [$link, trim($a1->textContent), trim($a2->textContent)];
}, '#chapters tbody tr');

if(is_array($data['ch_links'])) {
	$data['chaps'] = count($data['ch_links']);
}

var_dump($data);
?>