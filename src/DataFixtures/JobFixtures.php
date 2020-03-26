<?php

namespace App\DataFixtures;

use App\Entity\Job;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class JobFixtures extends Fixture
{
    public function load(ObjectManager $manager)
{
    // Tableau des choses à ajouter
    $tab = array(
        array('title' => 'Peintre'),
        array('title' => 'Docteur'),
        array('title' => 'Boulanger'),
        array('title' => 'Dentiste'),
        array('title' => 'Fleuriste'),
    );

    foreach($tab as $row)
    {
      // On crée le job
    $job = new Job();
    $job->setTitle($row['title']);

    $manager->persist($job);
    }

    // On déclenche l'enregistrement
    $manager->flush();
}
}
