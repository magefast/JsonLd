<?php

namespace Dragonfly\JsonLd\Block\StructuredData;

use Dragonfly\JsonLd\Block\Template;
use Dragonfly\JsonLd\Helper\Data;
use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

class Product extends Template
{
    public const LOGO_URL = 'https://www.com.ua/media/logo.svg';

    private Price $bundlePrice;
    private Registry $coreRegistry;
    private CategoryRepositoryInterface $categoryRepository;
    private Images $imagesHelper;
    private ?\Magento\Catalog\Model\Product $product;
    private Data $helper;

    /**
     * @param Context $context
     * @param Data $helper
     * @param Registry $registry
     * @param Price $bundlePrice
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Images $imagesHelper
     * @param array $data
     */
    public function __construct(
        Context                     $context,
        Data                        $helper,
        Registry                    $registry,
        Price                       $bundlePrice,
        CategoryRepositoryInterface $categoryRepository,
        Images                      $imagesHelper,
        array                       $data = []
    )
    {
        $this->helper = $helper;
        $this->coreRegistry = $registry;
        $this->bundlePrice = $bundlePrice;
        $this->categoryRepository = $categoryRepository;
        $this->imagesHelper = $imagesHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        if ($this->getProduct()) {
            //if ($this->getProduct()->isAvailable()) {
                return true;
            //}
        }

        return false;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct(): \Magento\Catalog\Model\Product
    {
        if (empty($this->product)) {
            $this->product = $this->coreRegistry->registry('product');
        }
        return $this->product;
    }

    /**
     * @param $product
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getProductCategory($product): ?string
    {
        $categoryIds = $product->getCategoryIds();

        if (is_array($categoryIds) && count($categoryIds) > 0) {
            $categoryId = end($categoryIds);
            $category = $this->categoryRepository->get((int)$categoryId);

            if (!empty($category) && $category->getName()) {
                return $category->getName();
            }
        }

        return null;
    }

    /**
     * @param $product
     * @return array
     */
    public function getProductImages($product): array
    {
        $imageBaseUrl = $this->imagesHelper->getBaseUrl() . 'catalog/product/';

        $productImages = [];
        if ($product->getMediaGallery()) {
            $gallery = $product->getMediaGallery();
            if (isset($gallery['images'])) {
                $imageN = 1;
                foreach ($gallery['images'] as $g) {
                    if (isset($g['file']) && $g['file'] != '') {
                        $productImages[] = $imageBaseUrl . $g['file'];
                        $imageN++;
                    }
                }
            }
        }

        return $productImages;
    }

    /**
     * @param $product
     * @return false|string
     */
    public function getProductPriceValid($product): bool|string
    {
        if ($product->getPrice() > $product->getFinalPrice()) {
            if (is_string($product->getSpecialToDate())) {
                $toDate = $product->getSpecialToDate();
                $toDate = strtotime($toDate);
                $toDate = date('Y-m-d', $toDate);

                return $toDate;
            }
        }

        return date('Y-m-d', strtotime("+10 days"));
    }

    /**
     * Retrieve logo image URL
     *
     * @return string
     */
    public function getLogoUrl(): string
    {
        return self::LOGO_URL;
    }

    /**
     * @param $string
     * @return string
     */
    public function cleanString($string): string
    {
        return $this->helper->cleanString($string);
    }

    /**
     * @param $product
     * @return array|float|int
     */
    public function getStartingPrice($product): float|int|array
    {
        if ($product->getTypeId() === 'bundle') {
            $price = $this->bundlePrice->getTotalPrices($product, 'min', 1);
        } else {
            $price = $product->getFinalPrice();
        }

        return $price;
    }
}
