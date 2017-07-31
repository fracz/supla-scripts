angular.module('supla-scripts').filter 'channelLabel', (deviceLabelFilter) ->
  (channel, withDevice = false) ->
    label = ''
    if withDevice
      label = deviceLabelFilter(channel?.device) + ' / '
    label + (channel?.caption or channel?.type?.name.substr(5))
