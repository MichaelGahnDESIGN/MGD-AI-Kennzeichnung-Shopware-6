<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Domain;

/**
 * Definiert die geschlossene fachliche Wertemenge für den KI-Status eines Bildes.
 */
enum LabelStatus: string
{
    /** Das Bild benötigt keine sichtbare KI-Kennzeichnung. */
    case None = 'none';

    /** Das Bild wurde vollständig durch KI erzeugt. */
    case Generated = 'generated';

    /** Das Bild enthält teilweise KI-generierte Bestandteile. */
    case PartiallyGenerated = 'partially-generated';

    /** Das Bild wurde mit KI verändert. */
    case Modified = 'modified';

    /** Das Bild ist ein Deepfake oder eine vergleichbare Manipulation. */
    case Deepfake = 'deepfake';
}
