<?php

declare(strict_types=1);

/**
 * Class Minify_CSS_Compressor
 * @package Minify
 */

/**
 * Compress CSS
 *
 * This is a heavy regex-based removal of whitespace, unnecessary
 * comments and tokens, and some CSS value minimization, where practical.
 * Many steps have been taken to avoid breaking comment-based hacks,
 * including the ie5/mac filter (and its inversion), but expect tricky
 * hacks involving comment tokens in 'content' value strings to break
 * minimization badly. A test suite is available.
 *
 * Note: This replaces a lot of spaces with line breaks. It's rumored
 * (https://github.com/yui/yuicompressor/blob/master/README.md#global-options)
 * that some source control tools and old browsers don't like very long lines.
 * Compressed files with shorter lines are also easier to diff. If this is
 * unacceptable please use CSSmin instead.
 *
 * @package Minify
 * @author Stephen Clay <steve@mrclay.org>
 * @author http://code.google.com/u/1stvamp/ (Issue 64 patch)
 *
 * @deprecated Use CSSmin (tubalmartin/cssmin)
 */
class Minify_CSS_Compressor
{
    /**
     * Minify a CSS string
     *
     * @param string $css
     *
     * @param array  $options (currently ignored)
     *
     * @return string
     */
    public static function process($css, $options = [])
    {
        return (new Minify_CSS_Compressor($options))->_process($css);
    }

    /**
     * @var array
     */
    protected $_options = null;

    /**
     * Are we "in" a hack? I.e. are some browsers targetted until the next comment?
     *
     * @var bool
     */
    protected $_inHack = false;

    /**
     * Constructor
     *
     * @param array $options (currently ignored)
     */
    private function __construct($options)
    {
        $this->_options = $options;
    }

    /**
     * Minify a CSS string
     *
     * @param string $css
     *
     * @return string
     */
    protected function _process($css)
    {
        $css = str_replace("\r\n", "\n", $css);

        // preserve empty comment after '>'
        // http://www.webdevout.net/css-hacks#in_css-selectors
        $css = preg_replace('@>/\\*\\s*\\*/@', '>/*keep*/', $css);

        // preserve empty comment between property and value
        // http://css-discuss.incutio.com/?page=BoxModelHack
        $css = preg_replace('@/\\*\\s*\\*/\\s*:@', '/*keep*/:', $css);
        $css = preg_replace('@:\\s*/\\*\\s*\\*/@', ':/*keep*/', $css);

        // apply callback to all valid comments (and strip out surrounding ws
        $pattern = '@\\s*/\\*([\\s\\S]*?)\\*/\\s*@';
        $css     = preg_replace_callback($pattern, [$this, '_commentCB'], $css);

        // remove ws around { } and last semicolon in declaration block
        $css = preg_replace('/\\s*{\\s*/', '{', $css);
        $css = preg_replace('/;?\\s*}\\s*/', '}', $css);

        // remove ws surrounding semicolons
        $css = preg_replace('/\\s*;\\s*/', ';', $css);

        // remove ws around urls
        $pattern = '/
                url\\(      # url(
                \\s*
                ([^\\)]+?)  # 1 = the URL (really just a bunch of non right parenthesis)
                \\s*
                \\)         # )
            /x';
        $css     = preg_replace($pattern, 'url($1)', $css);

