<?php


namespace SiteBundle\Services;


use Doctrine\ORM\EntityManager;
use SiteBundle\Entity\City;
use SiteBundle\Entity\Country;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class GoogleGeoCodeService extends ServiceContainer
{
    public function __construct(
        EntityManager $entity,
        TokenStorage $tokenStorage
    ) {
        parent::__construct($entity, $tokenStorage);
    }

    public function changeCityName(Country $country)
    {
        $cities = $this->em->getRepository('SiteBundle:City')->get();

        /** @var City $city */
        foreach ($cities as $city) {

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://maps.googleapis.com/maps/api/geocode/json?address=".$city->getName()."&language=HR&key=AIzaSyBBtP5n98gViFftGc15Pp8_rlQvDBMTdqU",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_CUSTOMREQUEST => "GET",
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                dump("cURL Error #:" . $err);
                die();
            }

            $response = json_decode($response, true);
            if ($response['status'] == 'OK') {
                $types = $response['results'][0]['address_components'][0]['types'];

                if(in_array('locality', $types)) {
                    $name = $response['results'][0]['address_components'][0]['long_name'];

                    $city->setName($name);

                    $this->em->persist($city);
                } else {
                    $this->em->remove($city);
                }
            } else {
                $this->em->remove($city);
            }
            $this->em->flush($city);
        }
    }
}