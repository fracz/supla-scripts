angular.module('supla-scripts').filter 'channelLabel', (deviceLabelFilter) ->
  (channel, withDevice = 'ifNoCaption') ->
    label = ''
    if (withDevice is 'ifNoCaption' and not channel?.caption) or withDevice is true
      label = deviceLabelFilter(channel?.device) + ' / '
    label + (channel?.caption or channel?.type?.name.substr(5))
