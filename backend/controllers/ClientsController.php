<?php

namespace suplascripts\controllers;

use Ramsey\Uuid\Uuid;
use suplascripts\models\automate\AutomateSender;
use suplascripts\models\Client;

class ClientsController extends BaseController {
    public function getListAction() {
        $this->ensureAuthenticated();
        $query = $this->getCurrentUser()->clients()->getQuery();
        if ($this->request()->getParam('onlyDevices')) {
            $query = $query->where(Client::SCENE_ID, null);
        }
        $clients = $query->orderBy(Client::LAST_CONNECTION_DATE, 'desc')->get();
        return $this->response($clients);
    }

    public function putAction($params) {
        $this->ensureAuthenticated();
        $client = $this->ensureExists($this->getCurrentUser()->clients()->getQuery()->find($params)->first());
        $parsedBody = $this->request()->getParsedBody();
        $client->update($parsedBody);
        $client->save();
        return $this->response($client);
    }

    public function deleteAction($params) {
        $this->ensureAuthenticated();
        $client = $this->ensureExists($this->getCurrentUser()->clients()->getQuery()->find($params)->first());
        $client->delete();
        return $this->response()->withStatus(204);
    }

    public function postAction() {
        $this->ensureAuthenticated();
        /** @var Client $client */
        $user = $this->getCurrentUser();
        $client = $user->clients()->whereNotNull(Client::AUTH_CODE)->first();
        if (!$client) {
            $client = $user->clients()->create([]);
        }
        // TODO label
        $client->authCode = Uuid::uuid4();
        $client->save();
        $automate = new AutomateSender($user);
        $automate->sendCommand('newClient', $client->authCode);
    }
}
