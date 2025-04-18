<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Event;
use App\Entity\Attendee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/bookings')]
class BookingController extends AbstractController
{
    #[Route('', name: 'booking_create', methods: ['POST'])]
    public function book(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $event = $em->getRepository(Event::class)->find($data['event_id']);
        $attendee = $em->getRepository(Attendee::class)->find($data['attendee_id']);

        if (!$event || !$attendee) {
            return $this->json(['error' => 'Event or Attendee not found'], 404);
        }

        // Check for overbooking
        $bookedCount = $em->getRepository(Booking::class)->count(['event' => $event]);
        if ($bookedCount >= $event->getCapacity()) {
            return $this->json(['error' => 'Event is fully booked'], 400);
        }

        // Prevent duplicate bookings
        $existing = $em->getRepository(Booking::class)->findOneBy([
            'event' => $event,
            'attendee' => $attendee
        ]);
        if ($existing) {
            return $this->json(['error' => 'Attendee already booked this event'], 400);
        }

        $booking = new Booking();
        $booking->setEvent($event);
        $booking->setAttendee($attendee);

        $em->persist($booking);
        $em->flush();

        return $this->json($booking);
    }

    #[Route('/', name: 'list_bookings', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $bookings = $em->getRepository(Booking::class)->findAll();

        $data = [];
        foreach ($bookings as $booking) {
            $data[] = [
                'id' => $booking->getId(),
                'attendee' => $booking->getAttendee()?->getName(),
                'event' => $booking->getEvent()?->getTitle(),
            ];
        }
        return $this->json($data);

    }

}