<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Enum\OrderStatus;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart_index', methods: ['GET'])]
    public function index(CartService $cartService): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $cartService->getCart(),
            'total' => $cartService->getTotal(),
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request, CartService $cartService): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);
        $cartService->add($product, $quantity);

        $this->addFlash('success', 'Le produit a été ajouté au panier.');

        // Rediriger vers la page précédente ou le panier
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?? $this->generateUrl('app_cart_index'));
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(Product $product, CartService $cartService): Response
    {
        $cartService->remove($product);
        $this->addFlash('success', 'Le produit a été retiré du panier.');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(Product $product, Request $request, CartService $cartService): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);
        $cartService->updateQuantity($product, $quantity);
        return $this->redirectToRoute('app_cart_index');
    }    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(CartService $cartService): Response
    {
        $cartService->clear();
        $this->addFlash('success', 'Le panier a été vidé.');
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/checkout', name: 'app_cart_checkout', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function checkout(CartService $cartService, EntityManagerInterface $entityManager): Response
    {
        $cart = $cartService->getCart();
        
        if (empty($cart)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart_index');
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        // Créer la commande
        $order = new Order();
        $order->setUser($user);
        $order->setStatus(OrderStatus::Pending);
        $order->setPaymentMethod('skipped'); // On skip le paiement
        $order->setShippingCost('0'); // Frais de livraison gratuits pour l'instant
        
        // Adresses par défaut (vous pouvez les améliorer plus tard)
        $defaultAddress = ($user->getAddress1() ?? 'Adresse non renseignée') . 
                         ($user->getAddress2() ? ', ' . $user->getAddress2() : '') .
                         ($user->getZipCode() && $user->getCity() ? ', ' . $user->getZipCode() . ' ' . $user->getCity() : '');
        
        $order->setShippingAddress($defaultAddress);
        $order->setBillingAddress($defaultAddress);
        
        // Date de livraison estimée (7 jours)
        $order->setExpectedDeliveryDate(new \DateTime('+7 days'));
        
        // Calculer les totaux
        $totalHT = 0;
        $totalTTC = 0;
        
        foreach ($cart as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];
            $unitPrice = $product->getPrice(); // Prix en centimes
            
            // Calcul HT (on considère 20% de TVA)
            $unitPriceHT = round($unitPrice / 1.20);
            $unitPriceTTC = $unitPrice;
            
            $totalPriceHT = $unitPriceHT * $quantity;
            $totalPriceTTCItem = $unitPriceTTC * $quantity;
            
            $totalHT += $totalPriceHT;
            $totalTTC += $totalPriceTTCItem;
            
            // Créer le détail de commande
            $orderDetail = new OrderDetail();
            $orderDetail->setOrderRef($order);
            $orderDetail->setProduct($product);
            $orderDetail->setQuantity($quantity);
            $orderDetail->setUnitPriceHT(number_format($unitPriceHT / 100, 2, '.', '')); // Conversion en euros
            $orderDetail->setUnitPriceTTC(number_format($unitPriceTTC / 100, 2, '.', ''));
            $orderDetail->setTotalPriceHT(number_format($totalPriceHT / 100, 2, '.', ''));
            $orderDetail->setTotalPriceTTC(number_format($totalPriceTTCItem / 100, 2, '.', ''));
            $orderDetail->setDiscountPercentage('0.00');
            
            $entityManager->persist($orderDetail);
            $order->addOrderDetail($orderDetail);
        }
        
        // Calculer la TVA
        $tvaAmount = $totalTTC - $totalHT;
        
        // Définir les totaux sur la commande (en euros)
        $order->setTotalHT(number_format($totalHT / 100, 2, '.', ''));
        $order->setTotalTTC(number_format($totalTTC / 100, 2, '.', ''));
        $order->setTvaAmount(number_format($tvaAmount / 100, 2, '.', ''));
        
        // Sauvegarder la commande
        $entityManager->persist($order);
        $entityManager->flush();
        
        // Vider le panier
        $cartService->clear();
        
        $this->addFlash('success', 'Votre commande a été créée avec succès ! Numéro de commande : #' . $order->getId());
        
        // Rediriger vers les commandes de l'utilisateur
        return $this->redirectToRoute('app_account_orders');
    }
}