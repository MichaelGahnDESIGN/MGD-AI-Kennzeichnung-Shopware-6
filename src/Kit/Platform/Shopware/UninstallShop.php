<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Platform\Shopware;

// Mini-Shop nur fürs Deinstallieren: Plugin-Dienste sind da evtl schon weg, SystemConfigService ist public
// Version aus dem UninstallContext, Updates gehen hier nie
// Befund Pilot barrierefrei (SW 6.7): Plugin ist vor uninstall() schon AUS, SystemConfigService filtert dessen Config raus
// > mit DBAL direkt in system_config lesen/schreiben (nur globale Werte, sales_channel_id NULL)
final class UninstallShop implements ShopwareApi
{
    /**
     * @param object $config Shopware\Core\System\SystemConfig\SystemConfigService
     * @param object|null $db Doctrine\DBAL\Connection (fetchOne/executeStatement), null = nur SystemConfigService
     */
    public function __construct(private readonly object $config, private readonly string $technicalName, private readonly string $version, private readonly ?object $db = null)
    {
    }

    public function plugin(string $technicalName, ?object $context = null): ?array
    {
        if ($technicalName !== $this->technicalName) {
            return null;
        }

        return ['version' => $this->version, 'upgrade' => null, 'installed' => true, 'active' => false, 'path' => '', 'composer' => false];
    }

    public function extract(string $zipFile, object $context): void
    {
        throw new \LogicException('Beim Deinstallieren nicht erlaubt.');
    }

    public function update(string $technicalName, object $context): void
    {
        throw new \LogicException('Beim Deinstallieren nicht erlaubt.');
    }

    public function runtimeAllowed(): bool
    {
        return false;
    }

    // gleiche Domain wie im Betrieb, sonst kommt die Anmeldung bei update/activate nie an (Befund slider)
    public function domain(): string
    {
        return ShopwareServices::domainOf($this->db);
    }

    public function get(string $key): mixed
    {
        if ($this->db === null) {
            return $this->config->get($key);
        }
        $raw = $this->db->fetchOne('SELECT configuration_value FROM system_config WHERE configuration_key = ? AND sales_channel_id IS NULL LIMIT 1', [$key]);
        $d = is_string($raw) ? json_decode($raw, true) : null;

        return is_array($d) && array_key_exists('_value', $d) ? $d['_value'] : null;
    }

    // null = Eintrag löschen (wie Shopware)
    public function set(string $key, mixed $value): void
    {
        if ($this->db === null) {
            $this->config->set($key, $value);

            return;
        }
        if ($value === null) {
            $this->db->executeStatement('DELETE FROM system_config WHERE configuration_key = ? AND sales_channel_id IS NULL', [$key]);

            return;
        }
        $json = (string) json_encode(['_value' => $value], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $now = gmdate('Y-m-d H:i:s.v');
        $has = $this->db->fetchOne('SELECT 1 FROM system_config WHERE configuration_key = ? AND sales_channel_id IS NULL LIMIT 1', [$key]);
        if ($has !== false && $has !== null) {
            $this->db->executeStatement('UPDATE system_config SET configuration_value = ?, updated_at = ? WHERE configuration_key = ? AND sales_channel_id IS NULL', [$json, $now, $key]);

            return;
        }
        $this->db->executeStatement('INSERT INTO system_config (id, configuration_key, configuration_value, sales_channel_id, created_at) VALUES (?, ?, ?, NULL, ?)', [random_bytes(16), $key, $json, $now]);
    }

    // DBAL-Verbindung aus dem SystemConfigService holen (private connection), damit Plugins nix ändern müssen
    public static function connectionOf(object $config): ?object
    {
        try {
            $r = new \ReflectionObject($config);
            while ($r !== false) {
                if ($r->hasProperty('connection')) {
                    $p = $r->getProperty('connection');
                    $c = $p->getValue($config);

                    return is_object($c) && method_exists($c, 'fetchOne') && method_exists($c, 'executeStatement') ? $c : null;
                }
                $r = $r->getParentClass();
            }
        } catch (\Throwable) {
        }

        return null;
    }
}
