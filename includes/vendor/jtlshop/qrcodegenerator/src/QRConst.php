<?php

/**
 * Class QRConst
 *
 * @filesource   QRConst.php
 * @created      26.11.2015
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

declare(strict_types=1);

namespace qrcodegenerator\QRCode;

class QRConst
{
    /**
     * @var array<int, int>
     */
    public static array $RSBLOCK = [
        QRCode::ERROR_CORRECT_LEVEL_L => 0,
        QRCode::ERROR_CORRECT_LEVEL_M => 1,
        QRCode::ERROR_CORRECT_LEVEL_Q => 2,
        QRCode::ERROR_CORRECT_LEVEL_H => 3,
    ];

    /**
     * @var array<int, int[]>
     */
    public static array $MAX_BITS = [
        QRCode::TYPE_01 => [128, 152, 72, 104],
        QRCode::TYPE_02 => [224, 272, 128, 176],
        QRCode::TYPE_03 => [352, 440, 208, 272],
        QRCode::TYPE_04 => [512, 640, 288, 384],
        QRCode::TYPE_05 => [688, 864, 368, 496],
        QRCode::TYPE_06 => [864, 1088, 480, 608],
        QRCode::TYPE_07 => [992, 1248, 528, 704],
        QRCode::TYPE_08 => [1232, 1552, 688, 880],
        QRCode::TYPE_09 => [1456, 1856, 800, 1056],
        QRCode::TYPE_10 => [1728, 2192, 976, 1232],
    ];

    /** NOTE: we define constants direct as numbers (we've removed the bit-shiftings here) */
    public const MODE_NUMBER   = 1; // result of: 1 << 0
    public const MODE_ALPHANUM = 2; // result of: 1 << 1
    public const MODE_BYTE     = 4; // result of: 1 << 2
    public const MODE_KANJI    = 8; // result of: 1 << 3

    /**
     * @var array<int, int>
     */
    public static array $MODE = [
        QRConst::MODE_NUMBER   => 0,
        QRConst::MODE_ALPHANUM => 1,
        QRConst::MODE_BYTE     => 2,
        QRConst::MODE_KANJI    => 3,
    ];

    public const MASK_PATTERN000 = 0;
    public const MASK_PATTERN001 = 1;
    public const MASK_PATTERN010 = 2;
    public const MASK_PATTERN011 = 3;
    public const MASK_PATTERN100 = 4;
    public const MASK_PATTERN101 = 5;
    public const MASK_PATTERN110 = 6;
    public const MASK_PATTERN111 = 7;

    /** NOTE: we define constants direct as numbers (we've removed the bit-shiftings here) */
    public const G15_MASK = 21522; // (1 << 14)|(1 << 12)|(1 << 10)|(1 << 4)|(1 << 1)
    public const G15      = 1335;  // (1 << 10)|(1 << 8)|(1 << 5)|(1 << 4)|(1 << 2)|(1 << 1)|(1 << 0)
    public const G18      = 7973;  // (1 << 12)|(1 << 11)|(1 << 10)|(1 << 9)|(1 << 8)|(1 << 5)|(1 << 2)|(1 << 0)

    public const PAD0 = 0xEC;
    public const PAD1 = 0x11;

    /**
     * @var array<array<int[]>>
     */
    public static array $MAX_LENGTH = [
        [[41, 25, 17, 10], [34, 20, 14, 8], [27, 16, 11, 7], [17, 10, 7, 4]],
        [[77, 47, 32, 20], [63, 38, 26, 16], [48, 29, 20, 12], [34, 20, 14, 8]],
        [[127, 77, 53, 32], [101, 61, 42, 26], [77, 47, 32, 20], [58, 35, 24, 15]],
        [[187, 114, 78, 48], [149, 90, 62, 38], [111, 67, 46, 28], [82, 50, 34, 21]],
        [[255, 154, 106, 65], [202, 122, 84, 52], [144, 87, 60, 37], [106, 64, 44, 27]],
        [[322, 195, 134, 82], [255, 154, 106, 65], [178, 108, 74, 45], [139, 84, 58, 36]],
        [[370, 224, 154, 95], [293, 178, 122, 75], [207, 125, 86, 53], [154, 93, 64, 39]],
        [[461, 279, 192, 118], [365, 221, 152, 93], [259, 157, 108, 66], [202, 122, 84, 52]],
        [[552, 335, 230, 141], [432, 262, 180, 111], [312, 189, 130, 80], [235, 143, 98, 60]],
        [[652, 395, 271, 167], [513, 311, 213, 131], [364, 221, 151, 93], [288, 174, 119, 74]],
    ];

    /**
     * @var array<int[]>
     */
    public static array $BLOCK_TABLE = [
        // 1
        [1, 26, 19], // L
        [1, 26, 16], // M
        [1, 26, 13], // Q
        [1, 26, 9], // H
        // 2
        [1, 44, 34],
        [1, 44, 28],
        [1, 44, 22],
        [1, 44, 16],
        // 3
        [1, 70, 55],
        [1, 70, 44],
        [2, 35, 17],
        [2, 35, 13],
        // 4
        [1, 100, 80],
        [2, 50, 32],
        [2, 50, 24],
        [4, 25, 9],
        // 5
        [1, 134, 108],
        [2, 67, 43],
        [2, 33, 15, 2, 34, 16],
        [2, 33, 11, 2, 34, 12],
        // 6
        [2, 86, 68],
        [4, 43, 27],
        [4, 43, 19],
        [4, 43, 15],
        // 7
        [2, 98, 78],
        [4, 49, 31],
        [2, 32, 14, 4, 33, 15],
        [4, 39, 13, 1, 40, 14],
        // 8
        [2, 121, 97],
        [2, 60, 38, 2, 61, 39],
        [4, 40, 18, 2, 41, 19],
        [4, 40, 14, 2, 41, 15],
        // 9
        [2, 146, 116],
        [3, 58, 36, 2, 59, 37],
        [4, 36, 16, 4, 37, 17],
        [4, 36, 12, 4, 37, 13],
        // 10
        [2, 86, 68, 2, 87, 69],
        [4, 69, 43, 1, 70, 44],
        [6, 43, 19, 2, 44, 20],
        [6, 43, 15, 2, 44, 16],
    ];

    /**
     * @var array<int[]>
     */
    public static array $PATTERN_POSITION = [
        [],
        [6, 18],
        [6, 22],
        [6, 26],
        [6, 30],
        [6, 34],
        [6, 22, 38],
        [6, 24, 42],
        [6, 26, 46],
        [6, 28, 50],
        [6, 30, 54],
        [6, 32, 58],
        [6, 34, 62],
        [6, 26, 46, 66],
        [6, 26, 48, 70],
        [6, 26, 50, 74],
        [6, 30, 54, 78],
        [6, 30, 56, 82],
        [6, 30, 58, 86],
        [6, 34, 62, 90],
        [6, 28, 50, 72, 94],
        [6, 26, 50, 74, 98],
        [6, 30, 54, 78, 102],
        [6, 28, 54, 80, 106],
        [6, 32, 58, 84, 110],
        [6, 30, 58, 86, 114],
        [6, 34, 62, 90, 118],
        [6, 26, 50, 74, 98, 122],
        [6, 30, 54, 78, 102, 126],
        [6, 26, 52, 78, 104, 130],
        [6, 30, 56, 82, 108, 134],
        [6, 34, 60, 86, 112, 138],
        [6, 30, 58, 86, 114, 142],
        [6, 34, 62, 90, 118, 146],
        [6, 30, 54, 78, 102, 126, 150],
        [6, 24, 50, 76, 102, 128, 154],
        [6, 28, 54, 80, 106, 132, 158],
        [6, 32, 58, 84, 110, 136, 162],
        [6, 26, 54, 82, 110, 138, 166],
        [6, 30, 58, 86, 114, 142, 170],
    ];
}
