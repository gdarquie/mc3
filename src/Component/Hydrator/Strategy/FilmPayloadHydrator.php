<?php

declare(strict_types=1);

namespace App\Component\Hydrator\Strategy;

use App\Component\DTO\Definition\DTOInterface;
use App\Component\DTO\Nested\AttributeNestedDTO;
use App\Component\DTO\Nested\NumberNestedInFilmDTO;
use App\Component\DTO\Payload\FilmPayloadDTO;
use App\Component\Factory\DTOFactory;
use App\Component\Hydrator\Description\HydratorDTOInterface;
use App\Component\Hydrator\Helper\AttributeHelper;
use App\Component\Hydrator\Helper\PersonHelper;
use App\Component\Hydrator\HydratorBasics;
use App\Component\Model\ModelConstants;
use App\Entity\Attribute;
use App\Entity\Film;
use App\Entity\Number;
use App\Entity\Work;
use Doctrine\ORM\EntityManagerInterface;

class FilmPayloadHydrator implements HydratorDTOInterface
{
    /**
     * @param DTOInterface $dto
     * @param array $data
     * @param EntityManagerInterface $em
     * @return FilmPayloadDTO
     */
    public static function hydrate(DTOInterface $dto, array $data, EntityManagerInterface $em):FilmPayloadDTO
    {
        $params = [];
        // set excludes paramaters to treate manually some properties
        $params['excludes'] = ['numbers', 'studios', 'pca'];
        // fields we are forced to complete, if not we throw an error
        $params['mandatory'] = ['uuid', 'title', 'imdb'];

        $data['model'] = ModelConstants::FILM_MODEL;
        /** @var Film $film */
        $film = $data['film'];

        /** @var FilmPayloadDTO $dto */
        $dto = HydratorBasics::hydrateDTOBase($dto, $data, $params);

        // manage numbers of a film
        if ($film->getNumbers()) {
            // get ordered Numbers (the order is in model film->numbers thanks to doctrine)
            $numbersDTOList = self::getNumbers($film, $em);
            if(count($numbersDTOList) > 0) {
                // add numbers to film
                $dto->setNumbers($numbersDTOList);
            }
        }

        // configuration for film payload
        $manyToMany = ['state', 'censorship'];

        // get attributes
        foreach ($film->getAttributes() as $attribute) {
            $code = $attribute->getCategory()->getCode();

            // handle exception
            if ($code === 'verdict') { // is this pca verdict?
                $dto->setPca([AttributeHelper::getAttribute($attribute, $em)]);
                continue;
            }

            // handle many to many
            if (in_array($code, $manyToMany)) {
                $manyToManyAttributes[$code][] = AttributeHelper::getAttribute($attribute, $em);
                continue;
            }

            // for all normal many to one
            $setter = 'set'.ucfirst($attribute->getCategory()->getCode());
            $dto->$setter([AttributeHelper::getAttribute($attribute, $em)]);
        }

        // for attributes again: set many to many
        foreach ($manyToMany as $category) {
            if(isset($manyToManyAttributes[$category])) {
                $setter = 'set'.ucfirst($category.'s'); // be carefull, to be changed if a category already finish with a s
                $dto->$setter($manyToManyAttributes[$category]);
            }
        }

        // get studios
        $studiosDTO = [];
        foreach($film->getStudios() as $studio) {
            $studiosDTO[] = [
                'name' => $studio->getName(),
                'uuid' => $studio->getUuid()
            ];
        }

        $dto->setStudios($studiosDTO);

        // get directors
        $directors = PersonHelper::getPersonsByProfession('director', ModelConstants::FILM_MODEL, $film, $em);
        $dto->setDirectors($directors);

        // get stats
        $dto = self::computeStats($dto, $em);

        return $dto;
    }

    /**
     * @param FilmPayloadDTO $dto
     * @param EntityManagerInterface $em
     * @return FilmPayloadDTO
     */
    public static function computeStats(FilmPayloadDTO $dto, EntityManagerInterface $em): FilmPayloadDTO
    {
        $numbers = $dto->getNumbers();
        // Sum of all numbers
        $numbersLength = 0;
        foreach ($numbers as $number) {
            if ($number->getLength()) {
                $numbersLength+=$number->getLength();
            }
        }
        $dto->setNumbersLength($numbersLength);

        // Ratio number/total length
        if ($dto->getLength()) { // to avoid division by zero error
            $numbersRatio = intval(round(100 / $dto->getLength() * $numbersLength, 2)*100); // php bug? result is slightly change : 3948.0 become 3947
            $dto->setNumbersRatio($numbersRatio);
        }

        if ($numbers && $numbersLength) {
            $averageNumberLength = intval(round($numbersLength/count($numbers)));
            $dto->setAverageNumberLength($averageNumberLength);
        }

        // Compute global average runtime = total numbers length divided by numbers count
        $numberRepository = $em->getRepository(Number::class);
        $numbersCount = $numberRepository->countNumbers();
        $totalNumbersLength = $numberRepository->getTotalNumbersLength();
        if ($numbersCount && $totalNumbersLength) {
            $globalAverageNumberLength = intval(round($totalNumbersLength/$numbersCount));
            $dto->setGlobalAverageNumberLength($globalAverageNumberLength);
        }

        return $dto;
    }

