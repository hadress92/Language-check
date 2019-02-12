<?php

class Language
{
    public $args;

    function __construct()
    {
        global $argv;
        $this->args = $argv;
        unset($this->args[0]);
    }

    function checkCountryLanguages()
    {
        if (count($this->args) <= 1) {
            $countryLanguageCodes = $this->returnCountryCodes();
            $countriesSpeakingSameLanguage = $this->countriesSpeakSameLanguage();
            foreach ($countryLanguageCodes as $code) {
                echo 'Country language code: ' . $code . "\n";
            }
            echo $this->args[1] . " speaks same language with these countries: " . $countryNames = implode(", ", $countriesSpeakingSameLanguage);
        } else {
            $countrySimilarLanguages = $this->checkIfCountriesSpeakingSameLanguage();
            if ($countrySimilarLanguages != 0) {
                echo $this->args[1] . ' and ' . $this->args[2] . ' speak the same language';
            } else {
                echo $this->args[1] . ' and ' . $this->args[2] . ' do not speak the same language';
            }
        }
    }

    function returnCountryCodes()
    {
        $countryCodesArray = [];
        foreach ($this->args as $arg) {
            $url = "https://restcountries.eu/rest/v2/name/" . $arg;
            $json = $this->CallAPI($url);
            $results = json_decode($json);
            foreach ($results as $result) {
                $langCodes = $result->languages;
                foreach ($langCodes as $langCode) {
                    $countryCodesArray[] = reset($langCode);
                }
            }
        }
        return $countryCodesArray;
    }

    function countriesSpeakSameLanguage()
    {
        $countryNamesArray = [];
        $countryCodes = $this->returnCountryCodes();
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

    function checkIfCountriesSpeakingSameLanguage()
    {
        $firstLanguagesArray = [];
        $secondLanguagesArray = [];
        $countryCodes = $this->returnCountryCodes();

        foreach ($countryCodes as $code) {
            $url = 'https://restcountries.eu/rest/v2/lang/' . $code;
            $json = $this->CallAPI($url);
            $results = json_decode($json);
            foreach ($results as $result) {
                if (strtoupper($result->name) == strtoupper($this->args[1])) {
                    foreach ($result->languages as $langCode) {
                        $firstLanguagesArray[] = reset($langCode);
                    }
                } elseif (strtoupper($result->name) == strtoupper($this->args[2])) {
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

class LanguageFactory
{
    public static function create()
    {
        return new Language();
    }
}

$language = LanguageFactory::create();
$language->checkCountryLanguages();
?>