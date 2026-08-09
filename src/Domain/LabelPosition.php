<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Domain;

/**
 * Definiert die vier sicheren Ecken, an denen eine Kennzeichnung erscheinen darf.
 */
enum LabelPosition: string
{
    /** Obere linke Bildecke. */
    case TopLeft = 'top-left';

    /** Obere rechte Bildecke. */
    case TopRight = 'top-right';

    /** Untere linke Bildecke. */
    case BottomLeft = 'bottom-left';

    /** Untere rechte Bildecke. */
    case BottomRight = 'bottom-right';
}
