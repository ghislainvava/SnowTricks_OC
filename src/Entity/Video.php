<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text', nullable: true)]
    private $frame;

    #[ORM\ManyToOne(targetEntity: Figure::class, inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false)]
    private $figure;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFrame(): ?string
    {
        return $this->frame;
    }

    public function setFrame(?string $frame): self
    {
        $this->frame = $frame;

        return $this;
    }

    public function getFigure(): ?Figure
    {
        return $this->figure;
    }

    public function setFigure(?Figure $figure): self
    {
        $this->figure = $figure;

        return $this;
    }
}
