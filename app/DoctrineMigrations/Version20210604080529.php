<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\AdsPayedDate;
use SiteBundle\Repository\Adspayeddaterepository;
use SiteBundle\Repository\AdsRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210604080529 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $payedAds = $this->connection->fetchAllAssociative("
            SELECT a.id as adsId, a.Address as address, c.Name as cityName, ap.date as payedDate 
                FROM `ads_payed_date` AS ap
                INNER JOIN ads AS a ON ap.ads = a.id
                INNER JOIN city AS c ON a.cityId = c.id
        ");

        $now = new \DateTime();

        foreach ($payedAds as $payedAd) {
            $payedDate = new \DateTime($payedAd['payedDate']);
            $dateDiff = $payedDate->diff($now)->format('%R%a');
            if ($dateDiff < 0 ) {
                $location = $this->geocode($payedAd['address'].','.$payedAd['cityName'].', Montenegro');

                $this->connection->update(
                    'ads',
                    ['lat' => $location[0], 'lng' => $location[1]],
                    ['Id' => $payedAd['adsId']]
                );
            }
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    private function geocode($address)
    {
        $mapsApiKey = $this->container->getParameter('google_maps_key');
        // url encode the address
        $address = urlencode($address);

        // google map geocode api url
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key=AIzaSyCMICDan60V1VxLc0oGCNDvtbcbAjYFX_Q";

        // get the json response
        $resp_json = file_get_contents($url);

        // decode the json
        $resp = json_decode($resp_json, true);

        // response status will be 'OK', if able to geocode given address
        if($resp['status']=='OK'){

            // get the important data
            $lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
            $longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";
            $formatted_address = isset($resp['results'][0]['formatted_address']) ? $resp['results'][0]['formatted_address'] : "";

            // verify if data is complete
            if($lati && $longi && $formatted_address){

                // put the data in the array
                $data_arr = array();

                array_push(
                    $data_arr,
                    $lati,
                    $longi,
                    $formatted_address
                );

                return $data_arr;

            }else{
                return false;
            }

        } else {
            echo "<strong>ERROR: {$resp['status']}</strong>";
            return false;
        }
    }
}
