<?php

namespace App\Entity;

use App\Entity\Traits\HasGedmoTimestampTrait;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\HasLimit;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\HasIdTrait;
use App\Repository\FaqRepository;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Entity\Traits\HasTimestampTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FaqRepository::class)]
class Faq
{
    use HasIdTrait;
    use HasIsOnlineTrait;
    use HasTimestampTrait;
    //use HasGedmoTimestampTrait;

    public const FAQ_LIMIT = HasLimit::FAQ_LIMIT;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private string $question = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private string $answer = '';

    public function __toString(): string
    {
        return (string) $this->getQuestion() ?: '';
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): static
    {
        $this->answer = $answer;

        return $this;
    }
}
