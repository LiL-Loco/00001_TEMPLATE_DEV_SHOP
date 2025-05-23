<?php

declare(strict_types=1);

namespace JTL\Router\Controller\Backend;

use DateTime;
use Exception;
use JTL\Backend\Menu;
use JTL\Backend\Permissions;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Media\Image;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\TwoFA\BackendTwoFA;
use JTL\TwoFA\BackendUserData;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

use function Functional\pluck;
use function Functional\reindex;

/**
 * Class AdminAccountController
 * @package JTL\Router\Controller\Backend
 */
class AdminAccountController extends AbstractBackendController
{
    private string $url;

    /**
     * @var array<string, string>
     */
    private array $messages = [
        'notice' => '',
        'error'  => ''
    ];

    public function getAdminLogin(int $adminID): ?stdClass
    {
        return $this->db->select('tadminlogin', 'kAdminlogin', $adminID);
    }

    /**
     * @return stdClass[]
     */
    public function getAdminList(): array
    {
        return $this->db->getObjects(
            'SELECT * FROM tadminlogin
                LEFT JOIN tadminlogingruppe
                    ON tadminlogin.kAdminlogingruppe = tadminlogingruppe.kAdminlogingruppe
                ORDER BY kAdminlogin'
        );
    }

    /**
     * @return stdClass[]
     */
    public function getAdminGroups(): array
    {
        return $this->db->getObjects(
            'SELECT tadminlogingruppe.*, COUNT(tadminlogin.kAdminlogingruppe) AS nCount
                FROM tadminlogingruppe
                LEFT JOIN tadminlogin
                    ON tadminlogin.kAdminlogingruppe = tadminlogingruppe.kAdminlogingruppe
                GROUP BY tadminlogingruppe.kAdminlogingruppe'
        );
    }

    /**
     * @return stdClass[]
     */
    public function getAdminDefPermissions(): array
    {
        $permissionsOrdered = [];
        $menu               = new Menu($this->db, $this->account, $this->getText);
        $perms              = reindex($this->db->selectAll('tadminrecht', [], []), static function (stdClass $e) {
            return $e->cRecht;
        });
        foreach ($menu->getStructure() as $rootName => $rootEntry) {
            $permMainTMP = [];
            foreach ($rootEntry->items as $secondName => $secondEntry) {
                if ($secondEntry === 'DYNAMIC_PLUGINS' || !empty($secondEntry->excludeFromAccessView)) {
                    continue;
                }
                if (\is_object($secondEntry)) {
                    if (isset($perms[$secondEntry->permissions])) {
                        $perms[$secondEntry->permissions]->name = $secondName;
                    } else {
                        $perms[$secondEntry->permissions] = (object)['name' => $secondName];
                    }

                    $permMainTMP[] = (object)[
                        'name'        => $secondName,
                        'permissions' => [$perms[$secondEntry->permissions]]
                    ];
                    unset($perms[$secondEntry->permissions]);
                } else {
                    $permSecondTMP = [];
                    foreach ($secondEntry as $thirdName => $thirdEntry) {
                        if (!empty($thirdEntry->excludeFromAccessView)) {
                            continue;
                        }
                        if (isset($perms[$thirdEntry->permissions])) {
                            $perms[$thirdEntry->permissions]->name = $thirdName;
                        } else {
                            $perms[$thirdEntry->permissions] = (object)['name' => $thirdName];
                        }
                        $permSecondTMP[] = $perms[$thirdEntry->permissions];
                        unset($perms[$thirdEntry->permissions]);
                    }
                    $permMainTMP[] = (object)[
                        'name'        => $secondName,
                        'permissions' => $permSecondTMP
                    ];
                }
            }
            $permissionsOrdered[] = (object)[
                'name'     => $rootName,
                'children' => $permMainTMP
            ];
        }
        if (!empty($perms)) {
            $permissionsOrdered[] = (object)[
                'name'     => \__('noMenuItem'),
                'children' => [
                    (object)[
                        'name'        => '',
                        'permissions' => $perms
                    ]
                ]
            ];
        }
        $activePluginIDs = $this->db->getObjects('SELECT kPlugin, cName FROM tplugin WHERE nStatus = 2');
        if (\count($activePluginIDs) > 0) {
            foreach ($permissionsOrdered as $item) {
                if ($item->name !== \__('Plug-ins')) {
                    continue;
                }
                $children = [
                    (object)[
                        'name'          => \__('All (ignore the following items)'),
                        'cBeschreibung' => \__('All (ignore the following items)'),
                        'cRecht'        => Permissions::PLUGIN_DETAIL_VIEW_ALL
                    ]
                ];
                foreach ($activePluginIDs as $plugin) {
                    $children[] = (object)[
                        'name'          => $plugin->cName,
                        'cBeschreibung' => $plugin->cName,
                        'cRecht'        => Permissions::PLUGIN_DETAIL_VIEW_ID . $plugin->kPlugin
                    ];
                }
                $item->children[] = (object)[
                    'name'        => \__('Plugindetails'),
                    'permissions' => $children
                ];
            }
        }

        return $permissionsOrdered;
    }

