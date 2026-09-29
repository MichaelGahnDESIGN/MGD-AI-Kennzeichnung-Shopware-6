<?php

declare(strict_types=1);

namespace MGDAIImageLabels\ScheduledTask;

use MGDAIImageLabels\Update\GitHubReleaseUpdater;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: UpdateTask::class)]
final class UpdateTaskHandler extends ScheduledTaskHandler
{
    public function __construct(EntityRepository $scheduledTaskRepository, LoggerInterface $logger, private readonly SystemConfigService $config, private readonly GitHubReleaseUpdater $updater)
    {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        // Updates gelten global; ein Sales-Channel-Override kann sie nicht aktivieren.
        if (!$this->config->getBool('MGDAIImageLabels.config.automaticUpdates')) { return; }
        try { $this->updater->checkAndPrepare(Context::createDefaultContext()); } catch (\Throwable $error) {
            $this->exceptionLogger->warning('MGD KI-Bildkennzeichnung: automatische Update-Vorbereitung fehlgeschlagen.', ['exceptionType' => $error::class]);
        }
    }
}
