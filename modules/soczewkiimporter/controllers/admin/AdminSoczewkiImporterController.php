<?php
require_once dirname(__FILE__) . '/../../classes/FeedReader.php';
require_once dirname(__FILE__) . '/../../classes/CategoryMapper.php';
require_once dirname(__FILE__) . '/../../classes/ProductImporter.php';

class AdminSoczewkiImporterController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();

        $feed = dirname(__FILE__) . '/../../../../feed.xml';
        if (!file_exists($feed)) {
            $feed = _PS_MODULE_DIR_ . 'soczewkiimporter/feed.xml';
        }

        $reader = new SoczewkiImporterFeedReader($feed);
        $category = Tools::getValue('category', '');
        $page = max(1, (int)Tools::getValue('page', 1));
        $limit = 100;

        $data = $reader->getItems($category, ($page - 1) * $limit, $limit);
        $tree = $reader->getCategoryTree();

        $importResult = null;

        if (Tools::isSubmit('import_batch') && $category !== '' && !empty($data['items'])) {
            $importer = new SoczewkiImporterProductImporter();

            // Safety: only import the exact currently displayed batch.
            $importResult = $importer->importItems($data['items']);
        }

        $mapper = new SoczewkiImporterCategoryMapper();
        $itemMap = array();

        foreach ($data['items'] as $item) {
            foreach ($item['categories'] as $path) {
                $resolved = $mapper->resolvePath($path);
                $itemMap[] = array(
                    'path' => $path,
                    'status' => $resolved ? 'ok' : 'error',
                    'resolved_id' => $resolved,
                );
            }
        }

        $this->context->smarty->assign(array(
            'si_tree' => $tree,
            'si_category' => $category,
            'si_items' => $data['items'],
            'si_total' => $data['total'],
            'si_page' => $page,
            'si_limit' => $limit,
            'si_pages' => max(1, (int)ceil($data['total'] / $limit)),
            'itemMap' => $itemMap,
            'importResult' => $importResult,
        ));

        $this->setTemplate('content.tpl');
    }
}
