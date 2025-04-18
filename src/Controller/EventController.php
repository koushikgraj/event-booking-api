<?php

namespace App\Controller;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/events')]
class EventController extends AbstractController
{

    #[Route('', name: 'event_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        
        $data = json_decode($request->getContent(), true);

        $event = new Event();
        $event->setTitle($data['title'] ?? '');
        $event->setDescription($data['description'] ?? '');
        $event->setCountry($data['country'] ?? '');
        $event->setCapacity($data['capacity'] ?? 0);
        $event->setStartDate(new \DateTime($data['startDate'] ?? 'now'));
        $event->setEndDate(new \DateTime($data['endDate'] ?? 'now'));

        $errors = $validator->validate($event);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $em->persist($event);
        $em->flush();

        return $this->json($event, 201);
    }

    #[Route('', name: 'event_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $events = $em->getRepository(Event::class)->findAll();
        return $this->json($events);
    }

    #[Route('/{id}', name: 'event_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(Event::class)->find($id);

        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        return $this->json([
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'country' => $event->getCountry(),
            'capacity' => $event->getCapacity(),
            'startDate' => $event->getStartDate()->format('Y-m-d\TH:i:s'),
            'endDate' => $event->getEndDate()->format('Y-m-d\TH:i:s'),
        ]);
    }

    #[Route('/{id}', name: 'event_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(Event::class)->find($id);
        if (!$event) return $this->json(['error' => 'Event not found'], 404);

        $data = json_decode($request->getContent(), true);
        $event->setTitle($data['title'] ?? $event->getTitle());
        $event->setDescription($data['description'] ?? $event->getDescription());
        $event->setCountry($data['country'] ?? $event->getCountry());
        $event->setCapacity($data['capacity'] ?? $event->getCapacity());
        $event->setStartDate(new \DateTime($data['startDate'] ?? $event->getStartDate()->format('Y-m-d')));
        $event->setEndDate(new \DateTime($data['endDate'] ?? $event->getEndDate()->format('Y-m-d')));

        $em->flush();

        return $this->json($event);
    }

    #[Route('/{id}', name: 'event_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $event = $em->getRepository(Event::class)->find($id);
        if (!$event) return $this->json(['error' => 'Event not found'], 404);

        $em->remove($event);
        $em->flush();

        return $this->json(['message' => 'Event deleted']);
    }
}

