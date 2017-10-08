angular.module('supla-scripts').component 'voiceFeedbackField',
  templateUrl: 'app/voice/form/voice-feedback-field.html'
  require:
    ngModel: 'ngModel'
  controller: (Channels, channelLabelFilter) ->

    CHANNEL_FEEDBACKS =
      FNC_LIGHTSWITCH: [{display: 'zaświeczone/zgaszone', field: 'on'}]
      FNC_POWERSWITCH: [{display: 'włączone/wyłączone', field: 'on'}]
      FNC_THERMOMETER: [{display: 'temperatura', field: 'temperature'}]
      FNC_HUMIDITYANDTEMPERATURE: [{display: 'wilgotność', field: 'humidity'}]

    new class
      text: ''

      config:
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
            onSelect: (item) -> "{#{item.channel.id}|#{item.field}}}"
            mode: 'replace'
          }
        ]

      $onInit: ->
        @ngModel.$render = => @text = @ngModel.$viewValue or ''
        Channels.getList(Object.keys(CHANNEL_FEEDBACKS)).then (@feedbackableChannels) =>

      flatten: (arrayOfArrays) ->
        [].concat.apply([], arrayOfArrays)

      onChange: ->
        @ngModel.$setViewValue(@text)
