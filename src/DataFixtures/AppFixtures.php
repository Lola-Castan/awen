<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Event;
use App\Entity\Post;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\EventCategory;
use App\Enum\ProductStatus;
use App\Enum\EventStatus;
use App\Enum\EventUserStatus;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Enum\OrderStatus;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    
    public function load(ObjectManager $manager): void
    {
        // Create roles
        $roleUser = new Role();
        $roleUser->setName('ROLE_USER');
        $manager->persist($roleUser);

        $roleCreator = new Role();
        $roleCreator->setName('ROLE_CREATOR');
        $manager->persist($roleCreator);

        $roleAdmin = new Role();
        $roleAdmin->setName('ROLE_ADMIN');
        $manager->persist($roleAdmin);
        // Create basic users (10 users)
        $users = [];
        $userNames = [
            ['amel', 'Amel', 'Bouchard', 'amel@example.com'],
            ['kenzo', 'Kenzo', 'Dubois-Nakamura', 'kenzo@example.com'],
            ['fatou', 'Fatou', 'Diallo', 'fatou@example.com'],
            ['yanis', 'Yanis', 'Martin-Benali', 'yanis@example.com'],
            ['lina', 'Lina', 'Rousseau', 'lina@example.com'],
            ['abdel', 'Abdel', 'Traore-Lemaire', 'abdel@example.com'],
            ['lou', 'Lou', 'Bernard', 'lou@example.com'],
            ['zineb', 'Zineb', 'Moreau-Haddad', 'zineb@example.com'],
            ['baptiste', 'Baptiste', 'Kouame', 'baptiste@example.com'],
            ['jade', 'Jade', 'Chen-Petit', 'jade@example.com']
        ];
        
        foreach ($userNames as $index => $userData) {
            $user = new User();
            $user->setUsername($userData[0])
                ->setEmail($userData[3])
                ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
                ->setFirstName($userData[1])
                ->setLastName($userData[2])
                ->setBirthDate(new \DateTimeImmutable('1985-0' . (($index % 9) + 1) . '-' . sprintf('%02d', ($index % 28) + 1)))
                ->setCreatedAt(new \DateTimeImmutable('-' . (60 - $index * 5) . ' days'));

            // Ajouter une photo de profil pour certains utilisateurs (pas tous)
            if ($index % 3 === 0) {
                $profilePictures = ['claracraft.jpg', 'julesart.jpg'];
                $user->setProfilePicture($profilePictures[$index % 2]);
            }

            $user->addRole($roleUser);
            $manager->persist($user);
            $users[] = $user;
        }
        
        // Create creators (10 creators)
        $creators = [];
        $creatorData = [
            ['clara', 'Clara', 'Moreau', 'clara@example.com', 'Atelier Clara', 'Céramiste passionnée par les formes organiques et les émaux naturels', 'cover_clara.jpg'],
            ['jules', 'Jules', 'Kouassi', 'jules@example.com', 'Bois & Lumière', 'Ébéniste sculpteur spécialisé dans le mobilier contemporain en bois massif', 'cover_jules.jpg'],
            ['leïla', 'Leïla', 'Benabdallah', 'leila@example.com', 'Leïla Textile', 'Créatrice textile engagée dans la mode éthique et le upcycling', 'cover_clara.jpg'],
            ['maxime', 'Maxime', 'Nguyen', 'maxime@example.com', 'Forge Maxime', 'Artisan forgeron créateur d\'objets décoratifs et utilitaires', 'cover_jules.jpg'],
            ['amina', 'Amina', 'Traoré', 'amina@example.com', 'Cosmétiques d\'Amina', 'Créatrice de cosmétiques naturels inspirés des traditions africaines', 'cover_clara.jpg'],
            ['enzo', 'Enzo', 'Lefebvre', 'enzo@example.com', 'Cuir & Créations', 'Maroquinier artisan spécialisé dans les accessoires sur mesure', 'cover_jules.jpg'],
            ['sofia', 'Sofia', 'Yamamoto', 'sofia@example.com', 'Verre Sofia', 'Maître verrier créant des pièces uniques en verre soufflé', 'cover_clara.jpg'],
            ['théo', 'Théo', 'Dubois', 'theo@example.com', 'Photo Théo', 'Photographe artisan capturant l\'essence des métiers d\'art', 'cover_jules.jpg'],
            ['nour', 'Nour', 'Mansouri', 'nour@example.com', 'Papeterie Nour', 'Créatrice de papeterie artisanale et de reliures d\'art', 'cover_clara.jpg'],
            ['mathis', 'Mathis', 'Diouf', 'mathis@example.com', 'Pierre & Mathis', 'Sculpteur sur pierre révélant la beauté des matériaux naturels', 'cover_jules.jpg']
        ];
        
        foreach ($creatorData as $index => $data) {
            $creator = new User();
            $creator->setUsername($data[0])
                ->setEmail($data[3])
                ->setPassword($this->passwordHasher->hashPassword($creator, 'password'))
                ->setFirstName($data[1])
                ->setLastName($data[2])
                ->setBirthDate(new \DateTimeImmutable('198' . (($index % 9) + 1) . '-0' . (($index % 9) + 1) . '-15'))
                ->setCreatedAt(new \DateTimeImmutable('-' . (90 - $index * 8) . ' days'));

            // Ajouter une photo de profil (alternance entre les deux images disponibles)
            $profilePictures = ['claracraft.jpg', 'julesart.jpg'];
            $creator->setProfilePicture($profilePictures[$index % 2]);

            $creator->addRole($roleUser);
            $creator->addRole($roleCreator);

            $creatorInfo = $creator->getCreatorInfo();
            $creatorInfo->setDisplayName($data[4])
                ->setInstagramProfile('https://instagram.com/' . $data[0])
                ->setDescription($data[5])
                ->setPracticalInfos('Livraison sous 5-7 jours ouvrés')
                ->setCoverImage($data[6]);

            $manager->persist($creator);
            $creators[] = $creator;
        }// Admin
        $admin = new User();
        $admin->setUsername('admin')
            ->setEmail('admin@example.com')
            ->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'))
            ->setFirstName('Camille')
            ->setLastName('Administrateur')
            ->setBirthDate(new \DateTimeImmutable('1980-12-12'))
            ->setCreatedAt(new \DateTimeImmutable());

        $admin->addRole($roleUser);
        $admin->addRole($roleAdmin);
        $manager->persist($admin);        
        // Création des catégories (plus variées)
        $categories = [];
        
        $categoryData = [
            ['Décoration', 'Objets décoratifs pour la maison'],
            ['Bijoux', 'Bijoux artisanaux et accessoires'],
            ['Art', 'Œuvres d\'art et sculptures'],
            ['Maison', 'Objets utiles pour le quotidien'],
            ['Mode', 'Vêtements et accessoires de mode'],
            ['Textile', 'Créations textiles et tissées'],
            ['Cuir', 'Maroquinerie et accessoires en cuir'],
            ['Verre', 'Créations en verre et cristal'],
            ['Métal', 'Objets forgés et travail du métal'],
            ['Cosmétiques', 'Produits de beauté naturels']
        ];
        
        foreach ($categoryData as $catData) {
            $category = new Category();
            $category->setName($catData[0]);
            $manager->persist($category);
            $categories[] = $category;
        }
        
        // Association des catégories aux créateurs (distribution variée)
        foreach ($creators as $index => $creator) {
            // Chaque créateur a 2-3 catégories
            $creator->addCategory($categories[$index % count($categories)]);
            $creator->addCategory($categories[($index + 1) % count($categories)]);
            if ($index % 3 === 0) {
                $creator->addCategory($categories[($index + 2) % count($categories)]);
            }
        }
          // Création des catégories d'événements
        $eventCategories = [];
        
        $eventCategoryAtelier = new EventCategory();
        $eventCategoryAtelier->setName('Atelier');
        $eventCategoryAtelier->setDescription('Ateliers pratiques et participatifs');
        $manager->persist($eventCategoryAtelier);
        $eventCategories[] = $eventCategoryAtelier;
        
        $eventCategoryExposition = new EventCategory();
        $eventCategoryExposition->setName('Exposition');
        $eventCategoryExposition->setDescription('Expositions artistiques et culturelles');
        $manager->persist($eventCategoryExposition);
        $eventCategories[] = $eventCategoryExposition;
        
        $eventCategoryMarche = new EventCategory();
        $eventCategoryMarche->setName('Marché');
        $eventCategoryMarche->setDescription('Marchés de créateurs et ventes éphémères');
        $manager->persist($eventCategoryMarche);
        $eventCategories[] = $eventCategoryMarche;
        
        $eventCategoryConference = new EventCategory();
        $eventCategoryConference->setName('Conférence');
        $eventCategoryConference->setDescription('Conférences, tables rondes et discussions');
        $manager->persist($eventCategoryConference);
        $eventCategories[] = $eventCategoryConference;
        
        $eventCategoryFormation = new EventCategory();
        $eventCategoryFormation->setName('Formation');
        $eventCategoryFormation->setDescription('Formations et cours techniques');
        $manager->persist($eventCategoryFormation);
        $eventCategories[] = $eventCategoryFormation;
        
        // Création des images (indépendantes des produits)
        $images = [];
        
        $image1 = new Image();
        $image1->setFilename('vase1.jpg')
            ->setAlt('Vue principale du vase artisanal')
            ->setTitle('Vase artisanal en céramique')
            ->setPosition(0);
        $manager->persist($image1);
        $images[] = $image1;
        
        $image2 = new Image();
        $image2->setFilename('vase2.jpg')
            ->setAlt('Vue de côté du vase artisanal')
            ->setTitle('Détail du vase')
            ->setPosition(1);
        $manager->persist($image2);
        $images[] = $image2;
        
        $image3 = new Image();
        $image3->setFilename('collier1.jpg')
            ->setAlt('Vue principale du collier en perles')
            ->setTitle('Collier en perles naturelles')
            ->setPosition(0);
        $manager->persist($image3);
        $images[] = $image3;
        
        $image4 = new Image();
        $image4->setFilename('sculpture1.jpg')
            ->setAlt('Vue principale de la sculpture en bois')
            ->setTitle('Sculpture abstraite en bois')
            ->setPosition(0);
        $manager->persist($image4);
        $images[] = $image4;
        
        $image5 = new Image();
        $image5->setFilename('sculpture2.jpg')
            ->setAlt('Vue de détail de la sculpture')
            ->setTitle('Détail de la sculpture en bois')
            ->setPosition(1);
        $manager->persist($image5);
        $images[] = $image5;
        
        $image6 = new Image();
        $image6->setFilename('sculpture3.jpg')
            ->setAlt("Vue d'ensemble de la sculpture")
            ->setTitle("Vue d'ensemble de la sculpture")
            ->setPosition(2);
        $manager->persist($image6);
        $images[] = $image6;
        
        // Images pour les événements
        $image7 = new Image();
        $image7->setFilename('atelier1.jpg')
            ->setAlt("Photo de l'atelier de création de bijoux")
            ->setTitle("Atelier création de bijoux");
        $manager->persist($image7);
        $images[] = $image7;
        
        $image8 = new Image();
        $image8->setFilename('expo1.jpg')
            ->setAlt("Photo de l'exposition d'art contemporain")
            ->setTitle("Exposition d'art contemporain");
        $manager->persist($image8);
        $images[] = $image8;
        
        $image9 = new Image();
        $image9->setFilename('marche1.jpg')
            ->setAlt("Photo du marché des créateurs")
            ->setTitle("Marché des créateurs");
        $manager->persist($image9);
        $images[] = $image9;
        
        // Create multiple products (30+ products)
        $products = [];
        
        $productData = [
            ['Vase artisanal', 'Vase en céramique fait main', 'Vase en céramique entièrement fait à la main avec des matériaux naturels et locaux.', 100, 500, 20, 30, 15, 1999, true, ProductStatus::Published],
            ['Collier perles naturelles', 'Collier en perles de bois', 'Collier élégant en perles de bois naturel, pièce unique.', 50, 50, 0, 0, 0, 2999, false, ProductStatus::Published],
            ['Sculpture abstraite', 'Sculpture en bois recyclé', 'Œuvre d\'art unique créée avec du bois de récupération.', 5, 1200, 40, 30, 50, 9990, true, ProductStatus::Published],
            ['Bracelet argent', 'Bracelet artisanal en argent', 'Bracelet fait main en argent 925, design contemporain.', 25, 30, 0, 0, 0, 4500, false, ProductStatus::Published],
            ['Lampe design', 'Lampe en bois flotté', 'Lampe unique créée à partir de bois flotté ramassé sur les plages.', 12, 800, 25, 25, 40, 7500, true, ProductStatus::Published],
            ['Sac en cuir', 'Sac bandoulière cuir', 'Sac artisanal en cuir pleine fleur, coutures main.', 30, 600, 30, 10, 25, 12000, false, ProductStatus::Published],
            ['Verre soufflé', 'Vase en verre coloré', 'Vase unique en verre soufflé, couleurs irisées.', 8, 400, 15, 15, 25, 6500, true, ProductStatus::Published],
            ['Bougie naturelle', 'Bougie cire de soja', 'Bougie parfumée à la cire de soja, senteur lavande.', 100, 200, 8, 8, 10, 1800, false, ProductStatus::Published],
            ['Plateau bois', 'Plateau de service chêne', 'Plateau de service en chêne massif, finition huile.', 20, 1000, 40, 25, 3, 5500, false, ProductStatus::Published],
            ['Boucles d\'oreilles', 'Boucles artisanales', 'Boucles d\'oreilles en argent et pierres semi-précieuses.', 40, 15, 0, 0, 0, 3200, false, ProductStatus::Published],
            ['Miroir décoratif', 'Miroir encadré bois', 'Miroir avec cadre en bois sculpté à la main.', 15, 2000, 50, 3, 50, 8500, true, ProductStatus::Published],
            ['Écharpe laine', 'Écharpe tricotée main', 'Écharpe en laine mérinos, tricotage traditionnel.', 25, 150, 0, 0, 0, 4200, false, ProductStatus::Published],
            ['Pot en grès', 'Pot décoratif grès', 'Pot en grès émaillé, parfait pour plantes d\'intérieur.', 35, 800, 20, 20, 25, 3500, false, ProductStatus::Published],
            ['Couteau cuisine', 'Couteau forgé main', 'Couteau de cuisine forgé à la main, lame carbone.', 10, 300, 25, 3, 2, 15000, true, ProductStatus::Published],
            ['Savon artisanal', 'Savon huiles essentielles', 'Savon saponifié à froid, ingrédients bio.', 200, 100, 8, 5, 3, 800, false, ProductStatus::Published],
            ['Tableau abstrait', 'Peinture acrylique', 'Tableau abstrait original, technique mixte.', 3, 500, 40, 2, 30, 25000, true, ProductStatus::Published],
            ['Panier osier', 'Panier tressé main', 'Panier en osier tressé selon méthodes traditionnelles.', 18, 200, 30, 30, 15, 2800, false, ProductStatus::Published],
            ['Pendentif pierre', 'Pendentif améthyste', 'Pendentif en argent serti d\'une améthyste naturelle.', 15, 25, 0, 0, 0, 5800, false, ProductStatus::Published],
            ['Carnet cuir', 'Carnet reliure cuir', 'Carnet artisanal avec couverture cuir et papier recyclé.', 50, 250, 15, 2, 20, 2200, false, ProductStatus::Published],
            ['Photographie art', 'Tirage photo limité', 'Photographie artistique, tirage limité et signé.', 5, 100, 30, 1, 40, 18000, true, ProductStatus::Published],
            ['Étagère murale', 'Étagère bois massif', 'Étagère murale en bois massif, design épuré.', 12, 1500, 60, 15, 8, 9500, false, ProductStatus::Published],
            ['Bague argent', 'Bague contemporaine', 'Bague en argent massif, design géométrique.', 20, 20, 0, 0, 0, 6200, false, ProductStatus::Published],
            ['Vase terre cuite', 'Vase traditionnel', 'Vase en terre cuite, technique ancestrale.', 25, 600, 18, 18, 22, 2800, false, ProductStatus::Published],
            ['Housse coussin', 'Housse lin brodé', 'Housse de coussin en lin avec broderies main.', 40, 100, 40, 40, 2, 3800, false, ProductStatus::Published],
            ['Sculpture métal', 'Sculpture fer forgé', 'Sculpture décorative en fer forgé, pièce unique.', 2, 5000, 30, 30, 80, 35000, true, ProductStatus::Published],
            ['Crème visage', 'Crème hydratante bio', 'Crème visage aux huiles végétales, formule bio.', 60, 50, 5, 5, 5, 2500, false, ProductStatus::Published],
            ['Mobile décoratif', 'Mobile en bois', 'Mobile décoratif en bois, suspension artistique.', 8, 150, 30, 30, 40, 4500, false, ProductStatus::Published],
            ['Théière céramique', 'Théière artisanale', 'Théière en céramique émaillée, capacité 1L.', 15, 600, 20, 15, 12, 6800, true, ProductStatus::Published],
            ['Châle tissé', 'Châle laine alpaga', 'Châle en laine d\'alpaga, tissage artisanal.', 12, 200, 0, 0, 0, 8500, false, ProductStatus::Published],
            ['Cadre photo', 'Cadre bois recyclé', 'Cadre photo en bois de récupération, style vintage.', 30, 300, 25, 2, 20, 1800, false, ProductStatus::Published]
        ];

        foreach ($productData as $index => $data) {
            $product = new Product();
            $product->setName($data[0])
                ->setShortDescription($data[1])
                ->setLongDescription($data[2])
                ->setStock($data[3])
                ->setWeight($data[4])
                ->setWidth($data[5])
                ->setDepth($data[6])
                ->setHeight($data[7])
                ->setPrice($data[8])
                ->setShowcaseProduct($data[9])
                ->setStatus($data[10])
                ->setCreatedAt(new \DateTimeImmutable('-' . (($index * 3) + 10) . ' days'))
                ->setCreator($creators[$index % count($creators)]);

            // Ajouter 1-2 catégories par produit
            $product->addCategory($categories[$index % count($categories)]);
            if ($index % 2 === 0) {
                $product->addCategory($categories[($index + 1) % count($categories)]);
            }

            // Ajouter 1-3 images par produit (réutiliser les mêmes images)
            $imageCount = ($index % 3) + 1;
            for ($i = 0; $i < $imageCount; $i++) {
                $imageIndex = ($index + $i) % count($images);
                $product->addImage($images[$imageIndex]);
            }

            $manager->persist($product);
            $products[] = $product;
        }

        // Création des événements (plus d'événements variés)
        $events = [];
        
        $eventData = [
            ['Atelier création bijoux', 'Apprenez à créer vos propres bijoux', 'Atelier pratique pour créer des bijoux uniques en perles naturelles.', 'Boutique Awen, Paris', '+7 days 14:00:00', '+7 days 17:00:00', EventStatus::Published],
            ['Exposition art contemporain', 'Découvrez les œuvres de nos artistes', 'Exposition collective présentant les dernières créations de nos artistes.', 'Galerie Moderna, Lyon', '+14 days 18:00:00', '+21 days 20:00:00', EventStatus::Published],
            ['Marché des créateurs', 'Rencontrez les créateurs locaux', 'Le rendez-vous mensuel des créateurs et amateurs d\'artisanat.', 'Place du marché, Nantes', '+30 days 10:00:00', '+30 days 18:00:00', EventStatus::Published],
            ['Atelier décoration durable', 'Créez des décorations écologiques', 'Apprenez à créer des décorations à partir de matériaux recyclés.', 'MakerSpace, Bordeaux', '+45 days 15:00:00', '+45 days 18:30:00', EventStatus::Draft],
            ['Conférence artisanat local', 'L\'importance de l\'artisanat local', 'Échanges sur la place de l\'artisanat dans l\'économie locale.', 'Centre culturel, Toulouse', '-15 days 10:00:00', '-15 days 12:30:00', EventStatus::Archived],
            ['Atelier poterie', 'Initiation au tour de potier', 'Découvrez l\'art de la poterie dans un atelier convivial.', 'Atelier Terre, Marseille', '+21 days 09:00:00', '+21 days 12:00:00', EventStatus::Published],
            ['Salon du textile', 'Textile artisanal et mode éthique', 'Salon dédié au textile artisanal et à la mode responsable.', 'Palais des congrès, Lille', '+60 days 10:00:00', '+62 days 18:00:00', EventStatus::Published],
            ['Atelier forge', 'Initiation au travail du métal', 'Apprenez les bases de la forge dans un véritable atelier.', 'Forge traditionnelle, Strasbourg', '+35 days 13:00:00', '+35 days 17:00:00', EventStatus::Published],
            ['Festival des métiers d\'art', 'Célébration de l\'artisanat', 'Festival annuel célébrant tous les métiers d\'art.', 'Centre-ville, Rennes', '+90 days 10:00:00', '+92 days 19:00:00', EventStatus::Draft],
            ['Atelier cosmétiques naturels', 'Fabriquez vos produits de beauté', 'Atelier pour créer ses propres cosmétiques bio.', 'Laboratoire Bio, Nice', '+28 days 14:30:00', '+28 days 17:30:00', EventStatus::Published],
            ['Marché de Noël artisanal', 'Marché spécial fêtes de fin d\'année', 'Marché de créateurs spécialisé dans les cadeaux artisanaux.', 'Place principale, Angers', '+120 days 15:00:00', '+122 days 20:00:00', EventStatus::Draft],
            ['Atelier reliure', 'L\'art de la reliure artisanale', 'Apprenez à relier vos propres livres et carnets.', 'Bibliothèque municipale, Dijon', '+42 days 10:00:00', '+42 days 16:00:00', EventStatus::Published],
            ['Exposition photographie', 'Regards sur l\'artisanat', 'Exposition photographique sur les métiers d\'art.', 'Galerie Photo, Montpellier', '+55 days 17:00:00', '+70 days 19:00:00', EventStatus::Published],
            ['Atelier sculpture pierre', 'Initiation à la taille de pierre', 'Découvrez l\'art ancestral de la sculpture sur pierre.', 'Carrière pédagogique, Caen', '+49 days 09:00:00', '+49 days 17:00:00', EventStatus::Published],
            ['Journée portes ouvertes', 'Visitez les ateliers d\'artisans', 'Journée spéciale pour découvrir les ateliers de nos créateurs.', 'Quartier artisanal, Tours', '+77 days 10:00:00', '+77 days 18:00:00', EventStatus::Published]
        ];

        foreach ($eventData as $index => $data) {
            $event = new Event();
            $event->setTitle($data[0])
                ->setShortDescription($data[1])
                ->setLongDescription($data[2])
                ->setLocation($data[3])
                ->setStartDateTime(new \DateTimeImmutable($data[4]))
                ->setEndDateTime(new \DateTimeImmutable($data[5]))
                ->setStatus($data[6]);

            // Ajouter 1-2 catégories d'événements
            $event->addEventCategory($eventCategories[$index % count($eventCategories)]);
            if ($index % 3 === 0) {
                $event->addEventCategory($eventCategories[($index + 1) % count($eventCategories)]);
            }

            // Ajouter 1-2 images par événement
            $imageCount = ($index % 2) + 1;
            for ($i = 0; $i < $imageCount; $i++) {
                $imageIndex = ($index + $i) % count($images);
                $event->addImage($images[$imageIndex]);
            }

            // Ajouter des organisateurs et participants
            $event->addUserWithStatus($creators[$index % count($creators)], EventUserStatus::ORGANIZER);
            
            // Ajouter quelques participants/intéressés
            if ($index % 2 === 0) {
                $event->addUserWithStatus($users[$index % count($users)], EventUserStatus::PARTICIPANT);
            }
            if ($index % 3 === 0) {
                $event->addUserWithStatus($users[($index + 1) % count($users)], EventUserStatus::INTERESTED);
            }

            $manager->persist($event);
            $events[] = $event;
        }        // Création des posts (plus de contenu varié)
        $posts = [];
        
        $postData = [
            ['Comment j\'ai commencé la céramique', 'Découvrez mon parcours dans le monde de la céramique, des premiers essais aux créations actuelles...', '-30 days', true],
            ['Les tendances bijouterie 2025', 'Les bijoux artisanaux connaissent un véritable renouveau ces dernières années...', '-25 days', true],
            ['Nouvelle collection été', 'Je travaille actuellement sur ma nouvelle collection pour l\'été...', '-5 days', false],
            ['L\'art du travail du bois', 'Le bois est un matériau noble qui offre d\'infinies possibilités créatives...', '-45 days', true],
            ['Retour sur mon exposition', 'La semaine dernière s\'est achevée mon exposition à Lyon...', '-8 days', true],
            ['Bienvenue sur Awen', 'Chers artisans et amateurs d\'art, nous sommes ravis de vous accueillir...', '-60 days', true],
            ['Mon expérience à l\'atelier', 'Le week-end dernier, j\'ai participé à un atelier formidable...', '-3 days', true],
            ['Techniques de forge moderne', 'La forge évolue avec son temps tout en conservant ses traditions...', '-20 days', true],
            ['Mode éthique et durable', 'L\'industrie textile se réinvente vers plus de durabilité...', '-15 days', true],
            ['Secrets de la maroquinerie', 'Travailler le cuir demande patience et précision...', '-12 days', true],
            ['Art du verre soufflé', 'Le verre en fusion offre des possibilités créatives infinies...', '-18 days', true],
            ['Cosmétiques fait maison', 'Fabriquer ses propres produits de beauté devient tendance...', '-10 days', true],
            ['Photographie et artisanat', 'Comment capturer l\'essence du travail artisanal...', '-22 days', true],
            ['Papeterie artisanale', 'L\'art de créer du papier unique et personnalisé...', '-35 days', true],
            ['Sculpture sur pierre', 'La pierre révèle ses secrets sous le ciseau de l\'artisan...', '-28 days', true],
            ['Atelier de groupe réussi', 'Retour sur notre dernier atelier collectif...', '-6 days', true],
            ['Préparation salon automne', 'Nous préparons activement le salon d\'automne...', '-4 days', false],
            ['Collaboration entre artisans', 'L\'union fait la force, même dans l\'artisanat...', '-14 days', true],
            ['Matériaux écologiques', 'Vers des créations plus respectueuses de l\'environnement...', '-16 days', true],
            ['Innovation et tradition', 'Comment concilier savoir-faire ancestral et techniques modernes...', '-24 days', true],
            ['Portrait d\'un créateur', 'Rencontre avec un artisan passionné de son métier...', '-26 days', true],
            ['Conseils débutants', 'Mes conseils pour se lancer dans l\'artisanat...', '-32 days', true],
            ['Événement communautaire', 'Retour sur notre dernière rencontre de créateurs...', '-7 days', true],
            ['Tendances décoration 2025', 'Les nouvelles tendances en matière de décoration intérieure...', '-11 days', true],
            ['Histoire de mon atelier', 'Comment j\'ai créé mon espace de travail idéal...', '-38 days', true]
        ];

        foreach ($postData as $index => $data) {
            $post = new Post();
            $post->setTitle($data[0])
                ->setContent($data[1] . ' Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.')
                ->setCreatedAt(new \DateTimeImmutable($data[2]))
                ->setIsPublished($data[3]);

            // Alterner entre créateurs, utilisateurs et admin
            if ($index % 4 === 0) {
                $post->setAuthor($creators[$index % count($creators)]);
            } elseif ($index % 4 === 1) {
                $post->setAuthor($creators[($index + 1) % count($creators)]);
            } elseif ($index % 4 === 2) {
                $post->setAuthor($users[$index % count($users)]);
            } else {
                $post->setAuthor($admin);
            }

            // Ajouter 1-2 images par post
            $imageCount = ($index % 2) + 1;
            for ($i = 0; $i < $imageCount; $i++) {
                $imageIndex = ($index + $i) % count($images);
                $post->addImage($images[$imageIndex]);
            }

            $manager->persist($post);
            $posts[] = $post;
        }        // Création de quelques commandes d'exemple
        $orders = [];
        
        // Commande 1 : Commande livrée
        $order1 = new Order();
        $order1->setUser($users[0])
            ->setStatus(OrderStatus::Delivered)
            ->setPaymentMethod('card')
            ->setShippingCost('500')
            ->setTotalHT('4165')
            ->setTvaAmount('833')
            ->setShippingAddress('15 rue des Lilas, 75001 Paris')
            ->setBillingAddress('15 rue des Lilas, 75001 Paris')
            ->setExpectedDeliveryDate(new \DateTimeImmutable('-5 days'))
            ->setDeliveredAt(new \DateTimeImmutable('-3 days'));
        
        $orderDetail1 = new OrderDetail();
        $orderDetail1->setOrderRef($order1)
            ->setProduct($products[0])
            ->setQuantity(1)
            ->setUnitPriceHT('1666')
            ->setUnitPriceTTC('1999')
            ->setTotalPriceHT('1666')
            ->setTotalPriceTTC('1999');
        
        $orderDetail2 = new OrderDetail();
        $orderDetail2->setOrderRef($order1)
            ->setProduct($products[1])
            ->setQuantity(1)
            ->setUnitPriceHT('2499')
            ->setUnitPriceTTC('2999')
            ->setTotalPriceHT('2499')
            ->setTotalPriceTTC('2999');
            
        $manager->persist($order1);
        $manager->persist($orderDetail1);
        $manager->persist($orderDetail2);
        
        // Commande 2 : Commande en cours
        $order2 = new Order();
        $order2->setUser($admin)
            ->setStatus(OrderStatus::Processing)
            ->setPaymentMethod('paypal')
            ->setShippingCost('0')
            ->setTotalHT('8325')
            ->setTvaAmount('1665')
            ->setShippingAddress('42 avenue des Champs-Élysées, 75008 Paris')
            ->setBillingAddress('42 avenue des Champs-Élysées, 75008 Paris')
            ->setExpectedDeliveryDate(new \DateTimeImmutable('+5 days'));
        
        $orderDetail3 = new OrderDetail();
        $orderDetail3->setOrderRef($order2)
            ->setProduct($products[2])
            ->setQuantity(1)
            ->setUnitPriceHT('8325')
            ->setUnitPriceTTC('9990')
            ->setTotalPriceHT('8325')
            ->setTotalPriceTTC('9990');
            
        $manager->persist($order2);
        $manager->persist($orderDetail3);
        
        // Commande 3 : Commande en attente
        $order3 = new Order();
        $order3->setUser($users[1])
            ->setStatus(OrderStatus::Pending)
            ->setPaymentMethod('card')
            ->setShippingCost('500')
            ->setTotalHT('3332')
            ->setTvaAmount('666')
            ->setShippingAddress('25 rue de la Paix, 69000 Lyon')
            ->setBillingAddress('25 rue de la Paix, 69000 Lyon')
            ->setExpectedDeliveryDate(new \DateTimeImmutable('+7 days'));
        
        $orderDetail4 = new OrderDetail();
        $orderDetail4->setOrderRef($order3)
            ->setProduct($products[0])
            ->setQuantity(2)
            ->setUnitPriceHT('1666')
            ->setUnitPriceTTC('1999')
            ->setTotalPriceHT('3332')
            ->setTotalPriceTTC('3998')
            ->setDiscountPercentage('10.00');
            
        $manager->persist($order3);
        $manager->persist($orderDetail4);

        $manager->flush();
    }
}
