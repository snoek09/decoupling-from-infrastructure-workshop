<?php
declare(strict_types=1);

namespace Test\Acceptance\Support;

use Common\EventDispatcher\EventDispatcher;
use DevPro\Application\CreateUser;
use DevPro\Application\ScheduleTraining;
use DevPro\Application\UpcomingEvent;
use DevPro\Application\UpcomingEvents;
use DevPro\Domain\Model\Ticket\TicketRepository;
use DevPro\Domain\Model\Training\TrainingRepository;
use DevPro\Domain\Model\Training\TrainingWasScheduled;
use DevPro\Domain\Model\User\UserRepository;

final class TestServiceContainer
{
    /**
     * @var ClockForTesting | null
     */
    private $clock;

    /**
     * @var EventDispatcher | null
     */
    private $eventDispatcher;

    /**
     * @var EventSubscriberSpy | null
     */
    private $eventSubscriberSpy;

    /**
     * @var InMemoryUserRepository | null
     */
    private $userRepository;

    /**
     * @var InMemoryTrainingRepository | null
     */
    private $trainingRepository;

    /**
     * @var InMemoryTicketRepository
     */
    private $ticketRepository;

    /**
     * @var InMemoryUpcomingEvents | null
     */
    private $upcomingEvents;

    private function clock(): ClockForTesting
    {
        return $this->clock ?? $this->clock = new ClockForTesting();
    }

    public function eventDispatcher(): EventDispatcher
    {
        if ($this->eventDispatcher === null) {
            $this->eventDispatcher = new EventDispatcher();

            $this->eventDispatcher->subscribeToAllEvents($this->eventSubscriberSpy());

            $this->eventDispatcher->subscribeToAllEvents(
                function (object $event): void {
                    echo '- Event dispatched: ' . get_class($event) . "\n";
                }
            );

            $this->eventDispatcher->registerSubscriber(
                TrainingWasScheduled::class,
                function (TrainingWasScheduled $event): void {
                    $this->upcomingEvents()->add(
                        new UpcomingEvent($event->title())
                    );
                }
            );
        }

        return $this->eventDispatcher;
    }

    public function setCurrentDate(string $date): void
    {
        $this->clock()->setCurrentDate($date);
    }

    public function eventSubscriberSpy(): EventSubscriberSpy
    {
        return $this->eventSubscriberSpy ?? $this->eventSubscriberSpy = new EventSubscriberSpy();
    }

    /**
     * @return array<object>
     */
    public function dispatchedEvents(): array
    {
        return $this->eventSubscriberSpy()->dispatchedEvents();
    }

    public function userRepository(): UserRepository
    {
        return $this->userRepository ?? $this->userRepository = new InMemoryUserRepository();
    }

    public function trainingRepository(): TrainingRepository
    {
        return $this->trainingRepository ?? $this->trainingRepository = new InMemoryTrainingRepository();
    }

    public function ticketRepository(): TicketRepository
    {
        return $this->ticketRepository ?? $this->ticketRepository = new InMemoryTicketRepository();
    }

    public function upcomingEvents(): UpcomingEvents
    {
        return $this->upcomingEvents ?? $this->upcomingEvents = new InMemoryUpcomingEvents();
    }

    public function createUser(): CreateUser
    {
        return new CreateUser($this->userRepository(), $this->eventDispatcher());
    }

    public function scheduleTraining(): ScheduleTraining
    {
        return new ScheduleTraining($this->trainingRepository(), $this->userRepository(), $this->eventDispatcher());
    }
}
