<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Domain;

/**
 * Definiert die erlaubten, festen Farbthemen einer Bildkennzeichnung.
 */
enum LabelTheme: string
{
    /** Orientiert sich an der bevorzugten Systemdarstellung. */
    case Auto = 'auto';

    /** Erzwingt eine helle Darstellung. */
    case Light = 'light';

    /** Erzwingt eine dunkle Darstellung. */
    case Dark = 'dark';
}
