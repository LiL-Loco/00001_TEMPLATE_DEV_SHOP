<?php

declare(strict_types=1);

namespace JTL\Smarty;

use JSMin\JSMin;
use JSMin\UnterminatedStringException;
use JTL\Events\Dispatcher;
use JTL\Helpers\GeneralObject;
use JTL\Language\LanguageHelper;
use JTL\phpQuery\phpQuery;
use JTL\Plugin\Helper;
use JTL\Profiler;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Template\BootChecker;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Smarty;

/**
 * Class JTLSmarty
 * @package \JTL\Smarty
 */
class JTLSmarty extends Smarty
{
    /**
     * @var array<string, string[]>
     */
    public array $config;

    /**
     * @var JTLSmarty[]
     */
    private static array $instance = [];

    public static bool $isChildTemplate = false;

    protected string $templateDir;

    /**
     * @var array<string, string>
     */
    private array $mapping = [];

    /**
     * @param bool                         $fast - set to true when init from backend to avoid setting session data
     * @param string                       $context
     * @param array<string, string[]>|null $config
     * @param bool                         $workaround - indicates an early call for JTLSmarty::getInstance()
     * before new() was called
     */
    public function __construct(
        bool $fast = false,
        public string $context = ContextType::FRONTEND,
        ?array $config = null,
        bool $workaround = false
    ) {
        parent::__construct();
        self::$_CHARSET = \JTL_CHARSET;
        $this->setErrorReporting(\SMARTY_LOG_LEVEL)
            ->setForceCompile(\SMARTY_FORCE_COMPILE)
            ->setDebugging(\SMARTY_DEBUG_CONSOLE)
            ->setUseSubDirs(\SMARTY_USE_SUB_DIRS);
        $this->config = $config ?? Shopsetting::getInstance()->getAll();
        $parent       = $this->initTemplate();
        if ($fast === false) {
            $this->registerPlugins();
            $this->init($parent);
        }
        if ($workaround === false) {
            // do not register instance when called from getInstance() to avoid skipping hooks
            self::$instance[$context] = $this;
            if ($fast === false && $context !== ContextType::BACKEND) {
                \executeHook(\HOOK_SMARTY_INC, ['smarty' => $this]);
            }
        }
    }

    public static function getInstance(bool $fast = false, string $context = ContextType::FRONTEND): self
    {
        $instance   = self::$instance[$context] ?? null;
        $workaround = $context === ContextType::FRONTEND && $instance === null;
        if ($workaround === true) {
            foreach (\debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS) as $item) {
                if (isset($item['file']) && \str_contains($item['file'], \PLUGIN_DIR)) {
                    Shop::Container()->getLogService()->info(
                        'Smarty invoked too early at {file} - please contact plugin author.',
                        ['file' => $item['file']]
                    );
                    break;
                }
            }
        }