    public function getAdminGroup(int $groupID): ?stdClass
    {
        return $this->db->select('tadminlogingruppe', 'kAdminlogingruppe', $groupID);
    }

    /**
     * @return array<int, string>
     */
    public function getAdminGroupPermissions(int $groupID): array
    {
        return pluck($this->db->selectAll('tadminrechtegruppe', 'kAdminlogingruppe', $groupID), 'cRecht');
    }

    public function getInfoInUse(string $row, int|string $value): bool
    {
        return \is_object($this->db->select('tadminlogin', $row, $value));
    }

    public function changeAdminUserLanguage(string $languageTag): void
    {
        $_SESSION['AdminAccount']->language = $languageTag;
        $_SESSION['Sprachen']               = LanguageHelper::getInstance()->gibInstallierteSprachen();

        if (!empty($_COOKIE['JTLSHOP'])) {
            unset($_SESSION['frontendUpToDate']);
        }

        $this->db->update(
            'tadminlogin',
            'kAdminlogin',
            $_SESSION['AdminAccount']->kAdminlogin,
            (object)['language' => $languageTag]
        );
    }

    /**
     * @return array<string, object{kAttribut: string, cName: string, cAttribValue: string, cAttribText: string}>
     */
    public function getAttributes(int $adminID): array
    {
        $extAttribs = $this->db->selectAll(
            'tadminloginattribut',
            'kAdminlogin',
            $adminID,
            'kAttribut, cName, cAttribValue, cAttribText',
            'cName ASC'
        );

        return \array_column($extAttribs, null, 'cName');
    }

    /**
     * @param stdClass                       $account
     * @param array<string, string|string[]> $extAttribs
     * @param array<string, int>             $errorMap
     * @return bool
     */
    public function saveAttributes(stdClass $account, array $extAttribs, array &$errorMap): bool
    {
        $result = true;
        $this->validateAccount($extAttribs);

        \executeHook(\HOOK_BACKEND_ACCOUNT_EDIT, [
            'oAccount' => $account,
            'type'     => 'VALIDATE',
            'attribs'  => &$extAttribs,
            'messages' => &$this->messages,
            'result'   => &$result
        ]);

        if ($result !== true) {
            $errorMap = \array_merge($errorMap, $result);

            return false;
        }

        $handledKeys = [];
        foreach ($extAttribs as $key => $value) {
            $longText  = null;
            $shortText = null;
            if (\is_array($value) && \count($value) > 0) {
                $shortText = Text::filterXSS($value[0]);
                $longText  = $value[1] ?? null;
            }
            $res = $this->db->getLastInsertedID(
                'INSERT INTO tadminloginattribut (kAdminlogin, cName, cAttribValue, cAttribText)
                    VALUES (:loginID, :loginName, :attribVal, :attribText)
                    ON DUPLICATE KEY UPDATE
                    cAttribValue = :attribVal,
                    cAttribText = :attribText',
                [
                    'loginID'    => $account->kAdminlogin,
                    'loginName'  => $key,
                    'attribVal'  => $shortText ?? Text::filterXSS($value),
                    'attribText' => $longText,
                ]
            );
            if ($res === 0) {
                $this->addError(\sprintf(\__('errorKeyChange'), $key));
            }
            $handledKeys[] = $key;
        }
        // nicht (mehr) vorhandene Attribute löschen
        $this->db->queryPrepared(
            "DELETE FROM tadminloginattribut
                WHERE kAdminlogin = :aid
                    AND cName NOT IN ('" . \implode("', '", $handledKeys) . "')",
            ['aid' => (int)$account->kAdminlogin]
        );
        if ($account->kAdminlogin === ($this->account->account()->kAdminlogin ?? null)) {
            $this->account->refreshAttributes();
        }

        return true;
    }

