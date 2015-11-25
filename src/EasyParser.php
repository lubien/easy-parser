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
    	if ($this->dom === false)
            return false;

        $actions = explode(' ', $selector);

    	$target = $this->dom;
    	$acumulator = [];
        $single_target = false;

    	foreach ($actions as $i => $action) {
    		$act = $this->actionInterpreter($action);

    		$acumulator[] = $act['query'];

    		if ($act['index'] !== -1) {
    			$target = $target->find(implode(' ', $acumulator), $act['index']);
    			$acumulator = [];

                if ($i === (count($actions)-1))
                    $single_target = true;
    		}
    	}

        if ($single_target === false) {
            $resp = [];

            foreach ($target->find(implode(' ', $acumulator)) as $tag) {
                $resp[] = $this->returnTag($tag);
            }

            return $resp;
        } else {
            return $this->returnTag($target);
        }
    }

    /**
     * Take a selector string and parses into a SimpleHTMLDOM understandable string
     * @param  string $action Receive a string containing desired parsing target
     * @return array          A query for SimpleHTMLDOM and a tag index. -1 for all tags
     */
    private function actionInterpreter($action)
    {
        // Separate in two parts the input query
        // $parts[1] : tag name like 'body', 'a', 'h1'
        // $parts[2] : a string containing all attribute's selectors like
        //             '[id=foo]', '[class=panel]', '[hasAttr]'
    	preg_match("/([.#a-z-A-Z0-9]+)?(\[(.*)\])?/", $action, $parts);

    	$query = $parts[1];

        // Below, we will match a numeric attribute that represents a searching index
        // If you search for 'p[0]' you will get the first paragraph. 'p[1]' for second
        // If $index keeps at -1, we will search for all results
        $index = -1;

    	if (!empty($parts[2])) {
            // Try to match a index
            if (preg_match("/\[([0-9]+)\]/", $parts[2], $matched_index)) {
                // Update index
                $index = $matched_index[1];
                // Remove this index from attributes because SimpleHTMLDOM doesn't understand it
                $parts[2] = str_replace($matched_index[0], '', $parts[2]);
            }

	    	$query .= $parts[2];
    	}

        // Return an array to be understood by $this->find()
    	return [
    		'query' => $query,
    		'index' => $index
    	];
    }

    /**
     * Return an array of values of data from a HTML tag
     * @param  Object $node A node came from a SimpleHTMLDOM search
     * @return array        Data scrapped from this tag
     */
    private function returnTag($node)
    {
        return [
            'innertext' => $node->innertext,
            'plaintext' => $node->plaintext,
            'attr' => $node->attr
        ];
    }
}