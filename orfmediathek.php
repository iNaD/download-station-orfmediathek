<?php

/**
 * @author Daniel Gehn <me@theinad.com>
 * @version 0.1a
 * @copyright 2015 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */

require_once 'provider.php';

class SynoFileHostingORF extends TheiNaDProvider {
    protected $LogPath = '/tmp/orf.log';

    protected static $qualityMap = array(
        'niedrig' => 1,
        'mittel' => 2,
        'hoch' => 3,
    );

    public function GetDownloadInfo() {
        // TODO: Implement URL parser
        $rawHtml = $this->curlRequest($this->Url);

        if($rawHtml == null) {
            return false;
        }

        $data = null;

        if(preg_match_all('#data-jsb="(.*?)"#si', $rawHtml, $matches) > 0) {
            foreach($matches[1] as $i => $match) {
                if($match != "") {
                    $json = json_decode(str_replace('&quot;', '"', $match));
                    if(isset($json->events)) {
                        if(isset($json->events[0]->values)) {
                            $data = $json->events[0]->values->segment->playlist_data;
                            break;
                        }
                    }
                }
            }
        }

        if($data === null) {
            return false;
        }

        $bestSource = null;
        $highestQuality = 0;

        foreach($data->sources as $source) {
            if($source->protocol == "http") {
                if($source->delivery == "progressive") {
                    if(isset(self::$qualityMap[$source->quality_string])) {
                        if (self::$qualityMap[$source->quality_string] > $highestQuality) {
                            $bestSource = $source->src;
                            $highestQuality = self::$qualityMap[$source->quality_string];
                        }
                    }
                }
            }
        }

        if($bestSource === null) {
            return false;
        }

        $title = ($data->title_prefix != "" ? $data->title_prefix . $data->title_separator : '') . $data->title;

        $url = trim($bestSource);

        $DownloadInfo = array();
        $DownloadInfo[DOWNLOAD_URL] = $url;
        $DownloadInfo[DOWNLOAD_FILENAME] = $this->buildFilename($url, $title);

        return $DownloadInfo;
    }

 }
?>
