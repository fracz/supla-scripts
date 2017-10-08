angular.module('supla-scripts').filter 'actionLabel', ->
  (action, channel) ->
    functionName = channel?.function?.name
    switch action
      when 'turnOn' then 'włącz'
      when 'turnOff' then 'wyłącz'
      when 'toggle'
        if functionName in ['FNC_CONTROLLINGTHEGATE', 'FNC_CONTROLLINGTHEGARAGEDOOR']
          'otwórz/zamknij'
        else if functionName in ['FNC_CONTROLLINGTHEGATEWAYLOCK', 'FNC_CONTROLLINGTHEDOORLOCK']
          'otwórz'
        else
          'przełącz'
      else
        '??'