        // remove ws between rules and colons
        $pattern = '/
                \\s*
                ([{;])              # 1 = beginning of block or rule separator
                \\s*
                ([\\*_]?[\\w\\-]+)  # 2 = property (and maybe IE filter)
                \\s*
                :
                \\s*
                (\\b|[#\'"-])        # 3 = first character of a value
            /x';
        $css     = preg_replace($pattern, '$1$2:$3', $css);

        // remove ws in selectors
        $pattern = '/
                (?:              # non-capture
                    \\s*
                    [^~>+,\\s]+  # selector part
                    \\s*
                    [,>+~]       # combinators
                )+
                \\s*
                [^~>+,\\s]+      # selector part
                {                # open declaration block
            /x';
        $css     = preg_replace_callback($pattern, [$this, '_selectorsCB'], $css);

        // minimize hex colors
        $pattern = '/([^=])#([a-f\\d])\\2([a-f\\d])\\3([a-f\\d])\\4([\\s;\\}])/i';
        $css     = preg_replace($pattern, '$1#$2$3$4$5', $css);

        // remove spaces between font families
        $pattern = '/font-family:([^;}]+)([;}])/';
        $css     = preg_replace_callback($pattern, [$this, '_fontFamilyCB'], $css);

        $css = preg_replace('/@import\\s+url/', '@import url', $css);

        // replace any ws involving newlines with a single newline
        $css = preg_replace('/[ \\t]*\\n+\\s*/', "\n", $css);

        // separate common descendent selectors w/ newlines (to limit line lengths)
        $pattern = '/([\\w#\\.\\*]+)\\s+([\\w#\\.\\*]+){/';
        $css     = preg_replace($pattern, "$1\n$2{", $css);

        // Use newline after 1st numeric value (to limit line lengths).
        $pattern = '/
            ((?:padding|margin|border|outline):\\d+(?:px|em)?) # 1 = prop : 1st numeric value
            \\s+
            /x';
        $css     = preg_replace($pattern, "$1\n", $css);

        // prevent triggering IE6 bug: http://www.crankygeek.com/ie6pebug/
        $css = preg_replace('/:first-l(etter|ine)\\{/', ':first-l$1 {', $css);

        return trim($css);
    }

    /**
     * Replace what looks like a set of selectors
     *
     * @param array $m regex matches
     *
     * @return string
     */
    protected function _selectorsCB($m)
    {
        // remove ws around the combinators
        return preg_replace('/\\s*([,>+~])\\s*/', '$1', $m[0]);
    }

    /**
     * Process a comment and return a replacement
     *
     * @param array $m regex matches
     *
     * @return string
     */
    protected function _commentCB($m)
    {
        $hasSurroundingWs = (trim($m[0]) !== $m[1]);
        $m                = $m[1];
        // $m is the comment content w/o the surrounding tokens,
        // but the return value will replace the entire comment.
        if ($m === 'keep') {
            return '/**/';
        }

        if ($m === '" "') {
            // component of http://tantek.com/CSS/Examples/midpass.html
            return '/*" "*/';
        }

        if (preg_match('@";\\}\\s*\\}/\\*\\s+@', $m)) {
            // component of http://tantek.com/CSS/Examples/midpass.html
            return '/*";}}/* */';
        }

        if ($this->_inHack) {
            // inversion: feeding only to one browser
            $pattern = '@
                    ^/               # comment started like /*/
                    \\s*
                    (\\S[\\s\\S]+?)  # has at least some non-ws content
                    \\s*
                    /\\*             # ends like /*/ or /**/
                @x';
            if (preg_match($pattern, $m, $n)) {
                // end hack mode after this comment, but preserve the hack and comment content
                $this->_inHack = false;

                return "/*/{$n[1]}/**/";
            }
        }

        if (str_ends_with($m, '\\')) { // comment ends like \*/
            // begin hack mode and preserve hack
            $this->_inHack = true;

            return '/*\\*/';
        }

        if ($m !== '' && $m[0] === '/') { // comment looks like /*/ foo */
            // begin hack mode and preserve hack
            $this->_inHack = true;

            return '/*/*/';
        }

        if ($this->_inHack) {
            // a regular comment ends hack mode but should be preserved
            $this->_inHack = false;

            return '/**/';
        }

        // Issue 107: if there's any surrounding whitespace, it may be important, so
        // replace the comment with a single space
        return $hasSurroundingWs ? ' ' : ''; // remove all other comments
    }

    /**
     * Process a font-family listing and return a replacement
     *
     * @param array $m regex matches
     *
     * @return string
     */
    protected function _fontFamilyCB($m)
    {
        // Issue 210: must not eliminate WS between words in unquoted families
        $flags  = PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY;
        $pieces = preg_split('/(\'[^\']+\'|"[^"]+")/', $m[1], -1, $flags);
        $out    = 'font-family:';

        while (null !== ($piece = array_shift($pieces))) {
            if ($piece[0] !== '"' && $piece[0] !== "'") {
                $piece = preg_replace('/\\s+/', ' ', $piece);
                $piece = preg_replace('/\\s?,\\s?/', ',', $piece);
            }
            $out .= $piece;
        }

        return $out . $m[2];
    }
}
