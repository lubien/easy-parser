<?php
use Lubien\EasyParser\EasyParser;

class EasyParserTest extends PHPUnit_Framework_TestCase
{
	// Path to a static HTML file used in tests
	public $example_dot_com = __DIR__ . '/assets/example.html';

	public function testLoading()
	{
		$parser = new EasyParser;

		// Load using a string
		$html = file_get_contents($this->example_dot_com);

		$this->assertTrue($parser->loadByText($html));

		// Load using a file path
		$this->assertTrue($parser->loadByFile($this->example_dot_com));

		// Fail to load a empty string
		$this->assertFalse($parser->loadByText(''));

		// Fail to load a file that doesn't exists
		$this->assertFalse($parser->loadByFile('hue.html'));
	}

	public function testParsing()
	{
		$parser = new EasyParser;
		$parser->loadByFile($this->example_dot_com);

		// Find a <h1> in different ways
		$h1 = $parser->find("html body h1[0]");
		$this->assertEquals('Example Domain', $h1['plaintext']);

		$h1 = $parser->find("HTML[0] BODY[0] H1[0]");
		$this->assertEquals('Example Domain', $h1['plaintext']);

		// Find an <a>
		$a = $parser->find("a[0]");
		$this->assertEquals('More information...', $a['plaintext']);
	}
}