angular.module('supla-scripts').component 'channelActions',
  templateUrl: 'app/dashboard/channel/channel-actions.html'
  bindings:
    channel: '<'
    onAction: '&'
  controller: (CHANNEL_AVAILABLE_ACTIONS, $scope) ->
    new class
      $onInit: ->
        @CHANNEL_AVAILABLE_ACTIONS = CHANNEL_AVAILABLE_ACTIONS
        if @channel.function.name.indexOf('DIMMER') >= 0
          @brightness = +@channel.state?.brightness
          @brightnessSliderOptions =
            floor: 0
            ceil: 100
            step: 1
            hideLimitLabels: yes
            hidePointerLabels: yes
            onEnd: => @updateRgb({brightness: @brightness})
          $scope.$watch('$ctrl.channel.state.brightness', ((@brightness) =>))
          $scope.$watch('$ctrl.channel.changing', ((v) => @brightnessSliderOptions.disabled = v))
        if @channel.function.name.indexOf('RGBLIGHTING') >= 0
          @colorBrightness = +@channel.state?.color_brightness
          @colorBrightnessSliderOptions =
            floor: 0
            ceil: 100
            step: 1
            hideLimitLabels: yes
            hidePointerLabels: yes
            onEnd: => @updateRgb({color_brightness: @colorBrightness})
          $scope.$watch('$ctrl.channel.state.color_brightness', ((@colorBrightness) =>))
          $scope.$watch('$ctrl.channel.changing', ((v) => @colorBrightnessSliderOptions.disabled = v))

      updateRgb: (toUpdate) ->
        state = angular.copy(@channel.state)
        angular.extend(state, toUpdate)
        state.color ?= 1
        state.brightness ?= 100
        state.color_brightness ?= 100
        command = ['setRgb', state.color, state.color_brightness, state.brightness].join(',')
        @onAction({action: command})
