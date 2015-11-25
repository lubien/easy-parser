<?php
namespace Lubien\EasyParser;

use Sunra\PhpSimple\HtmlDomParser;

class EasyParser
{
    private $dom = false;

    /**
     * Load an instance of SimpleHTMLDom by an HTML string
     * @param  string $html HTML string
     * @return bool 		True when $this->dom is populated
     */
    public function loadByText($html='')
    {
    	if (empty($html))
    		return false;

    	$this->dom = HtmlDomParser::str_get_html($html);

    	return !empty($this->dom) ? true : false;
    }

    /**
     * Load an instance of SimpleHTMLDom by an HTML URL path
     * @param  string $file Path to an HTML file
     * @return bool 		True when $this->dom is populated
     */
    public function loadByFile($file='')
    {
    	$html = @file_get_contents($file);

    	return $this->loadByText($html);
    }

    public function find($selector='')
    {
    	$actions = explode(' ', $selector);

    	$target = $this->dom;
    	$acumulator = [];

    	foreach ($actions as $i => $action) {
    		$foo = $this->search($action);
    		$acumulator[] = $foo['query'];

    		if ($foo['index'] !== -1) {
    			$target = $target->find(implode(' ', $acumulator), $foo['index']);
    			$acumulator = [];
    		}
    	}

    	return [
    		'innertext' => $target->innertext,
    		'plaintext' => $target->plaintext,
    	];
    }

    private function search($action)
    {
    	preg_match("/([.#a-z-A-Z0-9]+)?(\[(.*)\])?/", $action, $parts);

    	$query = strtolower($parts[1]);
    	$index = -1;

    	if (!empty($parts[2])) {
    		$attributes = ltrim($parts[2], '[');
    		$attributes = rtrim($attributes, ']');
    		$attributes = explode('][', $attributes);

    		$attributes_str = '';

	    	foreach ($attributes as $attr) {
	    		if (is_numeric($attr)) {
	    			$index = $attr;
	    			continue;
	    		} else {
	    			$attributes_str .= '[' . $attr . ']';
	    		}
	    	}

	    	$query .= $attributes_str;
    	}

    	return [
    		'query' => $query,
    		'index' => $index
    	];
    }
}