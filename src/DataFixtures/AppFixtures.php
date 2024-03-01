<?php

namespace App\DataFixtures;

use App\Entity\Announcements;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    /**
     * @param UserPasswordHasherInterface $userPasswordHasher
     */
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un user "normal"
        $user = new User();
        $user->setEmail("user@gmail.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setUsername("user");
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $currentDate = new \DateTime();
        $user->setFirstName("first_name");
        $user->setLastName("last_name");
        $user->setBirthDate($currentDate);
        $user->setVille("ville");
        $user->setDepartement('departement');
        $user->setNumeroRue('numero_rue');
        $user->setRue('rue');
        $user->setCodePostal('code_postal');
        $user->setStatusUser(1);
        $user->setNumeroTel('numero_tel');
        $user->setIsVerified(1);
        $user->setComplement('complement');
        $manager->persist($user);

        // Création d'un user admin
        $userAdmin = new User();
        $userAdmin->setEmail("admin@gmail.com");
        $user->setUsername("admin");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $currentDate = new \DateTime();
        $user->setFirstName("first_name");
        $user->setLastName("last_name");
        $user->setBirthDate($currentDate);
        $user->setVille("ville");
        $user->setDepartement('departement');
        $user->setNumeroRue('numero_rue');
        $user->setRue('rue');
        $user->setCodePostal('code_postal');
        $user->setStatusUser(1);
        $user->setNumeroTel('numero_tel');
        $user->setIsVerified(1);
        $user->setComplement('complement');
        $manager->persist($userAdmin);



        //Création d'une annonce
        $announcement = new Announcements();
        $announcement->setOwner($user);
        $announcement->setDescription("Description de l'annonce");
        $announcement->setDate(new \DateTime());
        $announcement->setLimitDate(new \DateTime());
        $announcement->setTitle("Titre de l'annonce");
        $announcement->setCategorie("Categorie de l'annonce");
        $announcement->setNumeroRue("Numero de rue de l'annonce");
        $announcement->setStatus(0);
        $announcement->setRue("Rue de l'annonce");
        $announcement->setCodePostal("Code postal de l'annonce");
        $announcement->setVille("Ville de l'annonce");
        $manager->persist($announcement);

        $manager->flush();
    }
}
