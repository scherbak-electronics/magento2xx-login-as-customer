<?php
declare(strict_types=1);

namespace Shch\Lasc\Ui\Customer\Component\Button;

use Exception;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;

/**
 * Get data for Login as Customer button.
 * Use this class as a base for virtual types declaration.
 */
class DataProvider
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var array
     */
    private $data;

    /**
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Escaper $escaper,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
        $this->data = $data;
    }

    /**
     * Get data for Login as Customer button.
     *
     * @param int $customerId
     * @return array
     * @throws Exception
     */
    public function getData(int $customerId): array
    {
        $buttonData = [
            'on_click' => sprintf("location.href = '%s';", $this->getLoginUrl($customerId)),
        ];

        return array_merge_recursive($buttonData, $this->data);
    }

    /**
     * Get Login as Customer login url.
     *
     * @param int $customerId
     * @return string
     */
    private function getLoginUrl(int $customerId): string
    {
        return $this->urlBuilder->getUrl('loginascustomer/login/login', ['customer_id' => $customerId]);
    }
}
