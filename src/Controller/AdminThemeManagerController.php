<?php
/**
 * Copyright (c) Since 2020 PrestaSafe and contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@prestasafe.com so we can send you a copy immediately.
 *
 * @author    PrestaSafe <contact@prestasafe.com>
 * @copyright Since 2020 PrestaSafe and contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaSafe
 */

namespace PrestaSafe\PrettyBlocks\Controller;

// use Doctrine\Common\Cache\CacheProvider;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminThemeManagerController extends FrameworkBundleAdminController
{
    public function uploadAction(Request $request)
    {
        $message = '';
        $posts = json_decode($request->getContent(), true);
        //  remove
        if (!empty($posts) && $posts['action'] == 'removeImage') {
            $url = '';
            if (isset($posts['state']['url'])) {
                $url = $posts['state']['url'];
            }
            if (isset($posts['state']['imgs']['url'])) {
                $url = $posts['state']['imgs']['url'];
            }

            $message = \Context::getContext()->getTranslator()->trans('Image removed successfully', [], 'Modules.Prettyblocks.Admin');
            $path = \HelperBuilder::pathFormattedFromUrl($url);
            $unlink = @unlink($path);
            if (!$unlink) {
                $message = \Context::getContext()->getTranslator()->trans('Image not found', [], 'Modules.Prettyblocks.Admin');
            }

            return (new JsonResponse())->setData([
                'action' => 'removeimage',
                'message' => $message,
                'request' => $posts,
                'success' => $unlink,
            ]);
        }
        //  upload
        $file = $_FILES['file'];
        $uploaded = false;
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $imgs = [];
        if (in_array($extension, \Module::getInstanceByName('prettyblocks')->valid_types)) {
            // can upload
            $new_name = \Tools::str2url(pathinfo($file['name'], PATHINFO_FILENAME));
            $path = '$/modules/prettyblocks/views/images/';
            if (\Tools::getIsset('path')) {
                $path = pSQL(\Tools::getValue('path'));
            }
            $upload_dir = \HelperBuilder::pathFormattedFromString($path);
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_name . '.' . $extension)) {
                $uploaded = true;

                $myurl = \HelperBuilder::pathFormattedToUrl($path) . '/' . $new_name . '.' . $extension;
                $imgs = [
                    'url' => $myurl,
                    'extension' => pathinfo($myurl, PATHINFO_EXTENSION),
                    'mediatype' => \HelperBuilder::getMediaTypeForExtension(pathinfo($myurl, PATHINFO_EXTENSION)),
                    'filename' => pathinfo($myurl, PATHINFO_BASENAME),
                ];
            } else {
                switch ($file['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        $imgs['error'] = \Context::getContext()->getTranslator()->trans('The uploaded file exceeds the upload_max_filesize directive.', [], 'Modules.Prettyblocks.Admin');
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $imgs['error'] = \Context::getContext()->getTranslator()->trans('The uploaded file exceeds the post_max_size directive.', [], 'Modules.Prettyblocks.Admin');
                        break;
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $imgs['error'] = \Context::getContext()->getTranslator()->trans('The uploaded file was only partially uploaded.', [], 'Modules.Prettyblocks.Admin');
                        break;
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $imgs['error'] = \Context::getContext()->getTranslator()->trans('Please provide a file.', [], 'Modules.Prettyblocks.Admin');
                        break;
                        break;
                }
                $message .= $imgs['error'];
            }
        }

        return (new JsonResponse())->setData([
            'file' => $request->files,
            'test' => json_decode(\Tools::file_get_contents('php://input'), true),
            'tools' => \Tools::getAllValues(),
            'path_request' => \Tools::getValue('path'),
            'path' => \HelperBuilder::pathFormattedFromString(\Tools::getValue('path')),
            'request' => $posts,
            'uploaded' => $uploaded,
            'ext' => $extension,
            'imgs' => $imgs,
            'files' => $_FILES['file'],
            'message' => $message,
        ]);
    }

    private function getShops()
    {
        $shops = \Shop::getShops();
        $results = [];

        foreach ($shops as $shop) {
            $shop['current_url'] = $this->buildShopUri($shop);
            $results[] = $shop;
        }

        return $results;
    }

    private function buildShopUri($shop)
    {
        return \Tools::getProtocol(\Tools::usingSecureMode()) . $shop['domain_ssl'] . $shop['uri'];
    }

    /**
     * return Symfony URL from  route
     *
     * @param string $route
     * @param string $entity
     *
     * @return string
     */
    private function getSFUrl($route, $entity = 'sf')
    {
        $domain = \Tools::getShopDomainSsl(true);

        return $domain . \Link::getUrlSmarty([
            'entity' => $entity,
            'route' => $route,
        ]);
    }

    public function indexAction()
    {
        $context = $this->get('prestashop.adapter.legacy.context')->getContext();

        $shop = $context->shop;
        $domain = \Tools::getShopDomainSsl(true);

        $filesystem = new Filesystem();
        $path = '/modules/prettyblocks/build/';
        $build_dir = _PS_ROOT_DIR_ . $path;
        $build_dir_https = \Tools::getShopDomainSsl(true) . $shop->physical_uri . ltrim($path, '/');
        $js = [];
        $css = [];
        $js_entry = '';
        if ($filesystem->exists($build_dir)) {
            // load manifest.json
            $manifest = $build_dir . 'manifest.json';

            if (!$filesystem->exists($manifest)) {
                throw new \Exception('manifest.json not exist');
            }
            $json = \Tools::file_get_contents($manifest);
            $json = json_decode($json, true);

            foreach ($json as $file) {
                if (isset($file['file'])) {
                    if (isset($file['isEntry']) && $file['isEntry']) {
                        $js_entry = $build_dir_https . $file['file'];
                    } else {
                        $js[] = $build_dir_https . $file['file'];
                    }
                }
                if (isset($file['css'])) {
                    foreach ($file['css'] as $entry) {
                        $css[] = $build_dir_https . $entry;
                    }
                }
            }
        }
        $module = \Module::getInstanceByName('prettyblocks');
        $uri = $module->getPathUri() . 'views/css/back.css?version=' . $module->version;
        $symfonyUrl = $this->getSFUrl('prettyblocks_homesimulator');
        $sfAdminBlockAPI = $this->getSFUrl('prettyblocks_api');
        $uploadUrl = $this->getSFUrl('prettyblocks_upload');
        $collectionURL = $this->getSFUrl('prettyblocks_collection');
        $link = new \Link();
        $blockUrl = $link->getModuleLink('prettyblocks', 'ajax');
        $blockAvailableUrls = $this->getSFUrl('prettyblocks_api_get_blocks_available');
        $settingsUrls = $this->getSFUrl('prettyblocks_theme_settings');
        $shop_url = $context->shop->getBaseUrl(true) . $this->getLangLink($context->language->id, $context, $context->shop->id);
        $translator = \Context::getContext()->getTranslator();
        $shops = $this->getShops();
        $available_language_ids = \Language::getLanguages(true, $context->shop->id);
        // url to load at startup : provided url or shop home page
        $startup_url = \Tools::getValue('startup_url', $shop_url);

        return $this->render('@Modules/prettyblocks/views/templates/admin/index.html.twig', [
            'css_back_custom' => $uri,
            'base_url' => $link->getBaseLink(),
            'favicon_url' => \Tools::getShopDomainSsl(true) . '/modules/' . $module->name . '/views/images/favicon.ico',
            'module_name' => $module->displayName,
            'shop_name' => $context->shop->name,
            'ajax_urls' => [
                'shops' => $shops,
                'simulate_home' => $symfonyUrl,
                'search_by_ref' => $symfonyUrl,
                'adminURL' => $context->link->getAdminBaseLink() . basename(_PS_ADMIN_DIR_),
                // 'update_ajax' => $updateAjax,
                'sf' => $sfAdminBlockAPI,
                'api' => $blockUrl,
                'current_domain' => $shop_url,
                'block_url' => $blockUrl,
                'state' => $blockUrl,
                'upload' => $uploadUrl,
                'collection' => $collectionURL,
                'blocks_available' => $blockAvailableUrls,
                'block_action_urls' => $blockUrl,
                'theme_settings' => $settingsUrls,
                'startup_url' => $startup_url,
            ],
            'trans_app' => [
                'current_shop' => $translator->trans('Shop in modification', [], 'Modules.Prettyblocks.Admin'),
                'save' => $translator->trans('Save', [], 'Modules.Prettyblocks.Admin'),
                'add_new_element' => $translator->trans('Add new element', [], 'Modules.Prettyblocks.Admin'),
                'avalaible_elements' => $translator->trans('Availables blocks', [], 'Modules.Prettyblocks.Admin'),
                'close' => $translator->trans('Close', [], 'Modules.Prettyblocks.Admin'),
                'current_zone' => $translator->trans('Current zone', [], 'Modules.Prettyblocks.Admin'),
                'loading' => $translator->trans('Loading', [], 'Modules.Prettyblocks.Admin'),
                'default_settings' => $translator->trans('Default settings', [], 'Modules.Prettyblocks.Admin'),
                'choose_template' => $translator->trans('Choose a template', [], 'Modules.Prettyblocks.Admin'),
                'use_container' => $translator->trans('Place the element in a column (container)', [], 'Modules.Prettyblocks.Admin'),
                'bg_color' => $translator->trans('Background color', [], 'Modules.Prettyblocks.Admin'),
                'ex_color' => $translator->trans('Add a color ex: #123456', [], 'Modules.Prettyblocks.Admin'),
                'theme_settings' => $translator->trans('Theme settings', [], 'Modules.Prettyblocks.Admin'),
                'type_search_here' => $translator->trans('Type your search here', [], 'Modules.Prettyblocks.Admin'),
            ],
            'security_app' => [
                'ajax_token' => \Configuration::getGlobalValue('_PRETTYBLOCKS_TOKEN_'),
                'prettyblocks_version' => $module->version,
                'available_language_ids' => $available_language_ids,
                'tinymce_api_key' => \TplSettings::getSettings('tinymce_api_key') ?? 'no-api-key',
            ],
            'css_build' => $css,
            'js_build' => $js,
            'js_entry' => $js_entry,
        ]);
    }

    // (ajax_urls.state)
    public function testAction(Request $request)
    {
        //  load blocks by zoneName
        //  Update config block
        if (empty($_POST)) {
            $_POST = json_decode($request->getContent(), true);
        }
        $data = json_decode($request->getContent(), true);
        if ($data) {
            foreach ($data as $key => $value) {
                $request->query->set($key, $value);
            }
        }
        $action = $request->query->get('action');
        // OK
        if ($action == 'getBlockConfig') {
            $id_lang = (int) $request->get('ctx_id_lang');
            $id_shop = (int) $request->get('ctx_id_shop');
            $id_block = (int) $request->query->get('id_prettyblocks');
            $state = new \PrettyBlocksModel($id_block, $id_lang, $id_shop);
            $block = $state->mergeStateWithFields();

            exit(json_encode([
                'state' => $block,
                'config' => $block['settings_formatted'],
            ]));
        }

        // a tester
        if ($action == 'getState' || $action == 'getSubState') {
            $state = new \PrettyBlocksModel((int) $request->query->get('id_prettyblocks'));
            $block = $state->mergeStateWithFields();
            exit(json_encode([
                'state' => $block,
                'state_db' => ($action == 'getState') ? $block['repeater_db'] : $block['repeater_db'],
            ]));
        }
        // OK

        // remove sub element OK
        if ($action == 'removeSubState') {
            $formattedID = (string) $request->query->get('formattedID');
            $ids = explode('-', $formattedID);
            $id_block = $ids[0];
            $substate_key = $ids[1];
            $id_lang = (int) $request->get('ctx_id_lang');
            $id_shop = (int) $request->get('ctx_id_shop');
            $state = new \PrettyBlocksModel($id_block, $id_lang, $id_shop);
            $block = $state->mergeStateWithFields();
            $state_db = json_decode($state->state, true);

            if (isset($state_db[$substate_key])) {
                unset($state_db[$substate_key]);
            }
            $encoded = json_encode($state_db);
            $state->state = $encoded;
            if ($state->save()) {
                exit(json_encode([
                    'success' => true,
                    'state' => $encoded,
                ]));
            }
        }

        // remove element
        if ($action == 'removeState') {
            $id_prettyblocks = (string) $request->query->get('id_prettyblocks');
            $block = new \PrettyBlocksModel($id_prettyblocks);
            if ($block->delete()) {
                exit(json_encode([
                    'success' => true,
                ]));
            }
        }

        // for pushing an empty element repeater
        if ($action == 'getEmptyState') {
            $success = true;
            $id_block = (int) $request->query->get('id_prettyblocks');
            $id_lang = (int) $request->query->get('ctx_id_lang');
            $id_shop = (int) $request->query->get('ctx_id_shop');
            $state = new \PrettyBlocksModel($id_block, $id_lang, $id_shop);

            $block = $state->mergeStateWithFields();
            $state_to_push = $block['state_to_push'];

            if (!isset($block['state_to_push'])) {
                $success = false;
                $state_to_push = [];
                exit(json_encode([
                    'state_to_push' => $state_to_push,
                    'success' => $success,
                ]));
            }
            $state_db = json_decode($state->state, true);
            $maxKey = 1;
            if (count($state_db) > 0) {
                foreach ($state_db as $key => $value) {
                    if ($key >= $maxKey) {
                        $maxKey = $key;
                    }
                }
            }

            $state_db[$maxKey + 1] = (object) $state_to_push;
            $encoded = json_encode($state_db);
            $state->state = $encoded;
            if ($state->save()) {
                exit(json_encode([
                    'success' => $success,
                    'to_push' => $state_to_push,
                ]));
            }
        }

        if ($action == 'updateStatePosition') {
            $items = $request->query->get('items');
            $item0 = $items[0];
            $id_block = (int) $item0['id_prettyblocks'];
            $id_lang = (int) $request->query->get('ctx_id_lang');
            $id_shop = (int) $request->query->get('ctx_id_shop');
            $state = new \PrettyBlocksModel($id_block, $id_lang, $id_shop);
            $state_db = json_decode($state->state, true);
            $keyPositions = [];
            foreach ($items as $item) {
                $itemDecoded = $item;
                $ids = explode('-', $itemDecoded['id']);
                $substate_key = $ids[1];
                $keyPositions[$substate_key] = $state_db[(int) $substate_key];
            }

            $state->state = json_encode($keyPositions);
            $state->save();
            exit(json_encode([
                'state' => $items,
                'errors' => $action,
            ]));
        }

        if ($action == 'loadBlockById') {
            $id_block = (int) $request->query->get('id_prettyblocks');
            $id_lang = (int) $request->query->get('ctx_id_lang');
            $id_shop = (int) $request->query->get('ctx_id_shop');
            $state = new \PrettyBlocksModel($id_block, $id_lang, $id_shop);
            $block = $state->mergeStateWithFields();

            return exit(json_encode($block, true));
        }

        if ($action == 'updateStateParentPosition') {
            $items = $request->query->get('items');
            $i = 1;
            $position = [];
            foreach ($items as $item) {
                $item = (object) $item;
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'prettyblocks` SET position=' . $i . ' WHERE id_prettyblocks = ' . (int) $item->id_prettyblocks;
                $position[$item->id_prettyblocks] = $position;
                \Db::getInstance()->execute($sql);
                ++$i;
            }
        }

        if ($action == 'updateThemeSettings') {
            $stateRequest = $request->query->get('stateRequest');
            \PrettyBlocksModel::updateThemeSettings($stateRequest);
            exit(json_encode([
                'success' => true,
                'saved' => true,
                'message' => $this->getTranslator()->trans('Updated with success', [], 'Modules.Prettyblocks.Admin'),
            ], true));
        }

        $blocks = \PrettyBlocksModel::getInstanceByZone('displayHome');

        return (new JsonResponse())->setData(['blocks' => $blocks]);
    }

    public function getSettingsAction(Request $request)
    {
        $id_shop = (int) $request->get('ctx_id_shop');
        $res = \PrettyBlocksModel::getThemeSettings(true, 'back', $id_shop);

        return (new JsonResponse())->setData([
            'settings' => $res,
            'errors' => 'No action found',
        ]);
    }

    /**
     * State action in AJAX.
     * Get and Update a block
     * (ajax_urls.state)
     *
     * @return JsonReponse
     */
    public function getState(Request $request)
    {
        $action = $request->get('action');
        if ($action == 'getState') {
            $state = new \PrettyBlocksModel((int) $request->query->get('id_prettyblocks'));
            $block = $state->mergeStateWithFields();

            return (new JsonResponse())->setData([
                'state' => $block,
                'state_db' => $block['repeater_db'],
            ]);
        }
        if ($action == 'updateState') {
            $id_block = (int) $request->query->get('id_prettyblocks');

            $state = new \PrettyBlocksModel($id_block);
            $stateRequest = $request->get('state');
            // dump(json_decode($stateRequest[0],true));
            // die();
            $formattedState = [];
            foreach ($stateRequest as $str) {
                $formattedState[] = json_decode($str, true);
            }

            // dump($formattedState);
            $state->state = json_encode($formattedState);
            // dump(json_encode($formattedState));
            // die();
            if ($state->save()) {
                return (new JsonResponse())->setData([
                    'success' => true,
                    'saved' => true,
                    'state' => $stateRequest,
                    'message' => $this->getTranslator()->trans('Updated with success', [], 'Modules.Prettyblocks.Admin'),
                ]);
            }
        }
        // remove element
        if ($action == 'removeState') {
            $id_prettyblocks = (string) $request->query->get('id_prettyblocks');
            $block = new \PrettyBlocksModel($id_prettyblocks);
            if ($block->delete()) {
                return (new JsonResponse())->setData([
                    'success' => true,
                ]);
            }
        }

        return (new JsonResponse())->setData([
            'state' => [],
            'message' => 'update successfull',
            'errors' => 'No action found',
        ]);
    }

    private function getTranslator()
    {
        return \Context::getContext()->getTranslator();
    }

    /**
     * api.blocks_available
     */
    public function getBlocksAvailableAction()
    {
        return (new JsonResponse())->setData([
            'blocks' => \PrettyBlocksModel::getBlocksAvailable(),
            'errors' => 'No action found',
        ]);
    }

    public function homeSimulator(Request $request)
    {
        return $this->render('@Modules/prettyblocks/views/templates/admin/homesimulator.html.twig');
    }

    /**
     * get collection
     *
     * @return \PrestaShopCollection
     */
    public function getCollectionAction(Request $request)
    {
        $collection = pSQL($request->query->get('collection'));
        $query = pSQL($request->query->get('query'));
        $selector = $request->query->get('selector') ?? '{id} - {name}';
        $psCollection = new \PrestaShopCollection($collection, \Context::getContext()->language->id);
        $columns = \FieldFormatter::formatSelectorsToArray($selector);
        $toSearch = \FieldFormatter::matchColumnsWithCollection($collection, $columns);

        $sqlHaving = '';
        foreach ($toSearch as $searchC) {
            if ($sqlHaving !== '') {
                $sqlHaving .= ' OR ';
            }
            $sqlHaving .= $searchC . ' LIKE "%' . pSQL($query) . '%"';
        }
        $psCollection->sqlWhere($sqlHaving);
        $res = $psCollection->getAll();
        $jayParsedAry = [];

        foreach ($res as $r) {
            $formattedName = '';
            foreach ($toSearch as $searchC) {
                $searchC = str_replace('a1.', '', $searchC);
                $searchC = str_replace('a0.', '', $searchC);
                $searchC = str_replace('l.', '', $searchC);
                $primary = str_replace('a1.', '', $toSearch['primary']);
                $primary = str_replace('a0.', '', $primary);
                $primary = str_replace('l.', '', $primary);
                if ($searchC !== $primary) {
                    $formattedName .= $r->{$searchC} ?? '';
                }
            }
            $jayParsedAry[]['show'] = [
                'id' => $r->id,
                'primary' => $r->id,
                'name' => $formattedName,
                'formatted' => \PrettyBlocksModel::formatFrontSelector($r, $selector) ?? $r->name,
            ];
        }

        return (new JsonResponse())->setData(
            [
                'results' => $jayParsedAry,
                'query' => \Tools::getAllValues(),
                'request' => $request->query->all(),
            ]
        );
    }

    /**
     * @param int|null $idLang
     * @param \Context|null $context
     * @param int|null $idShop
     *
     * @return string
     */
    protected function getLangLink($idLang = null, \Context $context = null, $idShop = null)
    {
        static $psRewritingSettings = null;
        if ($psRewritingSettings === null) {
            $psRewritingSettings = (int) \Configuration::get('PS_REWRITING_SETTINGS', null, null, $idShop);
        }

        if (!$context) {
            $context = \Context::getContext();
        }

        if (!in_array($idShop, [$context->shop->id,  null]) || !\Language::isMultiLanguageActivated($idShop) || !$psRewritingSettings) {
            return '';
        }

        if (!$idLang) {
            $idLang = $context->language->id;
        }

        return \Language::getIsoById($idLang) . '/';
    }
}
