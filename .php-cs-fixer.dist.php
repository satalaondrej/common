<?php

$finder = (new PhpCsFixer\Finder())
	->in(__DIR__ . '/src')
	->in(__DIR__ . '/tests')
	->exclude('vendor');

return (new PhpCsFixer\Config())
	->setRules([
		'@Symfony' => true,
		'yoda_style' => false,
		'blank_line_after_opening_tag' => false,
		'indentation_type' => true,
	])
	->setFinder($finder)
	->setIndent("\t")
	->setLineEnding("\n");
