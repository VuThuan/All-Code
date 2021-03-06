<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Voice;

use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Defaults;

/**
 * Class PhoneNumbers
 * https://developer.zendesk.com/rest_api/docs/voice-api/voice
 */
class PhoneNumbers extends ResourceAbstract
{
    use Defaults;

    /**
     * @inheritdoc
     */
    protected function setUpRoutes()
    {
        $this->setRoute('search', "{$this->getResourceName()}/search.json");
    }

    /**
     * Search for available phone numbers.
     *
     * @param array $queryParams
     *
     * @return \stdClass
     * @throws \Potato\Zendesk\Lib\Zendesk\API\Exceptions\RouteException
     */
    public function search(array $queryParams = [])
    {
        return $this->client->get($this->getRoute(__FUNCTION__), $queryParams);
    }
}
