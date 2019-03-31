angular.module('supla-scripts').constant 'CHANNEL_AVAILABLE_ACTIONS', do ->
  actions =
    POWERSWITCH: [
      {label: 'włącz', action: 'turnOn', isActive: (channel) -> channel.state.on}
      {label: 'wyłącz', action: 'turnOff', isActive: (channel) -> channel.state.on == false}
      {label: 'przełącz', action: 'toggle', isActive: -> no}
    ]
    CONTROLLINGTHEGATE: [
      {label: 'otwórz/zamknij', action: 'toggle', isActive: -> no}
    ]
    CONTROLLINGTHEGATEWAYLOCK: [
      {label: 'otwórz', action: 'toggle', isActive: -> no}
    ]
    RGBLIGHTING: [
      {label: 'wyłącz', action: 'setRgb,1,0,0', isActive: (channel) -> (channel.state.color_brightness != undefined || channel.state.brightness != undefined) && !channel.state.color_brightness && !channel.state.brightness}
      {label: 'biały', action: 'setRgb,ffffff,100,100', noButton: yes, isActive: -> no}
      {label: 'żółty', action: 'setRgb,ffff00,100,100', noButton: yes, isActive: -> no}
      {label: 'czerwony', action: 'setRgb,ff0000,100,100', noButton: yes, isActive: -> no}
      {label: 'pomarańczowy', action: 'setRgb,ff8800,100,100', noButton: yes, isActive: -> no}
      {label: 'jasny niebieski', action: 'setRgb,00ffff,100,100', noButton: yes, isActive: -> no}
      {label: 'niebieski', action: 'setRgb,0000ff,100,100', noButton: yes, isActive: -> no}
      {label: 'różowy', action: 'setRgb,ff00ff,100,100', noButton: yes, isActive: -> no}
      {label: 'zielony', action: 'setRgb,00ff00,100,100', noButton: yes, isActive: -> no}
      {label: 'losowy', action: 'setRgb,random,100,100', isActive: -> no}
    ]
    CONTROLLINGTHEROLLERSHUTTER: [
      {label: 'odsłoń', action: 'reveal', isActive: -> no}
      {label: 'zasłoń 20%', action: 'shut,20', isActive: -> no}
      {label: 'zasłoń 40%', action: 'shut,40', isActive: -> no}
      {label: 'zasłoń 60%', action: 'shut,60', isActive: -> no}
      {label: 'zasłoń 80%', action: 'shut,80', isActive: -> no}
      {label: 'zasłoń', action: 'shut', isActive: -> no}
    ]

  actions.LIGHTSWITCH = actions.STAIRCASETIMER = actions.POWERSWITCH
  actions.CONTROLLINGTHEGARAGEDOOR = actions.CONTROLLINGTHEGATE
  actions.CONTROLLINGTHEDOORLOCK = actions.CONTROLLINGTHEGATEWAYLOCK
  actions.DIMMERANDRGBLIGHTING = actions.RGBLIGHTING

  actions
