<?php

declare(strict_types=1);

namespace MGDAIImageLabels\Kit\Platform\Shopware;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

// Handler zur PingTask (angemeldet per Tag messenger.message_handler in services.xml, kein Attribut > sonst doppelt): meldet nur, wenn "Installation melden" an ist (macht der Install-Client selbst), wirft nie
final class PingTaskHandler extends ScheduledTaskHandler
{
    // SW 6.7 will den Logger als 2. Pflicht-Argument (6.5/6.6 optional) > immer mitgeben
    public function __construct(EntityRepository $scheduledTaskRepository, LoggerInterface $logger, private readonly ShopwareKit $kit)
    {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        try {
            $this->kit->installClient()?->ping();
        } catch (\Throwable) {
        }
    }
}
