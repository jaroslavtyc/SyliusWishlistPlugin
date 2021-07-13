<?php

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\DataTransformer\Wishlist;

use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use BitBag\SyliusWishlistPlugin\Command\Wishlist\WishlistTokenValueAwareInterface;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use Sylius\Bundle\ApiBundle\DataTransformer\CommandDataTransformerInterface;

class WishlistTokenValueAwareInputCommandDataTransformer implements CommandDataTransformerInterface
{
    public function transform($object, string $to, array $context = []): WishlistTokenValueAwareInterface
    {
        /** @var WishlistInterface $wishList */
        $wishList = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE];

        $object->setWishlistTokenValue($wishList->getToken());

        return $object;
    }

    public function supportsTransformation($object): bool
    {
        return $object instanceof WishlistTokenValueAwareInterface;
    }
}
