<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Platform\Shopware;

// extra zu ShopwareApi (UninstallShop braucht das nicht): Ordner für Katalog-Bilder, nicht im Web
interface CatalogMediaDir
{
    /** <projekt>/var/mgd-kit/<produkt>/catalog, '' = unbekannt (dann nur Farbfläche) */
    public function catalogMediaDir(string $product): string;
}
