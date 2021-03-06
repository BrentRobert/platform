<?php declare(strict_types=1);

namespace Shopware\Core\Content\Test\GoogleShopping;

use Shopware\Core\Content\GoogleShopping\Aggregate\GoogleShoppingMerchantAccount\GoogleShoppingMerchantAccountEntity;
use Shopware\Core\Content\GoogleShopping\GoogleShoppingRequest;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Util\AccessKeyHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use Shopware\Core\Framework\Uuid\Uuid;
use function Flag\next6050;

trait GoogleShoppingIntegration
{
    public function getSampleCredential(): array
    {
        return [
            'access_token' => 'ya29.a0Adw1xeW4xei7do9ByIQaiPkxjw617yU1pAvYXRn',
            'refresh_token' => '1//0gTTgzGwplfyTCgYIARAAGBASNwF-L9Ir_K8q5k3l5M0ouz4hdlQ4hoE2vrqejreIjA',
            'created' => 1585199421,
            'id_token' => 'GOOGLE.' . base64_encode(json_encode(['name' => 'John Doe', 'email' => 'john.doe@example.com'])) . '.ID_TOKEN',
            'scope' => 'https://www.googleapis.com/auth/content https://www.googleapis.com/auth/adwords',
            'expires_in' => 3599,
        ];
    }

    public function connectGoogleShoppingMerchantAccount(string $accountId, string $merchantId)
    {
        $id = Uuid::randomHex();

        $merchantRepository = $this->getContainer()->get('google_shopping_merchant_account.repository');

        $merchantRepository->create([[
            'id' => $id,
            'accountId' => $accountId,
            'merchantId' => $merchantId,
        ]], $this->context);

        return $id;
    }

    public function updatetDatafeedtoMerchantAccount(string $datafeedId, string $id)
    {
        $merchantRepository = $this->getContainer()->get('google_shopping_merchant_account.repository');

        $merchantRepository->update([[
            'id' => $id,
            'datafeedId' => $datafeedId,
        ]], $this->context);

        return $id;
    }

    public function createGoogleShoppingAccount(string $id, ?string $salesChannelId = null): array
    {
        $googleAccountRepository = $this->getContainer()->get('google_shopping_account.repository');

        $credential = $this->getSampleCredential();

        $googleAccount = $this->initGoogleAccountData($id, $credential, $salesChannelId);

        $googleAccountRepository->create([$googleAccount], $this->context);

        return compact('id', 'credential', 'googleAccount');
    }

    /**
     * @beforeClass
     */
    public static function beforeClass(): void
    {
        if (!next6050()) {
            static::markTestSkipped('Skipping feature test "NEXT-6050"');
        }

        KernelLifecycleManager::bootKernel();
    }

    public function createSalesChannelGoogleShopping(bool $maintenance = false): string
    {
        $id = Uuid::randomHex();

        $this->createSalesChannel(Defaults::SALES_CHANNEL_TYPE_GOOGLE_SHOPPING, $id);

        $this->createProductExport($id, $this->createStorefrontSalesChannel($maintenance));

        return $id;
    }

    public function createGoogleShoppingRequest(?string $salesChannelId)
    {
        if (empty($salesChannelId)) {
            $salesChannelId = $this->createSalesChannelGoogleShopping();
        }

        $salesChannelEntity = $this->getContainer()->get('sales_channel.repository')->search(new Criteria([$salesChannelId]), $this->context)->first();

        return new GoogleShoppingRequest($this->context, $salesChannelEntity);
    }

    public function getMockGoogleClient(): void
    {
        if ($this->getContainer()->initialized('google_shopping_client')) {
            return;
        }

        $this->getContainer()->set(
            'google_shopping_client',
            new GoogleShoppingClientMock('clientId', 'clientSecret', 'redirectUrl')
        );
    }

    public function createStorefrontSalesChannel(bool $maintenance = false): string
    {
        $storefrontSalesChannelId = Uuid::randomHex();

        return $this->createSalesChannel(Defaults::SALES_CHANNEL_TYPE_STOREFRONT, $storefrontSalesChannelId, $maintenance);
    }

    public function createProductExport($googleShoppingSalesChannelId, $storefrontSalesChannelId): string
    {
        /** @var EntityRepositoryInterface $streamRepository */
        $streamRepository = $this->getContainer()->get('product_export.repository');
        $id = Uuid::randomHex();

        $streamRepository->create([
            [
                'id' => $id,
                'name' => 'testStream',
                'productStreamId' => $this->createProductStream($storefrontSalesChannelId),
                'storefrontSalesChannelId' => $storefrontSalesChannelId,
                'salesChannelId' => $googleShoppingSalesChannelId,
                'salesChannelDomainId' => $this->getSalesChannelDomainId(),
                'fileName' => 'google_' . $id . '.xml',
                'accessKey' => 'test',
                'encoding' => 'test',
                'fileFormat' => 'test',
                'includeVariants' => true,
                'interval' => 1,
                'currencyId' => Defaults::CURRENCY,
                'generateByCronjob' => false,
            ],
        ], Context::createDefaultContext());

        return $id;
    }

