<?php

namespace suplascripts\controllers;

use suplascripts\models\scene\Scene;
use suplascripts\models\scene\SceneGroup;

class SceneGroupsController extends BaseController {
    public function postAction() {
        $this->ensureAuthenticated();
        $parsedBody = $this->request()->getParsedBody();
        $parsedBody['ordinalNumber'] = 999;
        /** @var SceneGroup $sceneGroup */
        $sceneGroup = $this->getCurrentUser()->sceneGroups()->create($parsedBody);
        $sceneGroup->save();
        return $this->response($sceneGroup)->withStatus(201);
    }

    public function getListAction() {
        $this->ensureAuthenticated();
        $scenes = $this->getCurrentUser()->sceneGroups()->getQuery()->orderBy(Scene::ORDINAL_NUMBER)->get();
        return $this->response($scenes);
    }

    public function putAction($params) {
        $this->ensureAuthenticated();
        /** @var SceneGroup $sceneGroup */
        $sceneGroup = $this->ensureExists($this->getCurrentUser()->sceneGroups()->getQuery()->find($params)->first());
        $parsedBody = $this->request()->getParsedBody();
        $sceneGroup->update($parsedBody);
        $sceneGroup->save();
        return $this->response($sceneGroup);
    }

    public function deleteAction($params) {
        $this->ensureAuthenticated();
        $sceneGroup = $this->ensureExists($this->getCurrentUser()->sceneGroups()->getQuery()->find($params)->first());
        $sceneGroup->delete();
        return $this->response()->withStatus(204);
    }

    public function updateOrderAction() {
        $parsedBody = $this->request()->getParsedBody();
        $map = $parsedBody['map'] ?? [];
        $this->getApp()->db->getConnection()->transaction(function () use ($map) {
            $groupIndex = -999;
            $sceneIndex = 0;
            foreach ($map as $groupDef) {
                $groupId = array_shift($groupDef);
                if ($groupId !== 'default') {
                    /** @var SceneGroup $group */
                    $group = $this->ensureExists($this->getCurrentUser()->sceneGroups()->getQuery()->find(['id' => $groupId])->first());
                    $group->ordinalNumber = $groupIndex++;
                    $group->save();
                } else {
                    $group = null;
                    $groupIndex = 0;
                }
                foreach ($groupDef as $sceneId) {
                    /** @var Scene $scene */
                    $scene = $this->ensureExists($this->getCurrentUser()->scenes()->getQuery()->find(['id' => $sceneId])->first());
                    $scene->ordinalNumber = $sceneIndex++;
                    if ($group) {
                        $scene->group()->associate($group);
                    } else {
                        $scene->group()->dissociate();
                    }
                    $scene->save();
                }
            }
        });
        return $this->response()->withStatus(204);
    }
}
