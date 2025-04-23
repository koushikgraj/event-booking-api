<?php

namespace App\Service;

use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Event;

class EventService
{
    private EventRepository $eventRepository;
    private EntityManagerInterface $em;

    // Constructor injection of EventRepository and EntityManagerInterface
    public function __construct(EventRepository $eventRepository, EntityManagerInterface $em)
    {
        $this->eventRepository = $eventRepository;
        $this->em = $em;
    }

    // Example method for creating an event
    public function createEvent(array $data)
    {
        $event = new Event();
        $event->setTitle($data['title']);
        $event->setDescription($data['description']);
        $event->setStartDate(new \DateTime($data['startDate']));
        $event->setEndDate(new \DateTime($data['endDate']));
        $event->setCapacity($data['capacity']);
        $event->setCountry($data['country']);

        // Persist the event to the database
        $this->em->persist($event);
        $this->em->flush();

        return $event;
    }

    // Example method for getting all events
    public function getAllEvents()
    {
        return $this->eventRepository->findAll();
    }

    // Example method for finding an event by its ID
    public function getEventById(int $id)
    {
        return $this->eventRepository->find($id);
    }

    // Example method for updating an event
    public function updateEvent(Event $event, array $data)
    {
        $event->setTitle($data['title']);
        $event->setDescription($data['description']);
        $event->setStartDate(new \DateTime($data['startDate']));
        $event->setEndDate(new \DateTime($data['endDate']));
        $event->setCapacity($data['capacity']);
        $event->setCountry($data['country']);

        $this->em->flush();

        return $event;
    }

    // Example method for deleting an event
    public function deleteEvent(Event $event)
    {
        $this->em->remove($event);
        $this->em->flush();
    }
}
