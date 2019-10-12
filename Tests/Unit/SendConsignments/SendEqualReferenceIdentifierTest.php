<?php declare(strict_types=1);

namespace MyParcelNL\Sdk\Tests\Unit\SendConsignments;

use DateTime;
use Exception;
use MyParcelNL\Sdk\src\Exception\ApiException;
use MyParcelNL\Sdk\src\Exception\MissingFieldException;
use MyParcelNL\Sdk\src\Helper\MyParcelCollection;
use MyParcelNL\Sdk\src\Factory\ConsignmentFactory;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;
use PHPUnit\Framework\TestCase;

class SendEqualReferenceIdentifierTest extends TestCase
{
    /**
     * @var int
     */
    private $timestamp;

    public function setUp(): void
    {
        $this->timestamp = (new DateTime())->getTimestamp();
    }

    /**
     * Test one shipment with createConcepts()
     *
     * @param array $consignmentTest
     *
     * @throws ApiException
     * @throws MissingFieldException
     * @throws Exception
     *
     * @dataProvider additionProvider()
     */
    public function testSendOneConsignment(array $consignmentTest): void
    {
        if (getenv('API_KEY') == null) {
            echo "\033[31m Set MyParcel API-key in 'Environment variables' before running UnitTest. Example: API_KEY=f8912fb260639db3b1ceaef2730a4b0643ff0c31. PhpStorm example: http://take.ms/sgpgU5\n\033[0m";

            return;
        }

        $myParcelCollection = new MyParcelCollection();

        $consignment = (ConsignmentFactory::createByCarrierId($consignmentTest['carrier_id']))
            ->setApiKey($consignmentTest['api_key'])
            ->setReferenceId($consignmentTest['reference_identifier'])
            ->setCountry($consignmentTest['cc'])
            ->setPerson($consignmentTest['person'])
            ->setCompany($consignmentTest['company'])
            ->setFullStreet($consignmentTest['full_street'])
            ->setPostalCode($consignmentTest['postal_code'])
            ->setCity($consignmentTest['city'])
            ->setEmail('your_email@test.nl')
            ->setPhone($consignmentTest['phone']);

        $myParcelCollection->addConsignment($consignment);

        /**
         * Create concept
         */
        $myParcelCollection->createConcepts()->setLatestData()->first();

        $savedCollection = MyParcelCollection::findByReferenceId($consignmentTest['reference_identifier'], $consignmentTest['api_key']);

        $savedCollection->setLatestData();

        $consignmentTest   = $this->additionProvider()[0];
        $savedConsignments = $savedCollection->getConsignmentsByReferenceId($consignmentTest['reference_identifier']);
        $this->assertCount(2, $savedConsignments);
    }

    /**
     * Data for the test
     *
     * @return array
     */
    public function additionProvider()
    {
        return [
            'normal_consignment' => [
                [
                    'api_key'              => getenv('API_KEY'),
                    'carrier_id'           => PostNLConsignment::CARRIER_ID,
                    'reference_identifier' => (string) $this->timestamp . '_input',
                    'cc'                   => 'NL',
                    'person'               => 'Reindert',
                    'company'              => 'Big Sale BV',
                    'full_street_input'    => 'Plein 1940-45 3b',
                    'full_street'          => 'Plein 1940-45 3 b',
                    'street'               => 'Plein 1940-45',
                    'number'               => 3,
                    'number_suffix'        => 'b',
                    'postal_code'          => '2231JE',
                    'city'                 => 'Rijnsburg',
                    'phone'                => '123456',
                ],
            ],
        ];
    }
}