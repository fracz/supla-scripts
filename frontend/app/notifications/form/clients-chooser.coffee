angular.module('supla-scripts').component 'clientsChooser',
  templateUrl: 'app/notifications/form/clients-chooser.html'
  require:
    ngModel: 'ngModel'
  controller: (Clients) ->
    new class
      $onInit: ->
        @ngModel.$render = @updateSelectedClients
        Clients.getList(onlyDevices: yes).then((@clients) =>).then(@updateSelectedClients)

      updateSelectedClients: =>
        @selectedClients = {}
        if @ngModel.$viewValue
          availableIds = @clients.map(({id}) -> id)
          @selectedClients[id] = yes for id in @ngModel.$viewValue when id in availableIds
        @updateLabel()

      toggle: ({id}) =>
        @selectedClients[id] = not @selectedClients[id]
        @updateModel()

      updateModel: =>
        selectedIds = (id for id, selected of @selectedClients when selected)
        @updateLabel()
        @ngModel.$setViewValue(selectedIds)

      updateLabel: =>
        selectedIds = (id for id, selected of @selectedClients when selected)
        if not selectedIds.length
          @label = 'Nie wybrano'
        else if selectedIds.length > 4
          @label = selectedIds.length + ' urządzeń'
        else
          @label = @clients.filter(({id}) -> id in selectedIds).map(({label}) -> label).join(', ')
