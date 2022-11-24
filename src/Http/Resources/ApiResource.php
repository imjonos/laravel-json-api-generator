<?php

namespace Nos\JsonApiGenerator\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ApiResource
 * Base class for create JSON API resource
 * @package App\Http\Resources\Api\V1
 */
abstract class ApiResource extends JsonResource
{
    /**
     * @var string
     */
    private string $_resourceType = '';

    /**
     * @var array
     */
    private array $_resourceAttributes = [];

    /**
     * @var array
     */
    private array $_resourceLinks = [];

    /**
     * @var array
     */
    private array $_resourceRelationships = [];

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return $this->getDataArray();
    }

    /**
     * Create result data resource array
     * @return array
     */

    protected function getDataArray(): array
    {
        $data = [
            'type' => $this->_resourceType,
            'id' => (string) $this->id
        ];

        if (count($this->_resourceAttributes)) {
            $data['attributes'] = $this->_resourceAttributes;
        }
        if (count($this->_resourceLinks)) {
            $data['links'] = $this->_resourceLinks;
        }
        if (count($this->_resourceRelationships)) {
            $data['relationships'] = $this->_resourceRelationships;
        }

        return $data;
    }

    /**
     * Set type of resource
     * @param string $type
     */
    protected function setType(string $type = ''): void
    {
        $this->_resourceType = $type;
    }

    /**
     * Set attributes of resource
     * @param array $attributes
     */
    protected function setAttributes(array $attributes = []): void
    {
        $this->_resourceAttributes = $attributes;
    }

    /**
     * Set links of resource
     * @param array $links
     */
    protected function setLinks(array $links = []): void
    {
        $this->_resourceLinks = $links;
    }

    /**
     * Set relationships of resource
     * @param array $relationships
     */
    protected function setRelationships(array $relationships = []): void
    {
        $this->_resourceRelationships = $relationships;
    }
}
