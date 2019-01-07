angular.module('supla-scripts').component 'clientsList',
  templateUrl: 'app/clients/clients-list.html'
  controller: (Clients, swangular, Notifier, $scope, $q, Users) ->
    new class
      $onInit: ->

        Clients.getList().then((@clients) =>)

      editLabel: (client) ->
        $q.when swal
          type: 'question'
          title: 'Podaj nową nazwę'
          showCancelButton: yes
          showConfirmButton: yes
          cancelButtonText: 'Anuluj'
          confirmButtonText: 'Zapisz'
          showLoaderOnConfirm: true
          input: 'text'
          preConfirm: (newLabel) =>
            client.label = newLabel
            client.put()
        .then ->
          Notifier.success('Nazwa klucza dostępu została zmieniona.')

      deactivate: (client) ->
        client.active = no
        client.put()

      activate: (client) ->
        client.active = yes
        client.put()

      deleteClient: (client) ->
        swangular.open
          scope: $scope
          type: 'question'
          title: 'Na pewno?'
          text: 'Czy chcesz usunąć ten klucz dostępu?'
          showCancelButton: yes
          showConfirmButton: yes
          cancelButtonText: 'Anuluj'
          confirmButtonText: 'Tak, usuń'
          confirmButtonColor: '#d62c1a'
          showLoaderOnConfirm: true
          preConfirm: -> client.remove()
        .then =>
          Notifier.success('Klucz dostępu został usunięty.')
          @clients.splice(@clients.indexOf(client), 1)
