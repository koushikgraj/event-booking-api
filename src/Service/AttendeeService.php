<?php
namespace App\Service;

use App\Entity\Attendee;
use Doctrine\ORM\EntityManagerInterface;

class AttendeeService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createAttendee(array $data): Attendee
    {
        $attendee = new Attendee();
        $attendee->setName($data['name'] ?? '');
        $attendee->setEmail($data['email'] ?? '');

        $this->em->persist($attendee);
        $this->em->flush();

        return $attendee;
    }

    public function getAll(): array
    {
        return $this->em->getRepository(Attendee::class)->findAll();
    }

    public function findById(int $id): ?Attendee
    {
        return $this->em->getRepository(Attendee::class)->find($id);
    }
}
