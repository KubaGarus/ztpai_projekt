<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity]
#[ORM\Table(name: "conversations")]
class Conversations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $conversation_id = null;

    #[ORM\ManyToOne(targetEntity: Documents::class)]
    #[ORM\JoinColumn(name: "document_id", referencedColumnName: "document_id", onDelete: "CASCADE")]
    private ?Documents $document = null;

    #[ORM\Column(type: "integer")]
    private int $order_num;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", onDelete: "SET NULL")]
    private ?User $user = null;

    #[ORM\Column(type: "text")]
    private string $content;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private DateTime $date;

    public function __construct()
    {
        $this->date = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->conversation_id;
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

    public function getOrderNum(): int
    {
        return $this->order_num;
    }

    public function setOrderNum(int $order_num): self
    {
        $this->order_num = $order_num;
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

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }
}
