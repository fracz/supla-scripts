<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace suplascripts\models\scene;

use Carbon\Carbon;
use suplascripts\models\HasSuplaApi;
use suplascripts\models\log\StateLogEntry;

class FeedbackTwigExtension extends \Twig_Extension {
    use HasSuplaApi;

    const URL_FETCH_TIMEOUT = 5;

    public function getFunctions() {
        return [
            new \Twig_Function('state', [$this, 'getChannelState'], ['needs_context' => true]),
            new \Twig_Function('history', [$this, 'getChannelHistory']),
            new \Twig_Function('getUrl', self::class . '::getUrlContents'),
            new \Twig_Function('time', [$this, 'getTime']),
            new \Twig_Function('timestamp', [$this, 'getTimestamp']),
            new \Twig_Function('sunriseTime', [$this, 'getSunriseTime']),
            new \Twig_Function('sunsetTime', [$this, 'getSunsetTime']),
        ];
    }

    public function getFilters() {
        return [
            new \Twig_Filter('colorName', [$this, 'getNearestColorName']),
            new \Twig_Filter('colorNamePl', [$this, 'getNearestColorNamePolish']),
            new \Twig_Filter('jsonDecode', [$this, 'jsonDecode']),
        ];
    }

    public function getChannelState(array $context, $channelId) {
        if ($context['noCache'] ?? false) {
            $this->getApi()->clearCache($channelId);
        }
        return $this->getApi()->getChannelState($channelId);
    }

    public function getChannelHistory($channelId, int $before = 0) {
        $before = $before ?: time();
        return $this->getApp()->getCurrentUser()->stateLogs()->getQuery()
            ->where([StateLogEntry::CHANNEL_ID => $channelId])
            ->where(StateLogEntry::CREATED_AT, '<=', Carbon::createFromTimestamp($before))
            ->orderByDesc(StateLogEntry::CREATED_AT)
            ->limit(20)
            ->get();
    }

    /**
     * @see https://stackoverflow.com/a/2994015/878514
     */
    public function getNearestColorName($color): string {
        $colors = [
            "black" => [0, 0, 0],
            "green" => [0, 128, 0],
            "silver" => [192, 192, 192],
            "lime" => [0, 255, 0],
            "gray" => [128, 0, 128],
            "olive" => [128, 128, 0],
            "white" => [255, 255, 255],
            "yellow" => [255, 255, 0],
            "maroon" => [128, 0, 0],
            "navy" => [0, 0, 128],
            "red" => [255, 0, 0],
            "orange" => [255, 165, 0],
            "blue" => [0, 0, 255],
            "purple" => [128, 0, 128],
            "teal" => [0, 128, 128],
            "fuchsia" => [255, 0, 255],
            "aqua" => [0, 255, 255],
        ];
        if (is_object($color) && property_exists($color, 'color')) {
            $color = $color->color;
        }
        $value = str_replace('0x', '#', $color);
        $distances = array();
        $val = $this->html2rgb($value);
        $nearestColor = '';
        if ($val) {
            foreach ($colors as $name => $c) {
                $color1 = $c;
                $color2 = $val;
                $distances[$name] = sqrt(pow($color1[0] - $color2[0], 2) + pow($color1[1] - $color2[1], 2) + pow($color1[2] - $color2[2], 2));
            }
            $minval = pow(2, 30); /*big value*/
            foreach ($distances as $k => $v) {
                if ($v < $minval) {
                    $minval = $v;
                    $nearestColor = $k;
                }
            }
        }
        return $nearestColor;
    }

    private function html2rgb($color): array {
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }
        if (strlen($color) == 6) {
            list($r, $g, $b) = array($color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]);
        } elseif (strlen($color) == 3) {
            list($r, $g, $b) = array($color[0] . $color[0],
                $color[1] . $color[1], $color[2] . $color[2]);
        } else {
            return [];
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array($r, $g, $b);
    }

    public function getNearestColorNamePolish($color) {
        return [
            "black" => 'czarny',
            "green" => 'zielony',
            "silver" => 'jasny szary',
            "lime" => 'limonkowy',
            "gray" => 'szary',
            "olive" => 'oliwkowy',
            "white" => 'biały',
            "yellow" => 'żółty',
            "maroon" => 'kasztanowaty',
            "navy" => 'granatowy',
            "red" => 'czerwony',
            "orange" => 'pomarańczowy',
            "blue" => 'niebieski',
            "purple" => 'fioletowy',
            "teal" => 'turkusowy',
            "fuchsia" => 'różowy',
            "aqua" => 'morski',
        ][$this->getNearestColorName($color)];
    }

    public function jsonDecode($json) {
        return json_decode($json, true);
    }

    public function getTime($date = 'now') {
        return date('H:i', strtotime($date));
    }

    public function getTimestamp($date = 'now') {
        return strtotime($date);
    }

    public function getSunriseTime($latitude = null, $longitude = null, $date = 'now') {
        $location = $this->getLatitudeAndLongitude($latitude, $longitude);
        $timestamp = date_sunrise(strtotime($date), SUNFUNCS_RET_TIMESTAMP, $location['latitude'], $location['longitude']);
        $date = (new \DateTime('now'))->setTimestamp($timestamp);
        return $date->format('H:i');
    }

    public function getSunsetTime($latitude = null, $longitude = null, $date = 'now') {
        $location = $this->getLatitudeAndLongitude($latitude, $longitude);
        $timestamp = date_sunset(strtotime($date), SUNFUNCS_RET_TIMESTAMP, $location['latitude'], $location['longitude']);
        $date = (new \DateTime('now'))->setTimestamp($timestamp);
        return $date->format('H:i');
    }

    private function getLatitudeAndLongitude($latitude, $longitude): array {
        $location = (new \DateTimeZone(date_default_timezone_get()))->getLocation();
        if ($latitude !== null) {
            $location['latitude'] = $latitude;
        }
        if ($longitude !== null) {
            $location['longitude'] = $longitude;
        }
        return $location;
    }

    public static function getUrlContents(string $url, $regex = '', array $config = []): string {
        if (is_array($regex)) {
            $config = $regex;
        }
        if ($regex && is_string($regex)) {
            $config['regex'] = $regex;
        }
        $key = \FileSystemCache::generateCacheKey(array_merge($config, [$url]), 'urls');
        $value = \FileSystemCache::retrieve($key);
        if ($value === false) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::URL_FETCH_TIMEOUT);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::URL_FETCH_TIMEOUT);
            if ($config['ignoreSslErrors'] ?? false) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }
            if (isset($config['headers']) && is_array($config['headers'])) {
                $headers = [];
                foreach ($config['headers'] as $headerName => $headerValue) {
                    $headers[] = "$headerName: $headerValue";
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
            $value = curl_exec($ch);
            curl_close($ch);
            if ($value === false) {
                $value = 'URL_FETCH_ERROR';
            }
            \FileSystemCache::store($key, $value, 60);
        }
        if (isset($config['regex']) && $config['regex'] && @preg_match($config['regex'], $value, $match)) {
            return $match[$config['regexGroup'] ?? 1];
        }
        return $value;
    }
}
