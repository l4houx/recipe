<?php

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class AppCountryFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['countries'];
    }

    public function load(ObjectManager $manager): void
    {
        $countries_en = [
            'BD' => 'Bangladesh',
            'BE' => 'Belgium',
            'BF' => 'Burkina Faso',
            'BG' => 'Bulgaria',
            'BA' => 'Bosnia and Herzegovina',
            'BB' => 'Barbados',
            'WF' => 'Wallis and Futuna',
            'BL' => 'Saint Barthelemy',
            'BM' => 'Bermuda',
            'BN' => 'Brunei',
            'BO' => 'Bolivia',
            'BH' => 'Bahrain',
            'BI' => 'Burundi',
            'BJ' => 'Benin',
            'BT' => 'Bhutan',
            'JM' => 'Jamaica',
            'BV' => 'Bouvet Island',
            'BW' => 'Botswana',
            'WS' => 'Samoa',
            'BQ' => 'Bonaire, Saint Eustatius and Saba',
            'BR' => 'Brazil',
            'BS' => 'Bahamas',
            'JE' => 'Jersey',
            'BY' => 'Belarus',
            'BZ' => 'Belize',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'RS' => 'Serbia',
            'TL' => 'East Timor',
            'RE' => 'Reunion',
            'TM' => 'Turkmenistan',
            'TJ' => 'Tajikistan',
            'RO' => 'Romania',
            'TK' => 'Tokelau',
            'GW' => 'Guinea-Bissau',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'GR' => 'Greece',
            'GQ' => 'Equatorial Guinea',
            'GP' => 'Guadeloupe',
            'JP' => 'Japan',
            'GY' => 'Guyana',
            'GG' => 'Guernsey',
            'GF' => 'French Guiana',
            'GE' => 'Georgia',
            'GD' => 'Grenada',
            'GB' => 'United Kingdom',
            'GA' => 'Gabon',
            'SV' => 'El Salvador',
            'GN' => 'Guinea',
            'GM' => 'Gambia',
            'GL' => 'Greenland',
            'GI' => 'Gibraltar',
            'GH' => 'Ghana',
            'OM' => 'Oman',
            'TN' => 'Tunisia',
            'JO' => 'Jordan',
            'HR' => 'Croatia',
            'HT' => 'Haiti',
            'HU' => 'Hungary',
            'HK' => 'Hong Kong',
            'HN' => 'Honduras',
            'HM' => 'Heard Island and McDonald Islands',
            'VE' => 'Venezuela',
            'PR' => 'Puerto Rico',
            'PS' => 'Palestinian Territory',
            'PW' => 'Palau',
            'PT' => 'Portugal',
            'SJ' => 'Svalbard and Jan Mayen',
            'PY' => 'Paraguay',
            'IQ' => 'Iraq',
            'PA' => 'Panama',
            'PF' => 'French Polynesia',
            'PG' => 'Papua New Guinea',
            'PE' => 'Peru',
            'PK' => 'Pakistan',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn',
            'PL' => 'Poland',
            'PM' => 'Saint Pierre and Miquelon',
            'ZM' => 'Zambia',
            'EH' => 'Western Sahara',
            'EE' => 'Estonia',
            'EG' => 'Egypt',
            'ZA' => 'South Africa',
            'EC' => 'Ecuador',
            'IT' => 'Italy',
            'VN' => 'Vietnam',
            'SB' => 'Solomon Islands',
            'ET' => 'Ethiopia',
            'SO' => 'Somalia',
            'ZW' => 'Zimbabwe',
            'SA' => 'Saudi Arabia',
            'ES' => 'Spain',
            'ER' => 'Eritrea',
            'ME' => 'Montenegro',
            'MD' => 'Moldova',
            'MG' => 'Madagascar',
            'MF' => 'Saint Martin',
            'MA' => 'Morocco',
            'MC' => 'Monaco',
            'UZ' => 'Uzbekistan',
            'MM' => 'Myanmar',
            'ML' => 'Mali',
            'MO' => 'Macao',
            'MN' => 'Mongolia',
            'MH' => 'Marshall Islands',
            'MK' => 'Macedonia',
            'MU' => 'Mauritius',
            'MT' => 'Malta',
            'MW' => 'Malawi',
            'MV' => 'Maldives',
            'MQ' => 'Martinique',
            'MP' => 'Northern Mariana Islands',
            'MS' => 'Montserrat',
            'MR' => 'Mauritania',
            'IM' => 'Isle of Man',
            'UG' => 'Uganda',
            'TZ' => 'Tanzania',
            'MY' => 'Malaysia',
            'MX' => 'Mexico',
            'FR' => 'France',
            'IO' => 'British Indian Ocean Territory',
            'SH' => 'Saint Helena',
            'FI' => 'Finland',
            'FJ' => 'Fiji',
            'FK' => 'Falkland Islands',
            'FM' => 'Micronesia',
            'FO' => 'Faroe Islands',
            'NI' => 'Nicaragua',
            'NL' => 'Netherlands',
            'NO' => 'Norway',
            'NA' => 'Namibia',
            'VU' => 'Vanuatu',
            'NC' => 'New Caledonia',
            'NE' => 'Niger',
            'NF' => 'Norfolk Island',
            'NG' => 'Nigeria',
            'NZ' => 'New Zealand',
            'NP' => 'Nepal',
            'NR' => 'Nauru',
            'NU' => 'Niue',
            'CK' => 'Cook Islands',
            'XK' => 'Kosovo',
            'CI' => 'Ivory Coast',
            'CH' => 'Switzerland',
            'CO' => 'Colombia',
            'CN' => 'China',
            'CM' => 'Cameroon',
            'CL' => 'Chile',
            'CC' => 'Cocos Islands',
            'CA' => 'Canada',
            'CG' => 'Republic of the Congo',
            'CF' => 'Central African Republic',
            'CD' => 'Democratic Republic of the Congo',
            'CZ' => 'Czech Republic',
            'CY' => 'Cyprus',
            'CX' => 'Christmas Island',
            'CR' => 'Costa Rica',
            'CW' => 'Curacao',
            'CV' => 'Cape Verde',
            'CU' => 'Cuba',
            'SZ' => 'Swaziland',
            'SY' => 'Syria',
            'SX' => 'Sint Maarten',
            'KG' => 'Kyrgyzstan',
            'KE' => 'Kenya',
            'SS' => 'South Sudan',
            'SR' => 'Suriname',
            'KI' => 'Kiribati',
            'KH' => 'Cambodia',
            'KN' => 'Saint Kitts and Nevis',
            'KM' => 'Comoros',
            'ST' => 'Sao Tome and Principe',
            'SK' => 'Slovakia',
            'KR' => 'South Korea',
            'SI' => 'Slovenia',
            'KP' => 'North Korea',
            'KW' => 'Kuwait',
            'SN' => 'Senegal',
            'SM' => 'San Marino',
            'SL' => 'Sierra Leone',
            'SC' => 'Seychelles',
            'KZ' => 'Kazakhstan',
            'KY' => 'Cayman Islands',
            'SG' => 'Singapore',
            'SE' => 'Sweden',
            'SD' => 'Sudan',
            'DO' => 'Dominican Republic',
            'DM' => 'Dominica',
            'DJ' => 'Djibouti',
            'DK' => 'Denmark',
            'VG' => 'British Virgin Islands',
            'DE' => 'Germany',
            'YE' => 'Yemen',
            'DZ' => 'Algeria',
            'US' => 'United States',
            'UY' => 'Uruguay',
            'YT' => 'Mayotte',
            'UM' => 'United States Minor Outlying Islands',
            'LB' => 'Lebanon',
            'LC' => 'Saint Lucia',
            'LA' => 'Laos',
            'TV' => 'Tuvalu',
            'TW' => 'Taiwan',
            'TT' => 'Trinidad and Tobago',
            'TR' => 'Turkey',
            'LK' => 'Sri Lanka',
            'LI' => 'Liechtenstein',
            'LV' => 'Latvia',
            'TO' => 'Tonga',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'LR' => 'Liberia',
            'LS' => 'Lesotho',
            'TH' => 'Thailand',
            'TF' => 'French Southern Territories',
            'TG' => 'Togo',
            'TD' => 'Chad',
            'TC' => 'Turks and Caicos Islands',
            'LY' => 'Libya',
            'VA' => 'Vatican',
            'VC' => 'Saint Vincent and the Grenadines',
            'AE' => 'United Arab Emirates',
            'AD' => 'Andorra',
            'AG' => 'Antigua and Barbuda',
            'AF' => 'Afghanistan',
            'AI' => 'Anguilla',
            'VI' => 'U.S. Virgin Islands',
            'IS' => 'Iceland',
            'IR' => 'Iran',
            'AM' => 'Armenia',
            'AL' => 'Albania',
            'AO' => 'Angola',
            'AQ' => 'Antarctica',
            'AS' => 'American Samoa',
            'AR' => 'Argentina',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AW' => 'Aruba',
            'IN' => 'India',
            'AX' => 'Aland Islands',
            'AZ' => 'Azerbaijan',
            'IE' => 'Ireland',
            'ID' => 'Indonesia',
            'UA' => 'Ukraine',
            'QA' => 'Qatar',
            'MZ' => 'Mozambique',
        ];

        $countries_fr = [
            'BD' => 'Bangladesh',
            'BE' => 'Belgique',
            'BF' => 'Burkina Faso',
            'BG' => 'Bulgarie',
            'BA' => 'Bosnie Herzégovine',
            'BB' => 'La Barbade',
            'WF' => 'Wallis et Futuna',
            'BL' => 'Saint Barthélemy',
            'BM' => 'Bermudes',
            'BN' => 'Brunei',
            'BO' => 'Bolivie',
            'BH' => 'Bahreïn',
            'BI' => 'Burundi',
            'BJ' => 'Bénin',
            'BT' => 'Bhoutan',
            'JM' => 'Jamaïque',
            'BV' => 'Île Bouvet',
            'BW' => 'Botswana',
            'WS' => 'Samoa',
            'BQ' => 'Bonaire, Saint Eustache et Saba',
            'BR' => 'Brésil',
            'BS' => 'Bahamas',
            'JE' => 'Jersey',
            'BY' => 'Biélorussie',
            'BZ' => 'Belize',
            'RU' => 'Russie',
            'RW' => 'Rwanda',
            'RS' => 'Serbie',
            'TL' => 'Timor oriental',
            'RE' => 'Réunion',
            'TM' => 'Turkménistan',
            'TJ' => 'Tadjikistan',
            'RO' => 'Roumanie',
            'TK' => 'Tokelau',
            'GW' => 'Guinée-Bissau',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GS' => 'Géorgie du Sud et les îles Sandwich du Sud',
            'GR' => 'Grèce',
            'GQ' => 'Guinée Équatoriale',
            'GP' => 'La guadeloupe',
            'JP' => 'Japon',
            'GY' => 'Guyane',
            'GG' => 'Guernesey',
            'GF' => 'Guinée Française',
            'GE' => 'Géorgie',
            'GD' => 'Grenade',
            'GB' => 'Royaume-Uni',
            'GA' => 'Gabon',
            'SV' => 'Le Salvador',
            'GN' => 'Guinée',
            'GM' => 'Gambie',
            'GL' => 'Groenland',
            'GI' => 'Gibraltar',
            'GH' => 'Ghana',
            'OM' => 'Oman',
            'TN' => 'Tunisie',
            'JO' => 'Jordan',
            'HR' => 'Croatie',
            'HT' => 'Haïti',
            'HU' => 'Hongrie',
            'HK' => 'Hong Kong',
            'HN' => 'Honduras',
            'HM' => 'Îles Heard et McDonald',
            'VE' => 'Venezuela',
            'PR' => 'Porto Rico',
            'PS' => 'Territoire Palestinien',
            'PW' => 'Palau',
            'PT' => 'Portugal',
            'SJ' => 'Svalbard et Jan Mayen',
            'PY' => 'Paraguay',
            'IQ' => 'Irak',
            'PA' => 'Panama',
            'PF' => 'Polynésie française',
            'PG' => 'Papouasie Nouvelle Guinée',
            'PE' => 'Pérou',
            'PK' => 'Pakistan',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn',
            'PL' => 'Pologne',
            'PM' => 'Saint Pierre et Miquelon',
            'ZM' => 'Zambie',
            'EH' => 'Sahara occidental',
            'EE' => 'Estonie',
            'EG' => 'Egypte',
            'ZA' => 'Afrique du Sud',
            'EC' => 'L\'Équateur',
            'IT' => 'Italie',
            'VN' => 'Vietnam',
            'SB' => 'Les îles Salomon',
            'ET' => 'Ethiopie',
            'SO' => 'Somalie',
            'ZW' => 'Zimbabwe',
            'SA' => 'Arabie Saoudite',
            'ES' => 'Espagne',
            'ER' => 'Erythrée',
            'ME' => 'Monténégro',
            'MD' => 'La Moldavie',
            'MG' => 'Madagascar',
            'MF' => 'Saint Martin',
            'MA' => 'Maroc',
            'MC' => 'Monaco',
            'UZ' => 'Ouzbékistan',
            'MM' => 'Myanmar',
            'ML' => 'Mali',
            'MO' => 'Macao',
            'MN' => 'Mongolie',
            'MH' => 'Iles Marshall',
            'MK' => 'Macedonia',
            'MU' => 'Maurice',
            'MT' => 'Malte',
            'MW' => 'Malawi',
            'MV' => 'Maldives',
            'MQ' => 'Martinique',
            'MP' => 'Northern Mariana Islands',
            'MS' => 'Montserrat',
            'MR' => 'Mauritanie',
            'IM' => 'Ile de Man',
            'UG' => 'Ouganda',
            'TZ' => 'Tanzanie',
            'MY' => 'Malaisie',
            'MX' => 'Mexique',
            'FR' => 'France',
            'IO' => 'Territoire britannique de l\'océan Indien',
            'SH' => 'Sainte Hélène',
            'FI' => 'Finlande',
            'FJ' => 'Fidji',
            'FK' => 'les îles Falkland',
            'FM' => 'Micronésie',
            'FO' => 'Îles Féroé',
            'NI' => 'Nicaragua',
            'NL' => 'Pays-Bas',
            'NO' => 'Norvège',
            'NA' => 'Namibie',
            'VU' => 'Vanuatu',
            'NC' => 'Nouvelle Calédonie',
            'NE' => 'Niger',
            'NF' => 'l\'ile de Norfolk',
            'NG' => 'Nigeria',
            'NZ' => 'Nouvelle-Zélande',
            'NP' => 'Népal',
            'NR' => 'Nauru',
            'NU' => 'Niue',
            'CK' => 'les Îles Cook',
            'XK' => 'Kosovo',
            'CI' => 'Côte d\'Ivoire',
            'CH' => 'Suisse',
            'CO' => 'Colombie',
            'CN' => 'Chine',
            'CM' => 'Cameroun',
            'CL' => 'Chili',
            'CC' => 'Îles Cocos',
            'CA' => 'Canada',
            'CG' => 'République du Congo',
            'CF' => 'République centrafricaine',
            'CD' => 'République Démocratique du Congo',
            'CZ' => 'République Tchèque',
            'CY' => 'Chypre',
            'CX' => 'L\'île de noël',
            'CR' => 'Costa Rica',
            'CW' => 'Curacao',
            'CV' => 'Cap-Vert',
            'CU' => 'Cuba',
            'SZ' => 'Swaziland',
            'SY' => 'Syria',
            'SX' => 'Sint Maarten',
            'KG' => 'Kirghizistan',
            'KE' => 'Kenya',
            'SS' => 'Soudan du sud',
            'SR' => 'Suriname',
            'KI' => 'Kiribati',
            'KH' => 'Cambodge',
            'KN' => 'Saint-Christophe-et-Niévès',
            'KM' => 'Comores',
            'ST' => 'Sao Tomé et Principe',
            'SK' => 'La slovaquie',
            'KR' => 'Corée du Sud',
            'SI' => 'La slovénie',
            'KP' => 'Corée du Nord',
            'KW' => 'Koweit',
            'SN' => 'Sénégal',
            'SM' => 'Saint Marin',
            'SL' => 'Sierra Leone',
            'SC' => 'les Seychelles',
            'KZ' => 'Le kazakhstan',
            'KY' => 'Îles Caïmans',
            'SG' => 'Singapour',
            'SE' => 'Suède',
            'SD' => 'Soudan',
            'DO' => 'République Dominicaine',
            'DM' => 'Dominique',
            'DJ' => 'Djibouti',
            'DK' => 'Danemark',
            'VG' => 'Îles Vierges britanniques',
            'DE' => 'Allemagne',
            'YE' => 'Yémen',
            'DZ' => 'Algérie',
            'US' => 'États Unis',
            'UY' => 'Uruguay',
            'YT' => 'Mayotte',
            'UM' => 'Îles mineures éloignées des États-Unis',
            'LB' => 'Liban',
            'LC' => 'Sainte-Lucie',
            'LA' => 'Laos',
            'TV' => 'Tuvalu',
            'TW' => 'Taïwan',
            'TT' => 'Trinité-et-Tobago',
            'TR' => 'la Turquie',
            'LK' => 'Sri Lanka',
            'LI' => 'Le Liechtenstein',
            'LV' => 'Lettonie',
            'TO' => 'Tonga',
            'LT' => 'Lituanie',
            'LU' => 'Luxembourg',
            'LR' => 'Libéria',
            'LS' => 'Lesotho',
            'TH' => 'Thaïlande',
            'TF' => 'Terres australes françaises',
            'TG' => 'Togo',
            'TD' => 'Le tchad',
            'TC' => 'îles Turques-et-Caïques',
            'LY' => 'Libye',
            'VA' => 'Vatican',
            'VC' => 'Saint-Vincent-et-les-Grenadines',
            'AE' => 'Emirats Arabes Unis',
            'AD' => 'Andorre',
            'AG' => 'Antigua-et-Barbuda',
            'AF' => 'L\'Afghanistan',
            'AI' => 'Anguilla',
            'VI' => 'Îles Vierges américaines',
            'IS' => 'Islande',
            'IR' => 'Iran',
            'AM' => 'Arménie',
            'AL' => 'Albanie',
            'AO' => 'Angola',
            'AQ' => 'Antarctique',
            'AS' => 'Samoa américaines',
            'AR' => 'Argentine',
            'AU' => 'Australie',
            'AT' => 'L\'Autriche',
            'AW' => 'Aruba',
            'IN' => 'Inde',
            'AX' => 'Iles Aland',
            'AZ' => 'Azerbaïdjan',
            'IE' => 'Irlande',
            'ID' => 'Indonésie',
            'UA' => 'Ukraine',
            'QA' => 'Qatar',
            'MZ' => 'Mozambique',
        ];

        foreach ($countries_en as $code => $countryname) {
            $country = new Country();
            $country->setCode($code);
            $country->setName($countryname);
            $manager->persist($country);
        }

        $manager->flush();
    }
}
