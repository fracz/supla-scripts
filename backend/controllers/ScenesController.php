<?php

namespace suplascripts\controllers;

use suplascripts\models\scene\Scene;

class ScenesController extends BaseController
{
    public function postAction()
    {
        $this->ensureAuthenticated();
        $parsedBody = $this->request()->getParsedBody();
        /** @var Scene $scene */
        $scene = $this->getCurrentUser()->scenes()->create($parsedBody);
        if (isset($parsedBody['generateSlug']) && $parsedBody['generateSlug']) {
            $scene->generateSlug();
        }
        $scene->save();
        $scene->log('Utworzono scenę');
        return $this->response($scene)->withStatus(201);
    }

    public function getListAction()
    {
        $this->ensureAuthenticated();
        $scenes = $this->getCurrentUser()->scenes()->getResults();
        return $this->response($scenes);
    }

    public function getAction($params)
    {
        $this->ensureAuthenticated();
        $scene = $this->ensureExists($this->getCurrentUser()->scenes()->getQuery()->find($params)->first());
        return $this->response($scene);
    }

    public function putAction($params)
    {
        $this->ensureAuthenticated();
        /** @var Scene $scene */
        $scene = $this->ensureExists($this->getCurrentUser()->scenes()->getQuery()->find($params)->first());
        $parsedBody = $this->request()->getParsedBody();
        $scene->update($parsedBody);
        if (isset($parsedBody['generateSlug'])) {
            $parsedBody['generateSlug'] ? $scene->generateSlug() : $scene->clearSlug();
        }
        $scene->save();
        $scene->log('Wprowadzono zmiany w scenie.');
        return $this->response($scene);
    }

    public function deleteAction($params)
    {
        $this->ensureAuthenticated();
        $scene = $this->ensureExists($this->getCurrentUser()->scenes()->getQuery()->find($params)->first());
        $scene->log('Usunięto scenę głosową.');
        $scene->delete();
        return $this->response()->withStatus(204);
    }
}
