<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CurrencyRepository")
 */
class Currency
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * ISO https://www.iso.org/ru/iso-4217-currency-codes.html
     * @ORM\Column(type="string", length=5)
     */
    private string $code;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private string $codeCbr;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rate", mappedBy="currency", fetch="EXTRA_LAZY")
     */
    private Collection $rates;

    public function __construct()
    {
        $this->rates = new ArrayCollection();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection|Rate[]
     */
    public function getRates(): Collection
    {
        return $this->rates;
    }

    public function addRate(Rate $rate): self
    {
        if (!$this->rates->contains($rate)) {
            $this->rates[] = $rate;
            $rate->setCurrency($this);
        }

        return $this;
    }

    public function removeRate(Rate $rate): self
    {
        if ($this->rates->contains($rate)) {
            $this->rates->removeElement($rate);
            // set the owning side to null (unless already changed)
            if ($rate->getCurrency() === $this) {
                $rate->setCurrency(null);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCodeCbr(): string
    {
        return $this->codeCbr;
    }

    /**
     * @param string $codeCbr
     * @return Currency
     */
    public function setCodeCbr(string $codeCbr): Currency
    {
        $this->codeCbr = $codeCbr;
        return $this;
    }

}
