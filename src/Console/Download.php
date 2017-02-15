<?php

namespace MichaelDrennen\Geonames\Console;

use Curl\Curl;
use Illuminate\Console\Command;

class Download extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "This command downloads the files you want from geonames.org and saves them locally.";

    protected $curl;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Curl $curl) {
        parent::__construct();
        $this->curl = $curl;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        //
        $this->line("Starting " . $this->signature);

        $remoteFilePaths = $this->getRemoteFilePathsToDownloadForGeonamesTable();

        $downloadedData = [];

        foreach ($remoteFilePaths as $remoteFilePath) {
            $this->curl->get($remoteFilePath);

            if ($this->curl->error) {
                echo $this->curl->error_code;
            } else {
                $downloadedData[] = $this->curl->response;
            }
        }


        $this->line("Finished " . $this->signature);
    }

    /**
     * @return array
     */
    protected function getRemoteFilePathsToDownloadForGeonamesTable() {
        $download_base_url = config('geonames.download_base_url');
        $countries = config('geonames.countries');

        // Comment this code out. Only necessary if I start letting users add to the config list in an exclusionary
        // manner. For example, "Pull all country files, BUT these." So in the countries array, you would find a * and
        // a number of country codes to exclude.
        //        if( sizeof($countries) == 1 && $countries[0] == '*' ){
        //            return [$download_base_url . 'allCountries.zip'];
        //        }

        // If the config setting for countries has the wildcard symbol "*", then the user wants data for all countries.
        if (array_search('*', $countries)) {
            return [$download_base_url . 'allCountries.zip'];
        }

        //
        $files = [];
        foreach ($countries as $country) {
            $files[] = $download_base_url . $country . '.zip';
        }
        return $files;
    }
}