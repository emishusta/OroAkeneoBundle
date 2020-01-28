<?php

namespace Oro\Bundle\AkeneoBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroAkeneoMigration implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_7';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /* Tables generation */
        $this->updateOroIntegrationTransportTable($schema);
    }

    /**
     * Create oro_integration_transport table
     */
    protected function updateOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('akeneo_product_unit', 'string', ['notnull' => true, 'default' => 'each']);
        $table->addColumn('akeneo_product_unit_precision', 'integer', ['notnull' => true, 'default' => 0]);
    }
}
