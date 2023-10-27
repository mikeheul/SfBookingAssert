<?php

namespace App\Controller;

use App\Entity\Chambre;
use Endroid\QrCode\QrCode;
use App\Entity\Reservation;
use App\Form\ReservationType;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Label\Font\NotoSans;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    // Ajouter une réservation
    #[Route('/', name: 'add_reservation')]
    #[Route('/reservation/chambre/{id}', name: 'add_reservation_chambre')]
    public function index(Chambre $chambre = null, ReservationRepository $rr, Request $request, EntityManagerInterface $entityManager): Response
    {
        if($this->getUser()) {

            $reservation = new Reservation();

            $disabled = false;
            if($chambre) {
                $disabled = true;
            }

            $form = $this->createForm(ReservationType::class, $reservation, ['disabled_chambre' => $disabled]);
            
            if($chambre) {
                $form->get('chambre')->setData($chambre);
            }

            $form->handleRequest($request);
            
            if($form->isSubmitted() && $form->isValid()) {
                $reservation = $form->getData();
                // associer la réservation à l'utilisateur connecté
                $reservation->setUser($this->getUser());
                $reservation->setChambre($form->get('chambre')->getData());
                // vérifier si la chambre est disponible aux dates choisies
                $isAvailable = $rr->findIfAvailable($reservation->getChambre(), $reservation->getDateStart(), $reservation->getDateEnd());
                // si aucune réservation à ces dates, on insère la réservation en BDD
                if(empty($isAvailable)) {
                    $entityManager->persist($reservation);
                    $entityManager->flush();    
                } else {
                    $this->addFlash('danger', 'Chambre non disponible à ces dates');
                    return $this->redirectToRoute('add_reservation');
                }
            
                return $this->redirectToRoute('app_reservation');
            }
            
            return $this->render('reservation/index.html.twig', [
                'formReservation' => $form,
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    // Lister toutes les réservations
    #[Route('/reservations', name: 'app_reservation')]
    public function reservations(ReservationRepository $rr): Response
    {
        if($this->getUser()) {
            // afficher toutes les réservations triés par type et par date de début
            $reservations = $rr->findBy([], ["pro" => "ASC", "dateStart" => "ASC"]);
            return $this->render('reservation/reservations.html.twig', [
                'reservations' => $reservations
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    // Afficher le détail d'une réservation
    #[Route('/reservation/{id}', name: 'show_reservation')]
    public function reservation(Reservation $reservation = null): Response
    {
        if($this->getUser()) {

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
        } else {
            return $this->redirectToRoute('app_login');
        }
    }
}
