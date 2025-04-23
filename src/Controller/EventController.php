<?php

namespace App\Controller;

use App\Entity\Event;
use App\Service\EventService;
use App\Service\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/events')]
class EventController extends AbstractController
{
    private EventService $eventService;
    private ValidationService $validationService;
    private EntityManagerInterface $em;

    public function __construct(EventService $eventService, ValidationService $validationService, EntityManagerInterface $em)
    {
        $this->eventService = $eventService;
        $this->validationService = $validationService;
        $this->em = $em;
    }

    #[Route('', name: 'event_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate the event data
        $errors = $this->validationService->validateEventData($data);

        if (!empty($errors)) {
            return $this->json(['errors' => $errors], 400);
        }

        // Create the event
        $event = $this->eventService->createEvent($data);

        return $this->json($event, 201);
    }

    #[Route('', name: 'event_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $events = $this->eventService->getAllEvents();
        return $this->json($events);
    }

    #[Route('/{id}', name: 'event_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $event = $this->eventService->getEventById($id);

        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        return $this->json($event);
    }

    #[Route('/{id}', name: 'event_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate the event data
        $errors = $this->validationService->validateEventData($data);

        if (!empty($errors)) {
            return $this->json(['errors' => $errors], 400);
        }

        // Get existing event
        $event = $this->eventService->getEventById($id);
        
        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        // Update the event details
        $updatedEvent = $this->eventService->updateEvent($event, $data);

        return $this->json($updatedEvent);
    }

    #[Route('/{id}', name: 'event_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $event = $this->eventService->getEventById($id);

        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        $this->eventService->deleteEvent($event);

        return $this->json(['message' => 'Event deleted']);
    }
}
