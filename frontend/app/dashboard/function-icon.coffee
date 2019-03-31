angular.module('supla-scripts').component 'functionIcon',
  templateUrl: 'app/dashboard/function-icon.html'
  bindings:
    channel: '<'
  controller: ->
    new class
      functionId: ->
        if @channel.function.name == 'UNSUPPORTED' then 0 else @channel.function.id

      stateSuffix: ->
        if @channel.state
          if @channel.state.hi
            return '-closed'
          if @channel.state.partial_hi
            return '-partial'
          if @channel.state.color_brightness != undefined && @channel.state.brightness != undefined
            return '-' + (if @channel.state.brightness then 'on' else 'off') + (if @channel.state.color_brightness then 'on' else 'off')
          else if @channel.state.color_brightness == 0 || @channel.state.brightness == 0
            return '-off'
          if @channel.state.on == false
            return '-off'
