<?php

namespace Potato\Zendesk\Lib\Zendesk\API\Resources\Embeddable;

use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Create;
use Potato\Zendesk\Lib\Zendesk\API\Traits\Resource\Update;

/**
 * Class ConfigSets
 * Requires web_widget:write scoped oauth token
 */
class ConfigSets extends ResourceAbstract
{
    use Create;
    use Update;

    /**
     * {@inheritdoc}
     */
    protected $objectName = 'config_set';

    /**
     * {@inheritdoc}
     */
    protected $objectNamePlural = 'config_sets';
}
