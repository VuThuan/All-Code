<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Chat;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Find;

/**
 * @method Integrations find()
 */
class Integrations extends ResourceAbstract
{
    use Find;

    /**
     * {@inheritdoc}
     */
    protected function setUpRoutes()
    {
        parent::setUpRoutes();

        $this->setRoutes(
            [
                'find' => 'zopim_integration.json',
            ]
        );
    }

    /**
     * Find the ChatAccount integrated to a Zendesk account
     *
     * @return \stdClass | null
     */
    public function find()
    {
        return $this->client->get($this->getRoute(__FUNCTION__));
    }
}
