<?php
$path = "/literal/:param1-:param2/literal2";


function makePathRegexp($path) {
    $path = trim($path, "/ \t\n\r\0\x0B");
    $length = strlen($path);
    
    $search = '/(:[a-zA-Z_0-9]+(\*|\+|\?|\()?|\*)/';
    $regexp = '/^\/';
    $offset = 0;
    
    while(preg_match($search, $path, $matches, PREG_OFFSET_CAPTURE, $offset) === 1) {
        $captureOffset = $matches[0][1];
        // Append all of the non-matching content verbatim to the regexp.
        if ($captureOffset !== $offset)
            $regexp .= str_replace("/", "\\/", substr($path, $offset, $captureOffset - $offset));

        // calculate the new offset
        $offset = $captureOffset + strlen($matches[0][0]);
        $pattern = null;
        
        if (isset($matches[2]) && $matches[2][0] === "(") {
            // Extract the provided pattern
            $depth = 1;
            $start = $offset-1;
            
            while($depth > 0 && $offset < $length) {
                if ($path[$offset] === ")" && $path[$offset-1] !== "\\")
                    --$depth;
                else if ($path[$offset] === "(" && $path[$offset-1] !== "\\")
                    ++$depth;
                ++$offset;
            }
            
            if ($depth > 0)
                throw new InvalidArgumentException("Unclosed group in regular expression for parameter '{$matches[1][0]}'");
            
            $pattern = substr($path, $start, $offset - $start);
        }
        
        if ($matches[0][0] === "*")
            $pattern = ".*".($offset !== $length ? "?" : "");
        else if ($pattern === null) {
            $multiplier = isset($matches[2]) && $matches[2][0] === "?" ? "*" : "+";
            $pattern = $offset === $length || $path[$offset] === "/"
                ? "([^\\/]{$multiplier})"
                : "(.{$multiplier}?)";
        }
        
        $regexp .= $pattern;
    }
    
    return $regexp . "\/?$/i";
}

echo makePathRegexp("/:param/*/ci-:next(\\d+)");