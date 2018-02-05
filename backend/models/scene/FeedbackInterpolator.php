<?php

namespace suplascripts\models\scene;

use suplascripts\models\HasSuplaApi;

class FeedbackInterpolator {
    use HasSuplaApi;

    public function interpolate(string $feedback) {
        return preg_replace_callback('#{{(\d+)\|(on|temperature|humidity|hi)\|(bool|number):?(.+?)?}}#', function ($match) {
            $replacement = $this->replaceChannelState($match[1], $match[2], $match[3], explode(',', $match[4]));
            return $replacement ?: $match[0];
        }, $feedback);
    }

    public function replaceChannelState($channelId, $field, $varType, $config) {
        $state = $this->getApi()->getChannelState($channelId);
        $desiredValue = $state->{$field};
        if (!$state->connected) {
            return ' URZĄDZENIE ROZŁĄCZONE ';
        }
        switch ($varType) {
            case 'bool':
                return $desiredValue ? ($config[0] ?? '1') : ($config[1] ?? '0');
            case 'number':
                return number_format($desiredValue, intval($config[0] ?? 1));
        }
    }
}
