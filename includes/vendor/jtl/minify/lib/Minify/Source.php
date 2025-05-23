<?php

declare(strict_types=1);

/**
 * Class Minify_Source
 * @package Minify
 */

/**
 * A content source to be minified by Minify.
 *
 * This allows per-source minification options and the mixing of files with
 * content from other sources.
 *
 * @package Minify
 * @author Stephen Clay <steve@mrclay.org>
 */
class Minify_Source implements Minify_SourceInterface
{
    /**
     * @var int time of last modification
     */
    protected $lastModified;

    /**
     * @var callback minifier function specifically for this source.
     */
    protected $minifier;

    /**
     * @var array minification options specific to this source.
     */
    protected $minifyOptions = [];

    /**
     * @var string full path of file
     */
    protected $filepath;

    /**
     * @var string HTTP Content Type (Minify requires one of the constants Minify::TYPE_*)
     */
    protected $contentType;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var callable
     */
    protected $getContentFunc;

    /**
     * @var string
     */
    protected $id;

    /**
     * Create a Minify_Source
     *
     * In the $spec array(), you can either provide a 'filepath' to an existing
     * file (existence will not be checked!) or give 'id' (unique string for
     * the content), 'content' (the string content) and 'lastModified'
     * (unixtime of last update).
     *
     * @param array $spec options
     */
    public function __construct($spec)
    {
        if (isset($spec['filepath'])) {
            $ext = pathinfo($spec['filepath'], PATHINFO_EXTENSION);
            switch ($ext) {
                case 'js':
                    $this->contentType = Minify::TYPE_JS;
                    break;
                case 'less': // fallthrough
                case 'scss': // fallthrough
                case 'css':
                    $this->contentType = Minify::TYPE_CSS;
                    break;
                case 'htm': // fallthrough
                case 'html':
                    $this->contentType = Minify::TYPE_HTML;
                    break;
            }
            $this->filepath = $spec['filepath'];
            $this->id       = $spec['filepath'];

            // TODO ideally not touch disk in constructor
            $this->lastModified = filemtime($spec['filepath']);

            if (!empty($spec['uploaderHoursBehind'])) {
                // offset for Windows uploaders with out of sync clocks
                $this->lastModified += round($spec['uploaderHoursBehind'] * 3600);
            }
        } elseif (isset($spec['id'])) {
            $this->id = 'id::' . $spec['id'];
            if (isset($spec['content'])) {
                $this->content = $spec['content'];
            } else {
                $this->getContentFunc = $spec['getContentFunc'];
            }
            $this->lastModified = $spec['lastModified'] ?? time();
        }
        if (isset($spec['contentType'])) {
            $this->contentType = $spec['contentType'];
        }
        if (isset($spec['minifier'])) {
            $this->setMinifier($spec['minifier']);
        }
        if (isset($spec['minifyOptions'])) {
            $this->minifyOptions = $spec['minifyOptions'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * {@inheritdoc}
     */
    public function getMinifier()
    {
        return $this->minifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setMinifier($minifier = null)
    {
        if ($minifier === '') {
            error_log(__METHOD__ . " cannot accept empty string. Use 'Minify::nullMinifier' or 'trim'.");
            $minifier = 'Minify::nullMinifier';
        }
        if ($minifier !== null && !is_callable($minifier, true)) {
            throw new InvalidArgumentException('minifier must be null or a valid callable');
        }
        $this->minifier = $minifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getMinifierOptions()
    {
        return $this->minifyOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function setMinifierOptions(array $options)
    {
        $this->minifyOptions = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if (null === $this->filepath) {
            $content = $this->content ?? call_user_func($this->getContentFunc, $this->id);
        } else {
            $content = file_get_contents($this->filepath);
        }

        // remove UTF-8 BOM if present
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            return substr($content, 3);
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilePath()
    {
        return $this->filepath;
    }

    public function setupUriRewrites()
    {
        if (
            $this->filepath
            && !isset($this->minifyOptions['currentDir'])
            && !isset($this->minifyOptions['prependRelativePath'])
        ) {
            $this->minifyOptions['currentDir'] = dirname($this->filepath);
        }
    }
}
