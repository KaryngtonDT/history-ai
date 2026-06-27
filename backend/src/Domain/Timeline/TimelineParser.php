<?php

declare(strict_types=1);

namespace App\Domain\Timeline;

final class TimelineParser
{
    private const DEFAULT_SECTION_TITLE = 'Timeline';

    public static function parse(string $markdown): Timeline
    {
        /** @var list<TimelineSection> $sections */
        $sections = [];
        $current = null;

        foreach (explode("\n", $markdown) as $line) {
            if (str_starts_with($line, '# ') && !str_starts_with($line, '## ')) {
                continue;
            }

            if (str_starts_with($line, '## ')) {
                if (null !== $current) {
                    $sections[] = $current;
                }

                $current = new TimelineSection(substr($line, 3), []);

                continue;
            }

            if (str_starts_with($line, '- ')) {
                if (null === $current) {
                    $current = new TimelineSection(self::DEFAULT_SECTION_TITLE, []);
                }

                $events = $current->events();
                $events[] = new TimelineEvent(substr($line, 2));
                $current = new TimelineSection($current->title(), $events);
            }
        }

        if (null !== $current) {
            $sections[] = $current;
        }

        return new Timeline($sections);
    }
}
