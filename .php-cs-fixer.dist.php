<?php

$finder = (new PhpCsFixer\Finder())
	->in(__DIR__ . '/src')
	->exclude('vendor');

return (new PhpCsFixer\Config())
	->setRules([
		'@Symfony' => true,
		'yoda_style' => false,
		'blank_line_after_opening_tag' => false,
		'indentation_type' => true,
		'single_line_comment_style' => [
			'comment_types' => [], // Don't convert any comment types
		],
		'phpdoc_to_comment' => false, // Keep /** @var ... */ annotations for PHPStan
	])
	->setFinder($finder)
	->setIndent("\t")
	->setLineEnding("\n");
