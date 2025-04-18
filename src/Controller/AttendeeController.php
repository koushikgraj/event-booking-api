<?php

namespace App\Controller;

use App\Entity\Attendee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/attendees')]
class AttendeeController extends AbstractController
{
    #[Route('', name: 'attendee_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $attendee = new Attendee();
        $attendee->setName($data['name']);
        $attendee->setEmail($data['email']);

        $errors = $validator->validate($attendee);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $em->persist($attendee);
        $em->flush();

        return $this->json($attendee);
    }

    #[Route('', name: 'attendee_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $attendees = $em->getRepository(Attendee::class)->findAll();
        return $this->json($attendees);
    }
}
