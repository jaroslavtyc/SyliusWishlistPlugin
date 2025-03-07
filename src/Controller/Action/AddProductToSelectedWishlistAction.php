<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusWishlistPlugin\Controller\Action;

use BitBag\SyliusWishlistPlugin\Command\Wishlist\AddProductToSelectedWishlist;
use BitBag\SyliusWishlistPlugin\Entity\WishlistInterface;
use BitBag\SyliusWishlistPlugin\Exception\ProductNotFoundException;
use BitBag\SyliusWishlistPlugin\Exception\WishlistNotFoundException;
use BitBag\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AddProductToSelectedWishlistAction
{
    private WishlistRepositoryInterface $wishlistRepository;

    private ProductRepositoryInterface $productRepository;

    private RequestStack $requestStack;

    private TranslatorInterface $translator;

    private UrlGeneratorInterface $urlGenerator;

    private MessageBusInterface $commandBus;

    public function __construct(
        WishlistRepositoryInterface $wishlistRepository,
        ProductRepositoryInterface $productRepository,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        MessageBusInterface $commandBus
    ) {
        $this->wishlistRepository = $wishlistRepository;
        $this->productRepository = $productRepository;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->commandBus = $commandBus;
    }

    public function __invoke(int $wishlistId, int $productId): Response
    {
        /** @var WishlistInterface $wishlist */
        $wishlist = $this->wishlistRepository->find($wishlistId);

        if (null === $wishlist) {
            throw new WishlistNotFoundException(
                'Wishlist not found.'
            );
        }

        /** @var ProductInterface $product */
        $product = $this->productRepository->find($productId);

        if (null === $product) {
            throw new ProductNotFoundException(
                sprintf('The Product %s does not exist', $productId)
            );
        }

        $addProductToSelectedWishlist = new AddProductToSelectedWishlist($wishlist, $product);
        $this->commandBus->dispatch($addProductToSelectedWishlist);

        /** @var Session $session */
        $session = $this->requestStack->getSession();

        $session->getFlashBag()->add('success', $this->translator->trans('bitbag_sylius_wishlist_plugin.ui.added_wishlist_item'));

        return new RedirectResponse(
            $this->urlGenerator->generate('bitbag_sylius_wishlist_plugin_shop_wishlist_show_chosen_wishlist', [
            'wishlistId' => $wishlistId,
        ])
        );
    }
}
