angular.module('supla-scripts').component 'feedbackField',
  templateUrl: 'app/scenes/feedback/feedback-field.html'
  bindings:
    condition: '<'
  require:
    ngModel: 'ngModel'
  controller: (Channels, channelLabelFilter, $timeout, $element) ->
    CHANNEL_FEEDBACKS =
      LIGHTSWITCH: [{display: 'zaświecone/zgaszone', suffix: 'on ? "zaświecone" : "zgaszone"'}]
      POWERSWITCH: [{display: 'włączone/wyłączone', suffix: 'on ? "włączone" : "wyłączone"'}]
      THERMOMETER: [
        {display: 'temperatura', suffix: 'temperature|number_format(1)'}
        {display: 'warunek temperatury', suffix: 'temperature < 10 ? "zimno" : "ciepło"'}
      ]
      HUMIDITYANDTEMPERATURE: [
        {display: 'temperatura', suffix: 'temperature|number_format(1)'},
        {display: 'warunek temperatury', suffix: 'temperature < 10 ? "zimno" : "ciepło"'}
        {display: 'wilgotność', suffix: 'humidity|number_format(1)'}
        {display: 'warunek wilgotności', suffix: 'humidity < 50 ? "sucho" : "wilgotno"'}
      ]
      OPENINGSENSOR_GARAGEDOOR: [{display: 'otwarta/zamknięta', suffix: 'hi ? "zamknięta" : "otwarta"'}]
      OPENINGSENSOR_DOOR: [{display: 'otwarte/zamknięte', suffix: 'hi ? "zamknięte" : "otwarte"'}]
      OPENINGSENSOR_ROLLERSHUTTER: [{display: 'otwarte/zamknięte', suffix: 'hi ? "zamknięte" : "otwarte"'}]
      CONTROLLINGTHEROLLERSHUTTER: [{display: 'procent zamknięcia', suffix: 'shut < 5 ? "zamknięte" : "otwarte"'}]
      OPENINGSENSOR_GATE: [{display: 'otwarta/zamknięta', suffix: 'hi ? "zamknięta" : "otwarta"'}]
      OPENINGSENSOR_GATEWAY: [{display: 'otwarta/zamknięta', suffix: 'hi ? "zamknięta" : "otwarta"'}]
      OPENINGSENSOR_WINDOW: [{display: 'otwarte/zamknięte', suffix: 'hi ? "zamknięte" : "otwarte"'}]
      MAILSENSOR: [{display: 'jest/nie ma', suffix: 'hi ? "nie ma" : "jest"'}]
      NOLIQUIDSENSOR: [{display: 'pusto/pełno', suffix: 'hi ? "pusto" : "pełno"'}]
      DIMMERANDRGBLIGHTING: [
        {display: 'jasność', suffix: 'brightness'}
        {display: 'kolor', suffix: 'color|colorNamePl'}
        {display: 'jasność koloru', suffix: 'color_brightness'}
        {display: 'warunek koloru', suffix: 'color|colorNamePl == "czerwony" ? "jest romantycznie" : "jest nudno"'}
      ]
      RGBLIGHTING: [
        {display: 'kolor', suffix: 'color|colorNamePl'}
        {display: 'jasność koloru', suffix: 'color_brightness'}
        {display: 'warunek koloru', suffix: 'color|colorNamePl == "czerwony" ? "jest romantycznie" : "jest nudno"'}
      ]
      ELECTRICITYMETER: [
        {display: 'stan licznika impulsów', suffix: 'calculatedValue'}
        {display: 'aktualna moc', suffix: 'phases[0].powerActive'}
        {display: 'aktualna moc (warunek)', suffix: 'phases[0].powerActive > 1000 ? "dość prasowania" : "może poprasujesz coś?"'}
      ]
      WATERMETER: [{display: 'stan licznika impulsów', suffix: 'calculatedValue'}]
      GASMETER: [{display: 'stan licznika impulsów', suffix: 'calculatedValue'}]

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
                availableFeedbacks = []
                if @feedbackableChannels
                  availableFeedbacks = @flatten @feedbackableChannels.map (channel) =>
                    angular.copy(CHANNEL_FEEDBACKS[channel.function.name]).map (feedback) =>
                      feedback.display = channelLabelFilter(channel) + " (#{feedback.display})"
                      feedback.channel = channel
                      if @condition
                        if feedback.suffix.indexOf('?') > 0
                          feedback.suffix = feedback.suffix.substr(0, feedback.suffix.indexOf('?')).trim()
                        else
                          return false
                      feedback
                availableFeedbacks = availableFeedbacks.filter((a) -> a)
                callback availableFeedbacks.filter (feedback) ->
                  !match[0] or feedback.display.toLocaleLowerCase().indexOf(match[1].toLowerCase()) >= 0
              onSelect: (item) =>
                $timeout(@onChange)
                "{state(#{item.channel.id}).#{item.suffix}}}"
              mode: 'replace'
            }
          ]
        $timeout(-> $element.find('textarea').keyup())

      flatten: (arrayOfArrays) ->
        [].concat.apply([], arrayOfArrays)

      onChange: =>
        @ngModel.$setViewValue(@text)
