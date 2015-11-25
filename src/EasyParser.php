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

    /**
     * Find HTML tags by an input text like 'html a[0]'
     * @param  string $selector Pattern to search in the DOM
     * @return array            An array of a single DOM tag or multiple tags
     */
    public function find($selector='')
    {
        // Requires the DOM to be populated
    	if ($this->dom === false)
            return false;

        // Separate selectors by spaces
        $actions = explode(' ', $selector);

        // $target : starting as the DOM itself, we will strict it until we find our final tag(s)
        // $acumulator : instead of searching for each tag, if you don't specify a index, we
        //               will store actions. This means that 'body header[0] p a span[0]' will
        //               separate execution into 'body header[0]' and 'p a span[0]', two searchs
        //               instead of all 5. It'll sure faster our script
        // $single_target : this will tell us if at the end we need one or more tags results
    	$target = $this->dom;
    	$acumulator = [];
        $single_target = false;

    	foreach ($actions as $i => $action) {
            // Interpret our text input into SimpleHTMLDOM undestandable values
    		$act = $this->actionInterpreter($action);

            // Add this query to our acumulator
    		$acumulator[] = $act['query'];

            // Only searches when we specify an index higher than -1
    		if ($act['index'] > -1) {
                // SimpleHTMLDOM to search in the DOM. As longs as we keep storing
                // results in $target, our result will get closer to our desired tag(s)
    			$target = $target->find(implode(' ', $acumulator), $act['index']);
                // Reset our acumulator
    			$acumulator = [];

                // If this was our last action and we know that we just searched in
                // the DOM, we make $single_target true. That means 'this was our last search'
                if ($i === (count($actions)-1))
                    $single_target = true;
    		}
    	}

        // If we don't made a 'last search' like explained above, that means we have to parse
        // all tags instead of one
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