    /**
     * @param array<string, string|bool|array<string, string>> $attribs
     * @return array<string, int>|true
     */
    public function validateAccount(array &$attribs): array|bool
    {
        $result = true;
        if (!$attribs['useAvatar']) {
            $attribs['useAvatar'] = 'N';
        }
        if ($attribs['useAvatar'] === 'U') {
            if (isset($_FILES['extAttribs']) && !empty($_FILES['extAttribs']['name']['useAvatarUpload'])) {
                $attribs['useAvatarUpload'] = $this->uploadAvatarImage($_FILES['extAttribs'], 'useAvatarUpload');

                if ($attribs['useAvatarUpload'] === false) {
                    $this->addError(\__('errorImageUpload'));

                    $result = ['useAvatarUpload' => 1];
                }
            } elseif (empty($attribs['useAvatarUpload'])) {
                $this->addError(\__('errorImageMissing'));

                $result = ['useAvatarUpload' => 1];
            }
        } elseif (!empty($attribs['useAvatarUpload']) && \is_string($attribs['useAvatarUpload'])) {
            if (\is_file(\PFAD_ROOT . $attribs['useAvatarUpload'])) {
                \unlink(\PFAD_ROOT . $attribs['useAvatarUpload']);
            }
            $attribs['useAvatarUpload'] = '';
        }

        foreach (LanguageHelper::getAllLanguages(0, true) as $language) {
            $useVita_ISO = 'useVita_' . $language->getCode();
            if (!empty($attribs[$useVita_ISO])) {
                /** @var string $shortText */
                $shortText = Text::filterXSS($attribs[$useVita_ISO]);
                $longtText = $attribs[$useVita_ISO];

                if (\mb_strlen($shortText) > 255) {
                    $shortText = \mb_substr($shortText, 0, 250) . '...';
                }

                $attribs[$useVita_ISO] = [$shortText, $longtText];
            }
        }

        return $result;
    }

    /**
     * @param array<string, array<mixed>> $tmpFile
     */
    public function uploadAvatarImage(array $tmpFile, string $attribName): false|string
    {
        $file    = [
            'type'     => $tmpFile['type'][$attribName],
            'tmp_name' => $tmpFile['tmp_name'][$attribName],
            'error'    => $tmpFile['error'][$attribName],
            'name'     => $tmpFile['name'][$attribName]
        ];
        $imgType = \array_search($file['type'], [
            \IMAGETYPE_JPEG => \image_type_to_mime_type(\IMAGETYPE_JPEG),
            \IMAGETYPE_PNG  => \image_type_to_mime_type(\IMAGETYPE_PNG),
            \IMAGETYPE_BMP  => \image_type_to_mime_type(\IMAGETYPE_BMP),
            \IMAGETYPE_GIF  => \image_type_to_mime_type(\IMAGETYPE_GIF),
        ], true);
        if ($imgType === false || !Image::isImageUpload($file)) {
            return false;
        }
        $imagePath = \PFAD_MEDIA_IMAGE . 'avatare/';
        $uploadDir = \PFAD_ROOT . $imagePath;
        $imageName = \time() . '_' . \pathinfo($file['name'], \PATHINFO_FILENAME)
            . \image_type_to_extension($imgType);
        if (\is_dir($uploadDir) || (\mkdir($uploadDir, 0755) && \is_dir($uploadDir))) {
            if (\move_uploaded_file($file['tmp_name'], \PFAD_ROOT . $imagePath . $imageName)) {
                return '/' . $imagePath . $imageName;
            }
        }

        return false;
    }

    public function deleteAttributes(stdClass $account): bool
    {
        $deleted = $this->db->delete(
            'tadminloginattribut',
            'kAdminlogin',
            (int)$account->kAdminlogin
        );

        return $deleted >= 0;
    }

