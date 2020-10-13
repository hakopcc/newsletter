<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\BrowseMapListing\Services;

use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class BrowseMapListingService
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function updateLocationRelated()
    {

        $manager = $this->container->get('doctrine')->getManager();
        $connection = $manager->getConnection();

        $queryCountries = 'UPDATE LocationRelated_1 SET LocationRelated_1.amount_listing = (SELECT COUNT(Listing.id) FROM Listing WHERE Listing.location_1 = LocationRelated_1.location_id)';
        $queryStates = 'UPDATE LocationRelated_3 SET LocationRelated_3.amount_listing = (SELECT COUNT(Listing.id) FROM Listing WHERE Listing.location_3 = LocationRelated_3.location_id)';

        $statement = $connection->prepare($queryCountries);
        $statement->execute();

        $statement = $connection->prepare($queryStates);
        $statement->execute();

    }

    public function insertLocationRelated($info = null, $levelLocation = 1)
    {
        $managerMain = $this->container->get('doctrine')->getManager('main');
        $connectionMain = $managerMain->getConnection();

        $connection = $this->container->get('doctrine.dbal.domain_connection');
        $params = $connection->getParams();

        $params['dbname'] = $info['domainDBName'];

        $connection->__construct(
            $params,
            $connection->getDriver(),
            $connection->getConfiguration(),
            $connection->getEventManager()
        );


        $connection->connect();

        $mapcode = $this->container->get('browsemaplisting.service')->getMapLocationsCode();

        switch ($levelLocation) {
            case 1:

                foreach ($mapcode['browsebymap_hash_world_mill'] as $key => $location) {

                    $statementLocation = $connectionMain->prepare('SELECT id FROM Location_1 WHERE name = :location_name');
                    $statementLocation->bindValue('location_name', $location);
                    $statementLocation->execute();

                    $hasLocation = $statementLocation->fetchAll();

                    if ($hasLocation) {
                        foreach ($hasLocation as $item => $object) {
                            $statement = $connection->prepare('SELECT location_id FROM LocationRelated_1 WHERE location_id = :location_id LIMIT 1');
                            $statement->bindValue('location_id', $object['id']);
                            $statement->execute();
                            $isInserted = $statement->fetch()['location_id'];

                            if (!$isInserted) {
                                $statement = $connection->prepare('INSERT INTO LocationRelated_1 (location_id,browsebymap_code,amount_listing) VALUES (:location_id, :browsebymap_code,(SELECT COUNT(Listing.id) FROM Listing WHERE Listing.location_1 = :location_id2))');
                                $statement->bindValue('location_id', $object['id']);
                                $statement->bindValue('browsebymap_code', $key);
                                $statement->bindValue('location_id2', $object['id']);
                                $statement->execute();
                            }
                        }
                    }
                }
                break;

            case 3:

                $browsebymapMap = $this->container->get('settings')->getDomainSetting('browsebymap_map');

                $forArray = [];
                if ($browsebymapMap) {
                    switch ($browsebymapMap) {
                        case 'au_mill':
                            $country = 'Australia';
                            $forArray = $mapcode['browsebymap_hash_au_mill'];
                            break;
                        case 'ca_lcc':
                            $country = 'Canada';
                            $forArray = $mapcode['browsebymap_hash_ca_lcc'];
                            break;
                        case 'de_mill':
                            $country = 'Germany';
                            $forArray = $mapcode['browsebymap_hash_de_mill'];
                            break;
                        case 'in_mill':
                            $country = 'India';
                            $forArray = $mapcode['browsebymap_hash_in_mill'];
                            break;
                        case 'br_map':
                            $country = 'Brasil';
                            $forArray = $mapcode['browsebymap_hash_br_map'];
                            break;
                        case 'us_aea':
                            $country = 'United States';
                            $forArray = $mapcode['browsebymap_hash_us_aea'];
                            break;
                        case 'fr_regions_2016_mill':
                            $country = 'France';
                            $forArray = $mapcode['browsebymap_hash_fr_regions_2016_mill'];
                            break;
                        case 'nz_mill':
                            $country = 'New Zealand';
                            $forArray = $mapcode['browsebymap_hash_nz_mill'];
                    }
                }

                foreach ($forArray as $key => $location) {

                    $statementLocation = $connectionMain->prepare('SELECT id FROM Location_3 WHERE name = :name AND location_1 = (SELECT id FROM Location_1 WHERE name = :name2)');
                    $statementLocation->bindValue('name', $location);
                    $statementLocation->bindValue('name2', $country);
                    $statementLocation->execute();

                    $hasLocation = $statementLocation->fetchAll();

                    if ($hasLocation) {
                        foreach ($hasLocation as $item => $object) {
                            $statement = $connection->prepare('SELECT location_id FROM LocationRelated_3 WHERE location_id = :location_id LIMIT 1');
                            $statement->bindValue('location_id', $object['id']);
                            $statement->execute();
                            $isInserted = $statement->fetch()['location_id'];
                            if (!$isInserted) {
                                $statement = $connection->prepare('INSERT INTO LocationRelated_3 (location_id,browsebymap_code, amount_listing) VALUES (:location_id, :browsebymap_code,(SELECT COUNT(Listing.id) FROM Listing WHERE Listing.location_3 = :location_id))');
                                $statement->bindValue('location_id', $object['id']);
                                $statement->bindValue('browsebymap_code', $key);
                                $statement->bindValue('location_id', $object['id']);
                                $statement->execute();
                            }
                        }
                    }
                }

                break;
        }
    }

    public function getMapLocationsCode()
    {
        $codes['browsebymap_hash_world_mill'] = [
            'BD' => 'Bangladesh',
            'BE' => 'Belgium',
            'BF' => 'Burkina Faso',
            'BG' => 'Bulgaria',
            'BA' => 'Bosnia and Herz.',
            'BN' => 'Brunei',
            'BO' => 'Bolivia',
            'JP' => 'Japan',
            'BI' => 'Burundi',
            'BJ' => 'Benin',
            'BT' => 'Bhutan',
            'JM' => 'Jamaica',
            'BW' => 'Botswana',
            'BR' => 'Brazil',
            'BS' => 'Bahamas',
            'BY' => 'Belarus',
            'BZ' => 'Belize',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'RS' => 'Serbia',
            'TL' => 'Timor-Leste',
            'TM' => 'Turkmenistan',
            'TJ' => 'Tajikistan',
            'RO' => 'Romania',
            'GW' => 'Guinea-Bissau',
            'GT' => 'Guatemala',
            'GR' => 'Greece',
            'GQ' => 'Eq. Guinea',
            'GY' => 'Guyana',
            'GE' => 'Georgia',
            'GB' => 'United Kingdom',
            'GA' => 'Gabon',
            'GN' => 'Guinea',
            'GM' => 'Gambia',
            'GL' => 'Greenland',
            'GH' => 'Ghana',
            'OM' => 'Oman',
            'TN' => 'Tunisia',
            'JO' => 'Jordan',
            'HR' => 'Croatia',
            'HT' => 'Haiti',
            'HU' => 'Hungary',
            'HN' => 'Honduras',
            'PR' => 'Puerto Rico',
            'PS' => 'Palestine',
            'PT' => 'Portugal',
            'PY' => 'Paraguay',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PE' => 'Peru',
            'PK' => 'Pakistan',
            'PH' => 'Philippines',
            'PL' => 'Poland',
            'ZM' => 'Zambia',
            'EH' => 'W. Sahara',
            'EE' => 'Estonia',
            'EG' => 'Egypt',
            'ZA' => 'South Africa',
            'EC' => 'Ecuador',
            'IT' => 'Italy',
            'VN' => 'Vietnam',
            'SB' => 'Solomon Is.',
            'ET' => 'Ethiopia',
            'SO' => 'Somalia',
            'ZW' => 'Zimbabwe',
            'ES' => 'Spain',
            'ER' => 'Eritrea',
            'ME' => 'Montenegro',
            'MD' => 'Moldova',
            'MG' => 'Madagascar',
            'MA' => 'Morocco',
            'UZ' => 'Uzbekistan',
            'MM' => 'Myanmar',
            'ML' => 'Mali',
            'MN' => 'Mongolia',
            'MK' => 'Macedonia',
            'MW' => 'Malawi',
            'MR' => 'Mauritania',
            'UG' => 'Uganda',
            'MY' => 'Malaysia',
            'MX' => 'Mexico',
            'IL' => 'Israel',
            'FR' => 'France',
            'XS' => 'Somaliland',
            'FI' => 'Finland',
            'FJ' => 'Fiji',
            'FK' => 'Falkland Is.',
            'NI' => 'Nicaragua',
            'NL' => 'Netherlands',
            'NO' => 'Norway',
            'NA' => 'Namibia',
            'VU' => 'Vanuatu',
            'NC' => 'New Caledonia',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NZ' => 'New Zealand',
            'NP' => 'Nepal',
            'XK' => 'Kosovo',
            'CI' => "Côte d'Ivoire",
            'CH' => 'Switzerland',
            'CO' => 'Colombia',
            'CN' => 'China',
            'CM' => 'Cameroon',
            'CL' => 'Chile',
            'XC' => 'N. Cyprus',
            'CA' => 'Canada',
            'CG' => 'Congo',
            'CF' => 'Central African Rep.',
            'CD' => 'Dem. Rep. Congo',
            'CZ' => 'Czech Rep.',
            'CY' => 'Cyprus',
            'CR' => 'Costa Rica',
            'CU' => 'Cuba',
            'SZ' => 'Swaziland',
            'SY' => 'Syria',
            'KG' => 'Kyrgyzstan',
            'KE' => 'Kenya',
            'SS' => 'S. Sudan',
            'SR' => 'Suriname',
            'KH' => 'Cambodia',
            'SV' => 'El Salvador',
            'SK' => 'Slovakia',
            'KR' => 'Korea',
            'SI' => 'Slovenia',
            'KP' => 'Dem. Rep. Korea',
            'KW' => 'Kuwait',
            'SN' => 'Senegal',
            'SL' => 'Sierra Leone',
            'KZ' => 'Kazakhstan',
            'SA' => 'Saudi Arabia',
            'SE' => 'Sweden',
            'SD' => 'Sudan',
            'DO' => 'Dominican Rep.',
            'DJ' => 'Djibouti',
            'DK' => 'Denmark',
            'DE' => 'Germany',
            'YE' => 'Yemen',
            'DZ' => 'Algeria',
            'US' => 'United States',
            'UY' => 'Uruguay',
            'LB' => 'Lebanon',
            'LA' => 'Lao PDR',
            'TW' => 'Taiwan',
            'TT' => 'Trinidad and Tobago',
            'TR' => 'Turkey',
            'LK' => 'Sri Lanka',
            'LV' => 'Latvia',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'LR' => 'Liberia',
            'LS' => 'Lesotho',
            'TH' => 'Thailand',
            'TF' => 'Fr. S. Antarctic Lands',
            'TG' => 'Togo',
            'TD' => 'Chad',
            'LY' => 'Libya',
            'AE' => 'United Arab Emirates',
            'VE' => 'Venezuela',
            'AF' => 'Afghanistan',
            'IQ' => 'Iraq',
            'IS' => 'Iceland',
            'IR' => 'Iran',
            'AM' => 'Armenia',
            'AL' => 'Albania',
            'AO' => 'Angola',
            'AR' => 'Argentina',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'IN' => 'India',
            'TZ' => 'Tanzania',
            'AZ' => 'Azerbaijan',
            'IE' => 'Ireland',
            'ID' => 'Indonesia',
            'UA' => 'Ukraine',
            'QA' => 'Qatar',
            'MZ' => 'Mozambique',
        ];

        // Australia
        $codes['browsebymap_hash_au_mill'] = [
            'AU-ACT' => 'Australian Capital Territory',
            'AU-WA'  => 'Western Australia',
            'AU-TAS' => 'Tasmania',
            'AU-'    => 'Jervis Bay Territory',
            'AU-VIC' => 'Victoria',
            'AU-NT'  => 'Northern Territory',
            'AU-QLD' => 'Queensland',
            'AU-SA'  => 'South Australia',
            'AU-NSW' => 'New South Wales',
        ];

        // Canada
        $codes['browsebymap_hash_ca_lcc'] = [
            'CA-NT' => 'Northwest Territories',
            'CA-NU' => 'Nunavut',
            'CA-NS' => 'Nova Scotia',
            'CA-BC' => 'British Columbia',
            'CA-SK' => 'Saskatchewan',
            'CA-QC' => 'Québec',
            'CA-PE' => 'Prince Edward Island',
            'CA-MB' => 'Manitoba',
            'CA-YT' => 'Yukon',
            'CA-NB' => 'New Brunswick',
            'CA-NL' => 'Newfoundland and Labrador',
            'CA-ON' => 'Ontario',
            'CA-AB' => 'Alberta',
        ];

        // France
        $codes['browsebymap_hash_fr_regions_2016_mill'] = [
            'FR-GF' => 'Guyane française',
            'FR-H'  => 'Corse',
            'FR-F'  => 'Centre',
            'FR-E'  => 'Bretagne',
            'FR-X1' => 'Bourgogne-Franche-Comté',
            'FR-MQ' => 'Martinique',
            'FR-YT' => 'Mayotte',
            'FR-X4' => 'Alsace-Champagne-Ardenne-Lorraine',
            'FR-X5' => 'Languedoc-Roussillon-Midi-Pyrénées',
            'FR-X6' => 'Nord-Pas-de-Calais-Picardie',
            'FR-X7' => 'Auvergne-Rhône-Alpes',
            'FR-X3' => 'Normandy',
            'FR-R'  => 'Pays de la Loire',
            'FR-GP' => 'Guadeloupe',
            'FR-U'  => "Provence-Alpes-Côte-d'Azur",
            'FR-J'  => 'Île-de-France',
            'FR-X2' => 'Aquitaine-Limousin-Poitou-Charentes',
            'FR-RE' => 'Réunion',
        ];

        // Germany
        $codes['browsebymap_hash_de_mill'] = [
            'DE-BE' => 'Berlin',
            'DE-ST' => 'Sachsen-Anhalt',
            'DE-RP' => 'Rheinland-Pfalz',
            'DE-BB' => 'Brandenburg',
            'DE-NI' => 'Niedersachsen',
            'DE-MV' => 'Mecklenburg-Vorpommern',
            'DE-TH' => 'Thüringen',
            'DE-BW' => 'Baden-Württemberg',
            'DE-HH' => 'Hamburg',
            'DE-SH' => 'Schleswig-Holstein',
            'DE-NW' => 'Nordrhein-Westfalen',
            'DE-SN' => 'Sachsen',
            'DE-HB' => 'Bremen',
            'DE-SL' => 'Saarland',
            'DE-BY' => 'Bayern',
            'DE-HE' => 'Hessen',
        ];

        // India
        $codes['browsebymap_hash_in_mill'] = [
            'IN-BR' => 'Bihar',
            'IN-PY' => 'Puducherry',
            'IN-DD' => 'Daman and Diu',
            'IN-DN' => 'Dadra and Nagar Haveli',
            'IN-DL' => 'Delhi',
            'IN-NL' => 'Nagaland',
            'IN-WB' => 'West Bengal',
            'IN-HR' => 'Haryana',
            'IN-HP' => 'Himachal Pradesh',
            'IN-AS' => 'Assam',
            'IN-UT' => 'Uttaranchal',
            'IN-JH' => 'Jharkhand',
            'IN-JK' => 'Jammu and Kashmir',
            'IN-UP' => 'Uttar Pradesh',
            'IN-SK' => 'Sikkim',
            'IN-MZ' => 'Mizoram',
            'IN-CT' => 'Chhattisgarh',
            'IN-CH' => 'Chandigarh',
            'IN-GA' => 'Goa',
            'IN-GJ' => 'Gujarat',
            'IN-RJ' => 'Rajasthan',
            'IN-MP' => 'Madhya Pradesh',
            'IN-OR' => 'Orissa',
            'IN-TN' => 'Tamil Nadu',
            'IN-AN' => 'Andaman and Nicobar',
            'IN-AP' => 'Andhra Pradesh',
            'IN-TR' => 'Tripura',
            'IN-AR' => 'Arunachal Pradesh',
            'IN-KA' => 'Karnataka',
            'IN-PB' => 'Punjab',
            'IN-ML' => 'Meghalaya',
            'IN-MN' => 'Manipur',
            'IN-MH' => 'Maharashtra',
            'IN-KL' => 'Kerala',
        ];

        // USA
        $codes['browsebymap_hash_us_aea'] = [
            'US-VA' => 'Virginia',
            'US-PA' => 'Pennsylvania',
            'US-TN' => 'Tennessee',
            'US-WV' => 'West Virginia',
            'US-NV' => 'Nevada',
            'US-TX' => 'Texas',
            'US-NH' => 'New Hampshire',
            'US-NY' => 'New York',
            'US-HI' => 'Hawaii',
            'US-VT' => 'Vermont',
            'US-NM' => 'New Mexico',
            'US-NC' => 'North Carolina',
            'US-ND' => 'North Dakota',
            'US-NE' => 'Nebraska',
            'US-LA' => 'Louisiana',
            'US-SD' => 'South Dakota',
            'US-DC' => 'District of Columbia',
            'US-DE' => 'Delaware',
            'US-FL' => 'Florida',
            'US-CT' => 'Connecticut',
            'US-WA' => 'Washington',
            'US-KS' => 'Kansas',
            'US-WI' => 'Wisconsin',
            'US-OR' => 'Oregon',
            'US-KY' => 'Kentucky',
            'US-ME' => 'Maine',
            'US-OH' => 'Ohio',
            'US-OK' => 'Oklahoma',
            'US-ID' => 'Idaho',
            'US-WY' => 'Wyoming',
            'US-UT' => 'Utah',
            'US-IN' => 'Indiana',
            'US-IL' => 'Illinois',
            'US-AK' => 'Alaska',
            'US-NJ' => 'New Jersey',
            'US-CO' => 'Colorado',
            'US-MD' => 'Maryland',
            'US-MA' => 'Massachusetts',
            'US-AL' => 'Alabama',
            'US-MO' => 'Missouri',
            'US-MN' => 'Minnesota',
            'US-CA' => 'California',
            'US-IA' => 'Iowa',
            'US-MI' => 'Michigan',
            'US-GA' => 'Georgia',
            'US-AZ' => 'Arizona',
            'US-MT' => 'Montana',
            'US-MS' => 'Mississippi',
            'US-SC' => 'South Carolina',
            'US-RI' => 'Rhode Island',
            'US-AR' => 'Arkansas',
        ];

        // Brasil
        $codes['browsebymap_hash_br_map'] = [
            'ac' => 'Acre',
            'al' => 'Alagoas',
            'am' => 'Amazonas',
            'ap' => 'Amapa',
            'ba' => 'Bahia',
            'ce' => 'Ceara',
            'df' => 'Distrito Federal',
            'es' => 'Espirito Santo',
            'go' => 'Goias',
            'ma' => 'Maranhao',
            'mt' => 'Mato Grosso',
            'ms' => 'Mato Grosso do Sul',
            'mg' => 'Minas Gerais',
            'pa' => 'Para',
            'pb' => 'Paraiba',
            'pr' => 'Parana',
            'pe' => 'Pernambuco',
            'pi' => 'Piaui',
            'rj' => 'Rio de Janeiro',
            'rn' => 'Rio Grande do Norte',
            'ro' => 'Rondonia',
            'rs' => 'Rio Grande do Sul',
            'rr' => 'Roraima',
            'sc' => 'Santa Catarina',
            'se' => 'Sergipe',
            'sp' => 'Sao Paulo',
            'to' => 'Tocantins',
        ];

        // New Zealand
        $codes['browsebymap_hash_nz_mill'] = [
            'NZ-NSN' => 'Nelson City',
            'NZ-STL' => 'Southland',
            'NZ-HKB' => "Hawke's Bay",
            'NZ-BOP' => 'Bay of Plenty',
            'NZ-AUK' => 'Auckland',
            'NZ-TKI' => 'Taranaki',
            'NZ-MBH' => 'Marlborough District',
            'NZ-TAS' => 'Tasman District',
            'NZ-CIT' => 'Chatham Islands Territory',
            'NZ-WKO' => 'Waikato',
            'NZ-WTC' => 'West Coast',
            'NZ-OTA' => 'Otago',
            'NZ-'    => 'The Snares',
            'NZ-NTL' => 'Northland',
            'NZ-MWT' => 'Manawatu-Wanganui',
            'NZ-WGN' => 'Wellington',
            'NZ-GIS' => 'Gisborne District',
            'NZ-CAN' => 'Canterbury',
        ];

        return $codes;
    }

    public function generateDataJson()
    {
        $managerMain = $this->container->get('doctrine')->getManager('main');
        $connectionMain = $managerMain->getConnection();

        $domainSelected = $this->container->get('multi_domain.information')->getDatabase();

        if (in_array($this->container->get('settings')->getDomainSetting('browsebymap_map'),
            array_keys($this->getMapLocationOptions()['World']))) {
            $statement = $connectionMain->prepare('SELECT Location_1.friendly_url as location_1, Location_1.name as name, LocationRelated_1.browsebymap_code as browsebymap_code, LocationRelated_1.amount_listing as amount_listings FROM Location_1 INNER JOIN '.$domainSelected.".LocationRelated_1 ON (Location_1.id = LocationRelated_1.location_id) WHERE LocationRelated_1.browsebymap_code != ''");
        } else {
            $statement = $connectionMain->prepare('SELECT Location_3.friendly_url as location_3, Location_3.location_1 as location_1, Location_3.name as name, LocationRelated_3.browsebymap_code as browsebymap_code, LocationRelated_3.amount_listing as amount_listings FROM Location_3 LEFT JOIN '.$domainSelected.".LocationRelated_3 ON (Location_3.id = LocationRelated_3.location_id) WHERE LocationRelated_3.browsebymap_code != ''");
        }

        $statement->execute();
        $results = $statement->fetchAll();

        $data = [];
        $amount = [];
        $locations = [];

        foreach ($results as $row) {
            $locations[$row['browsebymap_code']] = [
                'name'       => $row['name'],
                'location_1' => $row['location_1'],
            ];
            $row['location_3'] and $locations[$row['browsebymap_code']]['location_3'] = $row['location_3'];
            $row['amount_listings'] and $amount[$row['browsebymap_code']] = $row['amount_listings'];
        }

        $amount and $data['amount'] = $amount;
        $locations and $data['locations'] = $locations;

        $baseFolder = $this->container->getParameter('kernel.root_dir').'/../web';
        $tmpFolder = $baseFolder.'/custom/domain_'.$this->container->get('multi_domain.information')->getId().'/tmp/';

        if (!is_dir($tmpFolder) && !mkdir($tmpFolder, 0777, true)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created',
                $tmpFolder));
        }

        $filename = $tmpFolder.'/jvectormap_data.json';
        if (file_exists($filename)) {
            unlink($filename);
        }

        $handle = fopen($filename, 'wb');
        fwrite($handle, json_encode($data));
        fclose($handle);
    }

    public function getMapLocationOptions()
    {
        return [
            'World'     => [
                'africa_mill'        => 'Africa',
                'asia_mill'          => 'Asia',
                'continents_mill'    => 'Continents',
                'europe_mill'        => 'Europe',
                'north_america_mill' => 'North America',
                'oceania_mill'       => 'Oceania',
                'south_america_mill' => 'South America',
                'world_mill'         => 'World',
            ],
            'Countries' => [
                'au_mill'              => 'Australia',
                'ca_lcc'               => 'Canada',
                'fr_regions_2016_mill' => 'France',
                'de_mill'              => 'Germany',
                'us_aea'               => 'United States',
                'br_map'               => 'Brasil',
                'in_mill'              => 'India',
                'nz_mill'              => 'New Zealand',
            ],
        ];
    }

    public function getHexColorDiff($hex, $diff)
    {
        $rgb = str_split(trim($hex, '# '), 2);
        foreach ($rgb as &$hex) {
            $dec = hexdec($hex);
            if ($diff >= 0) {
                $dec += $diff;
            } else {
                $dec -= abs($diff);
            }
            $dec = max(0, min(255, $dec));
            $hex = str_pad(dechex($dec), 2, '0', STR_PAD_LEFT);
        }

        return '#'.implode($rgb);
    }
}
