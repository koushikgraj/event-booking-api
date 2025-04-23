<?php
// src/Controller/BookingController.php

namespace App\Controller;

use App\Service\BookingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/bookings')]
class BookingController extends AbstractController
{
    public function __construct(private BookingService $bookingService) {}

    #[Route('', name: 'booking_create', methods: ['POST'])]
    public function book(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['event_id'], $data['attendee_id'])) {
            return $this->json(['error' => 'event_id and attendee_id required'], 400);
        }
        if (!isset($data['eventId']) || !is_numeric($data['eventId'])) {
            return $this->json(['error' => 'Invalid event ID'], 400);
        }
        $event = $this->eventRepository->find($eventId);
        if (!$event) {
            return new JsonResponse(['error' => 'Invalid event ID'], 404);
        }

        $attendee = $this->attendeeRepository->find($attendeeId);
        if (!$attendee) {
            return new JsonResponse(['error' => 'Invalid attendee ID'], 404);
        }
        
        $eventId = (int) $data['eventId'];

        try {
            $booking = $this->bookingService->createBooking($eventId, $data['attendee_id']);
            return $this->json($booking, 201);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    #[Route('/', name: 'list_bookings', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $bookings = $this->bookingService->listBookings();

        $data = array_map(fn($b) => [
            'id' => $b->getId(),
            'attendee' => $b->getAttendee()?->getName(),
            'event' => $b->getEvent()?->getTitle(),
        ], $bookings);

        return $this->json($data);
    }
}
