<?php
require_once 'html_scraper.php';

function echo_html(...$htmls) {
	foreach ($htmls as $html) {
		echo '<pre>', htmlspecialchars((string) $html), '</pre>';
	}
}

$doc = new HTML_Scraper;

$doc->load_HTML_file('sample_data_file.html');
// $doc->load_HTML_file('https://www.royalroad.com/fiction/10073/the-wandering-inn');

// echo_html($doc);

// {
// 	// $nodes = $doc->xpath('//*[@id="chapters"]//tbody//a');
// 	$nodes = $doc->querySelector('#chapters tbody a');

// 	foreach($nodes as $n) {
// 		var_dump($n->getAttribute('href'));
// 		// echo_html(HTML_Scraper::from($n)->getbody());
// 	}
// }

$rows = $doc->querySelector('#chapters tbody tr');
$rows = HTML_Scraper::from($rows);
echo '<pre>';
$index = 0;
foreach ($rows as $row) {
	$link = $row->querySelector('td a', 0);
	printf("Link: %03d\t%s\n\t\t%s\n\n", ++$index, trim($link->textContent), $link->getAttribute('href'));
}
echo '</pre>';
?>