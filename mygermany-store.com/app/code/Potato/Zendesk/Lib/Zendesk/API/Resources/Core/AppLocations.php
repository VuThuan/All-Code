<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Core;

use Potato\Zendesk\Lib\Zendesk\API\Resources\ResourceAbstract;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Find;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\FindAll;

/**
 * The AppLocations class exposes methods seen at
 * https://developer.zendesk.com/rest_api/docs/core/app_locations
 */
class AppLocations extends ResourceAbstract
{
    use Find;
    use FindAll;

    /**
     * {@inheritdoc}
     */
    protected $objectName = 'location';
    /**
     * {@inheritdoc}
     */
    protected $objectNamePlural = 'locations';

    /**
     * @var string
     */
    protected $resourceName = 'apps/locations';
}