    public function actionAccountLock(): string
    {
        $adminID = Request::pInt('id');
        $account = $this->db->select('tadminlogin', 'kAdminlogin', $adminID);
        if ($account !== null && (int)$account->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin) {
            $this->addError(\__('errorSelfLock'));
        } elseif (\is_object($account)) {
            if ((int)$account->kAdminlogingruppe === \ADMINGROUP) {
                $this->addError(\__('errorLockAdmin'));
            } else {
                $result = true;
                $this->db->update('tadminlogin', 'kAdminlogin', $adminID, (object)['bAktiv' => 0]);
                \executeHook(\HOOK_BACKEND_ACCOUNT_EDIT, [
                    'oAccount' => $account,
                    'type'     => 'LOCK',
                    'attribs'  => null,
                    'messages' => &$this->messages,
                    'result'   => &$result
                ]);
                $this->db->update('active_admin_sessions', 'userID', $adminID, (object)['valid' => 0]);
                if ($result === true) {
                    $this->addNotice(\__('successLock'));
                }
            }
        } else {
            $this->addError(\__('errorUserNotFound'));
        }

        return 'index_redirect';
    }

    public function actionAccountUnLock(): string
    {
        $adminID = Request::pInt('id');
        $account = $this->db->select('tadminlogin', 'kAdminlogin', $adminID);
        if (\is_object($account)) {
            $result = true;
            $this->db->update('tadminlogin', 'kAdminlogin', $adminID, (object)['bAktiv' => 1]);
            \executeHook(\HOOK_BACKEND_ACCOUNT_EDIT, [
                'oAccount' => $account,
                'type'     => 'UNLOCK',
                'attribs'  => null,
                'messages' => &$this->messages,
                'result'   => &$result
            ]);
            if ($result === true) {
                $this->addNotice(\__('successUnlocked'));
            }
        } else {
            $this->addError(\__('errorUserNotFound'));
        }

        return 'index_redirect';
    }

