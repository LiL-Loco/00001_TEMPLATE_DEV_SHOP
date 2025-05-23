<?php

/**
 * new category structure
 *
 * @author fm
 * @created Mo, 11 Apr 2016 09:31:13 +0100
 */

declare(strict_types=1);

namespace JTL\Migrations;

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration20160411093113
 */
class Migration20160411093113 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'fm';
    }

    private function rebuildCategoryTree(int $parent_id, int $left): int
    {
        // the right value of this node is the left value + 1
        $right = $left + 1;
        // get all children of this node
        $result = $this->getDB()->getObjects(
            'SELECT kKategorie 
                FROM tkategorie 
                WHERE kOberKategorie = :pid 
                ORDER BY nSort, cName',
            ['pid' => $parent_id]
        );
        foreach ($result as $res) {
            $right = $this->rebuildCategoryTree((int)$res->kKategorie, $right);
        }
        // we've got the left value, and now that we've processed the children of this node we also know the right value
        $this->execute(
            'UPDATE tkategorie 
                SET lft = ' . $left . ', rght = ' . $right . ' 
                WHERE kKategorie = ' . $parent_id
        );

        // return the right value of this node + 1
        return $right + 1;
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->rebuildCategoryTree(0, 1);
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->execute('UPDATE `tkategorie` SET `lft` = 0, `rght` = 0;');
    }
}
