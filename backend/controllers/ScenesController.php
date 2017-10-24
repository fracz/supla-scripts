<?php

namespace suplascripts\controllers;

use Assert\Assertion;
use Slim\Http\Response;
use suplascripts\models\scene\FeedbackInterpolator;
use suplascripts\models\scene\Scene;
use suplascripts\models\scene\SceneExecutor;

class ScenesController extends BaseController {
    public function postAction() {
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

    public function getListAction() {
        $this->ensureAuthenticated();
        $scenes = $this->getCurrentUser()->scenes()->getResults();
        return $this->response($scenes);
    }

    public function getAction($params) {
        $this->ensureAuthenticated();
        $scene = $this->ensureExists($this->getCurrentUser()->scenes()->getQuery()->find($params)->first());
        return $this->response($scene);
    }

    public function putAction($params) {
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

    public function deleteAction($params) {
        $this->ensureAuthenticated();
        $scene = $this->ensureExists($this->getCurrentUser()->scenes()->getQuery()->find($params)->first());
        $scene->log('Usunięto scenę.');
        $scene->delete();
        return $this->response()->withStatus(204);
    }

    public function interpolateFeedbackAction() {
        $this->ensureAuthenticated();
        $request = $this->request()->getParsedBody();
        Assertion::notEmptyKey($request, 'feedback');
        return (new FeedbackInterpolator())->interpolate($request['feedback']);
    }

    public function executeSceneAction($params) {
        $scene = $this->getCurrentUser()->scenes()->getQuery()->where($params)->first();
        $this->ensureExists($scene);
        return $this->doExecuteScene($scene);
    }

    public function executeSceneBySlugAction($params) {
        Assertion::notEmptyKey($params, 'slug');
        $scene = Scene::where($params)->first();
        $this->ensureExists($scene);
        $this->getApp()->getContainer()['currentUser'] = $scene->user;
        return $this->doExecuteScene($scene);
    }

    private function doExecuteScene(Scene $scene): Response {
        $feedback = (new SceneExecutor())->executeWithFeedback($scene);
        if ($feedback) {
            return $this->getApp()->response->write($feedback);
        } else {
            return $this->response()->withStatus(204);
        }
    }
}
