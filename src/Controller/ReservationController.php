<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Label\Font\NotoSans;

class ReservationController extends AbstractController
{
    // Ajouter une réservation
    #[Route('/', name: 'add_reservation')]
    public function index(ReservationRepository $rr, Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            $reservation = $form->getData();
            // vérifier si la chambre est disponible aux dates choisies
            $isAvailable = $rr->findIfAvailable($reservation->getChambre(), $reservation->getDateStart(), $reservation->getDateEnd());
            // si aucune réservation à ces dates, on insère la réservation en BDD
            if(empty($isAvailable)) {
                $entityManager->persist($reservation);
                $entityManager->flush();    
            } 
            return $this->redirectToRoute('app_reservation');
        }
        
        return $this->render('reservation/index.html.twig', [
            'formReservation' => $form,
        ]);
    }

    // Lister toutes les réservations
    #[Route('/reservations', name: 'app_reservation')]
    public function reservations(ReservationRepository $rr): Response
    {
        // afficher toutes les réservations triés par type et par date de début
        $reservations = $rr->findBy([], ["pro" => "ASC", "dateStart" => "ASC"]);
        return $this->render('reservation/reservations.html.twig', [
            'reservations' => $reservations
        ]);
    }

    // Afficher le détail d'une réservation
    #[Route('/reservation/{id}', name: 'show_reservation')]
    public function reservation(Reservation $reservation = null): Response
    {
        // si la réservation existe
        if($reservation) {
            // Générer un QR Code avec les informations de la réservation
            $writer = new PngWriter();
            $qrCodes = [];
            $qrCode = QrCode::create($reservation->getInfos())
                ->setEncoding(new Encoding('UTF-8'))
                ->setSize(120)
                ->setMargin(0)
                ->setForegroundColor(new Color(0, 0, 0));
            $label = Label::create('')->setFont(new NotoSans(8));
            $qrCode->setSize(120)->setForegroundColor(new Color(0, 0, 0)); 
            $qrCode->setSize(120)->setBackgroundColor(new Color(255, 255, 255));
            $qrCodes['withImage'] = $writer->write($qrCode)->getDataUri();
            
            return $this->render('reservation/show.html.twig', [
                'reservation' => $reservation,
                'qrcode' => $qrCodes,
            ]);
        } else {
            return $this->redirectToRoute('app_reservation');
        }
    }
}
