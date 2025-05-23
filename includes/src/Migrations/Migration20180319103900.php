<?php

/**
 * Add backend logging option
 */

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Backend\AdminLoginConfig;
use JTL\Update\IMigration;
use JTL\Update\Migration;
use Monolog\Level;

/**
 * Class Migration20180319103900
 */
class Migration20180319103900 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'fm';
    }

    public function getDescription(): string
    {
        return 'Add backend logging option, removed old options';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->removeConfig('object_caching_activated');
        $this->removeConfig('object_caching_method');
        $this->removeConfig('object_caching_memcached_host');
        $this->removeConfig('object_caching_memcached_port');
        $this->removeConfig('object_caching_debug_mode');
        $this->removeConfig('global_gesamtsummenanzeige');
        $this->removeConfig('news_navigation_anzeige');
        $this->removeConfig('trustedshops_siegelbox_anzeigen');
        $this->removeConfig('page_cache_debugging');
        $this->removeConfig('caching_page_cache');
        $this->removeConfig('advanced_page_cache');
        $this->execute("DELETE FROM teinstellungen WHERE cName = '' AND cWert = ''");
        $this->execute("DELETE FROM teinstellungen WHERE kEinstellungenSektion = 8 AND cName LIKE 'box_%_anzeigen'");
        $this->execute(
            "INSERT INTO teinstellungenconf (kEinstellungenSektion, cName, cBeschreibung, cWertName, 
                                cInputTyp, cModulId, nSort, nStandardAnzeigen) 
          (SELECT teinstellungen.kEinstellungenSektion, teinstellungen.cName, '' AS cBeschreibung, 
                  teinstellungen.cName AS cWertName, 'text' AS cInputTyp, teinstellungen.cModulId,
                  0 AS nSort, 1 AS nStandardAnzeigen 
                FROM teinstellungen
                LEFT JOIN teinstellungenconf
                    ON teinstellungenconf.cWertName = teinstellungen.cName
                    AND teinstellungenconf.kEinstellungenSektion = teinstellungen.kEinstellungenSektion
                WHERE teinstellungenconf.cWertName IS NULL)"
        );
        $this->setConfig(
            'admin_login_logger_mode',
            '1',
            \CONF_GLOBAL,
            'Adminloginversuche loggen?',
            'listbox',
            1503,
            (object)[
                'cBeschreibung' => 'Sollen Backend-Loginversuche geloggt werden?',
                'inputOptions'  => [
                    AdminLoginConfig::CONFIG_DB   => 'in Datenbank',
                    AdminLoginConfig::CONFIG_FILE => 'in Textdatei'
                ]
            ]
        );
        $this->execute('UPDATE tjtllog SET nLevel = ' . Level::Alert->value . ' WHERE nLevel = 1');
        $this->execute('UPDATE tjtllog SET nLevel = ' . Level::Info->value . ' WHERE nLevel = 2');
        $this->execute('UPDATE tjtllog SET nLevel = ' . Level::Debug->value . ' WHERE nLevel = 4');
        $this->execute(
            'UPDATE teinstellungen 
            SET cWert = ' . Level::Debug->value . " 
            WHERE cName = 'systemlog_flag' 
            AND (cWert = 4 OR cWert = 5 OR cWert = 6 OR cWert = 7)"
        );
        $this->execute(
            'UPDATE teinstellungen 
            SET cWert = ' . Level::Info->value . " 
            WHERE cName = 'systemlog_flag' 
            AND (cWert = 2 OR cWert = 3)"
        );
        $this->execute(
            'UPDATE teinstellungen 
            SET cWert = ' . Level::Alert->value . "
             WHERE cName = 'systemlog_flag' 
             AND cWert = 1"
        );
        $this->execute(
            "UPDATE teinstellungenconf 
            SET cInputTyp = 'number', nStandardAnzeigen = 0 
            WHERE cName = 'systemlog_flag' 
            AND cInputTyp = 'text'
            AND kEinstellungenSektion = 1"
        );
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->removeConfig('admin_login_logger_mode');
    }
}
