<?php
namespace App\Controller;

use App\Entity\Attendee;
use App\Service\AttendeeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/attendees')]
class AttendeeController extends AbstractController
{
    private AttendeeService $attendeeService;

    public function __construct(AttendeeService $attendeeService)
    {
        $this->attendeeService = $attendeeService;
    }

    #[Route('', name: 'attendee_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $attendee = new Attendee();
        $attendee->setName($data['name'] ?? '');
        $attendee->setEmail($data['email'] ?? '');

        $errors = $validator->validate($attendee);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        $em->persist($attendee);
        $em->flush();

        return $this->json($attendee);
    }


    #[Route('', name: 'attendee_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $attendees = $this->attendeeService->getAll();
        $data = [];

        foreach ($attendees as $attendee) {
            $data[] = [
                'id' => $attendee->getId(),
                'name' => $attendee->getName(),
                'email' => $attendee->getEmail(),
            ];
        }

        return $this->json($data);
    }
}
