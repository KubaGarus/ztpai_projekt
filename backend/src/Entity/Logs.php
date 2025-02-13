<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity]
#[ORM\Table(name: "logs")]
class Logs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $log_id = null;

    #[ORM\ManyToOne(targetEntity: Documents::class)]
    #[ORM\JoinColumn(name: "document_id", referencedColumnName: "document_id", onDelete: "CASCADE")]
    private ?Documents $document = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "SET NULL")]
    private ?User $user = null;

    #[ORM\Column(type: "smallint")]
    private int $status_before;

    #[ORM\Column(type: "smallint")]
    private int $status_after;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private DateTime $upload_date;

    public function __construct()
    {
        $this->upload_date = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->log_id;
    }

    public function getDocument(): ?Documents
    {
        return $this->document;
    }

    public function setDocument(?Documents $document): self
    {
        $this->document = $document;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getStatusBefore(): int
    {
        return $this->status_before;
    }

    public function setStatusBefore(int $status_before): self
    {
        $this->status_before = $status_before;
        return $this;
    }

    public function getStatusAfter(): int
    {
        return $this->status_after;
    }

    public function setStatusAfter(int $status_after): self
    {
        $this->status_after = $status_after;
        return $this;
    }

    public function getUploadDate(): DateTime
    {
        return $this->upload_date;
    }
}
