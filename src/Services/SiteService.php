<?php

namespace App\Services;

use App\Repository\SiteRepository;

class SiteService
{
    function showAll() : array
    {
        $SR = SiteRepository::class;
        $sites = $SR->findAll();
        return $sites;
    }
}