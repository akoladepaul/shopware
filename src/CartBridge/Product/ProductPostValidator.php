<?php

namespace Shopware\CartBridge\Product;

use Shopware\Cart\Cart\Struct\CalculatedCart;
use Shopware\Cart\Cart\Struct\CartContainer;
use Shopware\Cart\Cart\CartProcessorInterface;
use Shopware\CartBridge\Product\Struct\CalculatedProduct;
use Shopware\Context\Struct\ShopContext;
use Shopware\Framework\Struct\StructCollection;

class ProductPostValidator implements CartProcessorInterface
{
    public function process(
        CartContainer $cartContainer,
        CalculatedCart $calculatedCart,
        StructCollection $dataCollection,
        ShopContext $context
    ): void {

        $products = $calculatedCart->getCalculatedLineItems()->filterInstance(
            CalculatedProduct::class
        );

        if ($products->count() <= 0) {
            return;
        }

        $throwException = false;

        /** @var CalculatedProduct[] $products */
        foreach ($products as $product) {

            if (!$product->getRule()) {
                continue;
            }

            $valid = $product->getRule()->match(
                $calculatedCart,
                $context,
                $dataCollection
            );

            if ($valid) {
                continue;
            }

            $calculatedCart->getCalculatedLineItems()->remove($product->getIdentifier());
            $cartContainer->getLineItems()->remove($product->getIdentifier());
            $throwException = true;
        }
    }
}