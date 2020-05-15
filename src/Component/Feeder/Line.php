<?php

namespace App\Component\Feeder;

use App\Entity\Film;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

class Line
{
    public static function save(
        EntityManagerInterface $em,
        array $line,
        FeederObserver $observer
    )
    {
        $observer->setLine($observer->getLine()+1);

        //save line
        //todo : replace, it's just a test
        if ($observer->getLine() === 2) {
            $film = new Film();
            $film->setTitle($line[1]);
            $film->setReleasedYear($line[3]);
            $film->setUuid(Uuid::uuid4());

            $em->persist($film);
            $em->flush();
            dd($film);

        }
    }
}