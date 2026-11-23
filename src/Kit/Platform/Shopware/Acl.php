<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Platform\Shopware;

// Rechte serverseitig nochmal prüfen, zusätzlich zum _acl der Route
// nur echte Admin-Benutzer (AdminApiSource mit userId), keine Integrationen
final class Acl
{
    public const READ = ['system_config:read'];
    public const WRITE = ['system_config:read', 'system_config:update'];
    // dieselben Rechte wie Shopware für "Erweiterung hochladen" + "Aktualisieren"
    public const PLUGIN = ['system.plugin_maintain', 'system.plugin_upload'];

    /** @param list<string> $privileges alle müssen passen */
    public static function allowed(object $context, array $privileges): bool
    {
        if ($privileges === []) {
            return false;
        }
        try {
            $s = method_exists($context, 'getSource') ? $context->getSource() : null;
            if (!is_object($s) || !method_exists($s, 'isAllowed') || !method_exists($s, 'getUserId')) {
                return false;
            }
            $user = $s->getUserId();
            if (!is_string($user) || $user === '') {
                return false;
            }
            foreach ($privileges as $p) {
                if ($s->isAllowed($p) !== true) {
                    return false;
                }
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
