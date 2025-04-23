<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Exception\ValidatorException;

class ValidationService
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validates the event data for creation and update.
     * 
     * @param array $data
     * @return array
     */
    public function validateEventData(array $data): array
    {
        $errors = [];

        // Validate title
        if (empty($data['title'])) {
            $errors['title'] = 'Title cannot be empty.';
        }

        // Validate description
        if (empty($data['description'])) {
            $errors['description'] = 'Description cannot be empty.';
        }

        // Validate country
        if (empty($data['country'])) {
            $errors['country'] = 'Country cannot be empty.';
        }

        // Validate capacity (should be an integer and greater than 0)
        if (empty($data['capacity']) || !is_numeric($data['capacity']) || $data['capacity'] <= 0) {
            $errors['capacity'] = 'Capacity must be a positive number.';
        }

        // Validate startDate and endDate (must be a valid date)
        if (empty($data['startDate']) || !$this->isValidDate($data['startDate'])) {
            $errors['startDate'] = 'Invalid start date format. Expected format: YYYY-MM-DD.';
        }

        if (empty($data['endDate']) || !$this->isValidDate($data['endDate'])) {
            $errors['endDate'] = 'Invalid end date format. Expected format: YYYY-MM-DD.';
        }

        // Check if end date is later than start date
        if (isset($data['startDate'], $data['endDate']) && $this->isValidDate($data['startDate']) && $this->isValidDate($data['endDate'])) {
            $startDate = new \DateTime($data['startDate']);
            $endDate = new \DateTime($data['endDate']);

            if ($startDate > $endDate) {
                $errors['endDate'] = 'End date must be later than start date.';
            }
        }

        return $errors;
    }

    /**
     * Checks if the provided string is a valid date in the format YYYY-MM-DD.
     * 
     * @param string $date
     * @return bool
     */
    private function isValidDate(string $date): bool
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
        return $dateTime && $dateTime->format('Y-m-d') === $date;
    }
}
