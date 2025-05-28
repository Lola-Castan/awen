<?php

namespace App\Enum;

/**
 * Enum représentant les différents statuts possibles d'une commande.
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    
    /**
     * Retourne une représentation lisible du statut
     */
    public function getLabel(): string
    {
        return match($this) {
            self::Pending => 'En attente',
            self::Paid => 'Payée',
            self::Processing => 'En préparation',
            self::Shipped => 'Expédiée',
            self::Delivered => 'Livrée',
            self::Cancelled => 'Annulée',
            self::Refunded => 'Remboursée',
        };
    }
    
    /**
     * Vérifie si la commande est en cours de traitement
     */
    public function isInProgress(): bool
    {
        return in_array($this, [
            self::Pending,
            self::Paid,
            self::Processing,
            self::Shipped
        ]);
    }
    
    /**
     * Vérifie si la commande est terminée (livrée ou annulée)
     */
    public function isCompleted(): bool
    {
        return in_array($this, [
            self::Delivered,
            self::Cancelled,
            self::Refunded
        ]);
    }
} 