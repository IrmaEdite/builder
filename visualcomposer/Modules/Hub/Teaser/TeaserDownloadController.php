<?php

namespace VisualComposer\Modules\Hub\Teaser;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Traits\EventsFilters;

class TeaserDownloadController extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        if (vcvenv('VCV_ENV_HUB_TEASER')) {
            $this->addFilter('vcv:hub:process:action:hubTeaser', 'processAction');
        }
    }

    protected function processAction($response, $payload, Options $optionsHelper)
    {
        if (!vcIsBadResponse($response) && $payload['data']) {
            $teaserElements = $this->getTeaserElements($payload['data']['teasers']);
            $optionsHelper->set('hubTeaserElements', $teaserElements);
            $response = ['status' => true];
        }

        return $response;
    }

    protected function getTeaserElements($teasers)
    {
        $groupList = [
            'All' => [
                'id' => 'All0',
                'index' => 0,
                'title' => 'All',
                'elements' => [],
            ],
        ];
        $x = 1;
        $strHelper = vchelper('Str');
        $allElements = [];
        $dataHelper = vchelper('Data');
        foreach ($teasers as $element) {
            $groups = explode(',', $element['group']);
            foreach ($groups as $group) {
                $group = trim($group);
                $catIndex = $x++;
                if (!isset($groupList[ $group ])) {
                    $groupList[ $group ] = [
                        'id' => ucfirst(strtolower($group)) . $catIndex,
                        'index' => $catIndex,
                        'title' => $group,
                        'elements' => [],
                    ];
                }
                $elementData = [
                    'tag' => $strHelper->studly($strHelper->slugify($element['name'])),
                    'name' => $element['name'],
                    'metaThumbnailUrl' => $element['thumbnailUrl'],
                    'metaPreviewUrl' => $element['previewUrl'],
                    'metaDescription' => $element['description'],
                ];
                $groupList[ $group ]['elements'][] = $elementData;
                $groupList[ $group ]['elements'] = $dataHelper->arrayDeepUnique($groupList[ $group ]['elements']);
                $allElements[] = $elementData;
            }
        }
        $groupList['All']['elements'] = $dataHelper->arrayDeepUnique($allElements);

        return $groupList;
    }
}
