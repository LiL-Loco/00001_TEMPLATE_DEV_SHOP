<?php

declare(strict_types=1);

/**
 * Class Minify_Controller_Groups
 * @package Minify
 */

/**
 * Controller class for serving predetermined groups of minimized sets, selected
 * by PATH_INFO
 *
 * <code>
 * Minify::serve('Groups', array(
 *     'groups' => array(
 *         'css' => array('//css/type.css', '//css/layout.css')
 *        ,'js' => array('//js/jquery.js', '//js/site.js')
 *     )
 * ));
 * </code>
 *
 * If the above code were placed in /serve.php, it would enable the URLs
 * /serve.php/js and /serve.php/css
 *
 * @package Minify
 * @author Stephen Clay <steve@mrclay.org>
 */
class Minify_Controller_Groups extends Minify_Controller_Files
{
    /**
     * @inheritdoc
     */
    public function createConfiguration(array $options): Minify_ServeConfiguration
    {
        // strip controller options
        $groups = $options['groups'];
        unset($options['groups']);

        $server = $this->env->server();

        // mod_fcgid places PATH_INFO in ORIG_PATH_INFO
        if (isset($server['ORIG_PATH_INFO'])) {
            $pathInfo = substr($server['ORIG_PATH_INFO'], 1);
        } elseif (isset($server['PATH_INFO'])) {
            $pathInfo = substr($server['PATH_INFO'], 1);
        } else {
            $pathInfo = false;
        }

        if (false === $pathInfo || !isset($groups[$pathInfo])) {
            // no PATH_INFO or not a valid group
            $this->logger->info("Missing PATH_INFO or no group set for \"$pathInfo\"");

            return new Minify_ServeConfiguration($options);
        }

        $files = $groups[$pathInfo];
        // if $files is a single object, casting will break it
        if (is_object($files)) {
            $files = [$files];
        } elseif (!is_array($files)) {
            $files = (array)$files;
        }

        $options['files'] = $files;

        return parent::createConfiguration($options);
    }
}
