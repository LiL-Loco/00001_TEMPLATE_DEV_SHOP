<?php

declare(strict_types=1);

namespace JTL\Plugin\Data;

use Illuminate\Support\Collection;
use JTL\Language\LanguageHelper;
use JTL\Plugin\Admin\InputType;
use stdClass;

use function Functional\first;
use function Functional\group;

/**
 * Class Localization
 * @package JTL\Plugin\Data
 */
class Localization
{
    /**
     * @var Collection<stdClass>
     */
    private Collection $langVars;

    public function __construct(private string $currentLanguageCode)
    {
        $this->langVars = new Collection();
    }

    /**
     * @param stdClass[] $data
     * @return Localization
     */
    public function load(array $data): self
    {
        $grouped = group($data, static function (stdClass $e) {
            return $e->kPluginSprachvariable;
        });
        foreach ($grouped as $group) {
            /** @var stdClass $lv */
            $lv                                    = first($group);
            $var                                   = new stdClass();
            $var->kPluginSprachvariable            = (int)$lv->kPluginSprachvariable;
            $var->id                               = $var->kPluginSprachvariable;
            $var->kPlugin                          = (int)$lv->kPlugin;
            $var->pluginID                         = $var->kPlugin;
            $var->cName                            = $lv->cName;
            $var->name                             = $var->cName;
            $var->cBeschreibung                    = $lv->cBeschreibung;
            $var->description                      = $var->cBeschreibung;
            $var->oPluginSprachvariableSprache_arr = [$lv->cISO => $lv->customValue];
            $var->values                           = $var->oPluginSprachvariableSprache_arr;
            $var->type                             = $lv->type ?? InputType::TEXT;
            foreach ($group as $translation) {
                $var->oPluginSprachvariableSprache_arr[$translation->cISO] = $translation->customValue;
                $var->values[$translation->cISO]                           = $translation->customValue;
            }
            $this->langVars->push($var);
        }

        return $this;
    }

    public function getTranslation(string $name, ?string $iso = null): ?string
    {
        $iso = \mb_convert_case($iso ?? $this->currentLanguageCode, \MB_CASE_UPPER);
        /** @var stdClass|null $first */
        $first = $this->langVars->firstWhere('name', $name);
        if (!isset($first->values[$iso])) {
            $defaultIso = LanguageHelper::getDefaultLanguage()->getCode();
            if ($iso !== \mb_convert_case($defaultIso, \MB_CASE_UPPER)) {
                return $this->getTranslation($name, $defaultIso);
            }
        }

        return $first->values[$iso] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function getTranslations(): array
    {
        $iso = \mb_convert_case($this->currentLanguageCode, \MB_CASE_UPPER);

        return $this->langVars->mapWithKeys(static function (stdClass $item) use ($iso): array {
            return [$item->name => $item->values[$iso] ?? null];
        })->toArray();
    }

    /**
     * compatibility dummy
     */
    public function setTranslations(): void
    {
    }

    /**
     * @return stdClass[]
     */
    public function getLangVarsCompat(): array
    {
        return $this->langVars->toArray();
    }

    public function getLangVars(): Collection
    {
        return $this->langVars;
    }

    public function setLangVars(Collection $langVars): void
    {
        $this->langVars = $langVars;
    }

    public function getCurrentLanguageCode(): string
    {
        return $this->currentLanguageCode;
    }

    public function setCurrentLanguageCode(string $currentLanguageCode): void
    {
        $this->currentLanguageCode = $currentLanguageCode;
    }
}
