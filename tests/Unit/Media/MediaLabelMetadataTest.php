<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Tests\Unit\Media;

use InvalidArgumentException;
use MGDAIImageLabels\Media\MediaLabelMetadata;
use PHPUnit\Framework\TestCase;

/**
 * Prüft die Invarianten des unveränderlichen Kennzeichnungswerts selbst.
 */
final class MediaLabelMetadataTest extends TestCase
{
    /**
     * Ein unbekannter Status darf nicht direkt in ein Domain-Objekt gelangen.
     */
    public function testConstructorRejectsInvalidStatus(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ungültiger KI-Kennzeichnungsstatus');

        new MediaLabelMetadata('beliebig', null, null);
    }

    /**
     * Eine unbekannte Position darf nicht direkt in ein Domain-Objekt gelangen.
     */
    public function testConstructorRejectsInvalidPosition(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ungültige Position');

        new MediaLabelMetadata('generated', 'center', null);
    }

    /**
     * Ein unbekanntes Theme darf nicht direkt in ein Domain-Objekt gelangen.
     */
    public function testConstructorRejectsInvalidTheme(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ungültiges Theme');

        new MediaLabelMetadata('generated', null, 'javascript');
    }
}
