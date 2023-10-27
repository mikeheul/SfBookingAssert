<?php

namespace App\Controller;

use App\Repository\ChambreRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChambreController extends AbstractController
{
    #[Route('/chambre', name: 'app_chambre')]
    public function index(ChambreRepository $cr): Response
    {
        $chambres = $cr->findAll();
        return $this->render('chambre/index.html.twig', [
            'chambres' => $chambres,
        ]);
    }
}
