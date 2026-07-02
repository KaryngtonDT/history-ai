<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\KnowledgeCollection;
use App\Domain\ShadowSecondBrain\KnowledgeDomainHeatmapEntry;
use App\Domain\ShadowSecondBrain\KnowledgeEntry;
use App\Domain\ShadowSecondBrain\KnowledgeSourceType;
use App\Domain\ShadowSecondBrain\KnowledgeStatistics;
use App\Domain\ShadowSecondBrain\KnowledgeTimelineCollection;

final class KnowledgeStatisticsBuilder
{
    public function build(KnowledgeCollection $entries, KnowledgeTimelineCollection $timeline): KnowledgeStatistics
    {
        $videoCount = 0;
        $pdfCount = 0;
        $conversationCount = 0;
        $exerciseCount = 0;
        $missionCount = 0;

        foreach ($timeline->all() as $event) {
            match ($event->sourceType()) {
                KnowledgeSourceType::Video, KnowledgeSourceType::Youtube => ++$videoCount,
                KnowledgeSourceType::Pdf => ++$pdfCount,
                KnowledgeSourceType::Conversation => ++$conversationCount,
                KnowledgeSourceType::Exercise => ++$exerciseCount,
                KnowledgeSourceType::Mission => ++$missionCount,
                default => null,
            };
        }

        return new KnowledgeStatistics(
            $videoCount,
            $pdfCount,
            $conversationCount,
            $exerciseCount,
            $missionCount,
            count($entries->all()),
            $this->domainHeatmap($entries),
        );
    }

    /** @return list<KnowledgeDomainHeatmapEntry> */
    private function domainHeatmap(KnowledgeCollection $entries): array
    {
        if ([] === $entries->all()) {
            return [];
        }

        /** @var array<string, array{label: string, totalMastery: int, count: int}> $domains */
        $domains = [];

        foreach ($entries->all() as $entry) {
            $domainKey = $this->domainKeyFor($entry);
            $label = ucwords(str_replace('_', ' ', $domainKey));

            if (!isset($domains[$domainKey])) {
                $domains[$domainKey] = ['label' => $label, 'totalMastery' => 0, 'count' => 0];
            }

            $domains[$domainKey]['totalMastery'] += $entry->masteryPercent();
            ++$domains[$domainKey]['count'];
        }

        $heatmap = [];

        foreach ($domains as $key => $data) {
            $percent = (int) round($data['totalMastery'] / max(1, $data['count']));
            $heatmap[] = new KnowledgeDomainHeatmapEntry($key, $data['label'], $percent);
        }

        usort(
            $heatmap,
            static fn (KnowledgeDomainHeatmapEntry $left, KnowledgeDomainHeatmapEntry $right): int => $right->percent() <=> $left->percent(),
        );

        return $heatmap;
    }

    private function domainKeyFor(KnowledgeEntry $entry): string
    {
        $parts = explode('_', $entry->conceptKey());

        return $parts[0] !== '' ? $parts[0] : 'general';
    }
}
