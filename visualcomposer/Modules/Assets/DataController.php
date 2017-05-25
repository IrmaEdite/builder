<?php

namespace VisualComposer\Modules\Assets;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Access\CurrentUser;
use VisualComposer\Helpers\Options;
use VisualComposer\Helpers\Traits\EventsFilters;

class DataController extends Container implements Module
{
    use EventsFilters;

    public function __construct()
    {
        /** @see \VisualComposer\Modules\Assets\DataController::setData */
        $this->addFilter(
            'vcv:dataAjax:setData',
            'setData'
        );
        /** @see \VisualComposer\Modules\Assets\DataController::getData */
        $this->addFilter(
            'vcv:dataAjax:getData',
            'getData'
        );
    }

    protected function getData($response, $payload, Options $optionsHelper, CurrentUser $currentUserAccessHelper)
    {
        // @codingStandardsIgnoreLine
        global $post_type_object;
        $sourceId = $payload['sourceId'];
        if (is_numeric($sourceId)
            // @codingStandardsIgnoreLine
            && $currentUserAccessHelper->wpAll([$post_type_object->cap->read, $sourceId])->get()
        ) {
            $postCustomCss = get_post_meta($sourceId, 'vcvSettingsSourceCustomCss', true);
            $response['cssSettings'] = [
                'custom' => $postCustomCss ? $postCustomCss : '',
                'global' => $optionsHelper->get('settingsGlobalCss', ''),
            ];
            $response['globalElements'] = $optionsHelper->get('globalElements', '');

            return $response;
        }

        return false;
    }

    protected function setData($response, $payload)
    {
        $requestHelper = vchelper('Request');
        $sourceId = $payload['sourceId'];
        $this->updateSourceAssets($sourceId);
        if ($requestHelper->input('wp-preview', '') === 'dopreview') {
            $this->updatePostAssets($sourceId);
        } else {
            $this->updateGlobalAssets($sourceId);
        }

        return $response;
    }

    protected function updateSourceAssets($sourceId)
    {
        $currentUserAccessHelper = vchelper('AccessCurrentUser');
        // @codingStandardsIgnoreLine
        global $post_type_object;
        if (is_numeric($sourceId)
            && $currentUserAccessHelper->wpAll(
            // @codingStandardsIgnoreLine
                [$post_type_object->cap->edit_post, $sourceId]
            )->get()
        ) {
            $requestHelper = vchelper('Request');
            update_post_meta($sourceId, 'vcvSourceAssetsFiles', $requestHelper->inputJson('vcv-source-assets-files'));
            update_post_meta($sourceId, 'vcvSourceCss', $requestHelper->input('vcv-source-css'));
            update_post_meta(
                $sourceId,
                'vcvSettingsSourceCustomCss',
                $requestHelper->input('vcv-settings-source-custom-css')
            );
        }
    }

    protected function updateGlobalAssets($sourceId)
    {
        $currentUserAccessHelper = vchelper('AccessCurrentUser');
        // @codingStandardsIgnoreLine
        global $post_type_object;
        if (is_numeric($sourceId)
            && $currentUserAccessHelper->wpAll(
            // @codingStandardsIgnoreLine
                [$post_type_object->cap->edit_post, $sourceId]
            )->get()
        ) {
            $optionsHelper = vchelper('Options');
            $requestHelper = vchelper('Request');
            // Base css
            $elementsCssData = $requestHelper->inputJson('vcv-elements-css-data', '');
            $globalElementsCssData = $optionsHelper->get('globalElementsCssData', []);
            $globalElementsCssData[ $sourceId ] = $elementsCssData;
            $optionsHelper->set('globalElementsCssData', $globalElementsCssData);
            $optionsHelper->set('globalElementsCssData', $globalElementsCssData);
            // Other data
            $optionsHelper->set('globalElementsCss', $requestHelper->input('vcv-global-elements-css'));
            $optionsHelper->set('settingsGlobalCss', $requestHelper->input('vcv-settings-global-css'));
        }
    }

    protected function updatePostAssets($sourceId)
    {
        $currentUserAccessHelper = vchelper('AccessCurrentUser');
        // @codingStandardsIgnoreLine
        global $post_type_object;
        // @codingStandardsIgnoreLine
        if (is_numeric($sourceId)
            && $currentUserAccessHelper->wpAll(
            // @codingStandardsIgnoreLine
            [$post_type_object->cap->edit_post, $sourceId]
            )->get()
        ) {
            $requestHelper = vchelper('Request');
            // Base css
            update_post_meta($sourceId, 'elementsCssData', $requestHelper->inputJson('vcv-elements-css-data', ''));
            // Other data
            // update_post_meta($sourceId, 'globalElementsCss', $requestHelper->input('vcv-global-elements-css'));
            update_post_meta($sourceId, 'settingsGlobalCss', $requestHelper->input('vcv-settings-global-css'));
        }
    }
}
