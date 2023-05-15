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
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Product::class)]
    private Collection $productId;

    public function __construct()
    {
        $this->productId = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProductId(): Collection
    {
        return $this->productId;
    }

    public function addProductId(Product $productId): self
    {
        if (!$this->productId->contains($productId)) {
            $this->productId->add($productId);
            $productId->setCategory($this);
        }

        return $this;
    }

    public function removeProductId(Product $productId): self
    {
        if ($this->productId->removeElement($productId)) {
            // set the owning side to null (unless already changed)
            if ($productId->getCategory() === $this) {
                $productId->setCategory(null);
            }
        }

        return $this;
    }
}
