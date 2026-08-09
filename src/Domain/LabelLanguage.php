<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Domain;

/**
 * Definiert die erlaubten Sprachmodi einer KI-Bildkennzeichnung.
 */
enum LabelLanguage: string
{
    /** Verwendet automatisch die Sprache des aktuellen Shop-Kontexts. */
    case Auto = 'auto';

    /** Erzwingt die deutsche Kennzeichnung. */
    case German = 'de';

    /** Erzwingt die englische Kennzeichnung. */
    case English = 'en';
}
