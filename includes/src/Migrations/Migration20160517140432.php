<?php

/**
 * add_lang_key_dimensions_2d
 *
 * @author msc
 * @created Tue, 17 May 2016 14:04:32 +0200
 */

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20160517140432
 */
class Migration20160517140432 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'msc';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->setLocalization('ger', 'productDetails', 'dimensions2d', 'Abmessungen (L&times;H)');
        $this->setLocalization('eng', 'productDetails', 'dimensions2d', 'Dimensions (L&times;H)');
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->execute("DELETE FROM `tsprachwerte` WHERE cName = 'dimensions2d'");
    }
}
