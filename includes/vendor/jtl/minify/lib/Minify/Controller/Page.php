<?php

/**
 * Class Minify_Controller_Page
 * @package Minify
 */

declare(strict_types=1);

use JSMin\JSMin;

/**
 * Controller class for serving a single HTML page
 *
 * @link http://code.google.com/p/minify/source/browse/trunk/web/examples/1/index.php#59
 * @package Minify
 * @author Stephen Clay <steve@mrclay.org>
 */
class Minify_Controller_Page extends Minify_Controller_Base
{
    /**
     * @inheritdoc
     */
    public function createConfiguration(array $options): Minify_ServeConfiguration
    {
        if (isset($options['file'])) {
            $sourceSpec = [
                'filepath' => $options['file']
            ];
            $f          = $options['file'];
        } else {
            // strip controller options
            $sourceSpec = [
                'content' => $options['content'],
                'id'      => $options['id'],
            ];
            $f          = $options['id'];
            unset($options['content'], $options['id']);
        }
        // something like "builder,index.php" or "directory,file.html"
        $selectionId = strtr(substr($f, 1 + strlen(dirname($f, 2))), '/\\', ',,');

        if (isset($options['minifyAll'])) {
            // this will be the 2nd argument passed to Minify_HTML::minify()
            $sourceSpec['minifyOptions'] = [
                'cssMinifier' => ['Minify_CSSmin', 'minify'],
                'jsMinifier'  => [JSMin::class, 'minify'],
            ];
            unset($options['minifyAll']);
        }

        $sourceSpec['contentType'] = Minify::TYPE_HTML;
        $sources[]                 = new Minify_Source($sourceSpec);

        return new Minify_ServeConfiguration($options, $sources, $selectionId);
    }
}
