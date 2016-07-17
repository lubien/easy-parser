# [Unmaintained] easy-parser
SimpleHTMLDOM abstraction

Note: I made this time ago but never really used. It's not com packagist too. Feel free to fork, enhance or whatever.

## Usage

```php
use Lubien\EasyParser;

$parser = new EasyParser;

// Load
$parser->loadByFile('example.html');
// or
$parser->loadByText('<html>...');

$h1 = $parser->find("html body h1[0]");
$href = $parser->find("body a");
echo $a[0]['attr']['href']; // http://foo.com
```

## License

MIT
