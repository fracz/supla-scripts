angular.module('supla-scripts').component 'voiceFeedbackField',
  templateUrl: 'app/voice/form/voice-feedback-field.html'
  require:
    ngModel: 'ngModel'
  controller: (Channels, channelLabelFilter) ->

    CHANNEL_FEEDBACKS =
      FNC_LIGHTSWITCH: [{display: 'zaświeczone/zgaszone', suffix: 'on|bool:zaświecone,zgaszone'}]
      FNC_POWERSWITCH: [{display: 'włączone/wyłączone', suffix: 'on|bool:włączone,wyłączone'}]
      FNC_THERMOMETER: [{display: 'temperatura', suffix: 'temperature|number:1'}]
      FNC_HUMIDITYANDTEMPERATURE: [{display: 'temperatura', suffix: 'temperature|number:1'}, {display: 'wilgotność', suffix: 'humidity|number:0'}]
      FNC_OPENINGSENSOR_GARAGEDOOR: [{display: 'otwarta/zamknięta', suffix: 'hi|bool:zamknięta,otwarta'}]
      FNC_OPENINGSENSOR_DOOR: [{display: 'otwarte/zamknięte', suffix: 'hi|bool:zamknięte,otwarte'}]
      FNC_OPENINGSENSOR_GATE: [{display: 'otwarta/zamknięta', suffix: 'hi|bool:zamknięta,otwarta'}]
      FNC_OPENINGSENSOR_GATEWAY: [{display: 'otwarta/zamknięta', suffix: 'hi|bool:zamknięta,otwarta'}]

    new class
      $onInit: ->
        @text = ''
        @ngModel.$render = => @text = @ngModel.$viewValue or ''
        Channels.getList(Object.keys(CHANNEL_FEEDBACKS)).then (@feedbackableChannels) =>
        @config =
          autocomplete: []
          dropdown: [
            {
              trigger: /\{([^\s]*)/ig
              list: (match, callback) =>
                availableFeedbacks =  @flatten @feedbackableChannels.map (channel) ->
                  angular.copy(CHANNEL_FEEDBACKS[channel.function.name]).map (feedback) ->
                    feedback.display = channelLabelFilter(channel) + " (#{feedback.display})"
                    feedback.channel = channel
                    feedback
                callback availableFeedbacks.filter (feedback) ->
                  !match[0] or feedback.display.toLocaleLowerCase().indexOf(match[1].toLowerCase()) >= 0
              onSelect: (item) -> "{#{item.channel.id}|#{item.suffix}}}"
              mode: 'replace'
            }
          ]

      flatten: (arrayOfArrays) ->
        [].concat.apply([], arrayOfArrays)

      onChange: ->
        @ngModel.$setViewValue(@text)
