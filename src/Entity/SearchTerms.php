<?php

namespace App\Entity;

use App\Repository\SearchTermsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SearchTermsRepository::class)
 */
class SearchTerms
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $searched;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSearched(): ?string
    {
        return $this->searched;
    }

    public function setSearched(string $searched): self
    {
        $this->searched = $searched;

        return $this;
    }
}