    /**
     * @throws Exception
     */
    public function actionAccountEdit(): string
    {
        $_SESSION['AdminAccount']->TwoFA_valid = true;
        /** @var int|null $adminID */
        $adminID     = Request::postInt('id', null);
        $qrCode      = '';
        $knownSecret = '';
        if ($adminID !== null) {
            $twoFA = new BackendTwoFA($this->db, BackendUserData::getByID($adminID, $this->db));
            if ($twoFA->is2FAauthSecretExist() === true) {
                $qrCode      = $twoFA->getQRcode();
                $knownSecret = $twoFA->getSecret();
            }
        }

        if (Request::pInt('save') === 1) {
            $errors              = [];
            $language            = Text::filterXSS(Request::pString('language'));
            $tmpAcc              = new stdClass();
            $tmpAcc->kAdminlogin = Request::pInt('kAdminlogin');
            $tmpAcc->cName       = Text::filterXSS(\trim(Request::pString('cName')));
            $tmpAcc->cMail       = Text::filterXSS(\trim(Request::pString('cMail')));
            $tmpAcc->language    = \array_key_exists($language, $this->getText->getAdminLanguages())
                ? $language
                : 'de-DE';
            $tmpAcc->cLogin      = Text::filterXSS(\trim(Request::pString('cLogin')));
            $tmpAcc->cPass       = \trim(Request::pString('cPass'));
            $tmpAcc->b2FAauth    = Request::pInt('b2FAauth');
            /** @var array<string, string> $tmpAttribs */
            $tmpAttribs = $_POST['extAttribs'] ?? [];
            if (\mb_strlen(Request::pString('c2FAsecret')) > 0) {
                $tmpAcc->c2FAauthSecret = \trim(Request::pString('c2FAsecret'));
            }
            $validUntil = Request::pInt('dGueltigBisAktiv') === 1;
            if ($validUntil) {
                try {
                    $tmpAcc->dGueltigBis = new DateTime(Request::pString('dGueltigBis'));
                } catch (Exception) {
                    $tmpAcc->dGueltigBis = false;
                }
                if ($tmpAcc->dGueltigBis !== false) {
                    $tmpAcc->dGueltigBis = $tmpAcc->dGueltigBis->format('Y-m-d H:i:s');
                }
            }
            $tmpAcc->kAdminlogingruppe = Request::pInt('kAdminlogingruppe');
            if ((bool)$tmpAcc->b2FAauth === true && !isset($tmpAcc->c2FAauthSecret)) {
                $errors['c2FAsecret'] = 1;
            }
            if (\mb_strlen($tmpAcc->cName) === 0) {
                $errors['cName'] = 1;
            }
            if (\mb_strlen($tmpAcc->cMail) === 0) {
                $errors['cMail'] = 1;
            } elseif (Text::filterEmailAddress($tmpAcc->cMail) === false) {
                $errors['cMail'] = 2;
                $this->alertService->addDanger(\__('validationErrorIncorrectEmail'), 'validationErrorIncorrectEmail');
            }
            if ($tmpAcc->kAdminlogin === 0 && \mb_strlen($tmpAcc->cPass) === 0) {
                $errors['cPass'] = 1;
            }
            if (\mb_strlen($tmpAcc->cLogin) === 0) {
                $errors['cLogin'] = 1;
            } elseif ($tmpAcc->kAdminlogin === 0 && $this->getInfoInUse('cLogin', $tmpAcc->cLogin)) {
                $errors['cLogin'] = 2;
            }
            if (
                $validUntil
                && $tmpAcc->kAdminlogingruppe !== \ADMINGROUP
                && \mb_strlen($tmpAcc->dGueltigBis ?: '') === 0
            ) {
                $errors['dGueltigBis'] = 1;
            }
            if ($tmpAcc->kAdminlogin > 0) {
                $oldAcc     = $this->getAdminLogin($tmpAcc->kAdminlogin);
                $groupCount = $this->db->getSingleInt(
                    'SELECT COUNT(*) AS cnt
                        FROM tadminlogin
                        WHERE kAdminlogingruppe = 1',
                    'cnt'
                );
                if (
                    $oldAcc !== null
                    && (int)$oldAcc->kAdminlogingruppe === \ADMINGROUP
                    && $tmpAcc->kAdminlogingruppe !== \ADMINGROUP
                    && $groupCount <= 1
                ) {
                    $errors['bMinAdmin'] = 1;
                }
            }
            if (\count($errors) > 0) {
                $this->getSmarty()->assign('cError_arr', $errors);
                $this->addError(\__('errorFillRequired'));
                if (isset($errors['bMinAdmin']) && $errors['bMinAdmin'] === 1) {
                    $this->addError(\__('errorAtLeastOneAdmin'));
                }
            } elseif ($tmpAcc->kAdminlogin > 0) {
                if (!$validUntil) {
                    $tmpAcc->dGueltigBis = '_DBNULL_';
                }
                // if we change the current admin-user, we have to update his session-credentials too!
                if (
                    $tmpAcc->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin
                    && $tmpAcc->cLogin !== $_SESSION['AdminAccount']->cLogin
                ) {
                    $_SESSION['AdminAccount']->cLogin = $tmpAcc->cLogin;
                }
                if (\mb_strlen($tmpAcc->cPass) > 0) {
                    $tmpAcc->cPass = Shop::Container()->getPasswordService()->hash($tmpAcc->cPass);
                    // if we change the current admin-user, we have to update his session-credentials too!
                    if ($tmpAcc->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin) {
                        $_SESSION['AdminAccount']->cPass = $tmpAcc->cPass;
                    }
                } else {
                    unset($tmpAcc->cPass);
                }

                $this->changeAdminUserLanguage($tmpAcc->language);

                if (
                    $this->db->update('tadminlogin', 'kAdminlogin', $tmpAcc->kAdminlogin, $tmpAcc) >= 0
                    && $this->saveAttributes($tmpAcc, $tmpAttribs, $errors)
                ) {
                    $result = true;
                    \executeHook(\HOOK_BACKEND_ACCOUNT_EDIT, [
                        'oAccount' => $tmpAcc,
                        'type'     => 'SAVE',
                        'attribs'  => &$tmpAttribs,
                        'messages' => &$this->messages,
                        'result'   => &$result
                    ]);
                    if ($result === true) {
                        $this->addNotice(\__('successUserSave'));

                        return 'index_redirect';
                    }
                    $this->getSmarty()->assign('cError_arr', \array_merge($errors, (array)$result));
                } else {
                    $this->addError(\__('errorUserSave'));
                    $this->getSmarty()->assign('cError_arr', $errors);
                }
            } else {
                unset($tmpAcc->kAdminlogin);
                $tmpAcc->bAktiv        = 1;
                $tmpAcc->nLoginVersuch = 0;
                $tmpAcc->dLetzterLogin = '_DBNULL_';
                if (!isset($tmpAcc->dGueltigBis) || \mb_strlen($tmpAcc->dGueltigBis) === 0) {
                    $tmpAcc->dGueltigBis = '_DBNULL_';
                }
                $tmpAcc->cPass = Shop::Container()->getPasswordService()->hash($tmpAcc->cPass);

                if (
                    ($tmpAcc->kAdminlogin = $this->db->insert('tadminlogin', $tmpAcc))
                    && $this->saveAttributes($tmpAcc, $tmpAttribs, $errors)
                ) {
                    $result = true;
                    \executeHook(\HOOK_BACKEND_ACCOUNT_EDIT, [
                        'oAccount' => $tmpAcc,
                        'type'     => 'SAVE',
                        'attribs'  => &$tmpAttribs,
                        'messages' => &$this->messages,
                        'result'   => &$result
                    ]);
                    if ($result === true) {
                        $this->addNotice(\__('successUserAdd'));

                        return 'index_redirect';
                    }
                    $this->getSmarty()->assign('cError_arr', \array_merge($errors, (array)$result));
                } else {
                    $this->addError(\__('errorUserAdd'));
                    $this->getSmarty()->assign('cError_arr', $errors);
                }
            }

            $account    = &$tmpAcc;
            $extAttribs = [];
            foreach ($tmpAttribs as $key => $attrib) {
                $extAttribs[$key] = (object)[
                    'kAttribut'    => null,
                    'cName'        => $key,
                    'cAttribValue' => $attrib
                ];
            }
            if ((int)$account->kAdminlogingruppe === 1) {
                unset($account->kAdminlogingruppe);
            }
        } elseif ($adminID > 0) {
            $account    = $this->getAdminLogin($adminID);
            $extAttribs = $this->getAttributes($adminID);
        } else {
            $account    = new stdClass();
            $extAttribs = [];
        }

        $this->getSmarty()->assign('attribValues', $extAttribs)
            ->assign('QRcodeString', $qrCode)
            ->assign('cKnownSecret', $knownSecret);

        $extContent = '';
        \executeHook(\HOOK_BACKEND_ACCOUNT_PREPARE_EDIT, [
            'oAccount' => $account,
            'smarty'   => $this->smarty,
            'attribs'  => $extAttribs,
            'content'  => &$extContent
        ]);

        $groupCount = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tadminlogin
                WHERE kAdminlogingruppe = 1',
            'cnt'
        );
        $this->getSmarty()->assign('oAccount', $account)
            ->assign('nAdminCount', $groupCount)
            ->assign('extContent', $extContent);