        return $instance ?? new self($fast, $context, null, $workaround);
    }

    public static function hasInstance(string $context): bool
    {
        return (self::$instance[$context] ?? null) !== null;
    }

    protected function initTemplate(): ?string
    {
        $model = Shop::Container()->getTemplateService()->getActiveTemplate();
        if ($model->getTemplate() === null) {
            throw new RuntimeException('Cannot load template ' . ($model->getName() ?? ''));
        }
        $paths      = $model->getPaths();
        $tplDir     = $model->getDir();
        $parent     = $model->getParent();
        $compileDir = $paths->getCompileDir();
        if (!\is_dir($compileDir) && !\mkdir($compileDir) && !\is_dir($compileDir)) {
            throw new RuntimeException(\sprintf('Directory "%s" could not be created', $compileDir));
        }
        $this->template_dir = [];
        $this->setCompileDir($compileDir)
            ->setCacheDir($paths->getCacheDir())
            ->assign('tplDir', $paths->getBaseDir())
            ->assign('parentTemplateDir');
        if ($parent !== null) {
            self::$isChildTemplate = true;
            $this->assign('tplDir', $paths->getParentDir())
                ->assign('parent_template_path', $paths->getParentDir())
                ->assign('parentTemplateDir', $paths->getParentRelDir())
                ->addTemplateDir($paths->getParentDir(), $parent);
        }
        $this->addTemplateDir($paths->getBaseDir(), $this->context);
        foreach (Helper::getTemplatePaths() as $moduleId => $path) {
            $templateKey = 'plugin_' . $moduleId;
            $this->addTemplateDir($path, $templateKey);
        }
        if (($bootstrapper = BootChecker::bootstrap($tplDir) ?? BootChecker::bootstrap($parent)) !== null) {
            $bootstrapper->setSmarty($this);
            $bootstrapper->setTemplate($model);
            $bootstrapper->boot();
        }
        $this->templateDir = $tplDir;

        return $parent;
    }

    protected function registerPlugins(): void
    {
        $pluginCollection = new PluginCollection($this, LanguageHelper::getInstance());
        $pluginCollection->registerPlugins();
        $pluginCollection->registerPhpFunctions();
        $pluginCollection->registerShopClasses();
    }

    /**
     * @throws \SmartyException
     */
    protected function init(?string $parent = null): void
    {
        $this->cache_lifetime = 86400;
        $this->template_class = \SHOW_TEMPLATE_HINTS > 0
            ? JTLSmartyTemplateHints::class
            : JTLSmartyTemplateClass::class;
        $this->setCachingParams($this->config);
        /** @var string $tplDir */
        $tplDir = $this->getTemplateDir($this->context);
        global $smarty;
        $smarty = $this;
        if (\file_exists($tplDir . 'php/functions_custom.php')) {
            require_once $tplDir . 'php/functions_custom.php';
        } elseif (\file_exists($tplDir . 'php/functions.php')) {
            require_once $tplDir . 'php/functions.php';
        } elseif ($parent !== null && \file_exists(\PFAD_ROOT . \PFAD_TEMPLATES . $parent . '/php/functions.php')) {
            require_once \PFAD_ROOT . \PFAD_TEMPLATES . $parent . '/php/functions.php';
        }
    }

    /**
     * @param array<string, string[]>|null $config
     */
    public function setCachingParams(?array $config = null): self
    {
        $config = $config ?? Shop::getSettings([\CONF_CACHING]);

        return $this->setCaching(self::CACHING_OFF)
            ->setCompileCheck((int)(($config['caching']['compile_check'] ?? 'Y') === 'Y'));
    }

    public function getTemplateUrlPath(): string
    {
        return \PFAD_TEMPLATES . $this->templateDir . '/';
    }

    public function outputFilter(string $tplOutput): string
    {
        $hookList = Helper::getHookList();
        if (
            GeneralObject::hasCount(\HOOK_SMARTY_OUTPUTFILTER, $hookList)
            || \count(Dispatcher::getInstance()->getListeners('shop.hook.' . \HOOK_SMARTY_OUTPUTFILTER)) > 0
        ) {
            $this->unregisterFilter('output', $this->outputFilter(...));
            $doc = phpQuery::newDocumentHTML($tplOutput, \JTL_CHARSET);
            \executeHook(\HOOK_SMARTY_OUTPUTFILTER, ['smarty' => $this, 'document' => $doc]);
            $tplOutput = $doc->htmlOuter();
        }

        return ($this->config['template']['general']['minify_html'] ?? 'N') === 'Y'
            ? $this->minifyHTML(
                $tplOutput,
                ($this->config['template']['general']['minify_html_css'] ?? 'N') === 'Y',
                ($this->config['template']['general']['minify_html_js'] ?? 'N') === 'Y'
            )
            : $tplOutput;
    }

    /**
     * @inheritdoc
     */
    public function isCached($template = null, $cacheID = null, $compileID = null, $parent = null): bool
    {
        return false;
    }

    /**
     * @param int|bool $mode
     * @return $this
     */
    public function setCaching($mode): self
    {
        $this->caching = (int)$mode;

        return $this;
    }

    /**
     * @param bool $mode
     * @return $this
     */
    public function setDebugging($mode): self
    {
        $this->debugging = $mode;

        return $this;
    }

    private function minifyHTML(string $html, bool $minifyCSS = false, bool $minifyJS = false): string
    {
        $options = [];
        if ($minifyCSS === true) {
            $options['cssMinifier'] = [\Minify_CSSmin::class, 'minify'];
        }
        if ($minifyJS === true) {
            $options['jsMinifier'] = [JSMin::class, 'minify'];
        }
        try {
            $res = (new \Minify_HTML($html, $options))->process();
        } catch (UnterminatedStringException) {
            $res = $html;
        }

        return $res;
    }

    public function getCustomFile(string $filename): string
    {
        if (
            self::$isChildTemplate === true
            || !isset($this->config['template']['general']['use_customtpl'])
            || $this->config['template']['general']['use_customtpl'] !== 'Y'
        ) {
            // disabled on child templates for now
            return $filename;
        }
        $file   = \basename($filename, '.tpl');
        $dir    = \dirname($filename);
        $custom = !\str_contains($dir, \PFAD_ROOT)
            ? $this->getTemplateDir($this->context) . (($dir === '.')
                ? ''
                : ($dir . '/')) . $file . '_custom.tpl'
            : ($dir . '/' . $file . '_custom.tpl');

        return \file_exists($custom) ? $custom : $filename;
    }

    /**
     * @inheritdoc
     */
    public function fetch($template = null, $cacheID = null, $compileID = null, $parent = null): string
    {
        $debug = $this->_debug->template_data ?? null;
        $res   = parent::fetch(
            $template === null ? $template : $this->getResourceName($template),
            $cacheID,
            $compileID,
            $parent
        );
        if ($debug !== null) {
            // fetch overwrites the old debug data so we have to merge it with our previously saved data
            $this->_debug->template_data = \array_merge($debug, $this->_debug->template_data);
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    public function display($template = null, $cacheID = null, $compileID = null, $parent = null): void
    {
        if ($this->context === ContextType::FRONTEND) {
            $this->registerFilter('output', $this->outputFilter(...));
        }
        parent::display($template === null ? null : $this->getResourceName($template), $cacheID, $compileID, $parent);
    }

    public function getResponse(string $template): ResponseInterface
    {
        if ($this->context === ContextType::FRONTEND) {
            $this->registerFilter('output', $this->outputFilter(...));
            /** @var JTLSmartyTemplateClass $tpl */
            $tpl                 = $this->createTemplate(
                $this->getResourceName($template),
                null,
                null,
                $this,
                false
            );
            $tpl->noOutputFilter = false;
        } else {
            $tpl = $this->createTemplate($template, null, null, $this, false);
        }

        $res = parent::fetch($tpl);
        Profiler::finalize();
        $response = new Response();
        $response->getBody()->write($res);

        return $response;
    }

    public function getCacheID(): null
    {
        return null;
    }

    public function getResourceName(string $resourceName): string
    {
        $transform = false;
        if (\str_starts_with($resourceName, 'string:') || \str_contains($resourceName, '[')) {
            return $resourceName;
        }
        if (\str_starts_with($resourceName, 'file:')) {
            $resourceName = \str_replace('file:', '', $resourceName);
            $transform    = true;
        }
        $mapped = $this->mapping[$resourceName] ?? null;
        if ($mapped !== null) {
            return $mapped;
        }
        $resourceCustomName = $this->getCustomFile($resourceName);
        $res                = $this->extendResource($resourceName, $resourceCustomName, $transform);

        $this->mapping[$resourceName] = $res;

        return $res;
    }

    protected function extendResource(string $resourceName, string $customName, bool $transform): string
    {
        if ($this->context !== ContextType::FRONTEND) {
            return $this->getResourceString($customName, $transform);
        }
        $cfbName = $customName;
        \executeHook(\HOOK_SMARTY_FETCH_TEMPLATE, [
            'original'  => &$resourceName,
            'custom'    => &$customName,
            'fallback'  => &$customName,
            'out'       => &$cfbName,
            'transform' => $transform
        ]);
        if ($resourceName !== $cfbName) {
            return $this->getResourceString($cfbName, $transform);
        }
        $extends = $this->getExtends($cfbName);
        if (\count($extends) > 1) {
            $transform = false;
            $cfbName   = \sprintf(
                'extends:%s',
                \implode('|', $extends)
            );
        }

        return $this->getResourceString($cfbName, $transform);
    }

    /**
     * @return string[]
     */
    private function getExtends(string $resourceCfbName): array
    {
        $extends = [];
        /** @var array<string, string> $templateDirs */
        $templateDirs = $this->getTemplateDir();
        foreach ($templateDirs as $module => $templateDir) {
            if (\str_starts_with($module, 'plugin_')) {
                $pluginID    = \mb_substr($module, 7);
                $templateVar = 'oPlugin_' . $pluginID;
                if ($this->getTemplateVars($templateVar) === null) {
                    $plugin = Helper::getPluginById($pluginID);
                    $this->assign($templateVar, $plugin);
                }
            }
            if (\file_exists($templateDir . $resourceCfbName)) {
                $extends[] = \sprintf('[%s]%s', $module, $resourceCfbName);
            }
        }

        return $extends;
    }

    private function getResourceString(string $resource, bool $transform): string
    {
        return $transform ? ('file:' . $resource) : $resource;
    }

    /**
     * @param bool $useSubDirs
     * @return $this
     */
    public function setUseSubDirs($useSubDirs): self
    {
        parent::setUseSubDirs((bool)$useSubDirs);

        return $this;
    }

    /**
     * @param bool $force
     * @return $this
     */
    public function setForceCompile($force): self
    {
        parent::setForceCompile((bool)$force);

        return $this;
    }

    /**
     * @param int $check
     * @return $this
     */
    public function setCompileCheck($check): self
    {
        parent::setCompileCheck($check);

        return $this;
    }

    /**
     * @param int $reporting
     * @return $this
     */
    public function setErrorReporting($reporting): self
    {
        parent::setErrorReporting($reporting);

        return $this;
    }

    public static function getIsChildTemplate(): bool
    {
        return self::$isChildTemplate;
    }

    /**
     * When Smarty is used in an insecure context (e.g. when third parties are granted access to shop admin) this
     * function activates a secure mode that:
     *   - deactivates {php}-tags
     *   - removes php code (that could be written to a file an then be executes)
     *   - applies a whitelist for php functions (Smarty modifiers and functions)
     *
     * @return $this
     * @throws \SmartyException
     */
    public function activateBackendSecurityMode(): self
    {
        $sec                = new \Smarty_Security($this);
        $jtlModifier        = [
            'replace_delim',
            'count_characters',
            'string_format',
            'string_date_format',
            'truncate',
        ];
        $secureFuncs        = $this->getSecurePhpFunctions();
        $sec->php_modifiers = \array_merge(
            $sec->php_modifiers,
            $jtlModifier,
            $secureFuncs
        );
        $sec->php_modifiers = \array_unique($sec->php_modifiers);
        $sec->php_functions = \array_unique(\array_merge($sec->php_functions, $secureFuncs, ['lang']));
        $this->enableSecurity($sec);

        return $this;
    }

    /**
     * Get a list of php functions that should be safe to use in an insecure context.
     *
     * @return string[]
     */
    public function getSecurePhpFunctions(): array
    {
        static $functions;
        if ($functions === null) {
            $functions = \array_map('\trim', \explode(',', \SECURE_PHP_FUNCTIONS));
        }

        return $functions;
    }

    public function assignDeprecated(string $name, mixed $value, string $version): self
    {
        $this->tpl_vars[$name] = new DeprecatedVariable($value, $name, $version);

        return $this;
    }

    /**
     * @inheritdoc
     * @return JTLSmarty
     */
    public function assign($tpl_var, $value = null, $nocache = false): self
    {
        parent::assign($tpl_var, $value, $nocache);

        return $this;
    }
}
