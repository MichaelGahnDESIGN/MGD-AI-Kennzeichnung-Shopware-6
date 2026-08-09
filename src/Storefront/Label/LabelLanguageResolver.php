<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Storefront\Label;

use MGDAIImageLabels\Domain\LabelLanguage;

/**
 * Löst die konfigurierte Sprachwahl auf genau eine sichere Ausgabesprache auf.
 *
 * Der Resolver gibt bewusst nur die kurzen internen Werte „de“ oder „en“
 * zurück. Eine unbekannte Einstellung oder eine nicht deutsche Locale fällt
 * auf Englisch als neutralen und im Fachkonzept festgelegten Rückfall zurück.
 */
final class LabelLanguageResolver
{
    /**
     * @param string $setting Bereits normalisierter Sprachmodus der Plugin-Konfiguration.
     * @param string $salesChannelLocale Locale des aktuellen Verkaufskanals.
     */
    public function resolve(string $setting, string $salesChannelLocale): string
    {
        if ($setting === LabelLanguage::German->value) {
            return LabelLanguage::German->value;
        }

        if ($setting === LabelLanguage::English->value) {
            return LabelLanguage::English->value;
        }

        if ($setting !== LabelLanguage::Auto->value) {
            return LabelLanguage::English->value;
        }

        if (preg_match('/^de(?:-|$)/iD', $salesChannelLocale) === 1) {
            return LabelLanguage::German->value;
        }

        return LabelLanguage::English->value;
    }
}