    /**
     * @param Film $film
     * @param EntityManagerInterface $em
     * @return array
     */
    public static function getNumbers(Film $film, EntityManagerInterface $em)
    {
        // sort numbers by beginTc
        $numbers = $film->getNumbers();
        $numbersDTOList = [];

        // add numbers
        foreach ($numbers as $order => $number) {

            $numberUuid = $number->getUuid();
            $numberBeginTc = $number->getBeginTc();
            $numberEndTc = $number->getEndTc();

            /** @var NumberNestedInFilmDTO $numberDTO */
            $numberDTO = DTOFactory::create(ModelConstants::NUMBER_NESTED_IN_FILM_DTO_MODEL);
            $numberDTO->setOrder($order);
            $numberDTO->setTitle($number->getTitle());
            $numberDTO->setBeginTc($numberBeginTc);
            $numberDTO->setEndTc($numberEndTc);
            $numberDTO->setUuid($numberUuid);

            // compute number length
            if ($numberBeginTc >= 0 && $numberEndTc && $numberEndTc >= $numberBeginTc) {
                $numberDTO->setLength($numberEndTc - $numberBeginTc);
            }

            //set performers
            $performers = $em->getRepository(Work::class)->findPersonByTargetAndProfession('number', $numberUuid, 'performer');

            $peformersList = [];
            if ($performers) {
                foreach ($performers as $performer) {
                    $personDTO = DTOFactory::create(ModelConstants::PERSON_NESTED_DTO_MODEL);
                    $personDTO = NestedPersonPayloadHydrator::hydrate($personDTO, ['person' => $performer], $em );
                    $peformersList[] = $personDTO;
                }
                $numberDTO->setPerformers($peformersList);
            }

            // get attributes
            $numberDTO = self::getAttributes($numberDTO, $number, $em);

            $numbersDTOList[] = $numberDTO;

        }

        return $numbersDTOList;
    }

    /**
     * @param NumberNestedInFilmDTO $dto
     * @param Number $number
     * @param EntityManagerInterface $em
     * @return NumberNestedInFilmDTO
     */
    private static function getAttributes(NumberNestedInFilmDTO $dto, Number $number, EntityManagerInterface  $em):NumberNestedInFilmDTO
    {
        foreach($number->getAttributes() as $attribute) {
            $code = $attribute->getCategory()->getCode();

            // Structure
            if ($code === 'structure') {
                $attributeDTO = self::createAttributeDTO($attribute, $em);

                $structure = $dto->getStructure();
                $structure[] = $attributeDTO;
                $dto->setStructure($structure);
            }

            // Performance type
            if ($code === 'performance_thesaurus') {
                $attributeDTO = self::createAttributeDTO($attribute, $em);

                $attributes = $dto->getPerformance();
                $attributes[] = $attributeDTO;
                $dto->setPerformance($attributes);
            }

            // Completeness
            if ($code === 'completeness_thesaurus') {
                $attributeDTO = self::createAttributeDTO($attribute, $em);

                $attributes = $dto->getCompleteness();
                $attributes[] = $attributeDTO;
                $dto->setCompleteness($attributes);
            }

            // Diegetic status of the number
            if ($code === 'diegetic_thesaurus') {
                $attributeDTO = self::createAttributeDTO($attribute, $em);

                $attributes = $dto->getDiegetic();
                $attributes[] = $attributeDTO;
                $dto->setDiegetic($attributes);
            }

            // Source of the number
            if ($code === 'source_thesaurus') {
                $attributeDTO = self::createAttributeDTO($attribute, $em);

                $attributes = $dto->getSource();
                $attributes[] = $attributeDTO;
                $dto->setSource($attributes);
            }

            // Cast
            if ($code === 'cast') {
                $attributeDTO = self::createAttributeDTO($attribute, $em);

                $attributes = $dto->getCast();
                $attributes[] = $attributeDTO;
                $dto->setCast($attributes);
            }
        }

        return $dto;
    }

    /**
     * @param Attribute $attribute
     * @param EntityManagerInterface $em
     * @return AttributeNestedDTO
     */
    public static function createAttributeDTO(Attribute $attribute, EntityManagerInterface $em)
    {
        $attributeDTO = DTOFactory::create(ModelConstants::ATTRIBUTE_NESTED_PAYLOAD);
        return NestedAttribute::hydrate($attributeDTO, ['attribute' => $attribute], $em);
    }

}