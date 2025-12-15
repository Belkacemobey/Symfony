<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomCategory = null;

    /**
     * @var Collection<int, Meubles>
     */
    #[ORM\OneToMany(targetEntity: Meubles::class, mappedBy: 'category')]
    private Collection $meubles;

    public function __construct()
    {
        $this->meubles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCategory(): ?string
    {
        return $this->nomCategory;
    }

    public function setNomCategory(string $nomCategory): static
    {
        $this->nomCategory = $nomCategory;

        return $this;
    }

    /**
     * @return Collection<int, Meubles>
     */
    public function getMeubles(): Collection
    {
        return $this->meubles;
    }

    public function addMeuble(Meubles $meuble): static
    {
        if (!$this->meubles->contains($meuble)) {
            $this->meubles->add($meuble);
            $meuble->setCategory($this);
        }

        return $this;
    }

    public function removeMeuble(Meubles $meuble): static
    {
        if ($this->meubles->removeElement($meuble)) {
            // set the owning side to null (unless already changed)
            if ($meuble->getCategory() === $this) {
                $meuble->setCategory(null);
            }
        }

        return $this;
    }
}
