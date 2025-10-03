<?php

namespace SiteBundle\Controller;

use SiteBundle\Helper\ArrayHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteController extends AbstractController
{
    protected $entity;
    protected $serializerFormat = "json";
    protected $response;
    protected $jsonResponse;
    protected $mainEntity;

    /**
     * Convert Request content to array
     * @param Request $request
     *
     * @return mixed
     * @throws \LogicException
     */
    public function requestToArray(Request $request)
    {
        $data = [];
        switch($request->getMethod()){
            case $request::METHOD_GET:
                $data = $request->query->all();
                break;
            case $request::METHOD_POST:
            case $request::METHOD_PUT:
                $data = $request->request->all();
                break;
            default:
                $data = $this->jsonStringToArray($request->getContent());
                break;
        }

        return $data;
    }

    /**
     * @param array $data
     * @param array $excludedProps
     */
    public function emptyValueSetToNull(array &$data, array $excludedProps = []): void
    {
        foreach ($data as $prop => &$item) {
            if (empty($item) && false === in_array($prop, $excludedProps)) {
                $item = null;
            }
        };
    }

    /**
     * @param $data
     *
     * @return JsonResponse
     */
    public function outputJson($data)
    {
        return $this->jsonResponse->setData($data);
    }

    /**
     * @param $data
     *
     * @return mixed|string
     */
    protected function objToArray($data)
    {
        /** @var ArrayHelper $array_helper */
        $array_helper = $this->get('app.array_helper');

        return $array_helper->objToArray($data);
    }

    /**
     * @param      $jsonString
     * @param bool $toArray
     *
     * @return array|mixed
     */
    protected function jsonStringToArray($jsonString , $toArray = true)
    {
        $tmp = [];
        if(is_array($jsonString)){
            foreach ($jsonString as $key => $arr){
                $tmp[$key] = $this->jsonStringToArray($arr, $toArray);
            }
        } else {
            $tmp = ($this->isJsonStr($jsonString)) ?  json_decode($jsonString, $toArray) : $jsonString;
        }
        return $tmp;
    }

    /**
     * overwrite default serialization format
     * @param $format
     */
    protected function setSerializerFormat($format)
    {
        $this->serializerFormat = $format;
    }

    private function isJsonStr($jsonStr)
    {
        json_decode($jsonStr);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
