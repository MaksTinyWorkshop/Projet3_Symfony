<?php

namespace App\Services;

use App\Repository\ParticipantsRepository;


class ParticipantsService {

    public function __construct(private ParticipantsRepository $participantsRepository) {}

    public function getAll():array
    {
        return $this->participantsRepository->findAll();
    }

}