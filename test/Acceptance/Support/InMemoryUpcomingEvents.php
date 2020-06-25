<?php
declare(strict_types=1);

namespace Test\Acceptance\Support;

use DevPro\Application\UpcomingEvent;
use DevPro\Application\UpcomingEvents;

final class InMemoryUpcomingEvents implements UpcomingEvents
{
    /**
     * @var array
     */
    private $upcomingEvents = [];

    public function add(UpcomingEvent $upcomingEvent): void
    {
        $this->upcomingEvents[] = $upcomingEvent;
    }
}
