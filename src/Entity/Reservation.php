<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReservationRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez saisir un intitulé')]
    private ?string $intitule = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'Veuillez sélectionner une date de début')]
    #[Assert\GreaterThanOrEqual(
        "today",
        message: 'La date doit être supérieure ou égale à la date courante'
    )]
    #[Assert\When(
        expression: 'this.getDateEnd() != null',
        constraints: [
            new Assert\LessThan(
                propertyPath: 'dateEnd',
                message: 'La date de fin doit être ultérieure à la date de début'
            )
        ]
    )]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'Veuillez sélectionner une date de fin')]
    #[Assert\When(
        expression: 'this.getDateStart() != null',
        constraints: [
            new Assert\GreaterThan(
                propertyPath: 'dateStart',
                message: 'La date de début doit être antérieure à la date de fin'
            )
        ]
    )]
    private ?\DateTimeInterface $dateEnd = null;

    #[ORM\Column]
    #[Assert\Type(type:'int', message: 'Veuillez saisir une valeur numérique entière')]
    #[Assert\GreaterThan(value: 0, message: 'La valeur doit être supérieure à 0')]
    private ?int $nbPersonnes = null;

    #[ORM\Column(length: 255)]
    #[Assert\Regex(
        pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{6,}$/",
        message: 'Le code de réservation doit contenir au moins une majuscules, une minuscule, un chiffre, un caratère spécial et 6 caractères minimum'
    )]
    private ?string $codeReservation = null;

    #[ORM\Column(length: 255)]
    #[Assert\EqualTo(
        propertyPath: 'codeReservation',
        message: 'Les 2 codes de réservation doivent correspondre'
    )]
    private ?string $confirmCodeReservation = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $pro = null;
    
    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?Chambre $chambre;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): static
    {
        $this->intitule = $intitule;

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): static
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getNbPersonnes(): ?int
    {
        return $this->nbPersonnes;
    }

    public function setNbPersonnes(int $nbPersonnes): static
    {
        $this->nbPersonnes = $nbPersonnes;

        return $this;
    }

    public function getCodeReservation(): ?string
    {
        return $this->codeReservation;
    }

    public function setCodeReservation(string $codeReservation): static
    {
        $this->codeReservation = $codeReservation;

        return $this;
    }

    public function getConfirmCodeReservation(): ?string
    {
        return $this->confirmCodeReservation;
    }

    public function setConfirmCodeReservation(string $confirmCodeReservation): static
    {
        $this->confirmCodeReservation = $confirmCodeReservation;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isPro(): ?string
    { 
        return ($this->pro) ? "<span class='badge badge-primary'>Pro</span>" : "<span class='badge badge-success'>Perso</span>";
    }

    public function setPro(bool $pro): static
    {
        $this->pro = $pro;

        return $this;
    }

    public function getInfos()
    {
        return $this->intitule."\n"
        .$this->chambre."\n"
        .$this->dateStart->format("d-m-Y")." au "
        .$this->dateEnd->format("d-m-Y")."\n"
        .$this->codeReservation;
    }

    public function getChambre(): ?Chambre
    {
        return $this->chambre;
    }

    public function setChambre(?Chambre $chambre): static
    {
        $this->chambre = $chambre;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
