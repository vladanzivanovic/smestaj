<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: vlada
 * Date: 6/26/2017
 * Time: 2:45 PM
 */

namespace SiteBundle\Helper;



use JMS\Serializer\SerializerInterface;

final class ArrayHelper
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializerBundle)
    {
        $this->serializer = $serializerBundle;
    }

    /**
     * Convert Array of Object or single Object to json format
     * @param mixed $data
     * @return mixed|string
     */
    public function objToArray($data)
    {
        return $this->serializer->toArray($data);
    }

    public function randomizeArray(array $data, $maxIteration = 0)
    {
        shuffle($data);

        if ($maxIteration > 0) {
            $data = array_slice($data, 0, $maxIteration);
        }

        return $data;
    }
}
