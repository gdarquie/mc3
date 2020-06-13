<?php

declare(strict_types=1);


namespace App\Component\DTO\Composition\Number;

use App\Component\DTO\Nested\FilmNestedDTO;
use App\Component\DTO\Payload\NumberPayloadDTO;

trait NumberDescriptionTrait
{
    /** @var string */
    protected $title;

    /** @var string */
    protected $film;

    /** @var integer */
    protected $startingTc = 0;

    /** @var integer */
    protected $endingTc = 0;

    /** @var string */
    protected $beginning = NumberPayloadDTO::NO_VALUE;

    /** @var string */
    protected $ending = NumberPayloadDTO::NO_VALUE;

    /** @var array */
    protected $completeness = []; // AttributeNestedDTO

    /** @var string */
    protected $completenessOption = NumberPayloadDTO::NO_VALUE; // AttributeNestedDTO

    /** @var string */
    protected $structure = NumberPayloadDTO::NO_VALUE; // AttributeNestedDTO

    /** @var int */
    protected $shots = 0;

    /** @var int */
    protected $averageShotLength = 0;

    /** @var string */
    protected $performance = NumberPayloadDTO::NO_VALUE; // one choice

    /** @var array */
    protected $performers = []; // PersonNestedDTO

    /** @var string */
    protected $cast = NumberPayloadDTO::NO_VALUE; // AttributeNestedDTO

    /** @var array */
    protected $noParticipationStars = [];

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getFilm(): string
    {
        return $this->film;
    }

    /**
     * @param string $film
     */
    public function setFilm(string $film): void
    {
        $this->film = $film;
    }

    /**
     * @return int
     */
    public function getStartingTc(): int
    {
        return $this->startingTc;
    }

    /**
     * @param int $startingTc
     */
    public function setStartingTc(int $startingTc): void
    {
        $this->startingTc = $startingTc;
    }

    /**
     * @return int
     */
    public function getEndingTc(): int
    {
        return $this->endingTc;
    }

    /**
     * @param int $endingTc
     */
    public function setEndingTc(int $endingTc): void
    {
        $this->endingTc = $endingTc;
    }

    /**
     * @return string
     */
    public function getBeginning(): string
    {
        return $this->beginning;
    }

    /**
     * @param string $beginning
     */
    public function setBeginning(string $beginning): void
    {
        $this->beginning = $beginning;
    }

    /**
     * @return string
     */
    public function getEnding(): string
    {
        return $this->ending;
    }

    /**
     * @param string $ending
     */
    public function setEnding(string $ending): void
    {
        $this->ending = $ending;
    }

    /**
     * @return array
     */
    public function getCompleteness(): array
    {
        return $this->completeness;
    }

    /**
     * @param array $completeness
     */
    public function setCompleteness(array $completeness): void
    {
        $this->completeness = $completeness;
    }

    /**
     * @return string
     */
    public function getCompletenessOption(): string
    {
        return $this->completenessOption;
    }

    /**
     * @param string $completenessOption
     */
    public function setCompletenessOption(string $completenessOption): void
    {
        $this->completenessOption = $completenessOption;
    }

    /**
     * @return string
     */
    public function getStructure(): string
    {
        return $this->structure;
    }

    /**
     * @param string $structure
     */
    public function setStructure(string $structure): void
    {
        $this->structure = $structure;
    }

    /**
     * @return int
     */
    public function getShots(): int
    {
        return $this->shots;
    }

    /**
     * @param int $shots
     */
    public function setShots(int $shots): void
    {
        $this->shots = $shots;
    }

    /**
     * @return int
     */
    public function getAverageShotLength(): int
    {
        return $this->averageShotLength;
    }

    /**
     * @param int $averageShotLength
     */
    public function setAverageShotLength(int $averageShotLength): void
    {
        $this->averageShotLength = $averageShotLength;
    }

    /**
     * @return string
     */
    public function getPerformance(): string
    {
        return $this->performance;
    }

    /**
     * @param string $performance
     */
    public function setPerformance(string $performance): void
    {
        $this->performance = $performance;
    }

    /**
     * @return array
     */
    public function getPerformers(): array
    {
        return $this->performers;
    }

    /**
     * @param array $performers
     */
    public function setPerformers(array $performers): void
    {
        $this->performers = $performers;
    }

    /**
     * @return string
     */
    public function getCast(): string
    {
        return $this->cast;
    }

    /**
     * @param string $cast
     */
    public function setCast(string $cast): void
    {
        $this->cast = $cast;
    }

    /**
     * @return array
     */
    public function getNoParticipationStars(): array
    {
        return $this->noParticipationStars;
    }

    /**
     * @param array $noParticipationStars
     */
    public function setNoParticipationStars(array $noParticipationStars): void
    {
        $this->noParticipationStars = $noParticipationStars;
    } // PersonNestedDTO (figurants)
}