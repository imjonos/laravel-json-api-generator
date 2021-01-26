<?php

namespace Nos\JsonApiGenerator\Http\Controllers;

use Nos\JsonApiGenerator\Exceptions\UnprocessableEntityException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected array $toOne;
    protected array $toMany;

    /**
     * Per page
     * @var int
     */
    protected int $perPage = 10;

    /**
     * Set the array with Relationships
     *
     * 'user' => [
     *       'class' => User::class,
     *       'type' => 'users',
     *       'key' => 'user_id'
     * ],
     *
     * @param array $array
     */
    public function setToOneRelationships(array $array): void
    {
        $this->toOne = $array;
    }

    /**
     * Get the array with Relationships
     *
     * 'slots' => [
     *      'class' => Slot::class,
     *      'type' => 'slots'
     *  ],
     *
     * @return array
     */
    public function getToManyRelationships(): array
    {
        return $this->toMany;
    }

    /**
     * Get the array with Relationships
     *
     * 'user' => [
     *       'class' => User::class,
     *       'type' => 'users',
     *       'key' => 'user_id'
     * ],
     *
     */
    public function getToOneRelationships(): array
    {
        return $this->toOne;
    }

    /**
     * Set the array with Relationships
     *
     * 'slots' => [
     *      'class' => Slot::class,
     *      'type' => 'slots'
     *  ],
     *
     * @param array $array
     */
    public function setToManyRelationships(array $array): void
    {
        $this->toMany = $array;
    }

    /**
     * Get Validated Data from request
     *
     * @param FormRequest $request
     * @return array
     */
    private function _getValidatedData(FormRequest $request): array
    {
        $validated = $request->validated();
        return $validated['data'];
    }

    /**
     * Get validated data
     * @param FormRequest $request
     * @return array
     * @throws UnprocessableEntityException
     */
    protected function getDataWithRelationshipsToOne(FormRequest $request): array
    {
        $toOne = $this->getToOneRelationships();
        $data = $this->_getValidatedData($request);
        $storeData = (isset($data['attributes'])) ? $data['attributes'] : [];

        if (isset($data['relationships']) && is_array($data['relationships'])) {
            foreach ($data['relationships'] as $key => $relationship) {
                if (isset($toOne[$key])) {
                    if (isset($relationship['data']) && is_array($relationship['data'])) {
                        $relationshipToOne = $toOne[$key];
                        $relationshipData = $relationship['data'];
                        $id = (int)$relationshipData['id'];
                        if ($relationshipData['type'] !== $relationshipToOne['type']) {
                            throw new UnprocessableEntityException();
                        }
                        $collection = $relationshipToOne['class']::find($id);
                        if ($collection) {
                            $storeData[$relationshipToOne['key']] = $collection->id;
                        } else {
                            throw new UnprocessableEntityException();
                        }

                    } else {
                        throw new UnprocessableEntityException();
                    }
                }
            }
        }
        return $storeData;
    }

    /**
     * Get validated data
     * @param FormRequest $request
     * @return array
     * @throws UnprocessableEntityException
     */
    protected function getDataWithRelationshipsToMany(FormRequest $request): array
    {

        $toMany = $this->getToManyRelationships();

        $data = $this->_getValidatedData($request);
        $storeData = [];

        if (isset($data['relationships']) && is_array($data['relationships'])) {
            foreach ($data['relationships'] as $key => $relationship) {
                if (isset($toMany[$key])) {
                    if (isset($relationship['data']) && is_array($relationship['data'])) {
                        $relationshipToMany = $toMany[$key];
                        foreach ($relationship['data'] as $relationshipData) {
                            $id = (int)$relationshipData['id'];
                            if ($relationshipData['type'] !== $relationshipToMany['type']) {
                                throw new UnprocessableEntityException();
                            }
                            $collection = $relationshipToMany['class']::find($id);
                            if ($collection) {
                                $storeData[$key][] = $collection->id;
                            } else {
                                throw new UnprocessableEntityException();
                            }
                        }
                    } else {
                        throw new UnprocessableEntityException();
                    }
                }
            }
        }
        return $storeData;
    }
}
