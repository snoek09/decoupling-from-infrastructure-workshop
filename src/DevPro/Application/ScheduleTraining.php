<?php
declare(strict_types=1);

namespace DevPro\Application;

use Common\EventDispatcher\EventDispatcher;
use DateTimeImmutable;
use DevPro\Domain\Model\Training\Training;
use DevPro\Domain\Model\Training\TrainingId;
use DevPro\Domain\Model\Training\TrainingRepository;
use DevPro\Domain\Model\User\UserId;
use DevPro\Domain\Model\User\UserRepository;

final class ScheduleTraining
{
    /**
     * @var TrainingRepository
     */
    private $trainingRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(
        TrainingRepository $trainingRepository,
        UserRepository $userRepository,
        EventDispatcher $eventDispatcher
    ) {
        $this->trainingRepository = $trainingRepository;
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function schedule(string $organizerId, string $title, string $scheduledDate): ?TrainingId
    {
        $dateTime = DateTimeImmutable::createFromFormat('d-m-Y', $scheduledDate);

        if ($dateTime === false) {
            return null;
        }

        $training = Training::schedule(
            $this->trainingRepository->nextIdentity(),
            $this->userRepository->getById(UserId::fromString($organizerId))->userId(),
            $title,
            $dateTime
        );

        $this->trainingRepository->save($training);

        $this->eventDispatcher->dispatchAll($training->releaseEvents());

        return $training->trainingId();
    }
}
