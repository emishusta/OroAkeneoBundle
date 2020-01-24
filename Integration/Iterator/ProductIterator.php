<?php

namespace Oro\Bundle\AkeneoBundle\Integration\Iterator;

use Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface;
use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;
use Psr\Log\LoggerInterface;

class ProductIterator extends AbstractIterator
{
    /**
     * @var bool
     */
    private $attributesInitialized = false;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var bool
     */
    private $familyVariantsInitialized = false;

    /**
     * @var array
     */
    private $familyVariants = [];

    /**
     * @var AttributeIterator
     */
    private $attributesList;

    /**
     * AttributeIterator constructor.
     */
    public function __construct(
        ResourceCursorInterface $resourceCursor,
        AkeneoPimEnterpriseClientInterface $client,
        LoggerInterface $logger,
        AttributeIterator $attributeList
    ) {
        parent::__construct($resourceCursor, $client, $logger);
        $this->attributesList = $attributeList;
    }

    /**
     * {@inheritdoc}
     */
    public function doCurrent()
    {
        $product = $this->resourceCursor->current();

        $this->setValueAttributeTypes($product);
        $this->setFamilyVariant($product);

        return $product;
    }

    /**
     * Set attribute types for product values.
     */
    protected function setValueAttributeTypes(array &$product)
    {
        if (false === $this->attributesInitialized) {
            foreach ($this->attributesList as $attribute) {
                if (null === $attribute) {
                    continue;
                }

                $this->attributes[$attribute['code']] = $attribute;
            }
            $this->attributesInitialized = true;
        }

        foreach ($product['values'] as $code => $values) {
            if (isset($this->attributes[$code])) {
                foreach ($values as $key => $value) {
                    $product['values'][$code][$key]['type'] = $this->attributes[$code]['type'];
                }
            } else {
                unset($product['values'][$code]);
            }
        }
    }

    /**
     * Set family variant from API.
     */
    private function setFamilyVariant(array &$model)
    {
        if (false === $this->familyVariantsInitialized) {
            foreach ($this->client->getFamilyApi()->all(self::PAGE_SIZE) as $family) {
                foreach ($this->client->getFamilyVariantApi()->all($family['code'], self::PAGE_SIZE) as $variant) {
                    $variant['family'] = $family['code'];
                    $this->familyVariants[$variant['code']] = $variant;
                }
            }
            $this->familyVariantsInitialized = true;
        }

        if (empty($model['family_variant'])) {
            return;
        }

        if (isset($this->familyVariants[$model['family_variant']])) {
            $model['family_variant'] = $this->familyVariants[$model['family_variant']];
        }
    }
}
