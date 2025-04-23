<?php
// src/Service/BookingService.php

namespace App\Service;

use App\Entity\Booking;
use App\Entity\Event;
use App\Entity\Attendee;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingService
{
    public function __construct(
        private EntityManagerInterface $em,
        private BookingRepository $bookingRepository
    ) {}

    public function createBooking(int $eventId, int $attendeeId): Booking
    {
        $event = $this->em->getRepository(Event::class)->find($eventId);
        $attendee = $this->em->getRepository(Attendee::class)->find($attendeeId);

        if (!$event || !$attendee) {
            throw new NotFoundHttpException('Event or Attendee not found.');
        }

        $bookedCount = $this->bookingRepository->count(['event' => $event]);
        if ($bookedCount >= $event->getCapacity()) {
            throw new BadRequestHttpException('Event is fully booked.');
        }

        $existing = $this->bookingRepository->findOneBy([
            'event' => $event,
            'attendee' => $attendee
        ]);
        if ($existing) {
            throw new BadRequestHttpException('Attendee already booked this event.');
        }

        $booking = new Booking();
        $booking->setEvent($event);
        $booking->setAttendee($attendee);

        $this->em->persist($booking);
        $this->em->flush();

        return $booking;
    }

    public function listBookings(): array
    {
        return $this->bookingRepository->findAll();
    }
}
