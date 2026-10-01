<?php
class SoczewkiImporterCategoryMapper
{
    private $categories = null;
    private $langId;

    public function __construct($langId = null)
    {
        $this->langId = $langId ? (int)$langId : (int)Context::getContext()->language->id;
    }

    public function mapPaths(array $paths)
    {
        $result = array();
        foreach ($paths as $path) {
            $result[$path] = $this->resolvePath($path);
        }
        return $result;
    }

    public function resolvePath($path)
    {
        $path = $this->normalizePath($path);
        if ($path === '') return 0;

        $this->loadCategories();

        foreach ($this->categories as $id => $row) {
            if ($this->buildPath((int)$id) === $path) {
                return (int)$id;
            }
        }

        return 0;
    }

    private function loadCategories()
    {
        if ($this->categories !== null) return;

        $rows = Db::getInstance()->executeS(
            'SELECT c.id_category, c.id_parent, cl.name
             FROM `' . _DB_PREFIX_ . 'category` c
             INNER JOIN `' . _DB_PREFIX_ . 'category_lang` cl
               ON cl.id_category = c.id_category
              AND cl.id_lang = ' . (int)$this->langId
             . ' ORDER BY c.id_category ASC'
        );

        $this->categories = array();
        foreach ($rows as $row) {
            $this->categories[(int)$row['id_category']] = array(
                'parent' => (int)$row['id_parent'],
                'name' => trim($row['name']),
            );
        }
    }

    private function buildPath($id)
    {
        $parts = array();
        $seen = array();

        while ($id > 0 && isset($this->categories[$id]) && !isset($seen[$id])) {
            $seen[$id] = true;
            $row = $this->categories[$id];

            // PrestaShop root and shop home are not part of feed paths.
            if ($id != 1 && $id != 2) {
                array_unshift($parts, trim($row['name']));
            }

            $id = (int)$row['parent'];
            if ($id === 0) break;
        }

        return $this->normalizePath(implode(' > ', $parts));
    }

    private function normalizePath($path)
    {
        $parts = array_values(array_filter(array_map('trim', explode('>', (string)$path)), 'strlen'));
        return implode(' > ', $parts);
    }
}
