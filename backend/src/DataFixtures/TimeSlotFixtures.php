<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\TimeSlot;
use App\Enum\DayOfWeek;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TimeSlotFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Garage 1: Monday to Friday, 9h-18h
        $garage1 = $this->getReference(sprintf(GarageFixtures::GARAGE_REFERENCE, 1), \App\Entity\Garage::class);
        foreach ([DayOfWeek::Lundi, DayOfWeek::Mardi, DayOfWeek::Mercredi, DayOfWeek::Jeudi, DayOfWeek::Vendredi] as $day) {
            $timeSlotMorning = new TimeSlot();
            $timeSlotMorning->setGarage($garage1)
                ->setDayOfWeek($day)
                ->setStartTime(new \DateTimeImmutable('09:00:00'))
                ->setEndTime(new \DateTimeImmutable('13:00:00'));
            $timeSlotAfternoon = new TimeSlot();
            $timeSlotAfternoon->setGarage($garage1)
                ->setDayOfWeek($day)
                ->setStartTime(new \DateTimeImmutable('14:00:00'))
                ->setEndTime(new \DateTimeImmutable('18:00:00'));
            $manager->persist($timeSlotMorning);
            $manager->persist($timeSlotAfternoon);
        }

        // Garage 2: Tuesday to Saturday, 8h-17h
        $garage2 = $this->getReference(sprintf(GarageFixtures::GARAGE_REFERENCE, 2), \App\Entity\Garage::class);
        foreach ([DayOfWeek::Mardi, DayOfWeek::Mercredi, DayOfWeek::Jeudi, DayOfWeek::Vendredi, DayOfWeek::Samedi] as $day) {
            $timeSlot = new TimeSlot();
            $timeSlot->setGarage($garage2)
                ->setDayOfWeek($day)
                ->setStartTime(new \DateTimeImmutable('08:00:00'))
                ->setEndTime(new \DateTimeImmutable('17:00:00'));
            $manager->persist($timeSlot);
        }

        // Garage 3: Monday, Wednesday, Friday, 10h-19h
        $garage3 = $this->getReference(sprintf(GarageFixtures::GARAGE_REFERENCE, 3), \App\Entity\Garage::class);
        foreach ([DayOfWeek::Lundi, DayOfWeek::Mercredi, DayOfWeek::Vendredi] as $day) {
            $timeSlot = new TimeSlot();
            $timeSlot->setGarage($garage3)
                ->setDayOfWeek($day)
                ->setStartTime(new \DateTimeImmutable('10:00:00'))
                ->setEndTime(new \DateTimeImmutable('19:00:00'));
            $manager->persist($timeSlot);
        }

        // Garage 4: Monday to Saturday, 9h-17h
        $garage4 = $this->getReference(sprintf(GarageFixtures::GARAGE_REFERENCE, 4), \App\Entity\Garage::class);
        foreach ([DayOfWeek::Lundi, DayOfWeek::Mardi, DayOfWeek::Mercredi, DayOfWeek::Jeudi, DayOfWeek::Vendredi, DayOfWeek::Samedi] as $day) {
            $timeSlot = new TimeSlot();
            $timeSlot->setGarage($garage4)
                ->setDayOfWeek($day)
                ->setStartTime(new \DateTimeImmutable('09:00:00'))
                ->setEndTime(new \DateTimeImmutable('17:00:00'));
            $manager->persist($timeSlot);
        }

        // Garage 5: Thursday to Sunday, 11h-20h
        $garage5 = $this->getReference(sprintf(GarageFixtures::GARAGE_REFERENCE, 5), \App\Entity\Garage::class);
        foreach ([DayOfWeek::Jeudi, DayOfWeek::Vendredi, DayOfWeek::Samedi, DayOfWeek::Dimanche] as $day) {
            $timeSlot = new TimeSlot();
            $timeSlot->setGarage($garage5)
                ->setDayOfWeek($day)
                ->setStartTime(new \DateTimeImmutable('11:00:00'))
                ->setEndTime(new \DateTimeImmutable('20:00:00'));
            $manager->persist($timeSlot);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [GarageFixtures::class];
    }
}