    public function getMerchantAccountEntity(array $googleAccount): GoogleShoppingMerchantAccountEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('accountId', $googleAccount['id']));

        $merchantRepository = $this->getContainer()->get('google_shopping_merchant_account.repository');

        $merchantAccount = $merchantRepository->search($criteria, $this->context)->first();

        return $merchantAccount;
    }

    protected function getSalesChannelDomainId(): string
    {
        /** @var EntityRepositoryInterface $repository */
        $repository = $this->getContainer()->get('sales_channel_domain.repository');

        return $repository->search(new Criteria(), $this->context)->first()->getId();
    }

    protected function createSalesChannel(string $salesChannelTypeId, ?string $salesChannelId = null, bool $maintenance = false)
    {
        if (!$salesChannelId) {
            $salesChannelId = Uuid::randomHex();
        }

        $salesChannel = [
            'id' => $salesChannelId,
            'accessKey' => AccessKeyHelper::generateAccessKey('sales-channel'),
            'navigation' => ['name' => 'test'],
            'typeId' => $salesChannelTypeId,
            'languageId' => Defaults::LANGUAGE_SYSTEM,
            'currencyId' => Defaults::CURRENCY,
            'currencyVersionId' => Defaults::LIVE_VERSION,
            'paymentMethodId' => $this->getValidPaymentMethodId(),
            'paymentMethodVersionId' => Defaults::LIVE_VERSION,
            'shippingMethodId' => $this->getValidShippingMethodId(),
            'shippingMethodVersionId' => Defaults::LIVE_VERSION,
            'navigationCategoryId' => $this->getValidCategoryId(),
            'navigationCategoryVersionId' => Defaults::LIVE_VERSION,
            'countryId' => $this->getValidCountryId(),
            'countryVersionId' => Defaults::LIVE_VERSION,
            'currencies' => [['id' => Defaults::CURRENCY]],
            'languages' => [['id' => Defaults::LANGUAGE_SYSTEM]],
            'shippingMethods' => [['id' => $this->getValidShippingMethodId()]],
            'paymentMethods' => [['id' => $this->getValidPaymentMethodId()]],
            'countries' => [['id' => $this->getValidCountryId()]],
            'name' => 'Storefront',
            'customerGroupId' => Defaults::FALLBACK_CUSTOMER_GROUP,
            'maintenance' => $maintenance,
        ];

        $this->getContainer()->get('sales_channel.repository')->create([$salesChannel], $this->context);

        return $salesChannelId;
    }

    private function createProductStream($salesChannelId): string
    {
        /** @var EntityRepositoryInterface $streamRepository */
        $streamRepository = $this->getContainer()->get('product_stream.repository');
        $id = Uuid::randomHex();
        $randomProductIds = implode('|', array_column($this->createProducts($salesChannelId), 'id'));

        $streamRepository->create([
            [
                'id' => $id,
                'filters' => [
                    [
                        'type' => 'equalsAny',
                        'field' => 'id',
                        'value' => $randomProductIds,
                    ],
                ],
                'name' => 'testStream',
            ],
        ], Context::createDefaultContext());

        return $id;
    }

    private function createProducts($salesChannelId): array
    {
        $products = [];

        for ($i = 0; $i < 3; ++$i) {
            $products[] = $this->getProductData($salesChannelId);
        }

        $this->getContainer()->get('product.repository')->create($products, Context::createDefaultContext());

        return $products;
    }

    private function getProductData(string $salesChannelId): array
    {
        $price = random_int(0, 10);

        return [
            'id' => Uuid::randomHex(),
            'productNumber' => Uuid::randomHex(),
            'stock' => 1,
            'name' => 'Test',
            'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => $price, 'net' => $price, 'linked' => false]],
            'manufacturer' => ['id' => Uuid::randomHex(), 'name' => 'test'],
            'tax' => ['id' => Uuid::randomHex(), 'taxRate' => 17, 'name' => 'with id'],
            'visibilities' => [
                ['salesChannelId' => $salesChannelId, 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
            ],
        ];
    }

    private function initGoogleAccountData(string $id, array $credential, ?string $salesChannelId): array
    {
        if (empty($salesChannelId)) {
            $salesChannelId = $this->createSalesChannelGoogleShopping();
        }

        return [
            'id' => $id,
            'salesChannelId' => $salesChannelId,
            'email' => 'foo@test.co',
            'name' => 'test',
            'credential' => $credential,
        ];
    }
}
