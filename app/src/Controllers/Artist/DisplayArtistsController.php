<?php

namespace App\Controllers\Artist;

use App\Lib\Http\Request;
use App\Lib\Http\Response;
use App\Lib\Controllers\AbstractController;
use App\Repositories\ArtistRepository;

class DisplayArtistsController extends AbstractController {
    public function process(Request $request): Response
    {
        $artistRepository = new ArtistRepository();

        $artists = $artistRepository->findAll();

        return $this->render('pages/home', ['artists' => $artists]);
    }
    
}