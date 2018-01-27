angular.module('supla-scripts').filter 'hasActions', ->
  ({actions}) ->
    if actions
      for offset, offsetActions of actions
        if offsetActions?.length
          return true
    false
