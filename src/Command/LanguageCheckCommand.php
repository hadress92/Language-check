<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LanguageCheckCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:language-check';

    protected function configure()
    {
        $this->setDescription('Check for Country Language.')
            ->addOption('country1',null, InputOption::VALUE_REQUIRED, 'country name?')
            ->addOption('country2',null, InputOption::VALUE_REQUIRED, 'country name?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $countries = [];
        if ($input->getOption('country1')){
            $countries[] = $input->getOption('country1');
        }
        if($input->getOption('country2')){
            $countries[] = $input->getOption('country2');
        }
        $this->checkCountryLanguages($countries);
    }

    function checkCountryLanguages(array $countries)
    {
        if (count($countries) <= 1) {
            $countryLanguageCodes = $this->returnCountryCodes($countries);
            $countriesSpeakingSameLanguage = $this->countriesSpeakSameLanguage($countries);
            foreach ($countryLanguageCodes as $code) {
                echo 'Country language code: ' . $code . "\n";
            }
            echo $countries[0] . " speaks same language with these countries: " . $countryNames = implode(", ", $countriesSpeakingSameLanguage);
        } else {
            $countrySimilarLanguages = $this->checkIfCountriesSpeakingSameLanguage($countries);
            if ($countrySimilarLanguages != 0) {
                echo $countries[0] . ' and ' . $countries[1] . ' speak the same language';
            } else {
                echo $countries[0] . ' and ' . $countries[1] . ' do not speak the same language';
            }
        }
    }

    function returnCountryCodes(array $countries)
    {
        $countryCodesArray = [];
        foreach ($countries as $arg) {
            $url = "https://restcountries.eu/rest/v2/name/" . $arg;
            $json = $this->CallAPI($url);
            $results = json_decode($json);
            if ($results){
            foreach ($results as $result) {
                $langCodes = $result->languages;
                foreach ($langCodes as $langCode) {
                    $countryCodesArray[] = reset($langCode);
                }
            }
            }
        }
        return $countryCodesArray;
    }

    function countriesSpeakSameLanguage($countries)
    {
        $countryNamesArray = [];
        $countryCodes = $this->returnCountryCodes($countries);
        foreach ($countryCodes as $code) {
            $url = 'https://restcountries.eu/rest/v2/lang/' . $code;
            $json = $this->CallAPI($url);
            $results = json_decode($json);
            foreach ($results as $result) {
                $countryNamesArray[] = $result->name;
            }
        }
        return $countryNamesArray;
    }

    function checkIfCountriesSpeakingSameLanguage($countries)
    {
        $firstLanguagesArray = [];
        $secondLanguagesArray = [];
        $countryCodes = $this->returnCountryCodes($countries);

        foreach ($countryCodes as $code) {
            $url = 'https://restcountries.eu/rest/v2/lang/' . $code;
            $json = $this->CallAPI($url);
            $results = json_decode($json);
            foreach ($results as $result) {
                if (strtoupper($result->name) == strtoupper($countries[0])) {
                    foreach ($result->languages as $langCode) {
                        $firstLanguagesArray[] = reset($langCode);
                    }
                } elseif (strtoupper($result->name) == strtoupper($countries[1])) {
                    foreach ($result->languages as $langCode) {
                        $secondLanguagesArray[] = reset($langCode);
                    }
                }
            }
        }

        return count(array_intersect($firstLanguagesArray, $secondLanguagesArray));
    }

    function callAPI($url)
    {
        $curl = curl_init();

        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
        );
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
