<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Update;

/**
 * Konservative Freigabe ohne eigene Interpretation von Composer-Versionsbedingungen.
 * Veränderte Abhängigkeiten gehören in die manuelle Shopware-Installation mit nativer Prüfung.
 */
final class RequirementPolicy
{
    /** @return array<string, string> */
    public static function normalize(mixed $requirements): array
    {
        if (!is_array($requirements) || $requirements === [] || array_is_list($requirements)) {
            throw new \RuntimeException('Ein vollständiger Composer-require-Bereich ist erforderlich.');
        }
        $normalized = [];
        foreach ($requirements as $package => $constraint) {
            if (!is_string($package) || !is_string($constraint) || trim($constraint) === '') {
                throw new \RuntimeException('Composer-Anforderungen müssen benannte Pakete mit Versionsbedingungen enthalten.');
            }
            $normalized[$package] = trim($constraint);
        }
        ksort($normalized, SORT_STRING);
        return $normalized;
    }

    public static function assertIdentical(mixed $installed, mixed $candidate): void
    {
        if (self::normalize($installed) !== self::normalize($candidate)) {
            throw new \RuntimeException('Geänderte Runtime-/Composer-Anforderungen erfordern eine manuelle Shopware-Installation mit nativer Anforderungsprüfung.');
        }
    }
}
