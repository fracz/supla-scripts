<?php

namespace suplascripts\models\voice;

use suplascripts\models\HasSuplaApi;

class FeedbackInterpolator
{
    use HasSuplaApi;

    public function interpolate(string $feedback)
    {
        return preg_replace_callback('#{{(\d+)\|(on|temperature|humidity)\|(bool|number):?(.+?)?}}#', function ($match) {
            $replacement = $this->replaceChannelState($match[1], $match[2], $match[3], explode(',', $match[4]));
            return $replacement ?: $match[0];
//            $variable = $match[1];
//            $resultIndex = isset($match[2]) && $match[2] ? $match[2] : 0;
//            $value = $results[$resultIndex]->{$variable};
//            if ($variable == 'on') {
//                $value = $value ? 'włączone' : 'wyłączone';
//            } else if ($variable == 'hi') {
//                $value = $value ? 'zamknięta' : 'otwarta';
//            } else if (floatval($value)) {
//                $value = number_format($value, 1, ',', '');
//            }
//            return $value;
        }, $feedback);
    }

    public function replaceChannelState($channelId, $field, $varType, $config)
    {
        $state = $this->getApi()->getChannelState($channelId);
        $desiredValue = $state->{$field};
        switch ($varType) {
            case 'bool':
                return $desiredValue ? ($config[0] ?? 'ON') : ($config[1] ?? 'OFF');
            case 'number':
                return number_format($desiredValue, $config[0] ?? 1);
        }
    }
}
