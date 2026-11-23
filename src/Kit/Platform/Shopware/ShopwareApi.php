<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Platform\Shopware;

// Schnittstelle zu Shopware, damit der Rest ohne Shopware testbar bleibt. Echt: ShopwareServices
interface ShopwareApi
{
    /**
     * Daten zum Plugin aus der Shopware-Plugin-Tabelle, null = gibts nicht
     * @return array{version:string,upgrade:?string,installed:bool,active:bool,path:string,composer:bool}|null
     */
    public function plugin(string $technicalName, ?object $context = null): ?array;

    /** geprüfte ZIP nach custom/plugins entpacken + Plugin-Liste neu lesen (wie "Erweiterung hochladen") */
    public function extract(string $zipFile, object $context): void;

    /** Shopware-Lifecycle "Aktualisieren" fürs Plugin */
    public function update(string $technicalName, object $context): void;

    /** false = Erweiterungen dürfen im Admin nicht verwaltet werden (shopware.deployment.runtime_extension_management) */
    public function runtimeAllowed(): bool;

    /** Domain des Shops, klein geschrieben, '' = unbekannt */
    public function domain(): string;

    public function get(string $key): mixed;

    public function set(string $key, mixed $value): void;
}
