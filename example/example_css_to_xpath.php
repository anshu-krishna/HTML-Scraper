<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>CSS to XPath Examples</title>
	<style>
		body {
			display: grid;
			grid-template-columns: max-content 1fr;
		}
		header, span {
			padding: 10px 15px;
			border: 1px solid #ededed;
		}
		header {
			text-align: center;
			font-weight: bold;
		}
		span {
			font-family: monospace;
		}
	</style>
</head>
<body>
<header>CSS</header>
<header>XPath</header>
<?php
require_once '../html_scraper.php';
$examples = [
	'div',
	'div.abc',
	'div.abc.xyz',
	'#mydiv',
	'#mydiv .abc.xyz',
	'div>p>span',
	'input[type="number"]',
	'.fic-title [property="name"]',
	'.fic-title [property="author"] a',
	'.fic-header img',
	'div.description div[property="description"]',
	'#chapters tbody tr',
	'#chapters tbody tr time',
	'#showTags span[property="genre"]',
	'#chapters tbody a'
];

$examples = array_map(function($selector) {
	return implode(PHP_EOL, array_map(function($str) {
		return "<span>" . htmlspecialchars($str) . "</span>";
	}, [$selector, HTML_Scraper::CSS_to_Xpath($selector)]));
}, $examples);

echo implode(PHP_EOL, $examples);
?>
</body>
</html>