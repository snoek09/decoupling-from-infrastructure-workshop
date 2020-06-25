<?php
declare(strict_types=1);

namespace DevPro\Application;

final class UpcomingEvent
{
    /**
     * @var string
     */
    private $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
