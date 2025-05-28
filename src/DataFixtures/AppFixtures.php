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

        // Create basic user
        $user = new User();
        $user->setUsername('basicuser')
            ->setEmail('user@example.com')
            ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setBirthDate(new \DateTimeImmutable('1990-01-01'))
            ->setCreatedAt(new \DateTimeImmutable());

        $user->addRole($roleUser);
        $manager->persist($user);

        // Creator
        $creator = new User();
        $creator->setUsername('creatoruser')
            ->setEmail('creator@example.com')
            ->setPassword($this->passwordHasher->hashPassword($creator, 'password'))
            ->setFirstName('Clara')
            ->setLastName('Craft')
            ->setBirthDate(new \DateTimeImmutable('1988-03-05'))
            ->setCreatedAt(new \DateTimeImmutable());

        $creator->addRole($roleUser);
        $creator->addRole($roleCreator);

        $creatorInfo = $creator->getCreatorInfo();
        $creatorInfo->setDisplayName('Clara C.')
            ->setInstagramProfile('https://instagram.com/claracraft')
            ->setFacebookProfile('https://facebook.com/claracraft')
            ->setPinterestProfile('https://pinterest.com/claracraft')
            ->setDescription('Créatrice passionnée par le DIY')
            ->setPracticalInfos('Livraison sous 5 jours ouvrés')
            ->setCoverImage('cover_clara.jpg');

        $manager->persist($creator);
        
        // Second Creator
        $creator2 = new User();
        $creator2->setUsername('jules')
            ->setEmail('jules@example.com')
            ->setPassword($this->passwordHasher->hashPassword($creator2, 'password'))
            ->setFirstName('Jules')
            ->setLastName('Martin')
            ->setBirthDate(new \DateTimeImmutable('1992-07-15'))
            ->setCreatedAt(new \DateTimeImmutable());

        $creator2->addRole($roleUser);
        $creator2->addRole($roleCreator);

        $creatorInfo2 = $creator2->getCreatorInfo();
        $creatorInfo2->setDisplayName('Jules Art')
            ->setInstagramProfile('https://instagram.com/julesart')
            ->setDescription('Artiste contemporain travaillant principalement avec le bois')
            ->setPracticalInfos('Ateliers disponibles sur demande')
            ->setCoverImage('cover_jules.jpg');

        $manager->persist($creator2);

        // Admin
        $admin = new User();
        $admin->setUsername('admin')
            ->setEmail('admin@example.com')
            ->setPassword($this->passwordHasher->hashPassword($admin, 'adminpass'))
            ->setFirstName('Ada')
            ->setLastName('Root')
            ->setBirthDate(new \DateTimeImmutable('1980-12-12'))
            ->setCreatedAt(new \DateTimeImmutable());

        $admin->addRole($roleUser);
        $admin->addRole($roleAdmin);
        $manager->persist($admin);
        
        // Création des catégories
        $categoryDeco = new Category();
        $categoryDeco->setName('Décoration');
        $manager->persist($categoryDeco);
        
        $categoryBijoux = new Category();
        $categoryBijoux->setName('Bijoux');
        $manager->persist($categoryBijoux);
        
        $categoryArt = new Category();
        $categoryArt->setName('Art');
        $manager->persist($categoryArt);
        
        $categoryMaison = new Category();
        $categoryMaison->setName('Maison');
        $manager->persist($categoryMaison);
        
        $categoryMode = new Category();
        $categoryMode->setName('Mode');
        $manager->persist($categoryMode);
        
        // Association des catégories aux créateurs
        $categoryDeco->addCreator($creator);
        $categoryBijoux->addCreator($creator);
        $categoryMaison->addCreator($creator);
        
        $categoryArt->addCreator($creator2);
        $categoryDeco->addCreator($creator2);
        
        // Création des catégories d'événements
        $eventCategoryAtelier = new EventCategory();
        $eventCategoryAtelier->setName('Atelier');
        $eventCategoryAtelier->setDescription('Ateliers pratiques et participatifs');
        $manager->persist($eventCategoryAtelier);
        
        $eventCategoryExposition = new EventCategory();
        $eventCategoryExposition->setName('Exposition');
        $eventCategoryExposition->setDescription('Expositions artistiques et culturelles');
        $manager->persist($eventCategoryExposition);
        
        $eventCategoryMarche = new EventCategory();
        $eventCategoryMarche->setName('Marché');
        $eventCategoryMarche->setDescription('Marchés de créateurs et ventes éphémères');
        $manager->persist($eventCategoryMarche);
        
        $eventCategoryConference = new EventCategory();
        $eventCategoryConference->setName('Conférence');
        $eventCategoryConference->setDescription('Conférences, tables rondes et discussions');
        $manager->persist($eventCategoryConference);
        
        $eventCategoryFormation = new EventCategory();
        $eventCategoryFormation->setName('Formation');
        $eventCategoryFormation->setDescription('Formations et cours techniques');
        $manager->persist($eventCategoryFormation);
        
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

        // Create a product with ManyToMany relation to images
        $product1 = new Product();
        $product1->setName('Vase artisanal')
            ->setShortDescription('Vase en céramique fait main')
            ->setLongDescription('Vase en céramique entièrement fait à la main avec des matériaux naturels et locaux. Chaque pièce est unique.')
            ->setStock(100)
            ->setWeight(500)
            ->setWidth(20)
            ->setDepth(30)
            ->setHeight(15)
            ->setPrice(1999) // Price in cents (19.99 EUR)
            ->setShowcaseProduct(true)
            ->setStatus(ProductStatus::Published)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setCreator($creator)
            ->addCategory($categoryDeco)
            ->addCategory($categoryMaison)
            ->addImage($image1)
            ->addImage($image2);

        $manager->persist($product1);
        
        // Create another product
        $product2 = new Product();
        $product2->setName('Collier perles')
            ->setShortDescription('Collier en perles naturelles')
            ->setLongDescription('Collier en perles naturelles monté à la main. Ce bijou élégant saura sublimer toutes vos tenues.')
            ->setStock(50)
            ->setWeight(700)
            ->setWidth(15)
            ->setDepth(25)
            ->setHeight(20)
            ->setPrice(2999) // Price in cents (29.99 EUR)
            ->setShowcaseProduct(false)
            ->setStatus(ProductStatus::Draft)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setCreator($creator)
            ->addCategory($categoryBijoux)
            ->addCategory($categoryMode)
            ->addImage($image3);

        $manager->persist($product2);
        
        // Product from second creator
        $product3 = new Product();
        $product3->setName('Sculpture bois')
            ->setShortDescription('Sculpture abstraite en bois')
            ->setLongDescription('Œuvre d\'art unique créée avec du bois de récupération. Cette sculpture apportera une touche originale à votre intérieur.')
            ->setStock(10)
            ->setWeight(1200)
            ->setWidth(40)
            ->setDepth(30)
            ->setHeight(50)
            ->setPrice(9990) // Price in cents (99.90 EUR)
            ->setShowcaseProduct(true)
            ->setStatus(ProductStatus::Published)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setCreator($creator2)
            ->addCategory($categoryArt)
            ->addCategory($categoryDeco)
            ->addImage($image4)
            ->addImage($image5)
            ->addImage($image6);

        $manager->persist($product3);
        
        // Création des événements
        
        // Événement 1: Atelier de création de bijoux
        $event1 = new Event();
        $event1->setTitle('Atelier de création de bijoux')
            ->setShortDescription('Apprenez à créer vos propres bijoux en perles naturelles')
            ->setLongDescription('Rejoignez-nous pour un atelier pratique où vous apprendrez à créer vos propres bijoux en perles naturelles. Tous les matériaux sont fournis et vous repartirez avec votre création. Cet atelier est adapté à tous les niveaux, aucune expérience préalable n\'est requise.')
            ->setLocation('Boutique Awen, 15 rue des Artisans, Paris')
            ->setStartDateTime(new \DateTimeImmutable('+7 days 14:00:00'))
            ->setEndDateTime(new \DateTimeImmutable('+7 days 17:00:00'))
            ->setStatus(EventStatus::Published) // Événement publié
            ->addImage($image7)
            ->addImage($image3)
            ->addEventCategory($eventCategoryAtelier)
            ->addEventCategory($eventCategoryFormation);
            
        $manager->persist($event1);
        
        // Événement 2: Exposition d'art contemporain
        $event2 = new Event();
        $event2->setTitle('Exposition d\'art contemporain')
            ->setShortDescription('Découvrez les nouvelles œuvres de Jules Martin')
            ->setLongDescription('Une exposition exceptionnelle présentant les dernières créations de Jules Martin. Venez découvrir ses sculptures en bois et échanger avec l\'artiste sur son processus créatif. Un verre de bienvenue sera offert.')
            ->setLocation('Galerie Moderna, 8 avenue des Arts, Lyon')
            ->setStartDateTime(new \DateTimeImmutable('+14 days 18:00:00'))
            ->setEndDateTime(new \DateTimeImmutable('+21 days 20:00:00'))
            ->setStatus(EventStatus::Cancelled) // Événement annulé
            ->addImage($image8)
            ->addImage($image4)
            ->addImage($image5)
            ->addEventCategory($eventCategoryExposition);
            
        $manager->persist($event2);
        
        // Événement 3: Marché des créateurs
        $event3 = new Event();
        $event3->setTitle('Marché des créateurs')
            ->setShortDescription('Rencontrez les créateurs locaux et découvrez leurs créations uniques')
            ->setLongDescription('Le marché des créateurs est l\'occasion idéale pour découvrir les talents locaux et leurs créations artisanales uniques. Bijoux, décorations, accessoires de mode, art... il y en a pour tous les goûts ! Venez nombreux soutenir l\'artisanat local.')
            ->setLocation('Place du marché, Nantes')
            ->setStartDateTime(new \DateTimeImmutable('+30 days 10:00:00'))
            ->setEndDateTime(new \DateTimeImmutable('+30 days 18:00:00'))
            ->setStatus(EventStatus::Published) // Événement publié
            ->addImage($image9)
            ->addEventCategory($eventCategoryMarche);
            
        $manager->persist($event3);
        
        // Événement 4: Atelier en cours de préparation
        $event4 = new Event();
        $event4->setTitle('Atelier de décoration durable')
            ->setShortDescription('Créez des décorations écologiques pour votre intérieur')
            ->setLongDescription('Dans cet atelier, vous apprendrez à créer des décorations pour votre maison à partir de matériaux recyclés et durables. Une façon créative de donner une seconde vie à vos objets du quotidien tout en décorant votre intérieur avec style.')
            ->setLocation('MakerSpace, 25 rue de l\'Innovation, Bordeaux')
            ->setStartDateTime(new \DateTimeImmutable('+45 days 15:00:00'))
            ->setEndDateTime(new \DateTimeImmutable('+45 days 18:30:00'))
            ->setStatus(EventStatus::Draft) // Événement en brouillon
            ->addImage($image1)
            ->addEventCategory($eventCategoryAtelier)
            ->addEventCategory($eventCategoryFormation);
            
        $manager->persist($event4);
        
        // Événement 5: Conférence terminée
        $event5 = new Event();
        $event5->setTitle('Conférence sur l\'artisanat local')
            ->setShortDescription('Échanges autour de l\'importance de l\'artisanat dans l\'économie locale')
            ->setLongDescription('Une conférence passionnante sur la place de l\'artisanat dans notre économie locale, avec des témoignages de créateurs et d\'experts du secteur. Un moment d\'échange et de partage autour de valeurs communes.')
            ->setLocation('Centre culturel, Toulouse')
            ->setStartDateTime(new \DateTimeImmutable('-15 days 10:00:00'))
            ->setEndDateTime(new \DateTimeImmutable('-15 days 12:30:00'))
            ->setStatus(EventStatus::Archived) // Événement archivé
            ->addImage($image8)
            ->addEventCategory($eventCategoryConference);
            
        $manager->persist($event5);
        
        // Gestion des relations Event-User via l'entité EventUser
        
        // Clara organise l'atelier de création de bijoux
        $event1->addUserWithStatus($creator, EventUserStatus::ORGANIZER);
        
        // Jules organise l'exposition d'art contemporain (annulée)
        $event2->addUserWithStatus($creator2, EventUserStatus::ORGANIZER);
        
        // Les deux créateurs organisent le marché des créateurs
        $event3->addUserWithStatus($creator, EventUserStatus::ORGANIZER);
        $event3->addUserWithStatus($creator2, EventUserStatus::ORGANIZER);
        
        // Clara organise l'atelier de décoration durable (en brouillon)
        $event4->addUserWithStatus($creator, EventUserStatus::ORGANIZER);
        
        // Jules organisait la conférence (archivée)
        $event5->addUserWithStatus($creator2, EventUserStatus::ORGANIZER);
        
        // L'utilisateur John est intéressé par l'atelier de création
        $event1->addUserWithStatus($user, EventUserStatus::INTERESTED);
        
        // John participe à l'exposition d'art (même si elle est annulée)
        $event2->addUserWithStatus($user, EventUserStatus::PARTICIPANT);
        
        // L'admin est invitée à tous les événements
        $event1->addUserWithStatus($admin, EventUserStatus::INVITED);
        $event2->addUserWithStatus($admin, EventUserStatus::INVITED);
        $event3->addUserWithStatus($admin, EventUserStatus::INVITED);
        $event4->addUserWithStatus($admin, EventUserStatus::INVITED);
        $event5->addUserWithStatus($admin, EventUserStatus::PARTICIPANT); // A participé à l'événement archivé
        
        // Jules est intéressé par l'atelier de Clara
        $event1->addUserWithStatus($creator2, EventUserStatus::INTERESTED);
        
        // Clara participe à l'exposition de Jules
        $event2->addUserWithStatus($creator, EventUserStatus::PARTICIPANT);
        
        // John a participé à la conférence archivée
        $event5->addUserWithStatus($user, EventUserStatus::PARTICIPANT);

        // Création des posts
        
        // Posts du créateur Clara
        $post1 = new Post();
        $post1->setTitle('Comment j\'ai commencé la céramique')
            ->setContent('Découvrez mon parcours dans le monde de la céramique, des premiers essais aux créations actuelles. La céramique est un art ancestral qui demande patience et précision.

J\'ai commencé il y a maintenant 5 ans, après avoir suivi un atelier d\'initiation qui m\'a complètement passionnée. Depuis, je n\'ai cessé d\'explorer différentes techniques et de perfectionner mon style.

Dans cet article, je partage avec vous les étapes clés de mon parcours, les difficultés rencontrées et comment j\'ai réussi à développer ma propre ligne de produits en céramique.')
            ->setAuthor($creator)
            ->setCreatedAt(new \DateTimeImmutable('-30 days'))
            ->setIsPublished(true)
            ->addImage($image1)
            ->addImage($image2);
        $manager->persist($post1);
        
        $post2 = new Post();
        $post2->setTitle('Les tendances de la bijouterie artisanale')
            ->setContent('Les bijoux artisanaux connaissent un véritable renouveau ces dernières années. De plus en plus de personnes se tournent vers des pièces uniques, fabriquées à la main avec des matériaux de qualité.

Parmi les tendances actuelles, on observe un retour aux matériaux naturels comme le bois, la pierre et les perles organiques. Les créations minimalistes et épurées sont également très recherchées, tout comme les bijoux inspirés de formes géométriques.

Dans ce post, je vous présente les tendances qui marqueront cette année et comment les intégrer à votre style personnel.')
            ->setAuthor($creator)
            ->setCreatedAt(new \DateTimeImmutable('-15 days'))
            ->setIsPublished(true)
            ->addImage($image3);
        $manager->persist($post2);
        
        $post3 = new Post();
        $post3->setTitle('À venir : nouvelle collection été')
            ->setContent('Je travaille actuellement sur ma nouvelle collection pour l\'été. Des couleurs vives, des matières légères et des designs rafraîchissants seront à l\'honneur.

Cette collection s\'inspire de mes voyages récents et de mon amour pour la nature. Vous y trouverez des pièces uniques qui apporteront une touche d\'originalité à votre intérieur ou à votre tenue.

Restez connectés pour découvrir les premiers aperçus dans les semaines à venir !')
            ->setAuthor($creator)
            ->setCreatedAt(new \DateTimeImmutable('-5 days'))
            ->setIsPublished(false) // Brouillon
            ->addImage($image2);
        $manager->persist($post3);
        
        // Posts du créateur Jules
        $post4 = new Post();
        $post4->setTitle('L\'art du travail du bois')
            ->setContent('Le bois est un matériau noble qui offre d\'infinies possibilités créatives. Dans cet article, je vous partage ma passion pour la sculpture sur bois et les techniques que j\'ai développées au fil des années.

Chaque essence de bois possède ses propres caractéristiques : dureté, grain, couleur, veinage... Apprendre à les connaître et à les respecter est essentiel pour créer des pièces qui mettent en valeur la beauté naturelle du matériau.

Je vous invite à découvrir mon approche de la sculpture, entre tradition et modernité, et comment je donne vie à mes idées à travers ce médium ancestral.')
            ->setAuthor($creator2)
            ->setCreatedAt(new \DateTimeImmutable('-45 days'))
            ->setIsPublished(true)
            ->addImage($image4)
            ->addImage($image5)
            ->addImage($image6);
        $manager->persist($post4);
        
        $post5 = new Post();
        $post5->setTitle('Retour sur mon exposition à Lyon')
            ->setContent('La semaine dernière s\'est achevée mon exposition à la Galerie Moderna de Lyon. Ce fut une expérience incroyable de pouvoir partager mon travail avec un public aussi enthousiaste et curieux.

Pendant dix jours, j\'ai eu l\'opportunité de présenter mes dernières créations et d\'échanger avec les visiteurs sur mon processus créatif. Ces conversations enrichissantes m\'ont apporté de nouvelles perspectives et idées pour mes futurs projets.

Je tiens à remercier tous ceux qui ont fait le déplacement et qui ont contribué à faire de cet événement un succès. Votre soutien est précieux et me motive à continuer à créer et à innover.')
            ->setAuthor($creator2)
            ->setCreatedAt(new \DateTimeImmutable('-8 days'))
            ->setIsPublished(true)
            ->addImage($image8);
        $manager->persist($post5);
        
        // Post de l'admin
        $post6 = new Post();
        $post6->setTitle('Bienvenue sur la plateforme Awen')
            ->setContent('Chers artisans et amateurs d\'art,

Nous sommes ravis de vous accueillir sur Awen, la nouvelle plateforme dédiée à l\'artisanat et à la création artistique locale. Notre ambition est de créer un espace où créateurs et passionnés peuvent se rencontrer, échanger et partager leur amour pour le fait-main.

Sur Awen, vous pourrez découvrir des créations uniques, suivre vos artisans préférés, participer à des événements exclusifs et même commander directement auprès des créateurs.

Nous vous invitons à explorer le site, à créer votre profil et à commencer cette aventure avec nous. N\'hésitez pas à nous faire part de vos suggestions pour améliorer l\'expérience Awen.')
            ->setAuthor($admin)
            ->setCreatedAt(new \DateTimeImmutable('-60 days'))
            ->setIsPublished(true)
            ->addImage($image9);
        $manager->persist($post6);
        
        // Post utilisateur standard
        $post7 = new Post();
        $post7->setTitle('Mon expérience à l\'atelier de bijoux')
            ->setContent('Le week-end dernier, j\'ai eu la chance de participer à l\'atelier de création de bijoux animé par Clara. Ce fut une expérience enrichissante que je souhaite partager avec vous.

En trois heures, j\'ai pu apprendre les bases de la création de bijoux en perles naturelles et repartir avec un magnifique bracelet personnalisé. Clara est une formatrice patiente et pédagogue qui sait transmettre sa passion.

L\'ambiance était conviviale et le petit groupe a permis d\'avoir un suivi personnalisé. Je recommande vivement cet atelier à tous ceux qui souhaitent s\'initier à la création de bijoux ou simplement passer un moment créatif et agréable.')
            ->setAuthor($user)
            ->setCreatedAt(new \DateTimeImmutable('-3 days'))
            ->setIsPublished(true)
            ->addImage($image3)
            ->addImage($image7);
        $manager->persist($post7);

        // Création des commandes
        
        // Commande 1 : Commande complétée par l'utilisateur de base
        $order1 = new Order();
        $order1->setUser($user)
            ->setStatus(OrderStatus::Delivered)
            ->setPaymentMethod('card')
            ->setShippingCost('500') // 5.00 EUR
            ->setTotalHT('4165') // 41.65 EUR
            ->setTvaAmount('833') // 8.33 EUR (20% de 41.65)
            ->setShippingAddress('15 rue des Lilas, 75001 Paris')
            ->setBillingAddress('15 rue des Lilas, 75001 Paris')
            ->setExpectedDeliveryDate(new \DateTimeImmutable('-5 days'))
            ->setDeliveredAt(new \DateTimeImmutable('-3 days'));
        
        $orderDetail1 = new OrderDetail();
        $orderDetail1->setOrderRef($order1)
            ->setProduct($product1)
            ->setQuantity(1)
            ->setUnitPriceHT('1666') // 16.66 EUR
            ->setUnitPriceTTC('1999') // 19.99 EUR
            ->setTotalPriceHT('1666')
            ->setTotalPriceTTC('1999');
        
        $orderDetail2 = new OrderDetail();
        $orderDetail2->setOrderRef($order1)
            ->setProduct($product2)
            ->setQuantity(1)
            ->setUnitPriceHT('2499') // 24.99 EUR
            ->setUnitPriceTTC('2999') // 29.99 EUR
            ->setTotalPriceHT('2499')
            ->setTotalPriceTTC('2999');
            
        $manager->persist($order1);
        $manager->persist($orderDetail1);
        $manager->persist($orderDetail2);
        
        // Commande 2 : Commande en cours de traitement
        $order2 = new Order();
        $order2->setUser($admin)
            ->setStatus(OrderStatus::Processing)
            ->setPaymentMethod('paypal')
            ->setShippingCost('0') // Livraison gratuite
            ->setTotalHT('8325') // 83.25 EUR
            ->setTvaAmount('1665') // 16.65 EUR (20% de 83.25)
            ->setShippingAddress('42 avenue des Champs-Élysées, 75008 Paris')
            ->setBillingAddress('42 avenue des Champs-Élysées, 75008 Paris')
            ->setExpectedDeliveryDate(new \DateTimeImmutable('+5 days'));
        
        $orderDetail3 = new OrderDetail();
        $orderDetail3->setOrderRef($order2)
            ->setProduct($product3)
            ->setQuantity(1)
            ->setUnitPriceHT('8325')
            ->setUnitPriceTTC('9990')
            ->setTotalPriceHT('8325')
            ->setTotalPriceTTC('9990');
            
        $manager->persist($order2);
        $manager->persist($orderDetail3);
        
        // Commande 3 : Commande en attente de paiement
        $order3 = new Order();
        $order3->setUser($user)
            ->setStatus(OrderStatus::Pending)
            ->setPaymentMethod('card')
            ->setShippingCost('500') // 5.00 EUR
            ->setTotalHT('3332') // 33.32 EUR
            ->setTvaAmount('666') // 6.66 EUR (20% de 33.32)
            ->setShippingAddress('15 rue des Lilas, 75001 Paris')
            ->setBillingAddress('15 rue des Lilas, 75001 Paris')
            ->setExpectedDeliveryDate(new \DateTimeImmutable('+7 days'));
        
        $orderDetail4 = new OrderDetail();
        $orderDetail4->setOrderRef($order3)
            ->setProduct($product1)
            ->setQuantity(2)
            ->setUnitPriceHT('1666')
            ->setUnitPriceTTC('1999')
            ->setTotalPriceHT('3332')
            ->setTotalPriceTTC('3998')
            ->setDiscountPercentage('10.00'); // 10% de réduction
            
        $manager->persist($order3);
        $manager->persist($orderDetail4);

        $manager->flush();
    }
}
