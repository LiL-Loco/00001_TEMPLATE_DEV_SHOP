<?php

/**
 * adds additional hints to payments and shipping
 *
 * @author ms
 * @created Wed, 19 Oct 2016 09:01:00 +0200
 */

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20161019090100
 */
class Migration20161019090100 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'ms';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->execute(
            'ALTER TABLE tversandartsprache
                ADD COLUMN cHinweistextShop TEXT NULL DEFAULT NULL AFTER cHinweistext;'
        );
        $this->execute('UPDATE tversandartsprache SET cHinweistextShop = cHinweistext;');

        $this->execute(
            'ALTER TABLE tzahlungsartsprache
                ADD COLUMN cHinweisTextShop TEXT NULL DEFAULT NULL AFTER cHinweisText;'
        );
        $this->execute('UPDATE tzahlungsartsprache SET cHinweisTextShop = cHinweisText;');
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->dropColumn('tversandartsprache', 'cHinweistextShop');

        $this->dropColumn('tzahlungsartsprache', 'cHinweistextShop');
    }
}