        return 'account_edit';
    }

    public function actionAccountDelete(): string
    {
        $adminID    = Request::pInt('id');
        $groupCount = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tadminlogin
                WHERE kAdminlogingruppe = 1',
            'cnt'
        );
        $account    = $this->db->select('tadminlogin', 'kAdminlogin', $adminID);
        if ($account !== null && (int)$account->kAdminlogin === (int)$_SESSION['AdminAccount']->kAdminlogin) {
            $this->addError(\__('errorSelfDelete'));
        } elseif (\is_object($account)) {
            if ((int)$account->kAdminlogingruppe === \ADMINGROUP && $groupCount <= 1) {
                $this->addError(\__('errorAtLeastOneAdmin'));
            } elseif (
                $this->deleteAttributes($account) &&
                $this->db->delete('tadminlogin', 'kAdminlogin', $adminID)
            ) {
                $this->db->update('active_admin_sessions', 'userID', $adminID, (object)['valid' => 0]);
                $result = true;
                \executeHook(\HOOK_BACKEND_ACCOUNT_EDIT, [
                    'oAccount' => $account,
                    'type'     => 'DELETE',
                    'attribs'  => null,
                    'messages' => &$this->messages,
                    'result'   => &$result
                ]);
                if ($result === true) {
                    $this->addNotice(\__('successUserDelete'));
                }
            } else {
                $this->addError(\__('errorUserDelete'));
            }
        } else {
            $this->addError(\__('errorUserNotFound'));
        }

        return 'index_redirect';
    }

    public function actionGroupEdit(): string
    {
        $debug = isset($_POST['debug']);
        /** @var int|null $groupID */
        $groupID = Request::postInt('id', null);
        if (Request::pInt('save') === 1) {
            $errors                        = [];
            $adminGroup                    = new stdClass();
            $adminGroup->kAdminlogingruppe = Request::pInt('kAdminlogingruppe');
            $adminGroup->cGruppe           = Text::filterXSS(\trim(Request::pString('cGruppe')));
            $adminGroup->cBeschreibung     = Text::filterXSS(\trim(Request::pString('cBeschreibung')));
            /** @var string[] $groupPermissions */
            $groupPermissions = Text::filterXSS($_POST['perm'] ?? []);
            if (\mb_strlen($adminGroup->cGruppe) === 0) {
                $errors['cGruppe'] = 1;
            }
            if (\mb_strlen($adminGroup->cBeschreibung) === 0) {
                $errors['cBeschreibung'] = 1;
            }
            if (\count($groupPermissions) === 0) {
                $errors['cPerm'] = 1;
            }
            if (\count($errors) > 0) {
                $this->getSmarty()->assign('cError_arr', $errors)
                    ->assign('oAdminGroup', $adminGroup)
                    ->assign('cAdminGroupPermission_arr', $groupPermissions);

                if (isset($errors['cPerm'])) {
                    $this->addError(\__('errorAtLeastOneRight'));
                } else {
                    $this->addError(\__('errorFillRequired'));
                }
            } else {
                if ($adminGroup->kAdminlogingruppe > 0) {
                    $this->db->update(
                        'tadminlogingruppe',
                        'kAdminlogingruppe',
                        $adminGroup->kAdminlogingruppe,
                        $adminGroup
                    );
                    $this->db->delete(
                        'tadminrechtegruppe',
                        'kAdminlogingruppe',
                        $adminGroup->kAdminlogingruppe
                    );
                    $permission                    = new stdClass();
                    $permission->kAdminlogingruppe = $adminGroup->kAdminlogingruppe;
                    foreach ($groupPermissions as $groupPermission) {
                        $permission->cRecht = $groupPermission;
                        $this->db->insert('tadminrechtegruppe', $permission);
                    }
                    $this->db->queryPrepared(
                        'UPDATE active_admin_sessions
                            SET valid = 0
                            WHERE userID IN (SELECT kAdminlogin
                                FROM tadminlogin
                                WHERE kAdminlogingruppe = :id)',
                        ['id' => $permission->kAdminlogingruppe]
                    );
                    $this->addNotice(\__('successGroupEdit'));

                    return 'group_redirect';
                }
                unset($adminGroup->kAdminlogingruppe);
                $groupID = $this->db->insert('tadminlogingruppe', $adminGroup);
                $this->db->delete('tadminrechtegruppe', 'kAdminlogingruppe', $groupID);
                $permission                    = new stdClass();
                $permission->kAdminlogingruppe = $groupID;
                foreach ($groupPermissions as $groupPermission) {
                    $permission->cRecht = $groupPermission;
                    $this->db->insert('tadminrechtegruppe', $permission);
                }
                $this->addNotice(\__('successGroupCreate'));

                return 'group_redirect';
            }
        } elseif ($groupID > 0) {
            if ((int)$groupID === 1) {
                \header('Location:  ' . $this->url . '?action=group_view&token=' . $_SESSION['jtl_token']);
            }
            $this->getSmarty()->assign('bDebug', $debug)
                ->assign('oAdminGroup', $this->getAdminGroup($groupID))
                ->assign('cAdminGroupPermission_arr', $this->getAdminGroupPermissions($groupID));
        }

        return 'group_edit';
    }

    public function actionGroupDelete(): string
    {
        $groupID = Request::pInt('id');
        $count   = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tadminlogin
                WHERE kAdminlogingruppe = :gid',
            'cnt',
            ['gid' => $groupID]
        );
        if ($count !== 0) {
            $this->addError(\__('errorGroupDeleteCustomer'));

            return 'group_redirect';
        }

        if ($groupID !== \ADMINGROUP) {
            $this->db->delete('tadminlogingruppe', 'kAdminlogingruppe', $groupID);
            $this->db->delete('tadminrechtegruppe', 'kAdminlogingruppe', $groupID);
            $this->addNotice(\__('successGroupDelete'));
        } else {
            $this->addError(\__('errorGroupDelete'));
        }

        return 'group_redirect';
    }

    /**
     * @throws Exception
     */
    public function getNextAction(): string
    {
        $action = 'account_view';
        if (isset($_REQUEST['action']) && Form::validateToken()) {
            $action = $_REQUEST['action'];
        }
        switch ($action) {
            case 'account_lock':
                $action = $this->actionAccountLock();
                break;
            case 'account_unlock':
                $action = $this->actionAccountUnLock();
                break;
            case 'account_edit':
                $action = $this->actionAccountEdit();
                break;
            case 'account_delete':
                $action = $this->actionAccountDelete();
                break;
            case 'group_edit':
                $action = $this->actionGroupEdit();
                break;
            case 'group_delete':
                $action = $this->actionGroupDelete();
                break;
            case 'quick_change_language':
                $action = 'account_view';
                break;
        }

        return $action;
    }

    public function actionQuickChangeLanguage(): void
    {
        $this->changeAdminUserLanguage(Request::verifyGPDataString('language'));
        $url = Request::verifyGPDataString('referer');
        if (!\str_starts_with($url, $this->baseURL)) {
            return;
        }
        \header('Location: ' . $url);
        exit;
    }

    /**
     * @former benutzerverwaltungRedirect()
     */
    public function benutzerverwaltungRedirect(string $tab = ''): never
    {
        if ($this->getNotice() !== '') {
            $_SESSION['benutzerverwaltung.notice'] = $this->getNotice();
        } else {
            unset($_SESSION['benutzerverwaltung.notice']);
        }
        if ($this->getError() !== '') {
            $_SESSION['benutzerverwaltung.error'] = $this->getError();
        } else {
            unset($_SESSION['benutzerverwaltung.error']);
        }

        $urlParams = null;
        if (!empty($tab)) {
            $urlParams = ['tab' => Text::filterXSS($tab)];
        }

        \header(
            'Location: ' . $this->url . (\is_array($urlParams)
                ? '?' . \http_build_query($urlParams, '', '&')
                : '')
        );
        exit;
    }

    public function finalize(string $step): void
    {
        if (isset($_SESSION['benutzerverwaltung.notice'])) {
            $this->messages['notice'] = $_SESSION['benutzerverwaltung.notice'];
            unset($_SESSION['benutzerverwaltung.notice']);
        }
        if (isset($_SESSION['benutzerverwaltung.error'])) {
            $this->messages['error'] = $_SESSION['benutzerverwaltung.error'];
            unset($_SESSION['benutzerverwaltung.error']);
        }
        switch ($step) {
            case 'account_edit':
                if (Request::pInt('id') > 0) {
                    $this->alertService->addWarning(\__('warningPasswordResetAuth'), 'warningPasswordResetAuth');
                }
                $this->getSmarty()->assign('oAdminGroup_arr', $this->getAdminGroups())
                    ->assign(
                        'languages',
                        $this->getText->getAdminLanguages()
                    );
                break;
            case 'account_view':
                $this->getSmarty()->assign('oAdminList_arr', $this->getAdminList())
                    ->assign('oAdminGroup_arr', $this->getAdminGroups());
                break;
            case 'group_edit':
                $this->getSmarty()->assign('permissions', $this->getAdminDefPermissions());
                break;
            case 'index_redirect':
                $this->benutzerverwaltungRedirect('account_view');
            // no break since benutzerverwaltungRedirect() will exit
            case 'group_redirect':
                $this->benutzerverwaltungRedirect('group_view');
        }

        $this->alertService->addNotice($this->getNotice(), 'userManagementNote');
        $this->alertService->addError($this->getError(), 'userManagementError');

        $this->getSmarty()->assign('action', $step)
            ->assign('cTab', Text::filterXSS(Request::verifyGPDataString('tab')));
    }

    public function addError(string $error): void
    {
        $this->messages['error'] .= $error;
    }

    public function addNotice(string $notice): void
    {
        $this->messages['notice'] .= $notice;
    }

    public function getNotice(): string
    {
        return $this->messages['notice'];
    }

    public function getError(): string
    {
        return $this->messages['error'];
    }

    /**
     * @inheritdoc
     */
    public function getResponse(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->url    = $this->baseURL . $this->route;
        $this->smarty = $smarty;
        if (Request::getVar('action') === 'quick_change_language') {
            $this->actionQuickChangeLanguage();
        }
        $this->checkPermissions(Permissions::ACCOUNT_VIEW);
        $this->getText->loadAdminLocale('pages/benutzerverwaltung');
        $this->finalize($this->getNextAction());

        return $smarty->assign('route', $this->route)
            ->assign('url', $this->url)
            ->getResponse('benutzer.tpl');
    }
}
